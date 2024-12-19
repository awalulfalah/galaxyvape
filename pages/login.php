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
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    try {
        // Cari user berdasarkan email
        $user = $database->users->findOne(['email' => $email]);

        if ($user && password_verify($password, $user->password)) {
            // Set session
            $_SESSION['user'] = [
                'id' => (string)$user->_id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ?? 'user'
            ];

            // Redirect berdasarkan role
            if ($user->role === 'admin') {
                header('Location: /admin/dashboard.php');
            } else {
                header('Location: /');
            }
            exit;
        } else {
            $errors[] = "Email atau password salah";
        }
    } catch (Exception $e) {
        $errors[] = "Terjadi kesalahan: " . $e->getMessage();
    }
}

include '../layouts/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Login</h2>

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
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required
                                   value="<?php echo $_POST['email'] ?? ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>

                    <div class="text-center mt-3">
                        <p>Belum punya akun? <a href="/pages/register.php">Daftar disini</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?> 