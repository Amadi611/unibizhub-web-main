<?php
require_once 'db_connect.php';

echo "<h2>Checking Admin User in Database</h2>";

// Check if admin user exists
$sql = "SELECT user_id, full_name, email, role, status FROM users WHERE role = 'Admin'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<p><strong>Admin user found:</strong></p>";
    while ($row = $result->fetch_assoc()) {
        echo "<pre>";
        echo "User ID: " . $row['user_id'] . "\n";
        echo "Full Name: " . $row['full_name'] . "\n";
        echo "Email: " . $row['email'] . "\n";
        echo "Role: " . $row['role'] . "\n";
        echo "Status: " . $row['status'] . "\n";
        echo "</pre>";
    }
} else {
    echo "<p><strong>No admin user found in database!</strong></p>";
    echo "<p>Would you like to create one?</p>";
    echo "<form method='POST'>";
    echo "Full Name: <input type='text' name='full_name' value='System Administrator' required><br><br>";
    echo "Email: <input type='email' name='email' value='admin@unibizhub.com' required><br><br>";
    echo "Password: <input type='password' name='password' value='admin123' required><br><br>";
    echo "<input type='submit' name='create_admin' value='Create Admin User'>";
    echo "</form>";
}

// Handle admin creation
if (isset($_POST['create_admin'])) {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    // Check if email already exists
    $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        echo "<p style='color: red;'>Email already exists!</p>";
    } else {
        $password_hash = hashPassword($password);
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role, status) VALUES (?, ?, ?, 'Admin', 'Active')");
        $stmt->bind_param("sss", $full_name, $email, $password_hash);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'><strong>Admin user created successfully!</strong></p>";
            echo "<p>You can now login with:</p>";
            echo "<p>Email: " . htmlspecialchars($email) . "</p>";
            echo "<p>Password: " . htmlspecialchars($password) . "</p>";
        } else {
            echo "<p style='color: red;'>Error creating admin: " . $stmt->error . "</p>";
        }
    }
}
?>
