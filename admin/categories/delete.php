<?php
require_once '../layouts/header.php';

if (isset($_GET['id'])) {
    $categoryId = $_GET['id'];

    try {
        // Get category name first
        $category = $database->categories->findOne(['_id' => new MongoDB\BSON\ObjectId($categoryId)]);
        if (!$category) {
            header('Location: index.php?error=category_not_found');
            exit;
        }

        // Update products to 'uncategorized'
        $database->products->updateMany(
            ['category' => $category->name],
            ['$set' => ['category' => 'uncategorized']]
        );

        // Delete the category
        $result = $database->categories->deleteOne(['_id' => new MongoDB\BSON\ObjectId($categoryId)]);

        if ($result->getDeletedCount()) {
            header('Location: index.php?success=deleted');
        } else {
            header('Location: index.php?error=delete_failed');
        }
    } catch (Exception $e) {
        header('Location: index.php?error=' . urlencode($e->getMessage()));
    }
    exit;
}

header('Location: index.php');
exit; 