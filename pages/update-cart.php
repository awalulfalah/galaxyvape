<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'] ?? '';
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    if (isset($_SESSION['cart'][$productId])) {
        if ($quantity > 0) {
            $_SESSION['cart'][$productId]['quantity'] = $quantity;
        } else {
            unset($_SESSION['cart'][$productId]);
        }
    }
}

header('Location: /pages/cart.php');
exit;
?> 