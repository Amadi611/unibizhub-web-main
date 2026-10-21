<?php
require_once __DIR__ . '/../db_connect.php';

if (!isLoggedIn() || getUserRole() !== 'Admin') {
    redirect('../login.php', 'Administrator access is required.', 'error');
}

if (empty($_SESSION['admin_csrf_token'])) {
    $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $action = $_POST['action'] ?? '';

    if (!$token || !hash_equals($_SESSION['admin_csrf_token'], $token)) {
        redirect('dashboard.php', 'Your session expired. Please try again.', 'error');
    }

    if ($action === 'approve_shop' || $action === 'cancel_shop') {
        $shop_id = filter_input(INPUT_POST, 'shop_id', FILTER_VALIDATE_INT);
        if (!$shop_id) {
            redirect('dashboard.php', 'Invalid shop selected.', 'error');
        }

        try {
            $conn->begin_transaction();
            $stmt = $conn->prepare(
                "SELECT s.user_id, s.status FROM shops s
                 JOIN users u ON u.user_id = s.user_id
                 WHERE s.shop_id = ? AND u.role = 'Entrepreneur'
                 FOR UPDATE"
            );
            $stmt->bind_param('i', $shop_id);
            $stmt->execute();
            $shop = $stmt->get_result()->fetch_assoc();

            if (!$shop || ($action === 'approve_shop' && $shop['status'] !== 'Pending') || ($action === 'cancel_shop' && !in_array($shop['status'], ['Active', 'Pending'], true))) {
                $conn->rollback();
                redirect('dashboard.php', 'This shop can no longer be changed.', 'warning');
            }

            if ($action === 'approve_shop') {
                $seller_id = (int) $shop['user_id'];
                $stmt = $conn->prepare("UPDATE shops SET status = 'Active' WHERE shop_id = ? AND status = 'Pending'");
                $stmt->bind_param('i', $shop_id);
                $stmt->execute();
                $stmt = $conn->prepare("UPDATE users SET status = 'Active' WHERE user_id = ? AND role = 'Entrepreneur'");
                $stmt->bind_param('i', $seller_id);
                $stmt->execute();
                $conn->commit();
                redirect('dashboard.php', 'Shop approved. The seller can now log in.', 'success');
            }

            $stmt = $conn->prepare("UPDATE shops SET status = 'Inactive' WHERE shop_id = ?");
            $stmt->bind_param('i', $shop_id);
            $stmt->execute();
            $conn->commit();
            redirect('dashboard.php', 'Shop cancelled. Its products are no longer available for sale.', 'success');
        } catch (Throwable $error) {
            $conn->rollback();
            redirect('dashboard.php', 'Unable to update this shop. Please try again.', 'error');
        }
    }

    if ($action === 'add_seller') {
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $phone = trim($_POST['phone'] ?? '');
        $faculty = trim($_POST['faculty'] ?? '');
        $student_id = trim($_POST['student_id'] ?? '');

        if ($full_name === '' || strlen($full_name) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100 || strlen($password) < 8 || $faculty === '' || strlen($faculty) > 100 || $student_id === '' || strlen($student_id) > 50 || ($phone !== '' && !preg_match('/^[0-9]{10,15}$/', $phone))) {
            redirect('dashboard.php', 'Enter valid seller details. Passwords must be at least 8 characters.', 'error');
        }

        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? OR (student_id = ? AND role = 'Entrepreneur') LIMIT 1");
        $stmt->bind_param('ss', $email, $student_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            redirect('dashboard.php', 'That email or student ID is already registered.', 'error');
        }

        $password_hash = hashPassword($password);
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role, phone, faculty, student_id, status) VALUES (?, ?, ?, 'Entrepreneur', ?, ?, ?, 'Active')");
        $stmt->bind_param('ssssss', $full_name, $email, $password_hash, $phone, $faculty, $student_id);
        if ($stmt->execute()) {
            redirect('dashboard.php', 'Seller account created. They can now log in and submit a shop for approval.', 'success');
        }
        redirect('dashboard.php', 'Seller account could not be created. Please try again.', 'error');
    }

    if ($action === 'add_customer') {
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $phone = trim($_POST['phone'] ?? '');

        if ($full_name === '' || strlen($full_name) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100 || strlen($password) < 8 || ($phone !== '' && !preg_match('/^[0-9]{10,15}$/', $phone))) {
            redirect('dashboard.php', 'Enter valid customer details. Passwords must be at least 8 characters.', 'error');
        }

        $stmt = $conn->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            redirect('dashboard.php', 'That email is already registered.', 'error');
        }

        $password_hash = hashPassword($password);
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role, phone, status) VALUES (?, ?, ?, 'Customer', ?, 'Active')");
        $stmt->bind_param('ssss', $full_name, $email, $password_hash, $phone);
        if ($stmt->execute()) {
            redirect('dashboard.php', 'Customer account created successfully.', 'success');
        }
        redirect('dashboard.php', 'Customer account could not be created. Please try again.', 'error');
    }

    redirect('dashboard.php', 'Unknown administrator action.', 'error');
}

