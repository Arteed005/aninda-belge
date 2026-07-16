<?php
require_once __DIR__ . '/bootstrap.php';

if (currentUser()) {
    header('Location: index.php');
    exit;
}

$errors = ['login' => [], 'signup' => []];
$old = ['loginEmail' => '', 'fullName' => '', 'signupEmail' => ''];
$activeTab = 'giris';

if (!empty($_SESSION['auth_flash'])) {
    $flash = $_SESSION['auth_flash'];
    $errors = $flash['errors'] ?? $errors;
    $old = $flash['old'] ?? $old;
    $activeTab = $flash['tab'] ?? 'giris';
    unset($_SESSION['auth_flash']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $tab = $action === 'signup' ? 'kayit' : 'giris';

    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        $_SESSION['auth_flash'] = [
            'errors' => ['login' => $tab === 'giris' ? ['Geçersiz istek, lütfen tekrar deneyin.'] : [], 'signup' => $tab === 'kayit' ? ['Geçersiz istek, lütfen tekrar deneyin.'] : []],
            'old' => $old,
            'tab' => $tab,
        ];
        header('Location: giris.php#' . $tab);
        exit;
    }

    if ($action === 'login') {
        $email = trim($_POST['login_email'] ?? '');
        $password = (string) ($_POST['login_password'] ?? '');
        $remember = !empty($_POST['remember']);

        $loginErrors = [];
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $loginErrors[] = 'Geçerli bir e-posta adresi girin.';
        }
        if ($password === '') {
            $loginErrors[] = 'Şifre zorunludur.';
        }

        $user = null;
        if (!$loginErrors) {
            $user = attemptLogin($email, $password);
            if (!$user) {
                $loginErrors[] = 'E-posta veya şifre hatalı.';
            }
        }

        if ($loginErrors) {
            $_SESSION['auth_flash'] = [
                'errors' => ['login' => $loginErrors, 'signup' => []],
                'old' => ['loginEmail' => $email, 'fullName' => '', 'signupEmail' => ''],
                'tab' => 'giris',
            ];
            header('Location: giris.php#giris');
            exit;
        }

        loginUser((int) $user['id'], $remember);
        header('Location: index.php');
        exit;
    }

    if ($action === 'signup') {
        $name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['signup_email'] ?? '');
        $password = (string) ($_POST['signup_password'] ?? '');
        $confirm = (string) ($_POST['signup_confirm'] ?? '');
        $agree = !empty($_POST['agree']);

        $signupErrors = [];
        if ($name === '') {
            $signupErrors[] = 'Ad soyad zorunludur.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $signupErrors[] = 'Geçerli bir e-posta adresi girin.';
        }
        if (strlen($password) < 6) {
            $signupErrors[] = 'Şifre en az 6 karakter olmalıdır.';
        }
        if ($password !== $confirm) {
            $signupErrors[] = 'Şifreler eşleşmiyor.';
        }
        if (!$agree) {
            $signupErrors[] = 'Devam etmek için Kullanım Şartları ve Gizlilik Politikası\'nı kabul etmelisin.';
        }
        if (!$signupErrors && emailExists($email)) {
            $signupErrors[] = 'Bu e-posta adresi zaten kayıtlı.';
        }

        if ($signupErrors) {
            $_SESSION['auth_flash'] = [
                'errors' => ['login' => [], 'signup' => $signupErrors],
                'old' => ['loginEmail' => '', 'fullName' => $name, 'signupEmail' => $email],
                'tab' => 'kayit',
            ];
            header('Location: giris.php#kayit');
            exit;
        }

        $userId = registerUser($name, $email, $password);
        $token = generateVerificationToken($userId);
        sendVerificationEmail($email, $name, $token);

        loginUser($userId, false);
        header('Location: index.php');
        exit;
    }

    http_response_code(400);
    exit('Geçersiz istek.');
}

