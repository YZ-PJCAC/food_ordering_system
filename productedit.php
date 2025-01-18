<?php
include 'asset/connect.php';

// Fetch product details based on the provided product_id
$product = null;
if (isset($_GET['product_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$_GET['product_id']]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch categories
$categoriesStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch selected categories for the product
$selectedCategories = [];
if ($product) {
    $categoriesForProductStmt = $pdo->prepare("SELECT category_id FROM product_categories WHERE product_id = ?");
    $categoriesForProductStmt->execute([$product['product_id']]);
    $selectedCategories = $categoriesForProductStmt->fetchAll(PDO::FETCH_COLUMN);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = $_POST['product_id'];
    $productName = trim($_POST['name']);
    $productPrice = trim($_POST['price']);
    $selectedCategories = $_POST['categories'] ?? []; // Get selected categories

    // Update product details
    $updateProductStmt = $pdo->prepare("UPDATE products SET name = ?, price = ? WHERE product_id = ?");
    $updateProductStmt->execute([$productName, $productPrice, $productId]);

    // Update product categories
    try {
        // Remove existing categories for the product
        $deleteCategoriesStmt = $pdo->prepare("DELETE FROM product_categories WHERE product_id = ?");
        $deleteCategoriesStmt->execute([$productId]);

        // Insert the newly selected categories
        if (!empty($selectedCategories)) {
            $insertCategoryStmt = $pdo->prepare("INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)");
            foreach ($selectedCategories as $categoryId) {
                $insertCategoryStmt->execute([$productId, $categoryId]);
            }
        }

        echo "<script>alert('Product and categories updated successfully.');</script>";
    } catch (PDOException $e) {
        echo "<script>alert('An error occurred while updating categories. Please try again.');</script>";
    }
}




// Handle adding a new category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['new_category'])) {
    $newCategory = trim($_POST['new_category']);
    if (!empty($newCategory)) {
        $insertCategoryStmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        try {
            $insertCategoryStmt->execute([$newCategory]);
        } catch (PDOException $e) {
            // Handle duplicate entry or other errors
            if ($e->getCode() == 23000) { // Duplicate entry error code
                echo "<script>alert('Category already exists.');</script>";
            } else {
                echo "<script>alert('An error occurred while adding the category. Please try again.');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/productedit.css">
    <title>Edit Product</title>
</head>

<body>
    <!-- Navigation Section -->
    <div class="header">
        <img class="logo" src="icon/logo.jpeg" alt="Logo">
        <div class="dashboard-welcome">
               <h1>Welcome To Product Edit Page</h1>
           </div>
        <a href="adprofileedit.php">
            <img class="profile" src="icon/profilerb.png" alt="Profile">
        </a>
    </div>



    <!-- Main Content Section -->
    <div class="main-content">
        <h1>Edit Product</h1>

        <?php if ($product): ?>
        <!-- Product Card -->
        <div class="product-card">
        <div class="image-placeholder">
      <img src="<?php echo (!empty($product['image']) && file_exists('../uploads/' . $product['image'])) 
        ? '../uploads/' . htmlspecialchars($product['image']) 
        : 'icon/placeholder.png'; ?>" alt="Product Image">
        </div>
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
            <p>RM <?php echo number_format($product['price'], 2); ?></p>
        </div>

        <!-- Form Section -->
        <form class="edit_form" action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['product_id']); ?>">

            <!-- Product Name Field -->
            <div>
                <label for="name">Product Name:</label>
                <input type="text" id="name" name="name" placeholder="Enter product name" 
                       value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>

            <!-- Product Price Field -->
            <div>
                <label for="price">Product Price (RM):</label>
                <input type="number" step="0.01" id="price" name="price" placeholder="Enter product price" 
                       value="<?php echo htmlspecialchars($product['price']); ?>" required>
            </div>

            <!-- Product Image Field -->
            <div>
                <label for="image">Product Image:</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>

            <!-- Categories Selection -->
      
<div>
    <label for="categories">Categories:</label>
    <select id="categories" name="categories[]" multiple>
        <?php foreach ($categories as $category): ?>
            <option value="<?php echo htmlspecialchars($category['category_id']); ?>" 
                <?php echo in_array($category['category_id'], $selectedCategories) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($category['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <small>Hold Ctrl (Cmd on Mac) to select multiple categories.</small>
</div>


            <!-- Add New Category -->
            <div>
                <label for="new-category">Add New Category:</label>
                <input type="text" id="new-category" name="new_category" placeholder="Enter new category name">
            </div>

            <!-- Action Buttons -->
            <div class="button-container">
                <button type="submit" class="submit_btn">Save Product</button>
                <a href="amp/amp.php" class="cancel_btn">Cancel</a>
            </div>
        </form>
        <?php else: ?>
        <p>Product not found or invalid product ID.</p>
        <?php endif; ?>
    </div>
</body>

</html>
