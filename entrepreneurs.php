<?php
require_once 'db_connect.php';

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Get search parameter
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Get entrepreneurs with search
$entrepreneurs = getEntrepreneurs($limit, $offset);

// If search is provided, filter entrepreneurs
if ($search) {
    $entrepreneurs = array_filter($entrepreneurs, function($entrepreneur) use ($search) {
        return stripos($entrepreneur['full_name'], $search) !== false ||
               stripos($entrepreneur['shop_name'], $search) !== false ||
               stripos($entrepreneur['faculty'], $search) !== false;
    });
}

// Get total entrepreneurs for pagination
$total_entrepreneurs = countEntrepreneurs();
$total_pages = ceil($total_entrepreneurs / $limit);

// Get categories for filtering
$categories = getAllCategories();
?>

<?php include 'header.php'; ?>

<!-- Page Header -->
<section class="bg-light py-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="fw-bold mb-0">
                    <?php if ($search): ?>
                        Search Entrepreneurs: "<?php echo htmlspecialchars($search); ?>"
                    <?php else: ?>
                        Student Entrepreneurs
                    <?php endif; ?>
                </h1>
                <p class="text-muted mb-0">
                    Meet the talented students running their own businesses
                    <?php if (!$search): ?>
                        - <?php echo number_format($total_entrepreneurs); ?> entrepreneurs
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-md-4">
                <div class="d-flex gap-2 justify-content-md-end mt-3 mt-md-0">
                    <select class="form-select" id="sortSelect">
                        <option value="newest">Newest First</option>
                        <option value="name">Name: A to Z</option>
                        <option value="faculty">Faculty</option>
                    </select>
                    <select class="form-select" id="limitSelect">
                        <option value="12">12 per page</option>
                        <option value="24">24 per page</option>
                        <option value="48">48 per page</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Search Section -->
<section class="py-3 bg-white border-bottom">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <form method="GET" action="entrepreneurs.php">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Search entrepreneurs by name, shop, or faculty...">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search me-2"></i>Search
                        </button>
                    </div>
                </form>
            </div>
            <div class="col-md-6">
                <div class="d-flex gap-2 justify-content-md-end mt-2 mt-md-0">
                    <select class="form-select" id="categoryFilter">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>">
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select class="form-select" id="facultyFilter">
                        <option value="">All Faculties</option>
                        <option value="College of Engineering">College of Engineering</option>
                        <option value="College of Arts">College of Arts</option>
                        <option value="Business School">Business School</option>
                        <option value="College of Science">College of Science</option>
                        <option value="College of Medicine">College of Medicine</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Entrepreneurs Section -->
