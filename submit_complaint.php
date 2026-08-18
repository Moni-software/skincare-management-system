<?php
/* =========================================================
   submit_complaint.php
   Validates the complaint form and inserts it into
   the `complaints` table using a prepared statement.
   ========================================================= */
require_once "connect.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: complaint.php");
    exit;
}

// Trim all incoming fields
$customer_name  = trim($_POST['customer_name'] ?? '');
$customer_email = trim($_POST['customer_email'] ?? '');
$customer_phone = trim($_POST['customer_phone'] ?? '');
$order_id       = trim($_POST['order_id'] ?? '');
$subject        = trim($_POST['subject'] ?? '');
$message        = trim($_POST['message'] ?? '');

// Server-side validation (never trust client-side JS alone)
if (
    strlen($customer_name) < 2 ||
    !filter_var($customer_email, FILTER_VALIDATE_EMAIL) ||
    strlen($subject) < 3 ||
    strlen($message) < 10
) {
    header("Location: complaint.php?status=error");
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO complaints (customer_name, customer_email, customer_phone, order_id, subject, message)
     VALUES (?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param(
    "ssssss",
    $customer_name,
    $customer_email,
    $customer_phone,
    $order_id,
    $subject,
    $message
);

if ($stmt->execute()) {
    header("Location: complaint.php?status=success");
} else {
    header("Location: complaint.php?status=error");
}

$stmt->close();
$conn->close();
exit;
?>

