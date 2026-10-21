<?php
require_once __DIR__ . '/../db_connect.php';

if (!isLoggedIn()) {
    redirect('../login.php', 'Please log in to access your seller account.', 'warning');
}

if (getUserRole() !== 'Entrepreneur') {
    switch (getUserRole()) {
        case 'Admin':
            redirect('../admin/dashboard.php');
        case 'Customer':
            redirect('../customer/dashboard.php');
        default:
            redirect('../login.php', 'Seller access is required.', 'error');
    }
}

$seller_id = (int) $_SESSION['user_id'];
if (empty($_SESSION['seller_csrf_token'])) {
    $_SESSION['seller_csrf_token'] = bin2hex(random_bytes(32));
}

$shop = getShopByUserId($seller_id);
$categories = getAllCategories();
$errors = [];
$store_image_upload = static function ($file, $directory) use (&$errors) {
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        $errors[] = 'Image upload failed. Please choose another image.';
        return false;
    }

    if (filesize($file['tmp_name']) > 5 * 1024 * 1024) {
        $errors[] = 'Images must be 5 MB or smaller.';
        return false;
    }

    $allowed_types = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];
    $file_info = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $file_info->file($file['tmp_name']);
    if (!isset($allowed_types[$mime_type])) {
        $errors[] = 'Upload a JPG, PNG, or WebP image.';
        return false;
    }

    $upload_directory = __DIR__ . '/../uploads/' . $directory;
    if (!is_dir($upload_directory) && !mkdir($upload_directory, 0755, true) && !is_dir($upload_directory)) {
        $errors[] = 'Image storage is unavailable. Please contact the administrator.';
        return false;
    }

    $file_name = bin2hex(random_bytes(16)) . '.' . $allowed_types[$mime_type];
    if (!move_uploaded_file($file['tmp_name'], $upload_directory . '/' . $file_name)) {
        $errors[] = 'Unable to save the uploaded image.';
        return false;
    }

    return 'uploads/' . $directory . '/' . $file_name;
};
$remove_uploaded_image = static function ($image_path, $directory) {
    $prefix = 'uploads/' . $directory . '/';
    if (is_string($image_path) && strncmp($image_path, $prefix, strlen($prefix)) === 0) {
        $file_path = __DIR__ . '/../' . $image_path;
        if (is_file($file_path)) {
            unlink($file_path);
        }
    }
};
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $action = $_POST['action'] ?? '';

    if (!$token || !hash_equals($_SESSION['seller_csrf_token'], $token)) {
        $errors[] = 'Your session expired. Refresh the page and try again.';
    } elseif ($action === 'request_shop') {
        $shop_name = trim($_POST['shop_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $shop_image = $store_image_upload($_FILES['shop_image'] ?? null, 'shop-images');

        if ($shop) {
            $remove_uploaded_image($shop_image, 'shop-images');
            redirect('dashboard.php', 'A shop request already exists for your account.', 'warning');
        } elseif ($shop_name === '' || strlen($shop_name) > 100) {
            $remove_uploaded_image($shop_image, 'shop-images');
            $errors[] = 'Enter a shop name of 1 to 100 characters.';
        } elseif ($shop_image === null) {
            $errors[] = 'Upload a shop image to submit your request.';
        } elseif ($shop_image === false) {
            // The upload helper already added a validation error.
        } else {
            $stmt = $conn->prepare("INSERT INTO shops (user_id, shop_name, description, shop_image, status) VALUES (?, ?, ?, ?, 'Pending')");
            $stmt->bind_param('isss', $seller_id, $shop_name, $description, $shop_image);

            if ($stmt->execute()) {
                redirect('dashboard.php', 'Shop request sent to the administrator for approval.', 'success');
            }

            $remove_uploaded_image($shop_image, 'shop-images');
            $errors[] = 'Unable to submit your shop request. Please try again.';
        }
    } elseif ($action === 'update_shop') {
        $shop_name = trim($_POST['shop_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $new_shop_image = $store_image_upload($_FILES['shop_image'] ?? null, 'shop-images');
        if (!$shop) {
            $remove_uploaded_image($new_shop_image, 'shop-images');
            $errors[] = 'No shop is available to update.';
        } elseif ($shop_name === '' || strlen($shop_name) > 100) {
            $remove_uploaded_image($new_shop_image, 'shop-images');
            $errors[] = 'Enter a shop name of 1 to 100 characters.';
        } elseif ($new_shop_image === false) {
            // The upload helper already added a validation error.
        } else {
            if ($new_shop_image !== null) {
                $stmt = $conn->prepare('UPDATE shops SET shop_name = ?, description = ?, shop_image = ? WHERE shop_id = ? AND user_id = ?');
                $stmt->bind_param('sssii', $shop_name, $description, $new_shop_image, $shop['shop_id'], $seller_id);
            } else {
                $stmt = $conn->prepare('UPDATE shops SET shop_name = ?, description = ? WHERE shop_id = ? AND user_id = ?');
                $stmt->bind_param('ssii', $shop_name, $description, $shop['shop_id'], $seller_id);
            }
            if ($stmt->execute()) {
                if ($new_shop_image !== null) {
                    $remove_uploaded_image($shop['shop_image'] ?? null, 'shop-images');
                }
                redirect('dashboard.php', 'Shop details updated.', 'success');
            }
            $remove_uploaded_image($new_shop_image, 'shop-images');
            $errors[] = 'Unable to update shop details. Please try again.';
        }
    } elseif (!$shop || $shop['status'] !== 'Active') {
        $errors[] = 'Product management is available after your shop is approved.';
    } elseif ($action === 'delete_product') {
        $product_id = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$product_id) {
            $errors[] = 'Invalid product selected.';
        } else {
            $stmt = $conn->prepare('SELECT product_image FROM products WHERE product_id = ? AND shop_id = ?');
            $stmt->bind_param('ii', $product_id, $shop['shop_id']);
            $stmt->execute();
            $product_to_delete = $stmt->get_result()->fetch_assoc();
            $stmt = $conn->prepare('DELETE FROM products WHERE product_id = ? AND shop_id = ?');
            $stmt->bind_param('ii', $product_id, $shop['shop_id']);
            if ($product_to_delete && $stmt->execute() && $stmt->affected_rows === 1) {
                $remove_uploaded_image($product_to_delete['product_image'], 'product-images');
                redirect('dashboard.php', 'Product deleted.', 'success');
            }
            $errors[] = 'Product could not be deleted.';
        }
    } elseif ($action === 'create_product' || $action === 'update_product') {
        $product_id = $action === 'update_product' ? filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT) : null;
        $product_name = trim($_POST['product_name'] ?? '');
        $product_description = trim($_POST['product_description'] ?? '');
        $price_input = trim($_POST['price'] ?? '');
        $stock_input = trim($_POST['stock_quantity'] ?? '');
        $category_input = $_POST['category_id'] ?? '';
        $new_product_image = $store_image_upload($_FILES['product_image'] ?? null, 'product-images');
        $product_status = $_POST['status'] ?? 'Active';

        if ($product_name === '' || strlen($product_name) > 200) {
            $errors[] = 'Product name is required and must be 200 characters or fewer.';
        }
        if (!is_numeric($price_input) || (float) $price_input <= 0 || (float) $price_input > 99999999.99) {
            $errors[] = 'Enter a valid product price greater than zero.';
        }
        if (filter_var($stock_input, FILTER_VALIDATE_INT) === false || (int) $stock_input < 0) {
            $errors[] = 'Stock quantity must be a whole number of zero or more.';
        }
        if ($category_input !== '' && !in_array((int) $category_input, array_map('intval', array_column($categories, 'category_id')), true)) {
            $errors[] = 'Select a valid product category.';
        }
        if ($new_product_image === false) {
            // The upload helper already added a validation error.
        }
        if (!in_array($product_status, ['Active', 'Inactive'], true)) {
            $errors[] = 'Select a valid product status.';
        }
        if ($action === 'update_product' && !$product_id) {
            $errors[] = 'Invalid product selected.';
        }

        $current_product = null;
        if ($action === 'update_product' && $product_id) {
            $stmt = $conn->prepare('SELECT product_image FROM products WHERE product_id = ? AND shop_id = ?');
            $stmt->bind_param('ii', $product_id, $shop['shop_id']);
            $stmt->execute();
            $current_product = $stmt->get_result()->fetch_assoc();
            if (!$current_product) {
                $errors[] = 'Product not found in your shop.';
            }
        }

        if (!$errors) {
            $price = (float) $price_input;
            $stock_quantity = (int) $stock_input;
            $category_id = $category_input === '' ? null : (int) $category_input;
            $product_image = $new_product_image ?? ($current_product['product_image'] ?? '');
            $product_status = $stock_quantity === 0 ? 'Out of Stock' : $product_status;

            if ($action === 'create_product') {
                $stmt = $conn->prepare(
                    'INSERT INTO products (shop_id, product_name, description, price, stock_quantity, category_id, product_image, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->bind_param('issdiiss', $shop['shop_id'], $product_name, $product_description, $price, $stock_quantity, $category_id, $product_image, $product_status);
            } else {
                $stmt = $conn->prepare(
                    'UPDATE products
                     SET product_name = ?, description = ?, price = ?, stock_quantity = ?, category_id = ?, product_image = ?, status = ?
                     WHERE product_id = ? AND shop_id = ?'
                );
                $stmt->bind_param('ssdiissii', $product_name, $product_description, $price, $stock_quantity, $category_id, $product_image, $product_status, $product_id, $shop['shop_id']);
            }

            if ($stmt->execute()) {
                if ($new_product_image && $current_product) {
                    $remove_uploaded_image($current_product['product_image'], 'product-images');
                }
                redirect('dashboard.php', $action === 'create_product' ? 'Product added to your shop.' : 'Product updated.', 'success');
            }
            $errors[] = 'Unable to save this product. Please try again.';
        }
        $remove_uploaded_image($new_product_image, 'product-images');
    } else {
        $errors[] = 'Unknown seller action.';
    }
}

$status_classes = ['Pending' => 'warning', 'Active' => 'success', 'Inactive' => 'secondary'];
$products = [];
if ($shop && $shop['status'] === 'Active') {
    $stmt = $conn->prepare(
        'SELECT p.*, c.category_name
         FROM products p
         LEFT JOIN categories c ON c.category_id = p.category_id
         WHERE p.shop_id = ?
         ORDER BY p.created_at DESC'
    );
    $stmt->bind_param('i', $shop['shop_id']);
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
include __DIR__ . '/../header.php';
?>

<style>
    .seller-shell { max-width: 1050px; }
    .seller-heading { border-bottom: 1px solid #dce3e8; padding-bottom: 1.25rem; }
    .seller-panel { border: 1px solid #dce3e8; border-radius: 4px; background: #fff; }
    .seller-product + .seller-product { border-top: 1px solid #dce3e8; }
    .product-editor { border-top: 1px solid #e5e9ec; }
</style>

<main class="container seller-shell py-4 py-lg-5">
    <header class="seller-heading d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <p class="text-uppercase small fw-semibold text-muted mb-1">Seller account</p>
            <h1 class="h2 mb-1">Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Seller'); ?></h1>
            <p class="text-muted mb-0">Manage your shop approval request</p>
        </div>
        <a class="btn btn-outline-primary" href="products.php"><i class="fas fa-store me-2" aria-hidden="true"></i>Browse marketplace</a>
    </header>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
            <?php foreach ($errors as $error): ?>
                <div><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($shop): ?>
        <section class="seller-panel p-4" aria-labelledby="shop-status-heading">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <p class="text-uppercase small fw-semibold text-muted mb-1">Your shop request</p>
                    <h2 class="h4 mb-2" id="shop-status-heading"><?php echo htmlspecialchars($shop['shop_name']); ?></h2>
                    <p class="text-muted mb-0"><?php echo htmlspecialchars($shop['description'] ?: 'No shop description provided.'); ?></p>
                </div>
                <?php if (!empty($shop['shop_image'])): ?>
                    <img src="<?php echo htmlspecialchars($shop['shop_image']); ?>" alt="<?php echo htmlspecialchars($shop['shop_name']); ?> shop" class="img-thumbnail" style="width: 112px; height: 96px; object-fit: cover;">
                <?php endif; ?>
                <span class="badge text-bg-<?php echo $status_classes[$shop['status']] ?? 'secondary'; ?> fs-6"><?php echo htmlspecialchars($shop['status']); ?></span>
            </div>

            <?php if ($shop['status'] === 'Pending'): ?>
                <div class="alert alert-warning mt-4 mb-0" role="status">
                    Your shop request has been sent to the administrator. You can start selling after it is approved.
                </div>
            <?php elseif ($shop['status'] === 'Active'): ?>
                <div class="alert alert-success mt-4 mb-0" role="status">
                    Your shop is approved and active. You can now prepare your products for the marketplace.
                </div>
            <?php else: ?>
                <div class="alert alert-secondary mt-4 mb-0" role="status">
                    This shop is inactive. Contact the administrator for more information.
                </div>
            <?php endif; ?>

            <form method="post" action="seller/dashboard.php" enctype="multipart/form-data" class="mt-4 pt-4 border-top">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['seller_csrf_token']); ?>">
                <input type="hidden" name="action" value="update_shop">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="updated-shop-name" class="form-label">Shop name</label>
                        <input class="form-control" id="updated-shop-name" name="shop_name" maxlength="100" required value="<?php echo htmlspecialchars($shop['shop_name']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="updated-shop-image" class="form-label">Shop logo</label>
                        <input class="form-control" id="updated-shop-image" name="shop_image" type="file" accept="image/jpeg,image/png,image/webp">
                        <div class="form-text">Optional. JPG, PNG, or WebP up to 5 MB. Upload a new file to replace the current logo.</div>
                    </div>
                    <div class="col-12">
                        <label for="updated-shop-description" class="form-label">Description</label>
                        <textarea class="form-control" id="updated-shop-description" name="description" rows="3"><?php echo htmlspecialchars($shop['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="col-12 d-flex justify-content-end pt-2">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-floppy-disk me-2" aria-hidden="true"></i>Save shop details</button>
                    </div>
                </div>
            </form>
        </section>

            <?php if ($shop['status'] === 'Active'): ?>
                <section class="mt-5" aria-labelledby="products-heading">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <p class="text-uppercase small fw-semibold text-muted mb-1">Inventory</p>
                            <h2 class="h4 mb-0" id="products-heading">Shop products <span class="text-muted fs-6">(<?php echo count($products); ?>)</span></h2>
                        </div>
                    </div>

                    <div class="seller-panel p-4 mb-4">
                        <h3 class="h5 mb-3">Add a product</h3>
                        <form method="post" action="seller/dashboard.php" enctype="multipart/form-data" class="row g-3">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['seller_csrf_token']); ?>">
                            <input type="hidden" name="action" value="create_product">
                            <div class="col-md-6">
                                <label class="form-label" for="new-product-name">Product name</label>
                                <input class="form-control" id="new-product-name" name="product_name" maxlength="200" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="new-product-price">Price (Rs)</label>
                                <input class="form-control" id="new-product-price" name="price" type="number" min="0.01" max="99999999.99" step="0.01" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="new-product-stock">Stock quantity</label>
                                <input class="form-control" id="new-product-stock" name="stock_quantity" type="number" min="0" step="1" value="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="new-product-category">Category</label>
                                <select class="form-select" id="new-product-category" name="category_id">
                                    <option value="">No category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo (int) $category['category_id']; ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="new-product-image">Product image <span class="text-muted">(optional, up to 5 MB)</span></label>
                                <input class="form-control" id="new-product-image" name="product_image" type="file" accept="image/jpeg,image/png,image/webp">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="new-product-description">Description</label>
                                <textarea class="form-control" id="new-product-description" name="product_description" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary" type="submit"><i class="fas fa-plus me-2" aria-hidden="true"></i>Add product</button>
                            </div>
                        </form>
                    </div>

                    <?php if (!$products): ?>
                        <div class="border rounded-1 bg-white p-4 text-center text-muted">No products yet. Add your first product above.</div>
                    <?php else: ?>
                        <div class="seller-panel">
                            <?php foreach ($products as $product): ?>
                                <article class="seller-product p-3 p-lg-4">
                                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                                        <div>
                                            <h3 class="h5 mb-1"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                                            <p class="text-muted small mb-2"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?> · <?php echo formatPrice($product['price']); ?> · Stock: <?php echo (int) $product['stock_quantity']; ?></p>
                                            <span class="badge text-bg-<?php echo $product['status'] === 'Active' ? 'success' : ($product['status'] === 'Out of Stock' ? 'warning' : 'secondary'); ?>"><?php echo htmlspecialchars($product['status']); ?></span>
                                        </div>
                                        <form method="post" action="seller/dashboard.php" onsubmit="return confirm('Delete this product?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['seller_csrf_token']); ?>">
                                            <input type="hidden" name="action" value="delete_product">
                                            <input type="hidden" name="product_id" value="<?php echo (int) $product['product_id']; ?>">
                                            <button class="btn btn-outline-danger btn-sm" type="submit"><i class="fas fa-trash me-1" aria-hidden="true"></i>Delete</button>
                                        </form>
                                    </div>
                                    <details class="product-editor mt-3 pt-3">
                                        <summary class="text-primary">Update product</summary>
                                        <form method="post" action="seller/dashboard.php" enctype="multipart/form-data" class="row g-3 mt-1">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['seller_csrf_token']); ?>">
                                            <input type="hidden" name="action" value="update_product">
                                            <input type="hidden" name="product_id" value="<?php echo (int) $product['product_id']; ?>">
                                            <div class="col-md-6">
                                                <label class="form-label" for="product-name-<?php echo (int) $product['product_id']; ?>">Product name</label>
                                                <input class="form-control" id="product-name-<?php echo (int) $product['product_id']; ?>" name="product_name" maxlength="200" required value="<?php echo htmlspecialchars($product['product_name']); ?>">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label" for="product-price-<?php echo (int) $product['product_id']; ?>">Price (Rs)</label>
                                                <input class="form-control" id="product-price-<?php echo (int) $product['product_id']; ?>" name="price" type="number" min="0.01" max="99999999.99" step="0.01" required value="<?php echo htmlspecialchars($product['price']); ?>">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label" for="product-stock-<?php echo (int) $product['product_id']; ?>">Stock quantity</label>
                                                <input class="form-control" id="product-stock-<?php echo (int) $product['product_id']; ?>" name="stock_quantity" type="number" min="0" step="1" required value="<?php echo (int) $product['stock_quantity']; ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label" for="product-category-<?php echo (int) $product['product_id']; ?>">Category</label>
                                                <select class="form-select" id="product-category-<?php echo (int) $product['product_id']; ?>" name="category_id">
                                                    <option value="">No category</option>
                                                    <?php foreach ($categories as $category): ?>
                                                        <option value="<?php echo (int) $category['category_id']; ?>" <?php echo (int) $product['category_id'] === (int) $category['category_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['category_name']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label" for="product-image-<?php echo (int) $product['product_id']; ?>">Replace product image</label>
                                                <input class="form-control" id="product-image-<?php echo (int) $product['product_id']; ?>" name="product_image" type="file" accept="image/jpeg,image/png,image/webp">
                                                <?php if (!empty($product['product_image'])): ?>
                                                    <img src="<?php echo htmlspecialchars($product['product_image']); ?>" alt="Current <?php echo htmlspecialchars($product['product_name']); ?> image" class="img-thumbnail mt-2" style="width: 96px; height: 72px; object-fit: cover;">
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label" for="product-status-<?php echo (int) $product['product_id']; ?>">Visibility</label>
                                                <select class="form-select" id="product-status-<?php echo (int) $product['product_id']; ?>" name="status">
                                                    <option value="Active" <?php echo $product['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                                                    <option value="Inactive" <?php echo $product['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label" for="product-description-<?php echo (int) $product['product_id']; ?>">Description</label>
                                                <textarea class="form-control" id="product-description-<?php echo (int) $product['product_id']; ?>" name="product_description" rows="3"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                                            </div>
                                            <div class="col-12">
                                                <button class="btn btn-outline-primary" type="submit"><i class="fas fa-floppy-disk me-2" aria-hidden="true"></i>Save changes</button>
                                            </div>
                                        </form>
                                    </details>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
    <?php else: ?>
        <section class="seller-panel p-4" aria-labelledby="request-shop-heading">
            <h2 class="h4 mb-2" id="request-shop-heading">Request a shop</h2>
            <p class="text-muted mb-4">Submit your shop details for administrator approval. Your shop will not be active in the marketplace until it is approved.</p>
            <form method="post" action="seller/dashboard.php" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['seller_csrf_token']); ?>">
                <input type="hidden" name="action" value="request_shop">
                <div class="mb-3">
                    <label for="shop_name" class="form-label">Shop name</label>
                    <input class="form-control" type="text" id="shop_name" name="shop_name" maxlength="100" required value="<?php echo htmlspecialchars($_POST['shop_name'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="shop_image" class="form-label">Shop image</label>
                    <input class="form-control" type="file" id="shop_image" name="shop_image" accept="image/jpeg,image/png,image/webp" required>
                    <div class="form-text">Choose a JPG, PNG, or WebP image up to 5 MB.</div>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description <span class="text-muted">(optional)</span></label>
                    <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>
                <button class="btn btn-primary" type="submit"><i class="fas fa-paper-plane me-2" aria-hidden="true"></i>Send approval request</button>
            </form>
        </section>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/../footer.php'; ?>