$pending_count = (int) $conn->query(
    "SELECT COUNT(*) AS total FROM shops s
     JOIN users u ON u.user_id = s.user_id
     WHERE s.status = 'Pending' AND u.role = 'Entrepreneur'"
)->fetch_assoc()['total'];
$active_count = (int) $conn->query("SELECT COUNT(*) AS total FROM shops WHERE status = 'Active'")->fetch_assoc()['total'];
$customer_count = (int) $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'Customer'")->fetch_assoc()['total'];
$revenue = (float) $conn->query(
    "SELECT COALESCE(SUM(p.amount), 0) AS total
     FROM payments p
     JOIN orders o ON o.order_id = p.order_id
     WHERE p.payment_status = 'Completed' AND o.order_status <> 'Cancelled'"
)->fetch_assoc()['total'];

$stmt = $conn->prepare(
    "SELECT s.shop_id, s.shop_name, s.description, s.created_at,
            s.shop_image,
            u.user_id, u.full_name, u.email, u.phone, u.faculty, u.student_id
     FROM shops s
     JOIN users u ON u.user_id = s.user_id
     WHERE s.status = 'Pending' AND u.role = 'Entrepreneur'
     ORDER BY s.created_at ASC"
);
$stmt->execute();
$pending_shops = $stmt->get_result();

$shop_result = $conn->query(
    "SELECT s.shop_id, s.shop_name, s.description, s.status, s.created_at,
            u.full_name, u.email, p.product_id, p.product_name, p.price,
            p.stock_quantity, p.status AS product_status
     FROM shops s
     JOIN users u ON u.user_id = s.user_id
     LEFT JOIN products p ON p.shop_id = s.shop_id
     WHERE u.role = 'Entrepreneur'
     ORDER BY s.created_at DESC, p.product_name ASC"
);
$shops = [];
while ($row = $shop_result->fetch_assoc()) {
    $shop_id = (int) $row['shop_id'];
    if (!isset($shops[$shop_id])) {
        $shops[$shop_id] = [
            'shop_id' => $shop_id,
            'shop_name' => $row['shop_name'],
            'description' => $row['description'],
            'status' => $row['status'],
            'created_at' => $row['created_at'],
            'full_name' => $row['full_name'],
            'email' => $row['email'],
            'products' => []
        ];
    }
    if ($row['product_id'] !== null) {
        $shops[$shop_id]['products'][] = [
            'product_name' => $row['product_name'],
            'price' => $row['price'],
            'stock_quantity' => $row['stock_quantity'],
            'status' => $row['product_status']
        ];
    }
}

