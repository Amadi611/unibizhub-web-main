<?php
require_once 'db_connect.php';

$sql = "SELECT product_name, product_image FROM products WHERE product_name LIKE '%handfree%' OR product_name LIKE '%Handfree%'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo 'Product: ' . $row['product_name'] . ' | Image: ' . $row['product_image'] . PHP_EOL;
    }
} else {
    echo 'No handfree product found' . PHP_EOL;
}
?>
