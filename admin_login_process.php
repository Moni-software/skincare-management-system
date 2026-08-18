<?php
session_start();
require_once "connect.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: admin_login.php");
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    header("Location: admin_login.php?error=1");
    exit;
}

$stmt = $conn->prepare("SELECT admin_id, full_name, username, password FROM admins WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $admin = $result->fetch_assoc();

    // Check both plain text matching and hashed verification
    if ($password === $admin['password'] || password_verify($password, $admin['password'])) {
        // Correct credentials -> start session
        $_SESSION['admin_id']   = $admin['admin_id'];
        $_SESSION['admin_name'] = $admin['full_name'];
        $stmt->close();
        $conn->close();
        header("Location: admin_dashboard.php");
        exit;
    }
}

$stmt->close();
$conn->close();
header("Location: admin_login.php?error=1");
exit;
?>