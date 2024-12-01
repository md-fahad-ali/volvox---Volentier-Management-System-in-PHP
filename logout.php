<?php
session_start();

// Clear auth tokens from database if remember me was used
if (isset($_COOKIE['remember_user']) && isset($_COOKIE['remember_token'])) {
    $conn = new mysqli("localhost", "root", "", "volunteer");
    $sql = "DELETE FROM auth_tokens WHERE user_id = ? AND token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $_COOKIE['remember_user'], $_COOKIE['remember_token']);
    $stmt->execute();
    
    // Clear cookies
    setcookie("remember_user", "", time()-3600, "/");
    setcookie("remember_token", "", time()-3600, "/");
}

// Clear session
$_SESSION = array();
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}
session_destroy();

// Redirect to login
header("Location: login.php");
exit;
?> 