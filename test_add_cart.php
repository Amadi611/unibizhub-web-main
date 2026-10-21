<?php
require_once 'db_connect.php';

// Check products
$result = $conn->query("SELECT COUNT(*) as count FROM products WHERE status = 'Active'");
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Add sample products if none exist
    $conn->query("INSERT INTO shops (user_id, shop_name, description, status) VALUES (1, 'Admin Shop', 'Sample shop', 'Active') ON DUPLICATE KEY UPDATE shop_name=shop_name");
    
    $shop_id = 1;
    $conn->query("INSERT INTO products (shop_id, product_name, description, price, stock_quantity, status) VALUES 
        ($shop_id, 'Sample Product 1', 'This is a sample product for testing', 100.00, 10, 'Active'),
        ($shop_id, 'Sample Product 2', 'Another sample product', 243.00, 5, 'Active'),
        ($shop_id, 'Sample Product 3', 'Third sample product', 343.00, 8, 'Active')");
    
    echo "Sample products added!<br>";
}

// Show products
$result = $conn->query("SELECT p.*, s.shop_name FROM products p JOIN shops s ON p.shop_id = s.shop_id WHERE p.status = 'Active'");
echo "<h3>Products:</h3>";
while ($row = $result->fetch_assoc()) {
    echo "- {$row['product_name']} (Stock: {$row['stock_quantity']}, Price: {$row['price']})<br>";
}

// Test add to cart
if (isset($_GET['test_add'])) {
    $result = $conn->query("SELECT product_id FROM products WHERE status = 'Active' LIMIT 1");
    $product = $result->fetch_assoc();
    
    if ($product && addToCart($product['product_id'], 1)) {
        echo "<br><strong>Test: Product added to cart successfully!</strong><br>";
        echo "Cart count: " . getCartCount() . "<br>";
        echo "Cart total: " . getCartTotal() . "<br>";
    } else {
        echo "<br><strong>Test: Failed to add product to cart</strong><br>";
    }
}

echo "<br><a href='?test_add=1'>Test Add to Cart</a> | <a href='products.php'>View Products</a>";
?>
