<?php



require_once __DIR__ . '/Database.php';

class DeviceModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ── CREATE ────────────────────────────────────────────────────────────

    public function create(array $data): int
    {
        $id = $this->db->execute(
            'INSERT INTO devices
       (user_id, name, type, category, status, location, icon)
     VALUES (?, ?, ?, ?, ?, ?, ?)',
            'issssss',
            $data['user_id'],
            $data['name'],
            $data['type']     ?? 'generic',
            $data['category'] ?? 'other',
            $data['status']   ?? 'off',
            $data['location'] ?? 'Home',
            $data['icon']     ?? '🔌'
        );

        if (!empty($data['resources'])) {
            $this->saveResources($id, $data['resources']);
        }

        return $id;
    }

    // ── READ ──────────────────────────────────────────────────────────────

    public function findById(int $id): ?array
    {
        $rows = $this->db->query(
            'SELECT * FROM devices WHERE id = ?',
            'i',
            $id
        );
        if (!$rows) return null;
        $device = $rows[0];
        $device['resources'] = $this->getResources((int) $device['id']);
        return $device;
    }

    public function findByHome(int $homeId): array
    {
        $devices = $this->db->query(
            'SELECT d.* FROM devices d
         JOIN users u ON u.id = d.user_id
         WHERE u.home_id = ?
         ORDER BY d.name',
            'i',
            $homeId
        );
        foreach ($devices as &$d) {
            $d['resources'] = $this->getResources((int)$d['id']);
        }
        return $devices;
    }

    public function totalWattageByHome(int $homeId): float
    {
        $rows = $this->db->query(
            'SELECT COALESCE(SUM(dr.consumption_rate), 0) AS total
         FROM devices d
         JOIN users u ON u.id = d.user_id
         JOIN device_resources dr ON dr.device_id = d.id
         WHERE u.home_id = ?
           AND d.status = "on"
           AND dr.resource_type = "electricity"',
            'i',
            $homeId
        );
        return (float)($rows[0]['total'] ?? 0);
    }
    /** All devices for a user */
    public function findByUser(int $userId): array
    {
        $devices = $this->db->query(
            'SELECT * FROM devices WHERE user_id = ? ORDER BY name',
            'i',
            $userId
        );

        foreach ($devices as &$d) {
            $d['resources'] = $this->getResources((int) $d['id']);
        }

        return $devices;
    }

    /** Devices filtered by status ('on' / 'off') */
    public function findByStatus(int $userId, string $status): array
    {
        return $this->db->query(
            'SELECT * FROM devices WHERE user_id = ? AND status = ?',
            'is',
            $userId,
            $status
        );
    }

    /** Total count of user's devices */
    public function countByUser(int $userId): int
    {
        $rows = $this->db->query(
            'SELECT COUNT(*) AS cnt FROM devices WHERE user_id = ?',
            'i',
            $userId
        );
        return (int) ($rows[0]['cnt'] ?? 0);
    }

    // ── UPDATE ────────────────────────────────────────────────────────────

    public function update(int $id, array $data): bool
    {
        $allowed = [
            'name',
            'type',
            'category',
            'status',
            'location',
            'icon',
            'daily_usage_kwh',
            'monthly_cost'
        ];
        $fields  = [];
        $values  = [];
        $types   = '';

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
                $types   .= is_float($data[$field]) ? 'd' : 's';
            }
        }

        if ($fields) {
            $values[] = $id;
            $types   .= 'i';
            $this->db->execute(
                'UPDATE devices SET ' . implode(', ', $fields) . ' WHERE id = ?',
                $types,
                ...$values
            );
        }

        if (!empty($data['resources'])) {
            $this->saveResources($id, $data['resources']);
        }

        return true;
    }

    /** Toggle on/off */
    public function toggleStatus(int $id): ?string
    {
        $device = $this->findById($id);
        if (!$device) return null;

        $newStatus = $device['status'] === 'on' ? 'off' : 'on';
        $this->db->execute(
            'UPDATE devices SET status = ? WHERE id = ?',
            'si',
            $newStatus,
            $id
        );

        return $newStatus;
    }

    // ── DELETE ────────────────────────────────────────────────────────────

    public function delete(int $id): bool
    {
        $this->db->execute('DELETE FROM devices WHERE id = ?', 'i', $id);
        return true;
    }

    // ── AGGREGATES ────────────────────────────────────────────────────────

    /** Total wattage of all ON devices for a user */
    public function totalWattage(int $userId): float
    {
        $rows = $this->db->query(
            'SELECT COALESCE(SUM(dr.consumption_rate), 0) AS total
         FROM devices d
         JOIN device_resources dr ON dr.device_id = d.id
         WHERE d.user_id = ?
           AND d.status = "on"
           AND dr.resource_type = "electricity"',
            'i',
            $userId
        );
        return (float)($rows[0]['total'] ?? 0);
    }
    public function saveResources(int $deviceId, array $resources): void
    {
        $this->db->execute('DELETE FROM device_resources WHERE device_id = ?', 'i', $deviceId);
        foreach ($resources as $r) {
            $this->db->execute(
                'INSERT INTO device_resources (device_id, resource_type, consumption_rate, unit)
             VALUES (?, ?, ?, ?)',
                'isds',
                $deviceId,
                $r['type'],
                (float)$r['rate'],
                $r['unit']
            );
        }
    }

    public function getResources(int $deviceId): array
    {
        return $this->db->query(
            'SELECT resource_type, consumption_rate, unit FROM device_resources WHERE device_id = ?',
            'i',
            $deviceId
        ) ?: [];
    }
}
