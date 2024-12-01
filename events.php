<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include(__DIR__ . '/config/db.php');

$eventName = isset($_GET['eventName']) ? $_GET['eventName'] : '';

if (empty($eventName)) {
    try {
        $stmt = $conn->prepare("SELECT id, title, description, event_date, location, max_volunteers FROM events");
        $stmt->execute();
        $eventResult = $stmt->get_result();
        $event = $eventResult->fetch_all(MYSQLI_ASSOC);

        if (!$event || count($event) === 0) {
            echo "No events found.";
            exit;
        }

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        exit;
    }
} else {
    try {
        // Fetch event details
        $stmt = $conn->prepare("SELECT id, title, description, event_date, location, max_volunteers, created_by FROM events WHERE title = ?");
        $stmt->bind_param("s", $eventName);
        $stmt->execute();
        $eventResult = $stmt->get_result();
        $event = $eventResult->fetch_assoc();

        if (!$event) {
            header('Location: events.php');
            exit;
        }

        

        // Fetch participants
        $eventId = $event['id'];
        $participantsStmt = $conn->prepare("
            SELECT accounts.id,
                   CONCAT(accounts.first_name, ' ', accounts.last_name) AS name,
                   accounts.username,
                   accounts.email,
                   accounts.picture
            FROM event_participants 
            JOIN accounts ON event_participants.participant_id = accounts.id 
            WHERE event_participants.event_id = ?
        ");
        $participantsStmt->bind_param("i", $eventId);
        $participantsStmt->execute();
        $participantsResult = $participantsStmt->get_result();
        $participants = $participantsResult->fetch_all(MYSQLI_ASSOC);

        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo htmlspecialchars($event['title']); ?> - Event Details</title>
            <link rel="stylesheet" href="./styles/navbar.css">
            <link rel="stylesheet" href="./styles/events.css">
            <link rel="stylesheet" href="./styles/group.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
            <style>
                .event-details-container {
                    max-width: 800px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: #f9f9f9;
                    border-radius: 8px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                }
                .participants-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                .participants-table th, .participants-table td {
                    padding: 10px;
                    text-align: left;
                    border-bottom: 1px solid #ddd;
                }
                .participants-table th {
                    background-color: #f2f2f2;
                }
                .participants-table img {
                    width: 50px;
                    height: 50px;
                    border-radius: 50%;
                }
            </style>
        </head>
        <body>
        <?php include(__DIR__ . '/components/navbar.php'); ?>

        <div class="event-details-container">
            <h2><?php echo htmlspecialchars($event['title']); ?></h2>
            <p><strong>Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($event['event_date'])); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
            <p><strong>Max Volunteers:</strong> <?php echo htmlspecialchars($event['max_volunteers']); ?></p>
            <p class="description"><?php echo htmlspecialchars($event['description']); ?></p>
            <h3>Participants</h3>
            <?php if ($_SESSION['username'] === $event['created_by']): ?>
                <?php if (count($participants) > 0): ?>
                    <table class="participants-table">
                        <thead>
                            <tr>
                                <th>Picture</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($participants as $participant): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo htmlspecialchars($participant['picture'] ?: 'assets/' . (strpos($participant['name'], 'female') !== false ? 'female.png' : 'man.png')); ?>" alt="<?php echo htmlspecialchars($participant['name']); ?>'s picture">
                                    </td>
                                    <td><?php echo htmlspecialchars($participant['name']); ?></td>
                                    <td><?php echo htmlspecialchars($participant['username']); ?></td>
                                    <td><?php echo htmlspecialchars($participant['email']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No participants have signed up for this event yet.</p>
                <?php endif; ?>
            <?php else: ?>
                <p>Participants list is only visible to the event organizer.</p>
            <?php endif; ?>
        </div>

        </body>
        </html>
        <?php
        exit;
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events</title>
    <link rel="stylesheet" href="./styles/navbar.css">
    <link rel="stylesheet" href="./styles/events.css">
    <link rel="stylesheet" href="./styles/group.css">
    <link href="https://fonts.googleapis.com/css2?family=Satisfy&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<?php include(__DIR__ . '/components/navbar.php'); ?>

<div class="search-bar">
    <div class="search-wrapper">
        <i class="fas fa-search search-icon"></i>
        <input type="text" id="searchInput" class="search-input" placeholder="Search events by title...">
        <button class="search-clear" id="clearSearch" onclick="document.getElementById('searchInput').value = ''; document.getElementById('searchInput').dispatchEvent(new Event('input'));">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>

<div class="events-container">
    <h2 class="profile-heading">Available Events</h2>
    <div class="events-grid" id="eventsGrid">
        <?php foreach ($event as $event): ?>
            <div class="event-card" data-title="<?php echo strtolower(htmlspecialchars($event['title'])); ?>">
                <div class="event-content">
                    <h3><i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($event['title']); ?></h3>
                    <p><i class="fas fa-clock"></i> <?php echo htmlspecialchars($event['event_date']) ?></p>
                    <p><i class="fas fa-map-marker-alt"></i> Location: <?php echo htmlspecialchars($event['location']) ?></p>
                    <p class="description"><i class="fas fa-info-circle"></i> <?php echo substr(htmlspecialchars($event['description']), 0, 160) ?>...</p>
                    <p><i class="fas fa-users"></i> Max Volunteers: <?php echo htmlspecialchars($event['max_volunteers']); ?></p>
                </div>
                <div class="button-container">
                    <a class="openModal" href="events.php?eventName=<?php echo urlencode($event['title']); ?>">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                    <?php if (isset($_SESSION['username'])) : ?>
                        <button class="join-btn" onclick="confirmJoin(<?php echo $event['id']; ?>)">
                            <i class="fas fa-user-plus"></i> Join
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div id="eventModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <div id="modalContent"></div>
    </div>
</div>



<script>

function confirmJoin(eventId) {
    fetch('./components/join_event.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ event_id: eventId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Successfully joined the event!');
            closeModal();
            location.reload();
        } else {
            alert('Error joining event: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while joining the event.');
    });
}

function closeModal() {
    document.getElementById('eventModal').style.display = 'none';
}

document.getElementById('searchInput').addEventListener('input', function() {
    const searchValue = this.value.toLowerCase();
    const eventCards = document.querySelectorAll('.event-card');

    eventCards.forEach(card => {
        const title = card.getAttribute('data-title');
        if (title.includes(searchValue)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});
</script>

</body>
</html>