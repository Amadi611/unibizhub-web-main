<?php
require_once 'db_connect.php';

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Get filter parameters
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Get categories for sidebar
$categories = getAllCategories();

// Get products with filters
$products = getProducts($limit, $offset, $category_id, $search);

// Get total products for pagination
$total_products = countProducts($category_id, $search);
$total_pages = ceil($total_products / $limit);

// Get current category name for display
$current_category = null;
if ($category_id) {
    foreach ($categories as $cat) {
        if ($cat['category_id'] == $category_id) {
            $current_category = $cat['category_name'];
            break;
        }
    }
}
?>

<?php include 'header.php'; ?>

<!-- Page Header -->
<section class="bg-light py-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="fw-bold mb-0">
                    <?php if ($current_category): ?>
                        <?php echo htmlspecialchars($current_category); ?>
                    <?php elseif ($search): ?>
                        Search Results: "<?php echo htmlspecialchars($search); ?>"
                    <?php else: ?>
                        All Products
                    <?php endif; ?>
                </h1>
                <p class="text-muted mb-0">
                    <?php echo number_format($total_products); ?> products found
                </p>
            </div>
            <div class="col-md-4">
                <div class="d-flex gap-2 justify-content-md-end mt-3 mt-md-0">
                    <select class="form-select" id="sortSelect">
                        <option value="newest">Newest First</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                        <option value="name">Name: A to Z</option>
                    </select>
                    <select class="form-select" id="limitSelect">
                        <option value="12">12 per page</option>
                        <option value="24">24 per page</option>
                        <option value="48">48 per page</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Products Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="card sticky-top" style="top: 100px;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
                    </div>
                    <div class="card-body">
                        <!-- Search -->
                        <div class="mb-4">
                            <h6 class="mb-3">Search Products</h6>
                            <form method="GET" action="products.php">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" 
                                           value="<?php echo htmlspecialchars($search); ?>" 
                                           placeholder="Search products...">
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <?php if ($category_id): ?>
                                    <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                                <?php endif; ?>
                            </form>
                        </div>
                        
                        <!-- Categories -->
                        <div class="mb-4">
                            <h6 class="mb-3">Categories</h6>
                            <div class="list-group list-group-flush">
                                <a href="products.php" 
                                   class="list-group-item list-group-item-action <?php echo !$category_id ? 'active' : ''; ?>">
                                    <i class="fas fa-th me-2"></i>All Categories
                                </a>
                                <?php foreach ($categories as $category): ?>
                                    <a href="products.php?category=<?php echo $category['category_id']; ?>" 
                                       class="list-group-item list-group-item-action <?php echo $category_id == $category['category_id'] ? 'active' : ''; ?>">
                                        <i class="fas fa-tag me-2"></i>
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                        <span class="badge bg-secondary float-end">
                                            <?php 
                                            // Count products in this category
                                            $cat_count = countProducts($category['category_id']);
                                            echo $cat_count;
                                            ?>
                                        </span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Price Range -->
                        <div class="mb-4">
                            <h6 class="mb-3">Price Range</h6>
                            <form method="GET" action="products.php">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" class="form-control" name="min_price" 
                                               placeholder="Min" min="0" step="0.01">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" class="form-control" name="max_price" 
                                               placeholder="Max" min="0" step="0.01">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-sm btn-outline-primary mt-2 w-100">
                                    Apply Price Filter
                                </button>
                                <?php if ($category_id): ?>
                                    <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                                <?php endif; ?>
                                <?php if ($search): ?>
                                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                                <?php endif; ?>
                            </form>
                        </div>
                        
                        <!-- Stock Status -->
                        <div class="mb-4">
                            <h6 class="mb-3">Availability</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="inStockOnly">
                                <label class="form-check-label" for="inStockOnly">
                                    In Stock Only
                                </label>
                            </div>
                        </div>
                        
                        <!-- Clear Filters -->
                        <div class="d-grid">
                            <a href="products.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Clear All Filters
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="col-lg-9">
                <?php if (empty($products)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-4x text-muted mb-3"></i>
                        <h4>No products found</h4>
                        <p class="text-muted">
                            <?php if ($search || $category_id): ?>
                                Try adjusting your filters or search terms.
                            <?php else: ?>
                                No products are available at the moment. Check back soon!
                            <?php endif; ?>
                        </p>
                        <a href="products.php" class="btn btn-primary">
                            <i class="fas fa-redo me-2"></i>Clear Filters
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Active Filters Display -->
                    <?php if ($search || $category_id): ?>
                        <div class="alert alert-info mb-4">
                            <strong>Active Filters:</strong>
                            <?php if ($search): ?>
                                <span class="badge bg-primary me-2">
                                    Search: "<?php echo htmlspecialchars($search); ?>"
                                    <a href="products.php?<?php echo $category_id ? 'category=' . $category_id : ''; ?>" 
                                       class="text-white text-decoration-none ms-1">×</a>
                                </span>
                            <?php endif; ?>
                            <?php if ($current_category): ?>
                                <span class="badge bg-primary me-2">
                                    Category: <?php echo htmlspecialchars($current_category); ?>
                                    <a href="products.php?<?php echo $search ? 'search=' . urlencode($search) : ''; ?>" 
                                       class="text-white text-decoration-none ms-1">×</a>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Products Grid -->
                    <div class="row g-4">
                        <?php foreach ($products as $product): ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="card product-card h-100">
                                    <?php if ($product['stock_quantity'] <= 5 && $product['stock_quantity'] > 0): ?>
                                        <div class="product-badge">Low Stock</div>
                                    <?php elseif ($product['stock_quantity'] == 0): ?>
                                        <div class="product-badge bg-danger">Out of Stock</div>
                                    <?php endif; ?>
                                    
                                    <div class="position-relative overflow-hidden">
                                        <img src="<?php echo !empty($product['product_image']) ? $product['product_image'] : 'https://via.placeholder.com/300x200/f0f0f0/666666?text=No+Image'; ?>" 
                                             class="card-img-top" 
                                             alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                                             style="height: 200px; object-fit: cover;">
                                        
                                        <!-- Quick Actions -->
                                        <div class="position-absolute top-0 end-0 p-2">
                                            <button class="btn btn-sm btn-light rounded-circle me-1" 
                                                    onclick="toggleWishlist(<?php echo $product['product_id']; ?>)">
                                                <i class="far fa-heart"></i>
                                            </button>
                                            <button class="btn btn-sm btn-light rounded-circle" 
                                                    onclick="quickView(<?php echo $product['product_id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body">
                                        <div class="mb-2">
                                            <span class="badge bg-primary">
                                                <?php echo htmlspecialchars($product['category_name'] ?: 'Uncategorized'); ?>
                                            </span>
                                        </div>
                                        
                                        <h5 class="card-title"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                                        <p class="card-text text-muted small">
                                            <?php echo truncateText($product['description'], 80); ?>
                                        </p>
                                        
                                        <div class="product-price mb-2">
                                            <?php echo formatPrice($product['price']); ?>
                                        </div>
                                        
                                        <div class="product-seller mb-3">
                                            <i class="fas fa-store me-1"></i>
                                            <a href="#" class="text-decoration-none">
                                                <?php echo htmlspecialchars($product['seller_name']); ?>
                                            </a>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="text-muted small">
                                                <?php echo $product['stock_quantity']; ?> in stock
                                            </span>
                                            <div class="rating-stars">
                                                <?php
                                                // Generate random rating for demo
                                                $rating = rand(3, 5);
                                                for ($i = 1; $i <= 5; $i++) {
                                                    if ($i <= $rating) {
                                                        echo '<i class="fas fa-star"></i>';
                                                    } else {
                                                        echo '<i class="far fa-star empty"></i>';
                                                    }
                                                }
                                                ?>
                                                <small class="text-muted">(<?php echo rand(5, 50); ?>)</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-footer bg-transparent">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <button class="btn btn-outline-primary btn-sm w-100" 
                                                        onclick="quickView(<?php echo $product['product_id']; ?>)">
                                                    <i class="fas fa-eye me-1"></i>View
                                                </button>
                                            </div>
                                            <div class="col-6">
                                                <form method="POST" action="cart.php" class="d-inline">
                                                    <input type="hidden" name="action" value="add">
                                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <button type="submit" class="btn btn-primary btn-sm w-100" 
                                                            <?php echo $product['stock_quantity'] == 0 ? 'disabled' : ''; ?>>
                                                        <i class="fas fa-shopping-cart me-1"></i>
                                                        <?php echo $product['stock_quantity'] == 0 ? 'Out of Stock' : 'Add to Cart'; ?>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="d-flex justify-content-center mt-5">
                            <?php
                            $base_url = "products.php";
                            $params = [];
                            if ($category_id) $params[] = "category=$category_id";
                            if ($search) $params[] = "search=" . urlencode($search);
                            $base_url .= !empty($params) ? '?' . implode('&', $params) : '';
                            
                            echo generatePagination($total_pages, $page, $base_url);
                            ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Quick View Modal -->
<div class="modal fade" id="quickViewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Product Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="quickViewContent">
                <!-- Content will be loaded via JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
// Sort functionality
document.getElementById('sortSelect').addEventListener('change', function() {
    const sort = this.value;
    const url = new URL(window.location);
    url.searchParams.set('sort', sort);
    window.location.href = url.toString();
});

// Limit functionality
document.getElementById('limitSelect').addEventListener('change', function() {
    const limit = this.value;
    const url = new URL(window.location);
    url.searchParams.set('limit', limit);
    url.searchParams.set('page', '1'); // Reset to first page
    window.location.href = url.toString();
});

// Add to cart function
function addToCart(productId) {
    // This would typically make an AJAX call to add to cart
    // For now, just show a message
    alert('Product added to cart! (This would normally add to your shopping cart)');
}

// Toggle wishlist function
function toggleWishlist(productId) {
    // This would typically make an AJAX call to toggle wishlist
    const icon = event.currentTarget.querySelector('i');
    if (icon.classList.contains('far')) {
        icon.classList.remove('far');
        icon.classList.add('fas');
        icon.style.color = '#ff6b35';
    } else {
        icon.classList.remove('fas');
        icon.classList.add('far');
        icon.style.color = '';
    }
}

// Quick view function
function quickView(productId) {
    // This would typically load product details via AJAX
    // For now, just show a placeholder
    const content = `
        <div class="row">
            <div class="col-md-6">
                <img src="https://via.placeholder.com/400x400/f0f0f0/666666?text=Product+Image" 
                     class="img-fluid rounded" alt="Product Image">
            </div>
            <div class="col-md-6">
                <h4>Product Name</h4>
                <p class="text-muted">Detailed product description would appear here.</p>
                <div class="h3 text-primary mb-3">Rs999.99</div>
                <div class="mb-3">
                    <label class="form-label">Quantity:</label>
                    <input type="number" class="form-control" value="1" min="1" style="width: 100px;">
                </div>
                <div class="d-grid gap-2">
                    <button class="btn btn-primary">
                        <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                    </button>
                    <button class="btn btn-outline-primary">
                        <i class="fas fa-heart me-2"></i>Add to Wishlist
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('quickViewContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('quickViewModal')).show();
}

// In stock filter
document.getElementById('inStockOnly').addEventListener('change', function() {
    const url = new URL(window.location);
    if (this.checked) {
        url.searchParams.set('in_stock', '1');
    } else {
        url.searchParams.delete('in_stock');
    }
    window.location.href = url.toString();
});

// Auto-apply filters
document.querySelectorAll('input[name="min_price"], input[name="max_price"]').forEach(input => {
    input.addEventListener('change', function() {
        const form = this.closest('form');
        const minPrice = form.querySelector('input[name="min_price"]').value;
        const maxPrice = form.querySelector('input[name="max_price"]').value;
        
        if (minPrice || maxPrice) {
            form.submit();
        }
    });
});
</script>

<?php include 'footer.php'; ?>
