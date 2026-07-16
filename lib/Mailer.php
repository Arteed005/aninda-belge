<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

function baseUrl(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    return $scheme . '://' . $host . $dir;
}

function sendMail(string $toEmail, string $toName, string $subject, string $htmlBody, string $altBody): bool
{
    $mailer = new PHPMailer(true);

    try {
        $mailer->isSMTP();
        $mailer->Host = SMTP_HOST;
        $mailer->Port = SMTP_PORT;
        $mailer->SMTPAuth = true;
        $mailer->Username = SMTP_USERNAME;
        $mailer->Password = SMTP_PASSWORD;
        $mailer->SMTPSecure = SMTP_ENCRYPTION;
        $mailer->CharSet = 'UTF-8';

        $mailer->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mailer->addAddress($toEmail, $toName);
        $mailer->Subject = $subject;
        $mailer->isHTML(true);
        $mailer->Body = $htmlBody;
        $mailer->AltBody = $altBody;

        return $mailer->send();
    } catch (PHPMailerException $e) {
        return false;
    }
}

/**
 * Shared branded shell for every outgoing email — logo + card layout + footer,
 * so new email types (password reset, etc.) don't each reinvent the wrapper.
 */
function renderEmailHtml(string $bodyHtml): string
{
    $logoUrl = baseUrl() . '/assets/logo-aninda-belge.png';
    $year = date('Y');

    return <<<HTML
<!DOCTYPE html>
<html lang="tr">
<head><meta charset="utf-8"></head>
<body style="margin:0;padding:0;background:#f5f7fa;font-family:Arial,Helvetica,sans-serif;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f7fa;padding:32px 16px;">
    <tr>
      <td align="center">
        <table role="presentation" width="480" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;max-width:480px;width:100%;">
          <tr>
            <td style="padding:32px 36px 8px;text-align:center;">
              <img src="{$logoUrl}" alt="Anında Belge" height="44" style="display:block;margin:0 auto;border:0;">
            </td>
          </tr>
          <tr>
            <td style="padding:16px 36px 32px;color:#1a2b4a;font-size:15px;line-height:1.7;">
              {$bodyHtml}
            </td>
          </tr>
          <tr>
            <td style="background:#1a2b4a;padding:18px 36px;text-align:center;color:rgba(255,255,255,0.6);font-size:12px;border-radius:0 0 16px 16px;">
              &copy; {$year} Anında Belge &middot; anindabelge.com
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
}

function sendVerificationEmail(string $toEmail, string $toName, string $token): bool
{
    $verifyUrl = baseUrl() . '/dogrula.php?token=' . urlencode($token);
    $safeName = htmlspecialchars($toName);
    $safeUrl = htmlspecialchars($verifyUrl);
    $ttlHours = VERIFY_TOKEN_TTL_HOURS;

    $bodyHtml = <<<HTML
<p style="margin:0 0 16px;">Merhaba {$safeName},</p>
<p style="margin:0 0 24px;">Anında Belge hesabını oluşturduğun için teşekkürler. E-posta adresini doğrulamak için aşağıdaki butona tıklaman yeterli:</p>
<p style="margin:0 0 28px;text-align:center;">
  <a href="{$safeUrl}" style="display:inline-block;background:#1e9e5c;color:#ffffff;text-decoration:none;font-weight:bold;font-size:15px;padding:13px 30px;border-radius:10px;">E-postamı Doğrula</a>
</p>
<p style="margin:0 0 8px;font-size:13px;color:#5b6b82;">Bu bağlantı {$ttlHours} saat boyunca geçerlidir.</p>
<p style="margin:0 0 8px;font-size:13px;color:#5b6b82;">E-postayı gelen kutunda göremiyorsan spam/gereksiz klasörünü kontrol etmeyi unutma.</p>
<p style="margin:0;font-size:13px;color:#8b96a6;">Bu kaydı sen oluşturmadıysan bu e-postayı yok sayabilirsin.</p>
HTML;

    $altBody = "Merhaba {$toName},\r\n\r\n"
        . "Anında Belge hesabını oluşturduğun için teşekkürler. E-posta adresini doğrulamak için aşağıdaki bağlantıya tıklaman yeterli:\r\n\r\n"
        . $verifyUrl . "\r\n\r\n"
        . "Bu bağlantı " . VERIFY_TOKEN_TTL_HOURS . " saat boyunca geçerlidir.\r\n\r\n"
        . "E-postayı gelen kutunda göremiyorsan spam/gereksiz klasörünü kontrol etmeyi unutma.\r\n\r\n"
        . "Bu kaydı sen oluşturmadıysan bu e-postayı yok sayabilirsin.\r\n\r\n"
        . "Anında Belge";

    return sendMail(
        $toEmail,
        $toName,
        'Anında Belge - E-posta Adresini Doğrula',
        renderEmailHtml($bodyHtml),
        $altBody
    );
}
