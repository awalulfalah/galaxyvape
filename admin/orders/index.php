<?php
require_once '../layouts/header.php';
require_once '../../config/order_status.php';

// Filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$skip = ($page - 1) * $limit;

try {
    // Build query
    $query = [];
    if (!empty($status)) {
        $query['order_status'] = $status;
    }

    // Get total orders for pagination
    $totalOrders = $database->orders->countDocuments($query);
    $totalPages = ceil($totalOrders / $limit);

    // Get orders
    $orders = $database->orders->find(
        $query,
        [
            'sort' => ['created_at' => -1],
            'skip' => $skip,
            'limit' => $limit
        ]
    )->toArray();

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Manajemen Pesanan</h1>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php
            switch ($_GET['success']) {
                case 'updated':
                    echo 'Status pesanan berhasil diperbarui.';
                    break;
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Status Pesanan</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>
                            Menunggu Pembayaran
                        </option>
                        <option value="paid" <?php echo $status === 'paid' ? 'selected' : ''; ?>>
                            Sudah Dibayar
                        </option>
                        <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>
                            Diproses
                        </option>
                        <option value="shipped" <?php echo $status === 'shipped' ? 'selected' : ''; ?>>
                            Dikirim
                        </option>
                        <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>
                            Selesai
                        </option>
                        <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>
                            Dibatalkan
                        </option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders List -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Tanggal</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo $order->_id; ?></td>
                                <td><?php echo $order->created_at->toDateTime()->format('d/m/Y H:i'); ?></td>
                                <td>
                                    <?php echo $order->shipping_info->name; ?><br>
                                    <small class="text-muted"><?php echo $order->shipping_info->email; ?></small>
                                </td>
                                <td>Rp <?php echo number_format($order->total, 0, ',', '.'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo STATUS_BADGES[$order->order_status]; ?>">
                                        <?php echo ORDER_STATUSES[$order->order_status]; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle" 
                                                data-bs-toggle="dropdown">
                                            Update Status
                                        </button>
                                        <ul class="dropdown-menu">
                                            <?php if ($order->order_status === 'pending'): ?>
                                                <li>
                                                    <a class="dropdown-item" href="#" 
                                                       onclick="updateStatus('<?php echo $order->_id; ?>', 'cancelled')">
                                                        Batalkan Pesanan
                                                    </a>
                                                </li>
                                            <?php endif; ?>

                                            <?php if ($order->order_status === 'paid'): ?>
                                                <li>
                                                    <a class="dropdown-item" href="#" 
                                                       onclick="updateStatus('<?php echo $order->_id; ?>', 'processing')">
                                                        Proses Pesanan
                                                    </a>
                                                </li>
                                            <?php endif; ?>

                                            <?php if ($order->order_status === 'processing'): ?>
                                                <li>
                                                    <a class="dropdown-item" href="#" 
                                                       onclick="updateStatus('<?php echo $order->_id; ?>', 'shipped')">
                                                        Kirim Pesanan
                                                    </a>
                                                </li>
                                            <?php endif; ?>

                                            <?php if ($order->order_status === 'shipped'): ?>
                                                <li>
                                                    <a class="dropdown-item" href="#" 
                                                       onclick="updateStatus('<?php echo $order->_id; ?>', 'completed')">
                                                        Selesaikan Pesanan
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-info" 
                                            onclick="viewDetail('<?php echo $order->_id; ?>')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Order Detail Modal -->
<div class="modal fade" id="orderDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pesanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetailContent">
                Loading...
            </div>
            <div class="modal-footer">
                <form action="update-status.php" method="POST" class="d-flex gap-2 w-100">
                    <input type="hidden" name="order_id" id="updateOrderId">
                    <select name="status" class="form-select" required>
                        <option value="">Pilih Status</option>
                        <div id="statusOptions"></div>
                    </select>
                    <input type="text" name="notes" class="form-control" placeholder="Catatan (opsional)">
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showOrderDetail(orderId) {
    const modal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
    const content = document.getElementById('orderDetailContent');
    const statusSelect = document.querySelector('select[name="status"]');
    
    document.getElementById('updateOrderId').value = orderId;
    content.innerHTML = 'Loading...';
    statusSelect.innerHTML = '<option value="">Pilih Status</option>';
    
    fetch(`get-detail.php?id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            content.innerHTML = formatOrderDetail(data);
            
            // Update opsi status yang valid
            const validTransitions = <?php echo json_encode(VALID_STATUS_TRANSITIONS); ?>;
            const currentStatus = data.order_status;
            
            validTransitions[currentStatus].forEach(status => {
                const option = document.createElement('option');
                option.value = status;
                option.textContent = <?php echo json_encode(ORDER_STATUSES); ?>[status];
                statusSelect.appendChild(option);
            });
        })
        .catch(error => {
            content.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
        });
        
    modal.show();
}

function formatOrderDetail(order) {
    const statusBadges = <?php echo json_encode(STATUS_BADGES); ?>;
    const statusLabels = <?php echo json_encode(ORDER_STATUSES); ?>;
    
    return `
        <div class="mb-3">
            <h6>Status Pesanan</h6>
            <span class="badge bg-${statusBadges[order.order_status]}">
                ${statusLabels[order.order_status]}
            </span>
        </div>
        <!-- ... sisa dari format detail pesanan ... -->
    `;
}
</script>

<?php
require_once '../layouts/footer.php';
?> 