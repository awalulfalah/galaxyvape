<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/auth.php';
require_once '../../config/database.php';

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Validate input
    if (empty($_POST['id'])) $errors[] = "ID pengguna tidak valid";
    if (empty($_POST['name'])) $errors[] = "Nama harus diisi";
    if (empty($_POST['email'])) $errors[] = "Email harus diisi";
    if (empty($_POST['phone'])) $errors[] = "Nomor telepon harus diisi";
    if (empty($_POST['role'])) $errors[] = "Role harus diisi";

    // Validate email format
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }

    // Check if email already exists (excluding current user)
    if (empty($errors)) {
        $existingUser = $database->users->findOne([
            '_id' => ['$ne' => new ObjectId($_POST['id'])],
            'email' => $_POST['email']
        ]);
        if ($existingUser) {
            $errors[] = "Email sudah digunakan oleh pengguna lain";
        }
    }

    if (empty($errors)) {
        try {
            $updateData = [
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'phone' => $_POST['phone'],
                'role' => $_POST['role'],
                'updated_at' => new UTCDateTime()
            ];

            // Update password if provided
            if (!empty($_POST['password'])) {
                $updateData['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }

            $result = $database->users->updateOne(
                ['_id' => new ObjectId($_POST['id'])],
                ['$set' => $updateData]
            );

            if ($result->getModifiedCount()) {
                header('Location: index.php?success=updated');
                exit;
            } else {
                $errors[] = "Tidak ada perubahan data";
            }
        } catch (Exception $e) {
            $errors[] = "Error: " . $e->getMessage();
        }
    }

    if (!empty($errors)) {
        header('Location: index.php?error=' . urlencode(implode(', ', $errors)));
        exit;
    }
}

header('Location: index.php');
exit; 