<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "volunteer";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = htmlspecialchars($_POST['username']);
    $password = $_POST['password'];

    // Fetch user from database
    $sql = "SELECT * FROM accounts WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Start session and store user data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['account_type'] = $user['account_type'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            
            echo "Login successful!";
            exit;
        } else {
            echo "Invalid password!";
            exit;
        }
    } else {
        echo "No account found with that username or email!";
        exit;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Volunteer System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="./styles/login.css"/>
</head>
<body>
    <div class="container">
        <div class="login-box">
            <div class="header">
                <h1>Welcome Back</h1>
                <p>Please login to your account</p>
            </div>

            <div class="error-message" id="errorMessage"></div>
            <div id="successMessage" class="success-message"></div>

            <form id="loginForm" method="post">
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           placeholder="Enter your username or email"
                           required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="Enter your password"
                           required>
                </div>
                <button type="submit" class="login-btn">Login</button>
            </form>

            <div class="register-link">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const loginForm = document.getElementById("loginForm");
            
            // Add message divs if not already present
            if (!document.getElementById("errorMessage")) {
                const errorDiv = document.createElement("div");
                errorDiv.id = "errorMessage";
                errorDiv.className = "error-message";
                loginForm.insertBefore(errorDiv, loginForm.firstChild);
            }
            
            if (!document.getElementById("successMessage")) {
                const successDiv = document.createElement("div");
                successDiv.id = "successMessage";
                successDiv.className = "success-message";
                loginForm.insertBefore(successDiv, loginForm.firstChild);
            }

            const errorMessage = document.getElementById("errorMessage");
            const successMessage = document.getElementById("successMessage");

            loginForm.addEventListener("submit", async (e) => {
                e.preventDefault();

                // Clear previous messages
                errorMessage.style.display = "none";
                successMessage.style.display = "none";

                try {
                    const formData = new FormData(loginForm);
                    const submitButton = loginForm.querySelector('button[type="submit"]');
                    submitButton.disabled = true;
                    submitButton.innerHTML = 'Logging in...';

                    const response = await fetch("login.php", {
                        method: "POST",
                        body: formData
                    });

                    const result = await response.text();

                    if (result.includes("Login successful")) {
                        successMessage.innerHTML = `
                            <div class="success-content">
                                <i class="fas fa-check-circle"></i>
                                Login successful! Redirecting...
                            </div>`;
                        successMessage.style.display = "block";

                        setTimeout(() => {
                            window.location.href = "index.php";
                        }, 1500);
                    } else {
                        errorMessage.innerHTML = `
                            <div class="error-content">
                                <i class="fas fa-exclamation-circle"></i>
                                ${result}
                            </div>`;
                        errorMessage.style.display = "block";
                        submitButton.disabled = false;
                        submitButton.innerHTML = 'Login';
                    }
                } catch (error) {
                    errorMessage.innerHTML = `
                        <div class="error-content">
                            <i class="fas fa-exclamation-circle"></i>
                            An error occurred. Please try again.
                        </div>`;
                    errorMessage.style.display = "block";
                    submitButton.disabled = false;
                    submitButton.innerHTML = 'Login';
                }
            });
        });
    </script>

   
</body>
</html> 