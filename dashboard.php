<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'executive') {
    header('Location: login.php');
    exit;
}

$success = '';
$error   = '';
$editEvent = null;
$editGallery = null;

// ── DELETE EVENT ──
if (isset($_GET['delete'])) {
    $delId = (int) $_GET['delete'];
    $res = $mysqli->query("SELECT image_filename FROM events WHERE id = $delId");
    if ($row = $res->fetch_assoc()) {
        if ($row['image_filename'] && file_exists(__DIR__ . '/' . $row['image_filename'])) {
            unlink(__DIR__ . '/' . $row['image_filename']);
        }
    }
    $mysqli->query("DELETE FROM events WHERE id = $delId");
    header('Location: dashboard.php?deleted=1');
    exit;
}

// ── PROMOTE USER ──
if (isset($_GET['promote'])) {
    $uid = (int) $_GET['promote'];
    $mysqli->query("UPDATE users SET role = 'executive' WHERE id = $uid");
    header('Location: dashboard.php?usermsg=promoted');
    exit;
}

// ── DEMOTE USER ──
if (isset($_GET['demote'])) {
    $uid = (int) $_GET['demote'];
    if ($uid !== (int)$_SESSION['user_id']) {
        $mysqli->query("UPDATE users SET role = 'member' WHERE id = $uid");
        header('Location: dashboard.php?usermsg=demoted');
    } else {
        header('Location: dashboard.php?usermsg=selfdemote');
    }
    exit;
}

// ── DELETE GALLERY ──
if (isset($_GET['deletegallery'])) {
    $gid = (int) $_GET['deletegallery'];
    $res = $mysqli->query("SELECT image_filename FROM gallery WHERE id = $gid");
    if ($row = $res->fetch_assoc()) {
        if ($row['image_filename'] && file_exists(__DIR__ . '/' . $row['image_filename'])) {
            unlink(__DIR__ . '/' . $row['image_filename']);
        }
    }
    $mysqli->query("DELETE FROM gallery WHERE id = $gid");
    header('Location: dashboard.php?gallerymsg=deleted');
    exit;
}

// ── LOAD GALLERY ITEM FOR EDITING ──
if (isset($_GET['editgallery'])) {
    $gid = (int) $_GET['editgallery'];
    $res = $mysqli->query("SELECT * FROM gallery WHERE id = $gid");
    $editGallery = $res->fetch_assoc();
}

// ── LOAD EVENT FOR EDITING ──
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $res = $mysqli->query("SELECT * FROM events WHERE id = $editId");
    $editEvent = $res->fetch_assoc();
}

// ── SAVE GALLERY (ADD or UPDATE) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gallery_action'])) {
    $gid   = (int) ($_POST['gallery_id'] ?? 0);
    $title = trim($_POST['gallery_title'] ?? '');
    $order = (int) ($_POST['display_order'] ?? 99);
    $image_filename = trim($_POST['existing_gallery_image'] ?? '');

    if ($title === '') {
        $error = 'Please enter a title for the gallery image.';
    } else {
        if (isset($_FILES['gallery_image']) && $_FILES['gallery_image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($_FILES['gallery_image']['type'], $allowed)) {
                $error = 'Only JPG, PNG, GIF, WEBP images are allowed.';
            } else {
                $ext = pathinfo($_FILES['gallery_image']['name'], PATHINFO_EXTENSION);
                $newFilename = 'gallery_' . time() . '.' . $ext;
                $uploadPath  = __DIR__ . '/uploads/gallery/' . $newFilename;
                if (move_uploaded_file($_FILES['gallery_image']['tmp_name'], $uploadPath)) {
                    if ($image_filename && file_exists(__DIR__ . '/' . $image_filename)) {
                        unlink(__DIR__ . '/' . $image_filename);
                    }
                    $image_filename = 'uploads/gallery/' . $newFilename;
                } else {
                    $error = 'Image upload failed. Please try again.';
                }
            }
        }

        if ($error === '' && $image_filename !== '') {
            if ($gid > 0) {
                $stmt = $mysqli->prepare('UPDATE gallery SET title=?, image_filename=?, display_order=? WHERE id=?');
                $stmt->bind_param('ssii', $title, $image_filename, $order, $gid);
            } else {
                $stmt = $mysqli->prepare('INSERT INTO gallery (title, image_filename, display_order) VALUES (?, ?, ?)');
                $stmt->bind_param('ssi', $title, $image_filename, $order);
            }
            if ($stmt->execute()) {
                $success = $gid > 0 ? 'Gallery image updated!' : 'Gallery image added!';
                $editGallery = null;
            } else {
                $error = 'Something went wrong. Please try again.';
            }
            $stmt->close();
        } elseif ($error === '' && $image_filename === '') {
            $error = 'Please select an image to upload.';
        }
    }
}

