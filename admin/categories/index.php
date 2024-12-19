<?php
require_once '../layouts/header.php';

// Get categories with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$skip = ($page - 1) * $limit;

try {
    // Get categories with product count
    $pipeline = [
        [
            '$lookup' => [
                'from' => 'products',
                'localField' => 'name',
                'foreignField' => 'category',
                'as' => 'products'
            ]
        ],
        [
            '$project' => [
                'name' => 1,
                'description' => 1,
                'active' => 1,
                'created_at' => 1,
                'productCount' => ['$size' => '$products'],
                'minPrice' => ['$min' => '$products.price'],
                'maxPrice' => ['$max' => '$products.price']
            ]
        ],
        ['$sort' => ['name' => 1]],
        ['$skip' => $skip],
        ['$limit' => $limit]
    ];

    $categories = $database->categories->aggregate($pipeline)->toArray();

    // Get total categories for pagination
    $totalCategories = $database->categories->countDocuments();
    $totalPages = ceil($totalCategories / $limit);

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Manajemen Kategori</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="fas fa-plus me-2"></i>Tambah Kategori
        </button>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php
            switch ($_GET['success']) {
                case 'created':
                    echo 'Kategori berhasil ditambahkan.';
                    break;
                case 'updated':
                    echo 'Kategori berhasil diperbarui.';
                    break;
                case 'deleted':
                    echo 'Kategori berhasil dihapus.';
                    break;
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Jumlah Produk</th>
                            <th>Range Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo ucfirst($category->name); ?></td>
                                <td><?php echo $category->productCount; ?></td>
                                <td>
                                    <?php if ($category->productCount > 0): ?>
                                        Rp <?php echo number_format($category->minPrice, 0, ',', '.'); ?> - 
                                        Rp <?php echo number_format($category->maxPrice, 0, ',', '.'); ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="editCategory('<?php echo $category->name; ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="confirmDelete('<?php echo $category->name; ?>', <?php echo $category->productCount; ?>)">
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

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="create.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="update.php" method="POST">
                <input type="hidden" name="old_name" id="editCategoryOldName">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" name="name" id="editCategoryName" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCategory(categoryName) {
    document.getElementById('editCategoryOldName').value = categoryName;
    document.getElementById('editCategoryName').value = categoryName;
    new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
}

function confirmDelete(categoryName, productCount) {
    const message = productCount > 0 
        ? `Kategori ini memiliki ${productCount} produk. Menghapus kategori akan mengubah kategori produk menjadi 'Uncategorized'. Lanjutkan?`
        : 'Apakah Anda yakin ingin menghapus kategori ini?';
    
    if (confirm(message)) {
        window.location.href = `delete.php?name=${encodeURIComponent(categoryName)}`;
    }
}
</script>

<?php require_once '../layouts/footer.php'; ?> 