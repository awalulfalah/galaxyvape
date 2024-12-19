<?php
require_once 'layouts/header.php';

use MongoDB\BSON\UTCDateTime;

// Get statistics
try {
    // Total orders
    $totalOrders = $database->orders->countDocuments();
    
    // Total revenue
    $revenue = $database->orders->aggregate([
        ['$match' => ['payment_status' => 'paid']],
        ['$group' => ['_id' => null, 'total' => ['$sum' => '$total']]]
    ])->toArray();
    $totalRevenue = $revenue[0]->total ?? 0;
    
    // Orders this month
    $startOfMonth = new UTCDateTime(strtotime('first day of this month') * 1000);
    $ordersThisMonth = $database->orders->countDocuments([
        'created_at' => ['$gte' => $startOfMonth]
    ]);
    
    // Best selling products
    $bestSellers = $database->orders->aggregate([
        ['$unwind' => '$items'],
        ['$group' => [
            '_id' => '$items.name',
            'total_sold' => ['$sum' => '$items.quantity'],
            'revenue' => ['$sum' => ['$multiply' => ['$items.price', '$items.quantity']]]
        ]],
        ['$sort' => ['total_sold' => -1]],
        ['$limit' => 5]
    ])->toArray();
    
    // Recent orders
    $recentOrders = $database->orders->find(
        [],
        [
            'sort' => ['created_at' => -1],
            'limit' => 5
        ]
    )->toArray();
    
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<div class="container-fluid">
    <h1 class="h2 mb-4">Dashboard</h1>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Pesanan</h5>
                    <h2 class="mt-3 mb-3"><?php echo number_format($totalOrders); ?></h2>
                    <p class="mb-0 text-muted">Pesanan bulan ini: <?php echo number_format($ordersThisMonth); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Pendapatan</h5>
                    <h2 class="mt-3 mb-3">Rp <?php echo number_format($totalRevenue, 0, ',', '.'); ?></h2>
                    <p class="mb-0 text-muted">Dari pesanan yang sudah dibayar</p>
                </div>
            </div>
        </div>
        <!-- Add more statistics cards as needed -->
    </div>

    <div class="row">
        <!-- Best Selling Products -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Produk Terlaris</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Terjual</th>
                                    <th>Pendapatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bestSellers as $product): ?>
                                <tr>
                                    <td><?php echo $product->_id; ?></td>
                                    <td><?php echo $product->total_sold; ?></td>
                                    <td>Rp <?php echo number_format($product->revenue, 0, ',', '.'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Pesanan Terbaru</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td><?php echo $order->_id; ?></td>
                                    <td><?php echo $order->shipping_info->name; ?></td>
                                    <td>Rp <?php echo number_format($order->total, 0, ',', '.'); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo getStatusBadgeClass($order->order_status); ?>">
                                            <?php echo getStatusLabel($order->order_status); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Helper functions
function getStatusLabel($status) {
    $labels = [
        'pending' => 'Menunggu Pembayaran',
        'paid' => 'Sudah Dibayar',
        'processing' => 'Diproses',
        'shipped' => 'Dikirim',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan'
    ];
    return $labels[$status] ?? $status;
}

function getStatusBadgeClass($status) {
    $classes = [
        'pending' => 'warning',
        'paid' => 'info',
        'processing' => 'primary',
        'shipped' => 'primary',
        'completed' => 'success',
        'cancelled' => 'danger'
    ];
    return $classes[$status] ?? 'secondary';
}

require_once 'layouts/footer.php';
?> 