$pageTitle = 'Giriş Yap / Kayıt Ol | ' . SITE_TITLE;
$pageDescription = 'Anında belge hesabına giriş yap veya ücretsiz kayıt ol, oluşturduğun belgelere istediğin zaman eriş.';
require __DIR__ . '/partials/_header.php';
?>

<main>
<section class="auth-section">
  <div class="auth-card" data-initial-tab="<?= htmlspecialchars($activeTab) ?>">
    <img src="/assets/logo-aninda-belge.png" alt="anında belge" class="auth-logo">

    <div class="auth-tabs">
      <button type="button" id="tab-giris" class="auth-tab" data-tab-btn="giris">Giriş Yap</button>
      <button type="button" id="tab-kayit" class="auth-tab" data-tab-btn="kayit">Kayıt Ol</button>
    </div>

    <div id="panel-giris" class="auth-panel">
      <?php if (!empty($errors['login'])): ?>
        <div class="form-errors">
          <ul>
            <?php foreach ($errors['login'] as $msg): ?>
              <li><?= htmlspecialchars($msg) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" action="giris.php" class="auth-form">
        <input type="hidden" name="action" value="login">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

        <div class="field">
          <label for="login_email">E-posta</label>
          <input type="email" id="login_email" name="login_email" placeholder="ornek@eposta.com" value="<?= htmlspecialchars($old['loginEmail']) ?>">
        </div>
        <div class="field">
          <label for="login_password">Şifre</label>
          <input type="password" id="login_password" name="login_password" placeholder="••••••••">
        </div>
        <div class="auth-row-between">
          <label class="auth-check-label">
            <input type="checkbox" name="remember">
            Beni Hatırla
          </label>
          <a href="#" class="auth-forgot">Şifremi Unuttum</a>
        </div>
        <button type="submit" class="auth-submit">Giriş Yap</button>
        <p class="auth-switch">Hesabın yok mu? <a href="#" data-tab-btn="kayit">Kayıt Ol</a></p>
      </form>
    </div>

    <div id="panel-kayit" class="auth-panel">
      <?php if (!empty($errors['signup'])): ?>
        <div class="form-errors">
          <ul>
            <?php foreach ($errors['signup'] as $msg): ?>
              <li><?= htmlspecialchars($msg) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" action="giris.php" id="signup-form" class="auth-form">
        <input type="hidden" name="action" value="signup">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

        <div class="field">
          <label for="full_name">Ad Soyad</label>
          <input type="text" id="full_name" name="full_name" placeholder="Adın Soyadın" value="<?= htmlspecialchars($old['fullName']) ?>">
        </div>
        <div class="field">
          <label for="signup_email">E-posta</label>
          <input type="email" id="signup_email" name="signup_email" placeholder="ornek@eposta.com" value="<?= htmlspecialchars($old['signupEmail']) ?>">
        </div>
        <div class="field">
          <label for="signup_password">Şifre</label>
          <input type="password" id="signup_password" name="signup_password" placeholder="••••••••">
        </div>
        <div class="field">
          <label for="signup_confirm">Şifre Tekrar</label>
          <input type="password" id="signup_confirm" name="signup_confirm" placeholder="••••••••">
          <p id="signup-mismatch" class="auth-mismatch" hidden>Şifreler eşleşmiyor</p>
        </div>
        <label class="auth-agree-label">
          <input type="checkbox" name="agree" id="signup_agree">
          <span><a href="kullanim-sartlari.php" target="_blank">Kullanım Şartları'nı</a> ve <a href="gizlilik-politikasi.php" target="_blank">Gizlilik Politikası'nı</a> kabul ediyorum</span>
        </label>
        <button type="submit" id="signup-submit" class="auth-submit">Kayıt Ol</button>
        <p class="auth-switch">Zaten hesabın var mı? <a href="#" data-tab-btn="giris">Giriş Yap</a></p>
      </form>
    </div>
  </div>
</section>
</main>

<script src="/assets/js/auth-tabs.js"></script>

<?php require __DIR__ . '/partials/_footer.php'; ?>
