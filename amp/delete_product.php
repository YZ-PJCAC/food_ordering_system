<?php
// Include database connection
include '../asset/connect.php';

// Set the response header to return JSON
header('Content-Type: application/json');

try {
    // Decode the incoming JSON request
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate input
    if (!isset($data['product_id'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit();
    }

    $productId = intval($data['product_id']);

    // Delete the product from the database
    $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = :product_id");
    $stmt->execute([':product_id' => $productId]);

    // Check if the deletion was successful
    if ($stmt->rowCount()) {
        echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
