<?php
require_once 'db_connect.php';
?>

<?php include 'header.php'; ?>

<!-- Page Header -->
<section class="bg-light py-5">
    <div class="container">
        <div class="text-center">
            <h1 class="fw-bold mb-3">Our Services</h1>
            <p class="lead">Comprehensive solutions for student entrepreneurs and customers</p>
        </div>
    </div>
</section>

<!-- Services Overview -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">What We Offer</h2>
            <p class="text-muted">UNIBIZHUB provides everything you need to succeed in the student marketplace</p>
        </div>
        
        <div class="row g-4">
            <!-- For Entrepreneurs -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100 border-0 shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-store me-2"></i>For Student Entrepreneurs</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                    <div>
                                        <h6 class="mb-1">Free Shop Setup</h6>
                                        <p class="small text-muted mb-0">Create your online store in minutes with no setup fees</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                    <div>
                                        <h6 class="mb-1">Product Management</h6>
                                        <p class="small text-muted mb-0">Easy inventory tracking and product listing tools</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                    <div>
                                        <h6 class="mb-1">Order Processing</h6>
                                        <p class="small text-muted mb-0">Streamlined order management and fulfillment</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                    <div>
                                        <h6 class="mb-1">Sales Analytics</h6>
                                        <p class="small text-muted mb-0">Detailed insights into your business performance</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                    <div>
                                        <h6 class="mb-1">Customer Reviews</h6>
                                        <p class="small text-muted mb-0">Build trust through customer feedback</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                    <div>
                                        <h6 class="mb-1">Marketing Support</h6>
                                        <p class="small text-muted mb-0">Promote your products to the campus community</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <a href="register.php?role=Entrepreneur" class="btn btn-primary">
                                <i class="fas fa-rocket me-2"></i>Start Your Business
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- For Customers -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100 border-0 shadow-lg">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>For Customers</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <i class="fas fa-check-circle text-primary me-3 mt-1"></i>
                                    <div>
                                        <h6 class="mb-1">Unique Products</h6>
                                        <p class="small text-muted mb-0">Discover items you won't find anywhere else</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <i class="fas fa-check-circle text-primary me-3 mt-1"></i>
                                    <div>
                                        <h6 class="mb-1">Student Support</h6>
                                        <p class="small text-muted mb-0">Support fellow students' businesses</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <i class="fas fa-check-circle text-primary me-3 mt-1"></i>
                                    <div>
                                        <h6 class="mb-1">Secure Shopping</h6>
                                        <p class="small text-muted mb-0">Safe transactions and buyer protection</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <i class="fas fa-check-circle text-primary me-3 mt-1"></i>
                                    <div>
                                        <h6 class="mb-1">Easy Ordering</h6>
                                        <p class="small text-muted mb-0">Simple checkout process with multiple payment options</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <i class="fas fa-check-circle text-primary me-3 mt-1"></i>
                                    <div>
                                        <h6 class="mb-1">Order Tracking</h6>
                                        <p class="small text-muted mb-0">Real-time updates on your order status</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <i class="fas fa-check-circle text-primary me-3 mt-1"></i>
                                    <div>
                                        <h6 class="mb-1">Reviews & Ratings</h6>
                                        <p class="small text-muted mb-0">Share your experience with the community</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <a href="products.php" class="btn btn-success">
                                <i class="fas fa-shopping-bag me-2"></i>Start Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Premium Features -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Premium Features</h2>
            <p class="text-muted">Advanced tools to help you grow your business</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Business Analytics</h5>
                        <p class="card-text">Track sales trends, customer behavior, and inventory performance with detailed analytics and reports.</p>
                        <ul class="list-unstyled text-start small">
                            <li><i class="fas fa-check text-success me-2"></i>Sales reports</li>
                            <li><i class="fas fa-check text-success me-2"></i>Customer insights</li>
                            <li><i class="fas fa-check text-success me-2"></i>Product performance</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="fas fa-bullhorn fa-3x text-warning mb-3"></i>
                        <h5 class="card-title">Marketing Tools</h5>
                        <p class="card-text">Promote your products with built-in marketing features and reach more customers on campus.</p>
                        <ul class="list-unstyled text-start small">
                            <li><i class="fas fa-check text-success me-2"></i>Featured listings</li>
                            <li><i class="fas fa-check text-success me-2"></i>Social media integration</li>
                            <li><i class="fas fa-check text-success me-2"></i>Email campaigns</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="fas fa-headset fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Priority Support</h5>
                        <p class="card-text">Get dedicated support from our team to help you succeed and resolve any issues quickly.</p>
                        <ul class="list-unstyled text-start small">
                            <li><i class="fas fa-check text-success me-2"></i>24/7 chat support</li>
                            <li><i class="fas fa-check text-success me-2"></i>Business consultation</li>
                            <li><i class="fas fa-check text-success me-2"></i>Technical assistance</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Service Categories -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Popular Categories</h2>
            <p class="text-muted">Explore the diverse range of products and services available</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-3 col-6">
                <div class="card h-100 text-center border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-tshirt fa-2x text-primary mb-3"></i>
                        <h6 class="card-title">Fashion & Apparel</h6>
                        <p class="card-text small">Custom clothing, accessories, and handmade fashion items</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-6">
                <div class="card h-100 text-center border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-palette fa-2x text-warning mb-3"></i>
                        <h6 class="card-title">Handcrafts</h6>
                        <p class="card-text small">Artistic creations, handmade goods, and crafts</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-6">
                <div class="card h-100 text-center border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-laptop fa-2x text-info mb-3"></i>
                        <h6 class="card-title">Digital Products</h6>
                        <p class="card-text small">E-books, designs, software, and digital services</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-6">
                <div class="card h-100 text-center border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-book fa-2x text-success mb-3"></i>
                        <h6 class="card-title">Education</h6>
                        <p class="card-text small">Tutoring, notes, study materials, and educational services</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-6">
                <div class="card h-100 text-center border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-utensils fa-2x text-danger mb-3"></i>
                        <h6 class="card-title">Food & Beverages</h6>
                        <p class="card-text small">Homemade snacks, meals, and specialty drinks</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-6">
                <div class="card h-100 text-center border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-dumbbell fa-2x text-secondary mb-3"></i>
                        <h6 class="card-title">Sports & Fitness</h6>
                        <p class="card-text small">Fitness equipment, training services, and sports gear</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-6">
                <div class="card h-100 text-center border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-mobile-alt fa-2x text-dark mb-3"></i>
                        <h6 class="card-title">Electronics</h6>
                        <p class="card-text small">Tech accessories, gadgets, and electronic services</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-6">
                <div class="card h-100 text-center border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-ellipsis-h fa-2x text-muted mb-3"></i>
                        <h6 class="card-title">Others</h6>
                        <p class="card-text small">Unique products and services that don't fit other categories</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="products.php" class="btn btn-primary">
                <i class="fas fa-th me-2"></i>Browse All Categories
            </a>
        </div>
    </div>
