include 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) header('Location: login.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    $stmt = $pdo->prepare("INSERT INTO support_tickets (user_id, subject, message) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $subject, $message]);
    header('Location: support_tickets.php');
}
