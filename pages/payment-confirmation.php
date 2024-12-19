<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../config/order_status.php';
require_once '../admin/includes/upload_helper.php';

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

// Redirect jika belum login
if (!isset($_SESSION['user'])) {
    header('Location: /pages/login.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $orderId = $_POST['order_id'] ?? '';
        
        // Validasi order ID
        if (!preg_match('/^[0-9a-fA-F]{24}$/', $orderId)) {
            throw new Exception("ID Pesanan tidak valid");
        }

        // Cek pesanan
        $order = $database->orders->findOne([
            '_id' => new ObjectId($orderId),
            'user_id' => new ObjectId($_SESSION['user']['id'])
        ]);

        if (!$order) {
            throw new Exception("Pesanan tidak ditemukan");
        }

        // Validasi status pesanan
        if ($order->order_status !== 'pending') {
            throw new Exception("Bukti pembayaran hanya dapat diupload untuk pesanan dengan status menunggu pembayaran");
        }

        // Upload bukti pembayaran
        if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] === UPLOAD_ERR_NO_FILE) {
            throw new Exception("Bukti pembayaran harus diupload");
        }

        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/payments/';
        $uploadedFile = uploadImage($_FILES['payment_proof'], $uploadDir);
        $paymentProofUrl = '/uploads/payments/' . $uploadedFile;

        // Update status pesanan
        $result = $database->orders->updateOne(
            ['_id' => new ObjectId($orderId)],
            [
                '$set' => [
                    'order_status' => 'waiting_confirmation',
                    'payment_proof' => $paymentProofUrl,
                    'updated_at' => new UTCDateTime()
                ],
                '$push' => [
                    'status_history' => [
                        'status' => 'waiting_confirmation',
                        'notes' => 'Bukti pembayaran telah diupload',
                        'timestamp' => new UTCDateTime(),
                        'updated_by' => $_SESSION['user']['name']
                    ]
                ]
            ]
        );

        if ($result->getModifiedCount()) {
            $success = true;
        } else {
            throw new Exception("Gagal mengupdate status pesanan");
        }

    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

include '../layouts/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Upload Bukti Pembayaran</h5>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            Bukti pembayaran berhasil diupload. Status pesanan telah diubah menjadi menunggu konfirmasi.
                            <br>
                            <a href="/pages/orders.php" class="btn btn-primary mt-3">
                                Kembali ke Daftar Pesanan
                            </a>
                        </div>
                    <?php else: ?>
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form action="" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="order_id" value="<?php echo $_GET['id'] ?? ''; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Bukti Pembayaran</label>
                                <input type="file" name="payment_proof" class="form-control" accept="image/*" required>
                                <small class="text-muted">Format: JPG/PNG/GIF/WEBP, Maksimal 5MB</small>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload me-2"></i>Upload Bukti Pembayaran
                                </button>
                                <a href="/pages/orders.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?> 