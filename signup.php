<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'db.php';

$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName   = trim($_POST['fullname'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $studentId  = trim($_POST['student_id'] ?? '');
    $batch      = trim($_POST['batch'] ?? '');
    $hall       = trim($_POST['hall'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmpassword'] ?? '';

    if ($fullName === '' || $email === '' || $phone === '' || $department === '' || $studentId === '' || $batch === '' || $hall === '' || $password === '' || $confirmPassword === '') {
        $errors[] = 'Please fill in all fields.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $checkStmt = $mysqli->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $checkStmt->bind_param('s', $email);
        $checkStmt->execute();
        $checkStmt->store_result();
        if ($checkStmt->num_rows > 0) {
            $errors[] = 'An account already exists with this email.';
        }
        $checkStmt->close();
    }

    if (!$errors) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $insertStmt = $mysqli->prepare('INSERT INTO users (full_name, email, phone, department, student_id, batch, hall, password_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $insertStmt->bind_param('ssssssss', $fullName, $email, $phone, $department, $studentId, $batch, $hall, $passwordHash);
        if ($insertStmt->execute()) {
            $successMessage = 'Account created successfully. You can now log in.';
        } else {
            $errors[] = 'Something went wrong. Please try again.';
        }
        $insertStmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KUET Math Club - Sign Up</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .auth-container {
            max-width: 550px;
            margin: 80px auto;
            padding: 2rem;
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .auth-header h1 {
            color: var(--primary-blue);
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        .auth-header p { color: var(--text-gray); font-size: 0.95rem; }
        .form-group { margin-bottom: 1.2rem; }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark-gray);
            font-weight: 500;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-gray);
            border-radius: 4px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--accent-purple);
            box-shadow: 0 0 0 3px rgba(126, 87, 194, 0.1);
        }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .form-divider {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--accent-purple);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 1.2rem 0 0.8rem;
            padding-bottom: 0.4rem;
            border-bottom: 1px solid var(--light-purple);
        }
        .submit-btn {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, var(--accent-purple), var(--secondary-blue));
            color: var(--white);
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-top: 0.5rem;
        }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(126, 87, 194, 0.3);
        }
        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-gray);
        }
        .auth-footer a { color: var(--accent-purple); text-decoration: none; font-weight: 500; }
        .auth-footer a:hover { text-decoration: underline; }
        .back-link {
            display: inline-block;
            margin-bottom: 1rem;
            color: var(--accent-purple);
            text-decoration: none;
            font-weight: 500;
        }
        .auth-message {
            margin-bottom: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .auth-message.error { background: #fdecea; color: #b3261e; border: 1px solid #f5c2c0; }
        .auth-message.success { background: #e8f5e9; color: #1b5e20; border: 1px solid #b7dfb9; }
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
                <li><a href="team.php">Team</a></li>
                <li><a href="gallery.php">Gallery</a></li>
                <li><a href="index.php#contact">Contact</a></li>
                <li><a href="login.php" class="btn-login">Login</a></li>
                <li><a href="signup.php" class="btn-signup">Sign Up</a></li>
            </ul>
        </div>
    </nav>

    <div class="auth-container">
        <a href="index.php" class="back-link">← Back to Home</a>
        <div class="auth-header">
            <h1>Sign Up</h1>
            <p>Join KUET Math Club and start your journey</p>
        </div>

        <?php if ($errors): ?>
            <div class="auth-message error"><?php echo htmlspecialchars(implode(' ', $errors)); ?></div>
        <?php endif; ?>
        <?php if ($successMessage !== ''): ?>
            <div class="auth-message success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <form method="POST" action="signup.php">

            <p class="form-divider">Personal Info</p>

            <div class="form-group">
                <label for="fullname">Full Name</label>
                <input type="text" id="fullname" name="fullname" placeholder="Enter your full name"
                       value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" placeholder="01XXXXXXXXX"
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                </div>
            </div>

            <p class="form-divider">Academic Info</p>

            <div class="form-row">
                <div class="form-group">
                    <label for="student_id">Student ID</label>
                    <input type="text" id="student_id" name="student_id" placeholder="e.g. 2207059"
                           value="<?php echo htmlspecialchars($_POST['student_id'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="batch">Batch / Year</label>
                    <input type="text" id="batch" name="batch" placeholder="e.g. 2022"
                           value="<?php echo htmlspecialchars($_POST['batch'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="department">Department</label>
                    <input type="text" id="department" name="department" placeholder="e.g. CSE"
                           value="<?php echo htmlspecialchars($_POST['department'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="hall">Hall Name</label>
                    <input type="text" id="hall" name="hall" placeholder="e.g. Khan Jahan Ali Hall"
                           value="<?php echo htmlspecialchars($_POST['hall'] ?? ''); ?>" required>
                </div>
            </div>

            <p class="form-divider">Account Security</p>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Min 6 characters" required>
                </div>
                <div class="form-group">
                    <label for="confirmpassword">Confirm Password</label>
                    <input type="password" id="confirmpassword" name="confirmpassword" placeholder="Repeat password" required>
                </div>
            </div>

            <button type="submit" class="submit-btn">Create Account</button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="login.php">Login</a>
        </div>
    </div>

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