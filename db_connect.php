<?php
/**
 * Database Connection Configuration
 * UNIBIZHUB - Entrepreneur Online Business Platform
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'unibizhub');
define('DB_USER', 'root');
define('DB_PASS', '');

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for better Unicode support
$conn->set_charset("utf8mb4");

// Error reporting settings
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration for security
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Function to sanitize user input
 */
function sanitize($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

/**
 * Function to hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Function to verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Function to check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Function to check user role
 */
function getUserRole() {
    return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
}

/**
 * Function to redirect with message
 */
function redirect($url, $message = '', $type = '') {
    if (!empty($message)) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $type;
    }
    header("Location: $url");
    exit();
}

/**
 * Function to display messages
 */
function displayMessage() {
    if (isset($_SESSION['message'])) {
        $type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info';
        $message = $_SESSION['message'];
        
        $alertClass = '';
        switch($type) {
            case 'success':
                $alertClass = 'alert-success';
                break;
            case 'error':
                $alertClass = 'alert-danger';
                break;
            case 'warning':
                $alertClass = 'alert-warning';
                break;
            default:
                $alertClass = 'alert-info';
        }
        
        echo "<div class='alert $alertClass alert-dismissible fade show' role='alert'>
                $message
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
        
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}

/**
 * Function to get user data by ID
 */
function getUserById($user_id) {
    global $conn;
    $sql = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Function to get shop data by user ID
 */
function getShopByUserId($user_id) {
    global $conn;
    $sql = "SELECT * FROM shops WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Function to get all categories
 */
function getAllCategories() {
    global $conn;
    $sql = "SELECT * FROM categories ORDER BY category_name";
    $result = $conn->query($sql);
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    return $categories;
}

/**
 * Function to get products with pagination
 */
function getProducts($limit = 12, $offset = 0, $category_id = null, $search = '') {
    global $conn;
    
    $sql = "SELECT p.*, s.shop_name, u.full_name as seller_name, c.category_name 
            FROM products p 
            JOIN shops s ON p.shop_id = s.shop_id 
            JOIN users u ON s.user_id = u.user_id 
            LEFT JOIN categories c ON p.category_id = c.category_id 
            WHERE p.status = 'Active' AND s.status = 'Active'";
    
    if ($category_id) {
        $sql .= " AND p.category_id = ?";
    }
    
    if ($search) {
        $sql .= " AND (p.product_name LIKE ? OR p.description LIKE ?)";
    }
    
    $sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    
    $params = [];
    $types = '';
    
    if ($category_id) {
        $params[] = $category_id;
        $types .= 'i';
    }
    
    if ($search) {
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= 'ss';
    }
    
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $products = [];
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

/**
 * Function to count total products
 */
function countProducts($category_id = null, $search = '') {
    global $conn;
    
    $sql = "SELECT COUNT(*) as total 
            FROM products p 
            JOIN shops s ON p.shop_id = s.shop_id 
            WHERE p.status = 'Active' AND s.status = 'Active'";
    
    if ($category_id) {
        $sql .= " AND p.category_id = ?";
    }
    
    if ($search) {
        $sql .= " AND (p.product_name LIKE ? OR p.description LIKE ?)";
    }
    
    $stmt = $conn->prepare($sql);
    
    $params = [];
    $types = '';
    
    if ($category_id) {
        $params[] = $category_id;
        $types .= 'i';
    }
    
    if ($search) {
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= 'ss';
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['total'];
}

/**
 * Function to get entrepreneurs (student sellers)
 */
function getEntrepreneurs($limit = 12, $offset = 0) {
    global $conn;
    
    $sql = "SELECT u.*, s.shop_name, s.shop_image, s.description as shop_description, c.category_name 
            FROM users u 
            JOIN shops s ON u.user_id = s.user_id 
            LEFT JOIN categories c ON s.category_id = c.category_id 
            WHERE u.role = 'Entrepreneur' AND u.status = 'Active' AND s.status = 'Active' 
            ORDER BY u.created_at DESC 
            LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    
    $entrepreneurs = [];
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $entrepreneurs[] = $row;
    }
    
    return $entrepreneurs;
}

/**
 * Function to count total entrepreneurs
 */
function countEntrepreneurs() {
    global $conn;
    
    $sql = "SELECT COUNT(*) as total 
            FROM users u 
            JOIN shops s ON u.user_id = s.user_id 
            WHERE u.role = 'Entrepreneur' AND u.status = 'Active' AND s.status = 'Active'";
    
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    return $row['total'];
}

/**
 * Function to format price
 */
function formatPrice($price) {
    return 'Rs' . number_format((float)($price ?? 0), 2);
}

/**
 * Function to truncate text
 */
function truncateText($text, $length = 100) {
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . '...';
    }
    return $text;
}

/**
 * Function to generate pagination
 */
function generatePagination($total_pages, $current_page, $base_url) {
    $pagination = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Previous button
    if ($current_page > 1) {
        $pagination .= '<li class="page-item"><a class="page-link" href="' . $base_url . '?page=' . ($current_page - 1) . '">Previous</a></li>';
    } else {
        $pagination .= '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
    }
    
    // Page numbers
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $current_page) {
            $pagination .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $pagination .= '<li class="page-item"><a class="page-link" href="' . $base_url . '?page=' . $i . '">' . $i . '</a></li>';
        }
    }
    
    // Next button
    if ($current_page < $total_pages) {
        $pagination .= '<li class="page-item"><a class="page-link" href="' . $base_url . '?page=' . ($current_page + 1) . '">Next</a></li>';
    } else {
        $pagination .= '<li class="page-item disabled"><span class="page-link">Next</span></li>';
    }
    
    $pagination .= '</ul></nav>';
    
    return $pagination;
}

/**
 * Cart Functions
 */

/**
 * Function to add product to cart
 */
function addToCart($product_id, $quantity = 1) {
    global $conn;
    
    // Get product details
    $stmt = $conn->prepare("SELECT p.*, s.shop_name, u.user_id as seller_id, u.full_name as seller_name 
                          FROM products p 
                          JOIN shops s ON p.shop_id = s.shop_id 
                          JOIN users u ON s.user_id = u.user_id 
                          WHERE p.product_id = ? AND p.status = 'Active'");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    
    if (!$product) {
        return false;
    }
    
    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Check if product already in cart
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = [
            'product_id' => $product_id,
            'product_name' => $product['product_name'],
            'price' => $product['price'],
            'product_image' => $product['product_image'],
            'shop_name' => $product['shop_name'],
            'seller_id' => $product['seller_id'],
            'seller_name' => $product['seller_name'],
            'quantity' => $quantity
        ];
    }
    
    return true;
}

/**
 * Function to remove product from cart
 */
function removeFromCart($product_id) {
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        return true;
    }
    return false;
}

