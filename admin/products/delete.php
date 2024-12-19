<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/auth.php';
require_once '../../config/database.php';

use MongoDB\BSON\ObjectId;

if (!isset($_GET['id'])) {
    header('Location: index.php?error=invalid_request');
    exit;
}

try {
    $productId = $_GET['id'];

    // Get product to delete image
    $product = $database->products->findOne(['_id' => new ObjectId($productId)]);
    if (!$product) {
        header('Location: index.php?error=product_not_found');
        exit;
    }

    // Delete product image if exists
    if (isset($product->image)) {
        $imagePath = $_SERVER['DOCUMENT_ROOT'] . $product->image;
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    // Delete product from database
    $result = $database->products->deleteOne(['_id' => new ObjectId($productId)]);

    if ($result->getDeletedCount()) {
        header('Location: index.php?success=deleted');
    } else {
        header('Location: index.php?error=delete_failed');
    }
} catch (Exception $e) {
    header('Location: index.php?error=' . urlencode($e->getMessage()));
}
exit; 