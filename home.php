<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Glow Care - Home</title>
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
            font-family: "Segoe UI", Arial, sans-serif;
            background: #ffffff;
            color: #3b2c25;
            line-height: 1.6;
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
        }

        .hero-section {
            min-height: 650px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 80px 20px;
            color: #ffffff;
            position: relative;
            background:
                linear-gradient(
                    rgba(45, 32, 25, 0.48),
                    rgba(45, 32, 25, 0.58)
                ),
                url("images/home.jpg")
                center/cover no-repeat;
            overflow: hidden;
        }

        .hero-content {
            max-width: 800px;
            animation: heroFade 1.2s ease forwards;
        }

        .hero-small-title {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 4px;
            margin-bottom: 18px;
            color: #e4c19c;
        }

        .hero-section h1 {
            font-family: Georgia, serif;
            font-size: clamp(42px, 7vw, 78px);
            font-weight: 400;
            letter-spacing: 2px;
            margin-bottom: 20px;
        }

        .hero-section h1 span {
            color: #d6a779;
        }

        .hero-section p {
            max-width: 650px;
            margin: 0 auto 35px;
            font-size: 17px;
            color: #f8f4ef;
            line-height: 1.8;
        }

        .hero-btn {
            display: inline-block;
            background: #b88656;
            color: #ffffff;
            padding: 14px 32px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            transition: 0.3s;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .hero-btn:hover {
            background: #ffffff;
            color: #5b4232;
            transform: translateY(-3px);
        }

        @keyframes heroFade {
            from {
                opacity: 0;
                transform: translateY(35px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stats-section {
            background: #f7f2ec;
            padding: 55px 20px;
        }

        .stats-container {
            max-width: 1100px;
            margin: auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            background: #ffffff;
            border: 1px solid #e8ddd2;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(67,48,35,0.06);
        }

        .stat-box {
            text-align: center;
            padding: 35px 20px;
            border-right: 1px solid #eee4da;
            transition: 0.3s;
        }

        .stat-box:last-child {
            border-right: none;
        }

        .stat-box:hover {
            background: #fcfaf8;
            transform: translateY(-4px);
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #8c6239;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 13px;
            color: #77675c;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .section {
            max-width: 1200px;
            margin: auto;
            padding: 90px 25px;
        }

        .section-heading {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-heading span {
            display: block;
            color: #b88656;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 3px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .section-heading h2 {
            font-family: Georgia, serif;
            font-size: 38px;
            font-weight: 400;
            color: #3c2d25;
            margin-bottom: 12px;
        }

        .section-heading p {
            max-width: 600px;
            margin: auto;
            color: #75675d;
            font-size: 15px;
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 22px;
        }

        .category-card {
            background: #ffffff;
            border: 1px solid #e8ddd2;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.4s ease, box-shadow 0.4s ease;
            display: flex;
            flex-direction: column;
        }

        .category-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 18px 40px rgba(71,50,35,0.12);
        }

        .category-image {
            width: 100%;
            height: 230px;
            overflow: hidden;
        }

        .category-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: 0.6s ease;
        }

        .category-card:hover .category-image img {
            transform: scale(1.08);
        }

        .category-content {
            padding: 25px 18px;
            text-align: center;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .category-content h3 {
            font-family: Georgia, serif;
            font-size: 22px;
            font-weight: 400;
            color: #49362b;
            margin-bottom: 8px;
        }

        .category-content p {
            font-size: 13px;
            color: #77675c;
            margin-bottom: 20px;
            flex: 1;
        }

        .category-btn {
            display: inline-block;
            padding: 10px 18px;
            background: #8c6239;
            color: #ffffff;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s;
        }

        .category-btn:hover {
            background: #3c2d25;
            transform: translateY(-2px);
        }

        /* Brand Intro Banner */
        .brand-intro-banner {
            max-width: 1200px;
            margin: 0 auto 90px;
            min-height: 320px;
            padding: 60px 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            border-radius: 14px;
            background: 
                linear-gradient(
                    135deg,
                    rgba(48,35,27,0.92),
                    rgba(65,48,38,0.85)
                ),
                url("images/beauty-banner.jpg")
                center/cover no-repeat;
            color: #ffffff;
            box-shadow: 0 15px 35px rgba(48,35,27,0.15);
        }

        .intro-banner-content {
            max-width: 700px;
        }

        .intro-banner-content span {
            color: #e2bb92;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 3px;
            display: block;
            margin-bottom: 12px;
            font-weight: 600;
        }

        .intro-banner-content h2 {
            font-family: Georgia, serif;
            font-size: 38px;
            font-weight: 400;
            margin-bottom: 15px;
            color: #fff;
            letter-spacing: 1px;
        }

        .intro-banner-content p {
            color: #f2eae3;
            font-size: 16px;
            line-height: 1.8;
            margin: 0;
        }

        .why-section {
            background: #f7f2ec;
            padding: 90px 25px;
        }

        .why-container {
            max-width: 1200px;
            margin: auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
        }

        .feature-card {
            background: #ffffff;
            padding: 35px 25px;
            text-align: center;
            border-radius: 12px;
            border: 1px solid #e7ddd3;
            transition: 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-7px);
            box-shadow: 0 15px 30px rgba(71,50,35,0.09);
        }

        .feature-icon {
            width: 65px;
            height: 65px;
            margin: 0 auto 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #f2e7db;
            font-size: 27px;
        }

        .feature-card h3 {
            font-family: Georgia, serif;
            font-size: 20px;
            font-weight: 400;
            color: #49362b;
            margin-bottom: 10px;
        }

        .feature-card p {
            font-size: 13px;
            color: #75675d;
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }

        .benefit-card {
            padding: 35px;
            border-left: 3px solid #b88656;
            background: #fcfaf8;
            transition: 0.3s;
        }

        .benefit-card:hover {
            transform: translateX(6px);
            box-shadow: 0 10px 30px rgba(71,50,35,0.07);
        }

        .benefit-card h3 {
            font-family: Georgia, serif;
            font-size: 22px;
            font-weight: 400;
            color: #49362b;
            margin-bottom: 10px;
        }

        .benefit-card p {
            font-size: 14px;
            color: #75675d;
        }

        .contact-strip {
            background: #49362b;
            color: #ffffff;
            padding: 45px 25px;
            display: flex;
            justify-content: center;
            gap: 100px;
            text-align: center;
            flex-wrap: wrap;
        }

        .contact-item .label {
            color: #d5a97c;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 5px;
        }

        .contact-item .value {
            font-size: 15px;
            color: #ffffff;
        }

        footer {
            background: #211915;
            color: #aaa;
            padding: 55px 25px 25px;
        }

        .footer-container {
            max-width: 1200px;
            margin: auto;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 50px;
            padding-bottom: 40px;
            border-bottom: 1px solid #392d27;
        }

        .footer-brand h2 {
            font-family: Georgia, serif;
            font-weight: 400;
            font-size: 28px;
            color: #ffffff;
            margin-bottom: 15px;
        }

        .footer-brand h2 span {
            color: #d2a679;
        }

        .footer-brand p {
            max-width: 350px;
            font-size: 13px;
            line-height: 1.8;
            color: #999;
        }

        .footer-column h3 {
            color: #ffffff;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 18px;
        }

        .footer-column a {
            display: block;
            color: #999;
            font-size: 13px;
            margin-bottom: 10px;
            transition: 0.3s;
        }

        .footer-column a:hover {
            color: #d2a679;
            padding-left: 4px;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 25px;
            font-size: 12px;
            color: #777;
        }

        /* Scroll Reveal Animation Styles */
        .reveal {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 1s cubic-bezier(0.16, 1, 0.3, 1), transform 1s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: opacity, transform;
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 1100px) {
            .category-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 800px) {
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
            .stat-box:nth-child(2) {
                border-right: none;
            }
            .category-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .benefits-grid {
                grid-template-columns: 1fr;
            }
            .footer-grid {
                grid-template-columns: 1fr 1fr;
            }
            .brand-intro-banner {
                padding: 40px;
            }
        }

        @media (max-width: 550px) {
            .hero-section {
                min-height: 580px;
            }
            .hero-section p {
                font-size: 14px;
            }
            .stats-container {
                grid-template-columns: 1fr;
            }
            .stat-box {
                border-right: none;
                border-bottom: 1px solid #eee4da;
            }
            .stat-box:last-child {
                border-bottom: none;
            }
            .category-grid {
                grid-template-columns: 1fr;
            }
            .features-grid {
                grid-template-columns: 1fr;
            }
            .footer-grid {
                grid-template-columns: 1fr;
            }
            .contact-strip {
                gap: 30px;
            }
            .brand-intro-banner {
                min-height: 280px;
                padding: 30px;
            }
            .intro-banner-content h2 {
                font-size: 30px;
            }
            .section-heading h2 {
                font-size: 30px;
            }
        }
    </style>
</head>

<body>

<?php include 'navbar.php'; ?>

<section class="hero-section">
    <div class="hero-content">
        <div class="hero-small-title">
            Beauty • Care • Confidence
        </div>
        <h1>
            Discover Your
            <span>Natural Glow</span>
        </h1>
        <p>
            Discover premium body care, skincare, hair care,
            makeup and fragrance products carefully selected
            to help you look and feel your best.
        </p>
        <a href="deals.php" class="hero-btn">
            Explore Products & Deals
        </a>
    </div>
</section>

<section class="stats-section">
    <div class="stats-container">
        <div class="stat-box">
            <div class="stat-number">
                10,000+
            </div>
            <div class="stat-label">
                Happy Customers
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-number">
                100%
            </div>
            <div class="stat-label">
                Quality Products
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-number">
                50+
            </div>
            <div class="stat-label">
                Exclusive Products
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-number">
                4.9 ★
            </div>
            <div class="stat-label">
                Customer Rating
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="section-heading">
        <span>
            Explore Our Collection
        </span>
        <h2>
            Shop By Category
        </h2>
        <p>
            Find everything you need for your daily
            beauty and self-care routine.
        </p>
    </div>

    <div class="category-grid">
        <div class="category-card">
            <div class="category-image">
                <img src="images/face-care.jpg" alt="Face Care">
            </div>
            <div class="category-content">
                <h3>
                    Face Care
                </h3>
                <p>
                    Nourish and refresh your skin with
                    our carefully selected face care products.
                </p>
                <a href="facecare.php" class="category-btn">
                    View Products
                </a>
            </div>
        </div>

        <div class="category-card">
            <div class="category-image">
                <img src="images/hair-care.jpg" alt="Hair Care">
            </div>
            <div class="category-content">
                <h3>
                    Hair Care
                </h3>
                <p>
                    Restore shine and strength with
                    shampoos, oils and conditioners.
                </p>
                <a href="haircare.php" class="category-btn">
                    View Products
                </a>
            </div>
        </div>

        <div class="category-card">
            <div class="category-image">
                <img src="images/makeup.jpg" alt="Makeup">
            </div>
            <div class="category-content">
                <h3>
                    Makeup
                </h3>
                <p>
                    Enhance your natural beauty with
                    beautiful everyday makeup essentials.
                </p>
                <a href="makeup.php" class="category-btn">
                    View Products
                </a>
            </div>
        </div>

        <div class="category-card">
            <div class="category-image">
                <img src="images/body-care.jpg" alt="Body Care">
            </div>
            <div class="category-content">
                <h3>
                    Body Care
                </h3>
                <p>
                    Keep your skin soft, smooth and
                    moisturized throughout the day.
                </p>
                <a href="bodycare.php" class="category-btn">
                    View Products
                </a>
            </div>
        </div>

        <div class="category-card">
            <div class="category-image">
                <img src="images/fragrance.jpg" alt="Fragrance">
            </div>
            <div class="category-content">
                <h3>
                    Fragrance
                </h3>
                <p>
                    Discover elegant perfumes and body
                    mists for every occasion.
                </p>
                <a href="fragrance.php" class="category-btn">
                    View Products
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Brand Intro Banner -->
<section class="brand-intro-banner">
    <div class="intro-banner-content">
        <span>
            Pure Care & Quality
        </span>
        <h2>
            Pure Care for Flawless Skin
        </h2>
        <p>
            Elevate your daily routine with 100% authentic, skin-friendly products designed for your ultimate confidence, natural elegance, and everyday self-care.
        </p>
    </div>
</section>

<section class="why-section">
    <div class="why-container">
        <div class="section-heading">
            <span>
                The Glow Care Difference
            </span>
            <h2>
                Why Choose Glow Care?
            </h2>
            <p>
                We make your beauty shopping experience
                simple, safe and enjoyable.
            </p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    🚚
                </div>
                <h3>
                    Fast Delivery
                </h3>
                <p>
                    Island-wide fast and safe delivery
                    right to your doorstep.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    ✨
                </div>
                <h3>
                    Quality Products
                </h3>
                <p>
                    Carefully selected beauty products
                    for your everyday needs.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    🛡️
                </div>
                <h3>
                    Secure Checkout
                </h3>
                <p>
                    Safe and secure payment experience
                    for every customer.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    🌿
                </div>
                <h3>
                    Beauty & Care
                </h3>
                <p>
                    Products selected with your beauty
                    and self-care routine in mind.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="section-heading">
        <span>
            Feel The Difference
        </span>
        <h2>
            What You Gain With Glow Care
        </h2>
    </div>

    <div class="benefits-grid">
        <div class="benefit-card">
            <h3>
                ✨ Radiant Looking Skin
            </h3>
            <p>
                Products designed to support
                a fresh, healthy-looking and naturally
                radiant appearance.
            </p>
        </div>

        <div class="benefit-card">
            <h3>
                💧 Daily Hydration
            </h3>
            <p>
                Keep your skin feeling soft, smooth
                and moisturized with suitable daily
                care products.
            </p>
        </div>

        <div class="benefit-card">
            <h3>
                🌿 Everyday Self-Care
            </h3>
            <p>
                Create a simple beauty routine with
                products selected for your daily
                self-care needs.
            </p>
        </div>
    </div>
</section>

<section class="contact-strip">
    <div class="contact-item">
        <div class="label">
            Call Us
        </div>
        <div class="value">
            011 555 8887
        </div>
    </div>

    <div class="contact-item">
        <div class="label">
            Visit Us
        </div>
        <div class="value">
            No 124/A, Ribbon Road, Colombo
        </div>
    </div>

    <div class="contact-item">
        <div class="label">
            Email
        </div>
        <div class="value">
            info@glowcare.com
        </div>
    </div>
</section>

<footer>
    <div class="footer-container">
        <div class="footer-grid">
            <div class="footer-brand">
                <h2>
                    Glow <span>Care</span>
                </h2>
                <p>
                    Your destination for quality beauty,
                    skincare, hair care, body care,
                    makeup and fragrance products.
                </p>
            </div>

            <div class="footer-column">
                <h3>
                    Products
                </h3>
                <a href="bodycare.php">
                    Body Care
                </a>
                <a href="facecare.php">
                    Face Care
                </a>
                <a href="haircare.php">
                    Hair Care
                </a>
                <a href="makeup.php">
                    Makeup
                </a>
                <a href="fragrance.php">
                    Fragrance
                </a>
            </div>
        </div>

        <div class="footer-bottom">
            &copy; <?php echo date("Y"); ?>
            Glow Care Pvt Ltd.
            All rights reserved.
        </div>
    </div>
</footer>

<script src="auth.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.15
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        }, observerOptions);

        const targetElements = document.querySelectorAll('.section-heading, .category-card, .brand-intro-banner, .feature-card, .benefit-card, .stats-container');
        
        targetElements.forEach(el => {
            el.classList.add('reveal');
            observer.observe(el);
        });
    });
</script>

</body>
</html>