<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include(__DIR__ . '/../config/db.php');
?>

<!-- Add Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="group.css">

<style>
.openModal {
  background: #48bb78;
  color: white;
  padding: 1rem 2rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  margin: 2rem;
  transition: all 0.3s ease;
}

.openModal:hover {
  background: #38a169;
  transform: translateY(-2px);
}

.button-group {
  position: absolute;
  bottom: 10px;
  left: 10px;
  right: 10px;
  display: flex;
  justify-content: space-between;
}

.event-card {
  position: relative;
  padding-bottom: 60px; /* Ensure space for buttons */
}
</style>

  <button id="openModal" class="openModal"><i class="fas fa-plus"></i> Create Event</button>


  <div id="modal" class="modal">
    <div class="modal-content">
      <span id="closeModal" class="close">&times;</span>
      <h2>Create New Event</h2>
      <form id="eventForm">
        <div class="form-group">
          <label for="title">Event Title</label>
          <input type="text" id="title" name="title" required>
        </div>

        <div class="form-group">
          <label for="description">Description</label>
          <textarea id="description" name="description" rows="4"></textarea>
        </div>

        <div class="form-group">
          <label for="event_date">Event Date and Time</label>
          <input type="datetime-local" id="event_date" name="event_date" required>
        </div>

        <div class="form-group">
          <label for="location">Location</label>
          <input type="text" id="location" name="location">
        </div>

        <div class="form-group">
          <label for="max_volunteers">Maximum Volunteers</label>
          <input type="number" id="max_volunteers" name="max_volunteers" min="1">
        </div>

        <input type="hidden" id="created_by" name="created_by"
          value="<?php echo htmlspecialchars($_SESSION['username']); ?>">

        <button type="submit" class="submit-btn">Create Event</button>
      </form>
    </div>
  </div>

  <!-- Edit Event Modal -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <span id="closeEditModal" class="close">&times;</span>
      <h2>Edit Event</h2>
      <form id="editEventForm">
        <input type="hidden" id="eventId" name="event_id">

        <div class="form-group">
          <label for="editTitle">Event Title</label>
          <input type="text" id="editTitle" name="title" required>
        </div>

        <div class="form-group">
          <label for="editDescription">Description</label>
          <textarea id="editDescription" name="description" rows="4"></textarea>
        </div>

        <div class="form-group">
          <label for="editEventDate">Event Date and Time</label>
          <input type="datetime-local" id="editEventDate" name="event_date" required>
        </div>

        <div class="form-group">
          <label for="editLocation">Location</label>
          <input type="text" id="editLocation" name="location">
        </div>

        <div class="form-group">
          <label for="editMaxVolunteers">Maximum Volunteers</label>
          <input type="number" id="editMaxVolunteers" name="max_volunteers" min="1">
        </div>

        <button type="submit" class="submit-btn">Save Changes</button>
      </form>
    </div>
  </div>

  <div class="events-container">

  </div>

  <script>
    const modal = document.getElementById('modal');
    const openModal = document.getElementById('openModal');
    const closeModal = document.getElementById('closeModal');
    const eventForm = document.getElementById('eventForm');

    openModal.addEventListener('click', () => {
      modal.style.display = 'flex';
    });

    closeModal.addEventListener('click', () => {
      modal.style.display = 'none';
    });

    window.addEventListener('click', (event) => {
      if (event.target === modal) {
        modal.style.display = 'none';
      }
    });

    console.log('Script loaded');

    async function refreshEventsList() {
      try {
        const response = await fetch('./create_event.php?action=get', {
          method: 'GET',
          credentials: 'same-origin'
        });

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.text();
        let events;
        try {
          events = JSON.parse(data);
        } catch (parseError) {
          console.error('Error parsing JSON:', parseError);
          document.querySelector('.events-container').innerHTML = '<p>Error loading events.</p>';
          return;
        }

        const eventsContainer = document.querySelector('.events-container');

        if (!Array.isArray(events)) {
          console.error('Events is not an array:', events);
          eventsContainer.innerHTML = '<p>Error loading events.</p>';
          return;
        }

        if (events.length === 0) {
          eventsContainer.innerHTML = '<p>No events available.</p>';
          return;
        }

        const eventsHtml = `
            <div class="events-grid">
                ${events.map(event => `
                    <div class="event-card">
                        <h3><i class="fas fa-calendar-alt"></i> ${escapeHtml(event.title)}</h3>
                        <p><i class="far fa-clock"></i> ${formatDate(event.event_date)}</p>
                        <p><i class="fas fa-map-marker-alt"></i> ${escapeHtml(event.location)}</p>
                        <p class="description"><i class="fas fa-info-circle"></i> ${escapeHtml(event.description).slice(0, 160)}...</p>
                        <p><i class="fas fa-users"></i> Max Volunteers: ${escapeHtml(event.max_volunteers)}</p>
                        
                        <div class="button-group">
                            <button onclick="openEditModal(${event.id}, '${escapeHtml(event.title)}', '${escapeHtml(event.event_date)}', '${escapeHtml(event.location)}', '${escapeHtml(event.description)}', ${event.max_volunteers})" class="edit-btn">
                              <i class="fas fa-edit"></i> Edit
                            </button>
                            <button onclick="deleteEvent(${event.id})" class="delete-btn">
                              <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;

        eventsContainer.innerHTML = eventsHtml;
      } catch (error) {
        console.error('Error refreshing events:', error);
        const eventsContainer = document.querySelector('.events-container');
        eventsContainer.innerHTML = '<p>Error loading events. Please try again later.</p>';
      }
    }

    function formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleString();
    }

    function escapeHtml(unsafe) {
      if (unsafe == null) return '';
      return unsafe
        .toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
    }

    eventForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      try {
        const formData = new FormData();

        formData.append('title', document.getElementById('title').value);
        formData.append('description', document.getElementById('description').value);
        formData.append('event_date', document.getElementById('event_date').value);
        formData.append('location', document.getElementById('location').value);
        formData.append('max_volunteers', document.getElementById('max_volunteers').value);
        formData.append('created_by', document.getElementById('created_by').value);

        const response = await fetch('./create_event.php', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        const data = await response.text();
        console.log('Raw response:', data);

        try {
          const jsonData = JSON.parse(data);
          if (jsonData.success) {
            modal.style.display = 'none';
            e.target.reset();
            await refreshEventsList();
          } else {
            throw new Error(jsonData.error || 'Failed to create event');
          }
        } catch (jsonError) {
          console.error('JSON parsing error:', jsonError);
        }
      } catch (error) {
        console.error('Error:', error);
        alert('Error: ' + error.message);
      }
    });

    async function deleteEvent(eventId) {
      if (!confirm('Are you sure you want to delete this event?')) {
        return;
      }

      try {
        const formData = new FormData();
        formData.append('event_id', eventId);

        const response = await fetch('./delete_event.php', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin'
        });

        const data = await response.json();

        if (data.success) {
          alert('Event deleted successfully!');
          await refreshEventsList();
        } else {
          throw new Error(data.error || 'Failed to delete event');
        }
      } catch (error) {
        console.error('Error:', error);
        alert('Error deleting event: ' + error.message);
      }
    }

    document.addEventListener('DOMContentLoaded', async () => {
      console.log("hi")
      await refreshEventsList();
    });

    function openEditModal(id, title, eventDate, location, description, maxVolunteers) {
      const modal = document.getElementById('editModal');
      modal.style.display = 'flex';

      document.getElementById('eventId').value = id;
      document.getElementById('editTitle').value = title;
      document.getElementById('editEventDate').value = eventDate;
      document.getElementById('editLocation').value = location;
      document.getElementById('editDescription').value = description;
      document.getElementById('editMaxVolunteers').value = maxVolunteers;
    }

    const closeEditModal = document.getElementById('closeEditModal');

    closeEditModal.addEventListener('click', () => {
      document.getElementById('editModal').style.display = 'none';
    });

    window.addEventListener('click', (event) => {
      if (event.target === modal) {
        modal.style.display = 'none';
      } else if (event.target === document.getElementById('editModal')) {
        document.getElementById('editModal').style.display = 'none';
      }
    });

    const editEventForm = document.getElementById('editEventForm');

    editEventForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      try {
        const formData = new FormData(editEventForm);

        const response = await fetch('./components/edit_event.php', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        const data = await response.json();

        if (data.success) {
          alert('Event updated successfully!');
          document.getElementById('editModal').style.display = 'none';
          await refreshEventsList();
        } else {
          throw new Error(data.error || 'Failed to update event');
        }
      } catch (error) {
        console.error('Error:', error);
        alert('Error updating event: ' + error.message);
      }
    });
  </script>

</body>

</html>