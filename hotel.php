<?php
include 'db.php';
session_start();

$hotel = null;
$rooms = [];
$error = '';

try {
    $hotel_id = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : 0;
    if ($hotel_id <= 0) {
        $error = 'Invalid hotel ID.';
    } else {
        // Fetch hotel details
        $stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ?");
        $stmt->execute([$hotel_id]);
        $hotel = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$hotel) {
            $error = 'Hotel not found.';
        } else {
            // Fetch available rooms
            $stmt = $pdo->prepare("SELECT * FROM rooms WHERE hotel_id = ?");
            $stmt->execute([$hotel_id]);
            $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title><?php echo $hotel ? htmlspecialchars($hotel['name']) : 'Hotel Details'; ?> - Hotels.com Clone</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #00ddeb, #ff6b6b);
            color: #333;
            overflow-x: hidden;
        }
        .container {
            max-width: 1000px;
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
        .hotel-details {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            animation: slideIn 0.8s ease-out;
        }
        .hotel-details img {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 2px solid #4ecdc4;
        }
        .hotel-details h2 {
            color: #6b48ff;
            margin-bottom: 15px;
        }
        .hotel-details p {
            margin-bottom: 10px;
        }
        .room-card {
            background: rgba(255, 255, 255, 0.9);
            padding: 15px;
            margin: 10px 0;
            border-radius: 10px;
            border-left: 4px solid #4ecdc4;
            animation: fadeInUp 0.8s ease-out;
        }
        .booking-form {
            margin-top: 20px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .booking-form label {
            display: block;
            margin-bottom: 5px;
            color: #6b48ff;
        }
        .booking-form input, .booking-form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #4ecdc4;
            border-radius: 8px;
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
            .header h1 { font-size: 2rem; }
            .btn { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo $hotel ? htmlspecialchars($hotel['name']) : 'Hotel Details'; ?></h1>
        </div>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php elseif ($hotel): ?>
            <div class="hotel-details">
                <?php if ($hotel['image_url']): ?>
                    <img src="<?php echo htmlspecialchars($hotel['image_url']); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>" onerror="this.src='https://via.placeholder.com/800x300';">
                <?php else: ?>
                    <img src="https://via.placeholder.com/800x300" alt="No image available">
                <?php endif; ?>
                <h2><?php echo htmlspecialchars($hotel['name']); ?></h2>
                <p><strong>City:</strong> <?php echo htmlspecialchars($hotel['city']); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($hotel['address']); ?></p>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($hotel['description']); ?></p>
                <p><strong>Price from:</strong> $<?php echo number_format($hotel['price_from'], 2); ?>/night</p>
                <p><strong>Rating:</strong> <span style="color: #32CD32;"><?php echo htmlspecialchars($hotel['rating']); ?>★</span></p>
                <p><strong>Service Level:</strong> <?php echo htmlspecialchars($hotel['service_level']); ?></p>
                <p><strong>Amenities:</strong> <?php echo $hotel['amenities'] ? htmlspecialchars($hotel['amenities']) : 'None'; ?></p>
                <h2>Available Rooms</h2>
                <?php if (empty($rooms)): ?>
                    <p class="error">No rooms available for this hotel.</p>
                <?php else: ?>
                    <?php foreach ($rooms as $room): ?>
                        <div class="room-card">
                            <p><strong>Room Type:</strong> <?php echo htmlspecialchars($room['type']); ?></p>
                            <p><strong>Price:</strong> $<?php echo number_format($room['price'], 2); ?>/night</p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div class="booking-form">
                    <h2>Book Now</h2>
                    <form action="book.php" method="POST">
                        <input type="hidden" name="hotel_id" value="<?php echo (int)$hotel_id; ?>">
                        <label for="room_id">Select Room:</label>
                        <select name="room_id" id="room_id" required>
                            <option value="">Choose a room</option>
                            <?php foreach ($rooms as $room): ?>
                                <option value="<?php echo (int)$room['id']; ?>"><?php echo htmlspecialchars($room['type']); ?> ($<?php echo number_format($room['price'], 2); ?>/night)</option>
                            <?php endforeach; ?>
                        </select>
                        <label for="check_in">Check-in Date:</label>
                        <input type="date" name="check_in" id="check_in" required min="<?php echo date('Y-m-d'); ?>">
                        <label for="check_out">Check-out Date:</label>
                        <input type="date" name="check_out" id="check_out" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        <label for="guests">Guests:</label>
                        <input type="number" name="guests" id="guests" min="1" max="10" required>
                        <button type="submit" class="btn">Book Now</button>
                    </form>
                </div>
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
        window.onload = () => showToast('Book your stay at <?php echo $hotel ? htmlspecialchars($hotel['name']) : 'this hotel'; ?>!');
    </script>
</body>
</html>
