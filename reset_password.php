<?php
include 'db.php';
session_start();
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user) {
            // Simulate sending reset link (no real email)
            $token = bin2hex(random_bytes(16));
            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?");
            $stmt->execute([$token, $user['id']]);
            $success = 'Password reset link sent to your email!';
        } else {
            $error = 'Email not found.';
        }
    } catch (PDOException $e) {
        $error = 'Error: ' . htmlspecialchars($e->getMessage());
    }
}
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        if ($user && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
            $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
            $stmt->execute([$new_password, $user['id']]);
            $success = 'Password reset successfully!';
        } elseif (!$user) {
            $error = 'Invalid or expired token.';
        }
    } catch (PDOException $e) {
        $error = 'Error: ' . htmlspecialchars($e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Hotels.com Clone</title>
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
            background: linear-gradient(45deg, #6b48ff, #00ddeb);
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
        .error { color: #ff6b6b; margin: 10px 0; }
        .success { color: #32CD32; margin: 10px 0; }
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
            .form input, .form button { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Reset Your Password</h1>
        </div>
        <form class="form" method="POST">
            <?php if (isset($_GET['token'])): ?>
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required aria-label="New password">
            <?php else: ?>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required aria-label="Email">
            <?php endif; ?>
            <?php if ($error): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p class="success"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>
            <button type="submit"><?php echo isset($_GET['token']) ? 'Reset Password' : 'Send Reset Link'; ?></button>
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
            window.onload = () => showToast('Reset your password!');
        <?php endif; ?>
    </script>
</body>
</html>
