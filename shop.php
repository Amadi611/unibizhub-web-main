<?php
require_once 'db_connect.php';

// Get user_id from URL
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($user_id === 0) {
    redirect('entrepreneurs.php', 'Invalid shop ID', 'error');
}

// Get shop and user information
$sql = "SELECT s.*, u.email, u.full_name, u.created_at as user_created_at
        FROM shops s 
        JOIN users u ON s.user_id = u.user_id 
        WHERE s.user_id = ? AND s.status = 'Active'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect('entrepreneurs.php', 'Shop not found or not active', 'error');
}

$shop = $result->fetch_assoc();

// Get shop products
$products = [];
$sql = "SELECT p.*, c.category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        WHERE p.shop_id = ? AND p.status = 'Active' 
        ORDER BY p.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $shop['shop_id']);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get shop statistics
$total_products = count($products);
$total_sales = 0; // This would need to be calculated from orders table
$avg_rating = 0; // This would need to be calculated from reviews table
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($shop['shop_name']); ?> - UNIBIZHUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container py-4">
        <!-- Shop Header -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="mb-2"><?php echo htmlspecialchars($shop['shop_name']); ?></h1>
                        <p class="text-muted mb-3"><?php echo htmlspecialchars($shop['description']); ?></p>
                        <div class="d-flex flex-wrap gap-3 mb-3">
                            <div class="text-center">
                                <div class="fw-bold text-primary"><?php echo $total_products; ?></div>
                                <small class="text-muted">Products</small>
                            </div>
                            <div class="text-center">
                                <div class="fw-bold text-success"><?php echo $total_sales; ?></div>
                                <small class="text-muted">Sales</small>
                            </div>
                            <div class="text-center">
                                <div class="fw-bold text-warning"><?php echo $avg_rating > 0 ? number_format($avg_rating, 1) : 'N/A'; ?></div>
                                <small class="text-muted">Rating</small>
                            </div>
                            <div class="text-center">
                                <div class="fw-bold text-info"><?php echo date('M d, Y', strtotime($shop['user_created_at'])); ?></div>
                                <small class="text-muted">Joined</small>
                            </div>
                        </div>
                        <?php if (!empty($shop['category_id'])): ?>
                            <div>
                                <span class="badge bg-secondary">
                                    <i class="fas fa-tag me-1"></i>
                                    <?php 
                                    $categories = getAllCategories();
                                    foreach ($categories as $category) {
                                        if ($category['category_id'] == $shop['category_id']) {
                                            echo htmlspecialchars($category['category_name']);
                                            break;
                                        }
                                    }
                                    ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <?php if (!empty($shop['shop_image'])): ?>
                            <img src="<?php echo htmlspecialchars($shop['shop_image']); ?>" alt="<?php echo htmlspecialchars($shop['shop_name']); ?> shop" class="img-fluid rounded mb-3" style="max-height: 180px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="d-flex flex-column gap-2">
                            <button class="btn btn-primary" onclick="window.location.href='contact.php?shop_id=<?php echo $shop['shop_id']; ?>'">
                                <i class="fas fa-envelope me-2"></i>Contact Shop
                            </button>
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i>
                                <?php echo htmlspecialchars($shop['full_name']); ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Section -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-box me-2"></i>Products
                    <?php if ($total_products > 0): ?>
                        <span class="badge bg-primary ms-2"><?php echo $total_products; ?></span>
                    <?php endif; ?>
                </h4>
            </div>
            <div class="card-body">
                <?php if (empty($products)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-box fa-4x text-muted mb-3"></i>
                        <h4>No Products Available</h4>
                        <p class="text-muted">This shop hasn't added any products yet.</p>
                    </div>
                <?php else: ?>
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
                        <?php foreach ($products as $product): ?>
                            <div class="col">
                                <div class="card h-100">
                                    <?php if (!empty($product['product_image'])): ?>
                                        <img src="<?php echo htmlspecialchars($product['product_image']); ?>" 
                                             class="card-img-top" 
                                             style="height: 200px; object-fit: cover;"
                                             alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                                             onerror="this.src='https://via.placeholder.com/300x200/f0f0f0/666666?text=No+Image';">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/300x200/f0f0f0/666666?text=No+Image" 
                                             class="card-img-top" 
                                             style="height: 200px; object-fit: cover;"
                                             alt="No image">
                                    <?php endif; ?>
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                                        <p class="card-text text-muted small"><?php echo truncateText($product['description'] ?? '', 100); ?></p>
                                        
                                        <?php if (!empty($product['category_name'])): ?>
                                            <div class="mb-2">
                                                <span class="badge bg-light text-dark">
                                                    <?php echo htmlspecialchars($product['category_name']); ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="mt-auto">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="fw-bold text-primary"><?php echo formatPrice($product['price'] ?? 0); ?></span>
                                                <span class="badge bg-<?php echo ($product['stock_quantity'] ?? 0) > 10 ? 'success' : (($product['stock_quantity'] ?? 0) > 0 ? 'warning' : 'danger'); ?>">
                                                    <?php echo $product['stock_quantity'] ?? 0; ?> in stock
                                                </span>
                                            </div>
                                            
                                            <button class="btn btn-primary btn-sm w-100" 
                                                    onclick="addToCart(<?php echo $product['product_id']; ?>)">
                                                <i class="fas fa-shopping-cart me-1"></i>Add to Cart
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-4">
            <a href="entrepreneurs.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Entrepreneurs
            </a>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function addToCart(productId) {
            // This would need to be implemented based on your cart system
            alert('Add to cart functionality would be implemented here for product ID: ' + productId);
        }
    </script>
</body>
</html>
