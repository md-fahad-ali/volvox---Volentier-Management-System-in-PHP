<?php
// Database configuration
include(__DIR__ . '/config/db.php'); 

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['picture'])) {
    $first_name = htmlspecialchars($_POST['first_name']);
    $last_name = htmlspecialchars($_POST['last_name']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Secure password hashing
    $birthdate = htmlspecialchars($_POST['birthdate']);
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);  // New email field
    $account_type = htmlspecialchars($_POST['account_type']);
    $picture = "";
    $gender = htmlspecialchars($_POST['gender']); // Add gender

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
        exit;
    }

    // Validation for gender
    if (!in_array($gender, ['male', 'female', 'other'])) {
        echo "Please select a valid gender.";
        exit;
    }

    // Handle file upload for picture
    if ($_FILES['picture']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $file_name = uniqid() . "_" . basename($_FILES["picture"]["name"]);
        $target_file = $target_dir . $file_name;

        // Get file extension and check allowed types
        $file_extension = strtolower(pathinfo($_FILES["picture"]["name"], PATHINFO_EXTENSION));
        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        $max_file_size = 2 * 1024 * 1024; // 2MB

        if (!in_array($file_extension, $allowed_types)) {
            echo "Invalid file type. Allowed types are: JPG, JPEG, PNG, GIF";
            exit;
        }

        if ($_FILES["picture"]["size"] > $max_file_size) {
            echo "File is too large. Maximum size allowed is 2MB.";
            exit;
        }

        if (move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file)) {
            $picture = $target_file;
        } else {
            echo "Error uploading the file.";
            exit;
        }
    }

    // Check if username or email already exists
    $sql_check = "SELECT * FROM accounts WHERE username = ? OR email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ss", $username, $email); // Check for both username and email
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['username'] === $username) {
            echo "Username is already taken.";
        } else {
            echo "Email is already registered.";
        }
        exit;
    }

    // Insert into the database
    $sql = "INSERT INTO accounts (first_name, last_name, password, birthdate, picture, account_type, username, email, gender) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", $first_name, $last_name, $password, $birthdate, $picture, $account_type, $username, $email, $gender);

    if ($stmt->execute()) {
        // Start session and store user data
        session_start();
        $_SESSION['user_id'] = $conn->insert_id; // Get the ID of the newly registered user
        $_SESSION['username'] = $username;
        $_SESSION['account_type'] = $account_type;
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name'] = $last_name;
        
        echo "Registration successful!";
        exit;
    } else {
        echo "Error: " . $stmt->error;
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
    <title>Register - Volunteer System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles/auth.css">
    <style>
        .image-upload-container {
            width: 150px;
            margin: 10px 0;
        }

        .preview-image {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #ddd;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .preview-image:hover {
            opacity: 0.8;
        }

        .error-message, .success-message {
            display: none;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
        }

        .error-message {
            background-color: #ffe6e6;
            color: #d63031;
            border: 1px solid #ff7675;
        }

        .success-message {
            background-color: #e6ffe6;
            color: #27ae60;
            border: 1px solid #2ecc71;
        }

        .gender-options {
            display: flex;
            gap: 20px;
            margin: 10px 0;
        }

        .radio-label {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }

        .radio-label input[type="radio"] {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-box">
            <div class="header">
                <h1>Create Account</h1>
                <p>Join our volunteer community</p>
            </div>

            <div class="error-message" id="errorMessage"></div>
            <div class="success-message" id="successMessage"></div>

            <form id="registerForm" method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="birthdate">Birthdate</label>
                    <input type="date" id="birthdate" name="birthdate" required>
                </div>

                <div class="form-group">
                    <label>Account Type</label>
                    <div class="account-type-selector">
                        <div class="account-type-option" data-type="volunteer">
                            <input type="radio" name="account_type" value="volunteer" required>
                            Volunteer
                        </div>
                        <div class="account-type-option" data-type="organization">
                            <input type="radio" name="account_type" value="organization">
                            Organization
                        </div>
                    </div>
                </div>

                

                <div class="form-group">
                    <label>Gender</label>
                    <div class="gender-options">
                        <label class="radio-label">
                            <input type="radio" name="gender" value="male" required onchange="updateDefaultImage('male')">
                            Male
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="gender" value="female" required onchange="updateDefaultImage('female')">
                            Female
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="gender" value="other" required onchange="updateDefaultImage('other')">
                            Other
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="picture">Profile Picture</label>
                    <div class="image-upload-container">
                        <input type="file" id="picture" name="picture" class="file-input" accept="image/*" onchange="previewImage(this)" style="display: none;">
                        <img id="imagePreview" src="assets/man.png" alt="Profile Preview" class="preview-image" onclick="document.getElementById('picture').click()">
                    </div>
                </div>

                

                <button type="submit" class="auth-btn">Register</button>
            </form>

            <div class="auth-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const form = document.getElementById("registerForm");
            const errorMessage = document.getElementById("errorMessage");
            const successMessage = document.getElementById("successMessage");

            // Account type selector
            const accountOptions = document.querySelectorAll('.account-type-option');
            accountOptions.forEach(option => {
                option.addEventListener('click', () => {
                    accountOptions.forEach(opt => opt.classList.remove('selected'));
                    option.classList.add('selected');
                    option.querySelector('input').checked = true;
                });
            });

            form.addEventListener("submit", async (e) => {
                e.preventDefault(); // Prevent form from submitting normally
                
                // Clear previous messages
                errorMessage.style.display = "none";
                successMessage.style.display = "none";
                
                try {
                    const formData = new FormData(form);
                    
                    const response = await fetch("register.php", {
                        method: "POST",
                        body: formData
                    });
                    
                    const result = await response.text();
                    
                    if (result.includes("Registration successful")) {
                        successMessage.textContent = "Registration successful! Redirecting...";
                        successMessage.style.display = "block";
                        
                        // Redirect after successful registration
                        setTimeout(() => {
                            window.location.href = "index.php";
                        }, 2000);
                    } else {
                        errorMessage.textContent = result;
                        errorMessage.style.display = "block";
                        
                        // Scroll to error message
                        errorMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                } catch (error) {
                    errorMessage.textContent = "An error occurred. Please try again.";
                    errorMessage.style.display = "block";
                }
            });
        });

        function updateDefaultImage(gender) {
            const imagePreview = document.getElementById('imagePreview');
            
            switch(gender) {
                case 'male':
                    imagePreview.src = 'assets/man.png';
                    break;
                case 'female':
                    imagePreview.src = 'assets/female.png';
                    break;
            }
        }

        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const genderInputs = document.querySelectorAll('input[name="gender"]');
            genderInputs.forEach(input => {
                input.addEventListener('change', function() {
                    updateDefaultImage(this.value);
                });
            });
        });
    </script>
</body>
</html>
