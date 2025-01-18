<?php
include '../asset/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $categories = trim($_POST['categories']); // Comma-separated categories
    $image = $_FILES['image'];

    if (empty($name) || empty($price) || empty($categories)) {
        echo json_encode(['success' => false, 'message' => 'All fields except image are required.']);
        exit;
    }

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Insert product
        $imageName = null;
        if (!empty($image['name'])) {
            $imageName = time() . '_' . $image['name'];
            move_uploaded_file($image['tmp_name'], "../uploads/$imageName");
        }

        $stmt = $pdo->prepare("INSERT INTO products (name, price, image) VALUES (:name, :price, :image)");
        $stmt->execute([
            'name' => $name,
            'price' => $price,
            'image' => $imageName
        ]);
        $productId = $pdo->lastInsertId();

        // Process categories
        $categoriesArray = array_map('trim', explode(',', $categories));
        foreach ($categoriesArray as $categoryName) {
            if (empty($categoryName)) continue;

            // Check if category exists
            $stmt = $pdo->prepare("SELECT category_id FROM categories WHERE name = :name");
            $stmt->execute(['name' => $categoryName]);
            $category = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$category) {
                // Insert new category
                $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (:name)");
                $stmt->execute(['name' => $categoryName]);
                $categoryId = $pdo->lastInsertId();
            } else {
                $categoryId = $category['category_id'];
            }

            // Link product to category
            $stmt = $pdo->prepare("INSERT INTO product_categories (product_id, category_id) VALUES (:product_id, :category_id)");
            $stmt->execute(['product_id' => $productId, 'category_id' => $categoryId]);
        }

        // Commit transaction
        $pdo->commit();

        echo json_encode(['success' => true, 'message' => 'Product added successfully.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
