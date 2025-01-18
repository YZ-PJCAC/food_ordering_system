
<?php
include '../asset/connect.php'; // Ensure this file connects to your database

// Fetch all products from the `products` table
try {
    $stmt = $pdo->query("SELECT product_id, name, price, image, visible FROM products ORDER BY product_id ASC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'products' => $products]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching products: ' . $e->getMessage()]);
}
?>
