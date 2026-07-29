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

if (empty($user['is_emlak'])) {
    header('Location: emlak-tasinmazlar.php');
    exit;
}

$action = $_POST['action'] ?? '';
$id = (int) ($_POST['id'] ?? 0);

if ($action === 'delete') {
    if ($id > 0) {
        deletePropertyForUser($id, $user['id']);
        $_SESSION['flash_notice'] = 'Taşınmaz silindi.';
    }
    header('Location: emlak-tasinmazlar.php');
    exit;
}

if ($action === 'create' || $action === 'update') {
    $title = trim((string) ($_POST['title'] ?? ''));
    if ($title === '') {
        $_SESSION['flash_notice'] = 'Başlık zorunludur.';
        header('Location: emlak-tasinmazlar.php');
        exit;
    }

    $data = [];
    foreach (PROPERTY_FIELDS as $field) {
        if ($field === 'title') {
            $data[$field] = $title;
            continue;
        }
        $value = trim((string) ($_POST[$field] ?? ''));
        $data[$field] = $value !== '' ? $value : null;
    }

    // Sahip olarak seçilebilecek kişiler, sadece bu kullanıcıya ait olanlarla sınırlanır.
    $ownerIds = [];
    foreach ((array) ($_POST['owner_ids'] ?? []) as $personId) {
        $personId = (int) $personId;
        if ($personId > 0 && getPersonByIdForUser($personId, $user['id']) !== null) {
            $ownerIds[] = $personId;
        }
    }

    if ($action === 'create') {
        $propertyId = saveProperty($user['id'], $data);
        $_SESSION['flash_notice'] = 'Taşınmaz kaydedildi.';
        setPropertyOwners($propertyId, $ownerIds);
    } elseif (updatePropertyForUser($id, $user['id'], $data)) {
        $_SESSION['flash_notice'] = 'Taşınmaz güncellendi.';
        setPropertyOwners($id, $ownerIds);
    }
}

header('Location: emlak-tasinmazlar.php');
exit;
