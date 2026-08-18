<?php
include("config.php");
session_start();

// Add to Cart logic
if (isset($_POST['add_to_cart'])) {
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
    <link rel="stylesheet" href="style.css">
    <style>
        /* Scroll වෙද්දී ක්‍රියාත්මක වන මූලික CSS Styles */
        .reveal-section {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
        }
        
        /* JavaScript මඟින් active class එක එකතු වූ පසු animation එක ක්‍රියාත්මක වේ */
        .reveal-section.active {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
    <script>
    // පාරිභෝගිකයා ලොග් වී ඇත්දැයි පරීක්ෂා කිරීම
    const isLoggedIn = localStorage.getItem("glowCareLoggedIn");
    
    if (isLoggedIn !== "true") {
        alert("Please login first to view exclusive deals and bundles!");
        window.location.href = "login.php"; 
    }
    </script>
</head>
<body>
<?php include 'navbar.php'; ?>
    

    <?php if (isset($success_msg)): ?>
        <div style="background: #d4edda; color: #155724; padding: 10px; text-align: center; font-weight: bold;">
            <?php echo $success_msg; ?> <a href="dashboard.php" style="color: #155724; text-decoration: underline;">View Cart</a>
        </div>
    <?php endif; ?>

    <!-- ප්‍රධාන ශීර්ෂය -->
    <div style="max-width: 1200px; margin: auto; padding: 40px 20px 20px 20px;" class="reveal-section">
        <h2 style="font-size: 32px; color: #333; margin-bottom: 10px;">Exclusive Deals, Bulk Offers & Bundles</h2>
        <div style="width: 80px; height: 3px; background: #ff4b72; margin-bottom: 15px;"></div>
        <p style="color: #666; font-size: 16px;">Explore our special category sections below with built-in size limits, mandatory discounts, and routine bundles.</p>
    </div>

    <div style="max-width: 1200px; margin: auto; padding: 0 20px 40px 20px;">

        <!-- SECTION 1: Large Volume Items -->
        <div class="reveal-section" style="margin-bottom: 40px;">
            <h3 style="color: #ff4b72; margin-bottom: 15px; border-bottom: 2px solid #ffd1dc; padding-bottom: 5px;" id="body-care">📦 Large Volume Items (> 800ml - Quantity Limit Applied)</h3>
            <div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <?php
                $sql1 = "SELECT * FROM deals WHERE section_type = 'large_volume'";
                $result1 = mysqli_query($conn, $sql1);
                while ($row = mysqli_fetch_assoc($result1)) {
                ?>
                    <div class="product-card" style="background: #fff; border: 1px solid #ffd1dc; border-radius: 10px; padding: 15px; text-align: center; box-shadow: 0 4px 8px rgba(0,0,0,0.05);">
                        <img src="<?php echo $row['image_url']; ?>" alt="<?php echo $row['name']; ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">
                        <h3><?php echo $row['name']; ?></h3>
                        <p><strong>Size:</strong> <?php echo $row['size']; ?> (Max <?php echo $row['max_qty']; ?> units)</p>
                        <p style="color: #ff4b72; font-size: 18px; font-weight: bold;">Rs. <?php echo number_format($row['price']); ?></p>
                        <form method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="product_name" value="<?php echo $row['name']; ?>">
                            <input type="hidden" name="product_price" value="<?php echo $row['price']; ?>">
                            <input type="hidden" name="product_size" value="<?php echo $row['size']; ?>">
                            <div style="margin: 10px 0; text-align: left;">
                                <label style="font-size: 13px; color: #555;">Quantity (Max <?php echo $row['max_qty']; ?>):</label>
                                <input type="number" name="qty" value="1" min="1" max="<?php echo $row['max_qty']; ?>" style="width: 60px; padding: 5px;">
                            </div>
                            <button type="submit" name="add_to_cart" class="btn" style="width: 100%; background: #ff4b72; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">Add to Cart</button>
                        </form>
                    </div>
                <?php } ?>
            </div>
        </div>

        <!-- SECTION 2: Heavy Weight Items -->
        <div class="reveal-section" style="margin-bottom: 40px;">
            <h3 style="color: #ff4b72; margin-bottom: 15px; border-bottom: 2px solid #ffd1dc; padding-bottom: 5px;" id="skin-care">🏷️ Heavy Weight Items (> 500g - Mandatory Discounts)</h3>
            <div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <?php
                $sql2 = "SELECT * FROM deals WHERE section_type = 'heavy_weight'";
                $result2 = mysqli_query($conn, $sql2);
                while ($row = mysqli_fetch_assoc($result2)) {
                ?>
                    <div class="product-card" style="background: #fff; border: 1px solid #ffd1dc; border-radius: 10px; padding: 15px; text-align: center; box-shadow: 0 4px 8px rgba(0,0,0,0.05);">
                        <img src="<?php echo $row['image_url']; ?>" alt="<?php echo $row['name']; ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">
                        <h3><?php echo $row['name']; ?></h3>
                        <p><strong>Weight:</strong> <?php echo $row['size']; ?> (Discounted)</p>
                        <?php if (!empty($row['old_price'])): ?>
                            <p style="text-decoration: line-through; color: #999; margin: 0;">Rs. <?php echo number_format($row['old_price']); ?></p>
                        <?php endif; ?>
                        <p style="color: #ff4b72; font-size: 18px; font-weight: bold;">Rs. <?php echo number_format($row['price']); ?></p>
                        <form method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="product_name" value="<?php echo $row['name']; ?>">
                            <input type="hidden" name="product_price" value="<?php echo $row['price']; ?>">
                            <input type="hidden" name="product_size" value="<?php echo $row['size']; ?>">
                            <button type="submit" name="add_to_cart" class="btn" style="width: 100%; background: #ff4b72; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer; margin-top: 10px;">Add to Cart</button>
                        </form>
                    </div>
                <?php } ?>
            </div>
        </div>

        <!-- SECTION 3: Special Routine Bundles -->
        <div class="reveal-section">
            <h3 style="color: #ff4b72; margin-bottom: 15px; border-bottom: 2px solid #ffd1dc; padding-bottom: 5px;" id="hair-care">✨ Special Routine Bundles (Face, Makeup & Hair Kits)</h3>
            
            <div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <?php
                $sql3 = "SELECT * FROM deals WHERE section_type = 'bundle'";
                $result3 = mysqli_query($conn, $sql3);
                
                $counter = 0; 
                
                while ($row = mysqli_fetch_assoc($result3)) {
                    
                    if ($counter == 0) {
                        echo '<h4 style="grid-column: 1 / -1; color: #333; margin: 15px 0 5px 0; border-left: 4px solid #ff4b72; padding-left: 8px;" id="skin-care-bundles">💄 Face Care Bundles</h4>';
                    }
                    elseif ($counter == 3) {
                        echo '<h4 style="grid-column: 1 / -1; color: #333; margin: 25px 0 5px 0; border-left: 4px solid #ff4b72; padding-left: 8px;" id="makeup">💋 Makeup Bundles</h4>';
                    }
                    elseif ($counter == 6) {
                        echo '<h4 style="grid-column: 1 / -1; color: #333; margin: 25px 0 5px 0; border-left: 4px solid #ff4b72; padding-left: 8px;">💆‍♀️ Hair Care Bundles</h4>';
                    }
                ?>
                    <div class="product-card" style="background: #fff; border: 1px solid #ffd1dc; border-radius: 10px; padding: 15px; text-align: center; box-shadow: 0 4px 8px rgba(0,0,0,0.05);">
                        <img src="<?php echo $row['image_url']; ?>" alt="<?php echo $row['name']; ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">
                        <h3><?php echo $row['name']; ?></h3>
                        <?php if (!empty($row['description'])): ?>
                            <p style="font-size: 13px; color: #555; margin-bottom: 8px;"><?php echo $row['description']; ?></p>
                        <?php endif; ?>
                        <p style="color: #ff4b72; font-size: 18px; font-weight: bold;">Rs. <?php echo number_format($row['price']); ?></p>
                        <form method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="product_name" value="<?php echo $row['name']; ?>">
                            <input type="hidden" name="product_price" value="<?php echo $row['price']; ?>">
                            <input type="hidden" name="product_size" value="<?php echo $row['size']; ?>">
                            <button type="submit" name="add_to_cart" class="btn" style="width: 100%; background: #ff4b72; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer; margin-top: 10px;">Add to Cart</button>
                        </form>
                    </div>
                <?php 
                    $counter++; 
                } 
                ?>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <footer style="text-align: center; padding: 25px; background: #333; color: white; margin-top: 50px;">
        <p>&copy; <?php echo date("Y"); ?> Glow Care. All rights reserved.</p>
    </footer>

    <!-- Scroll වෙද්දී Animation එක ක්‍රියාත්මක කිරීමට JavaScript කේතය -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const observerOptions = {
                root: null,
                rootMargin: '0px',
                threshold: 0.1 // screen එකට 10%ක් ආපු ගමන් ක්‍රියාත්මක වේ
            };

            const observer = new IntersectionObserver(function(entries, observer) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                        // එක වතාවක් animate වූ පසු observer එක ඉවත් කර ගත හැක (optional)
                        // observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            const sections = document.querySelectorAll('.reveal-section');
            sections.forEach(section => {
                observer.observe(section);
            });
        });
    </script>

</body>
</html>  