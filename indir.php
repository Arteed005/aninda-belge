<?php
require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$slug = $_GET['slug'] ?? '';
$config = isValidSlug($slug) ? getTemplateConfig($slug) : null;

if ($config === null) {
    http_response_code(404);
    exit('Şablon bulunamadı.');
}

if (!csrf_check($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    exit('Geçersiz istek, lütfen formu yeniden gönderin.');
}

$user = currentUser();
$watermark = shouldWatermark($user);

[$errors, $clean] = validateFormData($config, $_POST);
$extraClauses = validateCustomClauses($_POST);
$clauseOverrides = validateClauseOverrides($config, $_POST);

if (!empty($errors)) {
    $_SESSION['form_errors'][$slug] = $errors;
    $_SESSION['form_values'][$slug] = $clean;
    $_SESSION['form_extra_clauses'][$slug] = $extraClauses;
    $_SESSION['form_clause_overrides'][$slug] = $clauseOverrides;
    header('Location: sablon.php?slug=' . urlencode($slug));
    exit;
}

$renderedClauses = array_merge(renderClauses($config, $clean, $clauseOverrides), renderCustomClauses($extraClauses));
$dompdf = buildFittedPdf(fn($scale) => renderPdfHtml($config, $renderedClauses, $watermark, $scale));

$clean['extra_clauses'] = $extraClauses;
$clean['clause_overrides'] = $clauseOverrides;
saveDocument($user['id'] ?? null, $slug, $clean, $watermark);

$filename = $slug . '-' . date('Ymd-His') . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);
exit;
