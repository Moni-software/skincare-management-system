<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Submit a Complaint - GlowCare IT Support</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="form-wrapper">
    <h2>Submit a Complaint</h2>
    <p class="sub">Tell us what went wrong and our team will get back to you.</p>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
        <div class="alert alert-success">Your complaint has been submitted successfully. Our team will contact you soon.</div>
    <?php elseif (isset($_GET['status']) && $_GET['status'] === 'error'): ?>
        <div class="alert alert-error">Something went wrong. Please check your details and try again.</div>
    <?php endif; ?>

    <form id="complaintForm" action="submit_complaint.php" method="POST" novalidate>

        <div class="form-group">
            <label for="customer_name">Full Name</label>
            <input type="text" id="customer_name" name="customer_name" placeholder="e.g. Nimasha Perera">
            <div class="error-text"></div>
        </div>

        <div class="form-group">
            <label for="customer_email">Email Address</label>
            <input type="email" id="customer_email" name="customer_email" placeholder="you@example.com">
            <div class="error-text"></div>
        </div>

        <div class="form-group">
            <label for="customer_phone">Phone Number (optional)</label>
            <input type="text" id="customer_phone" name="customer_phone" placeholder="e.g. 077 1234567">
        </div>

        <div class="form-group">
            <label for="order_id">Order ID (if applicable)</label>
            <input type="text" id="order_id" name="order_id" placeholder="e.g. ORD-1024">
        </div>

        <div class="form-group">
            <label for="subject">Subject</label>
            <input type="text" id="subject" name="subject" placeholder="Brief summary of the issue">
            <div class="error-text"></div>
        </div>

        <div class="form-group">
            <label for="message">Message</label>
            <textarea id="message" name="message" placeholder="Describe your issue in detail..."></textarea>
            <div class="error-text"></div>
        </div>

        <button type="submit" class="btn">Submit Complaint</button>
    </form>
</div>

<footer>&copy; <?php echo date("Y"); ?> GlowCare Skincare Product Management System</footer>

<script src="js/script.js"></script>
</body>
</html>
