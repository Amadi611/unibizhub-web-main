<?php
require_once 'db_connect.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $role = getUserRole();
    switch($role) {
        case 'Admin':
            redirect('admin/dashboard.php');
            break;
        case 'Entrepreneur':
            redirect('seller/dashboard.php');
            break;
        default:
            redirect('customer/dashboard.php');
    }
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    // Validate input
    if (empty($email) || empty($password)) {
        redirect('login.php', 'Please fill in all fields', 'error');
    }
    
    // Check user credentials
        $sql = "SELECT * FROM users
                        WHERE email = ?
                            AND (status = 'Active' OR (status = 'Pending' AND role = 'Entrepreneur'))";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if (verifyPassword($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['phone'] = $user['phone'];
            $_SESSION['faculty'] = $user['faculty'];
            $_SESSION['student_id'] = $user['student_id'];
            
            // Set remember me cookie if checked
            if ($remember) {
                $token = bin2hex(random_bytes(16));
                $expires = time() + (86400 * 30); // 30 days
                
                // Store token in database (you might want to create a separate table for this)
                setcookie('remember_token', $token, $expires, '/', '', false, true);
            }
            
            // Redirect based on role
            switch($user['role']) {
                case 'Admin':
                    redirect('admin/dashboard.php', 'Welcome back, Administrator!', 'success');
                    break;
                case 'Entrepreneur':
                    redirect('seller/dashboard.php', 'Welcome back to your shop!', 'success');
                    break;
                default:
                    redirect('customer/dashboard.php', 'Welcome back!', 'success');
            }
        } else {
            redirect('login.php', 'Invalid email or password', 'error');
        }
    } else {
        redirect('login.php', 'Invalid email or password', 'error');
    }
}

// Handle remember me token
if (isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    // Here you would verify the token against your database
    // For simplicity, we'll just clear it
    setcookie('remember_token', '', time() - 3600, '/');
}
?>

<?php include 'header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold text-primary">Welcome Back</h2>
                        <p class="text-muted">Login to your UNIBIZHUB account</p>
                    </div>
                    
                    <form method="POST" action="login.php">
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-2"></i>Email Address
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                   required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-2"></i>Password
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Remember me for 30 days
                            </label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="mb-0">
                            Don't have an account? 
                            <a href="register.php" class="text-primary text-decoration-none fw-bold">
                                Register here
                            </a>
                        </p>
                        <p class="mt-2">
                            <a href="forgot-password.php" class="text-muted text-decoration-none">
                                <i class="fas fa-question-circle me-1"></i>Forgot your password?
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Quick Access Cards -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title text-center mb-3">Quick Access</h6>
                            <div class="d-flex justify-content-around">
                                <a href="register.php?role=Entrepreneur" class="text-decoration-none">
                                    <div class="text-center">
                                        <i class="fas fa-store fa-2x text-primary mb-2"></i>
                                        <p class="small mb-0">Become a Seller</p>
                                    </div>
                                </a>
                                <a href="register.php?role=Customer" class="text-decoration-none">
                                    <div class="text-center">
                                        <i class="fas fa-shopping-cart fa-2x text-success mb-2"></i>
                                        <p class="small mb-0">Start Shopping</p>
                                    </div>
                                </a>
                                <a href="about.php" class="text-decoration-none">
                                    <div class="text-center">
                                        <i class="fas fa-info-circle fa-2x text-info mb-2"></i>
                                        <p class="small mb-0">Learn More</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Login Benefits -->
        <div class="col-md-6 col-lg-7">
            <div class="ps-md-4">
                <div class="hero-section rounded p-5 mb-4" style="color: #1a365d;">
                    <h3 class="mb-3">Why Join UNIBIZHUB?</h3>
                    <ul class="list-unstyled">
                        <li class="mb-3">
                            <i class="fas fa-check-circle me-2 text-success"></i>
                            <strong>For Students:</strong> Start your entrepreneurial journey
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-check-circle me-2 text-success"></i>
                            <strong>For Customers:</strong> Support student businesses
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-check-circle me-2 text-success"></i>
                            <strong>For Everyone:</strong> Join a vibrant university marketplace
                        </li>
                    </ul>
                </div>
                
                <!-- Features Grid -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                                <h6 class="card-title">Secure Platform</h6>
                                <p class="card-text small text-muted">Safe transactions and data protection</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-3x text-success mb-3"></i>
                                <h6 class="card-title">Community Driven</h6>
                                <p class="card-text small text-muted">Support fellow students</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-mobile-alt fa-3x text-warning mb-3"></i>
                                <h6 class="card-title">Mobile Friendly</h6>
                                <p class="card-text small text-muted">Access anywhere, anytime</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-headset fa-3x text-info mb-3"></i>
                                <h6 class="card-title">24/7 Support</h6>
                                <p class="card-text small text-muted">Always here to help you</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

// Auto-focus email field
document.getElementById('email').focus();

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    if (!email || !password) {
        e.preventDefault();
        alert('Please fill in all fields');
        return false;
    }
    
    // Basic email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('Please enter a valid email address');
        return false;
    }
});
</script>

<?php include 'footer.php'; ?>
