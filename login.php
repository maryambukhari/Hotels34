<?php
include 'db.php';
session_start();

// Clear any existing session to prevent auto-login
session_unset();
session_destroy();
session_start();

$error = '';
$success = isset($_GET['success']) ? filter_var($_GET['success'], FILTER_SANITIZE_STRING) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    // Normalize email: lowercase and trim
    $email = strtolower(trim($email));

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        try {
            // Fetch user by email (case-insensitive)
            $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE LOWER(email) = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $error = 'No account found with this email.';
            } elseif (!password_verify($password, $user['password'])) {
                $error = 'Incorrect password.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'] ?? 'user';
                $success = 'Login successful! Redirecting to dashboard...';
                echo "<script>setTimeout(() => window.location.href = 'dashboard.php', 2000);</script>";
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hotels.com Clone</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #00ddeb, #ff6b6b);
            color: #333;
            overflow-x: hidden;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding: 40px 0;
            color: white;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            animation: fadeIn 1s ease-in;
        }
        .form {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            animation: slideIn 0.8s ease-out;
        }
        .form label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
            color: #6b48ff;
        }
        .form input {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .form input:focus {
            outline: none;
            border-color: #6b48ff;
            box-shadow: 0 0 8px rgba(107, 72, 255, 0.5);
        }
        .form button {
            padding: 10px 20px;
            background: linear-gradient(45deg, #6b48ff, #4ecdc4);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            animation: bounce 2s infinite;
        }
        .form button:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .error {
            color: #ff6b6b;
            margin: 10px 0;
            text-align: center;
            animation: fadeIn 0.5s;
        }
        .success {
            color: #32CD32;
            margin: 10px 0;
            text-align: center;
            animation: fadeIn 0.5s;
        }
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #333;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
            display: none;
            animation: slideInRight 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        @keyframes slideInRight {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }
        @media (max-width: 768px) {
            .container { padding: 10px; }
            .form input, .form button { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Login</h1>
        </div>
        <form class="form" method="POST">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required aria-label="Email">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required aria-label="Password">
            <?php if ($error): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p class="success"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>
            <button type="submit">Login</button>
            <p style="margin-top: 10px; text-align: center;">
                <a href="register.php" style="color: #6b48ff;">Register</a> | 
                <a href="reset_password.php" style="color: #6b48ff;">Forgot Password?</a>
            </p>
        </form>
    </div>
    <div id="toast" class="toast"></div>
    <script>
        function showToast(message) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.style.display = 'block';
            setTimeout(() => { toast.style.display = 'none'; }, 3000);
        }
        <?php if ($error): ?>
            window.onload = () => showToast('<?php echo addslashes($error); ?>');
        <?php elseif ($success): ?>
            window.onload = () => showToast('<?php echo addslashes($success); ?>');
        <?php else: ?>
            window.onload = () => showToast('Please login to continue!');
        <?php endif; ?>
    </script>
</body>
</html>
