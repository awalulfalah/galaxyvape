<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/auth.php';
require_once '../../config/database.php';

use MongoDB\BSON\UTCDateTime;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Validate input
    if (empty($_POST['name'])) $errors[] = "Nama harus diisi";
    if (empty($_POST['email'])) $errors[] = "Email harus diisi";
    if (empty($_POST['password'])) $errors[] = "Password harus diisi";
    if (empty($_POST['phone'])) $errors[] = "Nomor telepon harus diisi";
    if (empty($_POST['role'])) $errors[] = "Role harus diisi";

    // Validate email format
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }

    // Check if email already exists
    if (empty($errors)) {
        $existingUser = $database->users->findOne(['email' => $_POST['email']]);
        if ($existingUser) {
            $errors[] = "Email sudah terdaftar";
        }
    }

    if (empty($errors)) {
        try {
            $user = [
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                'phone' => $_POST['phone'],
                'role' => $_POST['role'],
                'active' => true,
                'created_at' => new UTCDateTime(),
                'updated_at' => new UTCDateTime()
            ];

            $result = $database->users->insertOne($user);
            
            if ($result->getInsertedCount()) {
                header('Location: index.php?success=created');
                exit;
            } else {
                $errors[] = "Gagal menambahkan pengguna";
            }
        } catch (Exception $e) {
            $errors[] = "Error: " . $e->getMessage();
        }
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header('Location: index.php?error=' . urlencode(implode(', ', $errors)));
        exit;
    }
}

header('Location: index.php');
exit; 