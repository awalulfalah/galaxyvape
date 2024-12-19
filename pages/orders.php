<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../config/order_status.php';

use MongoDB\BSON\ObjectId;
use MongoDB\Driver\Exception\Exception as MongoDBException;

// Redirect jika belum login
if (!isset($_SESSION['user'])) {
    header('Location: /pages/login.php');
    exit;
}

$errors = [];
$orders = [];

// Ambil daftar pesanan user
try {
    $userId = $_SESSION['user']['id'];
    if (!preg_match('/^[0-9a-fA-F]{24}$/', $userId)) {
        throw new MongoDBException("Invalid user ID format");
    }

    // Pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10;
    $skip = ($page - 1) * $limit;

    // Filter status jika ada
    $filter = ['user_id' => new ObjectId($userId)];
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $filter['order_status'] = $_GET['status'];
    }

    // Hitung total orders untuk pagination
    $totalOrders = $database->orders->countDocuments($filter);
    $totalPages = ceil($totalOrders / $limit);

    // Get orders dengan sort berdasarkan created_at descending
    $orders = $database->orders->find(
        $filter,
        [
            'sort' => ['created_at' => -1],
            'skip' => $skip,
            'limit' => $limit
        ]
    )->toArray();

} catch (MongoDBException $e) {
    $errors[] = "Error mengambil data pesanan: " . $e->getMessage();
}

include '../layouts/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Menu Profil</h5>
                    <div class="list-group">
                        <a href="/pages/profile.php" class="list-group-item list-group-item-action">
                            Informasi Profil
                        </a>
                        <a href="/pages/orders.php" class="list-group-item list-group-item-action active">
                            Riwayat Pesanan
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filter Status -->
            <div class="card mt-3">
                <div class="card-body">
                    <h5 class="card-title">Filter Status</h5>
                    <form action="" method="GET">
                        <select name="status" class="form-select mb-2">
                            <option value="">Semua Status</option>
                            <option value="pending" <?php echo isset($_GET['status']) && $_GET['status'] === 'pending' ? 'selected' : ''; ?>>
                                Menunggu Pembayaran
                            </option>
                            <option value="paid" <?php echo isset($_GET['status']) && $_GET['status'] === 'paid' ? 'selected' : ''; ?>>
                                Sudah Dibayar
                            </option>
                            <option value="processing" <?php echo isset($_GET['status']) && $_GET['status'] === 'processing' ? 'selected' : ''; ?>>
                                Diproses
                            </option>
                            <option value="shipped" <?php echo isset($_GET['status']) && $_GET['status'] === 'shipped' ? 'selected' : ''; ?>>
                                Dikirim
                            </option>
                            <option value="completed" <?php echo isset($_GET['status']) && $_GET['status'] === 'completed' ? 'selected' : ''; ?>>
                                Selesai
                            </option>
                            <option value="cancelled" <?php echo isset($_GET['status']) && $_GET['status'] === 'cancelled' ? 'selected' : ''; ?>>
                                Dibatalkan
                            </option>
                        </select>
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Orders List -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title mb-4">Riwayat Pesanan</h2>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($orders)): ?>
                        <div class="alert alert-info">
                            Belum ada pesanan.
                            <?php if (isset($_GET['status'])): ?>
                                <br>
                                <a href="?">Lihat semua pesanan</a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h5 class="card-title mb-1">Order #<?php echo $order->_id; ?></h5>
                                            <small class="text-muted">
                                                <?php echo $order->created_at->toDateTime()->format('d F Y H:i'); ?>
                                            </small>
                                        </div>
                                        <td>
                                            <span class="badge bg-<?php echo STATUS_BADGES[$order->order_status]; ?>">
                                                <?php echo ORDER_STATUSES[$order->order_status]; ?>
                                            </span>
                                        </td>
                                    </div>

                                    <!-- Order Items -->
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Produk</th>
                                                    <th class="text-end">Harga</th>
                                                    <th class="text-center">Jumlah</th>
                                                    <th class="text-end">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($order->items as $item): ?>
                                                    <tr>
                                                        <td><?php echo $item['name']; ?></td>
                                                        <td class="text-end">
                                                            Rp <?php echo number_format($item['price'], 0, ',', '.'); ?>
                                                        </td>
                                                        <td class="text-center"><?php echo $item['quantity']; ?></td>
                                                        <td class="text-end">
                                                            Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                                    <td class="text-end">
                                                        <strong>Rp <?php echo number_format($order->total, 0, ',', '.'); ?></strong>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <!-- Order Actions -->
                                    <div class="mt-3">
                                        <?php if ($order->order_status === 'pending'): ?>
                                            <form action="/pages/payment-confirmation.php" method="POST" class="d-inline">
                                                <input type="hidden" name="order_id" value="<?php echo $order->_id; ?>">
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-money-bill me-1"></i>Upload Bukti Pembayaran
                                                </button>
                                            </form>
                                            <div class="mt-2 small">
                                                <strong>Informasi Rekening:</strong><br>
                                                Bank BCA<br>
                                                No. Rekening: 1234567890<br>
                                                A/N: Galaxy Vape Store<br>
                                                Nominal: Rp <?php echo number_format($order->total, 0, ',', '.'); ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <a href="/pages/order-detail.php?id=<?php echo $order->_id; ?>" 
                                           class="btn btn-outline-primary btn-sm <?php echo $order->order_status === 'pending' ? 'mt-2' : ''; ?>">
                                            <i class="fas fa-eye me-1"></i>Detail Pesanan
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Page navigation" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include '../layouts/footer.php';
?> 