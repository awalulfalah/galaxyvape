<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

// Check if MongoDB connection is working
try {
    $featuredProducts = $database->products->find(['featured' => true])->toArray();
} catch (Exception $e) {
    die("Error fetching products: " . $e->getMessage());
}

include 'layouts/header.php';
?>

<div class="container mt-4">
    <!-- Hero Section -->
    <div class="hero-section text-center py-5 bg-dark text-white rounded">
        <h1>Welcome to Galaxy Vape</h1>
        <p class="lead">Discover Premium Vaping Experience</p>
    </div>

    <!-- Featured Products -->
    <section class="featured-products mt-5">
        <h2 class="text-center mb-4">Produk Unggulan</h2>
        <div class="row">
            <?php foreach ($featuredProducts as $product): ?>
            <div class="col-md-3 mb-4">
                <div class="card">
                    <img src="<?php echo $product->image; ?>" class="card-img-top" alt="<?php echo $product->name; ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $product->name; ?></h5>
                        <p class="card-text">Rp <?php echo number_format($product->price, 0, ',', '.'); ?></p>
                        <a href="/pages/product-detail.php?id=<?php echo $product->_id; ?>" class="btn btn-primary">Lihat Detail</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories mt-5">
        <h2 class="text-center mb-4">Kategori Produk</h2>
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Pod Systems</h5>
                        <a href="/pages/products.php?category=pod" class="btn btn-outline-primary">Lihat Semua</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Mod Devices</h5>
                        <a href="/pages/products.php?category=mod" class="btn btn-outline-primary">Lihat Semua</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">E-Liquid</h5>
                        <a href="/pages/products.php?category=liquid" class="btn btn-outline-primary">Lihat Semua</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Accessories</h5>
                        <a href="/pages/products.php?category=accessories" class="btn btn-outline-primary">Lihat Semua</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'layouts/footer.php'; ?> 