<?php
// includes/header.php
require_once __DIR__ . '/../includes/session.php';
$csrf = generateCsrfToken();
$tema = $_SESSION['tema'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="<?= $tema ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($pageTitle ?? 'Akademik Takip') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --brand: #6366f1; }
        .navbar-brand { color: var(--brand) !important; font-weight: 700; }
        .badge-beklemede   { background: #fbbf24; color:#000; }
        .badge-devam       { background: #3b82f6; color:#fff; }
        .badge-tamamlandi  { background: #22c55e; color:#fff; }
        .btn-brand { background: var(--brand); color:#fff; border:none; }
        .btn-brand:hover { background: #4f46e5; color:#fff; }
    </style>
</head>
<body>
<?php if (isLoggedIn()): ?>
<nav class="navbar navbar-expand-lg border-bottom mb-4">
    <div class="container">
        <a class="navbar-brand" href="/webProgramlama/Lab8/odev/projects.php">
            <i class="bi bi-mortarboard-fill"></i> Akademik Takip
        </a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="/webProgramlama/Lab8/odev/projects.php">Projeler</a></li>
                <li class="nav-item"><a class="nav-link" href="/webProgramlama/Lab8/odev/tasks.php">Görevler</a></li>
                <li class="nav-item"><a class="nav-link" href="/webProgramlama/Lab8/odev/files.php">Dosyalar</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Hoşgeldiniz, <strong><?= currentUserName() ?></strong></span>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">Hesap</button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/webProgramlama/Lab8/odev/profile.php"><i class="bi bi-person"></i> Profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="post" action="/webProgramlama/Lab8/odev/logout.php">
                                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right"></i> Güvenli Çıkış</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>
<?php endif; ?>
<div class="container">
