<?php
require __DIR__ . '/_guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$return = $_POST['return'] ?? 'belgeler.php';
if (!str_starts_with($return, 'belgeler.php')) {
    $return = 'belgeler.php';
}

if (!csrf_check($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    exit('Geçersiz istek, lütfen formu yeniden gönderin.');
}

$id = (int) ($_POST['id'] ?? 0);
if ($id > 0) {
    deleteDocumentAdmin($id);
    $_SESSION['flash_notice'] = formatDocNo($id) . ' numaralı belge silindi.';
}

header('Location: ' . $return);
exit;
