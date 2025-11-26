<?php
require_once __DIR__ . '/phpqrcode/qrlib.php';

// Ambil data yang akan dijadikan QR code dari parameter URL
$data_to_encode = $_GET['data'] ?? 'Tidak ada data';

// Hasilkan gambar QR Code langsung ke browser
// Fungsi ini akan secara otomatis mengatur header Content-Type: image/png
QRcode::png($data_to_encode, false, QR_ECLEVEL_L, 10, 2);

exit;