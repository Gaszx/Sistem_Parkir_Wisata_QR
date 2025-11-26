<?php
require_once 'auth_check.php';
require_once 'classes/Tiket.php';

$tiket = new Tiket();
$jenis_tiket_list = $tiket->getJenisTiket();

// Membuat map untuk mempermudah pencarian data tiket
$jenis_tiket_map = [];
foreach ($jenis_tiket_list as $jt) {
    $jenis_tiket_map[$jt['id']] = $jt;
}

$message = '';
$success = false;
$kode_tiket_baru = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buat_tiket'])) {
    // --- MENGAMBIL SEMUA DATA DARI FORM ---
    $jenis_tiket_id = $_POST['jenis_tiket_id'] ?? null;
    $petugas = $_SESSION['user']; 
    $plat_nomor = strtoupper($_POST['plat_nomor'] ?? '');
    $jumlah_pengunjung = !empty($_POST['jumlah_pengunjung']) ? (int)$_POST['jumlah_pengunjung'] : 1;
    $nama_pengunjung_input = $_POST['nama_pengunjung'] ?? '';
    
    // Mengambil data untuk fitur baru
    $is_warga_lokal = isset($_POST['warga_lokal']);
    $harga_manual = !empty($_POST['harga_manual']) ? (float)$_POST['harga_manual'] : 0;
    
    $selected_tiket_info = $jenis_tiket_map[$jenis_tiket_id] ?? null;
    
    if ($selected_tiket_info) {
        $total_bayar = $selected_tiket_info['harga'];
        $nama_pengunjung_final = $nama_pengunjung_input;

        if ($selected_tiket_info['loket'] == 'B') { // Logika hanya untuk tiket wisata
            // Prioritas harga: manual > normal
            $harga_satuan = ($harga_manual > 0) ? $harga_manual : $selected_tiket_info['harga'];
            $total_bayar = $harga_satuan * $jumlah_pengunjung;
            $nama_pengunjung_final = $nama_pengunjung_input . ' (' . $jumlah_pengunjung . ' orang)';
        }
        
        // Jika Warga Lokal, override total bayar menjadi 0, berlaku untuk semua loket
        if ($is_warga_lokal) {
            $total_bayar = 0;
        }

        try {
            // Memanggil fungsi buatTiket dengan semua parameter yang dibutuhkan
            $kode_tiket_baru = $tiket->buatTiket(
                $jenis_tiket_id,
                $petugas,
                $plat_nomor,
                $nama_pengunjung_final,
                $jumlah_pengunjung, // Parameter baru
                $total_bayar,
                $is_warga_lokal,    // Parameter baru
                $harga_manual       // Parameter baru
            );

            if ($kode_tiket_baru) {
                $message = "Tiket berhasil dibuat dengan kode: " . htmlspecialchars($kode_tiket_baru);
                $success = true;
            } else {
                $message = "Gagal membuat tiket!";
            }
        } catch (Exception $e) {
            $message = "Terjadi kesalahan: " . $e->getMessage();
            $success = false;
        }
    } else {
        $message = "Jenis tiket tidak valid!";
        $success = false;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Tiket - Sistem Tiket QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <style>
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card-hover { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25); }
        .form-check-input:checked { background-color: #667eea; border-color: #667eea; }
        .btn-create { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); border: none; }
        .nav-link.logout-link { color: #ffc107; font-weight: bold; }
        .nav-link.logout-link:hover { color: #ffffff; }
    </style>
</head>
<body class="bg-light">
    
<nav class="navbar navbar-expand-lg navbar-dark gradient-bg">
    <div class="container">
        <a class="navbar-brand animate__animated animate__fadeInLeft d-flex align-items-center" href="dashboard.php">
            <img src="assets/images/logoITG.png" alt="Logo ITG" style="height: 40px; margin-right: 10px;">
            <i class="fas fa-qrcode me-2"></i>
            <strong>Sistem Tiket QR Code</strong>
        </a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>
            <a class="nav-link active" href="buat_tiket.php"><i class="fas fa-plus-circle me-1"></i>Buat Tiket</a>
            <a class="nav-link" href="scan.php"><i class="fas fa-camera me-1"></i>Scan QR</a>
            <a class="nav-link" href="daftar_tiket.php"><i class="fas fa-list me-1"></i>Daftar Tiket</a>
            <a class="nav-link" href="laporan.php"><i class="fas fa-chart-line me-1"></i>Laporan</a>
            <a class="nav-link logout-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
        </div>
    </div>
</nav>
    
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg border-0 animate__animated animate__fadeInUp">
                <div class="card-header gradient-bg text-white text-center py-4">
                    <h2 class="mb-0"><i class="fas fa-plus-circle me-3"></i>Buat Tiket Baru</h2>
                </div>
                <div class="card-body p-4">
                    <?php if ($message): ?>
                    <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?> alert-dismissible fade show">
                        <strong><?php echo htmlspecialchars($message); ?></strong>
                        <?php if ($success): ?>
                        <div class="mt-2">
                            <a href="print_tiket.php?kode=<?php echo $kode_tiket_baru; ?>" class="btn btn-primary btn-sm" target="_blank"><i class="fas fa-print me-1"></i>Print</a>
                            <a href="dashboard.php" class="btn btn-outline-primary btn-sm"><i class="fas fa-home me-1"></i>Dashboard</a>
                        </div>
                        <?php endif; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST" id="formTiket">
                        <div class="row">
                            <div class="col-lg-6 mb-4">
                                <div class="card h-100 card-hover border-primary">
                                    <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="fas fa-car me-2"></i>Loket A - Parkir</h5></div>
                                    <div class="card-body"><?php foreach ($jenis_tiket_list as $jt): if ($jt['loket'] == 'A'): ?>
                                        <div class="form-check mb-3"><input class="form-check-input" type="radio" name="jenis_tiket_id" value="<?php echo $jt['id']; ?>" id="tiket_<?php echo $jt['id']; ?>" required data-loket="A"><label class="form-check-label w-100" for="tiket_<?php echo $jt['id']; ?>"><div class="d-flex justify-content-between"><strong><?php echo htmlspecialchars($jt['nama_jenis']); ?></strong> <span class="badge bg-success fs-6">Rp <?php echo number_format($jt['harga']); ?></span></div></label></div>
                                    <?php endif; endforeach; ?></div>
                                </div>
                            </div>
                            <div class="col-lg-6 mb-4">
                                <div class="card h-100 card-hover border-info">
                                    <div class="card-header bg-info text-white"><h5 class="mb-0"><i class="fas fa-mountain me-2"></i>Loket B - Wisata</h5></div>
                                    <div class="card-body"><?php foreach ($jenis_tiket_list as $jt): if ($jt['loket'] == 'B'): ?>
                                        <div class="form-check mb-3"><input class="form-check-input" type="radio" name="jenis_tiket_id" value="<?php echo $jt['id']; ?>" id="tiket_<?php echo $jt['id']; ?>" required data-loket="B"><label class="form-check-label w-100" for="tiket_<?php echo $jt['id']; ?>"><div class="d-flex justify-content-between"><strong><?php echo htmlspecialchars($jt['nama_jenis']); ?></strong> <span class="badge bg-success fs-6">Rp <?php echo number_format($jt['harga']); ?></span></div></label></div>
                                    <?php endif; endforeach; ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-2 card-hover">
                            <div class="card-header bg-secondary text-white"><h5 class="mb-0"><i class="fas fa-edit me-2"></i>Data & Opsi Tambahan</h5></div>
                            <div class="card-body">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" role="switch" id="warga_lokal" name="warga_lokal">
                                    <label class="form-check-label fw-bold text-primary" for="warga_lokal">Gratis untuk Warga Lokal</label>
                                </div><hr>
                                <div id="field-loket-a" style="display: none;">
                                    <label for="plat_nomor" class="form-label"><i class="fas fa-car me-1"></i>Plat Nomor Kendaraan</label>
                                    <input type="text" class="form-control" name="plat_nomor" placeholder="Contoh: Z 1234 ABC" style="text-transform: uppercase;">
                                </div>
                                <div id="fields-loket-b" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-4"><label class="form-label">Nama Pengunjung</label><input type="text" class="form-control" name="nama_pengunjung"></div>
                                        <div class="col-md-4"><label class="form-label">Jumlah Pengunjung</label><input type="number" class="form-control" name="jumlah_pengunjung" min="1" value="1"></div>
                                        <div class="col-md-4"><label class="form-label">Harga Manual (per Orang)</label><input type="number" class="form-control" name="harga_manual" min="0" placeholder="Kosongkan jika normal"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" name="buat_tiket" class="btn btn-create text-white btn-lg px-5"><i class="fas fa-qrcode me-2"></i>Buat Tiket</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
    
<footer class="text-center py-4 mt-4 bg-white border-top">
    <p class="mb-0 text-muted">&copy; <?php echo date('Y'); ?> Sistem Tiket Parkir & Wisata QR Code By : Kelompok 6 AKPL</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleFields() {
        const selectedTiket = document.querySelector('input[name="jenis_tiket_id"]:checked');
        const fieldLoketA = document.getElementById('field-loket-a');
        const fieldsLoketB = document.getElementById('fields-loket-b');
        
        if (selectedTiket) {
            const loket = selectedTiket.getAttribute('data-loket');
            if (loket === 'A') {
                fieldLoketA.style.display = 'block';
                fieldsLoketB.style.display = 'none';
            } else if (loket === 'B') {
                fieldLoketA.style.display = 'none';
                fieldsLoketB.style.display = 'block';
            }
        } else {
            fieldLoketA.style.display = 'none';
            fieldsLoketB.style.display = 'none';
        }
    }
    document.querySelectorAll('input[name="jenis_tiket_id"]').forEach(radio => {
        radio.addEventListener('change', toggleFields);
    });
    toggleFields(); 
</script>
</body>
</html>