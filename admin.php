<?php

require_once "connect.php";

$admin = null;
$result = $conn->query("SELECT full_name, email, contact_no FROM admins LIMIT 1");
if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IT Support - GlowCare</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Updated path from css/style.css to style.css -->
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

        .page-header {
            text-align: center;
            padding: 60px 20px 40px;
        }

        .page-header h1 {
            font-size: 32px;
            color: var(--pink-dark);
            margin-bottom: 10px;
        }

        .page-header p {
            color: var(--text-muted);
            max-width: 560px;
            margin: 0 auto;
        }

        .card-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 24px;
            justify-content: center;
            padding: 20px 40px 60px;
        }

        .card {
            background: var(--white);
            border-radius: 14px;
            padding: 32px;
            width: 280px;
            text-align: center;
            box-shadow: 0 4px 14px rgba(0,0,0,0.06);
            border: 1px solid var(--border);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .card .icon {
            font-size: 38px;
            margin-bottom: 14px;
        }

        .card h3 {
            margin-bottom: 10px;
            color: var(--text-dark);
        }

        .card p {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 18px;
        }

        .btn {
            display: inline-block;
            background: var(--pink);
            color: var(--white);
            padding: 10px 22px;
            border-radius: 30px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.2s;
        }

        .btn:hover {
            background: var(--pink-dark);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--pink);
            color: var(--pink-dark);
        }

        .btn-outline:hover {
            background: var(--pink);
            color: var(--white);
        }

        .contact-strip {
            background: var(--white);
            margin: 0 40px 60px;
            border-radius: 14px;
            padding: 30px 40px;
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            justify-content: center;
            box-shadow: 0 4px 14px rgba(0,0,0,0.06);
        }

        .contact-item {
            text-align: center;
            min-width: 180px;
        }

        .contact-item .label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .contact-item .value {
            font-weight: 600;
            color: var(--pink-dark);
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

    <?php include 'navbar.php'; ?>

<div class="page-header">
    <h1>How can we help you?</h1>
    <p>Welcome to the GlowCare IT Support Center. Report an issue with your order, an app problem,
       or get in touch with our support team below.</p>
</div>

<div class="card-grid">
    <div class="card">
        <div class="icon">📝</div>
        <h3>Submit a Complaint</h3>
        <p>Facing an issue with your order, payment, or the skincare product management system? Let us know.</p>
        <a href="complaint.php" class="btn">Submit Complaint</a>
    </div>

    <div class="card">
        <div class="icon">🔐</div>
        <h3>Admin Login</h3>
        <p>Are you a GlowCare administrator? Log in to view and manage customer complaints.</p>
        <a href="admin_login.php" class="btn btn-outline">Admin Login</a>
    </div>
</div>

<div class="contact-strip">
    <div class="contact-item">
        <div class="label">Support Email</div>
        <div class="value"><?php echo htmlspecialchars($admin['email'] ?? 'support@glowcare.com'); ?></div>
    </div>
    <div class="contact-item">
        <div class="label">Support Hotline</div>
        <div class="value"><?php echo htmlspecialchars($admin['contact_no'] ?? '+94 71 234 5678'); ?></div>
    </div>
    <div class="contact-item">
        <div class="label">Working Hours</div>
        <div class="value">Mon - Sat, 9:00 AM - 6:00 PM</div>
    </div>
</div>

<footer>&copy; <?php echo date("Y"); ?> GlowCare Skincare Product Management System</footer>

</body>
</html>