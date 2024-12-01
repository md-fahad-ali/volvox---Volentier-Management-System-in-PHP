<?php
session_start();
include(__DIR__ . '/config/db.php'); // Assuming this file contains the database connection


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch user data from the account table
$user_id = $_SESSION['user_id']; // Assuming user_id is stored in session
$query = "SELECT first_name, last_name, email, username, picture, gender FROM accounts WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="./styles/navbar.css">
    <link rel="stylesheet" href="./styles/profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Satisfy&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
</head>
<body>
<?php include(__DIR__ . '/components/navbar.php'); ?>
<div class="profile-box">
    <div class="profile-container">
        <?php
        $defaultImage = ($user['gender'] === 'female') ? 'assets/female.png' : 'assets/man.png';
        $imageUrl = empty($user['picture']) ? $defaultImage : $user['picture'];
        ?>
        <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="Profile" class="profile-picture">
        <h2 class="profile-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
        <p class="profile-username">@<?php echo htmlspecialchars($user['username']); ?></p>

        <div class="profile-details">
            <div>
                <i class="fas fa-envelope"></i>
                <span><?php echo htmlspecialchars($user['email']); ?></span>
            </div>
            <div>
                <i class="fas fa-user-tag"></i>
                <span><?php echo htmlspecialchars(ucfirst($_SESSION['account_type'])); ?></span>
            </div>
        </div>
    </div>
</div>
</body>
</html> 