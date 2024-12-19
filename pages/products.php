<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';

// Filter parameters
$category = isset($_GET['category']) ? $_GET['category'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : null;
$minPrice = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (int)$_GET['min_price'] : null;
$maxPrice = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (int)$_GET['max_price'] : null;

// Get active categories for filter
try {
    $categories = $database->categories->find(
        ['active' => true],
        [
            'sort' => ['name' => 1],
            'projection' => ['name' => 1]
        ]
    )->toArray();
} catch (Exception $e) {
    die("Error fetching categories: " . $e->getMessage());
}

// Build query
$query = [];

// Filter by category
if ($category && $category !== '') {
    $query['category'] = $category;
}

// Filter by search term
if ($search && $search !== '') {
    $query['name'] = ['$regex' => new MongoDB\BSON\Regex($search, 'i')];
}

// Filter by price range
if ($minPrice !== null || $maxPrice !== null) {
    $query['price'] = [];
    if ($minPrice !== null) {
        $query['price']['$gte'] = $minPrice;
    }
    if ($maxPrice !== null) {
        $query['price']['$lte'] = $maxPrice;
    }
}

// Debug query
// echo "<pre>Query: " . json_encode($query, JSON_PRETTY_PRINT) . "</pre>";

// Get products with sort and pagination
try {
    $options = [
        'sort' => ['created_at' => -1]
    ];
    
    $products = $database->products->find($query, $options)->toArray();
    
    // Debug results
    // echo "<pre>Found " . count($products) . " products</pre>";
} catch (Exception $e) {
    die("Error fetching products: " . $e->getMessage());
}

include '../layouts/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <!-- Filter Section -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Filter Produk</h5>
                    <form action="" method="GET">
                        <div class="mb-3">
                            <label class="form-label">Cari Produk</label>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Nama produk..." value="<?php echo $search; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="category" class="form-select">
                                <option value="">Semua Kategori</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat->name; ?>" 
                                        <?php echo $category === $cat->name ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($cat->name); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Rentang Harga</label>
                            <div class="input-group">
                                <input type="number" name="min_price" class="form-control" 
                                       placeholder="Min" value="<?php echo $minPrice; ?>">
                                <input type="number" name="max_price" class="form-control" 
                                       placeholder="Max" value="<?php echo $maxPrice; ?>">
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <?php if ($category || $search || $minPrice || $maxPrice): ?>
                                <a href="?" class="btn btn-outline-secondary mt-2">Reset Filter</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="col-md-9">
            <?php if (!empty($category)): ?>
                <h4 class="mb-4">Kategori: <?php echo ucfirst($category); ?></h4>
            <?php endif; ?>

            <div class="row">
                <?php if (empty($products)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        Tidak ada produk yang ditemukan.
                        <?php if ($category || $search || $minPrice || $maxPrice): ?>
                            <br>
                            <small>Coba ubah filter atau <a href="?">lihat semua produk</a></small>
                        <?php endif; ?>
                    </div>
                </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="<?php echo $product->image; ?>" class="card-img-top" 
                                 alt="<?php echo $product->name; ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $product->name; ?></h5>
                                <p class="card-text">
                                    Rp <?php echo number_format($product->price, 0, ',', '.'); ?>
                                </p>
                                <a href="/pages/product-detail.php?id=<?php echo $product->_id; ?>" 
                                   class="btn btn-primary w-100">Lihat Detail</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?> 