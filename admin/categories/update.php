<?php
require_once '../layouts/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['id']) || empty($_POST['name'])) {
        header('Location: index.php?error=invalid_input');
        exit;
    }

    $categoryId = $_POST['id'];
    $newName = strtolower(trim($_POST['name']));

    try {
        // Get current category
        $category = $database->categories->findOne(['_id' => new MongoDB\BSON\ObjectId($categoryId)]);
        if (!$category) {
            header('Location: index.php?error=category_not_found');
            exit;
        }

        // Update category
        $updateResult = $database->categories->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($categoryId)],
            [
                '$set' => [
                    'name' => $newName,
                    'description' => $_POST['description'] ?? '',
                    'updated_at' => new MongoDB\BSON\UTCDateTime()
                ]
            ]
        );

        // Update all products with old category name
        if ($updateResult->getModifiedCount()) {
            $database->products->updateMany(
                ['category' => $category->name],
                ['$set' => ['category' => $newName]]
            );
            header('Location: index.php?success=updated');
        } else {
            header('Location: index.php?error=no_changes');
        }
    } catch (Exception $e) {
        header('Location: index.php?error=' . urlencode($e->getMessage()));
    }
    exit;
}

header('Location: index.php');
exit; 