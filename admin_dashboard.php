<?php
session_start();

// Ensure admin is logged in
if (!isset($_SESSION['admin_name']) && !isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Database Connection Settings
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "glowcare_db";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Determine active tab
$active_tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 'tab-complaints';

// Check if orders table exists
$ordersExist = false;
$checkOrdersTable = $conn->query("SHOW TABLES LIKE 'orders'");
if ($checkOrdersTable && $checkOrdersTable->num_rows > 0) {
    $ordersExist = true;
}

// -------------------------------------------------------------
// POST HANDLERS (COMPLAINTS, DEALS, PRODUCTS, ORDERS)
// -------------------------------------------------------------

// 1. Update Complaint Status & Admin Reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complaint_id'], $_POST['status'])) {
    $cid = intval($_POST['complaint_id']);
    $st = $_POST['status'];
    $reply = $_POST['admin_reply'] ?? NULL;
    
    $stmt = $conn->prepare("UPDATE complaints SET status = ?, admin_reply = ? WHERE complaint_id = ?");
    $stmt->bind_param("ssi", $st, $reply, $cid);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_dashboard.php?tab=tab-complaints");
    exit();
}

// 2. Handle Deals (Add / Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deal_action'])) {
    $action = $_POST['deal_action'];
    $name = $_POST['name'] ?? '';
    $price = intval($_POST['price'] ?? 0);
    $old_price = ($_POST['old_price'] !== '') ? intval($_POST['old_price']) : NULL;
    $size = $_POST['size'] ?? '';
    $section_type = $_POST['section_type'] ?? '';
    $image_url = $_POST['image_url'] ?? '';
    $description = $_POST['description'] ?? NULL;
    $max_qty = ($_POST['max_qty'] !== '') ? intval($_POST['max_qty']) : NULL;

    if ($action === 'add') {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("INSERT INTO deals (id, name, price, old_price, size, image_url, description, max_qty, section_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isiisssis", $id, $name, $price, $old_price, $size, $image_url, $description, $max_qty, $section_type);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_dashboard.php?tab=tab-deals&deal_msg=added");
        exit();
    } elseif ($action === 'edit') {
        $deal_id = intval($_POST['deal_id']);
        $stmt = $conn->prepare("UPDATE deals SET name=?, price=?, old_price=?, size=?, image_url=?, description=?, max_qty=?, section_type=? WHERE id=?");
        $stmt->bind_param("siisssisi", $name, $price, $old_price, $size, $image_url, $description, $max_qty, $section_type, $deal_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_dashboard.php?tab=tab-deals&deal_msg=updated");
        exit();
    }
}

// 3. Handle Products (Add / Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_action'])) {
    $action = $_POST['product_action'];
    $P_name = $_POST['P_name'] ?? '';
    $image = $_POST['image'] ?? '';
    $category = $_POST['category'] ?? '';
    $sub_category = $_POST['sub_category'] ?? '';
    $skin_hair_type = $_POST['Skin_Hair_type'] ?? '';
    $P_price = $_POST['P_price'] ?? '';
    $P_quantity = $_POST['P_quantity'] ?? '';
    $In_stock = $_POST['In_stock'] ?? '';
    $guide = $_POST['guide'] ?? '';
    $benifits = $_POST['benifits'] ?? '';

    if ($action === 'add') {
        $stmt = $conn->prepare("INSERT INTO product (P_name, image, category, sub_category, `Skin/Hair_type`, P_price, P_quantity, In_stock, guide, benifits) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssss", $P_name, $image, $category, $sub_category, $skin_hair_type, $P_price, $P_quantity, $In_stock, $guide, $benifits);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_dashboard.php?tab=tab-products&product_msg=added");
        exit();
    } elseif ($action === 'edit') {
        $P_id = intval($_POST['P_id']);
        $stmt = $conn->prepare("UPDATE product SET P_name=?, image=?, category=?, sub_category=?, `Skin/Hair_type`=?, P_price=?, P_quantity=?, In_stock=?, guide=?, benifits=? WHERE P_id=?");
        $stmt->bind_param("ssssssssssi", $P_name, $image, $category, $sub_category, $skin_hair_type, $P_price, $P_quantity, $In_stock, $guide, $benifits, $P_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_dashboard.php?tab=tab-products&product_msg=updated");
        exit();
    }
}

