<?php
session_start();
require_once 'db.php';

// ── HANDLE REGISTRATION ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_event_id'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
    $event_id = (int) $_POST['register_event_id'];
    $user_id  = (int) $_SESSION['user_id'];

    $stmt = $mysqli->prepare('INSERT IGNORE INTO event_registrations (event_id, user_id) VALUES (?, ?)');
    $stmt->bind_param('ii', $event_id, $user_id);
    $stmt->execute();
    $stmt->close();

    header('Location: events.php?registered=' . $event_id);
    exit;
}

// ── GET USER'S REGISTERED EVENTS ──
$registeredEvents = [];
if (isset($_SESSION['user_id'])) {
    $uid = (int) $_SESSION['user_id'];
    $res = $mysqli->query("SELECT event_id FROM event_registrations WHERE user_id = $uid");
    while ($row = $res->fetch_assoc()) {
        $registeredEvents[] = $row['event_id'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KUET Math Club - Events</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .btn-register {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.5rem 1.2rem;
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-purple));
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(126,87,194,0.3);
        }
        .btn-registered {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.5rem 1.2rem;
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #b7dfb9;
            border-radius: 6px;
            font-size: 0.88rem;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
        }
        .btn-login-prompt {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.5rem 1.2rem;
            background: transparent;
            color: var(--accent-purple);
            border: 1px solid var(--accent-purple);
            border-radius: 6px;
            font-size: 0.88rem;
            font-weight: 600;
            text-decoration: none;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }
        .btn-login-prompt:hover {
            background: var(--accent-purple);
            color: white;
        }
        .reg-success {
            background: #e8f5e9;
            color: #1b5e20;
            border: 1px solid #b7dfb9;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            text-align: center;
        }
    </style>
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
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['user_role'] === 'executive'): ?>
                        <li><a href="dashboard.php" class="btn-login">Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php" class="btn-signup">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="btn-login">Login</a></li>
                    <li><a href="signup.php" class="btn-signup">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- ============ EVENTS SECTION ============ -->
    <section class="events" id="events">
        <div class="container">

            <?php if (isset($_GET['registered'])): ?>
                <div class="reg-success">✅ You have successfully registered for the event!</div>
            <?php endif; ?>

            <?php
            $categoryLabels = [
                'workshop'      => '📚 Workshops & Learning Sessions',
                'competition'   => '🏆 Competitions',
                'seminar'       => '🎤 Seminars & Talks',
                'fun'           => '🎮 Fun & Interactive Events',
                'collaborative' => '👥 Collaborative & Group Activities',
                'special'       => '⭐ Special Programs',
            ];

            foreach (['upcoming' => 'Upcoming Events', 'completed' => 'Past Events'] as $status => $heading):
                $result = $mysqli->query("
                    SELECT * FROM events
                    WHERE status = '$status'
                    ORDER BY FIELD(category,'workshop','competition','seminar','fun','collaborative','special'), id ASC
                ");

                if ($result->num_rows === 0) continue;

                $grouped = [];
                while ($row = $result->fetch_assoc()) {
                    $grouped[$row['category']][] = $row;
                }
            ?>

            <h2 class="section-title"><?php echo $heading; ?></h2>

            <?php foreach ($grouped as $cat => $events): ?>
                <div class="event-category">
                    <h3 class="category-title"><?php echo $categoryLabels[$cat] ?? '📌 Events'; ?></h3>
                </div>
                <div class="events-grid">
                    <?php foreach ($events as $e): ?>
                    <div class="event-card">
                        <?php if ($e['image_filename']): ?>
                        <div class="event-image">
                            <img src="<?php echo htmlspecialchars($e['image_filename']); ?>"
                                 alt="<?php echo htmlspecialchars($e['title']); ?>"
                                 style="width:100%; height:280px; object-fit:cover; border-radius:8px 8px 0 0;">
                        </div>
                        <?php endif; ?>
                        <div class="event-content">
                            <p class="event-date">
                                <?php echo htmlspecialchars($e['event_date']); ?>
                                <?php if ($e['event_time']): ?> | <?php echo htmlspecialchars($e['event_time']); ?><?php endif; ?>
                            </p>
                            <h3><?php echo htmlspecialchars($e['title']); ?></h3>
                            <p><?php echo htmlspecialchars($e['description']); ?></p>
                            <?php if ($e['location']): ?>
                            <p class="event-location">📍 <?php echo htmlspecialchars($e['location']); ?></p>
                            <?php endif; ?>

                            <?php if ($status === 'upcoming'): ?>
                                <?php if (!isset($_SESSION['user_id'])): ?>
                                    <a href="login.php" class="btn-login-prompt">🔐 Login to Register</a>
                                <?php elseif (in_array($e['id'], $registeredEvents)): ?>
                                    <span class="btn-registered">✅ Registered</span>
                                <?php else: ?>
                                    <form method="POST" action="events.php" style="display:inline;">
                                        <input type="hidden" name="register_event_id" value="<?php echo $e['id']; ?>">
                                        <button type="submit" class="btn-register">📝 Register Now</button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>

                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <?php endforeach; ?>

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