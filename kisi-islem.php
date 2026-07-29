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

if (($action === 'create' || $action === 'update') && !PERSONS_FEATURE_ENABLED) {
    $_SESSION['flash_notice'] = 'Bu özellik geçici olarak kullanım dışı.';
    header('Location: kisilerim.php');
    exit;
}

$validPersonTypes = ['ev_sahibi', 'kiraci', 'alici', 'satici', 'genel'];

if ($action === 'create' || $action === 'update') {
    $fullName = trim((string) ($_POST['full_name'] ?? ''));
    $personType = trim((string) ($_POST['person_type'] ?? ''));
    $tcNo = trim((string) ($_POST['tc_no'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $address = trim((string) ($_POST['address'] ?? ''));
    $notes = trim((string) ($_POST['notes'] ?? ''));

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

    $personType = in_array($personType, $validPersonTypes, true) ? $personType : null;
    $tcNo = $tcNo !== '' ? $tcNo : null;
    $phone = $phone !== '' ? $phone : null;
    $email = $email !== '' ? $email : null;
    $address = $address !== '' ? $address : null;
    $notes = $notes !== '' ? $notes : null;

    if ($action === 'create') {
        $personId = savePerson($user['id'], $fullName, $personType, $tcNo, $phone, $email, $address, $notes);
        $_SESSION['flash_notice'] = 'Kişi kaydedildi.';
        savePersonAddresses($personId, $_POST['addresses'] ?? []);
    } elseif (updatePersonForUser($id, $user['id'], $fullName, $personType, $tcNo, $phone, $email, $address, $notes)) {
        // Sahiplik updatePersonForUser'ın WHERE user_id şartıyla zaten doğrulandı,
        // bu yüzden addresses ancak güncelleme gerçekten bu kullanıcıya ait kayıtta başarılıysa yazılır.
        $_SESSION['flash_notice'] = 'Kişi güncellendi.';
        savePersonAddresses($id, $_POST['addresses'] ?? []);
    }
}

header('Location: kisilerim.php');
exit;
