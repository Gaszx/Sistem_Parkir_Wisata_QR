<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Opsional: Pengecekan session timeout (misal: 30 menit)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 18000)) {
    // Hancurkan session jika sudah terlalu lama
    session_unset();
    session_destroy();

    // Alihkan ke login dengan pesan timeout
    header('Location: login.php?error=timeout');
    exit;
}

// Perbarui waktu aktivitas terakhir
$_SESSION['login_time'] = time();
?>