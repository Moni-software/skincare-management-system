<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "connect.php";

$admin = null;
$result = $conn->query("SELECT full_name, email, contact_no FROM admins LIMIT 1");
if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
}

$supportEmail = htmlspecialchars($admin['email'] ?? 'support@glowcare.com');
$supportPhone = htmlspecialchars($admin['contact_no'] ?? '+94 71 234 5678');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Support - Glow Care</title>
    <link rel="stylesheet" href="style.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            background: #fbf7f4;
            color: #3b2c25;
            font-family: "Segoe UI", Arial, sans-serif;
            line-height: 1.6;
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
        }

        /* ===== HERO ===== */
        .support-hero {
            min-height: 360px;
            padding: 80px 25px 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #ffffff;
            background:
                linear-gradient(rgba(48, 35, 27, 0.86), rgba(48, 35, 27, 0.76)),
                radial-gradient(circle at top right, #b88656 0%, #49362b 55%, #2f211b 100%);
        }

        .support-hero-content {
            max-width: 760px;
            animation: fadeUp 0.8s ease both;
        }

        .eyebrow {
            display: block;
            margin-bottom: 12px;
            color: #e2bb92;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .support-hero h1 {
            margin-bottom: 15px;
            font-family: Georgia, serif;
            font-size: clamp(38px, 6vw, 58px);
            font-weight: 400;
            line-height: 1.15;
        }

        .support-hero h1 span {
            color: #d6a779;
        }

        .support-hero p {
            max-width: 650px;
            margin: 0 auto 28px;
            color: #f3ece6;
            font-size: 15px;
        }

        .hero-actions {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 12px 22px;
            border: 1px solid transparent;
            border-radius: 5px;
            background: #8c6239;
            color: #ffffff;
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

        .btn-light {
            border-color: rgba(255,255,255,0.7);
            background: transparent;
        }

        .btn-light:hover {
            background: #ffffff;
            color: #49362b;
        }

        /* ===== SHARED SECTIONS ===== */
        .section {
            max-width: 1200px;
            margin: auto;
            padding: 78px 25px;
        }

        .section-heading {
            max-width: 700px;
            margin: 0 auto 42px;
            text-align: center;
        }

        .section-heading .eyebrow {
            color: #b88656;
        }

        .section-heading h2 {
            margin-bottom: 12px;
            color: #49362b;
            font-family: Georgia, serif;
            font-size: 36px;
            font-weight: 400;
        }

        .section-heading p {
            color: #75675d;
            font-size: 14px;
        }

        /* ===== SUPPORT OPTIONS ===== */
        .support-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
        }

        .support-card {
            position: relative;
            padding: 32px 26px;
            overflow: hidden;
            border: 1px solid #e8ddd2;
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 8px 24px rgba(71, 50, 35, 0.05);
            transition: 0.3s ease;
        }

        .support-card:hover {
            transform: translateY(-7px);
            box-shadow: 0 16px 32px rgba(71, 50, 35, 0.10);
        }

        .support-icon {
            width: 62px;
            height: 62px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #f2e7db;
            font-size: 27px;
        }

        .support-card h3 {
            margin-bottom: 10px;
            color: #49362b;
            font-family: Georgia, serif;
            font-size: 22px;
            font-weight: 400;
        }

        .support-card p {
            min-height: 70px;
            margin-bottom: 22px;
            color: #75675d;
            font-size: 13px;
        }

        /* ===== CONTACT ===== */
        .contact-section {
            background: #49362b;
            color: #ffffff;
        }

        .contact-wrap {
            max-width: 1200px;
            margin: auto;
            padding: 62px 25px;
        }

        .contact-title {
            margin-bottom: 30px;
            text-align: center;
        }

        .contact-title .eyebrow {
            color: #d5a97c;
        }

        .contact-title h2 {
            font-family: Georgia, serif;
            font-size: 32px;
            font-weight: 400;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        .contact-item {
            padding: 26px 20px;
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 10px;
            background: rgba(255,255,255,0.05);
            text-align: center;
        }

        .contact-item .contact-icon {
            margin-bottom: 10px;
            font-size: 25px;
        }

        .contact-item .label {
            margin-bottom: 6px;
            color: #d5a97c;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .contact-item .value {
            color: #ffffff;
            font-size: 14px;
            word-break: break-word;
        }

        /* ===== KNOWLEDGE BASE ===== */
        .self-service {
            background: #f7f2ec;
        }

        .knowledge-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .knowledge-card {
            padding: 28px;
            border-left: 3px solid #b88656;
            background: #ffffff;
            box-shadow: 0 8px 22px rgba(71, 50, 35, 0.04);
            transition: 0.3s ease;
        }

        .knowledge-card:hover {
            transform: translateX(5px);
            box-shadow: 0 12px 26px rgba(71, 50, 35, 0.08);
        }

        .knowledge-card h3 {
            margin-bottom: 10px;
            color: #49362b;
            font-family: Georgia, serif;
            font-size: 20px;
            font-weight: 400;
        }

        .knowledge-card p {
            margin-bottom: 14px;
            color: #75675d;
            font-size: 13px;
        }

        .guide-link {
            color: #8c6239;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
        }

        .guide-link:hover {
            color: #49362b;
        }

        /* ===== FAQ ===== */
        .faq-list {
            max-width: 900px;
            margin: auto;
        }

        .faq-item {
            margin-bottom: 12px;
            overflow: hidden;
            border: 1px solid #e8ddd2;
            border-radius: 9px;
            background: #ffffff;
        }

        .faq-question {
            width: 100%;
            padding: 19px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            border: none;
            background: #ffffff;
            color: #49362b;
            font-family: inherit;
            font-size: 14px;
            font-weight: 700;
            text-align: left;
            cursor: pointer;
        }

        .faq-question:hover {
            background: #fcfaf8;
        }

        .faq-symbol {
            flex: 0 0 auto;
            color: #8c6239;
            font-size: 21px;
            font-weight: 400;
            transition: transform .25s ease;
        }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            padding: 0 22px;
            color: #75675d;
            font-size: 13px;
            transition: max-height .3s ease, padding .3s ease;
        }

        .faq-item.active .faq-answer {
            max-height: 220px;
            padding: 0 22px 20px;
        }

        .faq-item.active .faq-symbol {
            transform: rotate(45deg);
        }

        /* ===== STAFF ACCESS ===== */
        .staff-access {
            position: relative;
            z-index: 5;
            max-width: 900px;
            margin: 0 auto 70px;
            padding: 25px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            border: 1px solid #e8ddd2;
            border-radius: 10px;
            background: #fcfaf8;
        }

        .staff-access h3 {
            margin-bottom: 4px;
            color: #49362b;
            font-family: Georgia, serif;
            font-weight: 400;
        }

        .staff-access p {
            color: #75675d;
            font-size: 13px;
        }

        .btn-outline {
            border-color: #8c6239;
            background: transparent;
            color: #8c6239;
            white-space: nowrap;
        }

        .btn-outline:hover {
            background: #8c6239;
            color: #ffffff;
        }

        .admin-login-btn {
            position: relative;
            z-index: 10;
            pointer-events: auto !important;
        }

        /* ===== CHAT ===== */
        .chat-launcher {
            position: fixed;
            right: 24px;
            bottom: 24px;
            z-index: 1000;
            width: 58px;
            height: 58px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            border-radius: 50%;
            background: #8c6239;
            color: #ffffff;
            font-size: 25px;
            cursor: pointer;
            box-shadow: 0 10px 30px rgba(49, 33, 24, 0.28);
            transition: .3s ease;
        }

        .chat-launcher:hover {
            transform: translateY(-3px);
            background: #6f4c2c;
        }

        .chat-panel {
            position: fixed;
            right: 24px;
            bottom: 94px;
            z-index: 1000;
            width: min(360px, calc(100vw - 32px));
            overflow: hidden;
            border: 1px solid #e8ddd2;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 18px 50px rgba(49, 33, 24, 0.22);
            opacity: 0;
            pointer-events: none;
            transform: translateY(15px) scale(.98);
            transition: .25s ease;
        }

        .chat-panel.open {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0) scale(1);
        }

        .chat-header {
            padding: 17px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #49362b;
            color: #ffffff;
        }

        .chat-header h4 {
            font-family: Georgia, serif;
            font-size: 17px;
            font-weight: 400;
        }

        .chat-status {
            margin-top: 2px;
            color: #d9c5b3;
            font-size: 11px;
        }

        .chat-close {
            border: 0;
            background: transparent;
            color: #ffffff;
            font-size: 24px;
            cursor: pointer;
        }

        .chat-messages {
            height: 265px;
            padding: 16px;
            overflow-y: auto;
            background: #fcfaf8;
        }

        .message {
            max-width: 85%;
            margin-bottom: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            font-size: 12px;
            line-height: 1.5;
        }

        .message.bot {
            background: #f2e7db;
            color: #49362b;
            border-bottom-left-radius: 3px;
        }

        .message.user {
            margin-left: auto;
            background: #8c6239;
            color: #ffffff;
            border-bottom-right-radius: 3px;
        }

        .quick-actions {
            padding: 0 16px 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            background: #fcfaf8;
        }

        .quick-action {
            padding: 7px 9px;
            border: 1px solid #d9c7b7;
            border-radius: 20px;
            background: #ffffff;
            color: #6f4c2c;
            font-size: 10px;
            cursor: pointer;
        }

        .chat-input-row {
            padding: 12px;
            display: flex;
            gap: 8px;
            border-top: 1px solid #eee4da;
        }

        .chat-input-row input {
            flex: 1;
            min-width: 0;
            padding: 10px 11px;
            border: 1px solid #ddd0c4;
            border-radius: 6px;
            outline: none;
            font-family: inherit;
            font-size: 12px;
        }

        .chat-input-row input:focus {
            border-color: #b88656;
        }

        .chat-send {
            padding: 9px 12px;
            border: none;
            border-radius: 6px;
            background: #8c6239;
            color: #ffffff;
            cursor: pointer;
        }

        /* ===== FOOTER ===== */
        footer {
            padding: 28px 20px;
            background: #211915;
            color: #8f837d;
            text-align: center;
            font-size: 12px;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(22px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 950px) {
            .support-grid,
            .knowledge-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .contact-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 650px) {
            .support-hero {
                min-height: 330px;
                padding: 65px 20px 55px;
            }

            .section {
                padding: 62px 18px;
            }

            .section-heading h2 {
                font-size: 30px;
            }

            .support-grid,
            .knowledge-grid {
                grid-template-columns: 1fr;
            }

            .support-card p {
                min-height: auto;
            }

            .staff-access {
                margin: 0 18px 55px;
                flex-direction: column;
                align-items: flex-start;
            }

            .chat-launcher {
                right: 16px;
                bottom: 16px;
            }

            .chat-panel {
                right: 16px;
                bottom: 84px;
            }
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<!-- HERO -->
<section class="support-hero">
    <div class="support-hero-content">
        <span class="eyebrow">Glow Care Support Center</span>
        <h1>How Can We <span>Help You?</span></h1>
        <p>
            Get help with account access, orders, payments, system issues and general technical support.
            Choose an option below or use our self-service resources for quick assistance.
        </p>
        <div class="hero-actions">
            <a href="complaint.php" class="btn">Submit a Ticket</a>
            <button type="button" class="btn btn-light" onclick="openChat()">Start Live Chat</button>
        </div>
    </div>
</section>

<!-- DIRECT SUPPORT -->
<section class="section" id="support-options">
    <div class="section-heading">
        <span class="eyebrow">Direct Assistance</span>
        <h2>Get Support Your Way</h2>
        <p>Report a problem, chat with support, or contact our team directly when you need assistance.</p>
    </div>

    <div class="support-grid">
        <div class="support-card">
            <div class="support-icon">🎫</div>
            <h3>Submit a Ticket</h3>
            <p>Describe your issue using our Help Desk form. Our support team can review the details and follow up on your request.</p>
            <a href="complaint.php" class="btn">Open Help Desk</a>
        </div>

        <div class="support-card">
            <div class="support-icon">💬</div>
            <h3>Live Chat / Chatbot</h3>
            <p>Need a quick answer? Open the support chat for instant guidance on common account, order and system questions.</p>
            <button type="button" class="btn" onclick="openChat()">Start Chat</button>
        </div>

        <div class="support-card">
            <div class="support-icon">📞</div>
            <h3>Contact Support</h3>
            <p>For urgent assistance, contact Glow Care support directly by phone or email during our available support hours.</p>
            <a href="#contact-support" class="btn">View Contacts</a>
        </div>
    </div>
</section>

<!-- CONTACT INFORMATION -->
<section class="contact-section" id="contact-support">
    <div class="contact-wrap">
        <div class="contact-title">
            <span class="eyebrow">Contact Information</span>
            <h2>Need Immediate Assistance?</h2>
        </div>

        <div class="contact-grid">
            <div class="contact-item">
                <div class="contact-icon">✉️</div>
                <div class="label">Support Email</div>
                <div class="value"><?php echo $supportEmail; ?></div>
            </div>

            <div class="contact-item">
                <div class="contact-icon">☎️</div>
                <div class="label">Direct Hotline</div>
                <div class="value"><?php echo $supportPhone; ?></div>
            </div>

            <div class="contact-item">
                <div class="contact-icon">🕘</div>
                <div class="label">Support Hours</div>
                <div class="value">Monday - Saturday &nbsp; | &nbsp; 9:00 AM - 6:00 PM</div>
            </div>
        </div>
    </div>
</section>

<!-- SELF-SERVICE / KNOWLEDGE BASE -->
<section class="self-service" id="knowledge-base">
    <div class="section">
        <div class="section-heading">
            <span class="eyebrow">Self-Service Options</span>
            <h2>Knowledge Base & Documentation</h2>
            <p>Use these quick guides and tutorials to solve common technical problems without waiting for assistance.</p>
        </div>

        <div class="knowledge-grid">
            <div class="knowledge-card">
                <h3>🔐 Account & Password Help</h3>
                <p>Learn what to do when you cannot log in, forget your password, or need to update account information.</p>
                <a href="#faq-password" class="guide-link">View Guide →</a>
            </div>

            <div class="knowledge-card">
                <h3>🛒 Orders & Payments</h3>
                <p>Find guidance for cart issues, checkout problems, payment errors and common order-related questions.</p>
                <a href="#faq-payment" class="guide-link">View Guide →</a>
            </div>

            <div class="knowledge-card">
                <h3>🖥️ Website Troubleshooting</h3>
                <p>Try simple browser and connection checks when a page does not load correctly or a feature is not responding.</p>
                <a href="#faq-browser" class="guide-link">View Guide →</a>
            </div>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="section" id="faqs">
    <div class="section-heading">
        <span class="eyebrow">Frequently Asked Questions</span>
        <h2>Quick Answers</h2>
        <p>Find answers to frequently reported technical and account support questions.</p>
    </div>

    <div class="faq-list">
        <div class="faq-item" id="faq-password">
            <button class="faq-question" type="button">
                <span>How do I reset my password?</span>
                <span class="faq-symbol">+</span>
            </button>
            <div class="faq-answer">
                <p>Go to the login page and use the available password recovery option. Enter the email address linked to your account and follow the instructions provided. If you still cannot access your account, submit a support ticket.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" type="button">
                <span>What should I do if I cannot log in?</span>
                <span class="faq-symbol">+</span>
            </button>
            <div class="faq-answer">
                <p>Check that your email and password are entered correctly. If the issue continues, reset your password or use the Help Desk to report the login problem to support.</p>
            </div>
        </div>

        <div class="faq-item" id="faq-payment">
            <button class="faq-question" type="button">
                <span>What should I do if a payment or checkout fails?</span>
                <span class="faq-symbol">+</span>
            </button>
            <div class="faq-answer">
                <p>Check your internet connection, refresh the page and try again. If the payment issue continues, avoid submitting repeated payments and contact support with the relevant order details.</p>
            </div>
        </div>

        <div class="faq-item" id="faq-browser">
            <button class="faq-question" type="button">
                <span>What can I do if a website feature is not working?</span>
                <span class="faq-symbol">+</span>
            </button>
            <div class="faq-answer">
                <p>Refresh the page, check your internet connection and try using an updated browser. If the problem remains, submit a ticket and include a short description of what happened.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" type="button">
                <span>How can I get help with software or system usage?</span>
                <span class="faq-symbol">+</span>
            </button>
            <div class="faq-answer">
                <p>Use the Knowledge Base for common guidance first. For issues that require individual assistance, start the support chat or submit a Help Desk ticket with the system or feature you need help with.</p>
            </div>
        </div>
    </div>
</section>

<!-- ADMIN ACCESS -->
<div class="staff-access">
    <div>
        <h3>Support Staff Access</h3>
        <p>Glow Care administrators can log in to review and manage submitted customer support requests.</p>
    </div>
    <a href="admin_dashboard.php"
       class="btn btn-outline admin-login-btn"
       onclick="window.location.href='admin_dashboard.php'; return false;"
       role="button">
        Admin Login
    </a>
</div>

<footer>
    &copy; <?php echo date("Y"); ?> Glow Care Pvt Ltd. All rights reserved.
</footer>

<!-- CHATBOT PANEL -->
<button class="chat-launcher" type="button" onclick="toggleChat()" aria-label="Open support chat">💬</button>

<div class="chat-panel" id="chatPanel">
    <div class="chat-header">
        <div>
            <h4>Glow Care Support</h4>
            <div class="chat-status">● Quick help assistant</div>
        </div>
        <button class="chat-close" type="button" onclick="closeChat()" aria-label="Close chat">×</button>
    </div>

    <div class="chat-messages" id="chatMessages">
        <div class="message bot">Hello! 👋 How can I help you today? Choose a quick option below or type your question.</div>
    </div>

    <div class="quick-actions">
        <button class="quick-action" type="button" onclick="quickReply('password')">Password Help</button>
        <button class="quick-action" type="button" onclick="quickReply('order')">Order / Payment</button>
        <button class="quick-action" type="button" onclick="quickReply('technical')">Technical Issue</button>
        <button class="quick-action" type="button" onclick="quickReply('ticket')">Submit Ticket</button>
    </div>

    <div class="chat-input-row">
        <input type="text" id="chatInput" placeholder="Type your message..." autocomplete="off">
        <button class="chat-send" type="button" onclick="sendMessage()">Send</button>
    </div>
</div>

<script>
    // FAQ accordion
    document.querySelectorAll('.faq-question').forEach(function(button) {
        button.addEventListener('click', function() {
            const item = this.parentElement;
            const isOpen = item.classList.contains('active');

            document.querySelectorAll('.faq-item').forEach(function(faq) {
                faq.classList.remove('active');
            });

            if (!isOpen) {
                item.classList.add('active');
            }
        });
    });

    const chatPanel = document.getElementById('chatPanel');
    const chatMessages = document.getElementById('chatMessages');
    const chatInput = document.getElementById('chatInput');

    function openChat() {
        chatPanel.classList.add('open');
        setTimeout(function() { chatInput.focus(); }, 200);
    }

    function closeChat() {
        chatPanel.classList.remove('open');
    }

    function toggleChat() {
        chatPanel.classList.toggle('open');
        if (chatPanel.classList.contains('open')) {
            setTimeout(function() { chatInput.focus(); }, 200);
        }
    }

    function addMessage(text, type) {
        const message = document.createElement('div');
        message.className = 'message ' + type;
        message.textContent = text;
        chatMessages.appendChild(message);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function getBotResponse(message) {
        const text = message.toLowerCase();

        if (text.includes('password') || text.includes('login') || text.includes('account')) {
            return 'For account or password problems, use the password recovery option on the login page. If you still cannot access your account, please submit a Help Desk ticket.';
        }

        if (text.includes('order') || text.includes('payment') || text.includes('checkout') || text.includes('cart')) {
            return 'For order or payment issues, check your connection and order details first. If the problem continues, submit a ticket with the relevant order information.';
        }

        if (text.includes('technical') || text.includes('website') || text.includes('error') || text.includes('not working')) {
            return 'Try refreshing the page and using an updated browser. If the issue remains, submit a support ticket and describe the page or feature causing the problem.';
        }

        if (text.includes('ticket') || text.includes('complaint') || text.includes('help desk')) {
            return 'You can submit a support ticket using the Open Help Desk button on this page. Our support team can then review your issue.';
        }

        if (text.includes('contact') || text.includes('phone') || text.includes('email')) {
            return 'You can contact Glow Care support using the phone number or email shown in the Contact Information section of this page.';
        }

        return 'Thanks for your message. For the fastest help, choose a quick option, review the FAQs, or submit a Help Desk ticket with details about your issue.';
    }

    function sendMessage() {
        const text = chatInput.value.trim();
        if (!text) return;

        addMessage(text, 'user');
        chatInput.value = '';

        setTimeout(function() {
            addMessage(getBotResponse(text), 'bot');
        }, 350);
    }

    function quickReply(type) {
        const options = {
            password: 'I need help with my password or login.',
            order: 'I have an order or payment issue.',
            technical: 'A website feature is not working.',
            ticket: 'How do I submit a support ticket?'
        };

        chatInput.value = options[type] || '';
        sendMessage();
    }

    chatInput.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            sendMessage();
        }
    });
</script>

</body>
</html>