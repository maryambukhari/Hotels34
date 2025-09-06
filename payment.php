<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=payment.php&' . $_SERVER['QUERY_STRING']);
    exit;
}

$error = '';
$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

try {
    // Verify user_id exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        $error = 'User account not found. Please re-register or contact support.';
        unset($_SESSION['user_id']);
        header('Location: login.php');
        exit;
    }

    // Fetch booking details
    if ($booking_id > 0) {
        $stmt = $pdo->prepare("
            SELECT b.id, h.name AS hotel_name, r.type AS room_type, r.price
            FROM bookings b
            JOIN rooms r ON b.room_id = r.id
            JOIN hotels h ON r.hotel_id = h.id
            WHERE b.id = ? AND b.user_id = ?
        ");
        $stmt->execute([$booking_id, $_SESSION['user_id']]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$booking) {
            $error = 'Booking not found or does not belong to you.';
        }
    } else {
        $error = 'Invalid or missing booking ID.';
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $amount = $_POST['amount'] ?? 0;
        $method = $_POST['method'] ?? '';
        if ($amount <= 0 || !in_array($method, ['credit_card', 'debit_card', 'paypal', 'bank_transfer'])) {
            $error = 'Invalid payment details.';
        } else {
            // Insert payment into payments table
            $stmt = $pdo->prepare("
                INSERT INTO payments (booking_id, amount, method, status)
                VALUES (?, ?, ?, 'paid')
            ");
            $stmt->execute([$booking_id, $amount, $method]);

            // Update booking status
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ? AND user_id = ?");
            $stmt->execute([$booking_id, $_SESSION['user_id']]);

            header('Location: dashboard.php?message=Payment successful');
            exit;
        }
    }
} catch (PDOException $e) {
    $error = 'Database error: ' . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Hotels.com Clone</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #00ddeb, #ff6b6b);
            color: #333;
            overflow-x: hidden;
        }
        .container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
        }
        .payment-form {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            animation: slideIn 0.8s ease-out;
        }
        .payment-form h2 {
            color: #6b48ff;
            margin-bottom: 20px;
            text-align: center;
        }
        .payment-form p {
            margin-bottom: 10px;
        }
        .payment-form input, .payment-form select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 2px solid #4ecdc4;
            border-radius: 8px;
        }
        .btn {
            padding: 10px;
            background: linear-gradient(45deg, #6b48ff, #4ecdc4);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: block;
            width: 100%;
            transition: transform 0.2s, box-shadow 0.2s;
            animation: bounce 2s infinite;
        }
        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .error {
            color: #ff6b6b;
            text-align: center;
            margin: 10px 0;
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
            .btn { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="payment-form">
            <h2>Payment</h2>
            <?php if ($error): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php elseif ($booking): ?>
                <p><strong>Hotel:</strong> <?php echo htmlspecialchars($booking['hotel_name']); ?> - <?php echo htmlspecialchars($booking['room_type']); ?></p>
                <p><strong>Amount:</strong> $<?php echo number_format($booking['price'] * 5, 2); ?></p>
                <form method="POST">
                    <input type="hidden" name="amount" value="<?php echo htmlspecialchars($booking['price'] * 5); ?>">
                    <select name="method" required>
                        <option value="">Select Payment Method</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="debit_card">Debit Card</option>
                        <option value="paypal">PayPal</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                    <button type="submit" class="btn">Pay Now</button>
                </form>
            <?php endif; ?>
            <p style="text-align: center; margin-top: 20px;">
                <a href="index.php" class="btn">Back to Home</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="btn">My Dashboard</a>
                    <a href="logout.php" class="btn">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn">Login</a>
                    <a href="register.php" class="btn">Register</a>
                <?php endif; ?>
            </p>
        </div>
    </div>
    <div id="toast" class="toast"></div>
    <script>
        function showToast(message) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.style.display = 'block';
            setTimeout(() => { toast.style.display = 'none'; }, 3000);
        }
        window.onload = () => showToast('Complete your payment to confirm your booking.');
    </script>
</body>
</html>
