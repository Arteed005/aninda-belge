<?php

define('REMEMBER_ME_DAYS', 30);
define('VERIFY_TOKEN_TTL_HOURS', 24);

function registerUser(string $name, string $email, string $password): int
{
    $pdo = getPDO();
    $stmt = $pdo->prepare(
        'INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash)'
    );
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
    ]);
    return (int) $pdo->lastInsertId();
}

function generateVerificationToken(int $userId): string
{
    $token = bin2hex(random_bytes(32));
    $stmt = getPDO()->prepare(
        'UPDATE users SET verify_token_hash = :hash, verify_token_expires_at = :expires WHERE id = :id'
    );
    $stmt->execute([
        'hash' => hash('sha256', $token),
        'expires' => (new DateTime('+' . VERIFY_TOKEN_TTL_HOURS . ' hours'))->format('Y-m-d H:i:s'),
        'id' => $userId,
    ]);
    return $token;
}

function verifyEmailToken(string $token): bool
{
    $stmt = getPDO()->prepare(
        'SELECT id FROM users WHERE verify_token_hash = :hash AND verify_token_expires_at > NOW()'
    );
    $stmt->execute(['hash' => hash('sha256', $token)]);
    $user = $stmt->fetch();
    if (!$user) {
        return false;
    }

    $update = getPDO()->prepare(
        'UPDATE users SET email_verified_at = NOW(), verify_token_hash = NULL, verify_token_expires_at = NULL WHERE id = :id'
    );
    $update->execute(['id' => $user['id']]);
    return true;
}

function emailExists(string $email): bool
{
    $stmt = getPDO()->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    return $stmt->fetch() !== false;
}

function attemptLogin(string $email, string $password): ?array
{
    $stmt = getPDO()->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return null;
    }

    unset($user['password_hash']);
    return $user;
}

function loginUser(int $userId, bool $remember): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;

    if ($remember) {
        $params = session_get_cookie_params();
        setcookie(session_name(), session_id(), [
            'expires' => time() + REMEMBER_ME_DAYS * 86400,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}

function currentUser(): ?array
{
    static $user = false;
    if ($user !== false) {
        return $user;
    }

    if (empty($_SESSION['user_id'])) {
        return $user = null;
    }

    $stmt = getPDO()->prepare('SELECT id, name, email, email_verified_at, is_premium, is_admin, created_at FROM users WHERE id = :id');
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $row = $stmt->fetch();
    return $user = ($row ?: null);
}

function verifyUserPassword(int $userId, string $password): bool
{
    $stmt = getPDO()->prepare('SELECT password_hash FROM users WHERE id = :id');
    $stmt->execute(['id' => $userId]);
    $row = $stmt->fetch();
    return $row && password_verify($password, $row['password_hash']);
}

function updateUserPassword(int $userId, string $newPassword): void
{
    $stmt = getPDO()->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id');
    $stmt->execute([
        'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        'id' => $userId,
    ]);
}

function logoutUser(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], true);
    }
    session_destroy();
}
