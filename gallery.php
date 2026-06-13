<?php require_once 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KUET Math Club - Gallery</title>
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

    <!-- ============ GALLERY SECTION ============ -->
    <section class="gallery" id="gallery">
        <div class="container">
            <h2 class="section-title">Gallery</h2>
            <div class="gallery-grid">
                <?php
                $result = $mysqli->query("
                    SELECT * FROM gallery
                    ORDER BY display_order ASC
                ");
                while ($item = $result->fetch_assoc()):
                ?>
                <div class="gallery-item">
    <img src="<?php echo htmlspecialchars($item['image_filename']); ?>"
         alt="<?php echo htmlspecialchars($item['title']); ?>"
         style="width:100%; height:100%; object-fit:cover;">
    <div class="gallery-overlay">
        <p><?php echo htmlspecialchars($item['title']); ?></p>
    </div>
</div>
                <?php endwhile; ?>
            </div>