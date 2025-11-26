<?php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/Tiket.php';
require_once __DIR__ . '/classes/QRGenerator.php';

session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$tiket = new Tiket();

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Redirect to dashboard
header('Location: dashboard.php');
exit;
?>