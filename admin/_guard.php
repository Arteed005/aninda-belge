<?php

require_once __DIR__ . '/../bootstrap.php';

$adminUser = currentUser();

if (!$adminUser) {
    header('Location: ../giris.php');
    exit;
}

if (empty($adminUser['is_admin'])) {
    require __DIR__ . '/../404.php';
    exit;
}
