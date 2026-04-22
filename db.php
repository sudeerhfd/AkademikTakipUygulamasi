<?php
// profile.php - Profil sayfası
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/session.php';
checkSession();

$db  = getDB();
$uid = currentUserId();
$errors  = [];
$success = '';

// Mevcut kullanıcı bilgileri
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Geçersiz istek.';
    } else {
        $tab = $_POST['tab'] ?? 'info';

        // ─── Kişisel Bilgiler ───────────────────────────────────────────────
        if ($tab === 'info') {
            $ad_soyad = trim($_POST['ad_soyad'] ?? '');
            $tema     = in_array($_POST['tema'] ?? '', ['light', 'dark']) ? $_POST['tema'] : 'light';

            if (empty($ad_soyad)) {
                $errors[] = 'Ad Soyad zorunludur.';
            } else {
                $db->prepare("UPDATE users SET ad_soyad = ?, tema = ? WHERE id = ?")
                   ->execute([$ad_soyad, $tema, $uid]);
                $_SESSION['user_name'] = $ad_soyad;
                $_SESSION['tema']      = $tema;
                $user['ad_soyad']      = $ad_soyad;
                $user['tema']          = $tema;
                $success = 'Bilgiler güncellendi.';
            }
        }

        // ─── Şifre Değiştir ─────────────────────────────────────────────────
        if ($tab === 'password') {
            $mevcut = $_POST['mevcut_sifre'] ?? '';
            $yeni   = $_POST['yeni_sifre'] ?? '';
            $yeni2  = $_POST['yeni_sifre2'] ?? '';

            if (!password_verify($mevcut, $user['password'])) {
                $errors[] = 'Mevcut şifre hatalı.';
            } elseif (strlen($yeni) < 6) {
                $errors[] = 'Yeni şifre en az 6 karakter olmalıdır.';
            } elseif ($yeni !== $yeni2) {
                $errors[] = 'Yeni şifreler eşleşmiyor.';
            } else {
                $hash = password_hash($yeni, PASSWORD_BCRYPT);
                $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $uid]);
                $success = 'Şifre başarıyla değiştirildi.';
            }
        }
    }
}

$pageTitle = 'Profilim';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-7">
        <h4 class="mb-4">Profilim</h4>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show py-2">
                <?= sanitize($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php foreach ($errors as $e): ?>
            <div class="alert alert-danger py-2"><?= sanitize($e) ?></div>
        <?php endforeach; ?>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" id="profileTabs">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabInfo">Kişisel Bilgiler</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabPass">Şifre Değiştir</button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Kişisel Bilgiler -->
            <div class="tab-pane fade show active" id="tabInfo">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                            <input type="hidden" name="tab" value="info">
                            <div class="mb-3">
                                <label class="form-label">Ad Soyad</label>
                                <input type="text" name="ad_soyad" class="form-control"
                                       value="<?= sanitize($user['ad_soyad']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">E-posta <span class="text-muted small">(değiştirilemez)</span></label>
                                <input type="email" class="form-control" value="<?= sanitize($user['email']) ?>" disabled>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Tema</label>
                                <select name="tema" class="form-select">
                                    <option value="light" <?= $user['tema'] === 'light' ? 'selected' : '' ?>>Açık</option>
                                    <option value="dark"  <?= $user['tema'] === 'dark'  ? 'selected' : '' ?>>Koyu</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-brand">Kaydet</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Şifre Değiştir -->
            <div class="tab-pane fade" id="tabPass">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                            <input type="hidden" name="tab" value="password">
                            <div class="mb-3">
                                <label class="form-label">Mevcut Şifre</label>
                                <input type="password" name="mevcut_sifre" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Yeni Şifre</label>
                                <input type="password" name="yeni_sifre" class="form-control" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Yeni Şifre Tekrar</label>
                                <input type="password" name="yeni_sifre2" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-brand">Şifreyi Değiştir</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