$customers = $conn->query(
    "SELECT user_id, full_name, email, phone, status, created_at
     FROM users
     WHERE role = 'Customer'
     ORDER BY created_at DESC"
);
$sellers = $conn->query(
    "SELECT u.user_id, u.full_name, u.email, u.phone, u.faculty, u.student_id,
            u.status, u.created_at,
            GROUP_CONCAT(DISTINCT CONCAT(s.shop_name, ' (', s.status, ')') ORDER BY s.shop_name SEPARATOR ', ') AS shop_list
     FROM users u
     LEFT JOIN shops s ON s.user_id = u.user_id
     WHERE u.role = 'Entrepreneur'
     GROUP BY u.user_id, u.full_name, u.email, u.phone, u.faculty, u.student_id, u.status, u.created_at
     ORDER BY u.created_at DESC"
);
?>
<?php include __DIR__ . '/../header.php'; ?>

<style>
    .admin-shell { max-width: 1200px; }
    .admin-heading { border-bottom: 1px solid #dce3e8; padding-bottom: 1.25rem; }
    .admin-stat { background: #fff; border: 1px solid #dce3e8; border-left: 4px solid #e87532; padding: 1rem 1.25rem; height: 100%; }
    .admin-stat-label { color: #5e6a71; font-size: .875rem; }
    .admin-stat-value { color: #18343b; font-size: 1.75rem; font-weight: 700; }
    .shop-row + .shop-row { border-top: 1px solid #e4e9ec; }
    .shop-meta { color: #5e6a71; font-size: .9rem; }
    .shop-description { white-space: pre-line; }
    .shop-photo { width: 96px; height: 80px; object-fit: cover; }
    .product-list { min-width: 220px; }
    .product-list li + li { border-top: 1px solid #e4e9ec; }
    .admin-stat-value-revenue { font-size: 1.35rem; }
    @media (max-width: 575.98px) {
        .shop-actions { width: 100%; }
        .shop-actions button { width: 100%; }
    }
</style>

<main class="container admin-shell py-4 py-lg-5">
    <div class="admin-heading d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <p class="text-uppercase small fw-semibold text-muted mb-1">UNIBIZHUB administration</p>
            <h1 class="h2 mb-0">Administration dashboard</h1>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-3">
            <span class="text-muted">Signed in as <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Administrator'); ?></span>
            <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                <i class="fas fa-user-plus me-2" aria-hidden="true"></i>Add customer
            </button>
            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addSellerModal">
                <i class="fas fa-user-plus me-2" aria-hidden="true"></i>Add seller
            </button>
        </div>
    </div>

    <?php if ($pending_count > 0): ?>
        <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-3" role="status">
            <div>
                <i class="fas fa-bell me-2" aria-hidden="true"></i>
                <strong><?php echo number_format($pending_count); ?> seller<?php echo $pending_count === 1 ? '' : 's'; ?> <?php echo $pending_count === 1 ? 'has' : 'have'; ?> requested shop approval.</strong>
                Review the request<?php echo $pending_count === 1 ? '' : 's'; ?> before shops can become active.
            </div>
            <a class="btn btn-sm btn-outline-dark" href="#pending-heading">Review requests</a>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <section class="admin-stat" aria-label="Pending shops">
                <div class="admin-stat-label">Awaiting review</div>
                <div class="admin-stat-value"><?php echo number_format($pending_count); ?></div>
            </section>
        </div>
        <div class="col-sm-6 col-xl-3">
            <section class="admin-stat" aria-label="Active shops">
                <div class="admin-stat-label">Approved shops</div>
                <div class="admin-stat-value"><?php echo number_format($active_count); ?></div>
            </section>
        </div>
        <div class="col-sm-6 col-xl-3">
            <section class="admin-stat" aria-label="Total customers">
                <div class="admin-stat-label">Total customers</div>
                <div class="admin-stat-value"><?php echo number_format($customer_count); ?></div>
            </section>
        </div>
        <div class="col-sm-6 col-xl-3">
            <section class="admin-stat" aria-label="Paid revenue">
                <div class="admin-stat-label">Paid revenue</div>
                <div class="admin-stat-value admin-stat-value-revenue"><?php echo htmlspecialchars(formatPrice($revenue)); ?></div>
            </section>
        </div>
    </div>

    <section aria-labelledby="pending-heading">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h2 class="h5 mb-0" id="pending-heading">Pending applications</h2>
            <span class="badge text-bg-warning"><?php echo number_format($pending_count); ?> pending</span>
        </div>

        <?php if ($pending_shops->num_rows === 0): ?>
            <div class="border rounded-1 bg-white p-4 text-center text-muted">
                <i class="fas fa-circle-check fa-2x mb-2" aria-hidden="true"></i>
                <p class="mb-0">There are no shops waiting for approval.</p>
            </div>
        <?php else: ?>
            <div class="bg-white border rounded-1">
                <?php while ($shop = $pending_shops->fetch_assoc()): ?>
                    <article class="shop-row p-3 p-lg-4">
                        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                            <?php if (!empty($shop['shop_image'])): ?>
                                <img class="shop-photo img-thumbnail" src="<?php echo htmlspecialchars($shop['shop_image']); ?>" alt="<?php echo htmlspecialchars($shop['shop_name']); ?> shop">
                            <?php endif; ?>
                            <div class="flex-grow-1">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    <h3 class="h5 mb-0"><?php echo htmlspecialchars($shop['shop_name']); ?></h3>
                                    <span class="badge text-bg-warning">Pending</span>
                                </div>
                                <p class="shop-description mb-3"><?php echo htmlspecialchars($shop['description'] ?: 'No shop description provided.'); ?></p>
                                <div class="shop-meta d-flex flex-wrap gap-2 column-gap-4">
                                    <span><i class="fas fa-user me-1" aria-hidden="true"></i><?php echo htmlspecialchars($shop['full_name']); ?></span>
                                    <span><i class="fas fa-envelope me-1" aria-hidden="true"></i><?php echo htmlspecialchars($shop['email']); ?></span>
                                    <?php if (!empty($shop['phone'])): ?>
                                        <span><i class="fas fa-phone me-1" aria-hidden="true"></i><?php echo htmlspecialchars($shop['phone']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($shop['faculty'])): ?>
                                        <span><i class="fas fa-graduation-cap me-1" aria-hidden="true"></i><?php echo htmlspecialchars($shop['faculty']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($shop['student_id'])): ?>
                                        <span>Student ID: <?php echo htmlspecialchars($shop['student_id']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="small text-muted mt-2">Submitted <?php echo htmlspecialchars(date('M j, Y', strtotime($shop['created_at']))); ?></div>
                            </div>
                            <form class="shop-actions align-self-lg-start" method="post" action="admin/dashboard.php" onsubmit="return confirm('Approve this shop and activate the seller account?');">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['admin_csrf_token']); ?>">
                                <input type="hidden" name="action" value="approve_shop">
                                <input type="hidden" name="shop_id" value="<?php echo (int) $shop['shop_id']; ?>">
                                <button class="btn btn-success" type="submit">
                                    <i class="fas fa-check me-2" aria-hidden="true"></i>Approve shop
                                </button>
                            </form>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="mt-5" aria-labelledby="sellers-heading">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
            <h2 class="h5 mb-0" id="sellers-heading">Seller accounts</h2>
            <span class="badge text-bg-secondary"><?php echo number_format($sellers->num_rows); ?> sellers</span>
        </div>
        <?php if ($sellers->num_rows === 0): ?>
            <div class="border rounded-1 bg-white p-4 text-center text-muted">No seller accounts have been created yet.</div>
        <?php else: ?>
            <div class="table-responsive bg-white border rounded-1">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Seller</th>
                            <th scope="col">Contact</th>
                            <th scope="col">Faculty / Student ID</th>
                            <th scope="col">Shop(s)</th>
                            <th scope="col">Account</th>
                            <th scope="col">Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($seller = $sellers->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($seller['full_name']); ?></strong></td>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($seller['email']); ?>"><?php echo htmlspecialchars($seller['email']); ?></a>
                                    <div class="small text-muted"><?php echo htmlspecialchars($seller['phone'] ?: 'No phone provided'); ?></div>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($seller['faculty'] ?: 'Not provided'); ?>
                                    <div class="small text-muted">ID: <?php echo htmlspecialchars($seller['student_id'] ?: 'Not provided'); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($seller['shop_list'] ?: 'No shop yet'); ?></td>
                                <td><span class="badge text-bg-<?php echo $seller['status'] === 'Active' ? 'success' : ($seller['status'] === 'Pending' ? 'warning' : 'secondary'); ?>"><?php echo htmlspecialchars($seller['status']); ?></span></td>
                                <td><?php echo htmlspecialchars(date('M j, Y', strtotime($seller['created_at']))); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="mt-5" aria-labelledby="customers-heading">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
            <h2 class="h5 mb-0" id="customers-heading">Customer accounts</h2>
            <span class="badge text-bg-secondary"><?php echo number_format($customers->num_rows); ?> customers</span>
        </div>
        <?php if ($customers->num_rows === 0): ?>
            <div class="border rounded-1 bg-white p-4 text-center text-muted">No customer accounts have been created yet.</div>
        <?php else: ?>
            <div class="table-responsive bg-white border rounded-1">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Customer</th>
                            <th scope="col">Contact</th>
                            <th scope="col">Account</th>
                            <th scope="col">Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($customer = $customers->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($customer['full_name']); ?></strong></td>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($customer['email']); ?>"><?php echo htmlspecialchars($customer['email']); ?></a>
                                    <div class="small text-muted"><?php echo htmlspecialchars($customer['phone'] ?: 'No phone provided'); ?></div>
                                </td>
                                <td><span class="badge text-bg-<?php echo $customer['status'] === 'Active' ? 'success' : ($customer['status'] === 'Pending' ? 'warning' : 'secondary'); ?>"><?php echo htmlspecialchars($customer['status']); ?></span></td>
                                <td><?php echo htmlspecialchars(date('M j, Y', strtotime($customer['created_at']))); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="mt-5" aria-labelledby="shops-heading">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
            <h2 class="h5 mb-0" id="shops-heading">All shops and products</h2>
            <span class="badge text-bg-secondary"><?php echo number_format(count($shops)); ?> shops</span>
        </div>
        <?php if (!$shops): ?>
            <div class="border rounded-1 bg-white p-4 text-center text-muted">No seller shops have been created yet.</div>
        <?php else: ?>
            <div class="table-responsive bg-white border rounded-1">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Shop</th>
                            <th scope="col">Seller</th>
                            <th scope="col">Status</th>
                            <th scope="col">Products</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shops as $shop): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($shop['shop_name']); ?></strong>
                                    <div class="small text-muted"><?php echo htmlspecialchars(date('M j, Y', strtotime($shop['created_at']))); ?></div>
                                    <?php if (!empty($shop['description'])): ?><div class="small text-muted mt-1"><?php echo htmlspecialchars($shop['description']); ?></div><?php endif; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($shop['full_name']); ?>
                                    <div class="small text-muted"><?php echo htmlspecialchars($shop['email']); ?></div>
                                </td>
                                <td><span class="badge text-bg-<?php echo $shop['status'] === 'Active' ? 'success' : ($shop['status'] === 'Pending' ? 'warning' : 'secondary'); ?>"><?php echo htmlspecialchars($shop['status']); ?></span></td>
                                <td>
                                    <?php if (!$shop['products']): ?>
                                        <span class="text-muted">No products listed</span>
                                    <?php else: ?>
                                        <ul class="product-list list-unstyled mb-0">
                                            <?php foreach ($shop['products'] as $product): ?>
                                                <li class="py-2">
                                                    <div class="d-flex justify-content-between gap-3">
                                                        <strong><?php echo htmlspecialchars($product['product_name']); ?></strong>
                                                        <span><?php echo htmlspecialchars(formatPrice((float) $product['price'])); ?></span>
                                                    </div>
                                                    <div class="small text-muted"><?php echo number_format((int) $product['stock_quantity']); ?> in stock · <?php echo htmlspecialchars($product['status']); ?></div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (in_array($shop['status'], ['Active', 'Pending'], true)): ?>
                                        <form method="post" action="admin/dashboard.php" onsubmit="return confirm('Cancel this shop? It will no longer be visible for shopping.');">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['admin_csrf_token']); ?>">
                                            <input type="hidden" name="action" value="cancel_shop">
                                            <input type="hidden" name="shop_id" value="<?php echo (int) $shop['shop_id']; ?>">
                                            <button class="btn btn-sm btn-outline-danger" type="submit"><i class="fas fa-ban me-1" aria-hidden="true"></i>Cancel shop</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>

<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="admin/dashboard.php">
                <div class="modal-header">
                    <h2 class="modal-title h5" id="addCustomerModalLabel">Add customer</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['admin_csrf_token']); ?>">
                    <input type="hidden" name="action" value="add_customer">
                    <div class="mb-3"><label class="form-label" for="customer-full-name">Full name</label><input class="form-control" id="customer-full-name" name="full_name" maxlength="100" required></div>
                    <div class="mb-3"><label class="form-label" for="customer-email">Email</label><input class="form-control" id="customer-email" name="email" type="email" maxlength="100" required></div>
                    <div class="mb-3"><label class="form-label" for="customer-password">Temporary password</label><input class="form-control" id="customer-password" name="password" type="password" minlength="8" required></div>
                    <div><label class="form-label" for="customer-phone">Phone</label><input class="form-control" id="customer-phone" name="phone" inputmode="numeric" pattern="[0-9]{10,15}" maxlength="15"><div class="form-text">Enter 10 to 15 digits, or leave blank.</div></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary" type="submit"><i class="fas fa-user-plus me-2" aria-hidden="true"></i>Create customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addSellerModal" tabindex="-1" aria-labelledby="addSellerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="admin/dashboard.php">
                <div class="modal-header">
                    <h2 class="modal-title h5" id="addSellerModalLabel">Add seller</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['admin_csrf_token']); ?>">
                    <input type="hidden" name="action" value="add_seller">
                    <div class="mb-3"><label class="form-label" for="seller-full-name">Full name</label><input class="form-control" id="seller-full-name" name="full_name" maxlength="100" required></div>
                    <div class="mb-3"><label class="form-label" for="seller-email">Email</label><input class="form-control" id="seller-email" name="email" type="email" maxlength="100" required></div>
                    <div class="mb-3"><label class="form-label" for="seller-password">Temporary password</label><input class="form-control" id="seller-password" name="password" type="password" minlength="8" required></div>
                    <div class="mb-3"><label class="form-label" for="seller-phone">Phone</label><input class="form-control" id="seller-phone" name="phone" inputmode="numeric" pattern="[0-9]{10,15}" maxlength="15"><div class="form-text">Enter 10 to 15 digits, or leave blank.</div></div>
                    <div class="mb-3"><label class="form-label" for="seller-faculty">Faculty</label><input class="form-control" id="seller-faculty" name="faculty" maxlength="100" required></div>
                    <div><label class="form-label" for="seller-student-id">Student ID</label><input class="form-control" id="seller-student-id" name="student_id" maxlength="50" required></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary" type="submit"><i class="fas fa-user-plus me-2" aria-hidden="true"></i>Create seller</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../footer.php'; ?>
