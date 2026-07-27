<?php
require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$user = currentUser();
if (!$user) {
    header('Location: giris.php');
    exit;
}

if (!csrf_check($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    exit('Geçersiz istek, lütfen formu yeniden gönderin.');
}

if (empty($user['is_premium'])) {
    header('Location: kisilerim.php');
    exit;
}

$action = $_POST['action'] ?? '';
$id = (int) ($_POST['id'] ?? 0);

if ($action === 'delete') {
    if ($id > 0) {
        deletePersonForUser($id, $user['id']);
        $_SESSION['flash_notice'] = 'Kişi silindi.';
    }
    header('Location: kisilerim.php');
    exit;
}

if ($action === 'create' || $action === 'update') {
    $fullName = trim((string) ($_POST['full_name'] ?? ''));
    $tcNo = trim((string) ($_POST['tc_no'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $address = trim((string) ($_POST['address'] ?? ''));

    if ($fullName === '') {
        $_SESSION['flash_notice'] = 'Ad Soyad zorunludur.';
        header('Location: kisilerim.php');
        exit;
    }
    if ($tcNo !== '' && !preg_match('/^\d{11}$/', $tcNo)) {
        $_SESSION['flash_notice'] = 'T.C. Kimlik No 11 haneli rakamlardan oluşmalıdır.';
        header('Location: kisilerim.php');
        exit;
    }

    $tcNo = $tcNo !== '' ? $tcNo : null;
    $phone = $phone !== '' ? $phone : null;
    $email = $email !== '' ? $email : null;
    $address = $address !== '' ? $address : null;

    if ($action === 'create') {
        savePerson($user['id'], $fullName, $tcNo, $phone, $email, $address);
        $_SESSION['flash_notice'] = 'Kişi kaydedildi.';
    } else {
        updatePersonForUser($id, $user['id'], $fullName, $tcNo, $phone, $email, $address);
        $_SESSION['flash_notice'] = 'Kişi güncellendi.';
    }
}

header('Location: kisilerim.php');
exit;
