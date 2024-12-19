<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/auth.php';
require_once '../../config/database.php';
require_once '../includes/upload_helper.php';
require_once '../includes/mongo_helper.php';
require_once '../../vendor/autoload.php';

use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\ObjectId;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Validate input
    if (empty($_POST['name'])) $errors[] = "Nama produk harus diisi";
    if (empty($_POST['price'])) $errors[] = "Harga harus diisi";
    if (empty($_POST['category'])) $errors[] = "Kategori harus diisi";
    if (empty($_POST['description'])) $errors[] = "Deskripsi harus diisi";
    if (empty($_POST['stock'])) $errors[] = "Stok harus diisi";

    // Handle image upload
    $imagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        try {
            $fileName = uploadImage($_FILES['image'], '../uploads/products/');
            $imagePath = $fileName;
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    } else {
        $errors[] = "Gambar produk harus diupload";
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

    // Handle kategori
    $categoryName = '';
    if ($_POST['category'] === 'new' && !empty($_POST['new_category'])) {
        // Jika memilih kategori baru
        $categoryName = strtolower(trim($_POST['new_category']));
        
        // Cek apakah kategori sudah ada
        $existingCategory = $database->categories->findOne(['name' => $categoryName]);
        if (!$existingCategory) {
            // Buat kategori baru
            try {
                $category = [
                    'name' => $categoryName,
                    'description' => '',
                    'active' => true,
                    'created_at' => mongoTimestamp(),
                    'updated_at' => mongoTimestamp()
                ];
                $database->categories->insertOne($category);
            } catch (Exception $e) {
                $errors[] = "Gagal membuat kategori baru: " . $e->getMessage();
            }
        }
    } else {
        // Gunakan kategori yang dipilih
        $categoryName = $_POST['category'];
        
        // Validasi kategori exists
        $existingCategory = $database->categories->findOne(['name' => $categoryName]);
        if (!$existingCategory) {
            $errors[] = "Kategori tidak valid";
        }
    }

    if (empty($errors)) {
        try {
            $product = [
                'name' => $_POST['name'],
                'price' => (int)$_POST['price'],
                'category' => $categoryName,
                'description' => $_POST['description'],
                'stock' => (int)$_POST['stock'],
                'image' => $imagePath,
                'specifications' => $specifications,
                'featured' => isset($_POST['featured']),
                'created_at' => mongoTimestamp(),
                'updated_at' => mongoTimestamp()
            ];

            $result = $database->products->insertOne($product);
            
            if ($result->getInsertedCount()) {
                header('Location: index.php?success=created');
                exit;
            } else {
                $errors[] = "Gagal menambahkan produk";
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