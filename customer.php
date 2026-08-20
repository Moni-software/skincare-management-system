<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$success_msg = "";
$error_msg = "";

if (isset($_GET['success'])) {
    if ($_GET['success'] == 'details_updated') $success_msg = "Your profile details were updated successfully.";
    if ($_GET['success'] == 'complaint_added') $success_msg = "Your complaint has been submitted successfully to administration.";
    if ($_GET['success'] == 'order_placed') $success_msg = "Your order has been placed successfully.";
}

/* UPDATE PROFILE*/

if (isset($_POST['update_details'])) {
    $new_name = trim($_POST['name'] ?? '');
    $new_address = trim($_POST['address'] ?? '');
    $new_phone = trim($_POST['phone'] ?? '');

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($file_extension, $allowed_extensions, true)) {
            $new_filename = "profile_" . $customer_id . "_" . time() . "." . $file_extension;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                $img_stmt = $conn->prepare("UPDATE customers SET profile_image = ? WHERE id = ?");
                $img_stmt->bind_param("si", $new_filename, $customer_id);
                $img_stmt->execute();
                $img_stmt->close();
            }
        }
    }

    $up_stmt = $conn->prepare("UPDATE customers SET name = ?, address = ?, phone = ? WHERE id = ?");
    $up_stmt->bind_param("sssi", $new_name, $new_address, $new_phone, $customer_id);
    if ($up_stmt->execute()) {
        $up_stmt->close();
        header("Location: customer.php?success=details_updated");
        exit();
    } else {
        $error_msg = "Failed to update profile details.";
        $up_stmt->close();
    }
}

/*UPDATE CART QUANTITY / DELETE ITEM (AJAX)*/

if (isset($_POST['action_type'])) {
    $cart_id = intval($_POST['cart_id'] ?? 0);
    $action_type = $_POST['action_type'];

    if ($action_type === 'delete' && $cart_id > 0) {
        $del_cart = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND customer_id = ?");
        $del_cart->bind_param("ii", $cart_id, $customer_id);
        $del_cart->execute();
        $del_cart->close();
    } elseif ($action_type === 'update' && $cart_id > 0) {
        $new_qty = intval($_POST['quantity'] ?? 1);
        if ($new_qty > 0) {
            $up_cart = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND customer_id = ?");
            $up_cart->bind_param("iii", $new_qty, $cart_id, $customer_id);
            $up_cart->execute();
            $up_cart->close();
        } else {
            $del_cart = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND customer_id = ?");
            $del_cart->bind_param("ii", $cart_id, $customer_id);
            $del_cart->execute();
            $del_cart->close();
        }
    }
    exit();
}

/*SUBMIT COMPLAINT */

$complaint_error = "";
if (isset($_POST['submit_complaint'])) {
    $order_id = intval($_POST['order_id']);
    $message = trim($_POST['message']);

    $chk_order = $conn->prepare("SELECT order_id FROM orders WHERE order_id = ? AND customer_id = ?");
    $chk_order->bind_param("ii", $order_id, $customer_id);
    $chk_order->execute();
    $chk_res = $chk_order->get_result();

    if ($chk_res->num_rows > 0) {
        $ins_comp = $conn->prepare("INSERT INTO complaints (customer_id, order_id, message, status) VALUES (?, ?, ?, 'Pending')");
        $ins_comp->bind_param("iis", $customer_id, $order_id, $message);
        $ins_comp->execute();
        $ins_comp->close();
        $chk_order->close();
        header("Location: customer.php?success=complaint_added");
        exit();
    } else {
        $complaint_error = "Please enter a correct order ID that belongs to your account.";
    }
    $chk_order->close();
}

/* CHECKOUT / PLACE ORDER*/

