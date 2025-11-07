<?php
declare(strict_types=1);

namespace App\Model;

use PDO;
use PDOException;

final class User
{
    public int $id;
    public string $email;
    public string $name;
    public string $password;
    public int $role_id = 0;
    public string $role_slug;
    public string $role_label = '';

    public static function fromArray(array $data): self
    {
        $user = new self();
        $user->id = (int)$data['id'];
        $user->email = $data['email'];
        $user->name = $data['name'] ?? ($data['full_name'] ?? '');
        $user->password = $data['pass_hash'] ?? ($data['password_hash'] ?? '');
        $user->role_id = isset($data['role_id']) ? (int)$data['role_id'] : 0;

        if (isset($data['role_slug']) && $data['role_slug'] !== null) {
            $user->role_slug = (string)$data['role_slug'];
        } elseif (isset($data['role']) && !is_numeric($data['role'])) {
            $user->role_slug = (string)$data['role'];
        } elseif (isset($data['key_slug'])) {
            $user->role_slug = (string)$data['key_slug'];
        } else {
            $user->role_slug = 'viewer';
        }

        $user->role_label = (string)($data['role_label'] ?? $data['label'] ?? '');

        if ($user->role_id > 0 && ($user->role_slug === '' || $user->role_label === '')) {
            $meta = Role::metadataForId($user->role_id);
            if ($meta) {
                if (($meta['key_slug'] ?? '') !== '') {
                    $user->role_slug = (string)$meta['key_slug'];
                }
                if (($meta['label'] ?? '') !== '') {
                    $user->role_label = (string)$meta['label'];
                }
            }
        }

        if ($user->role_slug === '') {
            $user->role_slug = 'viewer';
        }

        if ($user->role_label === '') {
            $user->role_label = ucwords(str_replace('-', ' ', $user->role_slug));
        }
        return $user;
    }

    public static function find(int $id): ?self
    {
        $row = self::fetchUser('id', $id);
        return $row ? self::fromArray($row) : null;
    }

    public static function findByEmail(string $email): ?self
    {
        $row = self::fetchUser('email', $email);
        return $row ? self::fromArray($row) : null;
    }

    /**
     * Fetch a user row optionally joined with roles metadata.
     * Falls back to the legacy users table shape if the join fails.
     *
     * @param 'id'|'email' $field
     * @param mixed        $value
     */
    private static function fetchUser(string $field, $value): ?array
    {
        $pdo = DB::core();
        $column = $field === 'id' ? 'id' : 'email';

        $sql = <<<SQL
            SELECT u.*, r.key_slug AS role_slug, r.label AS role_label
            FROM users u
            LEFT JOIN roles r ON r.id = u.role_id
            WHERE u.{$column} = :value
            LIMIT 1
        SQL;

        try {
            $stmt = $pdo->prepare($sql);
            $paramType = $field === 'id' ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue(':value', $value, $paramType);
            $stmt->execute();
            $row = $stmt->fetch();
            if ($row !== false) {
                return $row;
            }
        } catch (PDOException $e) {
            // Fall back to legacy schema without roles table/column
        }

        $fallback = $pdo->prepare("SELECT * FROM users WHERE {$column} = :value LIMIT 1");
        $paramType = $field === 'id' ? PDO::PARAM_INT : PDO::PARAM_STR;
        $fallback->bindValue(':value', $value, $paramType);
        $fallback->execute();
        $row = $fallback->fetch();

        return $row !== false ? $row : null;
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function isSuperAdmin(): bool
    {
        $slug = strtolower($this->role_slug);
        return $slug === 'root' || $slug === 'super-admin';
    }
}
