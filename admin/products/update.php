<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/auth.php';
require_once '../../config/database.php';
require_once '../includes/upload_helper.php';

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Validate input
    if (empty($_POST['id'])) $errors[] = "ID produk tidak valid";
    if (empty($_POST['name'])) $errors[] = "Nama produk harus diisi";
    if (empty($_POST['price'])) $errors[] = "Harga harus diisi";
    if (empty($_POST['category'])) $errors[] = "Kategori harus diisi";
    if (empty($_POST['description'])) $errors[] = "Deskripsi harus diisi";
    if (empty($_POST['stock'])) $errors[] = "Stok harus diisi";

    // Handle image upload if provided
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        try {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/products';
            $imagePath = uploadImage($_FILES['image'], $uploadDir);
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }

    // Process specifications
    $specifications = [];
    if (!empty($_POST['spec_keys']) && !empty($_POST['spec_values'])) {
        foreach ($_POST['spec_keys'] as $index => $key) {
            if (!empty($key) && !empty($_POST['spec_values'][$index])) {
                $specifications[$key] = $_POST['spec_values'][$index];
            }
        }
    }

    if (empty($errors)) {
        try {
            $updateData = [
                'name' => $_POST['name'],
                'price' => (int)$_POST['price'],
                'category' => $_POST['category'],
                'description' => $_POST['description'],
                'stock' => (int)$_POST['stock'],
                'specifications' => $specifications,
                'featured' => isset($_POST['featured']),
                'updated_at' => new UTCDateTime()
            ];

            // Add image path if new image uploaded
            if ($imagePath) {
                $updateData['image'] = $imagePath;
            }

            $result = $database->products->updateOne(
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
        $_SESSION['errors'] = $errors;
        header('Location: edit.php?id=' . $_POST['id'] . '&error=' . urlencode(implode(', ', $errors)));
        exit;
    }
}

header('Location: index.php');
exit; 