<?php
include 'db.php';
session_start();

$featured_hotels = [];
try {
    // Fetch all hotels
    $stmt = $pdo->prepare("SELECT id, name, city, price_from, rating, image_url 
                           FROM hotels 
                           ORDER BY rating DESC");
    $stmt->execute();
    $featured_hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Database error: ' . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotels.com Clone</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #00ddeb, #ff6b6b);
            color: #333;
            overflow-x: hidden;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding: 60px 0;
            color: white;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            animation: fadeIn 1s ease-in;
        }
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .categories, .featured-hotels {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
            animation: slideIn 0.8s ease-out;
        }
        .category-card, .hotel-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            transition: transform 0.3s, box-shadow 0.3s;
            animation: fadeInUp 0.8s ease-out;
        }
        .category-card:hover, .hotel-card:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 24px rgba(0,0,0,0.4);
        }
        .category-card a, .hotel-card a {
            color: #6b48ff;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1rem;
        }
        .hotel-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 10px;
            border: 2px solid #4ecdc4;
            display: block;
        }
        .hotel-card h3 {
            color: #6b48ff;
            margin-bottom: 10px;
        }
        .hotel-card .rating {
            color: #32CD32;
            font-weight: bold;
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
            .categories, .featured-hotels { grid-template-columns: 1fr; }
            .btn { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to Hotels.com Clone</h1>
            <p>Find your perfect stay with ease</p>
        </div>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <h2 style="text-align: center; color: #6b48ff; margin: 20px 0;">Featured Hotels</h2>
        <div class="featured-hotels">
            <?php if (empty($featured_hotels)): ?>
                <p class="error">No hotels available at the moment.</p>
            <?php else: ?>
                <?php foreach ($featured_hotels as $hotel): ?>
                    <div class="hotel-card">
                        <?php if ($hotel['image_url']): ?>
                            <img src="<?php echo htmlspecialchars($hotel['image_url']); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>" onerror="this.src='https://via.placeholder.com/300x150';" style="max-width: 100%;">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/300x150" alt="No image available" style="max-width: 100%;">
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
        <h2 style="text-align: center; color: #6b48ff; margin: 20px 0;">Browse by Category</h2>
        <div class="categories">
            <div class="category-card">
                <a href="hotels.php?category=luxury">5-Star Luxury</a>
            </div>
            <div class="category-card">
                <a href="hotels.php?category=midscale">3-Star Comfort</a>
            </div>
            <div class="category-card">
                <a href="hotels.php?category=luxury">Luxury Hotels</a>
            </div>
            <div class="category-card">
                <a href="hotels.php?category=economy">Budget Stays</a>
            </div>
            <div class="category-card">
                <a href="hotels.php?category=beachfront">Beach Resorts</a>
            </div>
            <div class="category-card">
                <a href="hotels.php?category=city">City Hotels</a>
            </div>
            <div class="category-card">
                <a href="hotels.php?category=mountain_view">Mountain Retreats</a>
            </div>
            <div class="category-card">
                <a href="hotels.php?category=special_deals">Special Deals</a>
            </div>
        </div>
        <p style="text-align: center; margin-top: 20px;">
            <a href="hotels.php" class="btn">Browse All Hotels</a>
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
        window.onload = () => showToast('Welcome! Explore our hotels and book your stay.');
    </script>
</body>
</html>
