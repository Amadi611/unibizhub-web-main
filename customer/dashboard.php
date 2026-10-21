<?php
require_once __DIR__ . '/../db_connect.php';

if (!isLoggedIn()) {
    redirect('../login.php', 'Please log in to view your account.', 'warning');
}

if (getUserRole() !== 'Customer') {
    switch (getUserRole()) {
        case 'Admin':
            redirect('../admin/dashboard.php');
        case 'Entrepreneur':
            redirect('../seller/dashboard.php');
        default:
            redirect('../login.php', 'Customer access is required.', 'error');
    }
}

$customer_id = (int) $_SESSION['user_id'];
$stmt = $conn->prepare(
    'SELECT COUNT(*) AS order_count, COALESCE(SUM(total_amount), 0) AS total_spent
     FROM orders WHERE user_id = ?'
);
$stmt->bind_param('i', $customer_id);
$stmt->execute();
$order_summary = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare(
    'SELECT order_id, order_date, total_amount, order_status
     FROM orders WHERE user_id = ?
     ORDER BY order_date DESC LIMIT 5'
);
$stmt->bind_param('i', $customer_id);
$stmt->execute();
$recent_orders = $stmt->get_result();

$status_classes = [
    'Pending' => 'warning',
    'Processing' => 'info',
    'Shipped' => 'primary',
    'Delivered' => 'success',
    'Cancelled' => 'danger'
];

include __DIR__ . '/../header.php';
?>

<style>
    .customer-dashboard { max-width: 1120px; }
    .customer-title { border-bottom: 1px solid #dce3e8; padding-bottom: 1.25rem; }
    .account-stat { border-left: 3px solid #e87532; padding: .25rem 1rem; }
    .account-stat-label { color: #5e6a71; font-size: .875rem; }
    .account-stat-value { color: #18343b; font-size: 1.5rem; font-weight: 700; }
    .order-table th { color: #5e6a71; font-size: .8rem; font-weight: 600; text-transform: uppercase; }
</style>

<main class="container customer-dashboard py-4 py-lg-5">
    <header class="customer-title d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <p class="text-uppercase small fw-semibold text-muted mb-1">Customer account</p>
            <h1 class="h2 mb-1">Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Customer'); ?></h1>
            <p class="text-muted mb-0"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="cart.php"><i class="fas fa-cart-shopping me-2" aria-hidden="true"></i>Cart <span class="badge text-bg-secondary ms-1"><?php echo getCartCount(); ?></span></a>
            <a class="btn btn-primary" href="products.php"><i class="fas fa-magnifying-glass me-2" aria-hidden="true"></i>Browse products</a>
        </div>
    </header>

    <section class="row g-4 mb-5" aria-label="Account summary">
        <div class="col-sm-4">
            <div class="account-stat">
                <div class="account-stat-label">Orders placed</div>
                <div class="account-stat-value"><?php echo number_format((int) $order_summary['order_count']); ?></div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="account-stat">
                <div class="account-stat-label">Total spent</div>
                <div class="account-stat-value"><?php echo formatPrice($order_summary['total_spent']); ?></div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="account-stat">
                <div class="account-stat-label">Items in cart</div>
                <div class="account-stat-value"><?php echo number_format(getCartCount()); ?></div>
            </div>
        </div>
    </section>

    <section aria-labelledby="recent-orders-heading">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <h2 class="h5 mb-0" id="recent-orders-heading">Recent orders</h2>
            <a href="products.php" class="link-primary">Continue shopping <i class="fas fa-arrow-right ms-1" aria-hidden="true"></i></a>
        </div>

        <?php if ($recent_orders->num_rows === 0): ?>
            <div class="border rounded-1 bg-white p-4 text-center">
                <i class="fas fa-receipt fa-2x text-muted mb-3" aria-hidden="true"></i>
                <p class="mb-2 fw-semibold">No orders yet</p>
                <p class="text-muted mb-3">Your purchases will appear here after checkout.</p>
                <a href="products.php" class="btn btn-outline-primary">Explore products</a>
            </div>
        <?php else: ?>
            <div class="table-responsive border rounded-1 bg-white">
                <table class="table order-table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">Order</th>
                            <th scope="col">Date</th>
                            <th scope="col">Total</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $recent_orders->fetch_assoc()): ?>
                            <tr>
                                <th scope="row" class="ps-3">#<?php echo (int) $order['order_id']; ?></th>
                                <td><?php echo htmlspecialchars(date('M j, Y', strtotime($order['order_date']))); ?></td>
                                <td><?php echo formatPrice($order['total_amount']); ?></td>
                                <td><span class="badge text-bg-<?php echo $status_classes[$order['order_status']] ?? 'secondary'; ?>"><?php echo htmlspecialchars($order['order_status']); ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/../footer.php'; ?>
