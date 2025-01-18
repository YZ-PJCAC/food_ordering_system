<?php
session_start();
include 'asset/connect.php';

// Redirect to login if the user is not authenticated
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$selectedItems = $_POST['selected_items'] ?? [];

// Check if any items were selected
if (empty($selectedItems)) {
    echo "<p>No items selected. <a href='cart/cart.php'>Go back to your cart</a>.</p>";
    exit();
}

// Fetch details of selected items from the cart
try {
    $placeholders = implode(',', array_fill(0, count($selectedItems), '?'));
    $stmt = $pdo->prepare("SELECT p.product_id, p.name, p.price, p.image, c.quantity
                           FROM cart c
                           JOIN products p ON c.product_id = p.product_id
                           WHERE c.user_id = ? AND c.product_id IN ($placeholders)");
    $stmt->execute(array_merge([$user_id], $selectedItems));
    $itemsToCheckout = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}

// Function to resolve image paths
function getImagePath($image) {
    $basePath = 'uploads/';
    return !empty($image) && file_exists($basePath . $image) 
        ? $basePath . htmlspecialchars($image) 
        : 'icon/placeholder.png';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/checkout.css">
    <title>Checkout</title>
</head>
<body>
    <header class="top-section">
        <a href="home.html">
            <img class="logo" src="icon/logo.jpeg" alt="Hardie Laksa Logo">
        </a>
        <nav>
            <ul>
                <li><a href="cusdashboard.php">Home</a></li>
                <li><a href="Ourteam.php">Our Team</a></li>
                <li><a href="cart/cart.php">Cart</a></li>
                <li><a href="transaction.php">Transaction Details</a></li>
                <li><a href="QnA.php">Help</a></li>
            </ul>
        </nav>
        <a href="profile.html">
            <img class="profile" src="icon/profile.png" alt="User Profile">
        </a>
    </header>

    <h1><div class="checkout-title">Checkout</div></h1>

    <div class="checkout-section">
        <div class="left-panel">
            <h2>Selected Products</h2>
            <div class="laksa-summary">
                <?php
                $total = 0;
                foreach ($itemsToCheckout as $item) {
                    // Get the resolved image path
                    $imagePath = getImagePath($item['image']);

                    $itemTotal = $item['price'] * $item['quantity'];
                    $total += $itemTotal;

                    echo "
                        <div class='laksa-item'>
                            <img src='$imagePath' alt='" . htmlspecialchars($item['name']) . "' class='product-image'>
                            <div>
                                <p><strong>" . htmlspecialchars($item['name']) . "</strong></p>
                                <p>Quantity: " . $item['quantity'] . "</p>
                                <p>Subtotal: RM " . number_format($itemTotal, 2) . "</p>
                            </div>
                        </div>
                    ";
                }
                ?>
            </div>
            <div class="total-price">
                <h3>Total Price</h3>
                <p>RM <?php echo number_format($total, 2); ?></p>
            </div>
        </div>

        <div class="right-panel">
            <h3>Payment Options</h3>
            <form id="checkout-form" action="receipt.php" method="POST">
                <div class="payment-options">
                    <button type="button" onclick="submitPaymentMethod('Online_Banking')">Online Banking</button>
                    <button type="button" onclick="submitPaymentMethod('Credit/Debit')">Credit/Debit</button>
                    <button type="button" onclick="submitPaymentMethod('Cash')">Cash</button>
                </div>
                <?php foreach ($itemsToCheckout as $item): ?>
                    <input type="hidden" name="selected_items[]" value="<?php echo htmlspecialchars($item['product_id']); ?>">
                    <input type="hidden" name="quantities[<?php echo htmlspecialchars($item['product_id']); ?>]" value="<?php echo htmlspecialchars($item['quantity']); ?>">
                <?php endforeach; ?>
                <input type="hidden" name="payment_method" id="payment-method" value="">
            </form>
            <button class="cancel-btn" onclick="window.location.href='cart/cart.php'">Cancel</button>
        </div>
    </div>

    <script>
        function submitPaymentMethod(method) {
            document.getElementById("payment-method").value = method;
            document.getElementById("checkout-form").submit();
        }
    </script>
</body>
</html>
