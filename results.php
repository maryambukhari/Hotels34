<?php
include 'db.php';
session_start();
$city = isset($_GET['city']) ? $_GET['city'] : '';
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : '';
$check_out = isset($_GET['check_out']) ? $_GET['check_out'] : '';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 10000;
$min_rating = isset($_GET['min_rating']) ? (int)$_GET['min_rating'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'price_asc';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 6;
$offset = ($page - 1) * $per_page;

try {
    $query = "SELECT * FROM hotels WHERE city LIKE :city AND price_from >= :min_price AND price_from <= :max_price AND rating >= :min_rating";
    $query .= $sort === 'price_desc' ? " ORDER BY price_from DESC" : " ORDER BY price_from ASC";
    $query .= " LIMIT :per_page OFFSET :offset";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':city', "%$city%");
    $stmt->bindValue(':min_price', $min_price, PDO::PARAM_STR);
    $stmt->bindValue(':max_price', $max_price, PDO::PARAM_STR);
    $stmt->bindValue(':min_rating', $min_rating, PDO::PARAM_INT);
    $stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $hotels = $stmt->fetchAll();

    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM hotels WHERE city LIKE :city AND price_from >= :min_price AND price_from <= :max_price AND rating >= :min_rating");
    $count_stmt->bindValue(':city', "%$city%");
    $count_stmt->bindValue(':min_price', $min_price, PDO::PARAM_STR);
    $count_stmt->bindValue(':max_price', $max_price, PDO::PARAM_STR);
    $count_stmt->bindValue(':min_rating', $min_rating, PDO::PARAM_INT);
    $count_stmt->execute();
    $total_hotels = $count_stmt->fetchColumn();
    $total_pages = ceil($total_hotels / $per_page);
} catch (PDOException $e) {
    die('Query failed: ' . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Hotels.com Clone</title>
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
            padding: 40px 0;
            color: white;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            animation: fadeIn 1s ease-in;
        }
        .filter-form {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            animation: slideIn 0.8s ease-out;
        }
        .filter-form input, .filter-form select {
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .filter-form input:focus, .filter-form select:focus {
            outline: none;
            border-color: #6b48ff;
            box-shadow: ought: 0 0 8px rgba(107, 72, 255, 0.5);
        }
        .filter-form button {
            padding: 10px 20px;
            background: linear-gradient(45deg, #6b48ff, #00ddeb);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            animation: bounce 2s infinite;
        }
        .filter-form button:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .results {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            transition: transform 0.3s, box-shadow 0.3s;
            animation: fadeInUp 1s ease-out;
        }
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        }
        .card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.5s;
        }
        .card:hover img {
            transform: scale(1.1);
        }
        .card-content {
            padding: 15px;
        }
        .pagination {
            text-align: center;
            margin-top: 20px;
        }
        .pagination a {
            display: inline-block;
            padding: 10px 15px;
            margin: 0 5px;
            background: linear-gradient(45deg, #4ecdc4, #6b48ff);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: transform 0.2s;
        }
        .pagination a:hover {
            transform: scale(1.1);
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
            .filter-form { flex-direction: column; }
            .filter-form input, .filter-form select, .filter-form button { width: 100%; }
            .results { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Search Results</h1>
        </div>
        <form class="filter-form" method="GET">
            <input type="hidden" name="city" value="<?php echo htmlspecialchars($city); ?>">
            <input type="hidden" name="check_in" value="<?php echo htmlspecialchars($check_in); ?>">
            <input type="hidden" name="check_out" value="<?php echo htmlspecialchars($check_out); ?>">
            <label for="min_price">Min Price</label>
            <input type="number" id="min_price" name="min_price" value="<?php echo htmlspecialchars($min_price); ?>" aria-label="Minimum price">
            <label for="max_price">Max Price</label>
            <input type="number" id="max_price" name="max_price" value="<?php echo htmlspecialchars($max_price); ?>" aria-label="Maximum price">
            <label for="min_rating">Min Rating</label>
            <select id="min_rating" name="min_rating" aria-label="Minimum rating">
                <option value="0" <?php echo $min_rating == 0 ? 'selected' : ''; ?>>Any</option>
                <option value="3" <?php echo $min_rating == 3 ? 'selected' : ''; ?>>3+</option>
                <option value="4" <?php echo $min_rating == 4 ? 'selected' : ''; ?>>4+</option>
            </select>
            <label for="sort">Sort By</label>
            <select id="sort" name="sort" aria-label="Sort order">
                <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
            </select>
            <button type="submit">Apply Filters</button>
        </form>
        <div class="results">
            <?php
            if (empty($hotels)) {
                echo '<p>No hotels found.</p>';
            } else {
                foreach ($hotels as $hotel) {
                    echo '<div class="card">
                        <img src="https://via.placeholder.com/300x200?text=' . urlencode($hotel['name']) . '" alt="' . htmlspecialchars($hotel['name']) . '" loading="lazy">
                        <div class="card-content">
                            <h3>' . htmlspecialchars($hotel['name']) . '</h3>
                            <p>' . htmlspecialchars($hotel['city']) . ' - From $' . number_format($hotel['price_from'], 2) . '</p>
                            <p>Rating: ' . number_format($hotel['rating'], 1) . '</p>
                            <button onclick="window.location.href=\'hotel.php?id=' . $hotel['id'] . '&check_in=' . urlencode($check_in) . '&check_out=' . urlencode($check_out) . '\'" style="background: linear-gradient(45deg, #6b48ff, #00ddeb); color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">View Details</button>
                        </div>
                    </div>';
                }
            }
            ?>
        </div>
        <div class="pagination">
            <?php
            for ($i = 1; $i <= $total_pages; $i++) {
                echo '<a href="?city=' . urlencode($city) . '&check_in=' . urlencode($check_in) . '&check_out=' . urlencode($check_out) . '&min_price=' . $min_price . '&max_price=' . $max_price . '&min_rating=' . $min_rating . '&sort=' . $sort . '&page=' . $i . '">' . $i . '</a>';
            }
            ?>
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
        window.onload = () => showToast('Search results loaded!');
    </script>
</body>
</html>
