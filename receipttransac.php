<?php
session_start();
include 'asset/connect.php';

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    echo "<p>Order ID is required. <a href='transaction.php'>Go back to Transaction Details</a>.</p>";
    exit();
}

$order_id = $_GET['order_id'];

try {
    // Fetch order details
    $orderStmt = $pdo->prepare("
        SELECT o.total_amount, o.created_at, u.username
        FROM orders o
        JOIN users u ON o.user_id = u.user_id
        WHERE o.order_id = ?
    ");
    $orderStmt->execute([$order_id]);
    $order = $orderStmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo "<p>Order not found. <a href='transaction.php'>Go back to Transaction Details</a>.</p>";
        exit();
    }

    // Fetch order items
    $itemsStmt = $pdo->prepare("
        SELECT p.name, oi.quantity, oi.price
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = ?
    ");
    $itemsStmt->execute([$order_id]);
    $orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }

        .receipt-container {
            background-color: #f3f3f3;
            width: 90%;
            max-width: 400px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .receipt-details {
            text-align: left;
            margin: 20px 0;
        }

        .receipt-details p {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }
        
        .receipt-logo {
            width: 50px;
            height: 50px;
            clip-path: circle(50%);
        }

        .total {
            font-weight: bold;
            margin-top: 10px;
        }

        .button-container {
            margin-top: 20px;
        }

        .button-container a {
            display: inline-block;
            text-decoration: none;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            font-size: 14px;
        }

        .button-container a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="receipt-container" id="receipt-content">
        <img class="receipt-logo" src="icon/logo.jpeg" alt="Logo">
        <h1>Receipt</h1>
        <p>Hardie</p>
        <p>University Malaysia Sarawak</p>
        <p>94300 Kota Samarahan Sarawak</p>
        <div class="receipt-details">
            <p><span>Date:</span> <span><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></span></p>
            <p><span>Time:</span> <span><?php echo date('H:i', strtotime($order['created_at'])); ?></span></p>
            <?php foreach ($orderItems as $item): ?>
                <p><span><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></span> 
                   <span>RM <?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                </p>
            <?php endforeach; ?>
            <p class="total"><span>Total:</span> <span>RM <?php echo number_format($order['total_amount'], 2); ?></span></p>
        </div>
        <p><strong>Order Name:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
    </div>

    <div class="button-container">
        <a href="transaction.php">View Transaction Details</a>
    </div>

    <script>
        window.onload = function () {
            window.print();
        };
    </script>
</body>
</html>
