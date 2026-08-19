<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$navbar_customer_name = "Guest";

if (isset($_SESSION['customer_id']) && !empty($_SESSION['customer_id'])) {

    if (!isset($conn)) {
        include_once 'connect.php';
    }

    $customer_id = intval($_SESSION['customer_id']);

    $stmt = $conn->prepare("SELECT name FROM customers WHERE id = ?");

    if ($stmt) {
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $customer = $result->fetch_assoc();

        if ($customer) {
            $navbar_customer_name = $customer['name'];
        }

        $stmt->close();
    }
}
?>

<header class="main-navbar">

    <div class="navbar-container">

        <!-- Logo -->
        <a href="home.php" class="navbar-logo">
            <span class="logo-icon">✦</span>
            <span>Glow <b>Care</b></span>
        </a>


        <!-- Navigation -->
        <nav class="navbar-links">

            <a href="home.php" class="nav-link">
                Home
            </a>

            <!-- Products Dropdown -->
            <div class="nav-dropdown">

                <span class="products">Products <span class="arrow">▾</span></span>
            
            

                <div class="dropdown-menu">

                    <a href="bodycare.php">
                        🧴 Body Care
                    </a>

                    <a href="facecare.php">
                        ✨ Face Care
                    </a>

                    <a href="haircare.php">
                        💆 Hair Care
                    </a>

                    <a href="makeup.php">
                        💄 Makeup
                    </a>

                    <a href="fragrance.php">
                        🌸 Fragrance
                    </a>

                </div>

            </div>

            <a href="deals.php" class="nav-link">
                Deals
            </a>

            <a href="admin.php" class="nav-link">
                IT Support
            </a>

        </nav>


        <!-- Right Side -->
        <div class="navbar-right">

            <?php if (isset($_SESSION['customer_id']) && !empty($_SESSION['customer_id'])): ?>

                <!-- Logged In Customer -->
                <a href="customer.php" class="customer-profile">

                    <span class="customer-avatar">
                        <?php
                        echo strtoupper(
                            substr($navbar_customer_name, 0, 1)
                        );
                        ?>
                    </span>

                    <span class="customer-name">
                        <?php
                        echo htmlspecialchars($navbar_customer_name);
                        ?>
                    </span>

                </a>

            <?php else: ?>

                <!-- Login -->
                <a href="login.php" class="login-button">
                    Login
                </a>

            <?php endif; ?>


            <!-- Cart -->
            <a href="customer.php" class="cart-button">

                🛒

                <span class="cart-count">
                    0
                </span>

            </a>

        </div>

    </div>

</header>


<style>

/* 
   NAVBAR
 */

.main-navbar {
    width: 100%;
    background: #694e43;
    border-bottom: 1px solid #44352f;
    position: sticky;
    top: 0;
    z-index: 9999;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}


.navbar-container {
    width: 92%;
    max-width: 1250px;
    min-height: 76px;
    margin: auto;

    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 25px;
}


/*
   LOGO
 */

.navbar-logo {
    display: flex;
    align-items: center;
    gap: 7px;

    color: #ffffff;

    font-family: Georgia, serif;
    font-size: 25px;
    font-weight: bold;

    letter-spacing: 1.5px;
    text-decoration: none;
    white-space: nowrap;
}


.logo-icon {
    color: #d2a679;
    font-size: 25px;
}


.navbar-logo b {
    color: #d2a679;
}


/*
   NAVIGATION LINKS
 */

.navbar-links {
    display: flex;
    align-items: center;
    gap: 28px;

    margin-left: auto;
    margin-right: 20px;
}


.nav-link {
    position: relative;

    color: #ffffff;

    font-size: 13px;
    font-weight: 600;

    text-transform: uppercase;
    letter-spacing: 1px;

    padding: 10px 0;

    text-decoration: none;

    transition: 0.3s;
}


.nav-link:hover {
    color: #d2a679;
}


.nav-link::after {
    content: "";

    position: absolute;

    left: 0;
    bottom: 2px;

    width: 0;
    height: 1px;

    background: #d2a679;

    transition: 0.3s;
}


