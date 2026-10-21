<?php
require_once 'db_connect.php';

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    redirect('products.php', 'No order specified.', 'error');
}

$order_id = intval($_GET['order_id']);

// Get order details
$stmt = $conn->prepare("SELECT o.*, u.full_name, u.email, u.phone 
                      FROM orders o 
                      JOIN users u ON o.user_id = u.user_id 
                      WHERE o.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    redirect('products.php', 'Order not found.', 'error');
}

// Get order items
$stmt = $conn->prepare("SELECT oi.*, p.product_name, p.product_image, s.shop_name 
                      FROM order_items oi 
                      JOIN products p ON oi.product_id = p.product_id 
                      JOIN shops s ON p.shop_id = s.shop_id 
                      WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get payment details
$stmt = $conn->prepare("SELECT * FROM payments WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

include 'header.php';
?>

<!-- Order Confirmation Page -->
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Success Message -->
            <div class="text-center mb-4">
                <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
                <h1 class="mb-2">Order Confirmed!</h1>
                <p class="lead text-muted">Thank you for your purchase. Your order has been successfully placed.</p>
            </div>

            <!-- Order Details Card -->
            <div class="card border-success">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Order Details</h5>
                    <span class="badge bg-light text-success">#<?php echo $order_id; ?></span>
                </div>
                <div class="card-body">
                    <!-- Order Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Order Information</h6>
                            <p class="mb-1"><strong>Order ID:</strong> #<?php echo $order_id; ?></p>
                            <p class="mb-1"><strong>Date:</strong> <?php echo date('F j, Y H:i', strtotime($order['order_date'])); ?></p>
                            <p class="mb-1"><strong>Status:</strong> 
                                <span class="badge bg-<?php 
                                    echo match($order['order_status']) {
                                        'Pending' => 'warning',
                                        'Processing' => 'info',
                                        'Shipped' => 'primary',
                                        'Delivered' => 'success',
                                        'Cancelled' => 'danger',
                                        default => 'secondary'
                                    };
                                ?>">
                                    <?php echo htmlspecialchars($order['order_status']); ?>
                                </span>
                            </p>
                            <p class="mb-0"><strong>Payment:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Customer Information</h6>
                            <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                            <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                            <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                        </div>
                    </div>

                    <!-- Shipping Address -->
                    <div class="alert alert-light border mb-4">
                        <h6 class="mb-2"><i class="fas fa-shipping-fast me-2"></i>Shipping Address</h6>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                    </div>

                    <!-- Payment Instructions -->
                    <?php if ($order['payment_method'] === 'Bank Transfer'): ?>
                        <div class="alert alert-info mb-4">
                            <h6 class="mb-2"><i class="fas fa-university me-2"></i>Bank Transfer Instructions</h6>
                            <p class="mb-1"><strong>Bank:</strong> Commercial Bank</p>
                            <p class="mb-1"><strong>Account Name:</strong> UNIBIZHUB Platform</p>
                            <p class="mb-1"><strong>Account Number:</strong> 1234567890</p>
                            <p class="mb-1"><strong>Branch:</strong> University Branch</p>
                            <p class="mb-0"><strong>Reference:</strong> Order #<?php echo $order_id; ?></p>
                            <hr class="my-2">
                            <p class="mb-0 text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Please complete your payment within 24 hours.</p>
                        </div>
                    <?php elseif ($order['payment_method'] === 'Credit Card'): ?>
                        <div class="alert alert-info mb-4">
                            <h6 class="mb-2"><i class="fas fa-credit-card me-2"></i>Card Payment</h6>
                            <p class="mb-0">Your card payment will be processed shortly. You will receive a confirmation email once the payment is confirmed.</p>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success mb-4">
                            <h6 class="mb-2"><i class="fas fa-money-bill-wave me-2"></i>Cash on Delivery</h6>
                            <p class="mb-0">Please have the exact amount ready: <strong><?php echo formatPrice($order['total_amount']); ?></strong></p>
                        </div>
                    <?php endif; ?>

                    <!-- Order Items -->
                    <h6 class="mb-3">Order Items</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo !empty($item['product_image']) ? $item['product_image'] : 'https://via.placeholder.com/40x40/f0f0f0/666666?text=No+Image'; ?>" 
                                                     class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                <div>
                                                    <div class="fw-bold small"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($item['shop_name']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center"><?php echo $item['quantity']; ?></td>
                                        <td class="text-end"><?php echo formatPrice($item['price']); ?></td>
                                        <td class="text-end fw-bold"><?php echo formatPrice($item['subtotal']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Subtotal</strong></td>
                                    <td class="text-end"><?php echo formatPrice($order['total_amount']); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Shipping</strong></td>
                                    <td class="text-end text-success">Free</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total</strong></td>
                                    <td class="text-end fw-bold text-success fs-5"><?php echo formatPrice($order['total_amount']); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="products.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                        </a>
                        <?php if (getUserRole() === 'Customer'): ?>
                            <a href="customer/dashboard.php" class="btn btn-outline-primary">
                                <i class="fas fa-user me-2"></i>My Orders
                            </a>
                        <?php endif; ?>
                        <button onclick="window.print()" class="btn btn-outline-secondary">
                            <i class="fas fa-print me-2"></i>Print Receipt
                        </button>
                    </div>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6><i class="fas fa-info-circle me-2 text-info"></i>What's Next?</h6>
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            <i class="fas fa-envelope fa-2x text-primary mb-2"></i>
                            <h6>Confirmation Email</h6>
                            <small class="text-muted">You will receive an order confirmation email shortly.</small>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <i class="fas fa-box fa-2x text-warning mb-2"></i>
                            <h6>Order Processing</h6>
                            <small class="text-muted">We'll prepare your order and notify you when it's ready.</small>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <i class="fas fa-shipping-fast fa-2x text-success mb-2"></i>
                            <h6>Delivery</h6>
                            <small class="text-muted">Your order will be delivered to your specified address.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Support -->
            <div class="text-center mt-4">
                <p class="text-muted">
                    Need help? <a href="contact.php">Contact our support team</a> or call <strong>+94 11 234 5678</strong>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
