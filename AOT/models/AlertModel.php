<?php


require_once __DIR__ . '/Database.php';

class AlertModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ── CREATE ────────────────────────────────────────────────────────────

    public function create(array $data): int
    {
        return $this->db->execute(
            'INSERT INTO alerts
               (user_id, device_id, type, priority, title, message, is_read)
             VALUES (?, ?, ?, ?, ?, ?, 0)',
            'iissss',
            $data['user_id'],
            $data['device_id'] ?? null,
            $data['type']      ?? 'info',
            $data['priority']  ?? 'medium',
            $data['title']     ?? 'Alert',
            $data['message']   ?? ''
        );
    }

    // ── READ ──────────────────────────────────────────────────────────────

    public function findById(int $id): ?array
    {
        $rows = $this->db->query(
            'SELECT * FROM alerts WHERE id = ?',
            'i', $id
        );
        return $rows[0] ?? null;
    }

    /** All alerts for a user, newest first */
    public function findByUser(int $userId, int $limit = 20): array
    {
        return $this->db->query(
            'SELECT a.*, d.name AS device_name
             FROM alerts a
             LEFT JOIN devices d ON a.device_id = d.id
             WHERE a.user_id = ?
             ORDER BY a.created_at DESC
             LIMIT ?',
            'ii', $userId, $limit
        );
    }

    /** Unread alerts count — shown in topbar badge */
    public function unreadCount(int $userId): int
    {
        $rows = $this->db->query(
            'SELECT COUNT(*) AS cnt FROM alerts WHERE user_id = ? AND is_read = 0',
            'i', $userId
        );
        return (int) ($rows[0]['cnt'] ?? 0);
    }

    /** Only active (unread) high-priority alerts — for dashboard banner */
    public function activeCritical(int $userId): array
    {
        return $this->db->query(
            'SELECT * FROM alerts
             WHERE user_id = ? AND is_read = 0 AND priority = "high"
             ORDER BY created_at DESC',
            'i', $userId
        );
    }

    // ── UPDATE ────────────────────────────────────────────────────────────

    public function markRead(int $id): bool
    {
        $this->db->execute(
            'UPDATE alerts SET is_read = 1 WHERE id = ?',
            'i', $id
        );
        return true;
    }

    public function markAllRead(int $userId): bool
    {
        $this->db->execute(
            'UPDATE alerts SET is_read = 1 WHERE user_id = ?',
            'i', $userId
        );
        return true;
    }

    // ── DELETE ────────────────────────────────────────────────────────────

    public function delete(int $id): bool
    {
        $this->db->execute('DELETE FROM alerts WHERE id = ?', 'i', $id);
        return true;
    }

    public function deleteAllRead(int $userId): int
    {
        return $this->db->execute(
            'DELETE FROM alerts WHERE user_id = ? AND is_read = 1',
            'i', $userId
        );
    }
}
