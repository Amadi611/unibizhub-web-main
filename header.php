<?php
$header_base_path = in_array(basename(dirname($_SERVER['SCRIPT_NAME'] ?? '')), ['admin', 'customer', 'seller'], true) ? '../' : '';
$header_is_homepage = basename($_SERVER['SCRIPT_NAME'] ?? '') === 'index.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if ($header_is_homepage): ?>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <?php endif; ?>
    <?php if ($header_base_path !== ''): ?>
        <base href="../">
    <?php endif; ?>
    <title>UNIBIZHUB - Entrepreneur Online Business Platform</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css?v=<?php echo (int) filemtime(__DIR__ . '/style.css'); ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="favicon.ico">
</head>
<body class="<?php echo $header_is_homepage ? 'home-page' : ''; ?>">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <img src="./images/logo.svg" 
                     alt="UNIBIZHUB Logo" 
                     class="logo-image-only">
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                            <i class="fas fa-home me-1"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>" href="products.php">
                            <i class="fas fa-shopping-bag me-1"></i> Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'entrepreneurs.php' ? 'active' : ''; ?>" href="entrepreneurs.php">
                            <i class="fas fa-users me-1"></i> Entrepreneurs
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-info-circle me-1"></i> About
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="about.php">About Us</a></li>
                            <li><a class="dropdown-item" href="services.php">Our Services</a></li>
                            <li><a class="dropdown-item" href="contact.php">Contact Us</a></li>
                        </ul>
                    </li>
                </ul>
                
                <!-- Search Bar -->
                <form class="d-flex me-3" action="products.php" method="GET">
                    <input class="form-control me-2" type="search" name="search" placeholder="Search products..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button class="btn btn-orange-outline" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                
                <!-- User Menu -->
                <ul class="navbar-nav">
                    <!-- Cart Icon -->
                    <li class="nav-item me-2">
                        <a class="nav-link position-relative" href="cart.php">
                            <i class="fas fa-shopping-cart fa-lg"></i>
                            <?php if (function_exists('getCartCount') && getCartCount() > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                    <?php echo getCartCount(); ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i> 
                                <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if (getUserRole() == 'Admin'): ?>
                                    <li><a class="dropdown-item" href="admin/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Admin Dashboard</a></li>
                                <?php elseif (getUserRole() == 'Entrepreneur'): ?>
                                    <li><a class="dropdown-item" href="seller/dashboard.php"><i class="fas fa-store me-2"></i> My Shop</a></li>
                                <?php else: ?>
                                    <li><a class="dropdown-item" href="customer/dashboard.php"><i class="fas fa-user me-2"></i> My Account</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt me-1"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class="fas fa-user-plus me-1"></i> Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if (function_exists('displayMessage')): ?>
        <div class="container mt-3">
            <?php displayMessage(); ?>
        </div>
    <?php endif; ?>
