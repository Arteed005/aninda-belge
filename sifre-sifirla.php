<?php
require_once __DIR__ . '/bootstrap.php';

if (currentUser()) {
    header('Location: /');
    exit;
}

$token = trim((string) ($_GET['token'] ?? $_POST['token'] ?? ''));
$isResetForm = $token !== '';
$errors = [];
$notice = $_SESSION['password_reset_notice'] ?? null;
$requestSent = !empty($_SESSION['password_reset_sent']) && empty($_GET['yeniden']);
unset($_SESSION['password_reset_notice']);
unset($_SESSION['password_reset_sent']);

const PASSWORD_RESET_REQUEST_COOLDOWN_SECONDS = 60;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Geçersiz istek, lütfen tekrar deneyin.';
    } elseif (($_POST['action'] ?? '') === 'request') {
        [$emailError, $email] = validateEmail($_POST['email'] ?? null);
        if ($emailError !== null) {
            $errors[] = $emailError;
        } else {
            // E-posta bazlı (veritabanı) ve oturum bazlı (session) cooldown kontrolü
            $user = findUserByEmail($email);
            $dbRemaining = 0;
            if ($user !== null) {
                $dbRemaining = getPasswordResetCooldownSecondsRemaining(
                    $user['password_reset_requested_at'] ?? null,
                    PASSWORD_RESET_REQUEST_COOLDOWN_SECONDS
                );
            }

            $sessionLastRequest = $_SESSION['password_reset_last_request'] ?? 0;
            $sessionRemaining = max(0, PASSWORD_RESET_REQUEST_COOLDOWN_SECONDS - (time() - $sessionLastRequest));

            $secondsRemaining = max($dbRemaining, $sessionRemaining);

            if ($secondsRemaining > 0) {
                $_SESSION['password_reset_notice'] = 'Lütfen ' . $secondsRemaining . ' saniye sonra tekrar deneyin.';
            } else {
                $_SESSION['password_reset_last_request'] = time();
                if ($user !== null) {
                    $resetToken = generatePasswordResetToken((int) $user['id']);
                    sendPasswordResetEmail($user['email'], $user['name'], $resetToken);
                }
                $_SESSION['password_reset_sent'] = true;
                $_SESSION['password_reset_notice'] = 'Bu e-posta adresiyle bir hesap varsa, şifre yenileme bağlantısı gönderildi. Gelen kutunu ve spam klasörünü kontrol et.';
            }
            header('Location: sifre-sifirla.php');
            exit;
        }
    } elseif (($_POST['action'] ?? '') === 'reset') {
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirm'] ?? '');
        if (!isPasswordResetTokenValid($token)) {
            $errors[] = 'Bu şifre yenileme bağlantısı geçersiz veya süresi dolmuş.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Şifre en az 8 karakter olmalıdır.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Şifreler eşleşmiyor.';
        }
        if (!$errors && resetPasswordWithToken($token, $password)) {
            $_SESSION['flash_notice'] = 'Şifren güncellendi. Yeni şifrenle giriş yapabilirsin.';
            header('Location: giris.php#giris');
            exit;
        }
        if (!$errors) {
            $errors[] = 'Bu bağlantı artık kullanılamıyor. Yeni bir şifre yenileme bağlantısı isteyebilirsin.';
        }
    }
}

// Bağlantı yalnızca süresi dolmamış, tek kullanımlık token ile formu gösterir.
$resetTokenValid = $isResetForm && isPasswordResetTokenValid($token);
if ($isResetForm && !$errors && !$resetTokenValid) {
    $errors[] = 'Bu şifre yenileme bağlantısı geçersiz veya süresi dolmuş.';
}

$pageTitle = 'Şifre Yenileme | ' . SITE_TITLE;
$pageDescription = 'Anında Belge hesabının şifresini güvenle yenile.';
require __DIR__ . '/partials/_header.php';
?>

<main>
<section class="auth-section">
  <div class="auth-card">
    <img src="/assets/logo-aninda-belge.png" alt="anında belge" class="auth-logo">
    <?php if ($isResetForm): ?>
      <h1 class="auth-title">Yeni Şifre Oluştur</h1>
      <p class="auth-description">Hesabın için güçlü bir yeni şifre belirle.</p>
    <?php elseif ($requestSent): ?>
      <h1 class="auth-title">E-postanı Kontrol Et</h1>
      <p class="auth-description">Şifre yenileme bağlantısını gönderdik. Bağlantıyı açarak yeni şifreni oluşturabilirsin.</p>
    <?php else: ?>
      <h1 class="auth-title">Şifreni Yenile</h1>
      <p class="auth-description">E-posta adresini yaz, sana güvenli bir yenileme bağlantısı gönderelim.</p>
    <?php endif; ?>

    <?php if ($notice): ?><div class="form-notice"><?= htmlspecialchars($notice) ?></div><?php endif; ?>
    <?php if ($errors): ?>
      <div class="form-errors"><ul><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <?php if ($isResetForm && $resetTokenValid): ?>
      <form method="post" action="sifre-sifirla.php" class="auth-form">
        <input type="hidden" name="action" value="reset">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <div class="field"><label for="password">Yeni Şifre</label><input type="password" id="password" name="password" minlength="8" autocomplete="new-password" required></div>
        <div class="field"><label for="password_confirm">Yeni Şifre Tekrar</label><input type="password" id="password_confirm" name="password_confirm" minlength="8" autocomplete="new-password" required></div>
        <button type="submit" class="auth-submit">Şifremi Güncelle</button>
      </form>
    <?php elseif (!$isResetForm && !$requestSent): ?>
      <form method="post" action="sifre-sifirla.php" class="auth-form">
        <input type="hidden" name="action" value="request">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <div class="field"><label for="email">E-posta</label><input type="email" id="email" name="email" placeholder="ornek@eposta.com" autocomplete="email" required></div>
        <button type="submit" class="auth-submit">Yenileme Bağlantısı Gönder</button>
      </form>
    <?php elseif ($requestSent): ?>
      <?php $nextRequestAt = (int) ($_SESSION['password_reset_last_request'] ?? time()) + PASSWORD_RESET_REQUEST_COOLDOWN_SECONDS; ?>
      <a href="sifre-sifirla.php?yeniden=1" class="auth-submit auth-resend-link" id="resend-link" data-next-request-at="<?= $nextRequestAt ?>" aria-disabled="true">Tekrar Gönder (<span id="resend-countdown">60</span> sn)</a>
    <?php endif; ?>
    <p class="auth-switch"><a href="giris.php#giris">Giriş ekranına dön</a></p>
  </div>
</section>
</main>

<script>
  (function () {
    var link = document.getElementById('resend-link');
    var countdown = document.getElementById('resend-countdown');
    if (!link || !countdown) return;
    var endTime = Number(link.getAttribute('data-next-request-at')) * 1000;
    function tick() {
      var seconds = Math.max(0, Math.ceil((endTime - Date.now()) / 1000));
      if (seconds === 0) {
        link.removeAttribute('aria-disabled');
        link.textContent = 'Tekrar Gönder';
        return;
      }
      countdown.textContent = seconds;
      window.setTimeout(tick, 250);
    }
    link.addEventListener('click', function (event) {
      if (link.getAttribute('aria-disabled') === 'true') event.preventDefault();
    });
    tick();
  })();
</script>

<?php require __DIR__ . '/partials/_footer.php'; ?>