</section>

<!-- Pricing -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Simple, Transparent Pricing</h2>
            <p class="text-muted">No hidden fees. Start for free and grow as your business grows.</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header bg-white text-center">
                        <h5 class="text-primary">Free Plan</h5>
                        <h3 class="mb-0">Rs0<span class="text-muted small">/month</span></h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Up to 10 products</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Basic shop customization</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Order management</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Customer reviews</li>
                            <li class="mb-2"><i class="fas fa-times text-muted me-2"></i>Advanced analytics</li>
                            <li class="mb-2"><i class="fas fa-times text-muted me-2"></i>Priority support</li>
                        </ul>
                        <div class="d-grid">
                            <a href="register.php?role=Entrepreneur" class="btn btn-outline-primary">Get Started</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 border-primary">
                    <div class="card-header bg-primary text-white text-center position-relative">
                        <span class="badge bg-warning position-absolute top-0 start-50 translate-middle">POPULAR</span>
                        <h5>Pro Plan</h5>
                        <h3 class="mb-0">Rs299<span class="small">/month</span></h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Unlimited products</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Advanced shop features</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Sales analytics</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Marketing tools</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Priority support</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Featured listings</li>
                        </ul>
                        <div class="d-grid">
                            <a href="register.php?role=Entrepreneur" class="btn btn-primary">Upgrade to Pro</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header bg-white text-center">
                        <h5 class="text-success">Enterprise</h5>
                        <h3 class="mb-0">Rs999<span class="text-muted small">/month</span></h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Everything in Pro</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Multi-shop management</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>API access</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Custom branding</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Dedicated support</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Advanced reporting</li>
                        </ul>
                        <div class="d-grid">
                            <a href="contact.php" class="btn btn-outline-success">Contact Sales</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="fw-bold mb-3">Ready to Get Started?</h2>
        <p class="lead mb-4">Join UNIBIZHUB today and start your entrepreneurial journey</p>
        <div class="d-flex gap-3 justify-content-center">
            <a href="register.php" class="btn btn-light btn-lg">
                <i class="fas fa-user-plus me-2"></i>Sign Up Free
            </a>
            <a href="contact.php" class="btn btn-outline-light btn-lg">
                <i class="fas fa-question-circle me-2"></i>Learn More
            </a>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
