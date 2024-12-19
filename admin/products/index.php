<?php
require_once '../layouts/header.php';

// Get products with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$skip = ($page - 1) * $limit;

try {
    // Get total products
    $totalProducts = $database->products->countDocuments();
    $totalPages = ceil($totalProducts / $limit);

    // Get products
    $products = $database->products->find(
        [],
        [
            'sort' => ['created_at' => -1],
            'skip' => $skip,
            'limit' => $limit
        ]
    )->toArray();
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Manajemen Produk</h1>
        <a href="add.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Produk
        </a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Gambar</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo BASE_URL . $product->image; ?>" alt="<?php echo $product->name; ?>"
                                         class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                </td>
                                <td><?php echo $product->name; ?></td>
                                <td><?php echo ucfirst($product->category); ?></td>
                                <td>Rp <?php echo number_format($product->price, 0, ',', '.'); ?></td>
                                <td><?php echo $product->stock; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $product->stock > 0 ? 'success' : 'danger'; ?>">
                                        <?php echo $product->stock > 0 ? 'Tersedia' : 'Habis'; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="edit.php?id=<?php echo $product->_id; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="confirmDelete('<?php echo $product->_id; ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function confirmDelete(productId) {
    if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
        window.location.href = `delete.php?id=${productId}`;
    }
}
</script>

<?php require_once '../layouts/footer.php'; ?> 