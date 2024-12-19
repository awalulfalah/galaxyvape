<?php
require_once '../layouts/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['name'])) {
        header('Location: index.php?error=name_required');
        exit;
    }

    $categoryName = strtolower(trim($_POST['name']));

    try {
        // Check if category already exists
        $existingCategory = $database->categories->findOne(['name' => $categoryName]);
        if ($existingCategory) {
            header('Location: index.php?error=category_exists');
            exit;
        }

        // Insert new category
        $category = [
            'name' => $categoryName,
            'description' => $_POST['description'] ?? '',
            'active' => true,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ];

        $result = $database->categories->insertOne($category);
        
        if ($result->getInsertedCount()) {
            header('Location: index.php?success=created');
        } else {
            header('Location: index.php?error=create_failed');
        }
    } catch (Exception $e) {
        header('Location: index.php?error=' . urlencode($e->getMessage()));
    }
    exit;
}

header('Location: index.php');
exit; 