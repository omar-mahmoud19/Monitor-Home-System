<?php


require_once __DIR__ . '/Database.php';

class UserModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ── CREATE ────────────────────────────────────────────────────────────


    public function register(array $data): array
    {
        // Duplicate email check
        $exists = $this->db->query(
            'SELECT id FROM users WHERE email = ?',
            's',
            $data['email']
        );
        if ($exists) {
            return ['ok' => false, 'msg' => 'Email already registered'];
        }

        $hash   = password_hash($data['password'], PASSWORD_BCRYPT);
        $avatar = strtoupper(
            ($data['firstName'][0] ?? 'U') . ($data['lastName'][0] ?? 'U')
        );

        $id = $this->db->execute(
            'INSERT INTO users
                (firstName, lastName, email, password, homeName, avatar, role)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            'sssssss',
            $data['firstName'],
            $data['lastName'],
            $data['email'],
            $hash,
            $data['homeName'] ?? 'My Home',
            $avatar,
            $data['role']     ?? 'owner'
        );

        return ['ok' => true, 'user' => $this->findById($id)];
    }

    // ── READ ──────────────────────────────────────────────────────────────

    /**
     * Find a single user by ID (no password returned).
     */
    public function findById(int $id): ?array
    {
        $rows = $this->db->query(
            'SELECT id, firstName, lastName, email, homeName, avatar, role,
                    currency, units, address, status,
                    sim_speed, retention,
                    toggle_benchmarking, toggle_weather, toggle_audit,
                    created_at
             FROM users WHERE id = ?',
            'i',
            $id
        );

        if (!$rows) return null;
        return $this->normalise($rows[0]);
    }

    /**
     * Find a single user by email (includes password for auth).
     */
    public function findByEmail(string $email): ?array
    {
        $rows = $this->db->query(
            'SELECT * FROM users WHERE email = ?',
            's',
            $email
        );
        if (!$rows) return null;
        return $this->normalise($rows[0]);
    }

    /**
     * Return all users in the same home as $userId.
     */
    public function findByHome(int $userId): array
    {
        $rows = $this->db->query(
            'SELECT id, firstName, lastName, email, role, status, created_at
             FROM users
             WHERE status != ? OR status IS NULL
             ORDER BY created_at ASC',
            's',
            'deleted'
        );
        return array_map([$this, 'normalise'], $rows ?: []);
    }

    /**
     * Verify credentials; returns safe user array (no password) or null.
     */
    public function authenticate(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);
        if (!$user) return null;
        if (!password_verify($password, $user['password'])) return null;

        unset($user['password']);
        return $user;
    }

    // ── UPDATE ────────────────────────────────────────────────────────────

    /**
     * Update editable profile fields.
     */
    public function updateProfile(int $id, array $data): bool
    {
        $aliasMap = [
            'first_name' => 'firstName',
            'last_name'  => 'lastName',
            'home_name'  => 'homeName',
        ];
        foreach ($aliasMap as $alias => $col) {
            if (isset($data[$alias]) && !isset($data[$col])) {
                $data[$col] = $data[$alias];
            }
        }

        $allowed = ['firstName', 'lastName', 'homeName', 'currency', 'units', 'address'];
        $fields  = [];
        $values  = [];
        $types   = '';

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
                $types   .= 's';
            }
        }

        if (!$fields) return false;

        if (isset($data['firstName']) || isset($data['lastName'])) {
            $existing = $this->findById($id);
            $first    = $data['firstName'] ?? $existing['firstName'] ?? 'U';
            $last     = $data['lastName']  ?? $existing['lastName']  ?? 'U';
            $avatar   = strtoupper(($first[0] ?? 'U') . ($last[0] ?? 'U'));
            $fields[] = 'avatar = ?';
            $values[] = $avatar;
            $types   .= 's';
        }

        $values[] = $id;
        $types   .= 'i';

        $this->db->execute(
            'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?',
            $types,
            ...$values
        );

        return true;
    }

    /**
     * Update system / data settings.
     */
    public function updateSystemSettings(int $id, array $data): bool
    {
        $allowed = [
            'sim_speed'             => 'i',
            'retention'             => 'i',
            'toggle_benchmarking'   => 'i',
            'toggle_weather'        => 'i',
            'toggle_audit'          => 'i',
        ];

        $fields = [];
        $values = [];
        $types  = '';

        foreach ($allowed as $field => $type) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $values[] = (int) $data[$field];
                $types   .= $type;
            }
        }

        if (!$fields) return false;

        $values[] = $id;
        $types   .= 'i';

        $this->db->execute(
            'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?',
            $types,
            ...$values
        );

        return true;
    }

    /**
     * Change password.
     */
    public function changePassword(int $id, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $this->db->execute(
            'UPDATE users SET password = ? WHERE id = ?',
            'si',
            $hash,
            $id
        );
        return true;
    }

    /**
     * Update a single user's role.
     */
    public function updateRole(int $id, string $role): bool
    {
        $allowed = ['owner', 'admin', 'tenant', 'guest'];
        if (!in_array($role, $allowed)) return false;

        $this->db->execute(
            'UPDATE users SET role = ? WHERE id = ?',
            'si',
            $role,
            $id
        );
        return true;
    }

    /**
     * Soft-delete: sets status = 'deleted' and removes all user devices.
     */
    public function softDelete(int $id): bool
    {
        // ── حذف كل الأجهزة المرتبطة بالـ user أولاً ──
        $this->db->execute(
            'DELETE FROM devices WHERE user_id = ?',
            'i',
            $id
        );

        // ── بعدين soft-delete الـ user ──
        $this->db->execute(
            "UPDATE users SET status = 'deleted', email = CONCAT(email, '_deleted_', ?) WHERE id = ?",
            'ii',
            time(),
            $id
        );

        return true;
    }

    // ── DELETE ────────────────────────────────────────────────────────────

    public function delete(int $id): bool
    {
        $this->db->execute('DELETE FROM users WHERE id = ?', 'i', $id);
        return true;
    }

    // ── PRIVATE HELPERS ───────────────────────────────────────────────────

    private function normalise(array $row): array
    {
        $row['first_name'] = $row['firstName'] ?? null;
        $row['last_name']  = $row['lastName']  ?? null;
        $row['home_name']  = $row['homeName']  ?? null;
        $row['name']       = trim(($row['firstName'] ?? '') . ' ' . ($row['lastName'] ?? ''));

        foreach (['toggle_benchmarking', 'toggle_weather', 'toggle_audit'] as $flag) {
            if (array_key_exists($flag, $row)) {
                $row[$flag] = (bool) $row[$flag];
            }
        }

        return $row;
    }
}
