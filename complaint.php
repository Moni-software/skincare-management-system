<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Submit a Complaint - GlowCare IT Support</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="style.css">

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background: #f7f2ec;
        color: #3b2c25;
        font-family: "Segoe UI", Arial, sans-serif;
        line-height: 1.6;
        min-height: 100vh;
    }

    .complaint-hero {
        background:
            linear-gradient(rgba(48, 35, 27, 0.82), rgba(48, 35, 27, 0.82)),
            radial-gradient(circle at top right, #b88656 0%, #49362b 55%, #2f211b 100%);
        color: #ffffff;
        text-align: center;
        padding: 65px 20px 55px;
    }

    .complaint-hero .eyebrow {
        display: block;
        color: #e2bb92;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 3px;
        text-transform: uppercase;
        margin-bottom: 10px;
    }

    .complaint-hero h1 {
        font-family: Georgia, serif;
        font-size: clamp(34px, 5vw, 48px);
        font-weight: 400;
        margin-bottom: 10px;
    }

    .complaint-hero p {
        max-width: 620px;
        margin: 0 auto;
        color: #f3ece6;
        font-size: 14px;
    }

    .form-section {
        padding: 60px 20px 80px;
    }

    .form-wrapper {
        max-width: 760px;
        margin: 0 auto;
        background: #ffffff;
        border: 1px solid #e8ddd2;
        border-radius: 14px;
        box-shadow: 0 14px 35px rgba(71, 50, 35, 0.08);
        padding: 36px;
    }

    .form-wrapper h2 {
        font-family: Georgia, serif;
        color: #49362b;
        font-size: 30px;
        font-weight: 400;
        margin-bottom: 8px;
    }

    .form-wrapper .sub {
        color: #75675d;
        font-size: 14px;
        margin-bottom: 28px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 7px;
        color: #49362b;
        font-size: 13px;
        font-weight: 700;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        border: 1px solid #ddd0c4;
        border-radius: 7px;
        background: #fffdfb;
        color: #3b2c25;
        font-family: inherit;
        font-size: 14px;
        padding: 12px 14px;
        outline: none;
        transition: 0.25s ease;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        border-color: #b88656;
        box-shadow: 0 0 0 3px rgba(184, 134, 86, 0.10);
        background: #ffffff;
    }

    .form-group textarea {
        min-height: 150px;
        resize: vertical;
    }

    .error-text {
        margin-top: 5px;
        color: #b64b4b;
        font-size: 12px;
    }

    .btn {
        width: 100%;
        border: none;
        border-radius: 6px;
        background: #8c6239;
        color: #ffffff;
        padding: 13px 18px;
        font-family: inherit;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        cursor: pointer;
        transition: 0.3s ease;
    }

    .btn:hover {
        background: #6f4c2c;
        transform: translateY(-2px);
    }

    .alert {
        margin-bottom: 22px;
        padding: 13px 15px;
        border-radius: 7px;
        font-size: 13px;
    }

    .alert-success {
        background: #eef7ee;
        color: #2f6d39;
        border: 1px solid #cfe7d2;
    }

    .alert-error {
        background: #fff1f1;
        color: #a43d3d;
        border: 1px solid #efcccc;
    }

    .support-note {
        margin-top: 22px;
        padding: 15px;
        border-left: 3px solid #b88656;
        background: #fcfaf8;
        color: #75675d;
        font-size: 13px;
    }

    footer {
        text-align: center;
        padding: 28px 20px;
        background: #211915;
        color: #8f837d;
        font-size: 12px;
    }

    @media (max-width: 650px) {
        .complaint-hero {
            padding: 50px 18px 45px;
        }

        .form-section {
            padding: 40px 15px 60px;
        }

        .form-wrapper {
            padding: 24px 18px;
            border-radius: 10px;
        }

        .form-wrapper h2 {
            font-size: 26px;
        }
    }
</style>
</head>
<body>

<?php include 'navbar.php'; ?>

<section class="complaint-hero">
    <span class="eyebrow">Glow Care Help Desk</span>
    <h1>Submit a Support Ticket</h1>
    <p>Tell us what went wrong and our support team will review your request and get back to you.</p>
</section>

<section class="form-section">
    <div class="form-wrapper">
        <h2>Complaint Details</h2>
        <p class="sub">Please complete the form below with as much detail as possible.</p>

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

        <div class="support-note">
            For urgent assistance, you can also return to the IT Support page and use the support hotline, email or live chat.
        </div>
    </div>
</section>

<footer>&copy; <?php echo date("Y"); ?> Glow Care Pvt Ltd. All rights reserved.</footer>

<script src="js/script.js"></script>
</body>
</html>