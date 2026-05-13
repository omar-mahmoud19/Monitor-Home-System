<?php

require_once __DIR__ . '/Database.php';

class ActionLogModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ── CREATE ────────────────────────────────────────────────────────────

    /**
     * Short-form logger — use this everywhere.
     
     * @param int 
     * @param string 
     * @param string 
     */
    public function log(int $userId, string $actionType, string $description): int
    {
        return $this->db->execute(
            'INSERT INTO action_log
               (user_id, action_type, description, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?)',
            'issss',
            $userId,
            $actionType,
            $description,
            $_SERVER['REMOTE_ADDR']     ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        );
    }

    // ── READ ──────────────────────────────────────────────────────────────

    public function findById(int $id): ?array
    {
        $rows = $this->db->query(
            'SELECT * FROM action_log WHERE id = ?',
            'i', $id
        );
        return $rows[0] ?? null;
    }

    /** Paginated log for a user */
    public function findByUser(int $userId, int $limit = 50, int $offset = 0): array
    {
        return $this->db->query(
            'SELECT * FROM action_log
             WHERE user_id = ?
             ORDER BY created_at DESC
             LIMIT ? OFFSET ?',
            'iii', $userId, $limit, $offset
        );
    }

    /** Filter by action type (e.g. all "alert" rows) */
    public function findByType(int $userId, string $actionType, int $limit = 50): array
    {
        return $this->db->query(
            'SELECT * FROM action_log
             WHERE user_id = ? AND action_type = ?
             ORDER BY created_at DESC
             LIMIT ?',
            'isi', $userId, $actionType, $limit
        );
    }

    /** Entries within a date range — used by Reports page */
    public function findByRange(int $userId, string $from, string $to): array
    {
        return $this->db->query(
            'SELECT * FROM action_log
             WHERE user_id = ? AND created_at BETWEEN ? AND ?
             ORDER BY created_at DESC',
            'iss', $userId, $from, $to
        );
    }

    /** Count events per day for activity chart */
    public function dailyCounts(int $userId, int $days = 30): array
    {
        return $this->db->query(
            'SELECT DATE(created_at) AS day, COUNT(*) AS events
             FROM action_log
             WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY day
             ORDER BY day ASC',
            'ii', $userId, $days
        );
    }

    // ── DELETE ────────────────────────────────────────────────────────────

    public function purgeOlderThan(int $days): int
    {
        return $this->db->execute(
            'DELETE FROM action_log WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)',
            'i', $days
        );
    }
}
