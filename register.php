<?php
// public/register.php
require_once './config/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $phone = $_POST['phone'];

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format. Please include '@' and '.'";
    }

    // Validate phone number (should be 10 digits)
    if (!preg_match('/^\d{10}$/', $phone)) {
        $error = "Phone number must be 10 digits.";
    }

    // Check for existing email or phone number
    if (!isset($error)) {
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ? OR phone_number = ?");
        $stmt->execute([$email, $phone]);
        $existingUser = $stmt->fetch();

        if ($existingUser) {
            $error = "Cannot create account. An account already exists with this email or phone number.";
        }
    }

    // If no errors, proceed with the registration
    if (!isset($error)) {
        $stmt = $pdo->prepare("INSERT INTO Users (user_id, name, email, password, phone_number) VALUES (UUID(), ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $phone]);
        header('Location: login.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="assets/js/app.js" defer></script>
    <title>Register</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #e0eafc, #cfdef3); /* Soft gradient background */
            font-family: 'Arial', sans-serif;
        }
        .form-container {
            border-radius: 12px;
            padding: 30px;
            background: white;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            width: 320px;
            text-align: center;
        }
        h3 {
            margin-bottom: 20px;
            color: #333;
        }
        .form-container input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            transition: border-color 0.3s;
        }
        .form-container input:focus {
            border-color: #007BFF; /* Highlight border on focus */
            outline: none;
        }
        .form-container button {
            width: 100%;
            padding: 12px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .form-container button:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }
        .error-message {
            color: red;
            text-align: center;
            margin-top: 10px;
        }
        a {
            display: inline-block;
            margin-top: 15px;
            color: #007BFF;
            text-decoration: none;
            transition: color 0.3s;
        }
        a:hover {
            color: #0056b3; /* Darker blue on hover */
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h3>Register</h3>
        <form action="" method="POST">
            <input type="text" name="name" required placeholder="Name">
            <input type="email" name="email" required placeholder="Email">
            <input type="password" name="password" required placeholder="Password">
            <input type="text" name="phone" placeholder="Phone Number" maxlength="10">
            <button type="submit">Register</button>
            <a href="login.php">Already have an account? Login</a>
        </form>

        <?php if (isset($error)): ?>
            <p class="error-message"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
