<?php
session_start();
include 'connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please log in first.']);
    exit();
}

$customer_id = $_SESSION['customer_id'];
$product_id = intval($_POST['product_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid product details.']);
    exit();
}

// 1. Fetch the exact, correct price and details directly from the database
$stmt = $conn->prepare("SELECT P_name, P_price, image FROM product WHERE P_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Product not found.']);
    exit();
}

$product = $result->fetch_assoc();
$product_name = $product['P_name'];
$product_price = $product['P_price']; // Secure database price (e.g., 4890.00)
$product_image = basename($product['image']);
$stmt->close();

// 2. Check if item already exists in database cart for this customer
$chk = $conn->prepare("SELECT cart_id, quantity FROM cart WHERE customer_id = ? AND product_name = ?");
$chk->bind_param("is", $customer_id, $product_name);
$chk->execute();
$res = $chk->get_result();

if ($res->num_rows > 0) {
    // Update existing cart item quantity
    $row = $res->fetch_assoc();
    $new_qty = $row['quantity'] + $quantity;
    
    $up = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
    $up->bind_param("ii", $new_qty, $row['cart_id']);
    $up->execute();
    $up->close();
} else {
    // Insert new item into database cart with the secure DB price
    $ins = $conn->prepare("INSERT INTO cart (customer_id, product_name, product_price, quantity, product_image) VALUES (?, ?, ?, ?, ?)");
    $ins->bind_param("isdis", $customer_id, $product_name, $product_price, $quantity, $product_image);
    $ins->execute();
    $ins->close();
}

$chk->close();
echo json_encode(['status' => 'success']);
?>