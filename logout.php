<?php
session_start();

// Hapus semua data variabel dari session
$_SESSION = array();

// Hancurkan session
session_destroy();
header('Location: login.php');
exit;
?>