if (isset($_POST['checkout_order'])) {
    $sum_stmt = $conn->prepare("SELECT SUM(product_price * quantity) AS total FROM cart WHERE customer_id = ?");
    $sum_stmt->bind_param("i", $customer_id);
    $sum_stmt->execute();
    $sum_res = $sum_stmt->get_result()->fetch_assoc();
    $total_amount = $sum_res['total'] ?? 0;
    $sum_stmt->close();

    if ($total_amount > 0) {
        $payment_method = $_POST['payment_method'] ?? 'Cash on Delivery';

        $prod_stmt = $conn->prepare("SELECT product_name, quantity FROM cart WHERE customer_id = ?");
        $prod_stmt->bind_param("i", $customer_id);
        $prod_stmt->execute();
        $cart_products = $prod_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $prod_stmt->close();

        $product_list = [];
        foreach ($cart_products as $product) {
            $product_list[] = $product['product_name'] . " x" . $product['quantity'];
        }
        $products_text = implode(", ", $product_list);

        $payment_status = ($payment_method === "Credit/Debit Card") ? "Paid" : "Pending";

        $order_stmt = $conn->prepare("INSERT INTO orders (customer_id, products, total_amount, status, payment_status) VALUES (?, ?, ?, 'Pending Delivery', ?)");
        $order_stmt->bind_param("isds", $customer_id, $products_text, $total_amount, $payment_status);
        $order_stmt->execute();
        $order_stmt->close();

        $del_cart_all = $conn->prepare("DELETE FROM cart WHERE customer_id = ?");
        $del_cart_all->bind_param("i", $customer_id);
        $del_cart_all->execute();
        $del_cart_all->close();

        header("Location: customer.php?success=order_placed");
        exit();
    }
}

/* FETCH CUSTOMER DATA SAFELY*/

$customer_query = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$customer_query->bind_param("i", $customer_id);
$customer_query->execute();
$customer_result = $customer_query->get_result();
$customer = $customer_result->fetch_assoc() ?: [];
$customer_query->close();

$customer_name = isset($customer['name']) ? $customer['name'] : 'Valued Customer';
$customer_email = isset($customer['email']) ? $customer['email'] : '';
$customer_address = isset($customer['address']) ? $customer['address'] : '';
$customer_phone = isset($customer['phone']) ? $customer['phone'] : '';
$customer_profile_image = isset($customer['profile_image']) ? $customer['profile_image'] : '';

$default_avatar = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='150' height='150' viewBox='0 0 24 24' fill='%2349362b'><path d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z'/></svg>";
$p_img = !empty($customer_profile_image) ? 'uploads/' . $customer_profile_image : $default_avatar;

/*STATISTICS & FETCH LISTS */

$tot_ord_q = $conn->prepare("SELECT COUNT(*) AS total FROM orders WHERE customer_id = ?");
$tot_ord_q->bind_param("i", $customer_id);
$tot_ord_q->execute();
$total_orders = $tot_ord_q->get_result()->fetch_assoc()['total'] ?? 0;
$tot_ord_q->close();

$pen_ord_q = $conn->prepare("SELECT COUNT(*) AS pending FROM orders WHERE customer_id = ? AND status = 'Pending Delivery'");
$pen_ord_q->bind_param("i", $customer_id);
$pen_ord_q->execute();
$pending_orders = $pen_ord_q->get_result()->fetch_assoc()['pending'] ?? 0;
$pen_ord_q->close();

$orders_stmt = $conn->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY order_date DESC");
$orders_stmt->bind_param("i", $customer_id);
$orders_stmt->execute();
$orders_items = $orders_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$orders_stmt->close();

$cart_stmt = $conn->prepare("SELECT * FROM cart WHERE customer_id = ?");
$cart_stmt->bind_param("i", $customer_id);
$cart_stmt->execute();
$cart_items = $cart_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$cart_stmt->close();

$cart_unique_count = count($cart_items);

