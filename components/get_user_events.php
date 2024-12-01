<?php

include(__DIR__ . '/../config/db.php');

// Fetch events created by the current user
$stmt = $conn->prepare("SELECT * FROM events WHERE created_by = ? ORDER BY event_date");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>Your Events</h2>
<?php if ($result->num_rows > 0): ?>
    <div class="events-grid">
        <?php while ($event = $result->fetch_assoc()): ?>
            <div class="event-card">
                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                <p><strong>Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($event['event_date'])); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                <p><strong>Max Volunteers:</strong> <?php echo htmlspecialchars($event['max_volunteers']); ?></p>
                <p class="description"><?php echo htmlspecialchars($event['description']); ?></p>
                <button class="delete-btn" onclick="deleteEvent(<?php echo $event['id']; ?>)">Delete Event</button>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <p>You haven't created any events yet.</p>
<?php endif; ?> 