<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

// Enable CORS if needed
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");



// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "volunteer";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => "Connection failed: " . $conn->connect_error]));
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

// Check if user is an organization
if ($_SESSION['account_type'] !== 'organization') {
    echo json_encode(['error' => 'Access denied. Only certain users can create events.']);
    exit;
}

// Handle GET request for fetching events
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    try {
        $stmt = $conn->prepare("SELECT * FROM events WHERE created_by = ? ORDER BY event_date DESC");
        $stmt->bind_param("s", $_SESSION['username']);
        $stmt->execute();
        
        // Get the result
        $result = $stmt->get_result();
        
        // Fetch all rows as an associative array
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
        
        // Clear any previous output and send JSON response
        ob_clean();
        echo json_encode($events);
        
        $stmt->close();
    } catch (Exception $e) {
        ob_clean();
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Handle POST request for creating events
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $org_id = $_SESSION['user_id'];
    $title = isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '';
    $description = isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '';
    $event_date = isset($_POST['event_date']) ? $_POST['event_date'] : '';
    $location = isset($_POST['location']) ? htmlspecialchars($_POST['location']) : '';
    $max_volunteers = isset($_POST['max_volunteers']) ? (int)$_POST['max_volunteers'] : 0;
    $created_by = $_SESSION['username'];

    if (!$title || !$event_date) {
        echo json_encode(['error' => 'Required fields are missing']);
        exit;
    }

    try {
        $sql = "INSERT INTO events (org_id, title, description, event_date, location, max_volunteers, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssss", $org_id, $title, $description, $event_date, $location, $max_volunteers, $created_by);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Event created successfully',
                'event_id' => $conn->insert_id
            ]);
        } else {
            throw new Exception('Failed to create event');
        }

        $stmt->close();
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode([
            'error' => 'Database error',
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}

$conn->close();
?>
