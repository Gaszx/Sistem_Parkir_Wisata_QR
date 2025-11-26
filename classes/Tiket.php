<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/QRGenerator.php';

class Tiket {
    private $conn;
    private $table_name = "transaksi";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function generateKodeTiket($loket) {
        if (!in_array($loket, ['A', 'B'])) {
            throw new InvalidArgumentException("Loket harus A atau B");
        }

        $prefix = $loket . date('Ymd');
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE kode_tiket LIKE :prefix";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':prefix', $prefix . '%');
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $prefix . str_pad(($row['total'] ?? 0) + 1, 4, '0', STR_PAD_LEFT);
        } catch (PDOException $e) {
            error_log("Error generating ticket code: " . $e->getMessage());
            throw new Exception("Gagal membuat kode tiket");
        }
    }

    public function buatTiket($jenis_tiket_id, $petugas, $plat_nomor, $nama_pengunjung, $jumlah_pengunjung, $total_bayar, $is_warga_lokal, $harga_manual) {
        if (empty($petugas)) {
            throw new InvalidArgumentException("Nama petugas harus diisi");
        }

        $jenis_tiket = $this->getJenisTiketById($jenis_tiket_id);
        if (!$jenis_tiket) {
            throw new Exception("Jenis tiket tidak valid");
        }

        if ($jenis_tiket['loket'] == 'A' && empty($plat_nomor)) {
            throw new InvalidArgumentException("Plat nomor harus diisi untuk loket parkir");
        }

        if ($jenis_tiket['loket'] == 'B' && empty($nama_pengunjung)) {
            throw new InvalidArgumentException("Nama pengunjung harus diisi untuk loket wisata");
        }

        $kode_tiket = $this->generateKodeTiket($jenis_tiket['loket']);
        $qr_data = json_encode([
            'kode' => $kode_tiket,
            'loket' => $jenis_tiket['loket'],
            'timestamp' => time()
        ]);
        
        $qr_code = QRGenerator::generateQRCode($qr_data);
        
        $query = "INSERT INTO " . $this->table_name . " 
                    (kode_tiket, qr_code, jenis_tiket_id, loket, harga, petugas_masuk, plat_nomor, nama_pengunjung, jumlah_pengunjung, total_bayar, is_warga_lokal, harga_manual) 
                  VALUES 
                    (:kode_tiket, :qr_code, :jenis_tiket_id, :loket, :harga, :petugas_masuk, :plat_nomor, :nama_pengunjung, :jumlah_pengunjung, :total_bayar, :is_warga_lokal, :harga_manual)";
        
        try {
            $stmt = $this->conn->prepare($query);
            
            $params = [
                ':kode_tiket'       => $kode_tiket,
                ':qr_code'          => $qr_code,
                ':jenis_tiket_id'   => $jenis_tiket_id,
                ':loket'            => $jenis_tiket['loket'],
                ':harga'            => $jenis_tiket['harga'],
                ':petugas_masuk'    => $petugas,
                ':plat_nomor'       => $plat_nomor,
                ':nama_pengunjung'  => $nama_pengunjung,
                ':jumlah_pengunjung'=> ($jenis_tiket['loket'] == 'B') ? $jumlah_pengunjung : 1,
                ':total_bayar'      => $total_bayar,
                ':is_warga_lokal'   => (int)$is_warga_lokal,
                ':harga_manual'     => ($harga_manual > 0) ? $harga_manual : null
            ];

            if ($stmt->execute($params)) {
                $this->logScan($kode_tiket, 'masuk', $petugas, 'Tiket dibuat dan masuk');
                return $kode_tiket;
            }
            
            throw new Exception("Gagal menyimpan tiket ke database");
        } catch (PDOException $e) {
            error_log("Error creating ticket: " . $e->getMessage());
            throw new Exception("Gagal membuat tiket");
        }
    }

    public function scanKeluar($kode_tiket, $petugas = '') {
        try {
            $tiket = $this->getTiketByKode($kode_tiket);
            if (!$tiket || $tiket['status'] != 'aktif') {
                return [
                    'success' => false, 
                    'message' => 'Tiket tidak ditemukan atau sudah keluar'
                ];
            }

            $denda = 0;
            $total_bayar_akhir = $tiket['total_bayar']; 

            if ($tiket['loket'] == 'A' && $tiket['durasi_jam']) {
                $waktu_masuk = strtotime($tiket['tanggal_masuk']);
                $durasi_aktual = (time() - $waktu_masuk) / 3600; // in hours
                
                if ($durasi_aktual > $tiket['durasi_jam']) {
                    $jam_lebih = ceil($durasi_aktual - $tiket['durasi_jam']);
                    $denda = $jam_lebih * 2000; // Fine Rp 2000 per hour
                    $total_bayar_akhir += $denda;
                }
            }

            $query = "UPDATE " . $this->table_name . " 
                      SET tanggal_keluar = NOW(), status = 'keluar', 
                          petugas_keluar = :petugas, denda = :denda, total_bayar = :total_bayar 
                      WHERE kode_tiket = :kode_tiket";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':petugas', $petugas);
            $stmt->bindParam(':denda', $denda);
            $stmt->bindParam(':total_bayar', $total_bayar_akhir);
            $stmt->bindParam(':kode_tiket', $kode_tiket);
            
            if (!$stmt->execute()) {
                throw new Exception("Gagal update status tiket");
            }

            $keterangan = $denda > 0 ? 
                "Keluar dengan denda Rp " . number_format($denda, 0, ',', '.') : 
                "Keluar normal";
            $this->logScan($kode_tiket, 'keluar', $petugas, $keterangan);
            
            return [
                'success' => true, 
                'message' => 'Scan keluar berhasil',
                'tiket' => $this->getTiketByKode($kode_tiket),
            ];
            
        } catch (PDOException | Exception $e) {
            error_log("Error in scanKeluar: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Terjadi kesalahan sistem'
            ];
        }
    }

    public function getTiketByKode($kode_tiket) {
        $query = "SELECT t.*, jt.nama_jenis, jt.durasi_jam 
                  FROM " . $this->table_name . " t 
                  JOIN jenis_tiket jt ON t.jenis_tiket_id = jt.id 
                  WHERE t.kode_tiket = :kode_tiket 
                  LIMIT 1";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':kode_tiket', $kode_tiket);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? $data : null;
        } catch (PDOException $e) {
            error_log("Error getting ticket: " . $e->getMessage());
            return null;
        }
    }

    public function getAllTiket($tanggal_dari = null, $tanggal_sampai = null, $loket = null, $status = null) {
        $query = "SELECT t.*, jt.nama_jenis 
                  FROM " . $this->table_name . " t 
                  JOIN jenis_tiket jt ON t.jenis_tiket_id = jt.id 
                  WHERE 1=1";
        $params = [];
        if ($tanggal_dari && $tanggal_sampai) {
            $query .= " AND DATE(t.tanggal_masuk) BETWEEN :tanggal_dari AND :tanggal_sampai";
            $params[':tanggal_dari'] = $tanggal_dari;
            $params[':tanggal_sampai'] = $tanggal_sampai;
        }
        if ($loket) {
            $query .= " AND t.loket = :loket";
            $params[':loket'] = $loket;
        }
        if ($status) {
            $query .= " AND t.status = :status";
            $params[':status'] = $status;
        }
        $query .= " ORDER BY t.tanggal_masuk DESC";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting tickets: " . $e->getMessage());
            return [];
        }
    }

    public function getLaporanKeuangan($tanggal_dari = null, $tanggal_sampai = null) {
        // Query SQL yang telah disempurnakan
        $query = "SELECT 
                    t.loket, 
                    jt.nama_jenis, 
                    COUNT(t.id) as jumlah_transaksi,
                    SUM(t.jumlah_pengunjung) as jumlah_tiket,
                    SUM(t.total_bayar) as total_pendapatan,
                    -- PENAMBAHAN: Menghitung tiket warga lokal berdasarkan jumlah pengunjung --
                    SUM(CASE WHEN t.is_warga_lokal = 1 THEN t.jumlah_pengunjung ELSE 0 END) as jumlah_warga_lokal
                FROM " . $this->table_name . " t 
                JOIN jenis_tiket jt ON t.jenis_tiket_id = jt.id 
                WHERE 1=1";
        
        $params = [];
        if ($tanggal_dari && $tanggal_sampai) {
            $query .= " AND DATE(t.tanggal_masuk) BETWEEN :tanggal_dari AND :tanggal_sampai";
            $params[':tanggal_dari'] = $tanggal_dari;
            $params[':tanggal_sampai'] = $tanggal_sampai;
        }
        
        $query .= " GROUP BY t.loket, jt.nama_jenis ORDER BY t.loket, jt.nama_jenis";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting financial report: " . $e->getMessage());
            return [];
        }
    }

    public function getTotalPendapatan($tanggal_dari = null, $tanggal_sampai = null) {
        $query = "SELECT SUM(total_bayar) as total FROM " . $this->table_name . " WHERE 1=1";
        $params = [];
        if ($tanggal_dari && $tanggal_sampai) {
            $query .= " AND DATE(tanggal_masuk) BETWEEN :tanggal_dari AND :tanggal_sampai";
            $params[':tanggal_dari'] = $tanggal_dari;
            $params[':tanggal_sampai'] = $tanggal_sampai;
        }
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (float)($row['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error getting total income: " . $e->getMessage());
            return 0;
        }
    }
    
    public function getJenisTiket() {
        $query = "SELECT * FROM jenis_tiket ORDER BY loket, nama_jenis";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting ticket types: " . $e->getMessage());
            return [];
        }
    }
    public function getPendapatanHarian($tanggal_dari, $tanggal_sampai) {
        $query = "SELECT
                    DATE(tanggal_masuk) as tanggal,
                    SUM(total_bayar) as total
                  FROM " . $this->table_name . "
                  WHERE DATE(tanggal_masuk) BETWEEN :tanggal_dari AND :tanggal_sampai
                  GROUP BY DATE(tanggal_masuk)
                  ORDER BY DATE(tanggal_masuk) ASC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':tanggal_dari', $tanggal_dari);
            $stmt->bindParam(':tanggal_sampai', $tanggal_sampai);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting daily income: " . $e->getMessage());
            return [];
        }
    }
    
    private function getJenisTiketById($id) {
        $query = "SELECT * FROM jenis_tiket WHERE id = :id LIMIT 1";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? $data : null;
        } catch (PDOException $e) {
            error_log("Error getting ticket type: " . $e->getMessage());
            return null;
        }
    }
    
    public function getLogScan($kode_tiket = null) {
        $query = "SELECT ls.*, t.loket, jt.nama_jenis 
                  FROM log_scan ls 
                  JOIN transaksi t ON ls.kode_tiket = t.kode_tiket 
                  JOIN jenis_tiket jt ON t.jenis_tiket_id = jt.id";
        $params = [];
        if ($kode_tiket) {
            $query .= " WHERE ls.kode_tiket = :kode_tiket";
            $params[':kode_tiket'] = $kode_tiket;
        }
        $query .= " ORDER BY ls.waktu_scan DESC";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting scan logs: " . $e->getMessage());
            return [];
        }
    }
    
    public function logScan($kode_tiket, $jenis_scan, $petugas, $keterangan = '') {
        if (!in_array($jenis_scan, ['masuk', 'keluar'])) {
            return false;
        }

        $query = "INSERT INTO log_scan (kode_tiket, jenis_scan, petugas, keterangan) 
                  VALUES (:kode_tiket, :jenis_scan, :petugas, :keterangan)";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':kode_tiket', $kode_tiket);
            $stmt->bindParam(':jenis_scan', $jenis_scan);
            $stmt->bindParam(':petugas', $petugas);
            $stmt->bindParam(':keterangan', $keterangan);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error logging scan: " . $e->getMessage());
            return false;
        }
    }
}
?>