<?php
require_once __DIR__ . '/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$user = currentUser();
if (!$user || empty($user['is_emlak'])) {
    echo json_encode([]);
    exit;
}

$properties = getPropertiesForUser($user['id']);
$result = array_map(static function (array $prop): array {
    $owners = getPropertyOwners((int) $prop['id']);
    $primaryOwner = $owners[0] ?? null;
    $prop['owner_full_name'] = $primaryOwner['full_name'] ?? '';
    $prop['owner_tc_no'] = $primaryOwner['tc_no'] ?? '';
    $prop['owner_phone'] = $primaryOwner['phone'] ?? '';
    $prop['owner_email'] = $primaryOwner['email'] ?? '';
    $prop['owner_address'] = $primaryOwner['address'] ?? '';
    return $prop;
}, $properties);

echo json_encode($result, JSON_UNESCAPED_UNICODE);
