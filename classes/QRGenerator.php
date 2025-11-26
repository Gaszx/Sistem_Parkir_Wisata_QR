<?php
require_once __DIR__ . '/../phpqrcode/qrlib.php';

class QRGenerator {
    public static function generateQRCode($data) {
        try {
            ob_start(); // Mulai output buffering untuk menangkap output gambar
            QRcode::png($data, null, QR_ECLEVEL_H, 10, 2);
            $imageData = ob_get_contents(); // data gambar
            ob_end_clean(); 

            // Konversi data gambar mentah ke format Data URI
            return 'data:image/png;base64,' . base64_encode($imageData);

        } catch (Exception $e) {
            error_log('QR Code generation failed: ' . $e->getMessage());
            return null;
        }
    }
}
?>