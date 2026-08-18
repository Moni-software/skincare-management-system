<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Glow Care - Home</title>
    <link rel="stylesheet" href="style.css?v=1.3">
</head>
<body>

    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>

    <!-- Hero Banner Section -->
    <section class="hero-section animate-fade">
        <h1>Welcome to Glow Care</h1>
        <p>Discover your natural beauty with our exclusive body care, skincare, hair care, makeup, and fragrance collections.</p>
        <a href="deals.php" class="btn">Explore All Products & Deals</a>
    </section>

    <!-- Statistics Section -->
    <section class="stats-row container">
        <div class="stat-box">
            <div class="num">10,000+</div>
            <div class="lbl">Happy Customers</div>
        </div>
        <div class="stat-box">
            <div class="num">100%</div>
            <div class="lbl">Natural Ingredients</div>
        </div>
        <div class="stat-box">
            <div class="num">50+</div>
            <div class="lbl">Exclusive Bundles</div>
        </div>
        <div class="stat-box">
            <div class="num">4.9 ★</div>
            <div class="lbl">Customer Rating</div>
        </div>
    </section>

    <!-- Featured Categories Section -->
    <section class="container category-section">
        <h2 class="sub-category-title">Shop By Categories</h2>
        <div class="category-grid">
            
            <div class="card">
                <img src="images/body-care.jpg" alt="Body Care">
                <h3>Body Care</h3>
                <p>Keep your skin soft, smooth, and deeply moisturized with our luxurious body lotions and washes.</p>
                <a href="bodycare.php" class="btn">View Body Products</a>
            </div>

            <div class="card">
                <img src="images/face-care.jpg" alt="Face Care">
                <h3>Face Care</h3>
                <p>Nourish your skin with our best-selling face washes, exfoliating scrubs, and rejuvenating toners.</p>
                <a href="facecare.php" class="btn">View Face Products</a>
            </div>

            <div class="card">
                <img src="images/hair-care.jpg" alt="Hair Care">
                <h3>Hair Care</h3>
                <p>Restore strength and shine with our herbal shampoos, growth oils, and smooth conditioners.</p>
                <a href="haircare.php" class="btn">View Hair Products</a>
            </div>

            <div class="card">
                <img src="images/makeup.jpg" alt="Makeup">
                <h3>Makeup</h3>
                <p>Elevate your daily glow with vibrant lipsticks, matching foundations, and gorgeous eye shadow palettes.</p>
                <a href="makeup.php" class="btn">View Makeup Products</a>
            </div>

            <div class="card">
                <img src="images/fragrance.jpg" alt="Fragrance">
                <h3>Fragrance</h3>
                <p>Leave a lasting impression with our exquisite collection of long-lasting perfumes and body mists.</p>
                <a href="fragrance.php" class="btn">View Fragrance Products</a>
            </div>

        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="container category-section">
        <h2 class="sub-category-title">Why Choose Glow Care?</h2>
        <div class="category-grid">
            <div class="card">
                <h3 class="product-meta">🚚 Fast Delivery</h3>
                <p>Island-wide fast and safe delivery right to your doorstep.</p>
            </div>
            <div class="card">
                <h3 class="product-meta">✨ 100% Original</h3>
                <p>Genuine and authentic beauty products sourced directly.</p>
            </div>
            <div class="card">
                <h3 class="product-meta">🛡️ Secure Checkout</h3>
                <p>Safe and secure payment methods for your peace of mind.</p>
            </div>
            <div class="card">
                <h3 class="product-meta">🌿 Derm-Tested</h3>
                <p>Safe formulas tested and approved for all skin types.</p>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="container category-section">
        <h2 class="sub-category-title">What You Gain With Glow Care</h2>
        <div class="category-grid">
            <div class="card">
                <h3 class="product-meta">✨ Radiant & Glowing Skin</h3>
                <p>Deeply nourishes your skin cells to bring out a natural, healthy, and long-lasting inner glow.</p>
            </div>
            <div class="card">
                <h3 class="product-meta">💧 All-Day Moisture & Hydration</h3>
                <p>Locks in essential hydration to keep your body and face soft, smooth, and supple throughout the day.</p>
            </div>
            <div class="card">
                <h3 class="product-meta">🌿 Chemical-Free & Safe</h3>
                <p>Made with pure, skin-friendly ingredients that protect against damage without any harsh side effects.</p>
            </div>
        </div>
    </section>

    <!-- Contact Info Section -->
    <section class="contact-strip">
        <div class="contact-item">
            <div class="label">Phone</div>
            <div class="value">0115558887</div>
        </div>
        <div class="contact-item">
            <div class="label">Address</div>
            <div class="value">No 124/A/ Ribbon Road, Colombo</div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Glow Care. All rights reserved.</p>
    </footer>

    <!-- Scripts -->
    <script src="auth.js"></script>
</body>
</html>