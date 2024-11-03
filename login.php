<?php
require_once './config/connect.php';

session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to handle user login
function handleLogin($email, $password, $isAdmin) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        if (password_verify($password, $user['password'])) {
            // Store user ID, name, and role in session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            // Redirect based on role and admin request
            if ($isAdmin && $user['role'] === 'admin') {
                header('Location: admin_dashboard.php');
                exit();
            } elseif (!$isAdmin) {
                header('Location: index.php');
                exit();
            }
        } else {
            return "Invalid password.";
        }
    } else {
        return "No user found with that email address.";
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check which form was submitted
    if (isset($_POST['login_type'])) {
        if ($_POST['login_type'] === 'guest') {
            $error = handleLogin($_POST['guest_email'], $_POST['password'], false);
        } elseif ($_POST['login_type'] === 'admin') {
            $error = handleLogin($_POST['admin_email'], $_POST['password'], true);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="assets/js/app.js" defer></script>
    <title>Login</title>
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
        .role-selection {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        .input-section {
            margin-bottom: 15px;
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
    <script>
        function toggleLoginFields(role) {
            const guestFields = document.getElementById("guest_fields");
            const adminFields = document.getElementById("admin_fields");
            const loginTypeInput = document.getElementById("login_type");

            if (role === 'guest') {
                guestFields.style.display = "block";
                adminFields.style.display = "none";
                guestFields.querySelector('input').setAttribute('required', 'required'); // Set required for guest
                adminFields.querySelector('input').removeAttribute('required'); // Remove required for admin
                loginTypeInput.value = "guest";
            } else {
                guestFields.style.display = "none";
                adminFields.style.display = "block";
                guestFields.querySelector('input').removeAttribute('required'); // Remove required for guest
                adminFields.querySelector('input').setAttribute('required', 'required'); // Set required for admin
                loginTypeInput.value = "admin";
            }
        }

        function validateForm() {
            const guestEmail = document.getElementsByName("guest_email")[0];
            const adminEmail = document.getElementsByName("admin_email")[0];
            const password = document.getElementsByName("password")[0];
            const loginType = document.getElementById("login_type").value;

            if (loginType === 'guest' && guestEmail.value.trim() === '') {
                alert("Please enter your guest email.");
                guestEmail.focus();
                return false;
            } else if (loginType === 'admin' && adminEmail.value.trim() === '') {
                alert("Please enter your admin email.");
                adminEmail.focus();
                return false;
            } else if (password.value.trim() === '') {
                alert("Please enter your password.");
                password.focus();
                return false;
            }

            return true; // Proceed with submission
        }
    </script>
</head>
<body>
    <div class="form-container">
        <h3>Select Login Role</h3>
        <div class="role-selection">
            <button type="button" onclick="toggleLoginFields('guest')">Guest</button>
            <button type="button" onclick="toggleLoginFields('admin')">Admin</button>
        </div>

        <form action="" method="POST" onsubmit="return validateForm();">
            <div id="guest_fields" class="input-section" style="display: none;">
                <input type="email" name="guest_email" placeholder="Guest Email" required>
            </div>
            <div id="admin_fields" class="input-section" style="display: none;">
                <input type="email" name="admin_email" placeholder="Admin Email" required>
            </div>
            <input type="password" name="password" required placeholder="Password">
            <input type="hidden" id="login_type" name="login_type" value="">
            <button type="submit">Login</button>
        </form>

        <?php if ($error): ?>
            <p class="error-message"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        
        <a href="register.php">Register Now</a>
    </div>
</body>
</html>
