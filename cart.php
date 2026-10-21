<?php
require_once 'db_connect.php';

// Handle cart actions
if (isset($_POST['action'])) {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    
    switch ($_POST['action']) {
        case 'add':
            $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
            if (addToCart($product_id, $quantity)) {
                redirect('cart.php', 'Product added to cart!', 'success');
            } else {
                redirect('products.php', 'Failed to add product to cart.', 'error');
            }
            break;
            
        case 'update':
            $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
            updateCartQuantity($product_id, $quantity);
            redirect('cart.php', 'Cart updated!', 'success');
            break;
            
        case 'remove':
            removeFromCart($product_id);
            redirect('cart.php', 'Product removed from cart!', 'success');
            break;
            
        case 'clear':
            clearCart();
            redirect('cart.php', 'Cart cleared!', 'success');
            break;
    }
}

// Get cart items
$cart_items = getCartItems();
$cart_total = getCartTotal();
$cart_count = getCartCount();

include 'header.php';
?>

<!-- Cart Page -->
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4"><i class="fas fa-shopping-cart me-3 text-primary"></i>Shopping Cart</h1>
        </div>
    </div>

    <?php if (empty($cart_items)): ?>
        <!-- Empty Cart -->
        <div class="row">
            <div class="col-12 text-center py-5">
                <i class="fas fa-shopping-cart fa-5x text-muted mb-4"></i>
                <h3 class="text-muted">Your cart is empty</h3>
                <p class="text-muted">Looks like you haven't added any products to your cart yet.</p>
                <a href="products.php" class="btn btn-primary btn-lg mt-3">
                    <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Cart with Items -->
        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-box me-2"></i>Cart Items (<?php echo $cart_count; ?>)</h5>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="btn btn-sm btn-outline-light" onclick="return confirm('Clear entire cart?')">
                                <i class="fas fa-trash me-1"></i>Clear Cart
                            </button>
                        </form>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 100px;">Product</th>
                                        <th>Details</th>
                                        <th style="width: 150px;">Quantity</th>
                                        <th style="width: 120px;">Price</th>
                                        <th style="width: 100px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart_items as $item): ?>
                                        <tr>
                                            <td>
                                                <img src="<?php echo !empty($item['product_image']) ? $item['product_image'] : 'https://via.placeholder.com/80x80/f0f0f0/666666?text=No+Image'; ?>" 
                                                     class="img-fluid rounded" style="width: 80px; height: 80px; object-fit: cover;"
                                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                            </td>
                                            <td>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-store me-1"></i><?php echo htmlspecialchars($item['shop_name']); ?>
                                                </small><br>
                                                <small class="text-muted">
                                                    <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($item['seller_name']); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <form method="POST" class="d-flex align-items-center">
                                                    <input type="hidden" name="action" value="update">
                                                    <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                                    <div class="input-group input-group-sm" style="width: 120px;">
                                                        <button type="button" class="btn btn-outline-secondary" 
                                                                onclick="this.parentNode.querySelector('input').stepDown(); this.form.submit();">-</button>
                                                        <input type="number" name="quantity" class="form-control text-center" 
                                                               value="<?php echo $item['quantity']; ?>" min="1" 
                                                               onchange="this.form.submit()">
                                                        <button type="button" class="btn btn-outline-secondary" 
                                                                onclick="this.parentNode.querySelector('input').stepUp(); this.form.submit();">+</button>
                                                    </div>
                                                </form>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-primary"><?php echo formatPrice($item['price'] * $item['quantity']); ?></div>
                                                <small class="text-muted"><?php echo formatPrice($item['price']); ?> each</small>
                                            </td>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="remove">
                                                    <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                            onclick="return confirm('Remove this item?')"
                                                            title="Remove">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Continue Shopping -->
                <div class="mt-3">
                    <a href="products.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                    </a>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 100px;">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal (<?php echo $cart_count; ?> items)</span>
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
                            <span class="fw-bold">Total</span>
                            <span class="fw-bold fs-4 text-primary"><?php echo formatPrice($cart_total); ?></span>
                        </div>
                        
                        <?php if (isLoggedIn()): ?>
                            <a href="checkout.php" class="btn btn-success w-100 btn-lg">
                                <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                            </a>
                        <?php else: ?>
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle me-2"></i>Please login to checkout
                            </div>
                            <a href="login.php?redirect=cart.php" class="btn btn-primary w-100 btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Login to Continue
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
