<?php

function getActivePackagesForUser(int $userId): array
{
    $stmt = getPDO()->prepare(
        'SELECT package FROM user_packages
         WHERE user_id = :user_id AND (expires_at IS NULL OR expires_at > NOW())'
    );
    $stmt->execute(['user_id' => $userId]);
    return array_column($stmt->fetchAll(), 'package');
}

function grantPackageDays(int $userId, string $package, int $days): void
{
    $stmt = getPDO()->prepare(
        'INSERT INTO user_packages (user_id, package, expires_at)
         VALUES (:user_id, :package, DATE_ADD(NOW(), INTERVAL :days DAY))
         ON DUPLICATE KEY UPDATE
           expires_at = DATE_ADD(GREATEST(COALESCE(expires_at, NOW()), NOW()), INTERVAL :days2 DAY)'
    );
    $stmt->execute(['user_id' => $userId, 'package' => $package, 'days' => $days, 'days2' => $days]);
}

/**
 * Only meaningful when the caller already knows the package is active
 * (e.g. via getActivePackagesForUser()) — null here means "no expiry" (indefinite),
 * same NULL-means-forever convention as users.premium_expires_at.
 */
function getPackageExpiryForUser(int $userId, string $package): ?string
{
    $stmt = getPDO()->prepare(
        'SELECT expires_at FROM user_packages
         WHERE user_id = :user_id AND package = :package AND (expires_at IS NULL OR expires_at > NOW())'
    );
    $stmt->execute(['user_id' => $userId, 'package' => $package]);
    $row = $stmt->fetch();
    return $row ? $row['expires_at'] : null;
}

function togglePackageForUser(int $userId, string $package): void
{
    $pdo = getPDO();
    $active = in_array($package, getActivePackagesForUser($userId), true);
    if ($active) {
        $stmt = $pdo->prepare('DELETE FROM user_packages WHERE user_id = :user_id AND package = :package');
        $stmt->execute(['user_id' => $userId, 'package' => $package]);
        return;
    }
    $stmt = $pdo->prepare(
        'INSERT INTO user_packages (user_id, package, expires_at) VALUES (:user_id, :package, NULL)
         ON DUPLICATE KEY UPDATE expires_at = NULL'
    );
    $stmt->execute(['user_id' => $userId, 'package' => $package]);
}
