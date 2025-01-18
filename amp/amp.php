<?php
// Include database connection
include '../asset/connect.php';

// Fetch products
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories
$categoriesStmt = $pdo->query("SELECT * FROM categories ORDER BY created_at DESC");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="amp.css">
    <title>Manage Products</title>
</head>
<body>
    <!-- Top Section -->
    <div class="top-section">
        <div class="header">
            <img class="logo" src="../icon/logo.jpeg" alt="Logo">
            <div class="dashboard-welcome">
                <h1>Welcome To The Admin Manage Product Page</h1>
            </div>
            <a href="../adprofileedit.php">
                <img class="profile" src="../icon/profilerb.png" alt="Profile">
            </a>
        </div>
    </div>

    <!-- Bottom Section -->
    <div class="bottom-section">
        <!-- Sidebar Section -->
        <div class="sidebar">
            <h2>Admin Dashboard</h2>
            <ul>
                <li><a href="../addashboard.php">Dashboard</a></li>
                <li><a href="amp.php" class="active">Manage Products</a></li>
                <li><a href="../monthly_sales.php">Monthly Sales</a></li>
                <li><a href="../transacreport.php">Transaction Reports</a></li>
            </ul>
        </div>

        <!-- Main Content Section -->
        <div class="main-content">
            <div class="product-grid-wrapper">
                <!-- Title -->
                <div class="grid-header">
                    <h1>Manage Products</h1>
                    <input type="text" id="search-bar" placeholder="Search products by name..." onkeyup="filterProducts()">
                </div>

                <!-- Product Grid -->
                <div class="product-grid" id="product-grid">
                    <?php foreach ($products as $product): ?>
                    <div class="product-card" id="product-card-<?php echo $product['product_id']; ?>">
                        <!-- Buttons Section -->
                        <div class="product-card-buttons">
                            <!-- Edit Button -->
                            <a href="../productedit.php?product_id=<?php echo $product['product_id']; ?>" class="edit-product-button">
                                <img src="../icon/edit.png" alt="Edit">
                            </a>

                            <!-- Toggle Visibility Button -->
                            <button class="toggle-visibility-button" onclick="toggleVisibility(<?php echo $product['product_id']; ?>)">
                                <img src="../icon/close.png" alt="Toggle Visibility">
                            </button>

                            <!-- Delete Button -->
                            <button class="delete-product-button" onclick="deleteProduct(<?php echo $product['product_id']; ?>)">
                                <img src="../icon/delete.png" alt="Delete">
                            </button>
                        </div>

                        <!-- Product Image -->
                        <div class="image-placeholder">
                            <?php if (!empty($product['image']) && file_exists('../uploads/' . $product['image'])): ?>
                                <img src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <img src="../icon/default-placeholder.png" alt="No Image Available">
                            <?php endif; ?>
                        </div>

                        <!-- Product Details -->
                        <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                        <p>RM <?php echo number_format($product['price'], 2); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Add Product Button -->
                <button id="add-product-button" class="add-product-btn" onclick="showAddProductForm()">
                    Add Product
                </button>

                <!-- Add Product Form -->
                <form id="add-product-form" style="display: none;" enctype="multipart/form-data">
                    <h3>Add New Product</h3>
                    <input type="text" name="name" id="product-name" placeholder="Product Name" required>
                    <input type="number" name="price" id="product-price" placeholder="Price (RM)" step="0.01" required>
                    <input type="file" name="image" id="product-image" accept="image/*">

                    <div class="category-section">
                        <label for="product-categories">Categories:</label>
                        <input type="text" id="product-categories" name="categories" placeholder="Enter categories (comma-separated)" required>
                    </div>

                    <button type="button" onclick="submitProductForm()">Submit</button>
                    <button type="button" onclick="hideAddProductForm()">Cancel</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Show Add Product Form
        function showAddProductForm() {
            document.getElementById('add-product-form').style.display = 'block';
        }

        // Hide Add Product Form
        function hideAddProductForm() {
            document.getElementById('add-product-form').style.display = 'none';
        }

        // Submit Product Form using JavaScript
        function submitProductForm() {
            const name = document.getElementById('product-name').value.trim();
            const price = document.getElementById('product-price').value.trim();
            const categories = document.getElementById('product-categories').value.trim();
            const image = document.getElementById('product-image').files[0];

            if (!name || !price || !categories) {
                alert('Please fill out all required fields.');
                return;
            }

            const formData = new FormData();
            formData.append('name', name);
            formData.append('price', price);
            formData.append('categories', categories);
            if (image) {
                formData.append('image', image);
            }

            fetch('add_product.php', {
                method: 'POST',
                body: formData,
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.href = 'amp.php'; // Refresh the page
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
        }

        // Toggle Visibility
        function toggleVisibility(productId) {
            fetch('toggle_visibility.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const card = document.getElementById(`product-card-${productId}`);
                    card.style.opacity = data.is_visible ? '1' : '0.5';
                    alert(data.is_visible ? "Product is now visible." : "Product is now invisible.");
                } else {
                    alert("Failed to toggle visibility. Please try again.");
                }
            })
            .catch(error => {
                console.error("Error toggling visibility:", error);
            });
        }

        // Delete Product
        function deleteProduct(productId) {
            const confirmed = confirm("Are you sure you want to delete this product?");
            if (!confirmed) return;

            fetch('delete_product.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const productCard = document.getElementById(`product-card-${productId}`);
                    if (productCard) productCard.remove();
                    alert("Product successfully deleted.");
                } else {
                    alert("Failed to delete product. Please try again.");
                }
            })
            .catch(error => {
                console.error("Error deleting product:", error);
            });
        }


                // Search Filter for Products
                function filterProducts() {
            const searchValue = document.getElementById('search-bar').value.toLowerCase();
            const productCards = document.querySelectorAll('.product-card');

            productCards.forEach(card => {
                const productName = card.querySelector('h2').textContent.toLowerCase();
                if (productName.includes(searchValue)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
