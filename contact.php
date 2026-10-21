<?php
require_once 'db_connect.php';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    
    // Validation
    $errors = [];
    
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($subject)) $errors[] = "Subject is required";
    if (empty($message)) $errors[] = "Message is required";
    
    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($errors)) {
        // In a real application, you would send an email here
        // For now, we'll just show a success message
        redirect('contact.php', 'Thank you for your message! We will get back to you soon.', 'success');
    } else {
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = $_POST;
        redirect('contact.php');
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

<!-- Page Header -->
<section class="bg-light py-5">
    <div class="container">
        <div class="text-center">
            <h1 class="fw-bold mb-3">Contact Us</h1>
            <p class="lead">Get in touch with the UNIBIZHUB team</p>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Contact Form -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-envelope me-2"></i>Send us a Message</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($errors) && !empty($errors)): ?>
                            <div class="alert alert-danger">
                                <strong>Please fix the following errors:</strong>
                                <ul class="mb-0 mt-2">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="contact.php">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo isset($form_data['name']) ? htmlspecialchars($form_data['name']) : ''; ?>" 
                                           required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo isset($form_data['email']) ? htmlspecialchars($form_data['email']) : ''; ?>" 
                                           required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject *</label>
                                <input type="text" class="form-control" id="subject" name="subject" 
                                       value="<?php echo isset($form_data['subject']) ? htmlspecialchars($form_data['subject']) : ''; ?>" 
                                       required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label">Message *</label>
                                <textarea class="form-control" id="message" name="message" rows="6" 
                                          required><?php echo isset($form_data['message']) ? htmlspecialchars($form_data['message']) : ''; ?></textarea>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                                <button type="reset" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Clear
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Contact Information -->
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Contact Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6 class="text-primary"><i class="fas fa-envelope me-2"></i>Email</h6>
                            <p class="mb-0">info@unibizhub.com</p>
                            <small class="text-muted">General inquiries</small>
                        </div>
                        
                        <div class="mb-4">
                            <h6 class="text-primary"><i class="fas fa-phone me-2"></i>Phone</h6>
                            <p class="mb-0">+63 2 1234 5678</p>
                            <small class="text-muted">Mon-Fri, 9AM-6PM</small>
                        </div>
                        
                        <div class="mb-4">
                            <h6 class="text-primary"><i class="fas fa-map-marker-alt me-2"></i>Address</h6>
                            <p class="mb-0">University Campus Building</p>
                            <p class="mb-0">Student Center, Room 201</p>
                            <p class="mb-0">Manila, Philippines 1000</p>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-primary"><i class="fas fa-clock me-2"></i>Business Hours</h6>
                            <p class="mb-1"><strong>Monday - Friday:</strong> 9:00 AM - 6:00 PM</p>
                            <p class="mb-1"><strong>Saturday:</strong> 10:00 AM - 4:00 PM</p>
                            <p class="mb-0"><strong>Sunday:</strong> Closed</p>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-link me-2"></i>Quick Links</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="about.php" class="btn btn-outline-primary">
                                <i class="fas fa-info-circle me-2"></i>About UNIBIZHUB
                            </a>
                            <a href="services.php" class="btn btn-outline-primary">
                                <i class="fas fa-cog me-2"></i>Our Services
                            </a>
                            <a href="register.php" class="btn btn-outline-primary">
                                <i class="fas fa-user-plus me-2"></i>Register Account
                            </a>
                            <a href="faq.php" class="btn btn-outline-primary">
                                <i class="fas fa-question-circle me-2"></i>FAQ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Frequently Asked Questions</h2>
            <p class="text-muted">Quick answers to common questions</p>
        </div>
        
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-question-circle text-primary me-2"></i>How do I start selling on UNIBIZHUB?</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">Simply register as an entrepreneur, verify your student status, create your shop, and start adding products. The whole process takes less than 10 minutes!</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-question-circle text-primary me-2"></i>Are there any fees to use UNIBIZHUB?</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">Basic features are completely free! We offer premium plans with advanced features for growing businesses, but you can start and run a successful shop at no cost.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-question-circle text-primary me-2"></i>How do I get paid for my sales?</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">Payments are processed securely through our platform. You can withdraw your earnings to your bank account or preferred payment method once your account is verified.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-question-circle text-primary me-2"></i>Is my information secure on UNIBIZHUB?</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">Absolutely! We use industry-standard encryption and security measures to protect your personal and financial information. Your privacy and security are our top priorities.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <p class="mb-0">Have more questions? <a href="faq.php" class="text-primary">Visit our FAQ page</a> or <a href="contact.php" class="text-primary">contact us directly</a>.</p>
        </div>
    </div>
</section>

<!-- Social Media -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Connect With Us</h2>
            <p class="text-muted">Follow us on social media for updates and tips</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-4">
                        <div class="d-flex justify-content-center gap-4 mb-4">
                            <a href="#" class="text-decoration-none">
                                <div class="text-center">
                                    <i class="fab fa-facebook fa-3x text-primary mb-2"></i>
                                    <p class="small mb-0">Facebook</p>
                                </div>
                            </a>
                            <a href="#" class="text-decoration-none">
                                <div class="text-center">
                                    <i class="fab fa-twitter fa-3x text-info mb-2"></i>
                                    <p class="small mb-0">Twitter</p>
                                </div>
                            </a>
                            <a href="#" class="text-decoration-none">
                                <div class="text-center">
                                    <i class="fab fa-instagram fa-3x text-danger mb-2"></i>
                                    <p class="small mb-0">Instagram</p>
                                </div>
                            </a>
                            <a href="#" class="text-decoration-none">
                                <div class="text-center">
                                    <i class="fab fa-linkedin fa-3x text-primary mb-2"></i>
                                    <p class="small mb-0">LinkedIn</p>
                                </div>
                            </a>
                            <a href="#" class="text-decoration-none">
                                <div class="text-center">
                                    <i class="fab fa-youtube fa-3x text-danger mb-2"></i>
                                    <p class="small mb-0">YouTube</p>
                                </div>
                            </a>
                        </div>
                        <p class="text-muted">Stay updated with the latest features, success stories, and entrepreneurship tips!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Find Us</h2>
            <p class="text-muted">Visit our office at the university campus</p>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <!-- Placeholder for map - in real implementation, you would embed Google Maps -->
                        <div class="bg-light text-center py-5" style="min-height: 400px;">
                            <i class="fas fa-map-marked-alt fa-4x text-muted mb-3"></i>
                            <h4>Interactive Map</h4>
                            <p class="text-muted">University Campus Building<br>Student Center, Room 201<br>Manila, Philippines 1000</p>
                            <a href="https://maps.google.com" target="_blank" class="btn btn-primary">
                                <i class="fas fa-external-link-alt me-2"></i>Open in Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