/**
 * Function to update cart quantity
 */
function updateCartQuantity($product_id, $quantity) {
    if (isset($_SESSION['cart'][$product_id])) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
        }
        return true;
    }
    return false;
}

/**
 * Function to get cart items
 */
function getCartItems() {
    return isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
}

/**
 * Function to get cart total
 */
function getCartTotal() {
    $total = 0;
    $cart = getCartItems();
    foreach ($cart as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

/**
 * Function to get cart count
 */
function getCartCount() {
    $count = 0;
    $cart = getCartItems();
    foreach ($cart as $item) {
        $count += $item['quantity'];
    }
    return $count;
}

/**
 * Function to clear cart
 */
function clearCart() {
    $_SESSION['cart'] = [];
}

/**
 * Function to save customer details to session
 */
function saveCustomerDetails($data) {
    $_SESSION['customer_details'] = [
        'full_name' => $data['full_name'],
        'email' => $data['email'],
        'phone' => $data['phone'],
        'address' => $data['address'],
        'city' => $data['city'],
        'postal_code' => $data['postal_code'],
        'notes' => $data['notes'] ?? ''
    ];
}

/**
 * Function to get customer details from session
 */
function getCustomerDetails() {
    return isset($_SESSION['customer_details']) ? $_SESSION['customer_details'] : [];
}

/**
 * Function to create order
 */
function createOrder($user_id, $customer_details, $payment_method) {
    global $conn;
    
    $cart = getCartItems();
    if (empty($cart)) {
        return false;
    }
    
    // Validate required customer details
    $required = ['address', 'city', 'postal_code', 'full_name'];
    foreach ($required as $field) {
        if (empty($customer_details[$field])) {
            return false;
        }
    }
    
    $total_amount = getCartTotal();
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Create main order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, order_status, shipping_address, billing_address, payment_method) 
                              VALUES (?, ?, 'Pending', ?, ?, ?)");
        
        $shipping_address = $customer_details['address'] . ', ' . $customer_details['city'] . ' ' . $customer_details['postal_code'];
        $billing_address = $shipping_address;
        
        $stmt->bind_param("idsss", $user_id, $total_amount, $shipping_address, $billing_address, $payment_method);
        
        if (!$stmt->execute()) {
            throw new Exception("Order insert failed: " . $stmt->error);
        }
        
        $order_id = $conn->insert_id;
        
        if (!$order_id) {
            throw new Exception("Failed to get order ID");
        }
        
        // Create order items
        foreach ($cart as $item) {
            $subtotal = $item['price'] * $item['quantity'];
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) 
                                  VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iiidd", $order_id, $item['product_id'], $item['quantity'], $item['price'], $subtotal);
            
            if (!$stmt->execute()) {
                throw new Exception("Order item insert failed: " . $stmt->error);
            }
            
            // Update product stock
            $stmt = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ? AND stock_quantity >= ?");
            $stmt->bind_param("iii", $item['quantity'], $item['product_id'], $item['quantity']);
            
            if (!$stmt->execute()) {
                throw new Exception("Stock update failed: " . $stmt->error);
            }
            
            if ($stmt->affected_rows == 0) {
                throw new Exception("Insufficient stock for product ID: " . $item['product_id']);
            }
        }
        
        // Create payment record
        $stmt = $conn->prepare("INSERT INTO payments (order_id, payment_method, payment_status, amount) 
                              VALUES (?, ?, 'Pending', ?)");
        $stmt->bind_param("isd", $order_id, $payment_method, $total_amount);
        
        if (!$stmt->execute()) {
            throw new Exception("Payment insert failed: " . $stmt->error);
        }
        
        $conn->commit();
        
        // Clear cart and customer details
        clearCart();
        unset($_SESSION['customer_details']);
        
        return $order_id;
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Order creation failed: " . $e->getMessage());
        // Store error in session for display
        $_SESSION['order_error'] = $e->getMessage();
        return false;
    }
}
?>
