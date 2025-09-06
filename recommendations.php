include 'db.php';
$city = isset($_GET['city']) ? $_GET['city'] : '';
$stmt = $pdo->prepare("SELECT * FROM hotels WHERE city = ? ORDER BY rating DESC LIMIT 4");
$stmt->execute([$city]);
$hotels = $stmt->fetchAll();
