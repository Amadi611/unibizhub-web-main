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

// Get categories for dropdown
$categories = getAllCategories();

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = sanitize($_POST['role']);
    $phone = sanitize($_POST['phone']);
    $faculty = sanitize($_POST['faculty']);
    $student_id = sanitize($_POST['student_id']);
    $terms = isset($_POST['terms']);
    
    // Validation
    $errors = [];
    
    if (empty($full_name)) $errors[] = "Full name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($password)) $errors[] = "Password is required";
    if (empty($role)) $errors[] = "Please select a role";
    if (!in_array($role, ['Customer', 'Entrepreneur'], true)) $errors[] = "Invalid account type";
    if (!$terms) $errors[] = "You must agree to the terms and conditions";
    
    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Password validation
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Phone validation (optional but if provided)
    if (!empty($phone) && !preg_match('/^[0-9]{10,15}$/', $phone)) {
        $errors[] = "Invalid phone number format";
    }
    
    // Check if email already exists
    $sql = "SELECT user_id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors[] = "Email already registered";
    }
    
    // Additional validation for entrepreneurs
    if ($role == 'Entrepreneur') {
        if (empty($faculty)) $errors[] = "Faculty is required for entrepreneurs";
        if (empty($student_id)) $errors[] = "Student ID is required for entrepreneurs";
        
        // Check if student ID already exists
        if (!empty($student_id)) {
            $sql = "SELECT user_id FROM users WHERE student_id = ? AND role = 'Entrepreneur'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $student_id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $errors[] = "Student ID already registered";
            }
        }
    }
    
    if (empty($errors)) {
        // Hash password
        $hashed_password = hashPassword($password);
        
        // Insert new user
        $account_status = $role === 'Customer' ? 'Active' : 'Pending';
        $sql = "INSERT INTO users (full_name, email, password, role, phone, faculty, student_id, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $full_name, $email, $hashed_password, $role, $phone, $faculty, $student_id, $account_status);
        
        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            
            // Auto-login after registration
            $_SESSION['user_id'] = $user_id;
            $_SESSION['full_name'] = $full_name;
            $_SESSION['email'] = $email;
            $_SESSION['user_role'] = $role;
            $_SESSION['phone'] = $phone;
            $_SESSION['faculty'] = $faculty;
            $_SESSION['student_id'] = $student_id;
            
            // Redirect based on role
            switch($role) {
                case 'Admin':
                    redirect('admin/dashboard.php', 'Registration successful! Welcome Administrator.', 'success');
                    break;
                case 'Entrepreneur':
                    redirect('seller/dashboard.php', 'Registration successful! Submit a shop request for administrator approval.', 'success');
                    break;
                default:
                    redirect('customer/dashboard.php', 'Registration successful! Welcome to UNIBIZHUB!', 'success');
            }
        } else {
            redirect('register.php', 'Registration failed. Please try again.', 'error');
        }
    } else {
        // Store errors in session to display
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = $_POST;
        redirect('register.php');
    }
}

// Display errors if any
if (isset($_SESSION['errors'])) {
    $errors = $_SESSION['errors'];
    $form_data = $_SESSION['form_data'];
    unset($_SESSION['errors']);
    unset($_SESSION['form_data']);
}
?>

