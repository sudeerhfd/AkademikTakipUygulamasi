<?php
// tasks.php - Görev yönetimi
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/session.php';
checkSession();

$db  = getDB();
$uid = currentUserId();
$errors  = [];
$success = '';

// Proje filtresi
$filter_pid = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;

// POST işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Geçersiz istek.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'add') {
            $baslik   = trim($_POST['baslik'] ?? '');
            $pid      = (int)($_POST['project_id'] ?? 0) ?: null;
            $son_tarih = trim($_POST['son_tarih'] ?? '');
            if (empty($baslik)) {
                $errors[] = 'Görev başlığı zorunludur.';
            } else {
                $st = $db->prepare("INSERT INTO tasks (user_id, project_id, baslik, son_tarih) VALUES (?, ?, ?, ?)");
                $st->execute([$uid, $pid, $baslik, $son_tarih ?: null]);
                $success = 'Görev eklendi.';
            }
        } elseif ($action === 'status') {
            $tid   = (int)$_POST['task_id'];
            $durum = $_POST['durum'] ?? '';
            $allowed = ['beklemede', 'devam_ediyor', 'tamamlandi'];
            if (in_array($durum, $allowed)) {
                $st = $db->prepare("UPDATE tasks SET durum = ? WHERE id = ? AND user_id = ?");
                $st->execute([$durum, $tid, $uid]);
            }
            header('Location: tasks.php' . ($filter_pid ? "?project_id=$filter_pid" : ''));
            exit;
        } elseif ($action === 'delete') {
            $tid = (int)$_POST['task_id'];
            $st  = $db->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
            $st->execute([$tid, $uid]);
            $success = 'Görev silindi.';
        }
    }
}

// Görevleri getir
if ($filter_pid) {
    // Önce bu projenin kullanıcıya ait olduğunu doğrula
    $chk = $db->prepare("SELECT id, baslik FROM projects WHERE id = ? AND user_id = ?");
    $chk->execute([$filter_pid, $uid]);
    $prj = $chk->fetch();
    if (!$prj) { header('Location: tasks.php'); exit; }

    $stmt = $db->prepare(
        "SELECT t.*, p.baslik AS proje_baslik FROM tasks t
         LEFT JOIN projects p ON p.id = t.project_id
         WHERE t.user_id = ? AND t.project_id = ? ORDER BY t.created_at DESC"
    );
    $stmt->execute([$uid, $filter_pid]);
    $pageHeading = 'Proje Görevleri: ' . htmlspecialchars($prj['baslik'], ENT_QUOTES, 'UTF-8');
} else {
    $stmt = $db->prepare(
        "SELECT t.*, p.baslik AS proje_baslik FROM tasks t
         LEFT JOIN projects p ON p.id = t.project_id
         WHERE t.user_id = ? ORDER BY t.created_at DESC"
    );
    $stmt->execute([$uid]);
    $pageHeading = 'Tüm Görevlerim';
}
$tasks = $stmt->fetchAll();

// Proje listesi (yeni görev modalı için)
$projs = $db->prepare("SELECT id, baslik FROM projects WHERE user_id = ? ORDER BY baslik");
$projs->execute([$uid]);
$allProjects = $projs->fetchAll();

$pageTitle = 'Görev Takibi';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><?= $pageHeading ?></h4>
    <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#modalGorev">
        <i class="bi bi-plus-circle"></i> Yeni Görev
    </button>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show py-2">
        <?= sanitize($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (empty($tasks)): ?>
    <div class="text-center text-muted py-5">
        <i class="bi bi-check2-all fs-1"></i>
        <p class="mt-2">Görev bulunamadı.</p>
    </div>
<?php else: ?>
<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Başlık</th>
                <th>Proje</th>
                <th><i class="bi bi-calendar3"></i> Son Tarih</th>
                <th>Durum</th>
                <th>İşlem</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($tasks as $t): ?>
            <tr>
                <td><?= sanitize($t['baslik']) ?></td>
                <td>
                    <?php if ($t['proje_baslik']): ?>
                        <span class="badge bg-primary"><?= sanitize($t['proje_baslik']) ?></span>
                    <?php else: ?>
                        <span class="text-muted small">—</span>
                    <?php endif; ?>
                </td>
                <td class="small">
                    <?= $t['son_tarih'] ? date('d.m.Y H:i', strtotime($t['son_tarih'])) : '—' ?>
                </td>
                <td>
                    <?php if ($t['durum'] === 'beklemede'): ?>
                        <span class="badge badge-beklemede">Beklemede</span>
                    <?php elseif ($t['durum'] === 'devam_ediyor'): ?>
                        <span class="badge badge-devam">Devam ediyor</span>
                    <?php else: ?>
                        <span class="badge badge-tamamlandi">Tamamlandı</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="d-flex gap-1">
                    <?php if ($t['durum'] === 'beklemede'): ?>
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                            <input type="hidden" name="action" value="status">
                            <input type="hidden" name="task_id" value="<?= $t['id'] ?>">
                            <input type="hidden" name="durum" value="devam_ediyor">
                            <button class="btn btn-sm btn-outline-primary">Çalışmaya Başla</button>
                        </form>
                    <?php elseif ($t['durum'] === 'devam_ediyor'): ?>
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                            <input type="hidden" name="action" value="status">
                            <input type="hidden" name="task_id" value="<?= $t['id'] ?>">
                            <input type="hidden" name="durum" value="tamamlandi">
                            <button class="btn btn-sm btn-outline-success">Tamamla</button>
                        </form>
                    <?php endif; ?>
                        <form method="post" onsubmit="return confirm('Silinsin mi?');">
                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="task_id" value="<?= $t['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Yeni Görev Modal -->
<div class="modal fade" id="modalGorev" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yeni Görev Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Görev Başlığı <span class="text-danger">*</span></label>
                        <input type="text" name="baslik" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">İlgili Proje</label>
                        <select name="project_id" class="form-select">
                            <option value="">Genel / Projesiz</option>
                            <?php foreach ($allProjects as $ap): ?>
                                <option value="<?= $ap['id'] ?>"
                                    <?= ($filter_pid == $ap['id']) ? 'selected' : '' ?>>
                                    <?= sanitize($ap['baslik']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Son Tarih</label>
                        <input type="datetime-local" name="son_tarih" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-brand">Görevi Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
