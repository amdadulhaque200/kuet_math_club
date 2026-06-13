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

    <!-- ============ ACHIEVEMENTS SECTION ============ -->
    <section class="achievements">
        <div class="container">
            <h2 class="section-title">Our Achievements</h2>
            <div class="achievements-grid">
                <div class="achievement-item">
                    <div class="achievement-number">500+</div>
                    <div class="achievement-title">Active Members</div>
                </div>
                <div class="achievement-item">
                    <div class="achievement-number">50+</div>
                    <div class="achievement-title">Events Conducted</div>
                </div>
                <div class="achievement-item">
                    <div class="achievement-number">15+</div>
                    <div class="achievement-title">Research Papers</div>
                </div>
                <div class="achievement-item">
                    <div class="achievement-number">12</div>
                    <div class="achievement-title">National Awards</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ CONTACT SECTION ============ -->
    <section class="contact" id="contact">
        <div class="container">
            <h2 class="section-title">Get In Touch</h2>
            <div class="contact-wrapper">

                <?php if ($contactSuccess !== ''): ?>
                    <div style="background:#e8f5e9; color:#1b5e20; border:1px solid #b7dfb9;
                                padding:0.75rem 1rem; border-radius:6px; margin-bottom:1.5rem; font-size:0.95rem;">
                        <?php echo htmlspecialchars($contactSuccess); ?>
                    </div>
                <?php endif; ?>

                <?php if ($contactError !== ''): ?>
                    <div style="background:#fdecea; color:#b3261e; border:1px solid #f5c2c0;
                                padding:0.75rem 1rem; border-radius:6px; margin-bottom:1.5rem; font-size:0.95rem;">
                        <?php echo htmlspecialchars($contactError); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php#contact">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name"
                               placeholder="Enter your full name"
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email"
                               placeholder="Enter your email address"
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message"
                                  placeholder="Write your message here..." required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="submit-btn">Send Message</button>
                </form>

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