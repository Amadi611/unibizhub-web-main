<?php
require_once 'db_connect.php';

echo "<h3>Products Check</h3>";

// Check if there are any products
$result = $conn->query("SELECT COUNT(*) as count FROM products WHERE status = 'Active'");
$row = $result->fetch_assoc();
echo "Active Products: " . $row['count'] . "<br>";

// Show sample products
$result = $conn->query("SELECT p.*, s.shop_name FROM products p JOIN shops s ON p.shop_id = s.shop_id WHERE p.status = 'Active' LIMIT 5");
echo "<h4>Sample Products:</h4>";
echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Price</th><th>Stock</th><th>Shop</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['product_id']}</td><td>{$row['product_name']}</td><td>{$row['price']}</td><td>{$row['stock_quantity']}</td><td>{$row['shop_name']}</td></tr>";
}
echo "</table>";

// Check shops
$result = $conn->query("SELECT COUNT(*) as count FROM shops WHERE status = 'Active'");
$row = $result->fetch_assoc();
echo "<br>Active Shops: " . $row['count'] . "<br>";

// Show sample shops
$result = $conn->query("SELECT s.*, u.full_name FROM shops s JOIN users u ON s.user_id = u.user_id WHERE s.status = 'Active' LIMIT 3");
echo "<h4>Sample Shops:</h4>";
echo "<table border='1'><tr><th>ID</th><th>Shop Name</th><th>Owner</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['shop_id']}</td><td>{$row['shop_name']}</td><td>{$row['full_name']}</td></tr>";
}
echo "</table>";
?>
