<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Premium kullanıcılar (users.is_premium) filigransız indirir. Premium durumu
 * şu an admin panelinden manuel olarak (toggleUserPremium()) işaretleniyor —
 * gerçek ödeme akışı henüz yok (bkz. premium.php).
 */
function shouldWatermark(?array $user = null): bool
{
    if ($user !== null && !empty($user['is_premium'])) {
        return false;
    }
    return WATERMARK_DEFAULT;
}

function buildDompdf(): Dompdf
{
    $fontDir = STORAGE_DIR . '/fonts';
    if (!is_dir($fontDir)) {
        mkdir($fontDir, 0775, true);
    }

    $options = new Options();
    $options->set('isRemoteEnabled', false);
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('fontDir', $fontDir);
    $options->set('fontCache', $fontDir);
    $options->set('chroot', realpath(__DIR__ . '/..'));

    return new Dompdf($options);
}

function renderPdfHtml(array $config, array $renderedClauses, bool $watermark, float $scale = 1.0): string
{
    ob_start();
    require __DIR__ . '/../templates/pdf-shell.php';
    return ob_get_clean();
}

function renderResumePdfHtml(array $config, array $resumeData, string $theme, bool $watermark, float $scale = 1.0): string
{
    $allowedThemes = ['klasik', 'modern', 'minimalist'];
    if (!in_array($theme, $allowedThemes, true)) {
        $theme = 'klasik';
    }
    ob_start();
    require __DIR__ . '/../templates/resume-shell-' . $theme . '.php';
    return ob_get_clean();
}

/**
 * Belge her zaman tek A4 sayfasına sığmalı: içerik taştıkça yazı boyutunu/boşlukları
 * kademeli küçültüp tek sayfaya sığana kadar yeniden render eder. $renderHtml, o
 * turun $scale değeriyle çağrılıp tam HTML döndüren bir callback — hem sözleşme
 * (renderPdfHtml) hem CV (renderResumePdfHtml) yolu aynı döngüyü paylaşır.
 */
function buildFittedPdf(callable $renderHtml): Dompdf
{
    // Klasik/Minimalist temaların entry'lerinde break-inside:avoid olduğu için
    // en küçük ölçekte bile tek sayfaya sığmayan içerik burada 2+ sayfa olarak kabul edilir.
    $scales = [1.0, 0.92, 0.85, 0.78, 0.72, 0.66];
    $dompdf = null;

    foreach ($scales as $i => $scale) {
        $dompdf = buildDompdf();
        $dompdf->loadHtml($renderHtml($scale));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        if ($dompdf->getCanvas()->get_page_count() <= 1 || $i === count($scales) - 1) {
            break;
        }
    }

    return $dompdf;
}
