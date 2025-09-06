<?php
include 'db.php';
session_start();

$hotels = [];
$category = $_GET['category'] ?? '';
try {
    $query = "SELECT id, name, city, price_from, rating, image_url FROM hotels";
    if ($category) {
        $query .= " WHERE service_level = ? OR amenities LIKE ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$category, "%$category%"]);
    } else {
        $stmt = $pdo->prepare($query);
        $stmt->execute();
    }
    $hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Database error: ' . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Hotels - Hotels.com Clone</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: linear-gradient(135deg, #00ddeb, #ff6b6b); color: #333; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding: 40px 0; color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.3); animation: fadeIn 1s ease-in; }
        .hotel-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0; animation: slideIn 0.8s ease-out; }
        .hotel-card { background: rgba(255, 255, 255, 0.95); padding: 20px; border-radius: 15px; box-shadow: 0 8px 16px rgba(0,0,0,0.2); transition: transform 0.3s; }
        .hotel-card:hover { transform: scale(1.05); }
        .hotel-card img { width: 100%; height: 150px; object-fit: cover; border-radius: 10px; margin-bottom: 10px; border: 2px solid #4ecdc4; }
        .hotel-card h3 { color: #6b48ff; }
        .hotel-card .rating { color: #32CD32; font-weight: bold; }
        .btn { padding: 10px 20px; background: linear-gradient(45deg, #6b48ff, #4ecdc4); color: white; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; display: inline-block; animation: bounce 2s infinite; }
        .btn:hover { transform: scale(1.05); box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
        .error { color: #ff6b6b; text-align: center; margin: 20px 0; }
        .toast { position: fixed; bottom: 20px; right: 20px; background: #333; color: white; padding: 10px 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.3); display: none; animation: slideInRight 0.5s ease-in; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideIn { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        @keyframes bounce { 0%, 20%, 50%, 80%, 100% { transform: translateY(0); } 40% { transform: translateY(-10px); } 60% { transform: translateY(-5px); } }
        @keyframes slideInRight { from { transform: translateX(100%); } to { transform: translateX(0); } }
        @media (max-width: 768px) { .container { padding: 10px; } .hotel-list { grid-template-columns: 1fr; } .btn { width: 100%; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Browse Hotels</h1>
        </div>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <div class="hotel-list">
            <?php if (empty($hotels)): ?>
                <p class="error">No hotels found.</p>
            <?php else: ?>
                <?php foreach ($hotels as $hotel): ?>
                    <div class="hotel-card">
                        <?php if ($hotel['image_url']): ?>
                            <img src="<?php echo htmlspecialchars($hotel['image_url']); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>" onerror="this.src='https://via.placeholder.com/300x150';">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/300x150" alt="No image available">
                        <?php endif; ?>
                        <h3><?php echo htmlspecialchars($hotel['name']); ?></h3>
                        <p><strong>City:</strong> <?php echo htmlspecialchars($hotel['city']); ?></p>
                        <p><strong>Price from:</strong> $<?php echo number_format($hotel['price_from'], 2); ?>/night</p>
                        <p><strong>Rating:</strong> <span class="rating"><?php echo htmlspecialchars($hotel['rating']); ?>★</span></p>
                        <a href="hotel.php?hotel_id=<?php echo (int)$hotel['id']; ?>" class="btn">View Details</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
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
        window.onload = () => showToast('Browse and book your perfect hotel.');
    </script>
</body>
</html>
