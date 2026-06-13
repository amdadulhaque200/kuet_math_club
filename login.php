<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'executive') {
        header('Location: dashboard.php');
    } else {
        header('Location: member_dashboard.php');
    }
    exit;
}

$loginError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $loginError = 'Please enter both email and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $loginError = 'Please enter a valid email address.';
    } else {
        $stmt = $mysqli->prepare('SELECT id, full_name, email, password_hash, role FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id']    = (int) $user['id'];
            $_SESSION['user_name']  = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role']  = $user['role'];

            if ($user['role'] === 'executive') {
                header('Location: dashboard.php');
            } else {
                header('Location: member_dashboard.php');
            }
            exit;
        }

        $loginError = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KUET Math Club - Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .auth-container {
            max-width: 500px;
            margin: 80px auto;
            padding: 2rem;
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .auth-header { text-align: center; margin-bottom: 2rem; }
        .auth-header h1 { color: var(--primary-blue); font-size: 1.8rem; margin-bottom: 0.5rem; }
        .auth-header p  { color: var(--text-gray); font-size: 0.95rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; color: var(--dark-gray); font-weight: 500; }
        .form-group input {
            width: 100%; padding: 0.75rem;
            border: 1px solid var(--border-gray); border-radius: 4px;
            font-family: 'Poppins', sans-serif; font-size: 0.95rem;
            transition: border-color 0.3s ease; box-sizing: border-box;
        }
        .form-group input:focus { outline: none; border-color: var(--accent-purple); box-shadow: 0 0 0 3px rgba(126,87,194,0.1); }
        .submit-btn {
            width: 100%; padding: 0.75rem;
            background: linear-gradient(135deg, var(--accent-purple), var(--secondary-blue));
            color: var(--white); border: none; border-radius: 4px;
            font-size: 1rem; font-weight: 600; cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(126,87,194,0.3); }
        .auth-footer { text-align: center; margin-top: 1.5rem; color: var(--text-gray); }
        .auth-footer a { color: var(--accent-purple); text-decoration: none; font-weight: 500; }
        .auth-footer a:hover { text-decoration: underline; }
        .back-link { display: inline-block; margin-bottom: 1rem; color: var(--accent-purple); text-decoration: none; font-weight: 500; }
        .auth-message { margin-bottom: 1rem; padding: 0.75rem 1rem; border-radius: 4px; font-size: 0.9rem; background: #fdecea; color: #b3261e; border: 1px solid #f5c2c0; }
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
            <h1>Login</h1>
            <p>Welcome back to KUET Math Club</p>
        </div>

        <?php if ($loginError !== ''): ?>
            <div class="auth-message"><?php echo htmlspecialchars($loginError); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email"
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="submit-btn">Login</button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="signup.php">Sign Up</a>
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