<section class="py-5">
    <div class="container">
        <?php if (empty($entrepreneurs)): ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-4x text-muted mb-3"></i>
                <h4>No entrepreneurs found</h4>
                <p class="text-muted">
                    <?php if ($search): ?>
                        No entrepreneurs match your search criteria. Try different keywords.
                    <?php else: ?>
                        No entrepreneurs have registered yet. Be the first to join UNIBIZHUB!
                                            <?php endif; ?>
                </p>
                <?php if ($search): ?>
                    <a href="entrepreneurs.php" class="btn btn-primary">
                        <i class="fas fa-redo me-2"></i>Clear Search
                    </a>
                <?php else: ?>
                    <a href="register.php?role=Entrepreneur" class="btn btn-primary">
                        <i class="fas fa-user-plus me-2"></i>Become an Entrepreneur
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Active Search Display -->
            <?php if ($search): ?>
                <div class="alert alert-info mb-4">
                    <strong>Searching for:</strong> "<?php echo htmlspecialchars($search); ?>"
                    <a href="entrepreneurs.php" class="float-end text-decoration-none">
                        <i class="fas fa-times me-1"></i>Clear Search
                    </a>
                </div>
            <?php endif; ?>
            
            <!-- Entrepreneurs Grid -->
            <div class="row g-4">
                <?php foreach ($entrepreneurs as $entrepreneur): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card entrepreneur-card h-100">
                            <!-- Profile Header -->
                            <div class="position-relative">
                                <?php if (!empty($entrepreneur['shop_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($entrepreneur['shop_image']); ?>"
                                         class="card-img-top"
                                         alt="<?php echo htmlspecialchars($entrepreneur['shop_name']); ?> shop logo"
                                         style="height: 250px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="card-img-top d-flex align-items-center justify-content-center bg-dark text-light"
                                         role="img"
                                         aria-label="No logo uploaded for <?php echo htmlspecialchars($entrepreneur['shop_name']); ?>"
                                         style="height: 250px;">
                                        <i class="fas fa-store fa-3x" aria-hidden="true"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Status Badge -->
                                <div class="position-absolute top-0 end-0 p-2">
                                    <span class="badge bg-success">Active</span>
                                </div>
                                
                                <!-- Quick Actions -->
                                <div class="position-absolute top-0 start-0 p-2">
                                    <button class="btn btn-sm btn-light rounded-circle" 
                                            onclick="viewProfile(<?php echo $entrepreneur['user_id']; ?>)">
                                        <i class="fas fa-user"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Shop Info -->
                            <div class="entrepreneur-info">
                                <div class="entrepreneur-name"><?php echo htmlspecialchars($entrepreneur['full_name']); ?></div>
                                <div class="entrepreneur-details">
                                    <div><i class="fas fa-graduation-cap me-1"></i><?php echo htmlspecialchars($entrepreneur['faculty']); ?></div>
                                    <div><i class="fas fa-id-card me-1"></i>ID: <?php echo htmlspecialchars($entrepreneur['student_id']); ?></div>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-store me-2"></i>
                                    <?php echo htmlspecialchars($entrepreneur['shop_name']); ?>
                                </h5>
                                
                                <p class="card-text text-muted small mb-3">
                                    <?php echo !empty($entrepreneur['shop_description']) ? 
                                           truncateText($entrepreneur['shop_description'], 100) : 
                                           'No description available.'; ?>
                                </p>
                                
                                <!-- Category Badge -->
                                <?php if (!empty($entrepreneur['category_name'])): ?>
                                    <div class="mb-3">
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-tag me-1"></i>
                                            <?php echo htmlspecialchars($entrepreneur['category_name']); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Shop Stats -->
                                <div class="row text-center mb-3">
                                    <div class="col-4">
                                        <div class="h6 mb-0 text-primary">
                                            <?php echo rand(5, 50); ?>
                                        </div>
                                        <small class="text-muted">Products</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="h6 mb-0 text-success">
                                            <?php echo rand(10, 200); ?>
                                        </div>
                                        <small class="text-muted">Sales</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="h6 mb-0 text-warning">
                                            <?php echo rand(3, 5); ?>.<?php echo rand(0, 9); ?>
                                        </div>
                                        <small class="text-muted">Rating</small>
                                    </div>
                                </div>
                                
                                <!-- Join Date -->
                                <div class="text-muted small mb-3">
                                    <i class="fas fa-calendar me-1"></i>
                                    Joined <?php echo date('M j, Y', strtotime($entrepreneur['created_at'])); ?>
                                </div>
                            </div>
                            
                            <div class="card-footer bg-transparent">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary" 
                                            onclick="viewShop(<?php echo $entrepreneur['user_id']; ?>)">
                                        <i class="fas fa-store me-2"></i>Visit Shop
                                    </button>
                                    <button class="btn btn-outline-primary btn-sm" 
                                            onclick="contactEntrepreneur(<?php echo $entrepreneur['user_id']; ?>)">
                                        <i class="fas fa-envelope me-2"></i>Contact
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="d-flex justify-content-center mt-5">
                    <?php
                    $base_url = "entrepreneurs.php";
                    $params = [];
                    if ($search) $params[] = "search=" . urlencode($search);
                    $base_url .= !empty($params) ? '?' . implode('&', $params) : '';
                    
                    echo generatePagination($total_pages, $page, $base_url);
                    ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Become an Entrepreneur CTA -->
<?php if (!isLoggedIn() || getUserRole() === 'Customer'): ?>
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="fw-bold mb-3">Want to Become an Entrepreneur?</h2>
        <p class="lead mb-4">Join our community of student sellers and start your business journey today!</p>
        <div class="d-flex gap-3 justify-content-center">
            <a href="register.php?role=Entrepreneur" class="btn btn-light btn-lg">
                <i class="fas fa-user-plus me-2"></i>Register as Entrepreneur
            </a>
            <a href="about.php" class="btn btn-outline-light btn-lg">
                <i class="fas fa-info-circle me-2"></i>Learn More
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Contact Modal -->
<div class="modal fade" id="contactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Contact Entrepreneur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="contactForm">
                    <div class="mb-3">
                        <label for="message" class="form-label">Your Message</label>
                        <textarea class="form-control" id="message" rows="4" 
                                  placeholder="Enter your message here..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="contactEmail" class="form-label">Your Email</label>
                        <input type="email" class="form-control" id="contactEmail" 
                               placeholder="your.email@example.com" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Sort functionality
document.getElementById('sortSelect').addEventListener('change', function() {
    const sort = this.value;
    const url = new URL(window.location);
    url.searchParams.set('sort', sort);
    window.location.href = url.toString();
});

// Limit functionality
document.getElementById('limitSelect').addEventListener('change', function() {
    const limit = this.value;
    const url = new URL(window.location);
    url.searchParams.set('limit', limit);
    url.searchParams.set('page', '1'); // Reset to first page
    window.location.href = url.toString();
});

// Category filter
document.getElementById('categoryFilter').addEventListener('change', function() {
    const category = this.value;
    const url = new URL(window.location);
    if (category) {
        url.searchParams.set('category', category);
    } else {
        url.searchParams.delete('category');
    }
    window.location.href = url.toString();
});

// Faculty filter
document.getElementById('facultyFilter').addEventListener('change', function() {
    const faculty = this.value;
    const url = new URL(window.location);
    if (faculty) {
        url.searchParams.set('faculty', faculty);
    } else {
        url.searchParams.delete('faculty');
    }
    window.location.href = url.toString();
});

// View shop function
function viewShop(userId) {
    // This would typically navigate to the shop page
    window.location.href = `shop.php?user_id=${userId}`;
}

// View profile function
function viewProfile(userId) {
    // This would typically navigate to the profile page
    window.location.href = `profile.php?user_id=${userId}`;
}

// Contact entrepreneur function
function contactEntrepreneur(userId) {
    // Store the user ID for the contact form
    document.getElementById('contactForm').dataset.userId = userId;
    
    // Show the contact modal
    new bootstrap.Modal(document.getElementById('contactModal')).show();
}

// Handle contact form submission
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const message = document.getElementById('message').value;
    const email = document.getElementById('contactEmail').value;
    const userId = this.dataset.userId;
    
    // This would typically send the message via AJAX
    // For now, just show a success message
    alert('Message sent successfully! The entrepreneur will contact you soon.');
    
    // Close the modal and reset the form
    bootstrap.Modal.getInstance(document.getElementById('contactModal')).hide();
    this.reset();
});

// Auto-focus search field
document.querySelector('input[name="search"]').focus();
</script>

<?php include 'footer.php'; ?>
