<?php
require_once 'db_connect.php';

// Check if payment_method column exists in orders table
echo "<h3>Database Schema Check</h3>";

// Check orders table
$result = $conn->query("DESCRIBE orders");
echo "<h4>Orders Table:</h4>";
echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
}
echo "</table>";

// Check payments table
$result = $conn->query("DESCRIBE payments");
echo "<h4>Payments Table:</h4>";
echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
}
echo "</table>";

// Check if user is logged in and cart has items
echo "<h4>Session Check:</h4>";
echo "Logged in: " . (isLoggedIn() ? "Yes" : "No") . "<br>";
if (isLoggedIn()) {
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
}
echo "Cart items: " . count(getCartItems()) . "<br>";
echo "Cart total: " . getCartTotal() . "<br>";
?>
