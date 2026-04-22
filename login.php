<?php
// register.php - Kayıt sayfası
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/session.php';
startSecureSession();

if (isLoggedIn()) {
    header('Location: projects.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF kontrolü
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Geçersiz istek.';
    } else {
        $ad_soyad = trim($_POST['ad_soyad'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $sifre    = $_POST['sifre'] ?? '';
        $sifre2   = $_POST['sifre_tekrar'] ?? '';

        if (empty($ad_soyad)) $errors[] = 'Ad Soyad zorunludur.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Geçerli bir e-posta girin.';
        if (strlen($sifre) < 6) $errors[] = 'Şifre en az 6 karakter olmalıdır.';
        if ($sifre !== $sifre2) $errors[] = 'Şifreler eşleşmiyor.';

        if (empty($errors)) {
            $db = getDB();
            // E-posta tekrar kontrolü
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'Bu e-posta zaten kayıtlı.';
            } else {
                $hash = password_hash($sifre, PASSWORD_BCRYPT);
                $stmt = $db->prepare("INSERT INTO users (ad_soyad, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$ad_soyad, $email, $hash]);
                $success = true;
            }
        }
    }
}

$pageTitle = 'Kayıt Ol';
$csrf = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kayıt Ol – Akademik Takip</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container" style="max-width:480px; margin-top:80px;">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h4 class="text-center mb-4">Kayıt Ol</h4>

            <?php if ($success): ?>
                <div class="alert alert-success">Kayıt başarılı! <a href="login.php">Giriş yapın</a>.</div>
            <?php endif; ?>
            <?php foreach ($errors as $e): ?>
                <div class="alert alert-danger py-2"><?= sanitize($e) ?></div>
            <?php endforeach; ?>

            <form method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

                <div class="mb-3">
                    <label class="form-label">Ad Soyad</label>
                    <input type="text" name="ad_soyad" class="form-control"
                           value="<?= sanitize($_POST['ad_soyad'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">E-posta</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= sanitize($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Şifre</label>
                    <input type="password" name="sifre" class="form-control" required>
                </div>
                <div class="mb-4">
                    <label class="form-label">Şifre Tekrar</label>
                    <input type="password" name="sifre_tekrar" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Kayıt Ol</button>
            </form>
            <p class="text-center mt-3 mb-0">
                Hesabınız var mı? <a href="login.php">Giriş yapın</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
