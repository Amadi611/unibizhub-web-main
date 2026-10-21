<?php
require_once 'db_connect.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php?redirect=checkout.php', 'Please login to checkout.', 'warning');
}

// Check if cart is empty
$cart_items = getCartItems();
if (empty($cart_items)) {
    redirect('products.php', 'Your cart is empty!', 'warning');
}

// Get user data for pre-filling form
$user = getUserById($_SESSION['user_id']);
$customer_details = getCustomerDetails();

// Handle form submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    
    // Check if database has required columns
    $result = $conn->query("SHOW COLUMNS FROM orders LIKE 'payment_method'");
    if ($result->num_rows == 0) {
        // Add missing column
        $conn->query("ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) AFTER order_status");
    }
    
    $result = $conn->query("SHOW COLUMNS FROM payments WHERE Field = 'payment_method'");
    $row = $result->fetch_assoc();
    if ($row && strpos($row['Type'], 'Bank Transfer') === false) {
        // Update payment method enum
        $conn->query("ALTER TABLE payments MODIFY COLUMN payment_method ENUM('Credit Card', 'Debit Card', 'Cash on Delivery', 'Mobile Banking', 'Bank Transfer') NOT NULL");
    }
    
    // Validate and save customer details
    $required_fields = ['full_name', 'email', 'phone', 'address', 'city', 'postal_code', 'payment_method'];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
        }
    }
    
    // Validate terms acceptance - checkbox is considered checked if it exists in POST
    if (!isset($_POST['terms'])) {
        $errors[] = 'You must agree to the Terms & Conditions.';
    }
    
    if (empty($errors)) {
        // Save customer details to session
        $customer_data = [
            'full_name' => sanitize($_POST['full_name']),
            'email' => sanitize($_POST['email']),
            'phone' => sanitize($_POST['phone']),
            'address' => sanitize($_POST['address']),
            'city' => sanitize($_POST['city']),
            'postal_code' => sanitize($_POST['postal_code']),
            'notes' => sanitize($_POST['notes'] ?? '')
        ];
        saveCustomerDetails($customer_data);
        
        // Process payment method
        $payment_method = sanitize($_POST['payment_method']);
        
        // Create order
        $order_id = createOrder($_SESSION['user_id'], $customer_data, $payment_method);
        
        if ($order_id) {
            redirect('order-confirmation.php?order_id=' . $order_id, 'Order placed successfully!', 'success');
        } else {
            $errors[] = 'Failed to create order. Please check that all products are in stock and try again.';
            // Debug: log the error
            error_log('Order creation failed for user ' . $_SESSION['user_id']);
        }
    }
}

$cart_total = getCartTotal();

include 'header.php';
?>

