<?php
// logout.php - Güvenli çıkış
require_once __DIR__ . '/includes/session.php';
startSecureSession();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    // Cookie temizle
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }
    session_unset();
    session_destroy();
}

header('Location: login.php');
exit;
