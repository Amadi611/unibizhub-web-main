<?php
require_once 'db_connect.php';

// Get platform statistics for display
$stats = [
    'total_users' => 0,
    'total_shops' => 0,
    'total_products' => 0,
    'total_orders' => 0
];

// Get actual statistics from database
$sql = "SELECT COUNT(*) as count FROM users";
$result = $conn->query($sql);
$stats['total_users'] = $result->fetch_assoc()['count'];

$sql = "SELECT COUNT(*) as count FROM shops WHERE status = 'Active'";
$result = $conn->query($sql);
$stats['total_shops'] = $result->fetch_assoc()['count'];

$sql = "SELECT COUNT(*) as count FROM products WHERE status = 'Active'";
$result = $conn->query($sql);
$stats['total_products'] = $result->fetch_assoc()['count'];

$sql = "SELECT COUNT(*) as count FROM orders";
$result = $conn->query($sql);
$stats['total_orders'] = $result->fetch_assoc()['count'];
?>

<?php include 'header.php'; ?>

<!-- Page Header -->
<section class="bg-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="fw-bold mb-3">About UNIBIZHUB</h1>
                <p class="lead">Empowering university students to become successful entrepreneurs through our innovative online marketplace platform.</p>
            </div>
            <div class="col-lg-4 text-center">
                <img src="./images/logo.svg"
                     alt="UNIBIZHUB Logo" >
            </div>
        </div>
    </div>
</section>

<!-- Our Story -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4">
                <h2 class="fw-bold mb-4">Our Story</h2>
                <p class="mb-3">
                    UNIBIZHUB was born from a simple observation: university campuses are filled with talented, innovative students who have amazing products and services to offer, but lack a dedicated platform to showcase and sell them.
                </p>
                <p class="mb-3">
                    Founded in 2024, our mission is to create a vibrant entrepreneurial ecosystem within universities, where students can easily start their own businesses, connect with customers, and gain real-world business experience while still pursuing their education.
                </p>
                <p>
                    We believe that entrepreneurship shouldn't wait until graduation. With UNIBIZHUB, students can start their business journey today, learning valuable skills in marketing, customer service, inventory management, and financial literacy.
                </p>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card shadow-lg">
                    <div class="card-body p-4">
                        <h4 class="text-primary mb-3">Our Mission</h4>
                        <p class="mb-4">To empower university students to become successful entrepreneurs by providing a safe, user-friendly platform to showcase and sell their products and services to the campus community.</p>
                        
                        <h4 class="text-primary mb-3">Our Vision</h4>
                        <p class="mb-4">To become the leading student entrepreneurship platform in universities worldwide, fostering innovation and creating the next generation of business leaders.</p>
                        
                        <h4 class="text-primary mb-3">Our Values</h4>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i><strong>Innovation:</strong> Encouraging creativity and new ideas</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i><strong>Community:</strong> Building supportive student networks</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i><strong>Education:</strong> Learning through real business experience</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i><strong>Integrity:</strong> Maintaining trust and transparency</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Platform Statistics -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Our Impact</h2>
            <p class="text-muted">Growing together with our student community</p>
        </div>
        
        <div class="row text-center">
            <div class="col-md-3 mb-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['total_users']); ?>+</div>
                    <div class="stat-label">Active Users</div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-store"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['total_shops']); ?>+</div>
                    <div class="stat-label">Student Shops</div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['total_products']); ?>+</div>
                    <div class="stat-label">Products Listed</div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['total_orders']); ?>+</div>
                    <div class="stat-label">Orders Completed</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">How UNIBIZHUB Works</h2>
            <p class="text-muted">Simple steps to start your entrepreneurial journey</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="text-center">
                    <div class="step-icon mb-3">
                        <i class="fas fa-user-plus fa-2x"></i>
                    </div>
                    <h5 class="mb-3">1. Sign Up</h5>
                    <p>Register as a customer or entrepreneur. Students can verify their status to start selling.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="text-center">
                    <div class="step-icon mb-3">
                        <i class="fas fa-store fa-2x"></i>
                    </div>
                    <h5 class="mb-3">2. Create Shop</h5>
                    <p>Set up your online store, add products, and customize your shop to reflect your brand.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="text-center">
                    <div class="step-icon mb-3">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                    <h5 class="mb-3">3. Grow Business</h5>
                    <p>Manage orders, track sales, and build your customer base within the university community.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Platform Features</h2>
            <p class="text-muted">Everything you need to succeed as a student entrepreneur</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Secure Platform</h5>
                        <p class="card-text">Safe transactions and data protection for all users</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-mobile-alt fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Mobile Friendly</h5>
                        <p class="card-text">Access your shop and manage orders from any device</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-bar fa-3x text-warning mb-3"></i>
                        <h5 class="card-title">Analytics Dashboard</h5>
                        <p class="card-text">Track sales, view trends, and optimize your business</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-star fa-3x text-info mb-3"></i>
                        <h5 class="card-title">Review System</h5>
                        <p class="card-text">Build trust with customer reviews and ratings</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-3x text-danger mb-3"></i>
                        <h5 class="card-title">Community Support</h5>
                        <p class="card-text">Connect with fellow entrepreneurs and share experiences</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-graduation-cap fa-3x text-secondary mb-3"></i>
                        <h5 class="card-title">Educational Resources</h5>
                        <p class="card-text">Learn business skills through hands-on experience</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="fw-bold mb-3">Ready to Start Your Entrepreneurial Journey?</h2>
        <p class="lead mb-4">Join thousands of students already building their businesses on UNIBIZHUB</p>
        
        <div class="d-flex gap-3 justify-content-center">
            <?php if (!isLoggedIn()): ?>
                <a href="register.php?role=Entrepreneur" class="btn btn-light btn-lg">
                    <i class="fas fa-store me-2"></i>Start Selling
                </a>
                <a href="register.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-shopping-cart me-2"></i>Start Shopping
                </a>
            <?php else: ?>
                <?php if (getUserRole() === 'Customer'): ?>
                    <a href="register.php?role=Entrepreneur" class="btn btn-light btn-lg">
                        <i class="fas fa-store me-2"></i>Become a Seller
                    </a>
                    <a href="products.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-shopping-bag me-2"></i>Browse Products
                    </a>
                <?php else: ?>
                    <a href="seller/dashboard.php" class="btn btn-light btn-lg">
                        <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                    </a>
                    <a href="products.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-shopping-bag me-2"></i>Browse Products
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
