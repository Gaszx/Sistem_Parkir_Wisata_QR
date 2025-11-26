<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/Tiket.php';

$tiket = new Tiket();

// Ambil statistik utama
$today = date('Y-m-d');
$tiket_hari_ini = $tiket->getAllTiket($today, $today);
$tiket_aktif = $tiket->getAllTiket(null, null, null, 'aktif');
$pendapatan_hari_ini = $tiket->getTotalPendapatan($today, $today);

// Inisialisasi dan kalkulasi data untuk statistik loket
$count_loket_a = 0;
$count_loket_b = 0;
$pendapatan_a = 0;
$pendapatan_b = 0;

if (!empty($tiket_hari_ini)) {
    foreach ($tiket_hari_ini as $t) {
        if ($t['loket'] == 'A') {
            $count_loket_a++;
            $pendapatan_a += $t['total_bayar'];
        } elseif ($t['loket'] == 'B') {
            $count_loket_b++;
            $pendapatan_b += $t['total_bayar'];
        }
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Tiket QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .stat-card {
            border-radius: 15px;
            border: none;
            overflow: hidden;
        }
        .stat-icon {
            font-size: 3rem;
            opacity: 0.8;
        }
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 30px;
        }
        .quick-action-btn {
            border-radius: 50px;
            padding: 15px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        .quick-action-btn:hover {
            transform: scale(1.05);
        }
        .nav-link.logout-link {
            color: #ffc107; 
            font-weight: bold;
        }
        .nav-link.logout-link:hover {
            color: #ffffff;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark gradient-bg">
        <div class="container">
            <a class="navbar-brand animate__animated animate__fadeInLeft d-flex align-items-center" href="dashboard.php">
                <img src="assets\images\logoITG.png" alt="Logo_ITG" style="height: 40px; margin-right: 10px;">
                
                <i class="fas fa-qrcode me-2"></i>
                <strong>Sistem Tiket QR Code</strong>
            </a>
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link active" href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                </a>
                <a class="nav-link" href="buat_tiket.php">
                    <i class="fas fa-plus-circle me-1"></i>Buat Tiket
                </a>
                <a class="nav-link" href="scan.php">
                    <i class="fas fa-camera me-1"></i>Scan QR
                </a>
                <a class="nav-link" href="daftar_tiket.php">
                    <i class="fas fa-list me-1"></i>Daftar Tiket
                </a>
                <a class="nav-link" href="laporan.php">
                    <i class="fas fa-chart-line me-1"></i>Laporan
                </a>
                <a class="nav-link logout-link" href="logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold animate__animated animate__fadeInUp">
                <i class="fas fa-qrcode me-3"></i>Dashboard 
            </h1>
            <p class="lead animate__animated animate__fadeInUp animate__delay-1s">
                Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['user']); ?></strong>! Kelola tiket dengan mudah menggunakan QR-Code.
            </p>
            <div class="mt-4 animate__animated animate__fadeInUp animate__delay-2s">
                <a href="buat_tiket.php" class="btn btn-light btn-lg quick-action-btn me-3">
                    <i class="fas fa-plus-circle me-2"></i>Buat Tiket Baru
                </a>
                <a href="scan.php" class="btn btn-outline-light btn-lg quick-action-btn">
                    <i class="fas fa-camera me-2"></i>Scan QR Code
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stat-card card-hover bg-primary text-white animate__animated animate__fadeInUp">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="fw-bold"><?php echo count($tiket_hari_ini); ?></h3>
                                <p class="mb-0">Tiket Hari Ini</p>
                            </div>
                            <i class="fas fa-ticket-alt stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card stat-card card-hover bg-success text-white animate__animated animate__fadeInUp animate__delay-1s">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="fw-bold"><?php echo count($tiket_aktif); ?></h3>
                                <p class="mb-0">Tiket Aktif</p>
                            </div>
                            <i class="fas fa-check-circle stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card stat-card card-hover bg-warning text-white animate__animated animate__fadeInUp animate__delay-2s">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="fw-bold">Rp <?php echo number_format($pendapatan_hari_ini, 0, ',', '.'); ?></h3>
                                <p class="mb-0">Pendapatan Hari Ini</p>
                            </div>
                            <i class="fas fa-money-bill-wave stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card stat-card card-hover bg-info text-white animate__animated animate__fadeInUp animate__delay-3s">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="fw-bold" id="live-time"><?php echo date('H:i'); ?></h3>
                                <p class="mb-0">Waktu Sekarang</p>
                            </div>
                            <i class="fas fa-clock stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card card-hover animate__animated animate__fadeInLeft">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-car me-2"></i>Loket A - Parkir
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <h3 class="text-primary"><?php echo $count_loket_a; ?></h3>
                                <p class="text-muted">Tiket Hari Ini</p>
                            </div>
                            <div class="col-6">
                                <h3 class="text-success">Rp <?php echo number_format($pendapatan_a, 0, ',', '.'); ?></h3>
                                <p class="text-muted">Pendapatan</p>
                            </div>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-primary" style="width: <?php echo count($tiket_hari_ini) > 0 ? ($count_loket_a / count($tiket_hari_ini)) * 100 : 0; ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="card card-hover animate__animated animate__fadeInRight">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-mountain me-2"></i>Loket B - Wisata
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <h3 class="text-info"><?php echo $count_loket_b; ?></h3>
                                <p class="text-muted">Tiket Hari Ini</p>
                            </div>
                            <div class="col-6">
                                <h3 class="text-success">Rp <?php echo number_format($pendapatan_b, 0, ',', '.'); ?></h3>
                                <p class="text-muted">Pendapatan</p>
                            </div>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-info" style="width: <?php echo count($tiket_hari_ini) > 0 ? ($count_loket_b / count($tiket_hari_ini)) * 100 : 0; ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-hover animate__animated animate__fadeInUp">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-bolt me-2"></i>Aksi Cepat
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3 mb-3">
                                <a href="buat_tiket.php" class="btn btn-outline-primary btn-lg w-100 card-hover">
                                    <i class="fas fa-plus-circle d-block mb-2" style="font-size: 2rem;"></i>
                                    <strong>Buat Tiket</strong>
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="scan.php" class="btn btn-outline-warning btn-lg w-100 card-hover">
                                    <i class="fas fa-camera d-block mb-2" style="font-size: 2rem;"></i>
                                    <strong>Scan QR</strong>
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="daftar_tiket.php" class="btn btn-outline-info btn-lg w-100 card-hover">
                                    <i class="fas fa-list d-block mb-2" style="font-size: 2rem;"></i>
                                    <strong>Daftar Tiket</strong>
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="laporan.php" class="btn btn-outline-success btn-lg w-100 card-hover">
                                    <i class="fas fa-chart-line d-block mb-2" style="font-size: 2rem;"></i>
                                    <strong>Laporan</strong>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-hover animate__animated animate__fadeInUp">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>Tiket Terbaru Hari Ini
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($tiket_hari_ini)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada tiket hari ini</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Kode Tiket</th>
                                            <th>Loket</th>
                                            <th>Jenis</th>
                                            <th>Status</th>
                                            <th>Waktu</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($tiket_hari_ini, 0, 10) as $t): ?>
                                            <tr>
                                                <td>
                                                    <strong class="text-primary"><?php echo htmlspecialchars($t['kode_tiket']); ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $t['loket'] == 'A' ? 'primary' : 'info'; ?>">
                                                        Loket <?php echo htmlspecialchars($t['loket']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($t['nama_jenis']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $t['status'] == 'aktif' ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst(htmlspecialchars($t['status'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('H:i', strtotime($t['tanggal_masuk'])); ?></td>
                                                <td class="text-success fw-bold">
                                                    Rp <?php echo number_format($t['total_bayar'], 0, ',', '.'); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="gradient-bg text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">
                <i class="fas fa-qrcode me-2"></i>
                Sistem Tiket Parkir & Wisata QR Code By : Kelompok 6 AKPL &copy; <?php echo date('Y'); ?>
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto refresh setiap 30 detik untuk data baru
        setTimeout(function() {
            location.reload();
        }, 30000);

        // Update waktu real-time
        function updateTime() {
            const timeElement = document.getElementById('live-time');
            if (timeElement) {
                 const now = new Date();
                 const timeString = now.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                }).replace(/\./g, ':');
                timeElement.textContent = timeString;
            }
        }

        setInterval(updateTime, 1000);
        updateTime(); 
    </script>
</body>
</html>