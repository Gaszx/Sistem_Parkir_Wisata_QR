<?php
require_once 'auth_check.php';
require_once 'classes/Tiket.php';

$tiket = new Tiket();

// Handle filter dengan nilai default hari ini
$tanggal_dari = $_GET['tanggal_dari'] ?? date('Y-m-d');
$tanggal_sampai = $_GET['tanggal_sampai'] ?? date('Y-m-d');
$loket = $_GET['loket'] ?? '';
$status = $_GET['status'] ?? '';

$daftar_tiket = $tiket->getAllTiket($tanggal_dari, $tanggal_sampai, $loket, $status);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Tiket - Sistem Tiket QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <style>
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .table-hover tbody tr:hover { background-color: #f1f1f1; }
        .nav-link.logout-link { color: #ffc107; font-weight: bold; }
        .nav-link.logout-link:hover { color: #ffffff; }
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
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>
                <a class="nav-link" href="buat_tiket.php"><i class="fas fa-plus-circle me-1"></i>Buat Tiket</a>
                <a class="nav-link" href="scan.php"><i class="fas fa-camera me-1"></i>Scan QR</a>
                <a class="nav-link active" href="daftar_tiket.php"><i class="fas fa-list me-1"></i>Daftar Tiket</a>
                <a class="nav-link" href="laporan.php"><i class="fas fa-chart-line me-1"></i>Laporan</a>
                <a class="nav-link logout-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <div class="card shadow-sm animate__animated animate__fadeIn">
            <div class="card-header gradient-bg text-white">
                <h4 class="mb-0"><i class="fas fa-list-alt me-2"></i>Daftar Semua Tiket</h4>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3 mb-4 p-3 bg-light border rounded">
                    <div class="col-md-3">
                        <label for="tanggal_dari" class="form-label fw-bold">Tanggal Dari</label>
                        <input type="date" class="form-control" id="tanggal_dari" name="tanggal_dari" value="<?php echo htmlspecialchars($tanggal_dari); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="tanggal_sampai" class="form-label fw-bold">Tanggal Sampai</label>
                        <input type="date" class="form-control" id="tanggal_sampai" name="tanggal_sampai" value="<?php echo htmlspecialchars($tanggal_sampai); ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="loket" class="form-label fw-bold">Loket</label>
                        <select class="form-select" id="loket" name="loket">
                            <option value="">Semua</option>
                            <option value="A" <?php if ($loket == 'A') echo 'selected'; ?>>Loket A</option>
                            <option value="B" <?php if ($loket == 'B') echo 'selected'; ?>>Loket B</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label fw-bold">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Semua</option>
                            <option value="aktif" <?php if ($status == 'aktif') echo 'selected'; ?>>Aktif</option>
                            <option value="keluar" <?php if ($status == 'keluar') echo 'selected'; ?>>Keluar</option>
                            <option value="expired" <?php if ($status == 'expired') echo 'selected'; ?>>Expired</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>No</th>
                                <th>Kode Tiket</th>
                                <th>QR Code</th>
                                <th>Loket</th>
                                <th>Jenis</th>
                                <th>Status</th>
                                <th>Waktu Masuk</th>
                                <th>Waktu Keluar</th>
                                <th>Total Bayar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($daftar_tiket)): ?>
                                <tr>
                                    <td colspan="10" class="text-center p-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Tidak ada data tiket yang cocok dengan filter.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($daftar_tiket as $index => $t): ?>
                                    <tr>
                                        <td class="text-center"><?php echo $index + 1; ?></td>
                                        <td>
                                            <strong class="text-primary"><?php echo htmlspecialchars($t['kode_tiket']); ?></strong>
                                            <?php if ($t['plat_nomor']): ?>
                                                <br><small class="text-muted"><i class="fas fa-car"></i> <?php echo htmlspecialchars($t['plat_nomor']); ?></small>
                                            <?php endif; ?>
                                            <?php if ($t['nama_pengunjung']): ?>
                                                <br><small class="text-muted"><i class="fas fa-user"></i> <?php echo htmlspecialchars($t['nama_pengunjung']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <img src="<?php echo htmlspecialchars($t['qr_code']); ?>" width="50" height="50" class="img-thumbnail" alt="QR Code">
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-<?php echo $t['loket'] == 'A' ? 'primary' : 'info'; ?>">
                                                Loket <?php echo htmlspecialchars($t['loket']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($t['nama_jenis']); ?></td>
                                        <td class="text-center">
                                            <?php
                                            $status_map = ['aktif' => 'success', 'keluar' => 'secondary', 'expired' => 'danger'];
                                            $badge_class = $status_map[$t['status']] ?? 'dark';
                                            ?>
                                            <span class="badge bg-<?php echo $badge_class; ?>">
                                                <?php echo ucfirst(htmlspecialchars($t['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y H:i', strtotime($t['tanggal_masuk'])); ?>
                                            <?php if ($t['petugas_masuk']): ?>
                                                <br><small class="text-muted">Oleh: <?php echo htmlspecialchars($t['petugas_masuk']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($t['tanggal_keluar']): ?>
                                                <?php echo date('d/m/Y H:i', strtotime($t['tanggal_keluar'])); ?>
                                                <?php if ($t['petugas_keluar']): ?>
                                                    <br><small class="text-muted">Oleh: <?php echo htmlspecialchars($t['petugas_keluar']); ?></small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <strong class="text-success">
                                                Rp <?php echo number_format($t['total_bayar'], 0, ',', '.'); ?>
                                            </strong>
                                            <?php if ($t['denda'] > 0): ?>
                                                <br><small class="text-warning" title="Denda">
                                                    <i class="fas fa-exclamation-triangle"></i> Rp <?php echo number_format($t['denda'], 0, ',', '.'); ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="print_tiket.php?kode=<?php echo htmlspecialchars($t['kode_tiket']); ?>" class="btn btn-sm btn-outline-secondary" target="_blank" title="Print Tiket">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <div class="alert alert-light border fw-bold">
                        Total Tiket Ditemukan:
                        <span class="fs-5 ms-2"><?php echo count($daftar_tiket); ?></span>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <footer class="text-center py-4 mt-4 bg-white border-top">
        <p class="mb-0 text-muted">&copy; <?php echo date('Y'); ?> Sistem Tiket Parkir & Wisata QR Code By : Kelompok 6 AKPL</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>