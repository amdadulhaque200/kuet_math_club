<?php require_once 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KUET Math Club - Our Team</title>
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

    <!-- ============ EXECUTIVE COMMITTEE ============ -->
    <section class="team" id="team">
        <div class="container">
            <h2 class="section-title">Executive Committee</h2>
            <div class="team-grid">
                <?php
                $result = $mysqli->query("
                    SELECT * FROM team_members
                    WHERE role_type = 'executive'
                    ORDER BY display_order ASC
                ");
                while ($m = $result->fetch_assoc()):
                ?>
                <div class="member-card">
                    <div class="member-image"><?php echo htmlspecialchars($m['photo_emoji']); ?></div>
                    <h3><?php echo htmlspecialchars($m['name']); ?></h3>
                    <p class="member-position"><?php echo htmlspecialchars($m['position']); ?></p>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- ============ GENERAL MEMBERS ============ -->
    <section class="team" id="members">
        <div class="container">
            <h2 class="section-title">Members</h2>
            <div class="team-grid">
                <?php
                $result = $mysqli->query("
                    SELECT * FROM team_members
                    WHERE role_type = 'member'
                    ORDER BY display_order ASC
                ");
                while ($m = $result->fetch_assoc()):
                ?>
                <div class="member-card">
                    <div class="member-image"><?php echo htmlspecialchars($m['photo_emoji']); ?></div>
                    <h3><?php echo htmlspecialchars($m['name']); ?></h3>
                    <p class="member-position"><?php echo htmlspecialchars($m['position']); ?></p>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- ============ ACHIEVEMENTS ============ -->
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
                <a href="index.php#contact">Contact</a>
            </div>
            <div class="footer-divider"></div>
            <p class="copyright">&copy; <?php echo date('Y'); ?> KUET Math Club. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>