// ── SAVE EVENT (ADD or UPDATE) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['gallery_action'])) {
    $id          = (int) ($_POST['event_id'] ?? 0);
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category    = $_POST['category'] ?? '';
    $event_date  = trim($_POST['event_date'] ?? '');
    $event_time  = trim($_POST['event_time'] ?? '');
    $location    = trim($_POST['location'] ?? '');
    $status      = $_POST['status'] ?? 'upcoming';

    if ($title === '' || $description === '' || $category === '' || $event_date === '') {
        $error = 'Please fill in all required fields.';
    } else {
        $image_filename = trim($_POST['existing_image'] ?? '');
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($_FILES['image']['type'], $allowed)) {
                $error = 'Only JPG, PNG, GIF, WEBP images are allowed.';
            } else {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $newFilename = 'event_' . time() . '.' . $ext;
                $uploadPath  = __DIR__ . '/uploads/events/' . $newFilename;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    if ($image_filename && file_exists(__DIR__ . '/' . $image_filename)) {
                        unlink(__DIR__ . '/' . $image_filename);
                    }
                    $image_filename = 'uploads/events/' . $newFilename;
                } else {
                    $error = 'Image upload failed. Please try again.';
                }
            }
        }

        if ($error === '') {
            if ($id > 0) {
                $stmt = $mysqli->prepare('UPDATE events SET title=?, description=?, category=?, event_date=?, event_time=?, location=?, image_filename=?, status=? WHERE id=?');
                $stmt->bind_param('ssssssssi', $title, $description, $category, $event_date, $event_time, $location, $image_filename, $status, $id);
            } else {
                $stmt = $mysqli->prepare('INSERT INTO events (title, description, category, event_date, event_time, location, image_filename, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('ssssssss', $title, $description, $category, $event_date, $event_time, $location, $image_filename, $status);
            }
            if ($stmt->execute()) {
                $success = $id > 0 ? 'Event updated successfully!' : 'Event added successfully!';
                $editEvent = null;
            } else {
                $error = 'Something went wrong. Please try again.';
            }
            $stmt->close();
        }
    }
}

if (isset($_GET['deleted']))    $success = 'Event deleted successfully!';
if (isset($_GET['gallerymsg']) && $_GET['gallerymsg'] === 'deleted') $success = 'Gallery image deleted!';
if (isset($_GET['usermsg'])) {
    if ($_GET['usermsg'] === 'promoted')   $success = 'User promoted to Executive!';
    if ($_GET['usermsg'] === 'demoted')    $success = 'User demoted to Member.';
    if ($_GET['usermsg'] === 'selfdemote') $error   = 'You cannot demote yourself!';
}

