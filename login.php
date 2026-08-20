<?php
session_start();
require_once 'connect.php';

$login_error = "";
$register_error = "";

// Form input වල පැරණි අගයන් රඳවා ගැනීමට variables
$reg_name = "";
$reg_address = "";
$reg_phone = "";
$reg_email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $email = trim($_POST['login_email']);
    $password = $_POST['login_password'];

    if (empty($email) || empty($password)) {
        $login_error = "Please enter your email and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password FROM customers WHERE email = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $customer = $result->fetch_assoc();
                $password_valid = false;

                if (password_verify($password, $customer['password'])) {
                    $password_valid = true;
                } elseif ($password === $customer['password']) {
                    $password_valid = true;
                    $new_hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $update_password = $conn->prepare("UPDATE customers SET password = ? WHERE id = ?");
                    if ($update_password) {
                        $update_password->bind_param("si", $new_hashed_password, $customer['id']);
                        $update_password->execute();
                        $update_password->close();
                    }
                }

                if ($password_valid) {
                    session_regenerate_id(true);
                    $_SESSION['customer_id'] = $customer['id'];
                    $_SESSION['customer_name'] = $customer['name'];
                    header("Location: customer.php");
                    exit();
                } else {
                    $login_error = "Incorrect email or password.";
                }
            } else {
                $login_error = "Incorrect email or password.";
            }
            $stmt->close();
        } else {
            $login_error = "Database error. Please try again.";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['register'])) {
    $reg_name = trim($_POST['reg_name']);
    $reg_address = trim($_POST['reg_address']);
    $reg_phone = trim($_POST['reg_phone']);
    $reg_email = trim($_POST['reg_email']);
    $password = $_POST['reg_password'];

    if (empty($reg_name) || empty($reg_address) || empty($reg_phone) || empty($reg_email) || empty($password)) {
        $register_error = "Please fill in all fields.";
    } elseif (!filter_var($reg_email, FILTER_VALIDATE_EMAIL)) {
        $register_error = "Please enter a valid email address.";
    } elseif (!preg_match('/^[0-9]{10}$/', $reg_phone)) {
        $register_error = "Invalid phone number! Please enter a valid 10-digit phone number.";
    } else {
        $check_stmt = $conn->prepare("SELECT id FROM customers WHERE email = ? LIMIT 1");
        if ($check_stmt) {
            $check_stmt->bind_param("s", $reg_email);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                $register_error = "An account with this email already exists.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert_stmt = $conn->prepare("INSERT INTO customers (name, address, phone, email, password) VALUES (?, ?, ?, ?, ?)");
                if ($insert_stmt) {
                    $insert_stmt->bind_param("sssss", $reg_name, $reg_address, $reg_phone, $reg_email, $hashed_password);
                    if ($insert_stmt->execute()) {
                        session_regenerate_id(true);
                        $_SESSION['customer_id'] = $insert_stmt->insert_id;
                        $_SESSION['customer_name'] = $reg_name;
                        $insert_stmt->close();
                        header("Location: customer.php");
                        exit();
                    } else {
                        $register_error = "Registration failed. Please try again.";
                    }
                    $insert_stmt->close();
                } else {
                    $register_error = "Database error. Please try again.";
                }
            }
            $check_stmt->close();
        } else {
            $register_error = "Database error. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Glow Care - Login / Register</title>
    <link rel="stylesheet" href="style1.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #ffffff;
            color: #3b2c25;
            font-family: "Segoe UI", Arial, sans-serif;
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        a {
            text-decoration: none;
        }

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

        .navbar-logo {
            display: flex;
            align-items: center;
            gap: 7px;
            color: #ffffff;
            font-family: Georgia, serif;
            font-size: 25px;
            font-weight: bold;
            letter-spacing: 1.5px;
            white-space: nowrap;
        }

        .navbar-links {
            display: flex;
            align-items: center;
            gap: 28px;
            margin-left: auto;
        }

        .nav-link {
            position: relative;
            color: #ffffff;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 10px 0;
            transition: 0.3s;
        }

        .nav-link:hover, .nav-link.active {
            color: #d2a679;
        }

        .main-content {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 50px 20px;
            flex: 1;
            background-color: #fbf7f4;
        }

        .form-container {
            width: 100%;
            max-width: 450px;
            background: #ffffff;
            padding: 35px;
            border-radius: 12px;
            border: 1px solid #e8ddd2;
            box-shadow: 0 10px 30px rgba(71, 50, 35, 0.08);
        }

        h2 {
            font-family: Georgia, serif;
            color: #49362b;
            text-align: center;
            margin-bottom: 25px;
            font-size: 26px;
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 13px;
            color: #635248;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input, textarea {
            width: 100%;
            padding: 11px;
            border: 1px solid #e8ddd2;
            border-radius: 6px;
            box-sizing: border-box;
            font-family: inherit;
            color: #3b2c25;
            background: #fff;
            transition: 0.3s;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: #8c6239;
            box-shadow: 0 0 5px rgba(140, 98, 57, 0.2);
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: #8c6239;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #6f4c2c;
        }

        p {
            text-align: center;
            margin-top: 18px;
            font-size: 14px;
            color: #75675d;
        }

        p a {
            color: #8c6239;
            text-decoration: none;
            font-weight: bold;
        }

        p a:hover {
            text-decoration: underline;
        }

        .form-message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            text-align: center;
            font-size: 14px;
            background: #ffe5e5;
            color: #c62828;
            border: 1px solid #ffcaca;
        }

        footer {
            text-align: center;
            padding: 25px;
            background: #211915;
            color: #aaa;
            margin-top: auto;
            font-size: 13px;
        }
    </style>
</head>
<body>

    <header class="main-navbar">
        <div class="navbar-container">
            <a href="home.php" class="navbar-logo">
                <span style="color: #d2a679;">✦</span>
                <span>Glow <b style="color: #d2a679;">Care</b></span>
            </a>
            <nav class="navbar-links">
                <a href="home.php" class="nav-link">Home</a>
                <a href="deals.php" class="nav-link">Deals</a>
                <a href="login.php" class="nav-link active" id="auth-link">Login</a>
            </nav>
        </div>
    </header>

    <div class="main-content">
        <div class="form-container">
            
            <div class="form-box" id="login-box" style="<?php echo !empty($register_error) ? 'display:none;' : ''; ?>">
                <h2>Welcome Back</h2>
                <?php if (!empty($login_error)): ?>
                    <div class="form-message"><?php echo htmlspecialchars($login_error); ?></div>
                <?php endif; ?>
                <form method="POST" action="login.php">
                    <div class="form-group">
                        <label>Email Address:</label>
                        <input type="email" name="login_email" placeholder="Enter your email" autocomplete="off" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 22px;">
                        <label>Password:</label>
                        <input type="password" name="login_password" placeholder="Enter your password" autocomplete="new-password" required>
                    </div>
                    <button type="submit" name="login" class="btn">Login</button>
                </form>
                <p>Don't have an account? <a href="#" onclick="toggleForm(); return false;">Register here</a></p>
            </div>

            <div class="form-box" id="register-box" style="<?php echo !empty($register_error) ? 'display:block;' : 'display:none;'; ?>">
                <h2>Create Account</h2>
                <?php if (!empty($register_error)): ?>
                    <div class="form-message"><?php echo htmlspecialchars($register_error); ?></div>
                <?php endif; ?>
                <form method="POST" action="login.php">
                    <div class="form-group">
                        <label>Full Name:</label>
                        <input type="text" name="reg_name" placeholder="Enter your full name" value="<?php echo htmlspecialchars($reg_name); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Address:</label>
                        <textarea name="reg_address" placeholder="Enter your address" required style="height: 60px; resize: vertical;"><?php echo htmlspecialchars($reg_address); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Phone Number:</label>
                        <!-- 10 digits validation (value එක නැවත පෙන්වීමට value attribute එක එකතු කර ඇත) -->
                        <input type="tel" name="reg_phone" placeholder="0771234567" value="<?php echo htmlspecialchars($reg_phone); ?>" pattern="[0-9]{10}" maxlength="10" title="Please enter exactly 10 digits" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address:</label>
                        <input type="email" name="reg_email" placeholder="Enter your email address" value="<?php echo htmlspecialchars($reg_email); ?>" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 22px;">
                        <label>Create Password:</label>
                        <input type="password" name="reg_password" placeholder="Create a strong password" required>
                    </div>
                    <button type="submit" name="register" class="btn">Register</button>
                </form>
                <p>Already have an account? <a href="#" onclick="toggleForm(); return false;">Login here</a></p>
            </div>

        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Glow Care Pvt Ltd. All rights reserved.</p>
    </footer>

    <script>
        function toggleForm() {
            const loginBox = document.getElementById('login-box');
            const registerBox = document.getElementById('register-box');
            if (loginBox.style.display === 'none' || getComputedStyle(loginBox).display === 'none') {
                loginBox.style.display = 'block';
                registerBox.style.display = 'none';
            } else {
                loginBox.style.display = 'none';
                registerBox.style.display = 'block';
            }
        }
    </script>
</body>
</html>