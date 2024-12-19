<?php
require_once '../includes/auth.php';
require_once '../../config/database.php';
require_once '../../config/order_status.php';

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Order ID not provided']);
    exit;
}

try {
    $orderId = $_GET['id'];
    $order = $database->orders->findOne(['_id' => new ObjectId($orderId)]);
    
    if (!$order) {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found']);
        exit;
    }

    // Convert MongoDB document to array and format dates
    $orderArray = json_decode(json_encode($order), true);
    
    // Format dates
    if (isset($orderArray['created_at']['$date'])) {
        $orderArray['created_at'] = date('Y-m-d H:i:s', $orderArray['created_at']['$date'] / 1000);
    }
    if (isset($orderArray['updated_at']['$date'])) {
        $orderArray['updated_at'] = date('Y-m-d H:i:s', $orderArray['updated_at']['$date'] / 1000);
    }
    
    // Format status history dates
    if (isset($orderArray['status_history'])) {
        foreach ($orderArray['status_history'] as &$history) {
            if (isset($history['timestamp']['$date'])) {
                $history['timestamp'] = date('Y-m-d H:i:s', $history['timestamp']['$date'] / 1000);
            }
        }
    }
    
    // Add additional data
    $orderArray['status_label'] = ORDER_STATUSES[$order->order_status];
    $orderArray['status_badge'] = STATUS_BADGES[$order->order_status];
    
    // Format currency values
    $orderArray['total_formatted'] = 'Rp ' . number_format($orderArray['total'], 0, ',', '.');
    if (isset($orderArray['items'])) {
        foreach ($orderArray['items'] as &$item) {
            $item['price_formatted'] = 'Rp ' . number_format($item['price'], 0, ',', '.');
            $item['subtotal_formatted'] = 'Rp ' . number_format($item['price'] * $item['quantity'], 0, ',', '.');
        }
    }
    
    echo json_encode($orderArray);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 