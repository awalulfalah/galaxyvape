<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/session.php';
require_once '../config/database.php';

use MongoDB\BSON\ObjectId;

// Get order ID from URL
$orderId = isset($_GET['id']) ? $_GET['id'] : null;

if (!$orderId) {
    header('Location: /');
    exit;
}

try {
    $order = $database->orders->findOne(['_id' => new ObjectId($orderId)]);
    if (!$order) {
        header('Location: /');
        exit;
    }
} catch (Exception $e) {
    header('Location: /');
    exit;
}

include '../layouts/header.php';
?>

<div class="container mt-4">
    <div class="text-center">
        <i class="fas fa-check-circle text-success" style="font-size: 64px;"></i>
        <h2 class="mt-3">Terima Kasih!</h2>
        <p class="lead">Pesanan Anda telah berhasil dibuat.</p>
        <p>Nomor Order: <strong><?php echo $order->_id; ?></strong></p>
        
        <div class="card mt-4 mb-4 mx-auto" style="max-width: 500px;">
            <div class="card-body">
                <h5 class="card-title">Instruksi Pembayaran</h5>
                <p>Silakan transfer sejumlah:</p>
                <h3 class="text-primary">Rp <?php echo number_format($order->total, 0, ',', '.'); ?></h3>
                <p>ke rekening:</p>
                <p><strong>Bank BCA</strong><br>
                No. Rekening: 1234567890<br>
                Atas Nama: Galaxy Vape Store</p>
            </div>
        </div>
        
        <a href="/" class="btn btn-primary">Kembali ke Beranda</a>
    </div>
</div>

<?php include '../layouts/footer.php'; ?> 