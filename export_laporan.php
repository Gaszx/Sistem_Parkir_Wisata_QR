<?php
// Validasi keamanan dan memuat class
require_once 'auth_check.php';
require_once 'classes/Tiket.php';

$tiket = new Tiket();

// Mengambil tanggal dari parameter URL
$tanggal_dari = $_GET['tanggal_dari'] ?? date('Y-m-d');
$tanggal_sampai = $_GET['tanggal_sampai'] ?? date('Y-m-d');

$laporan = $tiket->getLaporanKeuangan($tanggal_dari, $tanggal_sampai);

// Menyiapkan nama file untuk diunduh
$filename = "laporan_keuangan_" . $tanggal_dari . "_sampai_" . $tanggal_sampai . ".csv";

// Menyiapkan header HTTP agar browser mengunduh file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

// Membuka output stream PHP untuk menulis file CSV
$output = fopen('php://output', 'w');

// Menulis Judul dan Periode Laporan
fputcsv($output, ['Laporan Keuangan Sistem Tiket QR Code']);
fputcsv($output, ['Periode', date('d M Y', strtotime($tanggal_dari)) . ' - ' . date('d M Y', strtotime($tanggal_sampai))]);
fputcsv($output, []); 

// Menulis baris header untuk tabel CSV
fputcsv($output, [
    'Loket', 
    'Jenis Tiket', 
    'Jumlah Pengunjung', 
    'Warga Lokal (Gratis)', // PENAMBAHAN: Kolom header baru
    'Total Pendapatan'
]);

// Menulis data dan menghitung total
if (!empty($laporan)) {
    $grand_total_tiket = 0;
    $grand_total_warga_lokal = 0; // PENAMBAHAN: Variabel total baru
    $grand_total_pendapatan = 0;

    foreach ($laporan as $item) {
        fputcsv($output, [
            $item['loket'],
            $item['nama_jenis'],
            $item['jumlah_tiket'],
            $item['jumlah_warga_lokal'], // PENAMBAHAN: Data warga lokal per baris
            $item['total_pendapatan']
        ]);

        // Akumulasi nilai untuk Grand Total
        $grand_total_tiket += $item['jumlah_tiket'];
        $grand_total_warga_lokal += $item['jumlah_warga_lokal']; // PENAMBAHAN: Akumulasi total warga lokal
        $grand_total_pendapatan += $item['total_pendapatan'];
    }

    // Menulis baris Grand Total
    fputcsv($output, []); 
    fputcsv($output, [
        'GRAND TOTAL',
        '', // Kolom 'Jenis Tiket' dikosongkan
        $grand_total_tiket,
        $grand_total_warga_lokal, 
        $grand_total_pendapatan
    ]);

} else {
    fputcsv($output, []);
    fputcsv($output, ['Tidak ada data ditemukan untuk periode yang dipilih.']);
}
fclose($output);
exit;

?>