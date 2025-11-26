<?php
require_once 'classes/Tiket.php';

$tiket = new Tiket();
$kode_tiket = $_GET['kode'] ?? '';

$log_scan = $tiket->getLogScan($kode_tiket);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Scan - Sistem Tiket QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-qrcode me-2"></i>
                Sistem Tiket QR Code
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="buat_tiket.php">Buat Tiket</a>
                <a class="nav-link" href="scan.php">Scan QR</a>
                <a class="nav-link" href="daftar_tiket.php">Daftar Tiket</a>
                <a class="nav-link" href="laporan.php">Laporan</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h4 class="mb-0"><i class="fas fa-history me-2"></i>Log Aktivitas Scan</h4>
            </div>
            <div class="card-body">
                <!-- Filter Form -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-8">
                        <label for="kode" class="form-label">Kode Tiket (Opsional)</label>
                        <input type="text" class="form-control" id="kode" name="kode" 
                               value="<?php echo $kode_tiket; ?>" placeholder="Masukkan kode tiket untuk filter">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary d-block w-100">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                    </div>
                </form>

                <!-- Tabel Log -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Kode Tiket</th>
                                <th>Loket</th>
                                <th>Jenis Tiket</th>
                                <th>Jenis Scan</th>
                                <th>Waktu Scan</th>
                                <th>Petugas</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($log_scan)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data log scan</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($log_scan as $index => $log): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td>
                                            <strong class="text-primary"><?php echo $log['kode_tiket']; ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $log['loket'] == 'A' ? 'primary' : 'info'; ?>">
                                                Loket <?php echo $log['loket']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $log['nama_jenis']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $log['jenis_scan'] == 'masuk' ? 'success' : 'warning'; ?>">
                                                <i class="fas fa-<?php echo $log['jenis_scan'] == 'masuk' ? 'sign-in-alt' : 'sign-out-alt'; ?> me-1"></i>
                                                <?php echo ucfirst($log['jenis_scan']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i:s', strtotime($log['waktu_scan'])); ?></td>
                                        <td><?php echo $log['petugas'] ?: '-'; ?></td>
                                        <td><?php echo $log['keterangan'] ?: '-'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <div class="alert alert-info">
                        <strong>Total Log: <?php echo count($log_scan); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
