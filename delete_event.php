<?php
session_start();
header('Content-Type: application/json');

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_id = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
    
    // Verify that the user owns this event
    $stmt = $conn->prepare("SELECT created_by FROM events WHERE id = ? AND created_by = ?");
    $stmt->bind_param("is", $event_id, $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['error' => 'Event not found or unauthorized']);
        exit;
    }
    
    // Delete the event
    $delete_stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
    $delete_stmt->bind_param("i", $event_id);
    
    if ($delete_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);
    } else {
        echo json_encode(['error' => 'Failed to delete event']);
    }
    
    $delete_stmt->close();
} else {
    echo json_encode(['error' => 'Invalid request method']);
}

$conn->close();
?> 