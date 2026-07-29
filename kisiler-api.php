<?php
require_once __DIR__ . '/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$user = currentUser();
if (!$user || empty($user['is_premium']) || !PERSONS_FEATURE_ENABLED) {
    echo json_encode([]);
    exit;
}

echo json_encode(getPersonsForUser($user['id']), JSON_UNESCAPED_UNICODE);