<?php include 'header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card shadow-lg">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold text-primary">Create Account</h2>
                        <p class="text-muted">Join the UNIBIZHUB community today</p>
                    </div>
                    
                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger">
                            <strong>Registration Errors:</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="register.php" id="registrationForm">
                        <!-- Personal Information -->
                        <div class="mb-4">
                            <h5 class="mb-3"><i class="fas fa-user me-2"></i>Personal Information</h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="full_name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?php echo isset($form_data['full_name']) ? htmlspecialchars($form_data['full_name']) : ''; ?>" 
                                           required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo isset($form_data['email']) ? htmlspecialchars($form_data['email']) : ''; ?>" 
                                           required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo isset($form_data['phone']) ? htmlspecialchars($form_data['phone']) : ''; ?>"
                                           placeholder="09XXXXXXXXX">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="role" class="form-label">Account Type *</label>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="">Select Role</option>
                                        <option value="Customer" <?php echo (isset($form_data['role']) && $form_data['role'] == 'Customer') ? 'selected' : ''; ?>>Customer</option>
                                        <option value="Entrepreneur" <?php echo (isset($form_data['role']) && $form_data['role'] == 'Entrepreneur') ? 'selected' : ''; ?>>Entrepreneur (Student Seller)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Academic Information (for Entrepreneurs) -->
                        <div class="mb-4" id="academicSection" style="display: none;">
                            <h5 class="mb-3"><i class="fas fa-graduation-cap me-2"></i>Academic Information</h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="faculty" class="form-label">Faculty/Department *</label>
                                    <input type="text" class="form-control" id="faculty" name="faculty" 
                                           value="<?php echo isset($form_data['faculty']) ? htmlspecialchars($form_data['faculty']) : ''; ?>"
                                           placeholder="e.g., College of Engineering">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="student_id" class="form-label">Student ID *</label>
                                    <input type="text" class="form-control" id="student_id" name="student_id" 
                                           value="<?php echo isset($form_data['student_id']) ? htmlspecialchars($form_data['student_id']) : ''; ?>"
                                           placeholder="e.g., 2021-12345">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Password Information -->
                        <div class="mb-4">
                            <h5 class="mb-3"><i class="fas fa-lock me-2"></i>Security</h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password *</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Minimum 8 characters</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Confirm Password *</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Password Strength Indicator -->
                            <div class="mb-3">
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar" id="passwordStrength" role="progressbar" style="width: 0%"></div>
                                </div>
                                <small class="text-muted" id="passwordStrengthText">Enter a password</small>
                            </div>
                        </div>
                        
                        <!-- Terms and Conditions -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a> and <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a> *
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="mb-0">
                            Already have an account? 
                            <a href="login.php" class="text-primary text-decoration-none fw-bold">
                                Login here
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Registration Benefits -->
        <div class="col-md-4">
            <div class="ps-md-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">Why Join UNIBIZHUB?</h5>
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Free Registration</strong>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>No Listing Fees</strong>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Secure Transactions</strong>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>University Community</strong>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>24/7 Support</strong>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Role Comparison -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">Account Types</h5>
                        
                        <div class="mb-3">
                            <h6 class="text-success">Customer</h6>
                            <p class="small text-muted">Browse and purchase products from student entrepreneurs</p>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-warning">Entrepreneur</h6>
                            <p class="small text-muted">Sell your products and manage your own online store</p>
                        </div>
                        
                        <div class="alert alert-info small">
                            <i class="fas fa-info-circle me-2"></i>
                            Student verification required for entrepreneurs
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>1. Account Registration</h6>
                <p>You must provide accurate information when registering an account. Student entrepreneurs must verify their university status.</p>
                
                <h6>2. Platform Usage</h6>
                <p>UNIBIZHUB is exclusively for university students. All activities must comply with university policies and local laws.</p>
                
                <h6>3. Product Listings</h6>
                <p>All products must be legal, appropriate, and owned by the seller. The platform reserves the right to remove inappropriate listings.</p>
                
                <h6>4. Transactions</h6>
                <p>All transactions are between buyers and sellers. UNIBIZHUB provides the platform but is not responsible for individual transactions.</p>
                
                <h6>5. Privacy</h6>
                <p>Your personal information will be protected according to our privacy policy and will not be shared without consent.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Privacy Modal -->
<div class="modal fade" id="privacyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Privacy Policy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Information Collection</h6>
                <p>We collect personal information including name, email, phone, and academic details for account creation and verification.</p>
                
                <h6>Information Usage</h6>
                <p>Your information is used to provide platform services, verify student status, and facilitate transactions.</p>
                
                <h6>Data Protection</h6>
                <p>We implement appropriate security measures to protect your personal data from unauthorized access.</p>
                
                <h6>Information Sharing</h6>
                <p>We do not sell or share your personal information with third parties without your consent.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Show/hide academic section based on role
document.getElementById('role').addEventListener('change', function() {
    const academicSection = document.getElementById('academicSection');
    const faculty = document.getElementById('faculty');
    const student_id = document.getElementById('student_id');
    
    if (this.value === 'Entrepreneur') {
        academicSection.style.display = 'block';
        faculty.setAttribute('required', 'required');
        student_id.setAttribute('required', 'required');
    } else {
        academicSection.style.display = 'none';
        faculty.removeAttribute('required');
        student_id.removeAttribute('required');
    }
});

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

document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('confirm_password');
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

// Password strength checker
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('passwordStrengthText');
    
    let strength = 0;
    
    if (password.length >= 8) strength += 25;
    if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 25;
    if (password.match(/[0-9]/)) strength += 25;
    if (password.match(/[^a-zA-Z0-9]/)) strength += 25;
    
    strengthBar.style.width = strength + '%';
    
    if (strength === 0) {
        strengthBar.className = 'progress-bar';
        strengthText.textContent = 'Enter a password';
    } else if (strength <= 25) {
        strengthBar.className = 'progress-bar bg-danger';
        strengthText.textContent = 'Weak password';
    } else if (strength <= 50) {
        strengthBar.className = 'progress-bar bg-warning';
        strengthText.textContent = 'Fair password';
    } else if (strength <= 75) {
        strengthBar.className = 'progress-bar bg-info';
        strengthText.textContent = 'Good password';
    } else {
        strengthBar.className = 'progress-bar bg-success';
        strengthText.textContent = 'Strong password';
    }
});

// Form validation
document.getElementById('registrationForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match');
        return false;
    }
    
    if (password.length < 8) {
        e.preventDefault();
        alert('Password must be at least 8 characters long');
        return false;
    }
});

// Auto-set role from URL parameter
const urlParams = new URLSearchParams(window.location.search);
const roleParam = urlParams.get('role');
if (roleParam) {
    document.getElementById('role').value = roleParam;
    document.getElementById('role').dispatchEvent(new Event('change'));
}
</script>

<?php include 'footer.php'; ?>
