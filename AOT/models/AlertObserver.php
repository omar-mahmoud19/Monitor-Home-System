<?php


// ── Observer contract ──────────────────────────────────────────────────────
interface AlertObserver
{
    public function update(array $alert): void;
}

// ── Subject contract ───────────────────────────────────────────────────────
interface AlertSubject
{
    public function attach(AlertObserver $observer): void;
    public function detach(AlertObserver $observer): void;
    public function notify(array $alert): void;
}

// ── Concrete Subject ───────────────────────────────────────────────────────
class AlertManager implements AlertSubject
{
    /** @var AlertObserver[] */
    private array $observers = [];

    public function attach(AlertObserver $observer): void
    {
        $this->observers[] = $observer;
    }

    public function detach(AlertObserver $observer): void
    {
        $this->observers = array_filter(
            $this->observers,
            fn($o) => $o !== $observer
        );
    }

    public function notify(array $alert): void
    {
        foreach ($this->observers as $observer) {
            $observer->update($alert);
        }
    }

    /** Convenience: log + notify in one call */
    public function trigger(array $alert): void
    {
        // Persist to action_log
        (new ActionLogModel())->log(
            $alert['user_id'] ?? 0,
            'alert',
            $alert['message'] ?? json_encode($alert)
        );

        $this->notify($alert);
    }
}

// ── Concrete Observers ─────────────────────────────────────────────────────


class EmailNotifier implements AlertObserver
{
    public function __construct(private string $email) {}

    public function update(array $alert): void
    {
        if (($alert['priority'] ?? '') !== 'high') return;

        $subject = '[AOT Alert] ' . ($alert['title'] ?? 'Smart Home Alert');
        $body    = $alert['message'] ?? json_encode($alert);

        mail($this->email, $subject, $body);
    }
}

/**
 * Placeholder SMS notifier — hook in Twilio or any SMS gateway here.
 */
class SMSNotifier implements AlertObserver
{
    public function __construct(private string $phone) {}

    public function update(array $alert): void
    {
        if (($alert['priority'] ?? '') !== 'high') return;
        // TODO: integrate Twilio SDK
        // TwilioClient::messages->create($this->phone, ['body' => $alert['message']]);
        error_log('[SMSNotifier] Would SMS ' . $this->phone . ': ' . ($alert['message'] ?? ''));
    }
}

/**
 * Writes alert to the alerts table so the dashboard can display it.
 */
class DashboardNotifier implements AlertObserver
{
    public function update(array $alert): void
    {
        (new AlertModel())->create($alert);
    }
}

// ── Lazy require for models used above ────────────────────────────────────
require_once __DIR__ . '/ActionLogModel.php';
require_once __DIR__ . '/AlertModel.php';
