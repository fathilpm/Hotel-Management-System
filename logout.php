<?php
session_start(); // Start the session

// Unset all session variables
$_SESSION = array();

// Destroy the session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy(); // Destroy the session

// Redirect to the login page or homepage
header("Location: login.php"); // Change to the path of your login page
exit; // Ensure no further code is executed
?>
