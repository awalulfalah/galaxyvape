<?php
require_once '../../config/database.php';

// Create categories collection if not exists
try {
    // Create a unique index on category name
    $database->categories->createIndex(['name' => 1], ['unique' => true]);
    
    echo "Categories collection initialized successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} 