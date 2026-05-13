<?php



require_once __DIR__ . '/Database.php';

class GoalModel
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
            'INSERT INTO goals
           (user_id, resource_type, period, target_value, current_value, unit, status)
         VALUES (?, ?, ?, ?, ?, ?, ?)',
            'issddss',
            $data['user_id'],
            $data['resource_type'],
            $data['period']        ?? 'monthly',
            (float)$data['target_value'],
            (float)($data['current_value'] ?? 0),
            $data['unit']          ?? '$',
            $data['status']        ?? 'active'
        );
    }
    // ── READ ──────────────────────────────────────────────────────────────

    public function findById(int $id): ?array
    {
        $rows = $this->db->query(
            'SELECT * FROM goals WHERE id = ?',
            'i',
            $id
        );
        return $rows[0] ?? null;
    }

    public function findByUser(int $userId): array
    {
        return $this->db->query(
            'SELECT * FROM goals WHERE user_id = ? ORDER BY created_at DESC',
            'i',
            $userId
        );
    }

    public function findActive(int $userId): array
    {
        return $this->db->query(
            'SELECT * FROM goals WHERE user_id = ? AND status = "active"',
            'i',
            $userId
        );
    }

    // ── UPDATE ────────────────────────────────────────────────────────────

    public function updateProgress(int $id, float $currentValue): bool
    {
        $goal = $this->findById($id);
        if (!$goal) return false;

        $status = $currentValue >= $goal['target_value'] ? 'achieved' : 'active';

        $this->db->execute(
            'UPDATE goals SET current_value = ?, status = ? WHERE id = ?',
            'dsi',
            $currentValue,
            $status,
            $id
        );

        return true;
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['resource_type', 'period', 'target_value', 'current_value', 'unit', 'status'];
        $fields  = [];
        $values  = [];
        $types   = '';

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
                $types   .= in_array($field, ['target_value', 'current_value']) ? 'd' : 's';
            }
        }

        if (!$fields) return false;

        $values[] = $id;
        $types   .= 'i';

        $this->db->execute(
            'UPDATE goals SET ' . implode(', ', $fields) . ' WHERE id = ?',
            $types,
            ...$values
        );

        return true;
    }

    // ── DELETE ────────────────────────────────────────────────────────────

    public function delete(int $id): bool
    {
        $this->db->execute('DELETE FROM goals WHERE id = ?', 'i', $id);
        return true;
    }

    // ── AGGREGATES ────────────────────────────────────────────────────────

    /** Percentage completion 0–100 */
    public function progress(int $id): float
    {
        $goal = $this->findById($id);
        if (!$goal || $goal['target_value'] == 0) return 0;
        return min(100, round(($goal['current_value'] / $goal['target_value']) * 100, 1));
    }
}
