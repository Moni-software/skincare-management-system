<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

/* =========================
   UPDATE PROFILE
========================= */
if (isset($_POST['update_details'])) {

    $new_name = trim($_POST['name']);
    $new_address = trim($_POST['address']);
    $new_phone = trim($_POST['phone']);

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {

        $target_dir = "uploads/";

        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = strtolower(
            pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION)
        );

        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($file_extension, $allowed_extensions, true)) {

            $new_filename =
                "profile_" . $customer_id . "_" . time() . "." . $file_extension;

            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file(
                $_FILES['profile_image']['tmp_name'],
                $target_file
            )) {

                $img_stmt = $conn->prepare(
                    "UPDATE customers SET profile_image = ? WHERE id = ?"
                );

                $img_stmt->bind_param(
                    "si",
                    $new_filename,
                    $customer_id
                );

                $img_stmt->execute();
                $img_stmt->close();
            }
        }
    }

    $up_stmt = $conn->prepare(
        "UPDATE customers
         SET name = ?, address = ?, phone = ?
         WHERE id = ?"
    );

    $up_stmt->bind_param(
        "sssi",
        $new_name,
        $new_address,
        $new_phone,
        $customer_id
    );

    $up_stmt->execute();
    $up_stmt->close();

    header("Location: customer.php?success=details_updated");
    exit();
}


/* =========================
   UPDATE CART QUANTITY
========================= */
if (isset($_POST['update_cart_qty'])) {

    $cart_id = intval($_POST['cart_id']);
    $new_qty = intval($_POST['quantity']);

    if ($new_qty > 0) {

        $up_cart = $conn->prepare(
            "UPDATE cart
             SET quantity = ?
             WHERE cart_id = ?
             AND customer_id = ?"
        );

        $up_cart->bind_param(
            "iii",
            $new_qty,
            $cart_id,
            $customer_id
        );

        $up_cart->execute();
        $up_cart->close();

    } else {

        $del_cart = $conn->prepare(
            "DELETE FROM cart
             WHERE cart_id = ?
             AND customer_id = ?"
        );

        $del_cart->bind_param(
            "ii",
            $cart_id,
            $customer_id
        );

        $del_cart->execute();
        $del_cart->close();
    }

    exit();
}


/* =========================
   SUBMIT COMPLAINT
========================= */
$complaint_error = "";

if (isset($_POST['submit_complaint'])) {

    $order_id = intval($_POST['order_id']);
    $message = trim($_POST['message']);

    $chk_order = $conn->prepare(
        "SELECT order_id
         FROM orders
         WHERE order_id = ?
         AND customer_id = ?"
    );

    $chk_order->bind_param(
        "ii",
        $order_id,
        $customer_id
    );

    $chk_order->execute();

    $chk_res = $chk_order->get_result();

    if ($chk_res->num_rows > 0) {

        $ins_comp = $conn->prepare(
            "INSERT INTO complaints
             (customer_id, order_id, message)
             VALUES (?, ?, ?)"
        );

        $ins_comp->bind_param(
            "iis",
            $customer_id,
            $order_id,
            $message
        );

        $ins_comp->execute();
        $ins_comp->close();

        $chk_order->close();

        header("Location: customer.php?success=complaint_added");
        exit();

    } else {

        $complaint_error =
            "⚠️ This Order ID does not belong to your account or does not exist!";
    }

    $chk_order->close();
}


