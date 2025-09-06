<?php
include 'db.php';
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=confirm.php&' . $_SERVER['QUERY_STRING']);
    exit;
}

$error = '';
$booking = null;

try {
    $booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
    if ($booking_id <= 0) {
        $error = 'Invalid booking ID.';
    } else {
        $stmt = $pdo->prepare("SELECT b.*, r.price, r.type, h.name AS hotel_name, p.amount, p.method, p.status 
                               FROM bookings b 
                               JOIN rooms r ON b.room_id = r.id 
                               JOIN hotels h ON r.hotel_id = h.id 
                               LEFT JOIN payments p ON b.id = p.booking_id 
                               WHERE b.id = ? AND b.user_id = ?");
        $stmt->execute([$booking_id, $_SESSION['user_id']]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            $error = 'Booking not found or you do not have access to this booking.';
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
    <title>Confirmation - Hotels.com Clone</title>
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
        .confirmation {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            animation: slideIn 0.8s ease-out;
        }
        .confirmation p {
            margin: 10px 0;
            color: #333;
        }
        .confirmation .success {
            color: #32CD32;
            font-weight: bold;
            text-align: center;
            animation: fadeIn 0.5s;
        }
        .confirmation .error {
            color: #ff6b6b;
            text-align: center;
            animation: fadeIn 0.5s;
        }
        .btn {
            padding: 10px 20px;
            background: linear-gradient(45deg, #6b48ff, #4ecdc4);
            color: white;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s, box-shadow 0.2s;
            animation: bounce 2s infinite;
        }
        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
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
        <div class="header">
            <h1>Booking Confirmation</h1>
        </div>
        <div class="confirmation">
            <?php if ($error): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php elseif ($booking): ?>
                <p class="success">Booking Confirmed!</p>
                <p><strong>Hotel:</strong> <?php echo htmlspecialchars($booking['hotel_name']); ?></p>
                <p><strong>Room:</strong> <?php echo htmlspecialchars($booking['type']); ?></p>
                <p><strong>Price per Night:</strong> $<?php echo number_format($booking['price'], 2); ?></p>
                <p><strong>Check-in:</strong> <?php echo htmlspecialchars($booking['check_in']); ?></p>
                <p><strong>Check-out:</strong> <?php echo htmlspecialchars($booking['check_out']); ?></p>
                <p><strong>Guests:</strong> <?php echo htmlspecialchars($booking['guests']); ?></p>
                <p><strong>Payment Amount:</strong> $<?php echo number_format($booking['amount'], 2); ?></p>
                <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($booking['method']); ?></p>
                <p><strong>Payment Status:</strong> <?php echo htmlspecialchars($booking['status']); ?></p>
            <?php else: ?>
                <p class="error">No booking details available.</p>
            <?php endif; ?>
            <p style="text-align: center; margin-top: 20px;">
                <a href="dashboard.php" class="btn">My Dashboard</a>
                <a href="hotels.php" class="btn">Browse Hotels</a>
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
        <?php if ($error): ?>
            window.onload = () => showToast('<?php echo addslashes($error); ?>');
        <?php else: ?>
            window.onload = () => showToast('Your booking is confirmed!');
        <?php endif; ?>
    </script>
</body>
</html>
