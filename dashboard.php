<?php
include 'db.php';
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$bookings = [];

try {
    // Fetch user bookings
    $stmt = $pdo->prepare("SELECT b.id, b.check_in, b.check_out, b.guests, b.status, 
                           h.name AS hotel_name, r.type AS room_type 
                           FROM bookings b 
                           JOIN rooms r ON b.room_id = r.id 
                           JOIN hotels h ON r.hotel_id = h.id 
                           WHERE b.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Error: ' . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Hotels.com Clone</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #00ddeb, #ff6b6b);
            color: #333;
            overflow-x: hidden;
        }
        .container {
            max-width: 800px;
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
        .booking-section {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            animation: slideIn 0.8s ease-out;
            margin-bottom: 20px;
        }
        .booking-section h2 {
            color: #6b48ff;
            margin-bottom: 15px;
        }
        .booking-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
            margin-bottom: 10px;
            transition: transform 0.3s, box-shadow 0.3s;
            animation: fadeInUp 0.8s ease-out;
        }
        .booking-card:hover {
            transform: scale(1.03);
            box-shadow: 0 12px 24px rgba(0,0,0,0.4);
        }
        .booking-card p {
            margin-bottom: 8px;
            color: #333;
        }
        .booking-card .status-pending { color: #4ecdc4; }
        .booking-card .status-confirmed { color: #32CD32; }
        .booking-card .status-cancelled { color: #ff6b6b; }
        .btn {
            padding: 8px 16px;
            background: linear-gradient(45deg, #6b48ff, #4ecdc4);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s;
            animation: bounce 2s infinite;
        }
        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 8px rgba(107, 72, 255, 0.5);
        }
        .error {
            color: #ff6b6b;
            text-align: center;
            margin: 20px 0;
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
        @keyframes fadeInUp {
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
            .booking-card, .btn { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>!</h1>
            <p>Your Dashboard</p>
        </div>
        <div class="booking-section">
            <h2>Your Bookings</h2>
            <?php if ($error): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php elseif (empty($bookings)): ?>
                <p>No bookings found. <a href="hotels.php" style="color: #6b48ff;">Explore Hotels</a></p>
            <?php else: ?>
                <?php foreach ($bookings as $booking): ?>
                    <div class="booking-card">
                        <p><strong>Hotel:</strong> <?php echo htmlspecialchars($booking['hotel_name']); ?></p>
                        <p><strong>Room Type:</strong> <?php echo htmlspecialchars($booking['room_type']); ?></p>
                        <p><strong>Check-in:</strong> <?php echo htmlspecialchars($booking['check_in']); ?></p>
                        <p><strong>Check-out:</strong> <?php echo htmlspecialchars($booking['check_out']); ?></p>
                        <p><strong>Guests:</strong> <?php echo htmlspecialchars($booking['guests']); ?></p>
                        <p><strong>Status:</strong> <span class="status-<?php echo strtolower($booking['status']); ?>"><?php echo htmlspecialchars($booking['status']); ?></span></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <p style="text-align: center;">
            <a href="hotels.php" class="btn">Browse Hotels</a>
            <a href="logout.php" class="btn">Logout</a>
        </p>
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
            window.onload = () => showToast('Welcome to your dashboard!');
        <?php endif; ?>
    </script>
</body>
</html>