.nav-link:hover::after {
    width: 100%;
}


/*
   PRODUCTS DROPDOWN
*/

.nav-dropdown {
    position: relative;
    display: flex;
    align-items: center;
}


.arrow {
    font-size: 11px;
    transition: 0.3s;
}


.nav-dropdown:hover .arrow {
    transform: rotate(180deg);
}


.dropdown-menu {

    position: absolute;

    top: calc(100% + 10px);
    left: -20px;

    width: 190px;

    background: #2c221e;

    border: 1px solid #4a3932;
    border-radius: 6px;

    padding: 8px 0;

    box-shadow: 0 15px 35px rgba(0,0,0,0.30);

    opacity: 0;
    visibility: hidden;

    transform: translateY(10px);

    transition: all 0.3s ease;
}


.nav-dropdown:hover .dropdown-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}


.dropdown-menu a {

    display: block;

    color: #ffffff;

    padding: 12px 20px;

    font-size: 13px;

    text-decoration: none;

    transition: 0.3s;
}


.dropdown-menu a:hover {

    background: #44352f;

    color: #d2a679;

    padding-left: 25px;
}


/* 
   RIGHT SIDE
*/

.navbar-right {

    display: flex;
    align-items: center;

    gap: 18px;

    white-space: nowrap;
}


/*
   LOGIN BUTTON
 */

.login-button {

    color: #ffffff;

    border: 1px solid #ffffff;

    padding: 8px 20px;

    border-radius: 3px;

    font-size: 12px;
    font-weight: 600;

    text-transform: uppercase;
    letter-spacing: 1px;

    text-decoration: none;

    transition: 0.3s;
}


.login-button:hover {

    background: #ffffff;

    color: #2c221e;
}


/* 
   CUSTOMER PROFILE
 */

.customer-profile {

    display: flex;
    align-items: center;

    gap: 8px;

    padding: 6px 13px;

    border-radius: 22px;

    background: #3d2f2a;

    border: 1px solid #514039;

    text-decoration: none;

    transition: 0.3s;
}


.customer-profile:hover {
    background: #493831;
}


.customer-avatar {

    width: 28px;
    height: 28px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: #ffffff;

    color: #855e4e;

    font-size: 12px;
    font-weight: bold;
}


.customer-name {

    color: #ffffff;

    font-size: 13px;
    font-weight: 600;

    max-width: 100px;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;
}


/*
   CART
 */

.cart-button {

    position: relative;

    display: flex;

    align-items: center;
    justify-content: center;

    color: #ffffff;

    font-size: 20px;

    width: 35px;
    height: 35px;

    text-decoration: none;

    transition: 0.3s;
}


.cart-button:hover {

    color: #d2a679;

    transform: translateY(-2px);
}


.cart-count {

    position: absolute;

    top: -4px;
    right: -5px;

    min-width: 17px;
    height: 17px;

    display: flex;

    align-items: center;
    justify-content: center;

    background: #d2a679;

    color: #2c221e;

    font-size: 9px;

    font-weight: bold;

    border-radius: 50%;

    padding: 2px;
}

.products{
color: #ffffff;
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    cursor: pointer;
    transition: 0.3s;}


/* ================================
   MOBILE
================================ */

@media (max-width: 900px) {

    .navbar-container {
        flex-wrap: wrap;
        padding: 12px 0;
    }

    .navbar-links {

        order: 3;

        width: 100%;

        margin: 0;

        justify-content: center;

        gap: 20px;
    }
}


@media (max-width: 600px) {

    .navbar-container {
        width: 94%;
    }

    .navbar-logo {
        font-size: 20px;
    }

    .logo-icon {
        font-size: 20px;
    }

    .navbar-links {
        justify-content: flex-start;
        overflow-x: auto;
        gap: 18px;
    }

    .nav-link {
        font-size: 11px;
    }

    .customer-name {
        display: none;
    }

    .login-button {
        padding: 7px 12px;
        font-size: 10px;
    }

}


</style>