$events       = $mysqli->query('SELECT * FROM events ORDER BY id DESC');
$users        = $mysqli->query('SELECT id, full_name, email, department, student_id, batch, hall, role FROM users ORDER BY role DESC, full_name ASC');
$galleryItems = $mysqli->query('SELECT * FROM gallery ORDER BY display_order ASC');
$regResult    = $mysqli->query("
    SELECT e.title, e.event_date, u.full_name, u.email, u.student_id, u.department, u.batch, u.hall, r.registered_at
    FROM event_registrations r
    JOIN events e ON r.event_id = e.id
    JOIN users u ON r.user_id = u.id
    ORDER BY e.id ASC, r.registered_at ASC
");
$regGrouped = [];
while ($row = $regResult->fetch_assoc()) {
    $regGrouped[$row['title']][] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Executive Dashboard - KUET Math Club</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-wrapper {
            max-width: 1000px;
            margin: 100px auto 4rem;
            padding: 0 2rem;
        }
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .dashboard-header h1 {
            font-size: 1.8rem;
            color: var(--primary-blue);
            font-family: 'Montserrat', sans-serif;
        }
        .dashboard-header span { font-size: 0.95rem; color: var(--text-gray); }
        .card {
            background: var(--white);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }
        .card h2 {
            font-size: 1.3rem;
            color: var(--primary-blue);
            margin-bottom: 1.5rem;
            font-family: 'Montserrat', sans-serif;
            border-bottom: 2px solid var(--light-purple);
            padding-bottom: 0.75rem;
        }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .form-group { margin-bottom: 1.2rem; }
        .form-group label {
            display: block;
            margin-bottom: 0.4rem;
            color: var(--dark-gray);
            font-weight: 500;
            font-size: 0.9rem;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.65rem 0.9rem;
            border: 1px solid var(--border-gray);
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            color: var(--dark-gray);
            transition: border-color 0.3s ease;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent-purple);
            box-shadow: 0 0 0 3px rgba(126,87,194,0.1);
        }
        .form-group textarea { min-height: 100px; resize: vertical; }
        .file-upload-area {
            border: 2px dashed var(--border-gray);
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: var(--light-gray);
        }
        .file-upload-area:hover { border-color: var(--accent-purple); background: var(--light-purple); }
        .file-upload-area input[type="file"] { display: none; }
        .file-upload-area label { cursor: pointer; color: var(--accent-purple); font-weight: 500; font-size: 0.9rem; }
        .file-upload-area p { color: var(--text-gray); font-size: 0.8rem; margin-top: 0.4rem; }
        #image-preview { margin-top: 1rem; display: none; }
        #image-preview img { max-height: 150px; border-radius: 6px; border: 1px solid var(--border-gray); }
        #file-name { margin-top: 0.5rem; font-size: 0.82rem; color: var(--text-gray); }
        #gallery-preview { margin-top: 1rem; display: none; }
        #gallery-preview img { max-height: 150px; border-radius: 6px; border: 1px solid var(--border-gray); }
        #gallery-file-name { margin-top: 0.5rem; font-size: 0.82rem; color: var(--text-gray); }
        .current-image { margin-bottom: 0.75rem; }
        .current-image img { max-height: 80px; border-radius: 6px; border: 1px solid var(--border-gray); }
        .current-image p { font-size: 0.8rem; color: var(--text-gray); margin-top: 0.3rem; }
        .btn-submit {
            padding: 0.7rem 2rem;
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-purple));
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(126,87,194,0.3); }
        .btn-cancel {
            padding: 0.7rem 1.5rem;
            background: var(--light-gray);
            color: var(--text-gray);
            border: 1px solid var(--border-gray);
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            margin-left: 0.5rem;
            text-decoration: none;
            display: inline-block;
        }
        .alert { padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1.5rem; font-size: 0.9rem; }
        .alert-success { background: #e8f5e9; color: #1b5e20; border: 1px solid #b7dfb9; }
        .alert-error   { background: #fdecea; color: #b3261e; border: 1px solid #f5c2c0; }
        .events-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        .events-table th { background: var(--primary-blue); color: white; padding: 0.75rem 1rem; text-align: left; }
        .events-table td { padding: 0.7rem 1rem; border-bottom: 1px solid var(--border-gray); color: var(--text-gray); }
        .events-table tr:hover td { background: var(--light-gray); }
        .events-table img { width: 60px; height: 40px; object-fit: cover; border-radius: 4px; }
        .badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.78rem; font-weight: 600; }
        .badge-upcoming   { background: #e8f5e9; color: #2e7d32; }
        .badge-completed  { background: #f3e5f5; color: #6a1b9a; }
        .badge-executive  { background: #e3f2fd; color: #1565c0; }
        .badge-member     { background: #fff3e0; color: #e65100; }
        .action-btns { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .btn-edit {
            padding: 0.3rem 0.8rem;
            background: var(--accent-purple);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 0.78rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-delete {
            padding: 0.3rem 0.8rem;
            background: #e53935;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 0.78rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-promote {
            padding: 0.3rem 0.8rem;
            background: #2e7d32;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 0.78rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-demote {
            padding: 0.3rem 0.8rem;
            background: #e65100;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 0.78rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-edit:hover    { background: var(--secondary-blue); }
        .btn-delete:hover  { background: #b71c1c; }
        .btn-promote:hover { background: #1b5e20; }
        .btn-demote:hover  { background: #bf360c; }
        .section-divider {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--accent-purple);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 2rem 0 1rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-gray);
        }
    </style>
</head>
<body>

    <nav>
        <div class="navbar-container">
            <div class="logo">KUET Math Club</div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="events.php">Events</a></li>
                <li><a href="team.php">Team</a></li>
                <li><a href="gallery.php">Gallery</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="dashboard-wrapper">

        <div class="dashboard-header">
            <h1>⚙️ Executive Dashboard</h1>
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        </div>

        <!-- GLOBAL ALERTS -->
        <?php if ($success !== ''): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- ===== ADD / EDIT EVENT ===== -->
        <div class="card">
            <h2><?php echo $editEvent ? '✏️ Edit Event' : '➕ Add New Event'; ?></h2>
            <form method="POST" action="dashboard.php" enctype="multipart/form-data">
                <input type="hidden" name="event_id" value="<?php echo $editEvent['id'] ?? 0; ?>">
                <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($editEvent['image_filename'] ?? ''); ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" name="title" placeholder="Event title"
                               value="<?php echo htmlspecialchars($editEvent['title'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category" required>
                            <option value="">-- Select --</option>
                            <?php
                            $cats = ['workshop'=>'Workshop','competition'=>'Competition','seminar'=>'Seminar','fun'=>'Fun & Interactive','collaborative'=>'Collaborative','special'=>'Special'];
                            foreach ($cats as $val => $label):
                                $sel = ($editEvent['category'] ?? '') === $val ? 'selected' : '';
                            ?>
                            <option value="<?php echo $val; ?>" <?php echo $sel; ?>><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description *</label>
                    <textarea name="description" placeholder="Event description"><?php echo htmlspecialchars($editEvent['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Date *</label>
                        <input type="text" name="event_date" placeholder="e.g. July 10, 2026"
                               value="<?php echo htmlspecialchars($editEvent['event_date'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Time</label>
                        <input type="text" name="event_time" placeholder="e.g. 4:00 PM - 6:00 PM"
                               value="<?php echo htmlspecialchars($editEvent['event_time'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" name="location" placeholder="e.g. Room 301, Academic Building"
                               value="<?php echo htmlspecialchars($editEvent['location'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="upcoming"  <?php echo ($editEvent['status'] ?? '') === 'upcoming'  ? 'selected' : ''; ?>>Upcoming</option>
                            <option value="completed" <?php echo ($editEvent['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Event Banner Image</label>
                    <?php if (!empty($editEvent['image_filename'])): ?>
                    <div class="current-image">
                        <img src="<?php echo htmlspecialchars($editEvent['image_filename']); ?>" alt="Current banner">
                        <p>Current image — upload a new one to replace it</p>
                    </div>
                    <?php endif; ?>
                    <div class="file-upload-area" onclick="document.getElementById('image-input').click()">
                        <input type="file" id="image-input" name="image" accept="image/*" onchange="previewImage(this)">
                        <label>📁 Click to select image from your PC</label>
                        <p>Supports JPG, PNG, GIF, WEBP</p>
                        <div id="file-name"></div>
                        <div id="image-preview"><img id="preview-img" src="" alt="Preview"></div>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <?php echo $editEvent ? 'Update Event' : 'Add Event'; ?>
                </button>
                <?php if ($editEvent): ?>
                <a href="dashboard.php" class="btn-cancel">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- ===== ALL EVENTS LIST ===== -->
        <div class="card">
            <h2>📋 All Events</h2>
            <table class="events-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Banner</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($e = $events->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $e['id']; ?></td>
                        <td>
                            <?php if ($e['image_filename']): ?>
                            <img src="<?php echo htmlspecialchars($e['image_filename']); ?>" alt="">
                            <?php else: ?>
                            <span style="color:#ccc;">No image</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($e['title']); ?></td>
                        <td><?php echo htmlspecialchars($e['category']); ?></td>
                        <td><?php echo htmlspecialchars($e['event_date']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $e['status']; ?>">
                                <?php echo ucfirst($e['status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="dashboard.php?edit=<?php echo $e['id']; ?>" class="btn-edit">✏️ Edit</a>
                                <a href="dashboard.php?delete=<?php echo $e['id']; ?>"
                                   class="btn-delete"
                                   onclick="return confirm('Delete this event? This cannot be undone.')">🗑️ Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- ===== GALLERY MANAGEMENT ===== -->
        <div class="card">
            <h2>🖼️ <?php echo $editGallery ? 'Edit Gallery Image' : 'Add Gallery Image'; ?></h2>
            <form method="POST" action="dashboard.php" enctype="multipart/form-data">
                <input type="hidden" name="gallery_action" value="1">
                <input type="hidden" name="gallery_id" value="<?php echo $editGallery['id'] ?? 0; ?>">
                <input type="hidden" name="existing_gallery_image" value="<?php echo htmlspecialchars($editGallery['image_filename'] ?? ''); ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" name="gallery_title" placeholder="e.g. Math Fest 2026"
                               value="<?php echo htmlspecialchars($editGallery['title'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Display Order</label>
                        <input type="number" name="display_order" placeholder="e.g. 1"
                               value="<?php echo htmlspecialchars($editGallery['display_order'] ?? 99); ?>">
                    </div>
                </div>

                <?php if (!empty($editGallery['image_filename'])): ?>
                <div class="current-image">
                    <img src="<?php echo htmlspecialchars($editGallery['image_filename']); ?>" alt="Current">
                    <p>Current image — upload a new one to replace it</p>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Image *</label>
                    <div class="file-upload-area" onclick="document.getElementById('gallery-input').click()">
                        <input type="file" id="gallery-input" name="gallery_image" accept="image/*" onchange="previewGallery(this)">
                        <label>📁 Click to select image from your PC</label>
                        <p>Supports JPG, PNG, GIF, WEBP</p>
                        <div id="gallery-file-name"></div>
                        <div id="gallery-preview"><img id="gallery-preview-img" src="" alt="Preview"></div>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <?php echo $editGallery ? 'Update Image' : 'Add to Gallery'; ?>
                </button>
                <?php if ($editGallery): ?>
                <a href="dashboard.php" class="btn-cancel">Cancel</a>
                <?php endif; ?>
            </form>

            <p class="section-divider">Current Gallery Images</p>
            <table class="events-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Preview</th>
                        <th>Title</th>
                        <th>Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($g = $galleryItems->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $g['id']; ?></td>
                        <td>
                            <img src="<?php echo htmlspecialchars($g['image_filename']); ?>"
                                 alt="<?php echo htmlspecialchars($g['title']); ?>">
                        </td>
                        <td><?php echo htmlspecialchars($g['title']); ?></td>
                        <td><?php echo $g['display_order']; ?></td>
                        <td>
                            <div class="action-btns">
                                <a href="dashboard.php?editgallery=<?php echo $g['id']; ?>" class="btn-edit">✏️ Edit</a>
                                <a href="dashboard.php?deletegallery=<?php echo $g['id']; ?>"
                                   class="btn-delete"
                                   onclick="return confirm('Delete this gallery image?')">🗑️ Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- ===== USER MANAGEMENT ===== -->
        <div class="card">
            <h2>👥 User Management</h2>
            <table class="events-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Student ID</th>
                        <th>Batch</th>
                        <th>Department</th>
                        <th>Hall</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($u = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $u['id']; ?></td>
                        <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo htmlspecialchars($u['student_id'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($u['batch'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($u['department']); ?></td>
                        <td><?php echo htmlspecialchars($u['hall'] ?? '-'); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $u['role']; ?>">
                                <?php echo ucfirst($u['role']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($u['id'] === (int)$_SESSION['user_id']): ?>
                                <span style="color:#ccc; font-size:0.8rem;">You</span>
                            <?php elseif ($u['role'] === 'member'): ?>
                                <a href="dashboard.php?promote=<?php echo $u['id']; ?>"
                                   class="btn-promote"
                                   onclick="return confirm('Promote <?php echo htmlspecialchars($u['full_name']); ?> to Executive?')">
                                   ⬆️ Promote
                                </a>
                            <?php else: ?>
                                <a href="dashboard.php?demote=<?php echo $u['id']; ?>"
                                   class="btn-demote"
                                   onclick="return confirm('Demote <?php echo htmlspecialchars($u['full_name']); ?> to Member?')">
                                   ⬇️ Demote
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- ===== EVENT REGISTRATIONS ===== -->
        <div class="card">
            <h2>📝 Event Registrations</h2>
            <?php if (empty($regGrouped)): ?>
                <p style="color:#888;">No registrations yet.</p>
            <?php else:
                foreach ($regGrouped as $eventTitle => $members): ?>
                <div style="margin-bottom:2rem;">
                    <h3 style="font-size:1rem; color:var(--primary-blue); margin-bottom:0.5rem;">
                        📅 <?php echo htmlspecialchars($eventTitle); ?>
                        <span style="font-size:0.82rem; color:#888; font-weight:400;">
                            — <?php echo htmlspecialchars($members[0]['event_date']); ?>
                            (<?php echo count($members); ?> registered)
                        </span>
                    </h3>
                    <table class="events-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Student ID</th>
                                <th>Batch</th>
                                <th>Department</th>
                                <th>Hall</th>
                                <th>Registered At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $i => $m): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><?php echo htmlspecialchars($m['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($m['email']); ?></td>
                                <td><?php echo htmlspecialchars($m['student_id'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($m['batch'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($m['department']); ?></td>
                                <td><?php echo htmlspecialchars($m['hall'] ?? '-'); ?></td>
                                <td><?php echo date('d M Y, h:i A', strtotime($m['registered_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; endif; ?>
        </div>

    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('image-preview');
            const previewImg = document.getElementById('preview-img');
            const fileName = document.getElementById('file-name');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                    fileName.textContent = '📎 ' + input.files[0].name;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function previewGallery(input) {
            const preview = document.getElementById('gallery-preview');
            const previewImg = document.getElementById('gallery-preview-img');
            const fileName = document.getElementById('gallery-file-name');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                    fileName.textContent = '📎 ' + input.files[0].name;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

</body>
</html>