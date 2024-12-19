<?php
require_once '../layouts/header.php';

// Get categories
try {
    // Ambil kategori dari collection categories yang aktif
    $categories = $database->categories->find(
        ['active' => true],
        ['sort' => ['name' => 1]]
    )->toArray();
} catch (Exception $e) {
    $errors[] = $e->getMessage();
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Tambah Produk</h1>
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
            <form method="POST" action="create.php" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Nama Produk</label>
                            <input type="text" name="name" class="form-control" required
                                   value="<?php echo $_POST['name'] ?? ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="4" required><?php echo $_POST['description'] ?? ''; ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Harga</label>
                                    <input type="number" name="price" class="form-control" required
                                           value="<?php echo $_POST['price'] ?? ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Stok</label>
                                    <input type="number" name="stock" class="form-control" required
                                           value="<?php echo $_POST['stock'] ?? ''; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="category" class="form-select" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category->name; ?>" 
                                            <?php echo (isset($_POST['category']) && $_POST['category'] === $category->name) ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($category->name); ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="new">+ Kategori Baru</option>
                            </select>
                        </div>

                        <div id="newCategoryInput" class="mb-3" style="display: none;">
                            <label class="form-label">Nama Kategori Baru</label>
                            <div class="input-group">
                                <input type="text" name="new_category" class="form-control" 
                                       placeholder="Masukkan nama kategori baru">
                                <button type="button" class="btn btn-outline-secondary" 
                                        onclick="document.querySelector('select[name=category]').value='';
                                                document.getElementById('newCategoryInput').style.display='none';">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="featured" class="form-check-input" id="featured"
                                       <?php echo isset($_POST['featured']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="featured">Tampilkan di Beranda</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Gambar Produk</label>
                            <input type="file" name="image" class="form-control" accept="image/*" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Spesifikasi</label>
                            <div id="specifications">
                                <div class="input-group mb-2">
                                    <input type="text" name="spec_keys[]" class="form-control" placeholder="Nama">
                                    <input type="text" name="spec_values[]" class="form-control" placeholder="Nilai">
                                    <button type="button" class="btn btn-danger" onclick="removeSpec(this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="addSpec()">
                                <i class="fas fa-plus me-2"></i>Tambah Spesifikasi
                            </button>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan Produk
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
    const newCategoryField = newCategoryInput.querySelector('input');
    
    if (this.value === 'new') {
        newCategoryInput.style.display = 'block';
        newCategoryField.required = true;
        this.required = false;
    } else {
        newCategoryInput.style.display = 'none';
        newCategoryField.required = false;
        this.required = true;
    }
});
</script>

<?php require_once '../layouts/footer.php'; ?> 