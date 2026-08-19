<?php
require_once 'connect.php';

if (isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['email'])) {
        header("Location: login.php");
        exit();
    }
    
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_size = $_POST['product_size'];
    $product_id = $_POST['product_id'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $_SESSION['cart'][] = [
        'id' => $product_id,
        'name' => $product_name,
        'price' => $product_price,
        'size' => $product_size
    ];

    $success_msg = "Product successfully added to your cart!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Glow Care - Deals & Bundles</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #fbf7f4; color: #3b2c25; font-family: "Segoe UI", Arial, sans-serif; line-height: 1.6; display: flex; flex-direction: column; min-height: 100vh; }
        a { text-decoration: none; }
        .product-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        @media (max-width: 900px) { .product-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 600px) { .product-grid { grid-template-columns: 1fr; } }
        .product-card { background: #fff; border: 1px solid #e8ddd2; border-radius: 10px; padding: 18px; text-align: center; box-shadow: 0 4px 15px rgba(71, 50, 35, 0.05); display: flex; flex-direction: column; justify-content: space-between; }
        .reveal-section { opacity: 0; transform: translateY(40px); transition: opacity 0.8s ease-out, transform 0.8s ease-out; }
        .reveal-section.active { opacity: 1; transform: translateY(0); }
        .btn { width: 100%; padding: 11px; background: #8c6239; color: white; border: none; border-radius: 6px; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; cursor: pointer; transition: background 0.3s; }
        .btn:hover { background: #6f4c2c; }
    </style>
</head>
<body>

    <!-- මෙතැනින් Navigation bar එක ඇතුළත් වේ -->
    <?php include("navbar.php"); ?>
    
    <?php if (isset($success_msg)): ?>
        <div style="background: #e6f4ea; color: #137333; padding: 12px; text-align: center; font-weight: 600; border-bottom: 1px solid #ceead6;">
            <?php echo $success_msg; ?> <a href="dashboard.php" style="color: #137333; text-decoration: underline; margin-left: 5px;">View Cart</a>
        </div>
    <?php endif; ?>

    <div style="max-width: 1200px; margin: auto; padding: 40px 20px 20px 20px;" class="reveal-section">
        <h2 style="font-family: Georgia, serif; font-size: 30px; color: #49362b; margin-bottom: 8px; font-weight: 400;">Exclusive Deals, Bulk Offers & Bundles</h2>
        <div style="width: 70px; height: 3px; background: #8c6239; margin-bottom: 15px;"></div>
    </div>

    <div style="max-width: 1200px; margin: auto; padding: 0 20px 40px 20px;">

        <div class="reveal-section" style="margin-bottom: 40px;">
            <h3 style="color: #49362b; font-family: Georgia, serif; margin-bottom: 20px; border-bottom: 2px solid #e8ddd2; padding-bottom: 8px;">📦 Large Volume Items</h3>
            <div class="product-grid">
                <?php
                $sql1 = "SELECT * FROM deals WHERE section_type = 'large_volume'";
                $result1 = mysqli_query($conn, $sql1);
                if($result1) {
                    while ($row = mysqli_fetch_assoc($result1)) {
                ?>
                    <div class="product-card">
                        <div>
                            <img src="images/<?php echo $row['image_url']; ?>" alt="<?php echo $row['name']; ?>" style="width: 100%; height: 180px; object-fit: cover; border-radius: 8px; margin-bottom: 12px;">
                            <h3 style="font-size: 17px; color: #3b2c25; margin-bottom: 8px;"><?php echo $row['name']; ?></h3>
                            <p style="font-size: 13px; color: #75675d; margin-bottom: 8px;"><strong>Size:</strong> <?php echo $row['size']; ?></p>
                            <p style="color: #8c6239; font-size: 17px; font-weight: bold; margin-bottom: 12px;">Rs. <?php echo number_format($row['price']); ?></p>
                        </div>
                        <form method="POST" class="cart-form">
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="product_name" value="<?php echo $row['name']; ?>">
                            <input type="hidden" name="product_price" value="<?php echo $row['price']; ?>">
                            <input type="hidden" name="product_size" value="<?php echo $row['size']; ?>">
                            <button type="submit" name="add_to_cart" class="btn">Add to Cart</button>
                        </form>
                    </div>
                <?php } } ?>
            </div>
        </div>

        <div class="reveal-section" style="margin-bottom: 40px;">
            <h3 style="color: #49362b; font-family: Georgia, serif; margin-bottom: 20px; border-bottom: 2px solid #e8ddd2; padding-bottom: 8px;">🏷️ Heavy Weight Items</h3>
            <div class="product-grid">
                <?php
                $sql2 = "SELECT * FROM deals WHERE section_type = 'heavy_weight'";
                $result2 = mysqli_query($conn, $sql2);
                if($result2) {
                    while ($row = mysqli_fetch_assoc($result2)) {
                ?>
                    <div class="product-card">
                        <div>
                            <img src="images/<?php echo $row['image_url']; ?>" alt="<?php echo $row['name']; ?>" style="width: 100%; height: 180px; object-fit: cover; border-radius: 8px; margin-bottom: 12px;">
                            <h3 style="font-size: 17px; color: #3b2c25; margin-bottom: 8px;"><?php echo $row['name']; ?></h3>
                            <p style="font-size: 13px; color: #75675d; margin-bottom: 6px;"><strong>Weight:</strong> <?php echo $row['size']; ?></p>
                            <?php if (!empty($row['old_price'])): ?>
                                <p style="text-decoration: line-through; color: #a89f97; font-size: 13px; margin: 0;">Rs. <?php echo number_format($row['old_price']); ?></p>
                            <?php endif; ?>
                            <p style="color: #8c6239; font-size: 17px; font-weight: bold; margin: 8px 0 12px 0;">Rs. <?php echo number_format($row['price']); ?></p>
                        </div>
                        <form method="POST" class="cart-form">
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="product_name" value="<?php echo $row['name']; ?>">
                            <input type="hidden" name="product_price" value="<?php echo $row['price']; ?>">
                            <input type="hidden" name="product_size" value="<?php echo $row['size']; ?>">
                            <button type="submit" name="add_to_cart" class="btn">Add to Cart</button>
                        </form>
                    </div>
                <?php } } ?>
            </div>
        </div>

        <div class="reveal-section">
            <h3 style="color: #49362b; font-family: Georgia, serif; margin-bottom: 20px; border-bottom: 2px solid #e8ddd2; padding-bottom: 8px;">✨ Special Routine Bundles</h3>
            <div class="product-grid">
                <?php
                $sql3 = "SELECT * FROM deals WHERE section_type = 'bundle'";
                $result3 = mysqli_query($conn, $sql3);
                if($result3) {
                    while ($row = mysqli_fetch_assoc($result3)) {
                ?>
                    <div class="product-card">
                        <div>
                            <img src="images/<?php echo $row['image_url']; ?>" alt="<?php echo $row['name']; ?>" style="width: 100%; height: 180px; object-fit: cover; border-radius: 8px; margin-bottom: 12px;">
                            <h3 style="font-size: 17px; color: #3b2c25; margin-bottom: 6px;"><?php echo $row['name']; ?></h3>
                            <p style="color: #8c6239; font-size: 17px; font-weight: bold; margin-bottom: 12px;">Rs. <?php echo number_format($row['price']); ?></p>
                        </div>
                        <form method="POST" class="cart-form">
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="product_name" value="<?php echo $row['name']; ?>">
                            <input type="hidden" name="product_price" value="<?php echo $row['price']; ?>">
                            <input type="hidden" name="product_size" value="<?php echo $row['size']; ?>">
                            <button type="submit" name="add_to_cart" class="btn">Add to Cart</button>
                        </form>
                    </div>
                <?php } } ?>
            </div>
        </div>
    </div>

    <footer style="text-align: center; padding: 25px; background: #211915; color: #aaa; margin-top: 50px; font-size: 13px;">
        <p>&copy; <?php echo date("Y"); ?> Glow Care Pvt Ltd. All rights reserved.</p>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => { if (entry.isIntersecting) entry.target.classList.add('active'); });
            }, { threshold: 0.1 });
            document.querySelectorAll('.reveal-section').forEach(s => observer.observe(s));

            const cartForms = document.querySelectorAll('.cart-form');
            cartForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const isLoggedIn = localStorage.getItem("glowCareLoggedIn");
                    if (isLoggedIn !== "true") {
                        e.preventDefault();
                        alert("You must login or register to add items to your cart!");
                        window.location.href = "login.php";
                    }
                });
            });
        });
    </script>
</body>
</html>