<?php



require_once __DIR__ . '/Database.php';

class TariffModel
{
    private Database $db;

    // Default fallback rates (used when no DB record exists)
    private const DEFAULTS = [
        'electricity' => ['rate' => 0.12, 'currency' => 'USD', 'unit_label' => 'kWh'],
        'water'       => ['rate' => 0.004, 'currency' => 'USD', 'unit_label' => 'L'],
        'gas'         => ['rate' => 0.80,  'currency' => 'USD', 'unit_label' => 'm³'],
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ── CREATE ────────────────────────────────────────────────────────────

    public function create(array $data): int
    {
        return $this->db->execute(
            'INSERT INTO tariffs
               (user_id, resource_type, rate, currency, unit_label, effective_from)
             VALUES (?, ?, ?, ?, ?, ?)',
            'issdss',
            $data['user_id'],
            $data['resource_type'],
            $data['rate'],
            $data['currency']      ?? 'USD',
            $data['unit_label']    ?? 'kWh',
            $data['effective_from'] ?? date('Y-m-d')
        );
    }

    // ── READ ──────────────────────────────────────────────────────────────

    /**
     * Current active tariff for a resource type.
     * Falls back to defaults if not configured.
     */
    public function getCurrent(int $userId, string $resourceType): array
    {
        $rows = $this->db->query(
            'SELECT * FROM tariffs
         WHERE user_id = ? AND resource_type = ?
           AND effective_from <= CURDATE()
           AND (effective_to IS NULL OR effective_to >= CURDATE())
         ORDER BY effective_from DESC
         LIMIT 1',
            'is',
            $userId,
            $resourceType
        );

        if ($rows) return $rows[0];

        // Default fallback
        $defaults = [
            'electricity' => [
                'rate' => 0.15,
                'peak_rate' => 0.28,
                'offpeak_rate' => 0.12,
                'peak_start' => '18:00',
                'peak_end' => '22:00',
                'currency' => 'USD',
                'unit_label' => 'kWh',
                'solar_capacity' => 4.5,
            ],
            'water' => ['rate' => 0.005, 'currency' => 'USD', 'unit_label' => 'L'],
            'gas'   => ['rate' => 0.45,  'currency' => 'USD', 'unit_label' => 'm3'],
        ];

        return array_merge(
            $defaults[$resourceType] ?? ['rate' => 0.10, 'currency' => 'USD', 'unit_label' => 'unit'],
            ['resource_type' => $resourceType, 'user_id' => $userId]
        );
    }
    /** All tariffs for a user */
    public function findByUser(int $userId): array
    {
        return $this->db->query(
            'SELECT * FROM tariffs WHERE user_id = ? ORDER BY resource_type, effective_from DESC',
            'i',
            $userId
        );
    }

    // ── UPDATE ────────────────────────────────────────────────────────────

    public function update(int $id, array $data): bool
    {
        $allowed = [
            'rate'           => 'd',
            'currency'       => 's',
            'unit_label'     => 's',
            'effective_from' => 's',
            'effective_to'   => 's',
            'peak_rate'      => 'd',
            'offpeak_rate'   => 'd',
            'peak_start'     => 's',
            'peak_end'       => 's',
            'solar_capacity' => 'd',
        ];

        $fields = [];
        $values = [];
        $types  = '';

        foreach ($allowed as $field => $type) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
                $types   .= $type;
            }
        }

        if (!$fields) return false;

        $values[] = $id;
        $types   .= 'i';

        $this->db->execute(
            'UPDATE tariffs SET ' . implode(', ', $fields) . ' WHERE id = ?',
            $types,
            ...$values
        );

        return true;
    }

    // ── HELPERS ───────────────────────────────────────────────────────────

    /**
     * Calculate cost for a given usage amount.
     *
     * @param int    $userId
     * @param string $resourceType
     * @param float  $amount        e.g. 45.3 kWh
     * @return float                cost in user's currency
     */
    public function calculateCost(int $userId, string $resourceType, float $amount): float
    {
        $tariff = $this->getCurrent($userId, $resourceType);
        return round($amount * $tariff['rate'], 2);
    }
}
