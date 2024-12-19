<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/session.php';
require_once '../config/database.php';

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle POST request untuk menambah item ke keranjang
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'] ?? '';
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    // Cek stok produk
    $product = $database->products->findOne([
        '_id' => new MongoDB\BSON\ObjectId($productId)
    ]);
    
    if ($product && $product->stock >= $quantity) {
        // Tambahkan ke keranjang dalam session
        $_SESSION['cart'][$productId] = [
            'name' => $product->name,
            'price' => $product->price,
            'quantity' => $quantity,
            'image' => $product->image
        ];
    }
}

// Handle GET request untuk menghapus item
if (isset($_GET['remove'])) {
    $productId = $_GET['remove'];
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
    header('Location: /pages/cart.php');
    exit;
}

// Hitung total
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}

include '../layouts/header.php';
?>

<div class="container mt-4">
    <h2>Keranjang Belanja</h2>
    
    <?php if (isset($_GET['status'])): ?>
        <?php if ($_GET['status'] === 'success'): ?>
            <div class="alert alert-success">Produk berhasil ditambahkan ke keranjang!</div>
        <?php elseif ($_GET['status'] === 'stock_error'): ?>
            <div class="alert alert-danger">Stok tidak mencukupi!</div>
        <?php elseif ($_GET['status'] === 'error'): ?>
            <div class="alert alert-danger">Terjadi kesalahan!</div>
        <?php endif; ?>
    <?php endif; ?>
    
    <?php if (empty($_SESSION['cart'])): ?>
        <div class="alert alert-info">Keranjang belanja kosong</div>
        <a href="/pages/products.php" class="btn btn-primary">Lihat Produk</a>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['cart'] as $productId => $item): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" style="width: 50px; height: 50px; object-fit: cover;" class="me-2">
                                    <?php echo $item['name']; ?>
                                </div>
                            </td>
                            <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                            <td>
                                <form action="/pages/update-cart.php" method="POST" class="d-flex align-items-center">
                                    <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                           min="1" class="form-control" style="width: 70px;">
                                    <button type="submit" class="btn btn-sm btn-outline-primary ms-2">Update</button>
                                </form>
                            </td>
                            <td>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                            <td>
                                <a href="/pages/cart.php?remove=<?php echo $productId; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Yakin ingin menghapus produk ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                        <td><strong>Rp <?php echo number_format($total, 0, ',', '.'); ?></strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="d-flex justify-content-between mt-4">
            <a href="/pages/products.php" class="btn btn-outline-primary">Lanjut Belanja</a>
            <a href="/pages/checkout.php" class="btn btn-primary">Checkout</a>
        </div>
    <?php endif; ?>
</div>

<?php include '../layouts/footer.php'; ?> 