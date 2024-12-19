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
    $userId = $_GET['id'];

    // Prevent deleting self
    if ($userId === $_SESSION['user']['id']) {
        header('Location: index.php?error=cannot_delete_self');
        exit;
    }

    // Check if user exists
    $user = $database->users->findOne(['_id' => new ObjectId($userId)]);
    if (!$user) {
        header('Location: index.php?error=user_not_found');
        exit;
    }

    $result = $database->users->deleteOne(['_id' => new ObjectId($userId)]);

    if ($result->getDeletedCount()) {
        header('Location: index.php?success=deleted');
    } else {
        header('Location: index.php?error=delete_failed');
    }
} catch (Exception $e) {
    header('Location: index.php?error=' . urlencode($e->getMessage()));
}
exit; 