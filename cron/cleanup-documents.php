<?php

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}

require_once __DIR__ . '/../config/db.php';

$pdo = getPDO();
$stmt = $pdo->prepare('DELETE FROM documents WHERE expires_at <= NOW()');
$stmt->execute();

echo $stmt->rowCount() . " süresi dolmuş belge silindi.\n";
