<?php
require_once 'db_connect.php';

echo "<h2>Admin Password Reset Tool</h2>";

// Check if admin exists
$sql = "SELECT user_id, full_name, email, role, status FROM users WHERE role = 'Admin'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    echo "<p><strong>Current admin found:</strong></p>";
    echo "<pre>";
    echo "Email: " . $admin['email'] . "\n";
    echo "Full Name: " . $admin['full_name'] . "\n";
    echo "Status: " . $admin['status'] . "\n";
    echo "</pre>";

    // Reset password
    $new_password = "admin123";
    $password_hash = hashPassword($new_password);
    
    $stmt = $conn->prepare("UPDATE users SET password = ?, status = 'Active' WHERE role = 'Admin'");
    $stmt->bind_param("s", $password_hash);
    
    if ($stmt->execute()) {
        echo "<p style='color: green; font-size: 18px;'><strong>✓ Admin password has been reset!</strong></p>";
        echo "<h3>New Login Credentials:</h3>";
        echo "<p><strong>Email:</strong> " . htmlspecialchars($admin['email']) . "</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "<p><a href='login.php' style='font-size: 18px;'>Click here to login</a></p>";
    } else {
        echo "<p style='color: red;'>Error updating password: " . $stmt->error . "</p>";
    }
} else {
    echo "<p style='color: red;'><strong>No admin user found!</strong></p>";
    
    // Create admin user
    $full_name = "System Administrator";
    $email = "admin@unibizhub.com";
    $password = "admin123";
    $password_hash = hashPassword($password);
    
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role, status) VALUES (?, ?, ?, 'Admin', 'Active')");
    $stmt->bind_param("sss", $full_name, $email, $password_hash);
    
    if ($stmt->execute()) {
        echo "<p style='color: green; font-size: 18px;'><strong>✓ Admin user created successfully!</strong></p>";
        echo "<h3>Login Credentials:</h3>";
        echo "<p><strong>Email:</strong> admin@unibizhub.com</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "<p><a href='login.php' style='font-size: 18px;'>Click here to login</a></p>";
    } else {
        echo "<p style='color: red;'>Error creating admin: " . $stmt->error . "</p>";
    }
}
?>
