<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/auth.php';
require_once '../../config/database.php';

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

if (!isset($_GET['id']) || !isset($_GET['activate'])) {
    header('Location: index.php?error=invalid_request');
    exit;
}

try {
    $userId = $_GET['id'];
    $activate = (bool)$_GET['activate'];

    // Prevent deactivating self
    if ($userId === $_SESSION['user']['id']) {
        header('Location: index.php?error=cannot_deactivate_self');
        exit;
    }

    $result = $database->users->updateOne(
        ['_id' => new ObjectId($userId)],
        [
            '$set' => [
                'active' => $activate,
                'updated_at' => new UTCDateTime()
            ]
        ]
    );

    if ($result->getModifiedCount()) {
        header('Location: index.php?success=updated');
    } else {
        header('Location: index.php?error=update_failed');
    }
} catch (Exception $e) {
    header('Location: index.php?error=' . urlencode($e->getMessage()));
}
exit; 