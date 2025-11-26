<?php
require_once 'auth_check.php';
require_once 'classes/Tiket.php';

$tiket = new Tiket();

$tanggal_dari = $_GET['tanggal_dari'] ?? date('Y-m-d');
$tanggal_sampai = $_GET['tanggal_sampai'] ?? date('Y-m-d');

$laporan_per_jenis = $tiket->getLaporanKeuangan($tanggal_dari, $tanggal_sampai);
$total_pendapatan = $tiket->getTotalPendapatan($tanggal_dari, $tanggal_sampai);

// Menghitung total tiket warga lokal dari data laporan
$total_warga_lokal = 0;
if (!empty($laporan_per_jenis)) {
    $total_warga_lokal = array_sum(array_column($laporan_per_jenis, 'jumlah_warga_lokal'));
}

$data_harian = $tiket->getPendapatanHarian($tanggal_dari, $tanggal_sampai);

$chart_labels = [];
$chart_data = [];
if (!empty($data_harian)) {
    $period = new DatePeriod(new DateTime($tanggal_dari), new DateInterval('P1D'), (new DateTime($tanggal_sampai))->modify('+1 day'));
    $revenue_map = [];
    foreach($data_harian as $harian) { $revenue_map[$harian['tanggal']] = $harian['total']; }
    foreach ($period as $value) {
        $date_key = $value->format('Y-m-d');
        $chart_labels[] = $value->format('d M');
        $chart_data[] = $revenue_map[$date_key] ?? 0;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan - Sistem Tiket QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
    <style>
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .nav-link.logout-link { color: #ffc107; font-weight: bold; }
        .dataTable-input { border: 1px solid #ced4da; padding: .375rem .75rem; border-radius: .25rem; }
        @media print {
            .no-print { display: none !important; }
            body { background-color: white !important; } .container { max-width: 100% !important; }
            .card { border: none !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark gradient-bg no-print">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
            <img src="assets/images/logoITG.png" alt="Logo ITG" style="height: 40px; margin-right: 10px;">
            <strong>Sistem Tiket QR Code</strong>
        </a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>
            <a class="nav-link" href="buat_tiket.php"><i class="fas fa-plus-circle me-1"></i>Buat Tiket</a>
            <a class="nav-link" href="scan.php"><i class="fas fa-camera me-1"></i>Scan QR</a>
            <a class="nav-link" href="daftar_tiket.php"><i class="fas fa-list me-1"></i>Daftar Tiket</a>
            <a class="nav-link active" href="laporan.php"><i class="fas fa-chart-line me-1"></i>Laporan</a>
            <a class="nav-link logout-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
        </div>
    </div>
</nav>

<div class="container my-4">
    <div class="d-none d-print-block text-center mb-4">
        <h3>Laporan Keuangan Sistem Tiket QR Code</h3>
        <p>Periode: <?php echo date('d F Y', strtotime($tanggal_dari)) . ' - ' . date('d F Y', strtotime($tanggal_sampai)); ?></p><hr>
    </div>
    <div class="card shadow-sm mb-4 no-print">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4"><label for="tanggal_dari" class="form-label fw-bold">Tanggal Dari</label><input type="date" class="form-control" name="tanggal_dari" value="<?php echo htmlspecialchars($tanggal_dari); ?>"></div>
                <div class="col-md-4"><label for="tanggal_sampai" class="form-label fw-bold">Tanggal Sampai</label><input type="date" class="form-control" name="tanggal_sampai" value="<?php echo htmlspecialchars($tanggal_sampai); ?>"></div>
                <div class="col-md-2 d-grid"><button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Filter</button></div>
                <div class="col-md-2 d-grid"><a href="export_laporan.php?tanggal_dari=<?php echo htmlspecialchars($tanggal_dari); ?>&tanggal_sampai=<?php echo htmlspecialchars($tanggal_sampai); ?>" class="btn btn-success"><i class="fas fa-file-excel me-2"></i>Export</a></div>
            </form>
        </div>
    </div>

    <?php if (empty($laporan_per_jenis)): ?>
        <div class="alert alert-info text-center"><i class="fas fa-info-circle fa-2x mb-2 d-block"></i><p class="mb-0">Tidak ada data untuk periode yang dipilih.</p></div>
    <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Laporan Keuangan</h4>
                <button class="btn btn-outline-secondary no-print" onclick="window.print()"><i class="fas fa-print me-2"></i>Cetak</button>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-7 mb-4">
                        <h5 class="mb-3 text-center">Grafik Pendapatan Harian</h5>
                        <div style="height: 350px;"><canvas id="revenueLineChart"></canvas></div>
                    </div>
                    <div class="col-lg-5 mb-4">
                        <h5 class="mb-3 text-center">Ringkasan Periode</h5>
                        <div class="card bg-success text-white mb-3"><div class="card-body"><p class="mb-1">Total Pendapatan</p><h3 class="fw-bold">Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></h3></div></div>
                        <div class="card bg-primary text-white"><div class="card-body"><p class="mb-1">Jumlah Tiket Warga Lokal (Gratis)</p><h3 class="fw-bold"><?php echo $total_warga_lokal; ?> Tiket</h3></div></div>
                    </div>
                </div>
                <hr>
                <h5 class="mb-3 mt-4">Rincian per Jenis Tiket</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="tabelRincian">
                        <thead class="table-dark">
                            <tr>
                                <th>Loket</th>
                                <th>Jenis Tiket</th>
                                <th class="text-center">Jumlah Tiket</th>
                                <th class="text-center">Warga Lokal</th>
                                <th class="text-end">Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($laporan_per_jenis as $item): ?>
                            <tr>
                                <td><span class="badge bg-<?php echo $item['loket'] == 'A' ? 'primary' : 'info'; ?>">Loket <?php echo htmlspecialchars($item['loket']); ?></span></td>
                                <td><?php echo htmlspecialchars($item['nama_jenis']); ?></td>
                                <td class="text-center"><?php echo $item['jumlah_tiket']; ?></td>
                                <td class="text-center"><?php echo $item['jumlah_warga_lokal']; ?></td>
                                <td class="text-end">Rp <?php echo number_format($item['total_pendapatan'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<footer class="text-center py-4 mt-4 bg-white border-top no-print">
    <p class="mb-0 text-muted">&copy; <?php echo date('Y'); ?> Sistem Tiket Parkir & Wisata QR Code By : Kelompok 6 AKPL</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($chart_data)): ?>
        const ctx = document.getElementById('revenueLineChart').getContext('2d');
        const config = {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    label: 'Pendapatan Harian (Rp)',
                    data: <?php echo json_encode($chart_data); ?>,
                    fill: true,
                    backgroundColor: 'rgba(102, 126, 234, 0.2)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { callback: value => 'Rp ' + new Intl.NumberFormat('id-ID').format(value) } } },
                plugins: { tooltip: { callbacks: { label: context => 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y) } } }
            }
        };
        new Chart(ctx, config);
    <?php endif; ?>

    const dataTable = new simpleDatatables.DataTable("#tabelRincian", {
        searchable: true,
        perPageSelect: false,
        labels: {
            placeholder: "Cari...",
            noRows: "Tidak ada data",
            info: "Menampilkan {start}-{end} dari {rows} baris",
        }
    });
});
</script>
</body>
</html>