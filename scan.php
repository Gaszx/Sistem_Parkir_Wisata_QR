<?php
require_once 'auth_check.php';
require_once 'classes/Tiket.php';

$tiket = new Tiket();
$result = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['scan_tiket'])) {
    $kode_tiket = trim($_POST['kode_tiket']);
    // Jenis scan sekarang di-hardcode sebagai 'keluar'
    $jenis_scan = $_POST['jenis_scan']; 
    $petugas = $_SESSION['user']; 
    
    // Logika hanya akan menjalankan scan keluar
    if ($jenis_scan == 'keluar') {
        $result = $tiket->scanKeluar($kode_tiket, $petugas);
    } else {
        // Logika untuk scan masuk bisa disimpan atau dihapus karena tidak akan pernah terpanggil
        $result = ['success' => false, 'message' => 'Jenis scan tidak valid.'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Tiket Keluar - Sistem Tiket QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <style>
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .nav-link.logout-link { color: #ffc107; font-weight: bold; }
        .nav-link.logout-link:hover { color: #ffffff; }
        #qr-reader { border: 2px dashed #ccc; border-radius: 8px; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark gradient-bg">
        <div class="container">
            <a class="navbar-brand animate__animated animate__fadeInLeft d-flex align-items-center" href="dashboard.php">
                <img src="assets\images\.png" alt="Logo_ITG" style="height: 40px; margin-right: 10px;">
                
                <i class="fas fa-qrcode me-2"></i>
                <strong>Sistem Tiket QR Code</strong>
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>
                <a class="nav-link" href="buat_tiket.php"><i class="fas fa-plus-circle me-1"></i>Buat Tiket</a>
                <a class="nav-link active" href="scan.php"><i class="fas fa-camera me-1"></i>Scan QR</a>
                <a class="nav-link" href="daftar_tiket.php"><i class="fas fa-list me-1"></i>Daftar Tiket</a>
                <a class="nav-link" href="laporan.php"><i class="fas fa-chart-line me-1"></i>Laporan</a>
                <a class="nav-link logout-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <div class="row">
            <div class="col-lg-7 mx-auto">
                <div class="card shadow-sm animate__animated animate__fadeIn">
                    <div class="card-header gradient-bg text-white">
                        <h4 class="mb-0"><i class="fas fa-camera-retro me-2"></i>Pindai QR Code untuk Keluar</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($result): ?>
                            <div class="alert alert-<?php echo $result['success'] ? 'success' : 'danger'; ?> animate__animated animate__fadeInDown" role="alert">
                                <h4 class="alert-heading">
                                    <i class="fas fa-<?php echo $result['success'] ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                                    <?php echo $result['success'] ? 'Berhasil!' : 'Gagal!'; ?>
                                </h4>
                                <p><?php echo htmlspecialchars($result['message']); ?></p>
                                <?php if (isset($result['tiket'])): $info = $result['tiket']; ?>
                                    <hr>
                                    <p class="mb-1"><strong>Kode Tiket:</strong> <?php echo htmlspecialchars($info['kode_tiket']); ?></p>
                                    <p class="mb-1"><strong>Jenis:</strong> <?php echo htmlspecialchars($info['nama_jenis']); ?></p>
                                    <?php if (!empty($info['plat_nomor'])): ?>
                                        <p class="mb-1"><strong>Plat Nomor:</strong> <?php echo htmlspecialchars($info['plat_nomor']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($info['nama_pengunjung'])): ?>
                                        <p class="mb-1"><strong>Pengunjung:</strong> <?php echo htmlspecialchars($info['nama_pengunjung']); ?></p>
                                    <?php endif; ?>
                                    <?php if (isset($info['denda']) && $info['denda'] > 0): ?>
                                        <p class="mb-1 text-warning fw-bold"><strong>Denda:</strong> Rp <?php echo number_format($info['denda'], 0, ',', '.'); ?></p>
                                        <p class="mb-0 text-success fw-bold"><strong>Total Bayar Akhir:</strong> Rp <?php echo number_format($info['total_bayar'], 0, ',', '.'); ?></p>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6 text-center">
                                <h5 class="mb-3">Kamera Pemindai</h5>
                                <div id="qr-reader" class="mb-2"></div>
                                <div id="scan-status" class="form-text">Kamera aktif, arahkan ke QR Code...</div>
                            </div>
                            
                            <div class="col-md-6">
                                <h5 class="mb-3">Atau Input Manual</h5>
                                <form method="POST" id="scanForm">
                                    <input type="hidden" name="jenis_scan" value="keluar">
                                    
                                    <div class="mb-3">
                                        <label for="kode_tiket" class="form-label fw-bold">Kode Tiket</label>
                                        <input type="text" class="form-control" id="kode_tiket" name="kode_tiket" placeholder="Kode akan terisi otomatis" required>
                                    </div>
                                    
                                    <input type="hidden" name="scan_tiket" value="1">
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-cogs me-2"></i>Proses Kode Manual
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let html5QrCode = null;
        const scanStatusEl = document.getElementById('scan-status');
        
        function onScanSuccess(decodedText, decodedResult) {
            try {
                const qrData = JSON.parse(decodedText);
                document.getElementById('kode_tiket').value = qrData.kode || decodedText;
            } catch (e) {
                document.getElementById('kode_tiket').value = decodedText;
            }
            
            scanStatusEl.innerHTML = `<span class="text-success fw-bold">QR Ditemukan! Memproses...</span>`;
            
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop();
            }
            
            document.getElementById('scanForm').submit();
        }

        function onScanFailure(error) {
        }

        function startScanning() {
            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("qr-reader");
            }
            
            html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                onScanSuccess,
                onScanFailure
            ).catch(err => {
                scanStatusEl.innerHTML = `<span class="text-danger">Gagal memulai kamera. Pastikan Anda memberikan izin akses kamera pada browser.</span>`;
                console.error("Gagal memulai kamera", err);
            });
        }

        window.addEventListener('load', function() {
            startScanning();
        });

    </script>
        <footer class="text-center py-4 mt-4 bg-white border-top">
        <p class="mb-0 text-muted">&copy; <?php echo date('Y'); ?> Sistem Tiket Parkir & Wisata QR Code By : Kelompok 6 AKPL</p>
    </footer>
</body>
</html>