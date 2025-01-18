<?php
include '../asset/connect.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $productId = $data['product_id'];

    // Fetch the current visibility status
    $stmt = $pdo->prepare("SELECT is_visible FROM products WHERE product_id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $newVisibility = !$product['is_visible'];

        // Update the visibility
        $updateStmt = $pdo->prepare("UPDATE products SET is_visible = ? WHERE product_id = ?");
        $updateStmt->execute([$newVisibility, $productId]);

        echo json_encode([
            'success' => true,
            'is_visible' => $newVisibility,
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
