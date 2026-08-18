<?php
session_start();

if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Login - GlowCare IT Support</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- Corrected file path from css/style.css to style.css -->
<link rel="stylesheet" href="style.css">
<style>
    :root {
        --pink: #e88ba4;
        --pink-dark: #c96a86;
        --pink-light: #fdf1f3;
        --text-dark: #3b2a2f;
        --text-muted: #7a6a6e;
        --white: #ffffff;
        --border: #f0d9df;
    }

    body {
        background-color: var(--pink-light);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        padding: 0;
    }

    /* Navbar styling */
    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: var(--white);
        padding: 15px 40px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .navbar .logo {
        font-weight: bold;
        font-size: 20px;
        color: var(--pink-dark);
    }

    .navbar nav a {
        margin-left: 20px;
        text-decoration: none;
        color: var(--text-dark);
        font-weight: 500;
    }

    /* Form Container Styling */
    .form-wrapper {
        background: var(--white);
        max-width: 400px;
        margin: 60px auto;
        padding: 40px;
        border-radius: 14px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.06);
        border: 1px solid var(--border);
    }

    .form-wrapper h2 {
        margin-top: 0;
        color: var(--pink-dark);
        text-align: center;
    }

    .form-wrapper .sub {
        text-align: center;
        color: var(--text-muted);
        font-size: 14px;
        margin-bottom: 24px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
        font-size: 14px;
        color: var(--text-dark);
    }

    .form-group input {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--border);
        border-radius: 8px;
        box-sizing: border-box;
        font-size: 14px;
    }

    .form-group input:focus {
        outline: none;
        border-color: var(--pink);
    }

    .btn {
        width: 100%;
        background: var(--pink);
        color: var(--white);
        padding: 12px;
        border-radius: 30px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        font-size: 15px;
        transition: background 0.2s;
    }

    .btn:hover {
        background: var(--pink-dark);
    }

    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
        padding: 10px 14px;
        border-radius: 8px;
        font-size: 14px;
        margin-bottom: 20px;
        text-align: center;
    }

    footer {
        text-align: center;
        padding: 20px;
        color: var(--text-muted);
        font-size: 13px;
    }
</style>
</head>
<body>

<div class="navbar">
    <div class="logo">GlowCare <span>IT Support</span></div>
    <nav>
        <a href="admin.php">Home</a>
        <a href="complaint.php">Submit Complaint</a>
        <a href="admin_login.php">Admin Login</a>
    </nav>
</div>

<div class="form-wrapper">
    <h2>Admin Login</h2>
    <p class="sub">Log in to manage customer complaints.</p>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error">Invalid username or password. Please try again.</div>
    <?php endif; ?>

    <form id="adminLoginForm" action="admin_login_process.php" method="POST" novalidate>

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Enter your username">
            <div class="error-text"></div>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password">
            <div class="error-text"></div>
        </div>

        <button type="submit" class="btn">Login</button>
    </form>
</div>

<footer>&copy; <?php echo date("Y"); ?> GlowCare Skincare Product Management System</footer>

<script src="js/script.js"></script>
</body>
</html>