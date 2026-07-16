<?php
require __DIR__ . '/_guard.php';

$id = (int) ($_GET['id'] ?? 0);
$doc = $id > 0 ? getDocumentById($id) : null;

if ($doc === null) {
    http_response_code(404);
    exit('Belge bulunamadı.');
}

$config = getTemplateConfig($doc['template_slug']);
if ($config === null) {
    http_response_code(404);
    exit('Şablon bulunamadı.');
}

$formData = json_decode($doc['form_data'], true) ?: [];
$watermark = (bool) $doc['is_watermarked'];
$isResume = ($config['kind'] ?? 'contract') === 'resume';

if ($isResume) {
    $groupEntries = $formData['groups'] ?? [];
    $resumeData = renderResumeData($config, $formData, $groupEntries);
    $dompdf = buildFittedPdf(fn($scale) => renderResumePdfHtml($config, $resumeData, $watermark, $scale));
} else {
    $extraClauses = $formData['extra_clauses'] ?? [];
    $renderedClauses = array_merge(renderClauses($config, $formData), renderCustomClauses($extraClauses));
    $dompdf = buildFittedPdf(fn($scale) => renderPdfHtml($config, $renderedClauses, $watermark, $scale));
}

$filename = formatDocNo($id) . '.pdf';
$dompdf->stream($filename, ['Attachment' => false]);
exit;
