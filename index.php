<?php
require_once 'db.php';

$contactSuccess = '';
$contactError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $message   = trim($_POST['message'] ?? '');

    if ($full_name === '' || $email === '' || $message === '') {
        $contactError = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $contactError = 'Please enter a valid email address.';
    } else {
        $stmt = $mysqli->prepare('INSERT INTO contact_messages (full_name, email, message) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $full_name, $email, $message);
        if ($stmt->execute()) {
            $contactSuccess = 'Your message has been sent successfully!';
        } else {
            $contactError = 'Something went wrong. Please try again.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KUET Math Club - Exploring Mathematics Beyond Boundaries</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- ============ NAVBAR ============ -->
    <nav>
        <div class="navbar-container">
            <div class="logo">KUET Math Club</div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="events.php">Events</a></li>
                <li><a href="team.php">Team</a></li>
                <li><a href="gallery.php">Gallery</a></li>
                <li><a href="index.php#contact">Contact</a></li>
                <li><a href="login.php" class="btn-login">Login</a></li>
                <li><a href="signup.php" class="btn-signup">Sign Up</a></li>
            </ul>
        </div>
    </nav>

    <!-- ============ HERO SECTION ============ -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1>KUET Math Club</h1>
            <p class="hero-tagline">Exploring Mathematics Beyond Boundaries</p>
            <p class="hero-description">
                Join a vibrant community of mathematics enthusiasts dedicated to exploring 
                the beauty and elegance of mathematical concepts through collaborative learning, 
                engaging workshops, and exciting competitions.
            </p>
            <div class="hero-buttons">
                <a href="signup.php" class="btn btn-primary">Join Us</a>
                <a href="about.php" class="btn btn-secondary">Learn More</a>
            </div>
        </div>
    </section>


    <!-- ============ FOOTER ============ -->
    <footer>
        <div class="footer-content">
            <div class="footer-brand">KUET Math Club</div>
            <p>Exploring Mathematics Beyond Boundaries</p>
            <div class="footer-links">
                <a href="index.php">Home</a>
                <a href="about.php">About</a>
                <a href="events.php">Events</a>
                <a href="team.php">Team</a>
                <a href="gallery.php">Gallery</a>
                <a href="#contact">Contact</a>
            </div>
            <div class="footer-divider"></div>
            <p class="copyright">&copy; <?php echo date('Y'); ?> KUET Math Club. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>