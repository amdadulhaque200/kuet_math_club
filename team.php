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
    <style>
        .team-page-hero {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--accent-purple) 100%);
            padding: 7rem 2rem 4rem;
            text-align: center;
            color: #fff;
        }
        .team-page-hero h1 { font-family: 'Montserrat', sans-serif; font-size: 2.5rem; font-weight: 700; margin-bottom: 0.75rem; }
        .team-page-hero p  { font-size: 1.05rem; opacity: 0.85; max-width: 500px; margin: 0 auto; }
        .stats-bar { display: flex; gap: 2rem; justify-content: center; flex-wrap: wrap; margin-top: 1.5rem; }
        .stat-item { text-align: center; }
        .stat-item .stat-num  { font-size: 2rem; font-weight: 700; color: #fff; font-family: 'Montserrat', sans-serif; line-height: 1; }
        .stat-item .stat-label{ font-size: 0.82rem; opacity: 0.75; margin-top: 0.25rem; }

        .team-section { padding: 4rem 2rem; }
        .team-section:nth-child(even) { background: #f9f8ff; }
        .team-section .container { max-width: 1100px; margin: 0 auto; }

        .section-label {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-purple));
            color: #fff; font-size: 0.78rem; font-weight: 700;
            letter-spacing: 2px; text-transform: uppercase;
            padding: 0.35rem 1rem; border-radius: 20px; margin-bottom: 0.75rem;
        }
        .section-heading { font-family: 'Montserrat', sans-serif; font-size: 1.9rem; font-weight: 700; color: var(--primary-blue); margin-bottom: 0.4rem; }
        .section-subtext { color: #888; font-size: 0.92rem; margin-bottom: 2.5rem; }

        /* Executive cards */
        .exec-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.5rem; }
        .exec-card {
            background: #fff; border-radius: 16px; padding: 2rem 1.5rem 1.5rem;
            text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #ede7f6; transition: transform 0.25s ease, box-shadow 0.25s ease;
            position: relative; overflow: hidden;
        }
        .exec-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
            background: linear-gradient(90deg, var(--primary-blue), var(--accent-purple));
        }
        .exec-card:hover { transform: translateY(-6px); box-shadow: 0 12px 30px rgba(94,53,177,0.15); }
        .exec-avatar {
            width: 80px; height: 80px; border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-purple));
            color: #fff; font-size: 1.8rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem; font-family: 'Montserrat', sans-serif;
        }
        .exec-avatar img {
            width: 80px; height: 80px; border-radius: 50%;
            object-fit: cover; display: block;
            border: 3px solid #ede7f6;
            margin: 0 auto 1rem;
        }
        .exec-card h3 { font-size: 1rem; font-weight: 700; color: var(--primary-blue); margin-bottom: 0.25rem; font-family: 'Montserrat', sans-serif; }
        .exec-card .position-badge { display: inline-block; background: #ede7f6; color: #5e35b1; font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.75rem; border-radius: 20px; margin-bottom: 1rem; }
        .exec-card .meta-row { display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.8rem; color: #888; margin-bottom: 0.3rem; }
        .exec-card .meta-row span { color: #555; font-weight: 500; }

        /* Member cards */
        .member-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 1.2rem; }
        .member-card-new {
            background: #fff; border-radius: 14px; padding: 1.5rem 1rem;
            text-align: center; box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid #f0f0f0; transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .member-card-new:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .member-avatar {
            width: 64px; height: 64px; border-radius: 50%;
            background: linear-gradient(135deg, #e8eaf6, #e1f5fe);
            color: var(--primary-blue); font-size: 1.2rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 0.85rem; font-family: 'Montserrat', sans-serif;
            border: 2px solid #e8eaf6;
        }
        .member-avatar img {
            width: 64px; height: 64px; border-radius: 50%;
            object-fit: cover; display: block;
            border: 2px solid #e8eaf6;
            margin: 0 auto 0.85rem;
        }
        .member-card-new h3 { font-size: 0.88rem; font-weight: 600; color: #333; margin-bottom: 0.3rem; font-family: 'Montserrat', sans-serif; }
        .member-card-new .dept { font-size: 0.76rem; color: #888; margin-bottom: 0.25rem; }
        .member-card-new .batch-pill { display: inline-block; background: #f3f0fb; color: #7e57c2; font-size: 0.72rem; font-weight: 600; padding: 0.2rem 0.6rem; border-radius: 20px; }

        .empty-state { text-align: center; padding: 3rem 1rem; color: #bbb; font-size: 0.95rem; }
        .empty-state .empty-icon { font-size: 2.5rem; margin-bottom: 0.75rem; }
    </style>
</head>
<body>

    <nav>
        <div class="navbar-container">
            <div class="logo">KUET Math Club</div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="events.php">Events</a></li>
                <li><a href="team.php" class="active">Team</a></li>
                <li><a href="gallery.php">Gallery</a></li>
                <li><a href="index.php#contact">Contact</a></li>
                <li><a href="login.php" class="btn-login">Login</a></li>
                <li><a href="signup.php" class="btn-signup">Sign Up</a></li>
            </ul>
        </div>
    </nav>

    <?php
        $execCount   = $mysqli->query("SELECT COUNT(*) AS c FROM users WHERE role = 'executive'")->fetch_assoc()['c'];
        $memberCount = $mysqli->query("SELECT COUNT(*) AS c FROM users WHERE role = 'member'")->fetch_assoc()['c'];
        $totalCount  = $execCount + $memberCount;

        $executives = $mysqli->query("SELECT full_name, department, batch, hall, profile_photo FROM users WHERE role = 'executive' ORDER BY full_name ASC");
        $members    = $mysqli->query("SELECT full_name, department, batch, profile_photo FROM users WHERE role = 'member' ORDER BY full_name ASC");
    ?>

    <!-- HERO -->
    <section class="team-page-hero">
        <h1>Meet Our Team</h1>
        <p>The passionate minds driving KUET Math Club forward</p>
        <div class="stats-bar">
            <div class="stat-item"><div class="stat-num"><?php echo $totalCount; ?></div><div class="stat-label">Total Members</div></div>
            <div class="stat-item"><div class="stat-num"><?php echo $execCount; ?></div><div class="stat-label">Executives</div></div>
            <div class="stat-item"><div class="stat-num"><?php echo $memberCount; ?></div><div class="stat-label">General Members</div></div>
        </div>
    </section>

    <!-- EXECUTIVES -->
    <section class="team-section" id="executives">
        <div class="container">
            <span class="section-label">Leadership</span>
            <h2 class="section-heading">Executive Committee</h2>
            <p class="section-subtext">Our elected executives who organise events, manage operations and lead the club.</p>
            <?php if ($executives->num_rows === 0): ?>
                <div class="empty-state"><div class="empty-icon">👤</div><p>No executives found.</p></div>
            <?php else: ?>
            <div class="exec-grid">
                <?php while ($e = $executives->fetch_assoc()):
                    $parts    = explode(' ', trim($e['full_name']));
                    $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                ?>
                <div class="exec-card">
                    <?php if (!empty($e['profile_photo'])): ?>
                        <img src="<?php echo htmlspecialchars($e['profile_photo']); ?>" class="exec-avatar" alt="<?php echo htmlspecialchars($e['full_name']); ?>" style="width:80px;height:80px;border-radius:50%;object-fit:cover;margin:0 auto 1rem;display:block;border:3px solid #ede7f6;">
                    <?php else: ?>
                        <div class="exec-avatar"><?php echo $initials; ?></div>
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($e['full_name']); ?></h3>
                    <span class="position-badge">Executive</span>
                    <?php if (!empty($e['department'])): ?>
                    <div class="meta-row">🎓 <span><?php echo htmlspecialchars($e['department']); ?></span></div>
                    <?php endif; ?>
                    <?php if (!empty($e['batch'])): ?>
                    <div class="meta-row">📅 <span>Batch <?php echo htmlspecialchars($e['batch']); ?></span></div>
                    <?php endif; ?>
                    <?php if (!empty($e['hall'])): ?>
                    <div class="meta-row">🏠 <span><?php echo htmlspecialchars($e['hall']); ?></span></div>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- MEMBERS -->
    <section class="team-section" id="members">
        <div class="container">
            <span class="section-label">Community</span>
            <h2 class="section-heading">General Members</h2>
            <p class="section-subtext">Registered members who are part of the KUET Math Club community.</p>
            <?php if ($members->num_rows === 0): ?>
                <div class="empty-state"><div class="empty-icon">👥</div><p>No members yet. <a href="signup.php" style="color:var(--accent-purple);">Be the first to join!</a></p></div>
            <?php else: ?>
            <div class="member-grid">
                <?php while ($m = $members->fetch_assoc()):
                    $parts    = explode(' ', trim($m['full_name']));
                    $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                ?>
                <div class="member-card-new">
                    <?php if (!empty($m['profile_photo'])): ?>
                        <img src="<?php echo htmlspecialchars($m['profile_photo']); ?>" style="width:64px;height:64px;border-radius:50%;object-fit:cover;display:block;margin:0 auto 0.85rem;border:2px solid #e8eaf6;" alt="<?php echo htmlspecialchars($m['full_name']); ?>">
                    <?php else: ?>
                        <div class="member-avatar"><?php echo $initials; ?></div>
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($m['full_name']); ?></h3>
                    <?php if (!empty($m['department'])): ?>
                    <p class="dept"><?php echo htmlspecialchars($m['department']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($m['batch'])): ?>
                    <span class="batch-pill">Batch <?php echo htmlspecialchars($m['batch']); ?></span>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

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