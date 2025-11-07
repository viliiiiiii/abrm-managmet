<?php
declare(strict_types=1);

namespace App\Model;

use App\Security\Passwords;
use PDO;

final class User
{
    public int $id;
    public string $email;
    public string $name;
    public string $password;
    public ?string $totp_secret;
    public string $role_slug;

    public static function fromArray(array $data): self
    {
        $user = new self();
        $user->id = (int)$data['id'];
        $user->email = $data['email'];
        $user->name = $data['name'] ?? '';
        $user->password = $data['password'];
        $user->totp_secret = $data['totp_secret'] ?? null;
        $user->role_slug = $data['role_slug'] ?? 'viewer';
        return $user;
    }

    public static function find(int $id): ?self
    {
        $stmt = DB::core()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? self::fromArray($row) : null;
    }

    public static function findByEmail(string $email): ?self
    {
        $stmt = DB::core()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ? self::fromArray($row) : null;
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function isTwoFactorEnabled(): bool
    {
        return !empty($this->totp_secret);
    }

    public function totpSecret(): string
    {
        return $this->totp_secret ?? '';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role_slug === 'super-admin';
    }
}