// 4. Update Order Delivery Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $oid = intval($_POST['order_id']);
    $dst = $_POST['status'];
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $dst, $oid);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_dashboard.php?tab=tab-orders");
    exit();
}

// -------------------------------------------------------------
// GET HANDLERS (DELETIONS & EDIT PRE-LOADS)
// -------------------------------------------------------------

if (isset($_GET['delete_deal'])) {
    $did = intval($_GET['delete_deal']);
    $stmt = $conn->prepare("DELETE FROM deals WHERE id = ?");
    $stmt->bind_param("i", $did);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_dashboard.php?tab=tab-deals&deal_msg=deleted");
    exit();
}

if (isset($_GET['delete_product'])) {
    $pid = intval($_GET['delete_product']);
    $stmt = $conn->prepare("DELETE FROM product WHERE P_id = ?");
    $stmt->bind_param("i", $pid);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_dashboard.php?tab=tab-products&product_msg=deleted");
    exit();
}

if (isset($_GET['delete_customer'])) {
    $cid = intval($_GET['delete_customer']);
    $stmt = $conn->prepare("DELETE FROM customers WHERE id = ?");
    $stmt->bind_param("i", $cid);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_dashboard.php?tab=tab-customers&customer_msg=deleted");
    exit();
}

// Pre-load Deal for editing
$editDeal = null;
if (isset($_GET['edit_deal'])) {
    $did = intval($_GET['edit_deal']);
    $res = $conn->query("SELECT * FROM deals WHERE id = $did");
    if ($res && $res->num_rows > 0) {
        $editDeal = $res->fetch_assoc();
    }
}

// Pre-load Product for editing
$editProduct = null;
if (isset($_GET['edit_product'])) {
    $pid = intval($_GET['edit_product']);
    $res = $conn->query("SELECT * FROM product WHERE P_id = $pid");
    if ($res && $res->num_rows > 0) {
        $editProduct = $res->fetch_assoc();
    }
}

// -------------------------------------------------------------
// DASHBOARD METRICS & FETCH DATA
// -------------------------------------------------------------

// Fetch Logged-in Admin Info
$admin_identifier = $_SESSION['admin_name'] ?? 'admin';
$adminInfoQuery = $conn->query("SELECT full_name, email, contact_no FROM admins WHERE username='$admin_identifier' OR email='$admin_identifier' LIMIT 1");
$adminInfo = ($adminInfoQuery && $adminInfoQuery->num_rows > 0) ? $adminInfoQuery->fetch_assoc() : ['full_name' => 'Admin', 'email' => 'support@glowcare.com', 'contact_no' => '+94 71 234 5678'];

// System Counts
$total_complaints = $conn->query("SELECT COUNT(*) AS c FROM complaints")->fetch_assoc()['c'] ?? 0;
$resolved_complaints = $conn->query("SELECT COUNT(*) AS c FROM complaints WHERE status = 'Resolved'")->fetch_assoc()['c'] ?? 0;
$deal_total = $conn->query("SELECT COUNT(*) AS c FROM deals")->fetch_assoc()['c'] ?? 0;
$product_total = $conn->query("SELECT COUNT(*) AS c FROM product")->fetch_assoc()['c'] ?? 0;
$customer_total = $conn->query("SELECT COUNT(*) AS c FROM customers")->fetch_assoc()['c'] ?? 0;

$order_total = 0;
$delivered_count = 0;
if ($ordersExist) {
    $order_total = $conn->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'] ?? 0;
    $delivered_count = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE status = 'Delivered'")->fetch_assoc()['c'] ?? 0;
}

