<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
if ($_SESSION['user_role'] === 'executive') {
    header('Location: dashboard.php');
    exit;
}

$uid = (int) $_SESSION['user_id'];

// Fetch user with profile photo
$stmt = $mysqli->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch registered events
$regResult = $mysqli->prepare("
    SELECT e.id, e.title, e.category, e.event_date, e.event_time, e.location,
           e.status, e.image_filename, r.registered_at
    FROM event_registrations r
    JOIN events e ON r.event_id = e.id
    WHERE r.user_id = ?
    ORDER BY r.registered_at DESC
");
$regResult->bind_param('i', $uid);
$regResult->execute();
$myEvents = $regResult->get_result();
$regResult->close();

// Initials fallback
$parts    = explode(' ', trim($user['full_name']));
$initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - KUET Math Club</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #f4f3fa; }

        /* ── NAV ── */
        .mb-nav {
            background: #fff;
            border-bottom: 1px solid #e0e0e0;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
        }
        .mb-nav-inner {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 60px;
        }
        .mb-nav-brand {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .mb-nav-brand .brand-icon {
            width: 32px; height: 32px;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-purple));
            color: #fff;
            font-size: 1rem;
            display: flex; align-items: center; justify-content: center;
        }
        .mb-nav-brand span {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--primary-blue);
        }
        .mb-nav-tabs {
            display: flex;
            list-style: none;
            margin: 0; padding: 0;
            gap: 2px;
        }
        .mb-nav-tabs a {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 500;
            color: #666;
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .mb-nav-tabs a:hover { background: #f3f0fb; color: #7e57c2; }
        .mb-nav-tabs a.active { background: #ede7f6; color: #5e35b1; font-weight: 600; }
        .mb-nav-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .mb-nav-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            border: 2px solid #ede7f6;
            object-fit: cover;
        }
        .mb-nav-avatar-initials {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-purple));
            color: #fff;
            font-size: 0.72rem;
            font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Montserrat', sans-serif;
        }
        .mb-home-btn {
            padding: 6px 12px; border-radius: 8px;
            font-size: 0.8rem; font-weight: 500;
            color: #555; text-decoration: none;
            border: 1px solid #ddd; transition: all 0.2s;
        }
        .mb-home-btn:hover { background: #f5f5f5; }
        .mb-logout-btn {
            padding: 6px 12px; border-radius: 8px;
            font-size: 0.8rem; font-weight: 600;
            color: #c62828; text-decoration: none;
            border: 1px solid #ffcdd2; background: #fff; transition: all 0.2s;
        }
        .mb-logout-btn:hover { background: #ffebee; }

        /* ── WRAPPER ── */
        .mb-wrapper {
            max-width: 1000px;
            margin: 2rem auto 4rem;
            padding: 0 2rem;
        }

        /* ── WELCOME BANNER ── */
        .welcome-banner {
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-purple));
            border-radius: 16px;
            padding: 2rem 2.5rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .welcome-photo {
            width: 72px; height: 72px;
            border-radius: 50%;
            border: 3px solid rgba(255,255,255,0.5);
            object-fit: cover;
            flex-shrink: 0;
        }
        .welcome-initials {
            width: 72px; height: 72px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            border: 3px solid rgba(255,255,255,0.5);
            color: #fff;
            font-size: 1.5rem;
            font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Montserrat', sans-serif;
            flex-shrink: 0;
        }
        .welcome-text h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.3rem;
        }
        .welcome-text p { color: rgba(255,255,255,0.8); font-size: 0.88rem; margin: 0; }
        .welcome-actions { margin-left: auto; display: flex; gap: 10px; flex-wrap: wrap; }
        .btn-profile-link {
            padding: 8px 18px;
            background: rgba(255,255,255,0.2);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.4);
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            backdrop-filter: blur(4px);
        }
        .btn-profile-link:hover { background: rgba(255,255,255,0.35); }

        /* ── STAT CARDS ── */
        .stat-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-card {
            background: #fff;
            border-radius: 14px;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border-left: 4px solid var(--accent-purple);
        }
        .stat-card .stat-num {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-blue);
            line-height: 1;
            margin-bottom: 0.3rem;
        }
        .stat-card .stat-label { font-size: 0.8rem; color: #999; font-weight: 500; }

        /* ── SECTION CARD ── */
        .section-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.07);
            padding: 1.75rem 2rem;
            margin-bottom: 1.5rem;
            scroll-margin-top: 72px;
        }
        .section-card h2 {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 1.25rem;
            padding-bottom: 0.6rem;
            border-bottom: 2px solid #ede7f6;
        }

        /* ── EVENT CARDS ── */
        .events-list { display: flex; flex-direction: column; gap: 1rem; }
        .event-item {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            padding: 1rem;
            border-radius: 12px;
            border: 1px solid #f0f0f0;
            background: #fafafa;
            transition: box-shadow 0.2s;
        }
        .event-item:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .event-thumb {
            width: 80px; height: 60px;
            border-radius: 8px;
            object-fit: cover;
            flex-shrink: 0;
            background: #ede7f6;
        }
        .event-thumb-placeholder {
            width: 80px; height: 60px;
            border-radius: 8px;
            background: linear-gradient(135deg, #ede7f6, #e3f2fd);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        .event-info { flex: 1; }
        .event-info h3 { font-size: 0.95rem; font-weight: 600; color: #222; margin-bottom: 0.3rem; }
        .event-meta { display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 0.4rem; }
        .event-meta span { font-size: 0.78rem; color: #888; }
        .event-meta span strong { color: #555; }
        .badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.72rem; font-weight: 600; }
        .badge-upcoming  { background: #e8f5e9; color: #2e7d32; }
        .badge-completed { background: #f3e5f5; color: #6a1b9a; }
        .reg-date { font-size: 0.76rem; color: #bbb; margin-top: 0.25rem; }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #bbb;
        }
        .empty-state .empty-icon { font-size: 2.5rem; margin-bottom: 0.75rem; }
        .empty-state a { color: var(--accent-purple); text-decoration: none; font-weight: 500; }

        /* ── PROFILE PREVIEW INSIDE DASHBOARD ── */
        .profile-preview {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }
        .profile-preview-left { flex-shrink: 0; text-align: center; }
        .profile-preview-photo,
        .profile-preview-initials {
            width: 80px; height: 80px;
            border-radius: 50%;
            margin-bottom: 0.75rem;
            object-fit: cover;
        }
        .profile-preview-initials {
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-purple));
            color: #fff;
            font-size: 1.6rem;
            font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Montserrat', sans-serif;
        }
        .btn-edit-profile {
            display: inline-block;
            padding: 5px 14px;
            background: #f3f0fb;
            color: var(--accent-purple);
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid #d1c4e9;
            transition: all 0.2s;
        }
        .btn-edit-profile:hover { background: #ede7f6; }
        .profile-preview-right { flex: 1; }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.85rem 2rem;
        }
        .info-item label {
            display: block;
            font-size: 0.72rem;
            font-weight: 600;
            color: #aaa;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 0.2rem;
        }
        .info-item span { font-size: 0.9rem; color: #333; font-weight: 500; }
    </style>
</head>
<body>

    <!-- ── NAV ── -->
    <nav class="mb-nav">
        <div class="mb-nav-inner">
            <a href="index.php" class="mb-nav-brand">
                <div class="brand-icon">∑</div>
                <span>Math Club</span>
            </a>
            <ul class="mb-nav-tabs">
                <li><a href="#my-profile" class="active">👤 Profile</a></li>
                <li><a href="#my-events">📋 My Events</a></li>
            </ul>
            <div class="mb-nav-right">
                <?php if (!empty($user['profile_photo'])): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_photo']); ?>" class="mb-nav-avatar" alt="">
                <?php else: ?>
                    <div class="mb-nav-avatar-initials"><?php echo $initials; ?></div>
                <?php endif; ?>
                <a href="index.php" class="mb-home-btn">🏠 Home</a>
                <a href="logout.php" class="mb-logout-btn">⏻ Logout</a>
            </div>
        </div>
    </nav>

    <div class="mb-wrapper">

        <!-- WELCOME BANNER -->
        <div class="welcome-banner">
            <?php if (!empty($user['profile_photo'])): ?>
                <img src="<?php echo htmlspecialchars($user['profile_photo']); ?>" class="welcome-photo" alt="">
            <?php else: ?>
                <div class="welcome-initials"><?php echo $initials; ?></div>
            <?php endif; ?>
            <div class="welcome-text">
                <h1>Welcome, <?php echo htmlspecialchars(explode(' ', $user['full_name'])[0]); ?>! 👋</h1>
                <p><?php echo htmlspecialchars($user['department']); ?> &nbsp;·&nbsp; Batch <?php echo htmlspecialchars($user['batch']); ?> &nbsp;·&nbsp; <?php echo htmlspecialchars($user['hall']); ?></p>
            </div>
            <div class="welcome-actions">
                <a href="profile.php" class="btn-profile-link">✏️ Edit Profile &amp; Photo</a>
                <a href="events.php" class="btn-profile-link">📅 Browse Events</a>
            </div>
        </div>

        <!-- STATS -->
        <?php
        $totalReg     = $mysqli->prepare("SELECT COUNT(*) AS c FROM event_registrations WHERE user_id = ?");
        $totalReg->bind_param('i', $uid);
        $totalReg->execute();
        $totalRegCount = $totalReg->get_result()->fetch_assoc()['c'];
        $totalReg->close();

        $upcomingReg = $mysqli->prepare("SELECT COUNT(*) AS c FROM event_registrations r JOIN events e ON r.event_id = e.id WHERE r.user_id = ? AND e.status = 'upcoming'");
        $upcomingReg->bind_param('i', $uid);
        $upcomingReg->execute();
        $upcomingCount = $upcomingReg->get_result()->fetch_assoc()['c'];
        $upcomingReg->close();

        $completedReg = $mysqli->prepare("SELECT COUNT(*) AS c FROM event_registrations r JOIN events e ON r.event_id = e.id WHERE r.user_id = ? AND e.status = 'completed'");
        $completedReg->bind_param('i', $uid);
        $completedReg->execute();
        $completedCount = $completedReg->get_result()->fetch_assoc()['c'];
        $completedReg->close();
        ?>
        <div class="stat-row">
            <div class="stat-card">
                <div class="stat-num"><?php echo $totalRegCount; ?></div>
                <div class="stat-label">Events Joined</div>
            </div>
            <div class="stat-card" style="border-left-color:#2e7d32;">
                <div class="stat-num" style="color:#2e7d32;"><?php echo $upcomingCount; ?></div>
                <div class="stat-label">Upcoming</div>
            </div>
            <div class="stat-card" style="border-left-color:#6a1b9a;">
                <div class="stat-num" style="color:#6a1b9a;"><?php echo $completedCount; ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>

        <!-- PROFILE SUMMARY -->
        <div class="section-card" id="my-profile">
            <h2>👤 My Profile</h2>
            <div class="profile-preview">
                <div class="profile-preview-left">
                    <?php if (!empty($user['profile_photo'])): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_photo']); ?>" class="profile-preview-photo" alt="">
                    <?php else: ?>
                        <div class="profile-preview-initials"><?php echo $initials; ?></div>
                    <?php endif; ?>
                    <br>
                    <a href="profile.php" class="btn-edit-profile">✏️ Edit Profile</a>
                </div>
                <div class="profile-preview-right">
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Full Name</label>
                            <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Email</label>
                            <span><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Phone</label>
                            <span><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Student ID</label>
                            <span><?php echo htmlspecialchars($user['student_id'] ?? '-'); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Department</label>
                            <span><?php echo htmlspecialchars($user['department'] ?? '-'); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Batch</label>
                            <span><?php echo htmlspecialchars($user['batch'] ?? '-'); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Hall</label>
                            <span><?php echo htmlspecialchars($user['hall'] ?? '-'); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Role</label>
                            <span>Member</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MY EVENTS -->
        <div class="section-card" id="my-events">
            <h2>📋 My Registered Events</h2>
            <?php if ($myEvents->num_rows === 0): ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <p>You haven't registered for any events yet.<br>
                    <a href="events.php">Browse upcoming events →</a></p>
                </div>
            <?php else: ?>
            <div class="events-list">
                <?php while ($ev = $myEvents->fetch_assoc()): ?>
                <div class="event-item">
                    <?php if (!empty($ev['image_filename'])): ?>
                        <img src="<?php echo htmlspecialchars($ev['image_filename']); ?>" class="event-thumb" alt="">
                    <?php else: ?>
                        <div class="event-thumb-placeholder">📅</div>
                    <?php endif; ?>
                    <div class="event-info">
                        <h3><?php echo htmlspecialchars($ev['title']); ?></h3>
                        <div class="event-meta">
                            <span>📅 <strong><?php echo htmlspecialchars($ev['event_date']); ?></strong></span>
                            <?php if (!empty($ev['event_time'])): ?>
                            <span>🕐 <?php echo htmlspecialchars($ev['event_time']); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($ev['location'])): ?>
                            <span>📍 <?php echo htmlspecialchars($ev['location']); ?></span>
                            <?php endif; ?>
                            <span>🏷️ <?php echo ucfirst(htmlspecialchars($ev['category'])); ?></span>
                        </div>
                        <span class="badge badge-<?php echo $ev['status']; ?>"><?php echo ucfirst($ev['status']); ?></span>
                        <div class="reg-date">Registered on <?php echo date('d M Y', strtotime($ev['registered_at'])); ?></div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <script>
        const navLinks = document.querySelectorAll('.mb-nav-tabs a');
        const sections = ['my-profile', 'my-events'];

        function updateActive() {
            let current = '';
            sections.forEach(id => {
                const el = document.getElementById(id);
                if (el && window.scrollY >= el.offsetTop - 80) current = id;
            });
            navLinks.forEach(a => {
                a.classList.toggle('active', a.getAttribute('href') === '#' + current);
            });
        }
        window.addEventListener('scroll', updateActive);
        updateActive();
    </script>

</body>
</html>