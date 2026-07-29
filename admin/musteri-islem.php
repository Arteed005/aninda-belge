<?php
require __DIR__ . '/_guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$return = $_POST['return'] ?? 'musteriler.php';
if (!str_starts_with($return, 'musteriler.php')) {
    $return = 'musteriler.php';
}

if (!csrf_check($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    exit('Geçersiz istek, lütfen formu yeniden gönderin.');
}

$id = (int) ($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

$validPackages = ['ucretsiz', 'premium', 'emlak'];

if ($id > 0 && $action === 'set_package') {
    $package = $_POST['package'] ?? '';
    if (in_array($package, $validPackages, true)) {
        // Emlak paketi Premium'un tüm avantajlarını da kapsar, bu yüzden
        // emlak seçilince is_premium de birlikte açılır.
        setUserPremium($id, $package === 'premium' || $package === 'emlak');
        setPackageForUser($id, 'emlak', $package === 'emlak');
        $_SESSION['flash_notice'] = 'Müşterinin paketi güncellendi.';
    }
} elseif ($id > 0 && $action === 'delete') {
    if ($id === $adminUser['id']) {
        $_SESSION['flash_notice'] = 'Kendi hesabınızı buradan silemezsiniz.';
    } else {
        deleteUserAdmin($id);
        $_SESSION['flash_notice'] = 'Müşteri silindi.';
    }
}

header('Location: ' . $return);
exit;
