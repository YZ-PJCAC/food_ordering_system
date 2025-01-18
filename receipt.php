<?php
session_start();
include 'asset/connect.php';

// Redirect if the user is not authenticated
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Retrieve selected items and payment method from POST data
$selectedItems = $_POST['selected_items'] ?? [];
$quantities = $_POST['quantities'] ?? [];
$paymentMethod = $_POST['payment_method'] ?? 'Unknown';

// If no items are selected, redirect back to the checkout page
if (empty($selectedItems)) {
    echo "<p>No items selected. <a href='checkout.php'>Go back to the checkout page.</a></p>";
    exit();
}

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Fetch selected products from the database
    $placeholders = implode(',', array_fill(0, count($selectedItems), '?'));
    $stmt = $pdo->prepare("SELECT product_id, name, price FROM products WHERE product_id IN ($placeholders)");
    $stmt->execute($selectedItems);
    $selectedProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch the user's name
    $userStmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
    $userStmt->execute([$user_id]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    $userName = $user['username'] ?? 'Unknown';

    // Calculate total and prepare receipt details
    $total = 0;
    $receiptDetails = [];
    foreach ($selectedProducts as $product) {
        $productId = $product['product_id'];
        $quantity = $quantities[$productId] ?? 1; // Default quantity to 1 if not set
        $itemTotal = $product['price'] * $quantity;
        $total += $itemTotal;

        $receiptDetails[] = [
            'name' => $product['name'],
            'price' => number_format($product['price'], 2),
            'quantity' => $quantity,
            'total' => number_format($itemTotal, 2),
        ];
    }

    // Insert order into the database
    $orderStmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'completed')");
    $orderStmt->execute([$user_id, $total]);
    $orderId = $pdo->lastInsertId(); // Retrieve the inserted order ID

    // Insert order items into the database
    $orderItemsStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($receiptDetails as $item) {
        $productId = array_search($item['name'], array_column($selectedProducts, 'name'));
        $orderItemsStmt->execute([
            $orderId,
            $selectedProducts[$productId]['product_id'],
            $item['quantity'],
            $selectedProducts[$productId]['price']
        ]);
    }

    // Insert transaction into the database
    $transactionStmt = $pdo->prepare("INSERT INTO transactions (order_id, payment_method, payment_status) VALUES (?, ?, 'paid')");
    $transactionStmt->execute([$orderId, strtolower(str_replace(' ', '_', $paymentMethod))]);

    // Commit transaction
    $pdo->commit();

} catch (PDOException $e) {
    // Rollback on failure
    $pdo->rollBack();
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
            <p><span>Date:</span> <span><?php echo date('d/m/Y'); ?></span></p>
            <p><span>Time:</span> <span><?php echo date('H:i'); ?></span></p>
            <?php foreach ($receiptDetails as $item): ?>
                <p><span><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></span> 
                   <span>RM <?php echo $item['total']; ?></span>
                </p>
            <?php endforeach; ?>
            <p class="total"><span>Total:</span> <span>RM <?php echo number_format($total, 2); ?></span></p>
            <p><span>Paid by:</span> <span><?php echo htmlspecialchars($paymentMethod); ?></span></p>
        </div>
        <p><strong>Order Name:</strong> <?php echo htmlspecialchars($userName); ?></p>
    </div>

    <div class="button-container">
        <a href="transaction.php">View Transaction Details</a>
    </div>

    <script>
        // Automatically trigger the print dialog when the page loads
        window.onload = function () {
            window.print();
        };
    </script>
</body>
</html>
