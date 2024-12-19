<?php
require_once '../layouts/header.php';
require_once '../includes/upload_helper.php';

use MongoDB\BSON\ObjectId;

// Get product data
try {
    if (!isset($_GET['id'])) {
        header('Location: index.php?error=invalid_request');
        exit;
    }

    $product = $database->products->findOne(['_id' => new ObjectId($_GET['id'])]);
    if (!$product) {
        header('Location: index.php?error=product_not_found');
        exit;
    }

    // Get categories
    $categories = $database->products->distinct('category');
} catch (Exception $e) {
    header('Location: index.php?error=' . urlencode($e->getMessage()));
    exit;
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Edit Produk</h1>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <?php if (isset($_SESSION['errors'])): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="update.php" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $product->_id; ?>">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Nama Produk</label>
                            <input type="text" name="name" class="form-control" required
                                   value="<?php echo $product->name; ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="4" required><?php echo $product->description; ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Harga</label>
                                    <input type="number" name="price" class="form-control" required
                                           value="<?php echo $product->price; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Stok</label>
                                    <input type="number" name="stock" class="form-control" required
                                           value="<?php echo $product->stock; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="category" class="form-select" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category; ?>" 
                                            <?php echo $product->category === $category ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($category); ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="new">Kategori Baru</option>
                            </select>
                        </div>

                        <div id="newCategoryInput" class="mb-3" style="display: none;">
                            <label class="form-label">Nama Kategori Baru</label>
                            <input type="text" name="new_category" class="form-control">
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="featured" class="form-check-input" id="featured"
                                       <?php echo isset($product->featured) && $product->featured ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="featured">Tampilkan di Beranda</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Gambar Produk</label>
                            <div class="mb-2">
                                <img src="<?php echo $product->image; ?>" alt="Current Image" class="img-thumbnail" style="max-height: 200px;">
                            </div>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">Kosongkan jika tidak ingin mengubah gambar</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Spesifikasi</label>
                            <div id="specifications">
                                <?php if (isset($product->specifications)): ?>
                                    <?php foreach ($product->specifications as $key => $value): ?>
                                        <div class="input-group mb-2">
                                            <input type="text" name="spec_keys[]" class="form-control" 
                                                   value="<?php echo $key; ?>" placeholder="Nama">
                                            <input type="text" name="spec_values[]" class="form-control" 
                                                   value="<?php echo $value; ?>" placeholder="Nilai">
                                            <button type="button" class="btn btn-danger" onclick="removeSpec(this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="addSpec()">
                                <i class="fas fa-plus me-2"></i>Tambah Spesifikasi
                            </button>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function addSpec() {
    const container = document.getElementById('specifications');
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <input type="text" name="spec_keys[]" class="form-control" placeholder="Nama">
        <input type="text" name="spec_values[]" class="form-control" placeholder="Nilai">
        <button type="button" class="btn btn-danger" onclick="removeSpec(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(div);
}

function removeSpec(button) {
    button.parentElement.remove();
}

document.querySelector('select[name="category"]').addEventListener('change', function() {
    const newCategoryInput = document.getElementById('newCategoryInput');
    if (this.value === 'new') {
        newCategoryInput.style.display = 'block';
        newCategoryInput.querySelector('input').required = true;
    } else {
        newCategoryInput.style.display = 'none';
        newCategoryInput.querySelector('input').required = false;
    }
});
</script>

<?php require_once '../layouts/footer.php'; ?> 