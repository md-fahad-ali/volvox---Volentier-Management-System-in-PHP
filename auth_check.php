<?php
// Create this new file to handle authentication checks
session_start();

function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}
?> 