<?php
// index.php - Ana yönlendirme
require_once __DIR__ . '/includes/session.php';
startSecureSession();
if (isLoggedIn()) {
    header('Location: projects.php');
} else {
    header('Location: login.php');
}
exit;
