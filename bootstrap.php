<?php

date_default_timezone_set('Europe/Istanbul');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/mail.php';
require_once __DIR__ . '/config/shopier.php';
require_once __DIR__ . '/lib/Security.php';
require_once __DIR__ . '/lib/TemplateConfig.php';
require_once __DIR__ . '/lib/CalculatorConfig.php';
require_once __DIR__ . '/lib/GuideConfig.php';
require_once __DIR__ . '/lib/ClauseRenderer.php';
require_once __DIR__ . '/lib/ResumeRenderer.php';
require_once __DIR__ . '/lib/ResumePhoto.php';
require_once __DIR__ . '/lib/FormFields.php';
require_once __DIR__ . '/lib/Validator.php';
require_once __DIR__ . '/lib/Documents.php';
require_once __DIR__ . '/lib/Pdf.php';
require_once __DIR__ . '/lib/Auth.php';
require_once __DIR__ . '/lib/Mailer.php';
require_once __DIR__ . '/lib/Admin.php';
require_once __DIR__ . '/lib/Shopier.php';