// Data Queries
$complaints = $conn->query("SELECT c.*, cust.name AS customer_name, cust.email AS customer_email, cust.phone AS customer_phone 
                            FROM complaints c 
                            LEFT JOIN customers cust ON c.customer_id = cust.id 
                            ORDER BY c.created_at DESC");
$deals = $conn->query("SELECT * FROM deals ORDER BY id DESC");
$products = $conn->query("SELECT * FROM product ORDER BY P_id DESC");
$customers = $conn->query("SELECT * FROM customers ORDER BY id DESC");
$orders = $ordersExist ? $conn->query("SELECT o.*, cust.name AS customer_name FROM orders o LEFT JOIN customers cust ON o.customer_id = cust.id ORDER BY o.order_date DESC") : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - GlowCare</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f6f7; margin: 0; padding: 0; color: #333; }
        .dashboard-header { background-color: #ffffff; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2d8dc; }
        .dashboard-header h2 { margin: 0; color: #4a3b40; }
        .logout-link { background-color: #d9534f; color: #fff; padding: 8px 16px; text-decoration: none; border-radius: 4px; font-size: 14px; }
        .logout-link:hover { background-color: #c9302c; }
        
        .dashboard-content { padding: 30px 40px; }
        
        .stats-row { display: flex; gap: 20px; margin-bottom: 30px; flex-wrap: wrap; }
        .stat-box { background: #fff; border: 1px solid #e2d8dc; border-radius: 8px; padding: 20px; flex: 1; min-width: 150px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .stat-box .num { font-size: 28px; font-weight: bold; color: #8c5366; margin-bottom: 5px; }
        .stat-box .lbl { font-size: 13px; color: #7a6a6e; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .tab-nav { display: flex; border-bottom: 2px solid #e2d8dc; margin-bottom: 25px; }
        .tab-nav a { padding: 12px 24px; text-decoration: none; color: #666; font-weight: 600; border-bottom: 3px solid transparent; margin-bottom: -2px; }
        .tab-nav a.active { color: #8c5366; border-bottom-color: #8c5366; }
        
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.03); margin-bottom: 30px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
        th { background-color: #f3ecef; color: #4a3b40; font-weight: 600; }
        tr:hover { background-color: #faf7f8; }
        
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; display: inline-block; }
        .status-Pending { background: #fff3cd; color: #856404; }
        .status-InProgress { background: #cce5ff; color: #004085; }
        .status-Resolved { background: #d4edda; color: #155724; }
        .status-PendingDelivery { background: #fff3cd; color: #856404; }
        .status-Delivered { background: #d4edda; color: #155724; }
        .status-Cancelled { background: #f8d7da; color: #721c24; }
        
        .action-select { padding: 4px 8px; border-radius: 4px; border: 1px solid #ccc; font-size: 13px; }
        .action-link { color: #0275d8; text-decoration: none; font-size: 13px; }
        .action-link.danger { color: #d9534f; }
        .action-link:hover { text-decoration: underline; }
        
        .deal-form, .product-form { background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #e2d8dc; margin-bottom: 25px; }
        .form-row { display: flex; gap: 15px; margin-bottom: 15px; }
        .form-group { flex: 1; display: flex; flex-direction: column; }
        .form-group label { font-size: 12px; font-weight: 600; color: #555; margin-bottom: 5px; }
        .form-group input, .form-group textarea { padding: 8px 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        .btn-save { background-color: #8c5366; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-save:hover { background-color: #734253; }
        
        .deal-image-preview, .product-image-preview { width: 40px; height: 40px; object-fit: cover; border-radius: 4px; }
        .msg { background-color: #d4edda; color: #155724; padding: 10px 15px; border-radius: 4px; margin-bottom: 20px; }
        footer { text-align: center; padding: 20px; color: #888; font-size: 13px; margin-top: 20px; }
    </style>
</head>
<body>

<div class="dashboard-header">
    <h2>Welcome, <?php echo htmlspecialchars($adminInfo['full_name']); ?></h2>
    <div>
        <span style="margin-right:20px; font-size:14px; color:#7a6a6e;">
            <?php echo htmlspecialchars($adminInfo['email']); ?> | <?php echo htmlspecialchars($adminInfo['contact_no']); ?>
        </span>
        <a href="logout.php" class="logout-link">Logout</a>
    </div>
</div>

<div class="dashboard-content">

    <div class="stats-row">
        <div class="stat-box"><div class="num"><?php echo $total_complaints; ?></div><div class="lbl">Complaints</div></div>
        <div class="stat-box"><div class="num"><?php echo $resolved_complaints; ?></div><div class="lbl">Resolved</div></div>
        <div class="stat-box"><div class="num"><?php echo $deal_total; ?></div><div class="lbl">Deals</div></div>
        <div class="stat-box"><div class="num"><?php echo $product_total; ?></div><div class="lbl">Products</div></div>
        <div class="stat-box"><div class="num"><?php echo $customer_total; ?></div><div class="lbl">Customers</div></div>
        <div class="stat-box"><div class="num"><?php echo $order_total; ?></div><div class="lbl">Total Orders</div></div>
        <div class="stat-box"><div class="num"><?php echo $delivered_count; ?></div><div class="lbl">Delivered</div></div>
    </div>

    <div class="tab-nav">
        <a href="?tab=tab-complaints" class="<?php echo $active_tab == 'tab-complaints' ? 'active' : ''; ?>">Complaints</a>
        <a href="?tab=tab-deals" class="<?php echo $active_tab == 'tab-deals' ? 'active' : ''; ?>">Deals</a>
        <a href="?tab=tab-products" class="<?php echo $active_tab == 'tab-products' ? 'active' : ''; ?>">Products</a>
        <a href="?tab=tab-customers" class="<?php echo $active_tab == 'tab-customers' ? 'active' : ''; ?>">Customers</a>
        <?php if ($ordersExist): ?>
        <a href="?tab=tab-orders" class="<?php echo $active_tab == 'tab-orders' ? 'active' : ''; ?>">Orders</a>
        <?php endif; ?>
    </div>

    <!-- TAB 1: COMPLAINTS -->
    <div id="tab-complaints" class="tab-content <?php echo $active_tab == 'tab-complaints' ? 'active' : ''; ?>">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer Name</th>
                    <th>Contact Info</th>
                    <th>Order ID</th>
                    <th>Message</th>
                    <th>Admin Reply</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Update Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($complaints && $complaints->num_rows > 0): ?>
                    <?php while ($row = $complaints->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['complaint_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['customer_name'] ?? 'Customer #'.$row['customer_id']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($row['customer_email'] ?? 'N/A'); ?><br>
                                <small><?php echo htmlspecialchars($row['customer_phone'] ?? ''); ?></small>
                            </td>
                            <td>#<?php echo htmlspecialchars($row['order_id']); ?></td>
                            <td style="max-width:200px;"><?php echo htmlspecialchars($row['message']); ?></td>
                            <td style="max-width:200px;"><?php echo htmlspecialchars($row['admin_reply'] ?? 'None'); ?></td>
                            <td><?php echo date("d M Y, h:i A", strtotime($row['created_at'])); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo str_replace(' ', '', $row['status']); ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" style="margin:0;">
                                    <input type="hidden" name="complaint_id" value="<?php echo $row['complaint_id']; ?>">
                                    <input type="hidden" name="tab" value="tab-complaints">
                                    <select name="status" class="action-select" onchange="this.form.submit()">
                                        <option value="Pending" <?php if ($row['status']=='Pending') echo 'selected'; ?>>Pending</option>
                                        <option value="In Progress" <?php if ($row['status']=='In Progress') echo 'selected'; ?>>In Progress</option>
                                        <option value="Resolved" <?php if ($row['status']=='Resolved') echo 'selected'; ?>>Resolved</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="9" style="text-align:center; padding:20px;">No complaints found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- TAB 2: DEALS -->
    <div id="tab-deals" class="tab-content <?php echo $active_tab == 'tab-deals' ? 'active' : ''; ?>">
        <?php if (isset($_GET['deal_msg'])): ?>
            <div class="msg"><?php echo htmlspecialchars($_GET['deal_msg']); ?> successfully.</div>
        <?php endif; ?>

        <div class="deal-form">
            <h3><?php echo $editDeal ? 'Edit Deal' : 'Add New Deal'; ?></h3>
            <form method="POST">
                <input type="hidden" name="deal_action" value="<?php echo $editDeal ? 'edit' : 'add'; ?>">
                <input type="hidden" name="tab" value="tab-deals">
                <?php if ($editDeal): ?>
                    <input type="hidden" name="deal_id" value="<?php echo $editDeal['id']; ?>">
                <?php endif; ?>

                <div class="form-row">
                    <?php if (!$editDeal): ?>
                    <div class="form-group">
                        <label>ID *</label>
                        <input type="number" name="id" required placeholder="e.g. 101">
                    </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label>Name *</label>
                        <input type="text" name="name" required value="<?php echo $editDeal ? htmlspecialchars($editDeal['name']) : ''; ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Price *</label>
                        <input type="number" name="price" required value="<?php echo $editDeal ? htmlspecialchars($editDeal['price']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Old Price (optional)</label>
                        <input type="number" name="old_price" value="<?php echo $editDeal && $editDeal['old_price'] !== null ? htmlspecialchars($editDeal['old_price']) : ''; ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Size *</label>
                        <input type="text" name="size" required value="<?php echo $editDeal ? htmlspecialchars($editDeal['size']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Section Type *</label>
                        <input type="text" name="section_type" required value="<?php echo $editDeal ? htmlspecialchars($editDeal['section_type']) : ''; ?>" placeholder="large_volume, heavy_weight, bundle">
                    </div>
                </div>
                <div class="form-group">
                    <label>Image URL *</label>
                    <input type="text" name="image_url" required value="<?php echo $editDeal ? htmlspecialchars($editDeal['image_url']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="2"><?php echo $editDeal ? htmlspecialchars($editDeal['description']) : ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label>Max Quantity</label>
                    <input type="number" name="max_qty" value="<?php echo $editDeal && $editDeal['max_qty'] !== null ? htmlspecialchars($editDeal['max_qty']) : ''; ?>">
                </div>
                <button type="submit" class="btn-save"><?php echo $editDeal ? 'Update Deal' : 'Add Deal'; ?></button>
                <?php if ($editDeal): ?>
                    <a href="admin_dashboard.php?tab=tab-deals" style="margin-left:10px;">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Old Price</th>
                    <th>Size</th>
                    <th>Image</th>
                    <th>Description</th>
                    <th>Max Qty</th>
                    <th>Section</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($deals && $deals->num_rows > 0): ?>
                    <?php while ($row = $deals->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td>Rs. <?php echo number_format($row['price'], 2); ?></td>
                            <td><?php echo $row['old_price'] ? 'Rs. '.number_format($row['old_price'], 2) : '-'; ?></td>
                            <td><?php echo htmlspecialchars($row['size']); ?></td>
                            <td>
                                <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="deal" class="deal-image-preview" onerror="this.style.display='none'">
                            </td>
                            <td><?php echo htmlspecialchars($row['description'] ?? '-'); ?></td>
                            <td><?php echo $row['max_qty'] ?? '-'; ?></td>
                            <td><?php echo htmlspecialchars($row['section_type']); ?></td>
                            <td>
                                <a href="admin_dashboard.php?edit_deal=<?php echo $row['id']; ?>&tab=tab-deals" class="action-link">Edit</a> |
                                <a href="admin_dashboard.php?delete_deal=<?php echo $row['id']; ?>&tab=tab-deals" class="action-link danger" onclick="return confirm('Delete this deal?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="10">No deals found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- TAB 3: PRODUCTS -->
    <div id="tab-products" class="tab-content <?php echo $active_tab == 'tab-products' ? 'active' : ''; ?>">
        <?php if (isset($_GET['product_msg'])): ?>
            <div class="msg"><?php echo htmlspecialchars($_GET['product_msg']); ?> successfully.</div>
        <?php endif; ?>

        <div class="product-form">
            <h3><?php echo $editProduct ? 'Edit Product' : 'Add New Product'; ?></h3>
            <form method="POST">
                <input type="hidden" name="product_action" value="<?php echo $editProduct ? 'edit' : 'add'; ?>">
                <input type="hidden" name="tab" value="tab-products">
                <?php if ($editProduct): ?>
                    <input type="hidden" name="P_id" value="<?php echo $editProduct['P_id']; ?>">
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="P_name" required value="<?php echo $editProduct ? htmlspecialchars($editProduct['P_name']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Image Path *</label>
                        <input type="text" name="image" required value="<?php echo $editProduct ? htmlspecialchars($editProduct['image']) : ''; ?>" placeholder="image/foam cleanser.jpeg">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Category *</label>
                        <input type="text" name="category" required value="<?php echo $editProduct ? htmlspecialchars($editProduct['category']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Sub Category *</label>
                        <input type="text" name="sub_category" required value="<?php echo $editProduct ? htmlspecialchars($editProduct['sub_category']) : ''; ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Skin/Hair Type *</label>
                        <input type="text" name="Skin_Hair_type" required value="<?php echo $editProduct ? htmlspecialchars($editProduct['Skin/Hair_type']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Price *</label>
                        <input type="text" name="P_price" required value="<?php echo $editProduct ? htmlspecialchars($editProduct['P_price']) : ''; ?>" placeholder="Rs. 4,800.00">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Quantity *</label>
                        <input type="text" name="P_quantity" required value="<?php echo $editProduct ? htmlspecialchars($editProduct['P_quantity']) : ''; ?>" placeholder="150ml">
                    </div>
                    <div class="form-group">
                        <label>In Stock (Yes/No) *</label>
                        <input type="text" name="In_stock" required value="<?php echo $editProduct ? htmlspecialchars($editProduct['In_stock']) : ''; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Usage Guide</label>
                    <textarea name="guide" rows="2"><?php echo $editProduct ? htmlspecialchars($editProduct['guide']) : ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label>Benefits</label>
                    <textarea name="benifits" rows="2"><?php echo $editProduct ? htmlspecialchars($editProduct['benifits']) : ''; ?></textarea>
                </div>
                <button type="submit" class="btn-save"><?php echo $editProduct ? 'Update Product' : 'Add Product'; ?></button>
                <?php if ($editProduct): ?>
                    <a href="admin_dashboard.php?tab=tab-products" style="margin-left:10px;">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>P_id</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Sub Category</th>
                    <th>Skin/Hair Type</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>In Stock</th>
                    <th>Guide</th>
                    <th>Benefits</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($products && $products->num_rows > 0): ?>
                    <?php while ($row = $products->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['P_id']; ?></td>
                            <td>
                                <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="prod" class="product-image-preview" onerror="this.style.display='none'">
                            </td>
                            <td><?php echo htmlspecialchars($row['P_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                            <td><?php echo htmlspecialchars($row['sub_category']); ?></td>
                            <td><?php echo htmlspecialchars($row['Skin/Hair_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['P_price']); ?></td>
                            <td><?php echo htmlspecialchars($row['P_quantity']); ?></td>
                            <td><?php echo htmlspecialchars($row['In_stock']); ?></td>
                            <td style="max-width:150px;"><?php echo htmlspecialchars($row['guide']); ?></td>
                            <td style="max-width:150px;"><?php echo htmlspecialchars($row['benifits']); ?></td>
                            <td>
                                <a href="admin_dashboard.php?edit_product=<?php echo $row['P_id']; ?>&tab=tab-products" class="action-link">Edit</a> |
                                <a href="admin_dashboard.php?delete_product=<?php echo $row['P_id']; ?>&tab=tab-products" class="action-link danger" onclick="return confirm('Delete product?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="12">No products found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- TAB 4: CUSTOMERS -->
    <div id="tab-customers" class="tab-content <?php echo $active_tab == 'tab-customers' ? 'active' : ''; ?>">
        <?php if (isset($_GET['customer_msg'])): ?>
            <div class="msg">Customer deleted successfully.</div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Registered At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($customers && $customers->num_rows > 0): ?>
                    <?php while ($row = $customers->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row['address'] ?? 'N/A'); ?></td>
                            <td><?php echo date("d M Y, h:i A", strtotime($row['created_at'])); ?></td>
                            <td>
                                <a href="admin_dashboard.php?delete_customer=<?php echo $row['id']; ?>&tab=tab-customers" class="action-link danger" onclick="return confirm('Delete customer?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7">No customers found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- TAB 5: ORDERS -->
    <?php if ($ordersExist): ?>
    <div id="tab-orders" class="tab-content <?php echo $active_tab == 'tab-orders' ? 'active' : ''; ?>">
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th>
                    <th>Products Description</th>
                    <th>Total Amount</th>
                    <th>Payment Status</th>
                    <th>Order Date</th>
                    <th>Status</th>
                    <th>Update Delivery Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders && $orders->num_rows > 0): ?>
                    <?php while ($row = $orders->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $row['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['customer_name'] ?? 'Customer ID: '.$row['customer_id']); ?></td>
                            <td style="max-width:250px;"><?php echo htmlspecialchars($row['products']); ?></td>
                            <td>Rs. <?php echo number_format($row['total_amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['payment_status']); ?></td>
                            <td><?php echo date("d M Y, h:i A", strtotime($row['order_date'])); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo str_replace(' ', '', $row['status']); ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" style="margin:0;">
                                    <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                    <input type="hidden" name="tab" value="tab-orders">
                                    <select name="status" class="action-select" onchange="this.form.submit()">
                                        <option value="Pending Delivery" <?php if ($row['status']=='Pending Delivery') echo 'selected'; ?>>Pending Delivery</option>
                                        <option value="Shipped" <?php if ($row['status']=='Shipped') echo 'selected'; ?>>Shipped</option>
                                        <option value="Delivered" <?php if ($row['status']=='Delivered') echo 'selected'; ?>>Delivered</option>
                                        <option value="Cancelled" <?php if ($row['status']=='Cancelled') echo 'selected'; ?>>Cancelled</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8">No orders found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</div>

<footer>&copy; <?php echo date("Y"); ?> GlowCare Skincare Product Management System</footer>

</body>
</html>
<?php $conn->close(); ?>