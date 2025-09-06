include 'db.php';
header('Content-Type: application/json');
$term = isset($_GET['term']) ? '%' . $_GET['term'] . '%' : '%';
$stmt = $pdo->prepare("SELECT name, city FROM hotels WHERE name LIKE ? OR city LIKE ? LIMIT 5");
$stmt->execute([$term, $term]);
echo json_encode($stmt->fetchAll());
