<?php
include("connect.php");
session_start();

// Define login status variable for PHP & HTML
$isLoggedIn = isset($_SESSION['customer_id']) ? 'true' : 'false';
$error_msg = '';
$success_msg = '';

// Handle Add to Cart Form Submission
if (isset($_POST['add_to_cart'])) {

    // 1. BLOCK USER IF NOT LOGGED IN
    if (!isset($_SESSION['customer_id'])) {
        $error_msg = "Please log in first to add items to your cart!";
    } else {
        // 2. PROCEED ONLY IF LOGGED IN
        $customer_id   = $_SESSION['customer_id'];
        $product_name  = trim($_POST['product_name']);
        $product_price = floatval($_POST['product_price']);
        $product_size  = trim($_POST['product_size']);
        $product_id    = intval($_POST['product_id']);
        $product_qty   = isset($_POST['qty']) ? max(1, intval($_POST['qty'])) : 1;

        // Get product image from database if available
        $img_query = $conn->prepare("SELECT image_url FROM deals WHERE id = ?");
        $img_query->bind_param("i", $product_id);
        $img_query->execute();
        $img_res = $img_query->get_result();
        $product_image = ($img_row = $img_res->fetch_assoc()) ? $img_row['image_url'] : '';
        $img_query->close();

        // Check if item already exists in database cart for this customer
        $chk = $conn->prepare("SELECT cart_id, quantity FROM cart WHERE customer_id = ? AND product_name = ?");
        $chk->bind_param("is", $customer_id, $product_name);
        $chk->execute();
        $res = $chk->get_result();

        if ($res->num_rows > 0) {
            // Update quantity if item exists
            $row = $res->fetch_assoc();
            $new_qty = $row['quantity'] + $product_qty;
            $up = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
            $up->bind_param("ii", $new_qty, $row['cart_id']);
            $up->execute();
            $up->close();
        } else {
            // Insert new cart row
            $ins = $conn->prepare("INSERT INTO cart (customer_id, product_name, product_price, quantity, product_image) VALUES (?, ?, ?, ?, ?)");
            $ins->bind_param("isdis", $customer_id, $product_name, $product_price, $product_qty, $product_image);
            $ins->execute();
            $ins->close();
        }
        $chk->close();

        $success_msg = "Product successfully added to your cart!";
    }
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
        .reveal-section {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
        }
        
        .reveal-section.active {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
    <script>
    const isLoggedIn = localStorage.getItem("glowCareLoggedIn");
    
    if (isLoggedIn !== "true") {
        alert("Please login first to view exclusive deals and bundles!");
        window.location.href = "login.php"; 
    }
    </script>
</head>
<body data-logged-in="<?php echo $isLoggedIn; ?>">

    <?php include 'navbar.php'; ?>

    <!-- Error Banner for Unauthenticated Users -->
    <?php if (!empty($error_msg)): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 12px; text-align: center; font-weight: bold; border-bottom: 1px solid #f5c6cb;">
            ⚠️ <?php echo $error_msg; ?> 
            <a href="login.php" style="color: #721c24; text-decoration: underline; margin-left: 10px;">Log In Now</a>
        </div>
    <?php endif; ?>

    <!-- Success Banner -->
    <?php if (!empty($success_msg)): ?>
        <div style="background: #d4edda; color: #155724; padding: 12px; text-align: center; font-weight: bold; border-bottom: 1px solid #c3e6cb;">
            <?php echo $success_msg; ?> 
            <a href="customer.php?tab=cart" style="color: #155724; text-decoration: underline; margin-left: 10px;">View Cart</a>
        </div>
    <?php endif; ?>

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
                if ($result1) {
                    while ($row = mysqli_fetch_assoc($result1)) {
                    ?>
                        <div class="product-card" style="background: #fff; border: 1px solid #ffd1dc; border-radius: 10px; padding: 15px; text-align: center; box-shadow: 0 4px 8px rgba(0,0,0,0.05);">
                            <img src="images/<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">
                            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                            <p><strong>Size:</strong> <?php echo htmlspecialchars($row['size']); ?> (Max <?php echo $row['max_qty']; ?> units)</p>
                            <p style="color: #ff4b72; font-size: 18px; font-weight: bold;">Rs. <?php echo number_format($row['price']); ?></p>
                            <form method="POST">
                                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($row['name']); ?>">
                                <input type="hidden" name="product_price" value="<?php echo $row['price']; ?>">
                                <input type="hidden" name="product_size" value="<?php echo htmlspecialchars($row['size']); ?>">
                                <div style="margin: 10px 0; text-align: left;">
                                    <label style="font-size: 13px; color: #555;">Quantity (Max <?php echo $row['max_qty']; ?>):</label>
                                    <input type="number" name="qty" value="1" min="1" max="<?php echo $row['max_qty']; ?>" style="width: 60px; padding: 5px;">
                                </div>
                                <button type="submit" name="add_to_cart" class="btn" style="width: 100%; background: #ff4b72; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">Add to Cart</button>
                            </form>
                        </div>
                    <?php 
                    }
                } 
                ?>
            </div>
        </div>

        <!-- SECTION 2: Heavy Weight Items -->
        <div class="reveal-section" style="margin-bottom: 40px;">
            <h3 style="color: #ff4b72; margin-bottom: 15px; border-bottom: 2px solid #ffd1dc; padding-bottom: 5px;" id="skin-care">🏷️ Heavy Weight Items (> 500g - Mandatory Discounts)</h3>
            <div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <?php
                $sql2 = "SELECT * FROM deals WHERE section_type = 'heavy_weight'";
                $result2 = mysqli_query($conn, $sql2);
                if ($result2) {
                    while ($row = mysqli_fetch_assoc($result2)) {
                    ?>
                        <div class="product-card" style="background: #fff; border: 1px solid #ffd1dc; border-radius: 10px; padding: 15px; text-align: center; box-shadow: 0 4px 8px rgba(0,0,0,0.05);">
                            <img src="images/<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">
                            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                            <p><strong>Weight:</strong> <?php echo htmlspecialchars($row['size']); ?> (Discounted)</p>
                            <?php if (!empty($row['old_price'])): ?>
                                <p style="text-decoration: line-through; color: #999; margin: 0;">Rs. <?php echo number_format($row['old_price']); ?></p>
                            <?php endif; ?>
                            <p style="color: #ff4b72; font-size: 18px; font-weight: bold;">Rs. <?php echo number_format($row['price']); ?></p>
                            <form method="POST">
                                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($row['name']); ?>">
                                <input type="hidden" name="product_price" value="<?php echo $row['price']; ?>">
                                <input type="hidden" name="product_size" value="<?php echo htmlspecialchars($row['size']); ?>">
                                <button type="submit" name="add_to_cart" class="btn" style="width: 100%; background: #ff4b72; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer; margin-top: 10px;">Add to Cart</button>
                            </form>
                        </div>
                    <?php 
                    }
                } 
                ?>
            </div>
        </div>

        <!-- SECTION 3: Special Routine Bundles -->
        <div class="reveal-section">
            <h3 style="color: #ff4b72; margin-bottom: 15px; border-bottom: 2px solid #ffd1dc; padding-bottom: 5px;" id="hair-care">✨ Special Routine Bundles (Face, Makeup & Hair Kits)</h3>
            
            <div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <?php
                $sql3 = "SELECT * FROM deals WHERE section_type = 'bundle'";
                $result3 = mysqli_query($conn, $sql3);
                
                if ($result3) {
                    $counter = 0; 
                    while ($row = mysqli_fetch_assoc($result3)) {
                        if ($counter == 0) {
                            echo '<h4 style="grid-column: 1 / -1; color: #333; margin: 15px 0 5px 0; border-left: 4px solid #ff4b72; padding-left: 8px;" id="skin-care-bundles">💄 Face Care Bundles</h4>';
                        } elseif ($counter == 3) {
                            echo '<h4 style="grid-column: 1 / -1; color: #333; margin: 25px 0 5px 0; border-left: 4px solid #ff4b72; padding-left: 8px;" id="makeup">💋 Makeup Bundles</h4>';
                        } elseif ($counter == 6) {
                            echo '<h4 style="grid-column: 1 / -1; color: #333; margin: 25px 0 5px 0; border-left: 4px solid #ff4b72; padding-left: 8px;">💆‍♀️ Hair Care Bundles</h4>';
                        }
                    ?>
                        <div class="product-card" style="background: #fff; border: 1px solid #ffd1dc; border-radius: 10px; padding: 15px; text-align: center; box-shadow: 0 4px 8px rgba(0,0,0,0.05);">
                            <img src="images/<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">
                            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                            <?php if (!empty($row['description'])): ?>
                                <p style="font-size: 13px; color: #555; margin-bottom: 8px;"><?php echo htmlspecialchars($row['description']); ?></p>
                            <?php endif; ?>
                            <p style="color: #ff4b72; font-size: 18px; font-weight: bold;">Rs. <?php echo number_format($row['price']); ?></p>
                            <form method="POST">
                                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($row['name']); ?>">
                                <input type="hidden" name="product_price" value="<?php echo $row['price']; ?>">
                                <input type="hidden" name="product_size" value="<?php echo htmlspecialchars($row['size']); ?>">
                                <button type="submit" name="add_to_cart" class="btn" style="width: 100%; background: #ff4b72; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer; margin-top: 10px;">Add to Cart</button>
                            </form>
                        </div>
                    <?php 
                        $counter++; 
                    }
                } 
                ?>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <footer style="text-align: center; padding: 25px; background: #333; color: white; margin-top: 50px;">
        <p>&copy; <?php echo date("Y"); ?> Glow Care. All rights reserved.</p>
    </footer>

    <!-- Scroll Animation Script -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const observerOptions = {
                root: null,
                rootMargin: '0px',
                threshold: 0.1 
            };

            const observer = new IntersectionObserver(function(entries, observer) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
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