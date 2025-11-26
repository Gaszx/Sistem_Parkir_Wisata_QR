<?php
session_start();

// zona waktu 
date_default_timezone_set('Asia/Jakarta');

// Jika pengguna belum login, alihkan ke halaman login.php
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$_SESSION['login_time'] = time();
?>