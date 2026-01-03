<?php
/**
 * REGISTRATION PAGE - Sekuwa Kerner
 * Premium Nepali Food Ordering Platform
 */

session_start();
require_once '../config/db.php';

if (isLoggedIn()) {
    redirect('../index.php');
}

$name = '';
$email = '';
$phone = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($conn, $_POST['name'] ?? '');
    $email = sanitize($conn, $_POST['email'] ?? '');
    $phone = sanitize($conn, $_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($name)) {
        $errors[] = 'Name is required.';
    } elseif (strlen($name) < 2) {
        $errors[] = 'Name must be at least 2 characters.';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'This email is already registered.';
        }
        $stmt->close();
    }
    
    if (!empty($phone) && !preg_match('/^[0-9]{10}$/', $phone)) {
        $errors[] = 'Please enter a valid 10-digit phone number.';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }
    
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'customer')");
        $stmt->bind_param("ssss", $name, $email, $phone, $hashed_password);
        
        if ($stmt->execute()) {
            setFlashMessage('success', '🎉 Account created! Please login.');
            redirect('login.php');
        } else {
            $errors[] = 'Registration failed. Please try again.';
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
    <title>Sign Up - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🍢</text></svg>">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="../index.php" class="logo">🍢 <?php echo SITE_NAME; ?></a>
            <ul class="nav-links">
                <li><a href="../index.php">Home</a></li>
                <li><a href="../products.php">Menu</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php" class="active btn btn-primary btn-small">Sign Up</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="auth-page">
        <div class="container">
            <div class="auth-box">
                <div style="text-align: center; margin-bottom: 1.5rem;">
                    <span style="font-size: 4rem;">🔥</span>
                </div>
                <h1>Join the Feast!</h1>
                <p class="subtitle">Create your account and start ordering authentic sekuwa</p>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <ul style="margin-left: 1rem;">
                            <?php foreach ($errors as $error): ?>
                                <li>❌ <?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form action="register.php" method="POST" class="auth-form" id="registerForm">
                    <div class="form-group">
                        <label for="name">👤 Full Name *</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            value="<?php echo htmlspecialchars($name); ?>"
                            placeholder="Your full name"
                            required
                            autocomplete="name"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="email">📧 Email Address *</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?php echo htmlspecialchars($email); ?>"
                            placeholder="your@email.com"
                            required
                            autocomplete="email"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">📱 Phone Number <span style="color: var(--color-text-muted);">(optional)</span></label>
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            value="<?php echo htmlspecialchars($phone); ?>"
                            placeholder="10-digit number"
                            pattern="[0-9]{10}"
                            autocomplete="tel"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="password">🔒 Password *</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password"
                            placeholder="Min. 6 characters"
                            minlength="6"
                            required
                            autocomplete="new-password"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">🔒 Confirm Password *</label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password"
                            placeholder="Re-enter password"
                            required
                            autocomplete="new-password"
                        >
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block btn-large">
                        🚀 Create Account
                    </button>
                </form>
                
                <p class="auth-footer">
                    Already have an account? <a href="login.php">Sign in here</a>
                </p>
                
                <div style="margin-top: 1.5rem; padding: 1rem; background: rgba(255,186,8,0.1); border-radius: 12px; border: 1px solid rgba(255,186,8,0.2); text-align: center;">
                    <p style="font-size: 0.85rem; color: var(--color-text-muted); margin: 0;">
                        🔒 Your data is secure with us
                    </p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom" style="border-top: none; padding-top: 0;">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="../assets/js/script.js"></script>
</body>
</html>
