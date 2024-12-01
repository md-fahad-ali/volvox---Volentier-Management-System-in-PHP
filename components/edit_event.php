<?php
include(__DIR__ . '/../config/db.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = $_POST['event_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $eventDate = $_POST['event_date'];
    $location = $_POST['location'];
    $maxVolunteers = $_POST['max_volunteers'];

    // Prepare and execute the update query
    $stmt = $conn->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, location = ?, max_volunteers = ? WHERE id = ?");
    $result = $stmt->execute([$title, $description, $eventDate, $location, $maxVolunteers, $eventId]);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update event']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?> 