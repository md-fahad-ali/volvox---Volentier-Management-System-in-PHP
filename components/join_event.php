<?php
session_start();
include(__DIR__ . '/../config/db.php');

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

// Debugging output
error_log("Received data: " . print_r($data, true));
error_log("Session user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set'));

if (isset($data['event_id']) && isset($_SESSION['user_id'])) {
    $eventId = $data['event_id'];
    $participantId = $_SESSION['user_id'];

    try {
        // Check if the user is already joined
        $checkStmt = $conn->prepare("SELECT * FROM event_participants WHERE event_id = ? AND participant_id = ?");
        $checkStmt->bind_param("ii", $eventId, $participantId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            echo json_encode(['success' => false, 'error' => 'You have already joined this event.']);
            exit;
        }

        // Check if the event has reached its maximum capacity
        $capacityStmt = $conn->prepare("SELECT COUNT(*) as participant_count, e.max_volunteers FROM event_participants ep JOIN events e ON ep.event_id = e.id WHERE ep.event_id = ?");
        $capacityStmt->bind_param("i", $eventId);
        $capacityStmt->execute();
        $capacityResult = $capacityStmt->get_result();
        $capacityData = $capacityResult->fetch_assoc();

        if ($capacityData['participant_count'] >= $capacityData['max_volunteers']) {
            echo json_encode(['success' => false, 'error' => 'This event has reached its maximum number of volunteers.']);
            exit;
        }

        // Insert the join record
        $stmt = $conn->prepare("INSERT INTO event_participants (event_id, participant_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $eventId, $participantId);
        $stmt->execute();

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Failed to join event: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
}
?> 