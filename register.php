<?php
session_start();
include('connect.php');

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $address = trim($_POST['address']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $role = 'customer'; // 

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $message = "<div class='error-msg'>This email is already registered!</div>";
    } else {
        $check->close();
        $sql = "INSERT INTO users (fullname, address, email, phone, password, role) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $fullname, $address, $email, $phone, $hashed_password, $role);

        if ($stmt->execute()) {
            $message = "<div class='success-msg'>Registration successful! <a href='login.php'>Login here</a></div>";
        } else {
            $message = "<div class='error-msg'>Error: " . $conn->error . "</div>";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - BestCare Hospital</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .form-container {
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        .form-container h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            margin-bottom: 6px;
            font-weight: 600;
            color: #444;
            font-size: 13px;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
            outline: none;
        }
        .form-group input:focus, .form-group textarea:focus {
            border-color: #8A2BE2;
        }
        .btn-submit {
            width: 100%;
            background-color: #8A2BE2;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn-submit:hover {
            opacity: 0.9;
        }
        .error-msg { background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; font-size: 14px; }
        .success-msg { background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; font-size: 14px; }
        .form-footer { text-align: center; margin-top: 15px; font-size: 14px; color: #666; }
        .form-footer a { color: #8A2BE2; text-decoration: none; font-weight: 600; }
        .form-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Create Account</h2>
    
    <?php echo $message; ?>

    <form action="register.php" method="POST">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="fullname" required placeholder="Enter your full name">
        </div>

        <div class="form-group">
            <label>Address</label>
            <textarea name="address" rows="2" required placeholder="Enter your address"></textarea>
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required placeholder="Enter your email">
        </div>

        <div class="form-group">
            <label>Phone Number</label>
            <input type="text" name="phone" required placeholder="Enter your phone number">
        </div>

        <div class="form-group">
            <label>Create Password</label>
            <input type="password" name="password" required placeholder="Create a password">
        </div>

        <button type="submit" class="btn-submit">Register</button>
    </form>

    <div class="form-footer">
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</div>

</body>
</html>