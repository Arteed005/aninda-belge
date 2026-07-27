<?php
require_once __DIR__ . '/bootstrap.php';

$user = currentUser();
if (!$user) {
    header('Location: giris.php');
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
$doc = $id > 0 ? getDocumentByIdForUser($id, $user['id']) : null;

if ($doc === null) {
    http_response_code(404);
    exit('Belge bulunamadı.');
}

$config = getTemplateConfig($doc['template_slug']);
if ($config === null || ($config['kind'] ?? 'contract') === 'resume') {
    // Özgeçmiş kopyalama Faz 1 kapsamı dışında (cv-olustur.php ayrı bir akış kullanıyor).
    header('Location: belgelerim.php');
    exit;
}

$formData = json_decode($doc['form_data'], true) ?: [];
$slug = $doc['template_slug'];

$_SESSION['form_extra_clauses'][$slug] = $formData['extra_clauses'] ?? [];
$_SESSION['form_clause_overrides'][$slug] = $formData['clause_overrides'] ?? [];
unset($formData['extra_clauses'], $formData['clause_overrides']);
$_SESSION['form_values'][$slug] = $formData;

header('Location: sablon.php?slug=' . urlencode($slug));
exit;