/* =========================
   CHECKOUT / PLACE ORDER
========================= */
if (isset($_POST['checkout_order'])) {

    $sum_stmt = $conn->prepare(
        "SELECT SUM(product_price * quantity) AS total
         FROM cart
         WHERE customer_id = ?"
    );

    $sum_stmt->bind_param("i", $customer_id);
    $sum_stmt->execute();

    $sum_res = $sum_stmt->get_result()->fetch_assoc();

    $total_amount = $sum_res['total'] ?? 0;

    $sum_stmt->close();

    if ($total_amount > 0) {

        $payment_method =
            $_POST['payment_method'] ?? 'Cash on Delivery';

        $prod_stmt = $conn->prepare(
            "SELECT product_name, quantity
             FROM cart
             WHERE customer_id = ?"
        );

        $prod_stmt->bind_param("i", $customer_id);
        $prod_stmt->execute();

        $cart_products = $prod_stmt
            ->get_result()
            ->fetch_all(MYSQLI_ASSOC);

        $prod_stmt->close();

        $product_list = [];

        foreach ($cart_products as $product) {

            $product_list[] =
                $product['product_name'] .
                " x" .
                $product['quantity'];
        }

        $products_text = implode(", ", $product_list);

        $payment_status = "Pending";

        if ($payment_method === "Credit/Debit Card") {
            $payment_status = "Paid";
        }

        $order_stmt = $conn->prepare(
            "INSERT INTO orders
             (customer_id, products, total_amount, status, payment_status)
             VALUES (?, ?, ?, 'Pending Delivery', ?)"
        );

        $order_stmt->bind_param(
            "isds",
            $customer_id,
            $products_text,
            $total_amount,
            $payment_status
        );

        $order_stmt->execute();
        $order_stmt->close();

        $del_cart_all = $conn->prepare(
            "DELETE FROM cart WHERE customer_id = ?"
        );

        $del_cart_all->bind_param("i", $customer_id);

        $del_cart_all->execute();
        $del_cart_all->close();

        header("Location: customer.php?success=order_placed");
        exit();
    }
}


/* =========================
   FETCH CUSTOMER
========================= */
$customer_query = $conn->prepare(
    "SELECT * FROM customers WHERE id = ?"
);

$customer_query->bind_param(
    "i",
    $customer_id
);

$customer_query->execute();

$customer_res = $customer_query->get_result();

$customer = $customer_res->fetch_assoc() ?: [
    'name' => 'Valued Customer',
    'email' => '',
    'address' => '',
    'phone' => '',
    'profile_image' => ''
];

$customer_query->close();


$default_avatar =
    "https://cdn-icons-png.flaticon.com/512/3135/3135715.png";

$p_img = !empty($customer['profile_image'])
    ? 'uploads/' . $customer['profile_image']
    : $default_avatar;


/* =========================
   TOTAL ORDERS
========================= */
$tot_ord_q = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM orders
     WHERE customer_id = ?"
);

$tot_ord_q->bind_param(
    "i",
    $customer_id
);

$tot_ord_q->execute();

$tot_ord_res =
    $tot_ord_q->get_result()->fetch_assoc();

$total_orders =
    $tot_ord_res['total'] ?? 0;

$tot_ord_q->close();


/* =========================
   PENDING ORDERS
========================= */
$pen_ord_q = $conn->prepare(
    "SELECT COUNT(*) AS pending
     FROM orders
     WHERE customer_id = ?
     AND status = 'Pending Delivery'"
);

$pen_ord_q->bind_param(
    "i",
    $customer_id
);

$pen_ord_q->execute();

$pen_ord_res =
    $pen_ord_q->get_result()->fetch_assoc();

$pending_orders =
    $pen_ord_res['pending'] ?? 0;

$pen_ord_q->close();


/* =========================
   FETCH ORDERS
========================= */
$orders_stmt = $conn->prepare(
    "SELECT *
     FROM orders
     WHERE customer_id = ?
     ORDER BY order_date DESC"
);

$orders_stmt->bind_param(
    "i",
    $customer_id
);

$orders_stmt->execute();

$orders_items =
    $orders_stmt
        ->get_result()
        ->fetch_all(MYSQLI_ASSOC);

$orders_stmt->close();


/* =========================
   FETCH CART
========================= */
$cart_stmt = $conn->prepare(
    "SELECT *
     FROM cart
     WHERE customer_id = ?"
);

$cart_stmt->bind_param(
    "i",
    $customer_id
);

$cart_stmt->execute();

$cart_items =
    $cart_stmt
        ->get_result()
        ->fetch_all(MYSQLI_ASSOC);

$cart_stmt->close();


/* =========================
   FETCH COMPLAINTS
========================= */
$complaints_stmt = $conn->prepare(
    "SELECT *
     FROM complaints
     WHERE customer_id = ?
     ORDER BY created_at DESC"
);

