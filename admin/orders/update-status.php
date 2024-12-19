<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Pastikan autoloader dimuat pertama
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/order_status.php';

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\Exception\Exception as MongoDBException;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?error=invalid_request');
    exit;
}

$orderId = $_POST['order_id'] ?? '';
$newStatus = $_POST['status'] ?? '';
$notes = $_POST['notes'] ?? '';

// Validasi input
if (empty($orderId) || empty($newStatus)) {
    header('Location: index.php?error=missing_parameters');
    exit;
}

// Validasi status baru
if (!array_key_exists($newStatus, ORDER_STATUSES)) {
    header('Location: index.php?error=invalid_status');
    exit;
}

try {
    // Get current order
    $order = $database->orders->findOne(['_id' => new ObjectId($orderId)]);
    if (!$order) {
        header('Location: index.php?error=order_not_found');
        exit;
    }

    // Validasi transisi status
    if (!in_array($newStatus, VALID_STATUS_TRANSITIONS[$order->order_status])) {
        header('Location: index.php?error=invalid_status_transition');
        exit;
    }

    // Prepare update data
    $updateData = [
        'status_history' => [
            'status' => $newStatus,
            'notes' => $notes,
            'timestamp' => new UTCDateTime(),
            'updated_by' => $_SESSION['user']['name']
        ]
    ];

    // Jika status berubah menjadi paid, update stok produk
    if ($newStatus === 'paid') {
        foreach ($order->items as $item) {
            $database->products->updateOne(
                ['_id' => new ObjectId($item['product_id'])],
                ['$inc' => ['stock' => -$item['quantity']]]
            );
        }
    }

    $result = $database->orders->updateOne(
        ['_id' => new ObjectId($orderId)],
        [
            '$set' => [
                'order_status' => $newStatus,
                'updated_at' => new UTCDateTime()
            ],
            '$push' => [
                'status_history' => $updateData['status_history']
            ]
        ]
    );

    if ($result->getModifiedCount()) {
        header('Location: index.php?success=status_updated');
    } else {
        header('Location: index.php?error=update_failed');
    }
} catch (MongoDBException $e) {
    header('Location: index.php?error=' . urlencode($e->getMessage()));
} catch (Exception $e) {
    header('Location: index.php?error=' . urlencode($e->getMessage()));
}
exit;