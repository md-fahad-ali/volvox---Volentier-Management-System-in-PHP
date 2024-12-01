<?php

$isOrganization = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'organization';
?>


<nav class="navbar">
    <div class="logo">
        <a href="./" style="color: black; text-decoration: none; font-family: 'Satisfy', cursive;">Volvox</a>
    </div>
    <ul id="nav-links">
        <li class="link"><a href="./">Home</a></li>
        <li class="link"><a href="./events.php">Events</a></li>
        <li class="link"><a href="./profile.php">Profile</a></li>
        
        <li class="link mobile-only"><a style="color:white;" href="./logout.php">Logout</a></li>
    </ul>
    <a href="./logout.php" class="get-started desktop-only">Logout</a>
    <div class="hamburger" onClick="toggleMenu()">
        <div></div>
        <div></div>
        <div></div>
    </div>
</nav> 

<script>
    function toggleMenu() {
        const navLinks = document.getElementById('nav-links');
        navLinks.classList.toggle('show');
    }
</script> 