$complaints_stmt->bind_param(
    "i",
    $customer_id
);

$complaints_stmt->execute();

$complaints_items =
    $complaints_stmt
        ->get_result()
        ->fetch_all(MYSQLI_ASSOC);

$complaints_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Glow Care - Customer Dashboard</title>
<link rel="stylesheet" href="style.css">

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

body {
    background: #fff8f4;
    color: #333;
}

.sub-navbar-wrap {
    background: #fff8f4;
    padding: 28px 20px 0;
}

.sub-navbar {
    max-width: 880px;
    min-height: 62px;
    margin: 0 auto;
    padding: 8px 12px;
    background: rgba(255,255,255,.92);
    border: 1px solid #e6dfe2;
    border-radius: 12px;
    box-shadow: 0 3px 14px rgba(0,0,0,.08);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.sub-navbar nav {
    display: flex;
    align-items: center;
    gap: 5px;
    flex-wrap: wrap;
}

.sub-navbar nav a {
    color: #222;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    padding: 10px 14px;
    border-radius: 7px;
    cursor: pointer;
    transition: .2s;
}

.sub-navbar nav a:hover,
.sub-navbar nav a.active {
    background: #f04b43;
    color: #fff;
}

.user-pill {
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
    color: #444;
    font-size: 13px;
    padding-right: 8px;
}

.user-pill img {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    object-fit: cover;
}

.container {
    max-width: 880px;
    margin: 20px auto 50px;
    padding: 0 20px;
}

.section-tab {
    display: none;
}

.section-tab.active-tab {
    display: block;
    animation: fadeIn .25s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(7px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.welcome-card {
    background: linear-gradient(110deg, #27212c, #3e2939);
    color: #fff;
    border-radius: 14px;
    padding: 27px 30px;
    margin-bottom: 22px;
}

.welcome-card h2 {
    color: #fff;
    border: 0;
    padding: 0;
    margin: 0 0 16px;
}

.stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 22px;
}

.stat-card {
    background: #fff;
    border-left: 4px solid #ed514c;
    border-radius: 12px;
    padding: 22px;
    text-align: center;
}

.stat-card h3 {
    color: #666;
    font-size: 13px;
    margin-bottom: 7px;
}

.stat-card p {
    font-size: 24px;
    font-weight: bold;
}

.card-box {
    background: #fff;
    border: 1px solid #e9e1e3;
    border-radius: 14px;
    padding: 22px;
    margin-bottom: 24px;
}

h2 {
    color: #4b263a;
    font-size: 18px;
    padding-bottom: 12px;
    margin-bottom: 16px;
    border-bottom: 1px solid #eee3e6;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
}

th {
    background: #682153;
    color: white;
    padding: 12px;
    text-align: left;
}

td {
    padding: 13px;
    border-bottom: 1px solid #f0e6e9;
    font-size: 12px;
}

.cart-img {
    width: 45px;
    height: 45px;
    object-fit: cover;
    border-radius: 50%;
}

.qty-input {
    width: 60px;
    padding: 6px;
    text-align: center;
}

.form-group {
    margin-bottom: 18px;
}

.form-group label {
    display: block;
    margin-bottom: 7px;
    color: #4b263a;
    font-weight: 600;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 11px 12px;
    border: 1px solid #e0d6da;
    border-radius: 7px;
}

.btn {
    background: #f04b43;
    color: #fff;
    border: 0;
    padding: 10px 18px;
    border-radius: 7px;
    cursor: pointer;
    font-weight: bold;
}

.logout-btn-profile {
    background: #5a3149;
    color: #fff;
    border: 0;
    padding: 11px 20px;
    border-radius: 7px;
    cursor: pointer;
    font-weight: bold;
}

.error-msg {
    color: #c62828;
    font-size: 12px;
    margin-top: 5px;
}

.success-msg {
    background: #e9f7ed;
    color: #28733c;
    padding: 11px 14px;
    border-radius: 8px;
    margin-bottom: 18px;
    text-align: center;
}

.payment-box-toggle {
    border: 1px solid #e6dce0;
    padding: 13px;
    border-radius: 8px;
    margin-bottom: 10px;
}

.profile-container {
    display: flex;
    align-items: center;
    gap: 25px;
    margin-bottom: 22px;
}

.profile-pic-wrapper {
    position: relative;
    width: 90px;
    height: 90px;
}

.profile-pic {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #f04b43;
}

.edit-icon-btn {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 30px;
    height: 30px;
    border: 0;
    border-radius: 50%;
    background: #5a3149;
    color: #fff;
    cursor: pointer;
}

@media (max-width: 700px) {

    .sub-navbar {
        flex-direction: column;
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

<div class="sub-navbar-wrap">

<div class="sub-navbar">

<nav>

<a onclick="switchTab('dashboard-tab')"
id="nav-dashboard"
class="active">
🏠 Dashboard
</a>

<a onclick="switchTab('cart-tab')"
id="nav-cart">
🛍️ My Cart
</a>

<a onclick="switchTab('orders-tab')"
id="nav-orders">
📦 My Orders
</a>

<a onclick="switchTab('complaints-tab')"
id="nav-complaints">
💬 Support & Complaints
</a>

<a onclick="switchTab('profile-tab')"
id="nav-profile">
👤 My Profile
</a>

</nav>

<div class="user-pill">

<img src="<?php echo htmlspecialchars($p_img); ?>"
alt="User">

<span>
<?php echo htmlspecialchars($customer['name']); ?>
</span>

</div>

</div>

</div>


<div class="container">

<?php if (isset($_GET['success'])): ?>

<div class="success-msg">
🎉 Operation Successful! Changes saved successfully.
</div>

<?php endif; ?>


<!-- DASHBOARD -->

<div id="dashboard-tab"
class="section-tab active-tab">

<div class="welcome-card">

<h2>
✨ Welcome Back,
<?php echo htmlspecialchars($customer['name']); ?>!
</h2>

<p>
Manage your luxury skincare routine,
track orders, and experience elegance at your fingertips.
</p>

</div>


<div class="stats-grid">

<div class="stat-card">

<h3>📦 Total Orders Placed</h3>

<p>
<?php echo $total_orders; ?>
</p>

</div>


<div class="stat-card">

<h3>🚚 Pending Deliveries</h3>

<p>
<?php echo $pending_orders; ?>
</p>

</div>

</div>


<div class="card-box">

<h2>📦 Recent Orders & Status</h2>

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

<?php
$count = 0;

foreach ($orders_items as $ord):

if ($count >= 3) break;

$count++;
?>

<tr>

<td>
<strong>
#GLW-<?php echo $ord['order_id']; ?>
</strong>
</td>

<td>
<?php echo htmlspecialchars($ord['products']); ?>
</td>

<td>
Rs.
<?php echo number_format($ord['total_amount'], 2); ?>
</td>

<td>

<strong style="color:#e04a45;">

⏳
<?php echo htmlspecialchars($ord['status']); ?>

</strong>

</td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>

<td colspan="4"
style="text-align:center;color:#888;padding:20px;">

No recent orders found.

</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</div>


<!-- CART -->

<div id="cart-tab"
class="section-tab">

<div class="card-box">

<h2>🛍️ My Cart & Secure Checkout</h2>

<table>

<thead>

<tr>

<th>Image</th>
<th>Product Name</th>
<th>Price</th>
<th>Quantity</th>
<th>Total</th>

</tr>

</thead>

<tbody>

<?php

$grand_total = 0;

if (count($cart_items) > 0):

foreach ($cart_items as $cart):

$subtotal =
$cart['product_price'] * $cart['quantity'];

$grand_total += $subtotal;

?>

<tr
data-cart-id="<?php echo $cart['cart_id']; ?>"
data-price="<?php echo $cart['product_price']; ?>"
>

<td>

<img
src="uploads/<?php echo htmlspecialchars($cart['product_image'] ?? 'default.jpg'); ?>"
class="cart-img"
alt="Product"
onerror="this.src='<?php echo $default_avatar; ?>'"
>

</td>

<td>
✨ <?php echo htmlspecialchars($cart['product_name']); ?>
</td>

<td>
Rs.
<?php echo number_format($cart['product_price'], 2); ?>
</td>

<td>

<input
type="number"
min="1"
value="<?php echo $cart['quantity']; ?>"
class="qty-input"
onchange="updateQuantity(
this,
<?php echo $cart['cart_id']; ?>,
<?php echo $cart['product_price']; ?>
)"
>

</td>

<td>

<strong>

Rs.

<span class="subtotal-text">

<?php echo number_format($subtotal, 2); ?>

</span>

</strong>

</td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>

<td colspan="5"
style="text-align:center;color:#888;padding:20px;">

🛒 Your cart is empty right now.

</td>

</tr>

<?php endif; ?>

</tbody>

</table>


<?php if ($grand_total > 0): ?>

<h3 style="margin:20px 0 15px;color:#4b263a;">

💎 Grand Total: Rs.

<span id="grandTotalText">

<?php echo number_format($grand_total, 2); ?>

</span>

</h3>


<form
action="customer.php"
method="POST"
onsubmit="return validateCreditCardForm()"
>

<div class="form-group">

<label>💳 Select Payment Method:</label>


<div class="payment-box-toggle">

<input
type="radio"
id="cod"
name="payment_method"
value="Cash on Delivery"
checked
onclick="toggleCardInputs(false)"
>

<label for="cod"
style="display:inline;font-weight:normal;">

💵 Cash on Delivery

</label>

</div>


<div class="payment-box-toggle">

<input
type="radio"
id="cc"
name="payment_method"
value="Credit/Debit Card"
onclick="toggleCardInputs(true)"
>

<label for="cc"
style="display:inline;font-weight:normal;">

💳 Bank Credit / Debit Card

</label>


<div id="cardFields"
style="display:none;margin-top:15px;">

<div class="form-group">

<label>Cardholder Name:</label>

<input
type="text"
id="cardName"
placeholder="Name on Card"
>

</div>


<div class="form-group">

<label>Card Number:</label>

<input
type="text"
id="cardNumber"
maxlength="16"
placeholder="16 digit card number"
>

<div id="cardNumError"
class="error-msg"></div>

</div>


<div class="form-group">

<label>Expire Year:</label>

<input
type="text"
id="cardYear"
maxlength="4"
placeholder="2027"
>

<div id="cardYearError"
class="error-msg"></div>

</div>


<div class="form-group">

<label>CVV:</label>

<input
type="password"
id="cardCvv"
maxlength="3"
placeholder="123"
>

<div id="cardCvvError"
class="error-msg"></div>

</div>

</div>

</div>

</div>


<button
type="submit"
name="checkout_order"
class="btn"
>

🚀 Proceed to Secure Checkout

</button>

</form>

<?php endif; ?>

</div>

</div>


<!-- ORDERS -->

<div id="orders-tab"
class="section-tab">

<div class="card-box">

<h2>📦 Recent Orders & Delivery Status</h2>

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

<td>
<strong>
#GLW-<?php echo $ord['order_id']; ?>
</strong>
</td>

<td>
<?php echo htmlspecialchars($ord['products']); ?>
</td>

<td>
Rs.
<?php echo number_format($ord['total_amount'], 2); ?>
</td>

<td>

<strong style="color:#e04a45;">

⏳
<?php echo htmlspecialchars($ord['status']); ?>

</strong>

</td>

<td>

<?php echo htmlspecialchars($ord['payment_status']); ?>

</td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>

<td colspan="5"
style="text-align:center;color:#888;padding:20px;">

📦 No orders found in your history.

</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</div>


<!-- COMPLAINTS -->

<div id="complaints-tab"
class="section-tab">

<div class="card-box">

<h2>💬 Customer Support & Complaints</h2>


<?php if (!empty($complaint_error)): ?>

<div class="error-msg"
style="margin-bottom:15px;font-size:14px;">

<?php echo htmlspecialchars($complaint_error); ?>

</div>

<?php endif; ?>


<form action="customer.php"
method="POST">

<div class="form-group">

<label>🔢 Order ID:</label>

<input
type="number"
name="order_id"
placeholder="Enter your Order ID"
required
>

</div>


<div class="form-group">

<label>✍️ Complaint Message:</label>

<textarea
name="message"
rows="4"
placeholder="Describe your issue here..."
required
></textarea>

</div>


<button
type="submit"
name="submit_complaint"
class="btn"
>

📨 Submit Complaint

</button>

</form>


<h3 style="margin-top:35px;">

📋 Past Complaints & Admin Replies

</h3>


<table>

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

<td>
#GLW-<?php echo $comp['order_id']; ?>
</td>

<td>
<?php echo htmlspecialchars($comp['message']); ?>
</td>

<td>

<?php if (!empty($comp['admin_reply'])): ?>

<?php echo htmlspecialchars($comp['admin_reply']); ?>

<?php else: ?>

<span style="color:#888;">

⏳ Pending Admin Reply...

</span>

<?php endif; ?>

</td>

<td>

<?php echo htmlspecialchars($comp['status']); ?>

</td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>

<td colspan="4"
style="text-align:center;color:#888;padding:20px;">

💬 No complaints submitted yet.

</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</div>


<!-- PROFILE -->

<div id="profile-tab"
class="section-tab">

<div class="card-box">

<h2>👤 Customer Profile & Settings</h2>


<form
action="customer.php"
method="POST"
enctype="multipart/form-data"
>

<div class="profile-container">

<div class="profile-pic-wrapper">

<img
src="<?php echo htmlspecialchars($p_img); ?>"
class="profile-pic"
id="profilePreview"
alt="Profile Picture"
>

<input
type="file"
name="profile_image"
id="imageInput"
style="display:none;"
accept="image/*"
onchange="previewImage(event)"
>

<button
type="button"
class="edit-icon-btn"
onclick="document.getElementById('imageInput').click()"
>

✏️

</button>

</div>


<div>

<h3>

<?php echo htmlspecialchars($customer['name']); ?>

</h3>

<p>
Click the pencil icon to upload a new profile photo.
</p>

</div>

</div>


<div class="form-group">

<label>👤 Full Name:</label>

<input
type="text"
name="name"
id="profileName"
value="<?php echo htmlspecialchars($customer['name']); ?>"
readonly
required
>

</div>


<div class="form-group">

<label>🏠 Delivery Address:</label>

<textarea
name="address"
id="profileAddress"
readonly
required
><?php echo htmlspecialchars($customer['address']); ?></textarea>

</div>


<div class="form-group">

<label>📞 Telephone Number:</label>

<input
type="text"
name="phone"
id="profilePhone"
value="<?php echo htmlspecialchars($customer['phone']); ?>"
readonly
required
>

</div>


<div class="form-group">

<label>✉️ Email Address:</label>

<input
type="email"
value="<?php echo htmlspecialchars($customer['email']); ?>"
disabled
>

</div>


<div style="display:flex;gap:15px;justify-content:space-between;">

<div>

<button
type="button"
class="btn"
id="editProfileBtn"
onclick="enableProfileEdit()"
>

✏️ Edit Profile

</button>


<button
type="submit"
name="update_details"
class="btn"
id="saveProfileBtn"
style="display:none;"
>

💾 Save Changes

</button>

</div>


<button
type="button"
onclick="confirmLogout()"
class="logout-btn-profile"
>

🚪 Logout

</button>

</div>

</form>

</div>

</div>

</div>


<script>

function switchTab(tabId) {

    document.querySelectorAll('.section-tab')
        .forEach(function(tab) {
            tab.classList.remove('active-tab');
        });

    document.querySelectorAll('.sub-navbar nav a')
        .forEach(function(nav) {
            nav.classList.remove('active');
        });

    const selectedTab =
        document.getElementById(tabId);

    if (selectedTab) {
        selectedTab.classList.add('active-tab');
    }

    const navMap = {
        'dashboard-tab': 'nav-dashboard',
        'cart-tab': 'nav-cart',
        'orders-tab': 'nav-orders',
        'complaints-tab': 'nav-complaints',
        'profile-tab': 'nav-profile'
    };

    if (navMap[tabId]) {

        document
            .getElementById(navMap[tabId])
            .classList.add('active');
    }
}


function confirmLogout() {

    if (confirm("Are you sure you want to log out?")) {

        window.location.href =
            "customer_logout.php";
    }
}


function updateQuantity(
    inputElement,
    cartId,
    price
) {

    let qty =
        parseInt(inputElement.value);

    if (qty <= 0 || isNaN(qty)) {

        qty = 1;

        inputElement.value = 1;
    }

    const row =
        inputElement.closest('tr');

    const subtotal =
        qty * price;

    row.querySelector('.subtotal-text')
        .textContent =
        subtotal.toLocaleString(
            'en-US',
            {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }
        );


    let grandTotal = 0;

    document
        .querySelectorAll('tr[data-cart-id]')
        .forEach(function(tr) {

            const p =
                parseFloat(
                    tr.getAttribute('data-price')
                );

            const q =
                parseInt(
                    tr.querySelector('.qty-input').value
                ) || 0;

            grandTotal += p * q;
        });


    const grandTotalText =
        document.getElementById('grandTotalText');

    if (grandTotalText) {

        grandTotalText.textContent =
            grandTotal.toLocaleString(
                'en-US',
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            );
    }


    const formData =
        new URLSearchParams();

    formData.append(
        'update_cart_qty',
        '1'
    );

    formData.append(
        'cart_id',
        cartId
    );

    formData.append(
        'quantity',
        qty
    );


    fetch('customer.php', {

        method: 'POST',

        headers: {
            'Content-Type':
                'application/x-www-form-urlencoded'
        },

        body:
            formData.toString()
    });
}


function enableProfileEdit() {

    document
        .getElementById('profileName')
        .removeAttribute('readonly');

    document
        .getElementById('profileAddress')
        .removeAttribute('readonly');

    document
        .getElementById('profilePhone')
        .removeAttribute('readonly');


    document
        .getElementById('editProfileBtn')
        .style.display = 'none';

    document
        .getElementById('saveProfileBtn')
        .style.display = 'inline-block';
}


function previewImage(event) {

    const file =
        event.target.files[0];

    if (!file) return;

    const reader =
        new FileReader();

    reader.onload = function() {

        document
            .getElementById('profilePreview')
            .src = reader.result;
    };

    reader.readAsDataURL(file);
}


function toggleCardInputs(show) {

    document
        .getElementById('cardFields')
        .style.display =
        show ? 'block' : 'none';
}


function validateCreditCardForm() {

    const isCardSelected =
        document
            .getElementById('cc')
            .checked;

    if (!isCardSelected) {
        return true;
    }


    let isValid = true;


    document
        .getElementById('cardNumError')
        .textContent = '';

    document
        .getElementById('cardYearError')
        .textContent = '';

    document
        .getElementById('cardCvvError')
        .textContent = '';


    const cardNumber =
        document
            .getElementById('cardNumber')
            .value
            .trim();


    if (!/^\d{16}$/.test(cardNumber)) {

        document
            .getElementById('cardNumError')
            .textContent =
            '⚠️ Card Number must be exactly 16 digits.';

        isValid = false;
    }


    const currentYear =
        new Date().getFullYear();

    const cardYear =
        parseInt(
            document
                .getElementById('cardYear')
                .value
                .trim(),
            10
        );


    if (
        isNaN(cardYear) ||
        cardYear < currentYear
    ) {

        document
            .getElementById('cardYearError')
            .textContent =
            '⚠️ Enter a valid expiry year.';

        isValid = false;
    }


    const cardCvv =
        document
            .getElementById('cardCvv')
            .value
            .trim();


    if (!/^\d{3}$/.test(cardCvv)) {

        document
            .getElementById('cardCvvError')
            .textContent =
            '⚠️ CVV must be exactly 3 digits.';

        isValid = false;
    }


    return isValid;
}

</script>

</body>

</html>