<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/session.php';
require_once '../config/database.php';

// Redirect jika sudah login
if (isset($_SESSION['user'])) {
    header('Location: /');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);

    // Validasi
    if (empty($name)) $errors[] = "Nama harus diisi";
    if (empty($email)) $errors[] = "Email harus diisi";
    if (empty($password)) $errors[] = "Password harus diisi";
    if ($password !== $password_confirm) $errors[] = "Password tidak cocok";
    if (empty($phone)) $errors[] = "Nomor telepon harus diisi";

    // Cek email sudah terdaftar
    if (empty($errors)) {
        $existingUser = $database->users->findOne(['email' => $email]);
        if ($existingUser) {
            $errors[] = "Email sudah terdaftar";
        }
    }

    // Proses registrasi
    if (empty($errors)) {
        try {
            $user = [
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'phone' => $phone,
                'role' => 'user',
                'created_at' => new MongoDB\BSON\UTCDateTime(),
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ];

            $result = $database->users->insertOne($user);

            if ($result->getInsertedCount()) {
                $_SESSION['user'] = [
                    'id' => (string)$result->getInsertedId(),
                    'name' => $name,
                    'email' => $email,
                    'role' => 'user'
                ];

                header('Location: /');
                exit;
            }
        } catch (Exception $e) {
            $errors[] = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

include '../layouts/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Register</h2>

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
                                   value="<?php echo $_POST['name'] ?? ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required
                                   value="<?php echo $_POST['email'] ?? ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="password_confirm" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nomor Telepon</label>
                            <input type="tel" name="phone" class="form-control" required
                                   value="<?php echo $_POST['phone'] ?? ''; ?>">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Register</button>
                    </form>

                    <div class="text-center mt-3">
                        <p>Sudah punya akun? <a href="/pages/login.php">Login disini</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?> 