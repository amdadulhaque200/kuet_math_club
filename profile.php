<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$uid     = (int) $_SESSION['user_id'];
$success = '';
$error   = '';

// Fetch full user data
$stmt = $mysqli->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_photo'])) {
    if ($_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($_FILES['profile_photo']['type'], $allowed)) {
            $error = 'Only JPG, PNG, GIF, WEBP images are allowed.';
        } else {
            $ext         = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
            $newFilename = 'profile_' . $uid . '_' . time() . '.' . $ext;
            $uploadPath  = __DIR__ . '/uploads/profiles/' . $newFilename;

            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $uploadPath)) {
                // Delete old photo
                if (!empty($user['profile_photo']) && file_exists(__DIR__ . '/' . $user['profile_photo'])) {
                    unlink(__DIR__ . '/' . $user['profile_photo']);
                }
                $photoPath = 'uploads/profiles/' . $newFilename;
                $upd = $mysqli->prepare('UPDATE users SET profile_photo = ? WHERE id = ?');
                $upd->bind_param('si', $photoPath, $uid);
                $upd->execute();
                $upd->close();
                $user['profile_photo'] = $photoPath;
                $success = 'Profile photo updated successfully!';
            } else {
                $error = 'Upload failed. Check that uploads/profiles/ folder exists and is writable.';
            }
        }
    } else {
        $error = 'No file selected or upload error.';
    }
}

// Back link depending on role
$backLink  = $_SESSION['user_role'] === 'executive' ? 'dashboard.php' : 'member_dashboard.php';
$backLabel = $_SESSION['user_role'] === 'executive' ? 'Executive Dashboard' : 'My Dashboard';

