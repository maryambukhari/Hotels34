include 'db.php';
$hotel_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT image_url FROM hotel_images WHERE hotel_id = ?");
$stmt->execute([$hotel_id]);
$images = $stmt->fetchAll();
$stmt = $pdo->prepare("SELECT name FROM hotels WHERE id = ?");
$stmt->execute([$hotel_id]);
$hotel = $stmt->fetch();