$complaints_stmt = $conn->prepare("SELECT * FROM complaints WHERE customer_id = ? ORDER BY created_at DESC");
$complaints_stmt->bind_param("i", $customer_id);
$complaints_stmt->execute();
$complaints_items = $complaints_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$complaints_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Glow Care - Luxury Customer Dashboard</title>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    html {
        scroll-behavior: smooth;
    }
    body {
        font-family: "Segoe UI", Arial, sans-serif;
        background: #f7f2ec;
        color: #3b2c25;
        line-height: 1.6;
    }
    a {
        text-decoration: none;
    }
    
    .dashboard-wrapper {
        max-width: 1350px;
        margin: 40px auto;
        padding: 0 20px;
        display: flex;
        gap: 35px;
    }
    
    .sidebar {
        width: 290px;
        background: #ffffff;
        border: 1px solid #e8ddd2;
        border-radius: 14px;
        padding: 35px 22px;
        height: fit-content;
        box-shadow: 0 15px 40px rgba(67,48,35,0.06);
    }
    
    .sidebar-profile {
        text-align: center;
        padding-bottom: 25px;
        border-bottom: 1px solid #eee4da;
        margin-bottom: 22px;
    }
    
    .sidebar-profile img {
        width: 85px;
        height: 85px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #b88656;
        background: #fcfaf8;
        margin-bottom: 12px;
    }
    
    .sidebar-profile h3 {
        font-family: Georgia, serif;
        font-size: 19px;
        color: #3c2d25;
        font-weight: 400;
    }
    
    .sidebar-nav {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .sidebar-nav a {
        color: #5b4232;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 13px 18px;
        border-radius: 8px;
        cursor: pointer;
        transition: 0.3s;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .sidebar-nav a:hover,
    .sidebar-nav a.active {
        background: #b88656;
        color: #ffffff;
    }

    .cart-badge-count {
        background: #3c2d25;
        color: #fff;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
    }
    .sidebar-nav a.active .cart-badge-count {
        background: #fff;
        color: #b88656;
    }
    
    .main-content {
        flex: 1;
    }
    
    .section-tab {
        display: none;
    }
    
    .section-tab.active-tab {
        display: block;
        animation: fadeIn 0.4s ease forwards;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .welcome-card {
        background: linear-gradient(rgba(45, 32, 25, 0.88), rgba(45, 32, 25, 0.88)), url("images/hero-model.jpg") center/cover no-repeat;
        color: #ffffff;
        border-radius: 14px;
        padding: 40px;
        margin-bottom: 30px;
        box-shadow: 0 15px 40px rgba(67,48,35,0.08);
    }
    
    .welcome-card h2 {
        font-family: Georgia, serif;
        font-size: 30px;
        font-weight: 400;
        margin-bottom: 12px;
        border: none;
        padding: 0;
        color: #fff;
    }
    
    .welcome-card p {
        color: #f8f4ef;
        font-size: 15px;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 22px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: #ffffff;
        border: 1px solid #e8ddd2;
        border-left: 4px solid #b88656;
        border-radius: 14px;
        padding: 28px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(67,48,35,0.04);
    }
    
    .stat-card h3 {
        color: #77675c;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
    }
    
    .stat-card p {
        font-size: 28px;
        font-weight: 700;
        color: #8c6239;
    }
    
    .card-box {
        background: #ffffff;
        border: 1px solid #e8ddd2;
        border-radius: 14px;
        padding: 38px;
        margin-bottom: 30px;
        box-shadow: 0 15px 40px rgba(67,48,35,0.06);
    }
    
    .card-box h2 {
        font-family: Georgia, serif;
        font-size: 25px;
        font-weight: 400;
        color: #3c2d25;
        padding-bottom: 14px;
        margin-bottom: 22px;
        border-bottom: 1px solid #eee4da;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
        background: #ffffff;
    }
    
    th {
        background: #49362b;
        color: #ffffff;
        padding: 15px;
        text-align: left;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }
    
    td {
        padding: 16px;
        border-bottom: 1px solid #eee4da;
        font-size: 14px;
        color: #5b4232;
        vertical-align: middle;
    }
    
    .cart-img {
        width: 65px;
        height: 65px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #e8ddd2;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        background: #fcfaf8;
    }
    
    .qty-input {
        width: 70px;
        padding: 9px;
        text-align: center;
        border: 1px solid #e8ddd2;
        border-radius: 6px;
        font-size: 14px;
    }

    .btn-delete-cart {
        background: #b33939;
        color: #fff;
        border: none;
        padding: 7px 12px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        transition: 0.3s;
    }

    .btn-delete-cart:hover {
        background: #822525;
    }
    
    .form-group {
        margin-bottom: 22px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #49362b;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 13px 16px;
        border: 1px solid #e8ddd2;
        border-radius: 6px;
        font-size: 14px;
        color: #3b2c25;
        background: #fcfaf8;
    }
    
    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #b88656;
        background: #ffffff;
    }
    
    .btn {
        display: inline-block;
        background: #b88656;
        color: #ffffff;
        border: 0;
        padding: 13px 30px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        font-size: 12px;
        transition: 0.3s;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .btn:hover {
        background: #3c2d25;
        transform: translateY(-2px);
    }
    
    .error-msg {
        color: #b33939;
        font-size: 13px;
        margin-top: 5px;
        font-weight: 600;
    }
    
    .success-msg {
        background: #f0f7f4;
        color: #2d6a4f;
        padding: 16px;
        border: 1px solid #b7e4c7;
        border-radius: 8px;
        margin-bottom: 25px;
        text-align: center;
        font-weight: 600;
    }

    .payment-box-toggle {
        border: 1px solid #e8ddd2;
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 12px;
        background: #fcfaf8;
    }

    @media (max-width: 900px) {
        .dashboard-wrapper {
            flex-direction: column;
        }
        .sidebar {
            width: 100%;
        }
        .stats-grid {
            grid-template-columns: 1fr;
        }
        table {
            display: block;
            overflow-x: auto;
        }
    }
</style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="dashboard-wrapper">
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-profile">
            <img src="<?php echo htmlspecialchars($p_img); ?>" alt="Customer Profile">
            <h3><?php echo htmlspecialchars($customer_name); ?></h3>
        </div>
        <div class="sidebar-nav">
            <a onclick="switchTab('dashboard-tab')" id="nav-dashboard" class="active">Dashboard</a>
            <a onclick="switchTab('cart-tab')" id="nav-cart">
                <span>My Cart</span>
                <span class="cart-badge-count" id="sidebarCartBadge"><?php echo $cart_unique_count; ?></span>
            </a>
            <a onclick="switchTab('orders-tab')" id="nav-orders">My Orders</a>
            <a onclick="switchTab('complaints-tab')" id="nav-complaints">Support & Complaints</a>
            <a onclick="switchTab('profile-tab')" id="nav-profile">My Profile</a>
            <a href="deals.php">Deals & Products</a>
            <a href="customer_logout.php" style="color: #b33939;">Logout</a>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        <?php if (!empty($success_msg)): ?>
            <div class="success-msg"><?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>

        <!-- DASHBOARD TAB -->
        <div id="dashboard-tab" class="section-tab active-tab">
            <div class="welcome-card">
                <h2>Welcome Back, <?php echo htmlspecialchars($customer_name); ?>!</h2>
                <p>Manage your luxury beauty care routine, track your orders, and experience elegance at your fingertips.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Orders Placed</h3>
                    <p><?php echo $total_orders; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Pending Deliveries</h3>
                    <p><?php echo $pending_orders; ?></p>
                </div>
            </div>

            <div class="card-box">
                <h2>Recent Orders & Status</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Products</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($orders_items) > 0): ?>
                            <?php $count = 0; foreach ($orders_items as $ord): if ($count >= 3) break; $count++; ?>
                                <tr>
                                    <td><strong>#GLW-<?php echo $ord['order_id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($ord['products']); ?></td>
                                    <td>Rs. <?php echo number_format($ord['total_amount'], 2); ?></td>
                                    <td><strong style="color:#b88656;"><?php echo htmlspecialchars($ord['status']); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align:center;color:#77675c;padding:25px;">No recent orders found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- CART TAB -->
        <div id="cart-tab" class="section-tab">
            <div class="card-box">
                <h2>My Cart & Secure Checkout</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $grand_total = 0; 
                        if (count($cart_items) > 0): 
                            foreach ($cart_items as $cart): 
                                $subtotal = $cart['product_price'] * $cart['quantity'];
                                $grand_total += $subtotal;
                                
                                $db_img = '';
                                $is_deal = false;

                                if (!empty($cart['product_image'])) {
                                    $db_img = trim($cart['product_image']);
                                } elseif (!empty($cart['image'])) {
                                    $db_img = trim($cart['image']);
                                } else {
                                    if(!empty($cart['product_id'])) {
                                        $p_id_chk = $cart['product_id'];
                                        
                                        $prod_img_q = $conn->prepare("SELECT image FROM product WHERE P_id = ? LIMIT 1");
                                        $prod_img_q->bind_param("i", $p_id_chk);
                                        $prod_img_q->execute();
                                        $res_pi = $prod_img_q->get_result();
                                        if($r_pi = $res_pi->fetch_assoc()) {
                                            if(!empty($r_pi['image'])) $db_img = $r_pi['image'];
                                        }
                                        $prod_img_q->close();

                                        if(empty($db_img)) {
                                            $deal_img_q = $conn->prepare("SELECT image_url FROM deals WHERE id = ? LIMIT 1");
                                            $deal_img_q->bind_param("i", $p_id_chk);
                                            $deal_img_q->execute();
                                            $res_di = $deal_img_q->get_result();
                                            if($r_di = $res_di->fetch_assoc()) {
                                                if(!empty($r_di['image_url'])) {
                                                    $db_img = $r_di['image_url'];
                                                    $is_deal = true;
                                                }
                                            }
                                            $deal_img_q->close();
                                        }
                                    }
                                }

                                $resolved_img = 'image/default.jpg';
                                if (!empty($db_img) && $db_img !== 'N/A') {
                                    $clean_img = basename($db_img);
                                    
                                    if ($is_deal) {
                                        if (file_exists('images/' . $clean_img)) {
                                            $resolved_img = 'images/' . $clean_img;
                                        } else {
                                            $resolved_img = 'images/' . $clean_img;
                                        }
                                    } else {
                                        if (file_exists('image/' . $clean_img)) {
                                            $resolved_img = 'image/' . $clean_img;
                                        } elseif (file_exists('images/' . $clean_img)) {
                                            $resolved_img = 'images/' . $clean_img;
                                        } else {
                                            $resolved_img = 'image/' . $clean_img;
                                        }
                                    }
                                }
                        ?>
                            <tr data-cart-id="<?php echo $cart['cart_id']; ?>" data-price="<?php echo $cart['product_price']; ?>">
                                <td><img src="<?php echo htmlspecialchars($resolved_img); ?>" class="cart-img" alt="Product Image"></td>
                                <td><?php echo htmlspecialchars($cart['product_name']); ?></td>
                                <td>Rs. <?php echo number_format($cart['product_price'], 2); ?></td>
                                <td>
                                    <input type="number" min="1" value="<?php echo $cart['quantity']; ?>" class="qty-input" onchange="updateQuantity(this, <?php echo $cart['cart_id']; ?>, <?php echo $cart['product_price']; ?>)">
                                </td>
                                <td><strong>Rs. <span class="subtotal-text"><?php echo number_format($subtotal, 2); ?></span></strong></td>
                                <td>
                                    <button type="button" class="btn-delete-cart" onclick="deleteCartItem(<?php echo $cart['cart_id']; ?>)">Remove</button>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="6" style="text-align:center;color:#77675c;padding:30px;">Your cart is empty right now.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div id="checkoutSectionWrapper" style="<?php echo ($grand_total <= 0) ? 'display:none;' : ''; ?>">
                    <div style="margin-top:25px; padding:22px; background:#fcfaf8; border:1px solid #e8ddd2; border-radius:10px; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-family:Georgia,serif;font-size:18px;color:#3c2d25;text-transform:uppercase;letter-spacing:1px;">Total Investment:</span>
                        <span style="font-size:22px;font-weight:700;color:#8c6239;">Rs. <span id="grandTotalText"><?php echo number_format($grand_total, 2); ?></span></span>
                    </div>

                    <form action="customer.php" method="POST" onsubmit="return validateCreditCardForm()" style="margin-top:22px;">
                        <div class="form-group">
                            <label>Select Payment Method:</label>
                            <div class="payment-box-toggle">
                                <input type="radio" id="cod" name="payment_method" value="Cash on Delivery" checked onclick="toggleCardInputs(false)">
                                <label for="cod" style="display:inline;font-weight:normal;text-transform:none;">Cash on Delivery</label>
                            </div>
                            <div class="payment-box-toggle">
                                <input type="radio" id="cc" name="payment_method" value="Credit/Debit Card" onclick="toggleCardInputs(true)">
                                <label for="cc" style="display:inline;font-weight:normal;text-transform:none;">Bank Credit / Debit Card</label>
                                <div id="cardFields" style="display:none;margin-top:15px;">
                                    <div class="form-group">
                                        <label>Cardholder Name:</label>
                                        <input type="text" id="cardName" placeholder="Name on Card">
                                    </div>
                                    <div class="form-group">
                                        <label>Card Number:</label>
                                        <input type="text" id="cardNumber" maxlength="16" placeholder="16 digit card number">
                                        <div id="cardNumError" class="error-msg"></div>
                                    </div>
                                    <div class="form-group">
                                        <label>Expire Year:</label>
                                        <input type="text" id="cardYear" maxlength="4" placeholder="2027">
                                        <div id="cardYearError" class="error-msg"></div>
                                    </div>
                                    <div class="form-group">
                                        <label>CVV:</label>
                                        <input type="password" id="cardCvv" maxlength="3" placeholder="123">
                                        <div id="cardCvvError" class="error-msg"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" name="checkout_order" class="btn">Proceed to Secure Checkout</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ORDERS TAB -->
        <div id="orders-tab" class="section-tab">
            <div class="card-box">
                <h2>Recent Orders & Delivery Status</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Products</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($orders_items) > 0): ?>
                            <?php foreach ($orders_items as $ord): ?>
                                <tr>
                                    <td><strong>#GLW-<?php echo $ord['order_id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($ord['products']); ?></td>
                                    <td>Rs. <?php echo number_format($ord['total_amount'], 2); ?></td>
                                    <td><strong style="color:#b88656;"><?php echo htmlspecialchars($ord['status']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($ord['payment_status']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center;color:#77675c;padding:25px;">No orders found in your history.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- COMPLAINTS TAB -->
        <div id="complaints-tab" class="section-tab">
            <div class="card-box">
                <h2>Customer Support & Complaints</h2>
                <?php if (!empty($complaint_error)): ?>
                    <div class="error-msg" style="margin-bottom:15px;font-size:14px;"><?php echo htmlspecialchars($complaint_error); ?></div>
                <?php endif; ?>

                <form action="customer.php" method="POST">
                    <div class="form-group">
                        <label>Order ID:</label>
                        <input type="number" name="order_id" placeholder="Enter your valid Order ID" required>
                    </div>
                    <div class="form-group">
                        <label>Complaint Message:</label>
                        <textarea name="message" rows="4" placeholder="Describe your issue here..." required></textarea>
                    </div>
                    <button type="submit" name="submit_complaint" class="btn">Submit Complaint</button>
                </form>

                <h3 style="margin-top:35px;font-family:Georgia,serif;font-size:20px;color:#3c2d25;">Past Complaints & Admin Replies</h3>
                <table style="margin-top:15px;">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Complaint</th>
                            <th>Admin Reply</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($complaints_items) > 0): ?>
                            <?php foreach ($complaints_items as $comp): ?>
                                <tr>
                                    <td>#GLW-<?php echo $comp['order_id']; ?></td>
                                    <td><?php echo htmlspecialchars($comp['message']); ?></td>
                                    <td>
                                        <?php if (!empty($comp['admin_reply'])): ?>
                                            <?php echo htmlspecialchars($comp['admin_reply']); ?>
                                        <?php else: ?>
                                            <span style="color:#77675c;">Pending Admin Reply...</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($comp['status']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align:center;color:#77675c;padding:25px;">No complaints submitted yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PROFILE TAB -->
        <div id="profile-tab" class="section-tab">
            <div class="card-box">
                <h2>Customer Profile & Settings</h2>
                <?php if (!empty($error_msg)): ?>
                    <div class="error-msg" style="margin-bottom:15px;"><?php echo htmlspecialchars($error_msg); ?></div>
                <?php endif; ?>

                <form action="customer.php" method="POST" enctype="multipart/form-data">
                    <div style="display:flex;align-items:center;gap:25px;margin-bottom:25px;">
                        <img src="<?php echo htmlspecialchars($p_img); ?>" style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:2px solid #b88656;background:#fcfaf8;" id="profilePreview" alt="Profile">
                        <div>
                            <input type="file" name="profile_image" accept="image/*" style="margin-bottom:8px;">
                            <p style="color:#77675c;font-size:12px;">Choose a new luxury photo and click Save Changes.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Full Name:</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($customer_name); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Delivery Address:</label>
                        <textarea name="address" rows="3" required><?php echo htmlspecialchars($customer_address); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Telephone Number:</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($customer_phone); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address (Cannot be changed):</label>
                        <input type="email" value="<?php echo htmlspecialchars($customer_email); ?>" disabled style="background:#eee;">
                    </div>

                    <div style="display:flex;gap:15px;margin-top:25px;">
                        <button type="submit" name="update_details" class="btn">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tabId) {
    document.querySelectorAll('.section-tab').forEach(tab => tab.classList.remove('active-tab'));
    document.querySelectorAll('.sidebar-nav a').forEach(nav => nav.classList.remove('active'));
    
    const selectedTab = document.getElementById(tabId);
    if (selectedTab) selectedTab.classList.add('active-tab');
    
    const navMap = {
        'dashboard-tab': 'nav-dashboard',
        'cart-tab': 'nav-cart',
        'orders-tab': 'nav-orders',
        'complaints-tab': 'nav-complaints',
        'profile-tab': 'nav-profile'
    };
    if (navMap[tabId]) {
        document.getElementById(navMap[tabId]).classList.add('active');
    }
}

window.addEventListener('DOMContentLoaded', () => {
    if (window.location.hash === '#cart' || window.location.search.includes('tab=cart')) {
        switchTab('cart-tab');
    }
});

function updateQuantity(inputElement, cartId, price) {
    let qty = parseInt(inputElement.value);
    if (qty <= 0 || isNaN(qty)) { qty = 1; inputElement.value = 1; }
    
    const row = inputElement.closest('tr');
    const subtotal = qty * price;
    row.querySelector('.subtotal-text').textContent = subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    
    recalculateGrandTotal();

    const formData = new URLSearchParams();
    formData.append('action_type', 'update');
    formData.append('cart_id', cartId);
    formData.append('quantity', qty);
    
    fetch('customer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    });
}

function deleteCartItem(cartId) {
    const row = document.querySelector(`tr[data-cart-id="${cartId}"]`);
    if (row) {
        row.remove();
    }
    
    recalculateGrandTotal();

    const remainingRows = document.querySelectorAll('tr[data-cart-id]');
    if (remainingRows.length === 0) {
        const tbody = document.querySelector('#cart-tab tbody');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#77675c;padding:30px;">Your cart is empty right now.</td></tr>';
        }
        const checkoutWrapper = document.getElementById('checkoutSectionWrapper');
        if (checkoutWrapper) {
            checkoutWrapper.style.display = 'none';
        }
    }

    const formData = new URLSearchParams();
    formData.append('action_type', 'delete');
    formData.append('cart_id', cartId);
    
    fetch('customer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    }).then(() => {
        const badge = document.getElementById('sidebarCartBadge');
        if (badge) {
            let currentCount = parseInt(badge.textContent) || 1;
            badge.textContent = Math.max(0, currentCount - 1);
        }
    });
}

function recalculateGrandTotal() {
    let grandTotal = 0;
    document.querySelectorAll('tr[data-cart-id]').forEach(tr => {
        const p = parseFloat(tr.getAttribute('data-price'));
        const q = parseInt(tr.querySelector('.qty-input').value) || 0;
        grandTotal += p * q;
    });
    
    const grandTotalText = document.getElementById('grandTotalText');
    if (grandTotalText) {
        grandTotalText.textContent = grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    const checkoutWrapper = document.getElementById('checkoutSectionWrapper');
    if (checkoutWrapper) {
        if (grandTotal > 0) {
            checkoutWrapper.style.display = 'block';
        } else {
            checkoutWrapper.style.display = 'none';
        }
    }
}

function toggleCardInputs(show) {
    document.getElementById('cardFields').style.display = show ? 'block' : 'none';
}

function validateCreditCardForm() {
    if (!document.getElementById('cc').checked) return true;
    let isValid = true;
    document.getElementById('cardNumError').textContent = '',
    document.getElementById('cardYearError').textContent = '',
    document.getElementById('cardCvvError').textContent = '';

    const cardNumber = document.getElementById('cardNumber').value.trim();
    if (!/^\d{16}$/.test(cardNumber)) {
        document.getElementById('cardNumError').textContent = 'Card Number must be exactly 16 digits.';
        isValid = false;
    }

    const currentYear = new Date().getFullYear();
    const cardYear = parseInt(document.getElementById('cardYear').value.trim(), 10);
    if (isNaN(cardYear) || cardYear < currentYear) {
        document.getElementById('cardYearError').textContent = 'Enter a valid expiry year.';
        isValid = false;
    }

    const cardCvv = document.getElementById('cardCvv').value.trim();
    if (!/^\d{3}$/.test(cardCvv)) {
        document.getElementById('cardCvvError').textContent = 'CVV must be exactly 3 digits.';
        isValid = false;
    }
    return isValid;
}
</script>

</body>
</html>