// Initials for avatar fallback
$parts    = explode(' ', trim($user['full_name']));
$initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - KUET Math Club</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #f4f3fa; }

        .profile-nav {
            background: #fff;
            border-bottom: 1px solid #e0e0e0;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
        }
        .profile-nav-inner {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 60px;
        }
        .profile-nav-brand {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            color: var(--primary-blue);
            text-decoration: none;
        }
        .profile-nav-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .nav-back-btn {
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--accent-purple);
            text-decoration: none;
            border: 1px solid #d1c4e9;
            background: #f3f0fb;
            transition: all 0.2s;
        }
        .nav-back-btn:hover { background: #ede7f6; }
        .nav-logout-btn {
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 600;
            color: #c62828;
            text-decoration: none;
            border: 1px solid #ffcdd2;
            background: #fff;
            transition: all 0.2s;
        }
        .nav-logout-btn:hover { background: #ffebee; }

        .profile-wrapper {
            max-width: 900px;
            margin: 2.5rem auto 4rem;
            padding: 0 2rem;
        }

        /* ── PROFILE HERO CARD ── */
        .profile-hero {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        .profile-hero-banner {
            height: 100px;
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-purple));
        }
        .profile-hero-body {
            padding: 0 2rem 2rem;
            display: flex;
            align-items: flex-end;
            gap: 1.5rem;
            margin-top: -45px;
            flex-wrap: wrap;
        }
        .profile-photo-wrap {
            position: relative;
            flex-shrink: 0;
        }
        .profile-photo-wrap img,
        .profile-photo-wrap .avatar-initials {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            border: 4px solid #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            object-fit: cover;
        }
        .profile-photo-wrap .avatar-initials {
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-purple));
            color: #fff;
            font-size: 1.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Montserrat', sans-serif;
        }
        .profile-photo-edit {
            position: absolute;
            bottom: 4px;
            right: 4px;
            width: 26px;
            height: 26px;
            background: var(--accent-purple);
            color: #fff;
            border-radius: 50%;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 2px solid #fff;
            transition: background 0.2s;
        }
        .profile-photo-edit:hover { background: var(--primary-blue); }
        .profile-hero-info {
            flex: 1;
            padding-top: 50px;
        }
        .profile-hero-info h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: #222;
            margin-bottom: 0.25rem;
        }
        .role-badge {
            display: inline-block;
            padding: 0.25rem 0.8rem;
            border-radius: 20px;
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .role-executive { background: #e3f2fd; color: #1565c0; }
        .role-member    { background: #ede7f6; color: #5e35b1; }

        /* ── INFO CARD ── */
        .info-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.07);
            padding: 1.75rem 2rem;
            margin-bottom: 1.5rem;
        }
        .info-card h2 {
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 1.25rem;
            padding-bottom: 0.6rem;
            border-bottom: 2px solid #ede7f6;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem 2rem;
        }
        .info-item label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #aaa;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 0.25rem;
        }
        .info-item span {
            font-size: 0.95rem;
            color: #333;
            font-weight: 500;
        }
        .info-item span.empty { color: #ccc; font-style: italic; }

        /* ── PHOTO UPLOAD CARD ── */
        .upload-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.07);
            padding: 1.75rem 2rem;
            margin-bottom: 1.5rem;
        }
        .upload-card h2 {
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 1.25rem;
            padding-bottom: 0.6rem;
            border-bottom: 2px solid #ede7f6;
        }
        .upload-zone {
            border: 2px dashed #d1c4e9;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            background: #faf8ff;
            cursor: pointer;
            transition: all 0.3s;
        }
        .upload-zone:hover { border-color: var(--accent-purple); background: #f3f0fb; }
        .upload-zone input[type="file"] { display: none; }
        .upload-zone .upload-icon { font-size: 2rem; margin-bottom: 0.5rem; }
        .upload-zone p { color: #888; font-size: 0.85rem; margin: 0.25rem 0 0; }
        .upload-zone .upload-label {
            display: inline-block;
            color: var(--accent-purple);
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
        }
        #photo-preview { margin-top: 1rem; display: none; text-align: center; }
        #photo-preview img { max-height: 160px; border-radius: 12px; border: 2px solid #ede7f6; }
        #photo-name { margin-top: 0.4rem; font-size: 0.8rem; color: #888; }
        .btn-upload {
            margin-top: 1rem;
            padding: 0.7rem 2rem;
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-purple));
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
        }
        .btn-upload:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(126,87,194,0.3); }

        .alert { padding: 0.8rem 1rem; border-radius: 10px; margin-bottom: 1.25rem; font-size: 0.88rem; }
        .alert-success { background: #e8f5e9; color: #1b5e20; border: 1px solid #b7dfb9; }
        .alert-error   { background: #fdecea; color: #b3261e; border: 1px solid #f5c2c0; }
    </style>
</head>
<body>

    <!-- NAV -->
    <nav class="profile-nav">
        <div class="profile-nav-inner">
            <a href="index.php" class="profile-nav-brand">∑ KUET Math Club</a>
            <div class="profile-nav-right">
                <a href="<?php echo $backLink; ?>" class="nav-back-btn">← <?php echo $backLabel; ?></a>
                <a href="logout.php" class="nav-logout-btn">⏻ Logout</a>
            </div>
        </div>
    </nav>

    <div class="profile-wrapper">

        <?php if ($success): ?>
            <div class="alert alert-success">✅ <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error">⚠️ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- PROFILE HERO -->
        <div class="profile-hero">
            <div class="profile-hero-banner"></div>
            <div class="profile-hero-body">
                <div class="profile-photo-wrap">
                    <?php if (!empty($user['profile_photo'])): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_photo']); ?>" alt="Profile Photo">
                    <?php else: ?>
                        <div class="avatar-initials"><?php echo $initials; ?></div>
                    <?php endif; ?>
                    <div class="profile-photo-edit" onclick="document.getElementById('photo-input').click()" title="Change photo">✏️</div>
                </div>
                <div class="profile-hero-info">
                    <h1><?php echo htmlspecialchars($user['full_name']); ?></h1>
                    <span class="role-badge role-<?php echo $user['role']; ?>">
                        <?php echo $user['role'] === 'executive' ? '⭐ Executive' : '👤 Member'; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- PERSONAL INFO -->
        <div class="info-card">
            <h2>👤 Personal Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>Full Name</label>
                    <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                </div>
                <div class="info-item">
                    <label>Email Address</label>
                    <span><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <div class="info-item">
                    <label>Phone Number</label>
                    <span><?php echo htmlspecialchars($user['phone'] ?? ''); ?> </span>
                </div>
                <div class="info-item">
                    <label>Hall</label>
                    <span><?php echo !empty($user['hall']) ? htmlspecialchars($user['hall']) : '<span class="empty">Not set</span>'; ?></span>
                </div>
            </div>
        </div>

        <!-- ACADEMIC INFO -->
        <div class="info-card">
            <h2>🎓 Academic Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>Student ID</label>
                    <span><?php echo !empty($user['student_id']) ? htmlspecialchars($user['student_id']) : '<span class="empty">Not set</span>'; ?></span>
                </div>
                <div class="info-item">
                    <label>Department</label>
                    <span><?php echo !empty($user['department']) ? htmlspecialchars($user['department']) : '<span class="empty">Not set</span>'; ?></span>
                </div>
                <div class="info-item">
                    <label>Batch / Year</label>
                    <span><?php echo !empty($user['batch']) ? htmlspecialchars($user['batch']) : '<span class="empty">Not set</span>'; ?></span>
                </div>
                <div class="info-item">
                    <label>Role</label>
                    <span><?php echo ucfirst($user['role']); ?></span>
                </div>
            </div>
        </div>

        <!-- PHOTO UPLOAD -->
        <div class="upload-card">
            <h2>📷 Profile Photo</h2>
            <form method="POST" action="profile.php" enctype="multipart/form-data">
                <div class="upload-zone" onclick="document.getElementById('photo-input').click()">
                    <input type="file" id="photo-input" name="profile_photo" accept="image/*" onchange="previewPhoto(this)">
                    <div class="upload-icon">📁</div>
                    <label class="upload-label">Click to select a photo</label>
                    <p>Supports JPG, PNG, GIF, WEBP &mdash; max recommended 2MB</p>
                    <div id="photo-name"></div>
                    <div id="photo-preview"><img id="photo-preview-img" src="" alt="Preview"></div>
                </div>
                <br>
                <button type="submit" class="btn-upload">⬆️ Upload Photo</button>
            </form>
        </div>

    </div>

    <script>
        function previewPhoto(input) {
            const preview = document.getElementById('photo-preview');
            const img     = document.getElementById('photo-preview-img');
            const name    = document.getElementById('photo-name');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    img.src = e.target.result;
                    preview.style.display = 'block';
                    name.textContent = '📎 ' + input.files[0].name;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

</body>
</html>