<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include(__DIR__ . '/../config/db.php');

try {
    // Fetch all events from the events table
    $stmt = $conn->prepare("SELECT id, title, description, event_date, location, max_volunteers FROM events");
    $stmt->execute();
    $result = $stmt->get_result();
    $events = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    echo 'Error fetching events: ' . $e->getMessage();
    exit;
}

if ($events):
?>
    <div class="events-container">
        <h2 class="profile-heading">Available Events</h2>
        <div class="events-grid">
            <?php foreach ($events as $event): ?>
                <div class="event-card">
                    <div class="event-content">
                        <h3><i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($event['title']); ?></h3>
                        <p><i class="fas fa-clock"></i> <?php echo htmlspecialchars($event['event_date']); ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> Location: <?php echo htmlspecialchars($event['location']); ?></p>
                        <p class="description"><i class="fas fa-info-circle"></i> <?php echo substr(htmlspecialchars($event['description']), 0, 160); ?>...</p>
                        <p><i class="fas fa-users"></i> Max Volunteers: <?php echo htmlspecialchars($event['max_volunteers']); ?></p>
                    </div>
                    <div class="button-container">
                        <button class="openModal" onclick="joinEvent(<?php echo $event['id']; ?>)">
                            <i class="fas fa-handshake"></i> Join Event
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php else: ?>
    <div class="no-events">
        <i class="fas fa-calendar-times"></i>
        <p>No events found.</p>
    </div>
<?php endif; ?>

<style>
.no-events {
    text-align: center;
    padding: 2rem;
    color: #666;
}

.no-events i {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.profile-heading {
    font-size: 2.2rem;
    color: #1a237e;
    margin: 2rem 0;
    text-align: center;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.events-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.events-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 35px;
    margin-top: 20px;
    padding-bottom: 20px;
}

.event-card {
    background: linear-gradient(145deg, #ffffff, #f5f7fa);
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    padding: 2rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 1px solid rgba(255,255,255,0.18);
    display: flex;
    flex-direction: column;
}

.event-content {
    flex-grow: 1;
}

.button-container {
    margin-top: auto;
    padding-top: 1rem;
}

.event-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.15);
}

.event-card h3 {
    color: #1a237e;
    margin-bottom: 1rem;
    font-size: 1.5rem;
}

.event-card p {
    margin: 0.8rem 0;
    color: #555;
}

.event-card i {
    color: #1a73e8;
    margin-right: 0.5rem;
    width: 20px;
    text-align: center;
}

.event-card button.openModal {
    width: 100%;
    padding: 0.8rem;
    background: #1a73e8;
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.event-card button.openModal:hover {
    background: #1557b0;
    transform: translateY(-2px);
}

.openModal > .fa-handshake{
    color:white;
}

@media (max-width: 768px) {
    .events-grid {
        grid-template-columns: 1fr;
    }
    
    .event-card {
        margin: 1rem;
        min-height: 350px; /* Adjust for mobile */
    }
}
</style>

<script>
function joinEvent(eventId) {
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
</script>