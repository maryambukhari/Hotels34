<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Policies - Hotels.com Clone</title>
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
        .policy-section {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            margin-bottom: 20px;
            animation: slideIn 0.8s ease-out;
        }
        .policy-section h2 {
            color: #6b48ff;
            margin-bottom: 15px;
        }
        .policy-section p, .policy-section ul {
            margin-bottom: 15px;
            color: #333;
            line-height: 1.6;
        }
        .policy-section ul {
            padding-left: 20px;
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
            <h1>Terms & Policies</h1>
            <p>Understand our booking, cancellation, and refund policies</p>
        </div>
        <div class="policy-section">
            <h2>Booking Policy</h2>
            <p>All bookings must be made through our platform. Users must be logged in to book a hotel. Bookings are subject to availability and confirmation.</p>
            <ul>
                <li>Provide accurate check-in/check-out dates and guest details.</li>
                <li>Payments are processed securely via supported methods (credit card, debit card, PayPal, bank transfer).</li>
                <li>Booking confirmation is sent via email upon successful payment.</li>
            </ul>
        </div>
        <div class="policy-section">
            <h2>Cancellation Policy</h2>
            <p>Cancellations can be made by logged-in users through the dashboard. Refund eligibility depends on the hotel’s cancellation terms.</p>
            <ul>
                <li>Free cancellations are available for bookings with a "Free Cancellation" tag until the specified date.</li>
                <li>Non-refunded bookings may incur a cancellation fee.</li>
                <li>Refunds, if applicable, are processed within 7-10 business days.</li>
            </ul>
        </div>
        <div class="policy-section">
            <h2>Refund Policy</h2>
            <p>Refunds are issued for eligible cancellations or failed bookings.</p>
            <ul>
                <li>Refunds are credited to the original payment method.</li>
                <li>Contact support for disputes or issues with refunds.</li>
                <li>No refunds for no-shows or late cancellations.</li>
            </ul>
        </div>
        <p style="text-align: center;">
            <a href="index.php" class="btn">Back to Home</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php" class="btn">My Dashboard</a>
                <a href="logout.php" class="btn">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn">Login</a>
                <a href="register.php" class="btn">Register</a>
            <?php endif; ?>
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
        window.onload = () => showToast('Review our policies for a smooth booking experience.');
    </script>
</body>
</html>
