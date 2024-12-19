<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false ? 'active' : ''; ?>" 
                   href="/admin/dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'products') !== false ? 'active' : ''; ?>" 
                   href="/admin/products/index.php">
                    <i class="fas fa-box me-2"></i>
                    Produk
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'categories') !== false ? 'active' : ''; ?>" 
                   href="/admin/categories/index.php">
                    <i class="fas fa-tags me-2"></i>
                    Kategori
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'orders') !== false ? 'active' : ''; ?>" 
                   href="/admin/orders/index.php">
                    <i class="fas fa-shopping-cart me-2"></i>
                    Pesanan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'users') !== false ? 'active' : ''; ?>" 
                   href="/admin/users/index.php">
                    <i class="fas fa-users me-2"></i>
                    Pengguna
                </a>
            </li>
        </ul>
    </div>
</nav> 