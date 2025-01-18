<?php
session_start();
include 'asset/connect.php';

// Redirect if user is not authenticated
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch order and product details
try {
    $stmt = $pdo->prepare("
        SELECT 
            o.order_id,
            o.total_amount,
            o.status,
            o.created_at,
            GROUP_CONCAT(CONCAT(p.name, ' (x', oi.quantity, ')') SEPARATOR ', ') AS product_names
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN products p ON oi.product_id = p.product_id
        WHERE o.user_id = ?
        GROUP BY o.order_id
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/transaction.css">

    <style>
        /* Add your styles here */
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
        }

        .top-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 90%;
            max-width: 1200px;
            background-color: #ffe6e6;
            padding: 10px 20px;
            border-radius: 50px;
            margin: 20px auto;
            flex-wrap: wrap;
        }

        .transaction-title {
            text-align: center;
        }

        .transaction-section {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .order-item {
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-title {
            font-size: 18px;
            font-weight: bold;
        }

        .order-details {
            margin-top: 10px;
            font-size: 14px;
            color: #555;
        }

        .total-price {
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
        }

        .print-receipt-btn {
    background-color: #007bff;
    color: white;
    font-size: 16px;
    font-weight: bold;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: 0.3s ease;
    margin-top: 10px;
}

.print-receipt-btn:hover {
    background-color: #0056b3;
}

    </style>
    <title>Transaction Details</title>
</head>
<body>
    <header class="top-section">
        <img class="logo" src="icon/logo.jpeg" alt="Logo">
        <nav>
            <ul>
                <li><a href="cusdashboard.php">Home</a></li>
                <li><a href="Ourteam.php">Our Team</a></li>
                <li><a href="cart/cart.php">Cart</a></li>
                <li><a href="transaction.php">Transaction Details</a></li>
                <li><a href="QnA.php">Help</a></li>
            </ul>
        </nav>
        <a href="cusprofileedit.php">
            <img class="profile" src="icon/profilerb.png" alt="Profile">
        </a>
    </header>

    <div class="transaction-title">
        <h1>Transaction Details</h1>
    </div>

    <div class="transaction-section">
        <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $index => $order): ?>
                <div class="order-item">
                    <p class="order-title">Order ID: <?php echo htmlspecialchars($order['order_id']); ?></p>
                    <p class="order-details">Products: <?php echo htmlspecialchars($order['product_names']); ?></p>
                    <p class="order-details">Order Date: <?php echo htmlspecialchars($order['created_at']); ?></p>
                    <p class="order-details">Status: <?php echo htmlspecialchars($order['status']); ?></p>
                    <p class="total-price">Total: RM <?php echo number_format($order['total_amount'], 2); ?></p>
                    <?php if ($index === 0): ?>
                        <div class="button-container">
    <button class="print-receipt-btn" onclick="window.location.href='receipttransac.php?order_id=<?php echo $order['order_id']; ?>'">
        Print Receipt
    </button>
</div>

                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No transactions found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
