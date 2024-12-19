<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/session.php';
require_once '../config/database.php';

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

// Redirect jika belum login
if (!isset($_SESSION['user'])) {
    $_SESSION['redirect_after_login'] = '/pages/checkout.php';
    header('Location: /pages/login.php');
    exit;
}

// Redirect jika keranjang kosong
if (empty($_SESSION['cart'])) {
    header('Location: /pages/cart.php');
    exit;
}

// Get user data
try {
    $userData = $database->users->findOne([
        '_id' => new ObjectId($_SESSION['user']['id'])
    ]);
    if (!$userData) {
        header('Location: /pages/login.php');
        exit;
    }
} catch (Exception $e) {
    header('Location: /pages/cart.php?error=user_not_found');
    exit;
}

// Hitung total
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validasi input
        $errors = [];
        if (empty($_POST['address'])) $errors[] = "Alamat harus diisi";
        if (empty($_POST['payment_method'])) $errors[] = "Metode pembayaran harus dipilih";

        if (empty($errors)) {
            // Buat order
            $order = [
                'user_id' => new ObjectId($_SESSION['user']['id']),
                'items' => array_values($_SESSION['cart']),
                'total' => $total,
                'shipping_info' => [
                    'name' => $_POST['name'],
                    'email' => $_POST['email'],
                    'phone' => $_POST['phone'],
                    'address' => $_POST['address']
                ],
                'payment_method' => $_POST['payment_method'],
                'order_status' => 'pending',
                'created_at' => new UTCDateTime(),
                'updated_at' => new UTCDateTime(),
                'history' => [
                    [
                        'status' => 'pending',
                        'timestamp' => new UTCDateTime(),
                        'notes' => 'Order dibuat'
                    ]
                ]
            ];

            $result = $database->orders->insertOne($order);
            
            if ($result->getInsertedCount()) {
                // Clear cart
                unset($_SESSION['cart']);
                
                // Redirect to success page
                header('Location: /pages/order-success.php?id=' . $result->getInsertedId());
                exit;
            } else {
                $errors[] = "Gagal membuat pesanan";
            }
        }
    } catch (Exception $e) {
        $errors[] = "Error: " . $e->getMessage();
    }
}

include '../layouts/header.php';
?>

<div class="container mt-4">
    <h2 class="mb-4">Checkout</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <!-- User Info Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Informasi Pemesan</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Nama:</strong></p>
                            <p><?php echo $userData->name; ?></p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Email:</strong></p>
                            <p><?php echo $userData->email; ?></p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Telepon:</strong></p>
                            <p><?php echo $userData->phone; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping Form -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Informasi Pengiriman</h5>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Alamat Lengkap</label>
                            <textarea name="address" class="form-control" rows="3" required 
                                    placeholder="Masukkan alamat lengkap pengiriman..."><?php 
                                echo isset($_POST['address']) ? $_POST['address'] : ''; 
                            ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Metode Pembayaran</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="">Pilih Metode Pembayaran</option>
                                <option value="bank_transfer" <?php echo isset($_POST['payment_method']) && $_POST['payment_method'] === 'bank_transfer' ? 'selected' : ''; ?>>Transfer Bank</option>
                            </select>
                        </div>

                        <div id="bank_details" class="mb-3 <?php echo (!isset($_POST['payment_method']) || $_POST['payment_method'] !== 'bank_transfer') ? 'd-none' : ''; ?>">
                            <div class="alert alert-info">
                                <h6>Informasi Rekening:</h6>
                                <p class="mb-0">
                                    Bank BCA<br>
                                    No. Rekening: 1234567890<br>
                                    Atas Nama: Galaxy Vape Store
                                </p>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Buat Pesanan</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Ringkasan Pesanan</h5>
                    
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span><?php echo $item['name']; ?> (<?php echo $item['quantity']; ?>x)</span>
                        <span>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></span>
                    </div>
                    <?php endforeach; ?>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <strong>Total</strong>
                        <strong>Rp <?php echo number_format($total, 0, ',', '.'); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelector('select[name="payment_method"]').addEventListener('change', function() {
    const bankDetails = document.getElementById('bank_details');
    if (this.value === 'bank_transfer') {
        bankDetails.classList.remove('d-none');
    } else {
        bankDetails.classList.add('d-none');
    }
});
</script>

<?php include '../layouts/footer.php'; ?> 