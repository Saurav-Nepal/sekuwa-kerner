<?php
/**
 * LOGIN PAGE - Sekuwa Kerner
 * Premium Nepali Food Ordering Platform
 */

session_start();
require_once '../config/db.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('../admin/dashboard.php');
    } else {
        redirect('../index.php');
    }
}

$email = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($conn, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                setFlashMessage('success', '🎉 Welcome back, ' . $user['name'] . '!');
                
                if ($user['role'] === 'admin') {
                    redirect('../admin/dashboard.php');
                } else {
                    if (isset($_SESSION['redirect_after_login'])) {
                        $redirect = $_SESSION['redirect_after_login'];
                        unset($_SESSION['redirect_after_login']);
                        redirect($redirect);
                    } else {
                        redirect('../index.php');
                    }
                }
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
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
    <title>Login - <?php echo SITE_NAME; ?></title>
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
                <li><a href="login.php" class="active">Login</a></li>
                <li><a href="register.php" class="btn btn-primary btn-small">Sign Up</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="auth-page">
        <div class="container">
            <div class="auth-box">
                <div style="text-align: center; margin-bottom: 1.5rem;">
                    <span style="font-size: 4rem;">🍢</span>
                </div>
                <h1>Welcome Back!</h1>
                <p class="subtitle">Sign in to continue ordering delicious sekuwa</p>
                
                <?php if ($error): ?>
                    <div class="alert alert-error">❌ <?php echo $error; ?></div>
                <?php endif; ?>
                
                <form action="login.php" method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="email">📧 Email Address</label>
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
                        <label for="password">🔒 Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password"
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
                        >
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block btn-large">
                        🔥 Sign In
                    </button>
                </form>
                
                <p class="auth-footer">
                    Don't have an account? <a href="register.php">Create one here</a>
                </p>
                
                <div class="demo-credentials">
                    <p><strong>🧪 Demo Accounts:</strong></p>
                    <p>👨‍💼 Admin: admin@sekuwakerner.com / password</p>
                    <p>👤 Customer: ram@example.com / password</p>
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
