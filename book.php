<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=book.php&' . $_SERVER['QUERY_STRING']);
    exit;
}

$error = '';
$booking_details = null;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $hotel_id = isset($_POST['hotel_id']) ? (int)$_POST['hotel_id'] : 0;
        $room_id = isset($_POST['room_id']) ? (int)$_POST['room_id'] : 0;
        $check_in = isset($_POST['check_in']) ? $_POST['check_in'] : '';
        $check_out = isset($_POST['check_out']) ? $_POST['check_out'] : '';
        $guests = isset($_POST['guests']) ? (int)$_POST['guests'] : 0;

        // Validate inputs
        if ($hotel_id <= 0 || $room_id <= 0 || empty($check_in) || empty($check_out) || $guests <= 0) {
            $error = 'Please fill all required fields correctly.';
        } elseif (strtotime($check_in) < strtotime(date('Y-m-d'))) {
            $error = 'Check-in date cannot be in the past.';
        } elseif (strtotime($check_out) <= strtotime($check_in)) {
            $error = 'Check-out date must be after check-in date.';
        } else {
            // Verify hotel and room exist
            $stmt = $pdo->prepare("SELECT h.id, h.name AS hotel_name, r.id AS room_id, r.type AS room_type, r.price 
                                   FROM hotels h 
                                   JOIN rooms r ON h.id = r.hotel_id 
                                   WHERE h.id = ? AND r.id = ?");
            $stmt->execute([$hotel_id, $room_id]);
            $booking_details = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking_details) {
                $error = 'Invalid hotel or room selected.';
            } else {
                // Insert booking
                $stmt = $pdo->prepare("
                    INSERT INTO bookings (user_id, room_id, check_in, check_out, guests, status) 
                    VALUES (?, ?, ?, ?, ?, 'pending')
                ");
                $stmt->execute([$_SESSION['user_id'], $room_id, $check_in, $check_out, $guests]);

                $booking_id = $pdo->lastInsertId();
                header("Location: payment.php?booking_id=$booking_id");
                exit;
            }
        }
    } else {
        $error = 'Invalid request method.';
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
    <title>Book Your Stay - Hotels.com Clone</title>
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
        .booking-details {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            animation: slideIn 0.8s ease-out;
        }
        .booking-details h2 {
            color: #6b48ff;
            margin-bottom: 15px;
        }
        .booking-details p {
            margin-bottom: 10px;
            color: #333;
        }
        .btn {
            padding: 10px 20px;
            background: linear-gradient(45deg, #6b48ff, #4ecdc4);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
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
            .header h1 { font-size: 2rem; }
            .btn { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Book Your Stay</h1>
        </div>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php elseif ($booking_details): ?>
            <div class="booking-details">
                <h2>Booking Confirmation</h2>
                <p><strong>Hotel:</strong> <?php echo htmlspecialchars($booking_details['hotel_name']); ?></p>
                <p><strong>Room Type:</strong> <?php echo htmlspecialchars($booking_details['room_type']); ?></p>
                <p><strong>Price:</strong> $<?php echo number_format($booking_details['price'], 2); ?>/night</p>
                <p><strong>Check-in:</strong> <?php echo htmlspecialchars($_POST['check_in']); ?></p>
                <p><strong>Check-out:</strong> <?php echo htmlspecialchars($_POST['check_out']); ?></p>
                <p><strong>Guests:</strong> <?php echo (int)$_POST['guests']; ?></p>
                <p style="color: #ff6b6b;">Please review your booking details. You will be redirected to payment after confirmation.</p>
                <form method="POST">
                    <input type="hidden" name="hotel_id" value="<?php echo (int)$hotel_id; ?>">
                    <input type="hidden" name="room_id" value="<?php echo (int)$room_id; ?>">
                    <input type="hidden" name="check_in" value="<?php echo htmlspecialchars($check_in); ?>">
                    <input type="hidden" name="check_out" value="<?php echo htmlspecialchars($check_out); ?>">
                    <input type="hidden" name="guests" value="<?php echo (int)$guests; ?>">
                    <button type="submit" class="btn">Confirm Booking</button>
                </form>
            </div>
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
            <a href="policies.php" class="btn">Terms & Policies</a>
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
        window.onload = () => showToast('Confirm your booking details to proceed to payment.');
    </script>
</body>
</html>
