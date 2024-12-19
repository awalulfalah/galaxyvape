<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/session.php';
require_once '../config/database.php';

use MongoDB\BSON\ObjectId;

// Get product ID from URL
$productId = isset($_GET['id']) ? $_GET['id'] : null;

if (!$productId) {
    die("Product ID not provided");
}

try {
    // Validasi format ObjectId
    if (!preg_match('/^[0-9a-fA-F]{24}$/', $productId)) {
        die("Invalid product ID format");
    }
    
    $product = $database->products->findOne([
        '_id' => new MongoDB\BSON\ObjectId($productId)
    ]);
    
    if (!$product) {
        die("Product not found");
    }
} catch (Exception $e) {
    die("Error fetching product: " . $e->getMessage());
}

include '../layouts/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <!-- Product Image -->
        <div class="col-md-6">
            <img src="<?php echo $product->image; ?>" class="img-fluid rounded" alt="<?php echo $product->name; ?>">
        </div>
        
        <!-- Product Details -->
        <div class="col-md-6">
            <h1><?php echo $product->name; ?></h1>
            <h3 class="text-primary mb-4">Rp <?php echo number_format($product->price, 0, ',', '.'); ?></h3>
            
            <div class="mb-4">
                <h5>Deskripsi</h5>
                <p><?php echo $product->description; ?></p>
            </div>
            
            <?php if (isset($product->specifications)): ?>
            <div class="mb-4">
                <h5>Spesifikasi</h5>
                <ul>
                    <?php foreach ($product->specifications as $key => $value): ?>
                    <li><strong><?php echo ucfirst($key); ?>:</strong> <?php echo $value; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <div class="mb-4">
                <h5>Stok: <span class="text-<?php echo $product->stock > 0 ? 'success' : 'danger'; ?>">
                    <?php echo $product->stock > 0 ? $product->stock : 'Habis'; ?>
                </span></h5>
            </div>
            
            <?php if ($product->stock > 0): ?>
            <form action="/pages/cart.php" method="POST" class="mb-4">
                <input type="hidden" name="product_id" value="<?php echo $product->_id; ?>">
                <div class="input-group mb-3">
                    <label class="input-group-text">Jumlah</label>
                    <input type="number" name="quantity" class="form-control" value="1" min="1" max="<?php echo $product->stock; ?>">
                    <button type="submit" class="btn btn-primary">Tambah ke Keranjang</button>
                </div>
            </form>
            <?php else: ?>
            <button class="btn btn-secondary" disabled>Stok Habis</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?> 