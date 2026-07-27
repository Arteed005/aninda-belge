<?php
require_once __DIR__ . '/bootstrap.php';

logoutUser();
header('Location: /');
exit;
