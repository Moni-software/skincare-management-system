<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "connect.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: complaint.php");
    exit();
}

$customer_name  = trim($_POST["customer_name"] ?? "");
$customer_email = trim($_POST["customer_email"] ?? "");
$customer_phone = trim($_POST["customer_phone"] ?? "");
$order_id_raw   = trim($_POST["order_id"] ?? "");
$subject        = trim($_POST["subject"] ?? "");
$message        = trim($_POST["message"] ?? "");

// Basic validation
if (
    $customer_name === "" ||
    $customer_email === "" ||
    !filter_var($customer_email, FILTER_VALIDATE_EMAIL) ||
    $subject === "" ||
    $message === ""
) {
    header("Location: complaint.php?status=error");
    exit();
}

mysqli_report(MYSQLI_REPORT_OFF);

$alterQueries = [
    "ALTER TABLE complaints ADD COLUMN IF NOT EXISTS customer_name_text VARCHAR(150) NULL",
    "ALTER TABLE complaints ADD COLUMN IF NOT EXISTS customer_email VARCHAR(190) NULL",
    "ALTER TABLE complaints ADD COLUMN IF NOT EXISTS customer_phone VARCHAR(50) NULL",
    "ALTER TABLE complaints ADD COLUMN IF NOT EXISTS subject VARCHAR(255) NULL",
    "ALTER TABLE complaints ADD COLUMN IF NOT EXISTS admin_reply TEXT NULL"
];

foreach ($alterQueries as $sql) {
    $conn->query($sql);
}

$conn->query("ALTER TABLE complaints MODIFY customer_id INT NULL");
$conn->query("ALTER TABLE complaints MODIFY order_id INT NULL");


$order_id = null;
if ($order_id_raw !== "" && ctype_digit($order_id_raw)) {
    $candidate = (int)$order_id_raw;

    $check = $conn->prepare("SELECT order_id FROM orders WHERE order_id = ? LIMIT 1");
    if ($check) {
        $check->bind_param("i", $candidate);
        $check->execute();
        $result = $check->get_result();
        if ($result && $result->num_rows > 0) {
            $order_id = $candidate;
        }
        $check->close();
    }
}

$customer_id = null;
$status = "Pending";

$stmt = $conn->prepare(
    "INSERT INTO complaints
        (customer_id, order_id, customer_name_text, customer_email, customer_phone, subject, message, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    header("Location: complaint.php?status=error");
    exit();
}

$stmt->bind_param(
    "iissssss",
    $customer_id,
    $order_id,
    $customer_name,
    $customer_email,
    $customer_phone,
    $subject,
    $message,
    $status
);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    header("Location: complaint.php?status=success");
    exit();
}

$stmt->close();
$conn->close();

header("Location: complaint.php?status=error");
exit();
?>