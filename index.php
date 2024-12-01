<?php
require_once 'auth_check.php';
checkAuth();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Volunteer System</title>
    <link rel="stylesheet" href="./styles/index.css">
    <link rel="stylesheet" href="./styles/navbar.css">
    <link rel="stylesheet" href="./styles/group.css">
    <link href="https://fonts.googleapis.com/css2?family=Satisfy&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <!-- Navbar is included here -->
    <?php include(__DIR__ . '/components/navbar.php'); ?>

    <div class="tab">
        <div class="user-info">
            <div class="welcome-message">
                <?php
                // Connect to database to get all user data in one query
                $conn = new mysqli("localhost", "root", "", "volunteer");
                $stmt = $conn->prepare("SELECT first_name, last_name, email, birthdate, gender FROM accounts WHERE id = ?");
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();

                echo "Hello, " . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . "! 👋";
                ?>
            </div>

            
            <div class="profile-details">
                <div class="profile-card">
                    <div class="profile-item">
                        <div class="profile-icon">
                            <i class="fas fa-user-tag"></i>
                        </div>
                        <div class="profile-content">
                            <span class="profile-label">Account Type</span>
                            <span class="profile-value"><?php echo htmlspecialchars($_SESSION['account_type']); ?></span>
                        </div>
                    </div>
                    <div class="profile-item">
                        <div class="profile-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="profile-content">
                            <span class="profile-label">Email</span>
                            <span class="profile-value"><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                    </div>
                    <div class="profile-item">
                        <div class="profile-icon">
                            <i class="fas fa-venus-mars"></i>
                        </div>
                        <div class="profile-content">
                            <span class="profile-label">Gender</span>
                            <span class="profile-value">
                                <?php
                                $gender = htmlspecialchars($user['gender']);
                                $genderIcon = '';
                                switch ($gender) {
                                    case 'male':
                                        $genderIcon = '👨';
                                        $gender = 'Male';
                                        break;
                                    case 'female':
                                        $genderIcon = '👩';
                                        $gender = 'Female';
                                        break;
                                    default:
                                        $genderIcon = '👤';
                                        $gender = 'Other';
                                }
                                echo $genderIcon . ' ' . $gender;
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <style>
            .profile-heading {
                font-size: 2.2rem;
                color: #1a237e;
                margin: 2rem 0;
                text-align: center;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            .profile-details {
                
                margin: 0 auto;
                display: flex;
                flex-wrap: wrap;
                position: relative;
            }

            .profile-card {
                background: linear-gradient(145deg, #ffffff, #f5f7fa);
                border-radius: 20px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.1);
                padding: 3rem;
                width: 100%;
                border: 1px solid rgba(255,255,255,0.18);
                margin-bottom: 2rem;
            }

            .profile-item {
                display: flex;
                align-items: center;
                padding: 1.5rem;
                margin-bottom: 1rem;
                border-radius: 12px;
                background: rgba(255,255,255,0.8);
                backdrop-filter: blur(10px);
                transition: all 0.3s ease;
            }

            .profile-item:last-child {
                margin-bottom: 0;
            }

            .profile-item:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                background: rgba(255,255,255,0.95);
            }

            .profile-icon {
                width: 50px;
                height: 50px;
                background: #1a237e;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-right: 2rem;
            }

            .profile-icon i {
                color: white;
                font-size: 1.5rem;
            }

            .profile-content {
                flex: 1;
            }

            .profile-label {
                display: block;
                font-weight: 600;
                color: #1a237e;
                font-size: 1rem;
                margin-bottom: 0.5rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .profile-value {
                display: block;
                color: #37474f;
                font-size: 1.2rem;
                font-weight: 500;
            }

            @media (max-width: 768px) {
                .profile-details {
                    padding: 0 1rem;
                    flex-direction: column;
                }
                
                .profile-card {
                    padding: 1.5rem;
                    width:auto;
                }

                .profile-item {
                    flex-direction: row;
                    padding: 1rem;
                }

                .profile-icon {
                    width: 40px;
                    height: 40px;
                    margin-right: 1rem;
                }

                .profile-icon i {
                    font-size: 1.2rem;
                }

                .profile-label {
                    font-size: 0.9rem;
                }

                .profile-value {
                    font-size: 1.1rem;
                }
            }
            </style>
            <!-- Modern card section with Font Awesome icons -->
            <div class="card-container">
                <div class="card">
                    <div class="icon-wrapper">
                        <i class="fas fa-calendar-check fa-3x" style="color: #4CAF50;"></i>
                    </div>
                    <h3>
                        <i class="fas fa-chart-line"></i> 
                        Total Events Created
                    </h3>
                    <p class="stat-number">
                        <?php
                        // Query to count the total number of events created
                        $stmt = $conn->prepare("SELECT COUNT(*) as total_events FROM events");
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $eventData = $result->fetch_assoc();
                        echo $eventData['total_events']; // Removed span and counter class
                        ?>
                    </p>
                </div>
                <div class="card">
                    <div class="icon-wrapper">
                        <i class="fas fa-user-friends fa-3x" style="color: #2196F3;"></i>
                    </div>
                    <h3>
                        <i class="fas fa-hands-helping"></i>
                        Total Volunteers
                    </h3>
                    <p class="stat-number">
                        <?php
                        // Count total number of event participants
                        $stmt = $conn->prepare("SELECT COUNT(*) as participant_count FROM event_participants");
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $participantData = $result->fetch_assoc();
                        echo $participantData['participant_count']; // Removed span and counter class
                        ?>
                    </p>
                </div>
                <div class="card">
                    <div class="icon-wrapper">
                        <i class="fas fa-city fa-3x" style="color: #FF9800;"></i>
                    </div>
                    <h3>
                        <i class="fas fa-building"></i>
                        Organizations
                    </h3>
                    <p class="stat-number">
                        <?php
                        // Count number of organization accounts
                        $stmt = $conn->prepare("SELECT COUNT(*) as org_count FROM accounts WHERE account_type = 'organization'");
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $orgData = $result->fetch_assoc();
                        echo $orgData['org_count']; // Removed span and counter class
                        ?>
                    </p>
                </div>
            </div>

            <style>
            .card-container {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-around;
                gap: 2rem;
                padding: 2rem;
            }

            .card {
                background: #ffffff;
                border-radius: 20px;
                padding: 2rem;
                flex: 1 1 30%;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                transition: all 0.3s ease;
                border: 1px solid rgba(255,255,255,0.2);
                backdrop-filter: blur(10px);
                margin-bottom: 2rem;
            }

            .card:hover {
                transform: translateY(-10px);
                box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            }

            .icon-wrapper {
                background: rgba(255,255,255,0.9);
                width: 80px;
                height: 80px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 1.5rem;
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            }

            .card h3 {
                color: #2c3e50;
                font-size: 1.5rem;
                font-weight: 600;
                margin: 1rem 0;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
            }

            .stat-number {
                font-size: 2.5rem;
                font-weight: 700;
                color: #2c3e50; /* Changed from gradient to solid color */
                margin: 1rem 0;
            }

            @media (max-width: 768px) {
                .card-container {
                    flex-direction: column;
                    padding: 1rem;
                }
                
                .card {
                    margin-bottom: 1rem;
                    width:auto;
                }
            }
            </style>

            <?php
            $stmt->close();
            $conn->close();
            ?>
        </div>

        <?php if ($_SESSION['account_type'] === 'organization'): ?>
            <br />
            <?php include 'components/group.php'; ?>
        <?php else: ?>
            <?php include 'components/joined.php'; ?>
        <?php endif; ?>
    </div>
</body>

</html>