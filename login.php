<?php
session_start();

require_once 'connect.php';

/*
|--------------------------------------------------------------------------
| LOGIN / REGISTER MESSAGES
|--------------------------------------------------------------------------
*/

$login_error = "";
$register_error = "";


/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {

    $email = trim($_POST['login_email']);
    $password = $_POST['login_password'];

    if (empty($email) || empty($password)) {

        $login_error = "Please enter your email and password.";

    } else {

        $stmt = $conn->prepare(
            "SELECT id, name, password FROM customers WHERE email = ? LIMIT 1"
        );

        if ($stmt) {

            $stmt->bind_param("s", $email);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows === 1) {

                $customer = $result->fetch_assoc();

                /*
                 * This supports BOTH:
                 * 1. Old plain-text passwords already in your database
                 * 2. New passwords created with password_hash()
                 */

                $password_valid = false;

                // Check hashed password
                if (password_verify($password, $customer['password'])) {
                    $password_valid = true;
                }

                // Check old plain-text password
                elseif ($password === $customer['password']) {
                    $password_valid = true;

                    /*
                     * Automatically convert the old plain-text password
                     * into a secure hashed password.
                     */
                    $new_hashed_password = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    $update_password = $conn->prepare(
                        "UPDATE customers SET password = ? WHERE id = ?"
                    );

                    if ($update_password) {

                        $update_password->bind_param(
                            "si",
                            $new_hashed_password,
                            $customer['id']
                        );

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


/*
|--------------------------------------------------------------------------
| REGISTER
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['register'])) {

    $name = trim($_POST['reg_name']);
    $address = trim($_POST['reg_address']);
    $phone = trim($_POST['reg_phone']);
    $email = trim($_POST['reg_email']);
    $password = $_POST['reg_password'];

    if (
        empty($name) ||
        empty($address) ||
        empty($phone) ||
        empty($email) ||
        empty($password)
    ) {

        $register_error = "Please fill in all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $register_error = "Please enter a valid email address.";

    } else {

        $check_stmt = $conn->prepare(
            "SELECT id FROM customers WHERE email = ? LIMIT 1"
        );

        if ($check_stmt) {

            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();

            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {

                $register_error = "An account with this email already exists.";

            } else {

                $hashed_password = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $insert_stmt = $conn->prepare(
                    "INSERT INTO customers
                    (name, address, phone, email, password)
                    VALUES (?, ?, ?, ?, ?)"
                );

                if ($insert_stmt) {

                    $insert_stmt->bind_param(
                        "sssss",
                        $name,
                        $address,
                        $phone,
                        $email,
                        $hashed_password
                    );

                    if ($insert_stmt->execute()) {

                        session_regenerate_id(true);

                        $_SESSION['customer_id'] = $insert_stmt->insert_id;
                        $_SESSION['customer_name'] = $name;

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

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Glow Care - Login / Register</title>

    <link rel="stylesheet" href="style.css">

    <style>

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .login-page-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 45px 20px;
            box-sizing: border-box;
        }

        .form-container {
            width: 100%;
            max-width: 450px;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            border: 1px solid #ffd1dc;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            box-sizing: border-box;
        }

        .form-box h2 {
            color: #ff4b72;
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
            font-family: inherit;
        }

        .form-group textarea {
            height: 60px;
            resize: vertical;
        }

        .login-button {
            width: 100%;
            padding: 12px;
            background: #ff4b72;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        .login-button:hover {
            background: #e83d62;
        }

        .form-message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            text-align: center;
            font-size: 14px;
        }

        .error-message {
            background: #ffe5e5;
            color: #c62828;
            border: 1px solid #ffcaca;
        }

        footer {
            text-align: center;
            padding: 25px;
            background: #333;
            color: white;
        }

    </style>

</head>

<body>

<?php include 'navbar.php'; ?>

<main class="login-page-content">

    <div class="form-container">

        <!-- LOGIN FORM -->

        <div
            class="form-box"
            id="login-box"
            style="<?php echo !empty($register_error) ? 'display:none;' : ''; ?>"
        >

            <h2>Login to Glow Care</h2>

            <?php if (!empty($login_error)): ?>

                <div class="form-message error-message">
                    <?php echo htmlspecialchars($login_error); ?>
                </div>

            <?php endif; ?>

            <form method="POST" action="login.php">

                <div class="form-group">

                    <label>Email Address:</label>

                    <input
                        type="email"
                        name="login_email"
                        placeholder="Enter your email"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>Password:</label>

                    <input
                        type="password"
                        name="login_password"
                        placeholder="Enter your password"
                        required
                    >

                </div>

                <button
                    type="submit"
                    name="login"
                    class="login-button"
                >
                    Login
                </button>

            </form>

            <p style="text-align:center; margin-top:15px; font-size:14px;">

                Don't have an account?

                <a
                    href="#"
                    onclick="toggleForm(); return false;"
                    style="color:#ff4b72; text-decoration:none; font-weight:bold;"
                >
                    Register here
                </a>

            </p>

        </div>


        <!-- REGISTER FORM -->

        <div
            class="form-box"
            id="register-box"
            style="<?php echo !empty($register_error) ? 'display:block;' : 'display:none;'; ?>"
        >

            <h2>Create an Account</h2>

            <?php if (!empty($register_error)): ?>

                <div class="form-message error-message">
                    <?php echo htmlspecialchars($register_error); ?>
                </div>

            <?php endif; ?>

            <form method="POST" action="login.php">

                <div class="form-group">

                    <label>Full Name:</label>

                    <input
                        type="text"
                        name="reg_name"
                        placeholder="Enter your full name"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>Address:</label>

                    <textarea
                        name="reg_address"
                        placeholder="Enter your address"
                        required
                    ></textarea>

                </div>

                <div class="form-group">

                    <label>Phone Number:</label>

                    <input
                        type="tel"
                        name="reg_phone"
                        placeholder="Enter your phone number"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>Email Address:</label>

                    <input
                        type="email"
                        name="reg_email"
                        placeholder="Enter your email address"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>Create Password:</label>

                    <input
                        type="password"
                        name="reg_password"
                        placeholder="Create a strong password"
                        required
                    >

                </div>

                <button
                    type="submit"
                    name="register"
                    class="login-button"
                >
                    Register
                </button>

            </form>

            <p style="text-align:center; margin-top:15px; font-size:14px;">

                Already have an account?

                <a
                    href="#"
                    onclick="toggleForm(); return false;"
                    style="color:#ff4b72; text-decoration:none; font-weight:bold;"
                >
                    Login here
                </a>

            </p>

        </div>

    </div>

</main>


<footer>

    <p>
        &copy; <?php echo date("Y"); ?>
        Glow Care. All rights reserved.
    </p>

</footer>


<script>

function toggleForm() {

    const loginBox = document.getElementById('login-box');
    const registerBox = document.getElementById('register-box');

    if (
        loginBox.style.display === 'none' ||
        getComputedStyle(loginBox).display === 'none'
    ) {

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