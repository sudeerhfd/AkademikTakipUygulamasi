<?php
// login.php - Giriş sayfası
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/session.php';
startSecureSession();

if (isLoggedIn()) {
    header('Location: projects.php');
    exit;
}

// "Beni Hatırla" cookie ile otomatik giriş
if (!isLoggedIn() && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    // Güvenli: token'ı DB'de saklayabilirsiniz; basit örnek için sadece user_id şifrelenmiş
    // Gerçek uygulamada remember_tokens tablosu kullanın
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Geçersiz istek.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $sifre = $_POST['sifre'] ?? '';
        $remember = isset($_POST['remember']);

        if (empty($email) || empty($sifre)) {
            $errors[] = 'E-posta ve şifre zorunludur.';
        } else {
            $db = getDB();
            $stmt = $db->prepare("SELECT id, ad_soyad, password, tema FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($sifre, $user['password'])) {
                // Session fixation önlemi: yeni session ID üret
                session_regenerate_id(true);

                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['ad_soyad'];
                $_SESSION['tema']      = $user['tema'];
                $_SESSION['last_activity'] = time();

                // Beni Hatırla
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + (86400 * 30), '/', '', false, true);
                    // Gerçek uygulamada bu token'ı DB'ye kaydedin
                }

                header('Location: projects.php');
                exit;
            } else {
                $errors[] = 'E-posta veya şifre hatalı.';
            }
        }
    }
}

$csrf = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Giriş Yap – Akademik Takip</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container" style="max-width:460px; margin-top:80px;">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h4 class="text-center mb-4">Giriş Yap</h4>

            <?php if (isset($_GET['timeout'])): ?>
                <div class="alert alert-warning py-2">Oturumunuz zaman aşımına uğradı.</div>
            <?php endif; ?>
            <?php foreach ($errors as $e): ?>
                <div class="alert alert-danger py-2"><?= sanitize($e) ?></div>
            <?php endforeach; ?>

            <form method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

                <div class="mb-3">
                    <label class="form-label">E-posta</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= sanitize($_POST['email'] ?? '') ?>" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Şifre</label>
                    <input type="password" name="sifre" class="form-control" required>
                </div>
                <div class="mb-4 form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Beni Hatırla</label>
                </div>
                <button type="submit" class="btn btn-primary w-100">Giriş Yap</button>
            </form>
            <p class="text-center mt-3 mb-0">
                Hesabınız yok mu? <a href="register.php">Kayıt olun</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
