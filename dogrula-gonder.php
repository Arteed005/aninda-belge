<?php
require_once __DIR__ . '/bootstrap.php';

$user = currentUser();
if (!$user) {
    header('Location: giris.php#giris');
    exit;
}

if (empty($user['email_verified_at'])) {
    $lastResend = $_SESSION['last_verify_resend'] ?? 0;

    if (time() - $lastResend < 60) {
        $_SESSION['flash_notice'] = 'Az önce bir doğrulama e-postası gönderildi. Birkaç dakika bekleyip tekrar dene.';
    } else {
        $_SESSION['last_verify_resend'] = time();
        $token = generateVerificationToken((int) $user['id']);
        sendVerificationEmail($user['email'], $user['name'], $token);
        $_SESSION['flash_notice'] = 'Doğrulama e-postası tekrar gönderildi. Gelen kutunda göremiyorsan spam/gereksiz klasörünü kontrol etmeyi unutma.';
    }
}

header('Location: index.php');
exit;
