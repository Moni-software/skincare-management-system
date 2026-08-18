<?php
require_once 'connect.php';

$categoryName = 'Facecare';

// Prepared statement using MySQLi ($conn)
$stmt = $conn->prepare("SELECT * FROM product WHERE category = ? ORDER BY sub_category");
$stmt->bind_param("s", $categoryName);
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);

$subCategories = [];
foreach ($products as $product) {
    $subCategories[$product['sub_category']][] = $product;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowCare - Face Care</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'navbar.php'; ?>

    <main class="container">
        <?php if (empty($subCategories)): ?>
            <p class="no-products">No products found in Face Care.</p>
        <?php else: ?>
            <?php foreach ($subCategories as $subCategoryName => $items): ?>
                
                <section class="category-section">
                    <h2 class="sub-category-title"><?php echo htmlspecialchars($subCategoryName); ?></h2>
                    
                    <div class="product-grid">
                        <?php foreach ($items as $item): ?>
                            <div class="product-card" data-id="<?php echo $item['P_id']; ?>">
                                
                              
                                <div class="product-image-container">
                                    <?php 
                                        $imagePath = (!empty($item['image']) && $item['image'] !== 'N/A') 
                                            ? $item['image'] 
                                            : 'images/default.jpg'; 
                                    ?>
                                    <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                                         alt="<?php echo htmlspecialchars($item['P_name']); ?>" 
                                         class="product-image">
                                </div>
                                
                                <div class="product-details">
                                    <h3 class="product-name"><?php echo htmlspecialchars($item['P_name']); ?></h3>
                                    
                                    <?php if (!empty($item['Skin/Hair_type']) && $item['Skin/Hair_type'] !== 'N/A'): ?>
                                        <p class="product-meta"><?php echo htmlspecialchars($item['Skin/Hair_type']); ?></p>
                                    <?php endif; ?>

                                    <p class="price">Rs. <?php echo number_format($item['P_price'], 2); ?></p>

                                    <?php if (!empty($item['P_quantity']) && $item['P_quantity'] !== 'N/A'): ?>
                                        <p class="product-info">Quantity: <?php echo htmlspecialchars($item['P_quantity']); ?></p>
                                    <?php endif; ?>

                                    <p class="product-info">Stock: <?php echo htmlspecialchars($item['In_stock']); ?></p>

                                    <?php if (!empty($item['guide']) && $item['guide'] !== 'N/A'): ?>
                                        <p class="product-info"><strong>How to Use:</strong> <?php echo htmlspecialchars($item['guide']); ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($item['benifits']) && $item['benifits'] !== 'N/A'): ?>
                                        <p class="product-info"><strong>Benefits:</strong> <?php echo htmlspecialchars($item['benifits']); ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="card-actions">
                                    <button class="btn-add-cart">Add to Cart</button>
                                    <div class="quantity-control">
                                        <button class="qty-btn btn-minus">-</button>
                                        <span class="qty-val">1</span>
                                        <button class="qty-btn btn-plus">+</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <script src="P_script.js"></script>
</body>
</html>