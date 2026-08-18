<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$navbar_customer_name = "Guest";

/* Check whether a customer is logged in */
if (isset($_SESSION['customer_id']) && !empty($_SESSION['customer_id'])) {

    if (!isset($conn)) {
        include_once 'connect.php';
    }

    $navbar_customer_id = intval($_SESSION['customer_id']);

    $navbar_stmt = $conn->prepare(
        "SELECT name FROM customers WHERE id = ?"
    );

    if ($navbar_stmt) {

        $navbar_stmt->bind_param("i", $navbar_customer_id);
        $navbar_stmt->execute();

        $navbar_result = $navbar_stmt->get_result();
        $navbar_customer = $navbar_result->fetch_assoc();

        if ($navbar_customer) {
            $navbar_customer_name = $navbar_customer['name'];
        }

        $navbar_stmt->close();
    }
}
?>

<header class="main-header">

    <div class="logo">
        Glow Care
    </div>

    <nav>

        <a href="home.php">Home</a>

        <div class="dropdown">

            <button class="dropbtn">
                Products ▾
            </button>

            <div class="dropdown-content">

                <a href="bodycare.php">Body Care</a>
                <a href="facecare.php">Face Care</a>
                <a href="haircare.php">Hair Care</a>
                <a href="makeup.php">Makeup</a>
                <a href="fragrance.php">Fragrance</a>

            </div>

        </div>

        <a href="deals.php">Deals</a>

        <a href="admin.php">IT Support</a>


        <?php if (isset($_SESSION['customer_id']) && !empty($_SESSION['customer_id'])): ?>

            <!-- Customer is logged in -->

            <a href="customer.php" class="customer-account">
                👤 <?php echo htmlspecialchars($navbar_customer_name); ?>
            </a>

        <?php else: ?>

            <!-- Customer is logged out -->

            <a href="login.php" id="auth-link">
                Login
            </a>

        <?php endif; ?>

    </nav>

</header>