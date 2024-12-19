<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/session.php';
require_once '../config/database.php';

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\Exception\Exception as MongoDBException;

// Redirect jika belum login
if (!isset($_SESSION['user'])) {
    header('Location: /pages/login.php');
    exit;
}

$errors = [];
$success = false;

// Ambil data user dari database
try {
    $userId = $_SESSION['user']['id'];
    if (!preg_match('/^[0-9a-fA-F]{24}$/', $userId)) {
        throw new MongoDBException("Invalid user ID format");
    }

    $user = $database->users->findOne(['_id' => new ObjectId($userId)]);
    if (!$user) {
        header('Location: /pages/logout.php');
        exit;
    }
} catch (MongoDBException $e) {
    $errors[] = "Error mengambil data user: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($user)) {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $new_password_confirm = $_POST['new_password_confirm'] ?? '';

    // Validasi input
    if (empty($name)) $errors[] = "Nama harus diisi";
    if (empty($email)) $errors[] = "Email harus diisi";
    if (empty($phone)) $errors[] = "Nomor telepon harus diisi";

    // Cek email jika berubah
    if ($email !== $user->email) {
        try {
            $existingUser = $database->users->findOne([
                '_id' => ['$ne' => new ObjectId($userId)],
                'email' => $email
            ]);
            if ($existingUser) {
                $errors[] = "Email sudah digunakan";
            }
        } catch (MongoDBException $e) {
            $errors[] = "Error memeriksa email: " . $e->getMessage();
        }
    }

    // Update profil jika tidak ada error
    if (empty($errors)) {
        try {
            $now = new UTCDateTime();
            $updateData = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'updated_at' => $now
            ];

            // Update password jika diisi
            if (!empty($current_password)) {
                if (!password_verify($current_password, $user->password)) {
                    $errors[] = "Password saat ini salah";
                } elseif (empty($new_password)) {
                    $errors[] = "Password baru harus diisi";
                } elseif ($new_password !== $new_password_confirm) {
                    $errors[] = "Konfirmasi password baru tidak cocok";
                } else {
                    $updateData['password'] = password_hash($new_password, PASSWORD_DEFAULT);
                }
            }

            if (empty($errors)) {
                $result = $database->users->updateOne(
                    ['_id' => new ObjectId($userId)],
                    ['$set' => $updateData]
                );

                if ($result->getModifiedCount()) {
                    $_SESSION['user']['name'] = $name;
                    $_SESSION['user']['email'] = $email;
                    $success = true;
                    
                    // Refresh user data
                    $user = $database->users->findOne(['_id' => new ObjectId($userId)]);
                }
            }
        } catch (MongoDBException $e) {
            $errors[] = "Error updating profile: " . $e->getMessage();
        }
    }
}

include '../layouts/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Menu Profil</h5>
                    <div class="list-group">
                        <a href="/pages/profile.php" class="list-group-item list-group-item-action active">
                            Informasi Profil
                        </a>
                        <a href="/pages/orders.php" class="list-group-item list-group-item-action">
                            Riwayat Pesanan
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Form -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title mb-4">Edit Profil</h2>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            Profil berhasil diperbarui!
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control" required
                                   value="<?php echo $_POST['name'] ?? $user->name; ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required
                                   value="<?php echo $_POST['email'] ?? $user->email; ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nomor Telepon</label>
                            <input type="tel" name="phone" class="form-control" required
                                   value="<?php echo $_POST['phone'] ?? $user->phone; ?>">
                        </div>

                        <h5 class="mt-4 mb-3">Ubah Password</h5>
                        <div class="mb-3">
                            <label class="form-label">Password Saat Ini</label>
                            <input type="password" name="current_password" class="form-control">
                            <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="new_password" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" name="new_password_confirm" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </form>
                </div>
            </div>

            <!-- Account Info -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">Informasi Akun</h5>
                    <p class="mb-1">Bergabung sejak: <?php echo $user->created_at->toDateTime()->format('d F Y'); ?></p>
                    <p class="mb-1">Status: <?php echo ucfirst($user->role); ?></p>
                    <p class="mb-0">Login terakhir: <?php echo isset($user->last_login) ? $user->last_login->toDateTime()->format('d F Y H:i') : '-'; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?> 