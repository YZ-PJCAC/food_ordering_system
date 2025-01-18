<?php
session_start();
include '../asset/connect.php';

// Redirect to login if the user is not authenticated
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart items for the current user
try {
    $stmt = $pdo->prepare("
        SELECT c.cart_id, p.product_id, p.name, p.price, p.image, c.quantity
        FROM cart c
        JOIN products p ON c.product_id = p.product_id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="cart.css">
    
    <title>Cart</title>
</head>
<body>
<div class="container">
    <!-- Navigation Bar -->
    <div class="top-section">
        <img class="logo" src="../icon/logo.jpeg" alt="Logo">
        <nav>
            <ul>
            <li><a href="../cusdashboard.php">Home</a></li>
                    <li><a href="../Ourteam.php">Our Team</a></li>
                    <li><a href="cart.php">Cart</a></li>
                    <li><a href="../transaction.php">Transaction Details</a></li>
                    <li><a href="../QnA.php">Help</a></li>
            </ul>
        </nav>
      
        <a href="../cusprofileedit.php">
                <img class="profile" src="../icon/profilerb.png" alt="Profile">
            </a>
       
    </div>

    <div class="cart-container">

    <div class="select-all-container">
            <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)">
            <label for="select-all">Select All</label>
        </div>

        <div class="cart-items">
            <?php if (empty($cartItems)): ?>
                <p>Your cart is empty. <a href="../cusdashboard.php">Go shopping!</a></p>
            <?php else: ?>
                <form id="cart-form" method="POST" action="../checkout.php">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item">
                            <input 
                                type="checkbox" 
                                class="cart-checkbox" 
                                name="selected_items[]" 
                                value="<?php echo $item['product_id']; ?>"
                                onchange="calculateSelectedTotal()"
                            >
                            <img src="<?php echo (!empty($item['image']) && file_exists('../uploads/' . $item['image'])) 
    ? '../uploads/' . htmlspecialchars($item['image']) 
    : '../icon/default-placeholder.png'; ?>" 
    alt="<?php echo htmlspecialchars($item['name']); ?>">

                            <div class="cart-item-description">
                                <p><strong><?php echo htmlspecialchars($item['name']); ?></strong></p>
                                <p class="cart-item-price">RM <?php echo number_format($item['price'], 2); ?></p>
                            </div>
                            <div class="cart-item-actions">
                                <button type="button" onclick="updateQuantity(<?php echo $item['product_id']; ?>, -1)">-</button>
                                <p id="quantity-<?php echo $item['product_id']; ?>"><?php echo $item['quantity']; ?></p>
                                <button type="button" onclick="updateQuantity(<?php echo $item['product_id']; ?>, 1)">+</button>
                                <button type="button" onclick="removeItem(<?php echo $item['product_id']; ?>)">Delete</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </form>
            <?php endif; ?>
        </div>

        <div class="checkout-tab">
            <h2>Checkout</h2>
            <div class="checkout-products" id="checkout-products">
                <!-- Selected products will appear here -->
            </div>
            <p class="total">Total: RM 0.00</p>
            <button type="button" onclick="redirectToCheckout()">Proceed to Checkout</button>
        </div>
    </div>
</div>

    <script>
        function updateQuantity(productId, change) {
            fetch('update_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId, change: change }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || "Failed to update quantity.");
                }
            })
            .catch(error => console.error("Error updating cart:", error));
        }

        function removeItem(productId) {
            fetch('update_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId, change: 0 }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || "Failed to remove item.");
                }
            })
            .catch(error => console.error("Error removing item:", error));
        }

            // Function to toggle all checkboxes
    function toggleSelectAll(selectAllCheckbox) {
        const checkboxes = document.querySelectorAll(".cart-checkbox");
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
        calculateSelectedTotal(); // Recalculate total after toggling
    }


        function calculateSelectedTotal() {
            const checkboxes = document.querySelectorAll(".cart-checkbox:checked");
            const productsContainer = document.getElementById("checkout-products");
            let total = 0;

            productsContainer.innerHTML = ""; // Clear previous selected items

            checkboxes.forEach((checkbox) => {
                const productId = checkbox.value;
                const quantity = parseInt(document.getElementById(`quantity-${productId}`).textContent);
                const price = parseFloat(checkbox.closest(".cart-item").querySelector(".cart-item-price").textContent.replace("RM ", ""));
                const subtotal = price * quantity;

                total += subtotal;

                // Add selected product to the right-side display
                const productDiv = document.createElement("div");
                productDiv.className = "checkout-product";
                productDiv.innerHTML = `
                    <p>${checkbox.closest(".cart-item").querySelector(".cart-item-description strong").textContent}</p>
                    <p>RM ${subtotal.toFixed(2)}</p>
                `;
                productsContainer.appendChild(productDiv);
            });

            // Update total price
            document.querySelector(".checkout-tab .total").textContent = `Total: RM ${total.toFixed(2)}`;
        }

        function redirectToCheckout() {
            const selectedItems = Array.from(document.querySelectorAll(".cart-checkbox:checked")).map((checkbox) => {
                return checkbox.value;
            });

            if (selectedItems.length === 0) {
                alert("Please select at least one item to proceed to checkout.");
                return;
            }

            const params = new URLSearchParams();
            selectedItems.forEach((item) => params.append("selected_items[]", item));
            document.getElementById("cart-form").action = "../checkout.php?" + params.toString();
            document.getElementById("cart-form").submit();



        }
    </script>
</body>
</html>