<!-- Checkout Page -->
<div class="container py-5">
    <div class="row">
        <div class="col-12 mb-4">
            <h1 class="mb-3"><i class="fas fa-credit-card me-3 text-primary"></i>Checkout</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                    <li class="breadcrumb-item"><a href="cart.php">Cart</a></li>
                    <li class="breadcrumb-item active">Checkout</li>
                </ol>
            </nav>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>Please fix the following errors:
            <ul class="mb-0 mt-2">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
            <?php if (isset($_SESSION['order_error'])): ?>
                <hr>
                <small class="text-muted">Debug: <?php echo htmlspecialchars($_SESSION['order_error']); ?></small>
                <?php unset($_SESSION['order_error']); ?>
            <?php endif; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="row">
            <!-- Customer Details -->
            <div class="col-lg-8 mb-4">
                <!-- Contact Information -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Contact Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       value="<?php echo htmlspecialchars($customer_details['full_name'] ?? $user['full_name'] ?? ''); ?>" 
                                       required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($customer_details['email'] ?? $user['email'] ?? ''); ?>" 
                                       required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($customer_details['phone'] ?? $user['phone'] ?? ''); ?>" 
                                   placeholder="07X XXX XXXX" required>
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-shipping-fast me-2"></i>Shipping Address</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="address" class="form-label">Street Address *</label>
                            <textarea class="form-control" id="address" name="address" rows="2" 
                                      required><?php echo htmlspecialchars($customer_details['address'] ?? ''); ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City *</label>
                                <input type="text" class="form-control" id="city" name="city" 
                                       value="<?php echo htmlspecialchars($customer_details['city'] ?? ''); ?>" 
                                       required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="postal_code" class="form-label">Postal Code *</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code" 
                                       value="<?php echo htmlspecialchars($customer_details['postal_code'] ?? ''); ?>" 
                                       required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Order Notes (Optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2" 
                                      placeholder="Special instructions for delivery..."><?php echo htmlspecialchars($customer_details['notes'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Payment Method</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Cash on Delivery -->
                            <div class="col-md-4 mb-3">
                                <div class="form-check card h-100">
                                    <div class="card-body">
                                        <input class="form-check-input" type="radio" name="payment_method" 
                                               id="payment_cash" value="Cash on Delivery" checked>
                                        <label class="form-check-label w-100" for="payment_cash">
                                            <div class="text-center">
                                                <i class="fas fa-money-bill-wave fa-2x text-success mb-2"></i>
                                                <h6 class="mb-1">Cash on Delivery</h6>
                                                <small class="text-muted">Pay when you receive</small>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Credit/Debit Card -->
                            <div class="col-md-4 mb-3">
                                <div class="form-check card h-100">
                                    <div class="card-body">
                                        <input class="form-check-input" type="radio" name="payment_method" 
                                               id="payment_card" value="Credit Card">
                                        <label class="form-check-label w-100" for="payment_card">
                                            <div class="text-center">
                                                <i class="fas fa-credit-card fa-2x text-primary mb-2"></i>
                                                <h6 class="mb-1">Credit/Debit Card</h6>
                                                <small class="text-muted">Secure card payment</small>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Bank Transfer -->
                            <div class="col-md-4 mb-3">
                                <div class="form-check card h-100">
                                    <div class="card-body">
                                        <input class="form-check-input" type="radio" name="payment_method" 
                                               id="payment_bank" value="Bank Transfer">
                                        <label class="form-check-label w-100" for="payment_bank">
                                            <div class="text-center">
                                                <i class="fas fa-university fa-2x text-info mb-2"></i>
                                                <h6 class="mb-1">Bank Transfer</h6>
                                                <small class="text-muted">Direct bank transfer</small>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bank Details (shown when bank transfer selected) -->
                        <div id="bank_details" class="alert alert-info d-none">
                            <h6><i class="fas fa-university me-2"></i>Bank Transfer Details</h6>
                            <p class="mb-1"><strong>Bank:</strong> Commercial Bank</p>
                            <p class="mb-1"><strong>Account Name:</strong> UNIBIZHUB Platform</p>
                            <p class="mb-1"><strong>Account Number:</strong> 1234567890</p>
                            <p class="mb-1"><strong>Branch:</strong> University Branch</p>
                            <p class="mb-0"><strong>Note:</strong> Please use your Order ID as reference</p>
                        </div>

                        <!-- Card Payment Form (shown when card selected) -->
                        <div id="card_details" class="d-none">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>Card payment processing will be integrated with payment gateway.
                            </div>
                            <div class="mb-3">
                                <label for="card_number" class="form-label">Card Number</label>
                                <input type="text" class="form-control" id="card_number" placeholder="XXXX XXXX XXXX XXXX">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="card_expiry" class="form-label">Expiry Date</label>
                                    <input type="text" class="form-control" id="card_expiry" placeholder="MM/YY">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="card_cvv" class="form-label">CVV</label>
                                    <input type="text" class="form-control" id="card_cvv" placeholder="123">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="card_name" class="form-label">Name on Card</label>
                                <input type="text" class="form-control" id="card_name">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 100px;">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <!-- Cart Items -->
                        <div class="mb-3" style="max-height: 200px; overflow-y: auto;">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo !empty($item['product_image']) ? $item['product_image'] : 'https://via.placeholder.com/40x40/f0f0f0/666666?text=No+Image'; ?>" 
                                             class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                        <div>
                                            <small class="d-block fw-bold"><?php echo truncateText(htmlspecialchars($item['product_name']), 20); ?></small>
                                            <small class="text-muted">x<?php echo $item['quantity']; ?></small>
                                        </div>
                                    </div>
                                    <span class="fw-bold"><?php echo formatPrice($item['price'] * $item['quantity']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <hr>
                        
                        <!-- Totals -->
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span><?php echo formatPrice($cart_total); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping</span>
                            <span class="text-success">Free</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax</span>
                            <span>Included</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold fs-5">Total</span>
                            <span class="fw-bold fs-4 text-success"><?php echo formatPrice($cart_total); ?></span>
                        </div>

                        <!-- Terms -->
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms & Conditions</a>
                            </label>
                        </div>

                        <!-- Place Order Button -->
                        <button type="submit" name="place_order" class="btn btn-success w-100 btn-lg">
                            <i class="fas fa-check-circle me-2"></i>Place Order
                        </button>
                        
                        <!-- Back to Cart -->
                        <a href="cart.php" class="btn btn-outline-secondary w-100 mt-2">
                            <i class="fas fa-arrow-left me-2"></i>Back to Cart
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Terms & Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>1. Order Acceptance</h6>
                <p>All orders are subject to availability and confirmation of the order price.</p>
                
                <h6>2. Payment</h6>
                <p>We accept Cash on Delivery, Credit/Debit Card, and Bank Transfer payments.</p>
                
                <h6>3. Delivery</h6>
                <p>Delivery times are estimates and may vary based on location and availability.</p>
                
                <h6>4. Returns</h6>
                <p>Products can be returned within 7 days of delivery if defective or not as described.</p>
                
                <h6>5. Privacy</h6>
                <p>Your personal information is protected and will only be used for order processing.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Show/hide payment method details
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('bank_details').classList.add('d-none');
        document.getElementById('card_details').classList.add('d-none');
        
        if (this.value === 'Bank Transfer') {
            document.getElementById('bank_details').classList.remove('d-none');
        } else if (this.value === 'Credit Card') {
            document.getElementById('card_details').classList.remove('d-none');
        }
    });
});
</script>

<?php include 'footer.php'; ?>
