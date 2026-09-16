<?php
require_once __DIR__ . '/bootstrap.php';

$user = currentUser();
if (!$user) {
    header('Location: giris.php#giris');
    exit;
}

const EMAIL_VERIFY_REQUEST_COOLDOWN_SECONDS = 60;

if (empty($user['email_verified_at'])) {
    $dbRemaining = getEmailVerifyCooldownSecondsRemaining(
        $user['email_verify_requested_at'] ?? null,
        EMAIL_VERIFY_REQUEST_COOLDOWN_SECONDS
    );

    $sessionLastResend = $_SESSION['last_verify_resend'] ?? 0;
    $sessionRemaining = max(0, EMAIL_VERIFY_REQUEST_COOLDOWN_SECONDS - (time() - $sessionLastResend));

    $secondsRemaining = max($dbRemaining, $sessionRemaining);

    if ($secondsRemaining > 0) {
        $_SESSION['flash_notice'] = 'Lütfen ' . $secondsRemaining . ' saniye sonra tekrar deneyin.';
    } else {
        $_SESSION['last_verify_resend'] = time();
        $token = generateVerificationToken((int) $user['id']);
        sendVerificationEmail($user['email'], $user['name'], $token);
        $_SESSION['flash_notice'] = 'Doğrulama e-postası tekrar gönderildi. Gelen kutunda göremiyorsan spam/gereksiz klasörünü kontrol etmeyi unutma.';
    }
}

header('Location: /');
exit;
