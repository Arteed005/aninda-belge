<?php
require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$slug = 'ozgecmis-cv';
$config = getTemplateConfig($slug);
if ($config === null) {
    http_response_code(404);
    exit('Şablon bulunamadı.');
}

if (!csrf_check($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    exit('Geçersiz istek, lütfen formu yeniden gönderin.');
}

$themeKeys = array_column($config['themes'] ?? [], 'key');
$theme = $_GET['tema'] ?? '';
if (!in_array($theme, $themeKeys, true)) {
    $theme = 'klasik';
}

$user = currentUser();
$watermark = shouldWatermark($user);

[$errors, $clean] = validateFormData($config, $_POST);
$groupEntries = validateRepeatableGroups($config, $_POST);

if (!empty($errors)) {
    $_SESSION['form_errors'][$slug] = $errors;
    $_SESSION['form_values'][$slug] = $clean;
    $_SESSION['form_groups'][$slug] = $groupEntries;
    header('Location: cv-olustur.php?tema=' . urlencode($theme));
    exit;
}

$photoDataUri = processResumePhoto($_FILES['photo'] ?? null);

$resumeData = renderResumeData($config, $clean, $groupEntries, $photoDataUri);
$dompdf = buildFittedPdf(fn($scale) => renderResumePdfHtml($config, $resumeData, $theme, $watermark, $scale));

$clean['groups'] = $groupEntries;
$clean['theme'] = $theme;
saveDocument($user['id'] ?? null, $slug, $clean, $watermark);

$filename = $slug . '-' . date('Ymd-His') . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);
exit;
