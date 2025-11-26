<?php
require_once 'auth_check.php';
require_once 'classes/Tiket.php';

if (!isset($_GET['kode'])) {
    header('Location: dashboard.php');
    exit;
}

$tiket = new Tiket();
$kode_tiket = $_GET['kode'];
$tiket_detail = $tiket->getTiketByKode($kode_tiket);

if (!$tiket_detail) {
    header('Location: dashboard.php');
    exit;
}

// Menyiapkan data untuk QR Code dalam format JSON
$qr_data = json_encode([
    'kode' => $tiket_detail['kode_tiket'],
    'loket' => $tiket_detail['loket']
]);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Tiket - <?php echo htmlspecialchars($tiket_detail['kode_tiket']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" class="no-print">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" class="no-print">
    <style>
        @media screen {
            body { background-color: #f0f0f0; }
            .tiket-wrapper { display: flex; justify-content: center; padding: 20px; }
            .tiket-print {
                width: 302px; /* Lebar ~80mm */
                padding: 15px; background: white;
                box-shadow: 0 0 10px rgba(0,0,0,0.15); border: 1px solid #ccc;
            }
        }
        @media print {
            .no-print { display: none !important; }
            body, html { margin: 0 !important; padding: 0 !important; background-color: white !important; }
            .tiket-wrapper { padding: 0; }
            .tiket-print {
                width: 78mm; margin: 0; padding: 0;
                border: none; box-shadow: none; font-size: 10pt;
            }
        }
        .tiket-content { font-family: 'Courier New', monospace; color: #000; }
        .ticket-content h4, .ticket-content p, .ticket-content div { margin: 0; padding: 0; }
        .centered { text-align: center; }
        .line { border-top: 1px dashed #000; margin: 8px 0; }
    </style>
</head>
<body>
    <div class="container mt-4 no-print">
        <div class="text-center">
            <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print me-2"></i>Cetak Tiket</button>
            <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
        </div>
    </div>

    <div class="tiket-wrapper">
        <div class="tiket-print">
            <div class="ticket-content">
                <div class="centered">
                    <h4>TIKET <?php echo htmlspecialchars(strtoupper($tiket_detail['loket'] == 'A' ? 'PARKIR' : 'WISATA')); ?></h4>
                    <p>Agro Wisata Tepas Papandayan</p>
                </div>
                <div class="line"></div>
                <div class="centered">
                    <img src="generate_qr.php?data=<?php echo urlencode($qr_data); ?>" width="170" height="170" alt="QR Code">
                    <h5 style="letter-spacing: 1px;" class="mt-2"><?php echo htmlspecialchars($tiket_detail['kode_tiket']); ?></h5>
                </div>
                <div class="line"></div>
                <?php if ($tiket_detail['plat_nomor']): ?>
                    <div><strong>Plat No:</strong> <?php echo htmlspecialchars($tiket_detail['plat_nomor']); ?></div>
                <?php endif; ?>
                <?php if ($tiket_detail['nama_pengunjung']): ?>
                    <div><strong>Pengunjung:</strong> <?php echo htmlspecialchars($tiket_detail['nama_pengunjung']); ?></div>
                <?php endif; ?>
                <div><strong>Masuk:</strong> <?php echo date('d/m/y H:i', strtotime($tiket_detail['tanggal_masuk'])); ?></div>
                <div class="line"></div>
                <div class="centered">
                    <strong>TOTAL BAYAR</strong>
                    <div style="font-size: 1.3em; font-weight: bold;">Rp <?php echo number_format($tiket_detail['total_bayar'], 0, ',', '.'); ?></div>
                </div>
                <div class="line"></div>
                <div class="centered" style="font-size: 0.8em;">
                    <p>Simpan tiket untuk scan di pintu keluar.</p>
                    <p>Terima Kasih Atas Kunjungan Anda.</p>
                </div>
                <div class="centered" style="font-size: 0.6em;">
                    <p>By : Kelompok 6 AKPL</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('load', function() {
            // Beri jeda sedikit lebih lama agar gambar QR dari URL sempat dimuat
            setTimeout(function() { window.print(); }, 1000);
        });
    </script>
</body>
</html>