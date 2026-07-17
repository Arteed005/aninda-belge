<?php

/**
 * Turns an uploaded CV photo into a square, resized JPEG data URI so it can be
 * embedded directly in resume data (and PDF <img src="data:...">) without ever
 * touching the filesystem — the photo rides along in documents.form_data JSON
 * and is cleaned up by the existing retention cron with everything else.
 */
function processResumePhoto(?array $file): ?string
{
    if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    if ($file['size'] > 3 * 1024 * 1024) {
        return null;
    }

    $info = @getimagesize($file['tmp_name']);
    if ($info === false) {
        return null;
    }

    $mime = $info['mime'];
    $src = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($file['tmp_name']),
        'image/png' => @imagecreatefrompng($file['tmp_name']),
        default => null,
    };
    if (!$src) {
        return null;
    }

    $srcW = imagesx($src);
    $srcH = imagesy($src);
    $side = min($srcW, $srcH);
    $srcX = (int) (($srcW - $side) / 2);
    $srcY = (int) (($srcH - $side) / 2);

    $size = 400;
    $dest = imagecreatetruecolor($size, $size);
    $white = imagecolorallocate($dest, 255, 255, 255);
    imagefill($dest, 0, 0, $white);
    imagecopyresampled($dest, $src, 0, 0, $srcX, $srcY, $size, $size, $side, $side);
    imagedestroy($src);

    ob_start();
    imagejpeg($dest, null, 85);
    $jpegData = ob_get_clean();
    imagedestroy($dest);

    return 'data:image/jpeg;base64,' . base64_encode($jpegData);
}
