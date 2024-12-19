<?php
require_once '../layouts/header.php';

// Filter parameters
$role = isset($_GET['role']) ? $_GET['role'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$skip = ($page - 1) * $limit;

try {
    // Build query
    $query = [];
    if (!empty($role)) {
        $query['role'] = $role;
    }
    if (!empty($search)) {
        $query['$or'] = [
            ['name' => ['$regex' => $search, '$options' => 'i']],
            ['email' => ['$regex' => $search, '$options' => 'i']],
            ['phone' => ['$regex' => $search, '$options' => 'i']]
        ];
    }

    // Get total users for pagination
    $totalUsers = $database->users->countDocuments($query);
    $totalPages = ceil($totalUsers / $limit);

    // Get users
    $users = $database->users->find(
        $query,
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
        <h1 class="h2">Manajemen Pengguna</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-plus me-2"></i>Tambah Pengguna
        </button>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php
            switch ($_GET['success']) {
                case 'created':
                    echo 'Pengguna berhasil ditambahkan.';
                    break;
                case 'updated':
                    echo 'Pengguna berhasil diperbarui.';
                    break;
                case 'deleted':
                    echo 'Pengguna berhasil dihapus.';
                    break;
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Cari Pengguna</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Nama, Email, atau No. Telepon"
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select">
                        <option value="">Semua Role</option>
                        <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="user" <?php echo $role === 'user' ? 'selected' : ''; ?>>User</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Users List -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>No. Telepon</th>
                            <th>Role</th>
                            <th>Terdaftar</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user->name; ?></td>
                                <td><?php echo $user->email; ?></td>
                                <td><?php echo $user->phone; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $user->role === 'admin' ? 'danger' : 'primary'; ?>">
                                        <?php echo ucfirst($user->role); ?>
                                    </span>
                                </td>
                                <td><?php echo $user->created_at->toDateTime()->format('d/m/Y'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo isset($user->active) && !$user->active ? 'danger' : 'success'; ?>">
                                        <?php echo isset($user->active) && !$user->active ? 'Nonaktif' : 'Aktif'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="editUser(<?php echo json_encode($user); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($user->_id != $_SESSION['user']['id']): ?>
                                        <?php if (isset($user->active) && !$user->active): ?>
                                            <button type="button" class="btn btn-sm btn-success" 
                                                    onclick="toggleStatus('<?php echo $user->_id; ?>', true)">
                                                <i class="fas fa-user-check"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-warning" 
                                                    onclick="toggleStatus('<?php echo $user->_id; ?>', false)">
                                                <i class="fas fa-user-slash"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="deleteUser('<?php echo $user->_id; ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
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
                                <a class="page-link" href="?page=<?php echo $i; ?>&role=<?php echo $role; ?>&search=<?php echo urlencode($search); ?>">
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="create.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. Telepon</label>
                        <input type="tel" name="phone" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
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

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="update.php" method="POST">
                <input type="hidden" name="id" id="editUserId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" id="editUserName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="editUserEmail" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password" class="form-control" 
                               placeholder="Kosongkan jika tidak ingin mengubah password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. Telepon</label>
                        <input type="tel" name="phone" id="editUserPhone" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" id="editUserRole" class="form-select" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
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
function editUser(user) {
    document.getElementById('editUserId').value = user._id;
    document.getElementById('editUserName').value = user.name;
    document.getElementById('editUserEmail').value = user.email;
    document.getElementById('editUserPhone').value = user.phone;
    document.getElementById('editUserRole').value = user.role;
    
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}

function toggleStatus(userId, activate) {
    if (confirm(`Apakah Anda yakin ingin ${activate ? 'mengaktifkan' : 'menonaktifkan'} pengguna ini?`)) {
        window.location.href = `toggle-status.php?id=${userId}&activate=${activate ? 1 : 0}`;
    }
}

function deleteUser(userId) {
    if (confirm('Apakah Anda yakin ingin menghapus pengguna ini?')) {
        window.location.href = `delete.php?id=${userId}`;
    }
}
</script>

<?php require_once '../layouts/footer.php'; ?> 