<?php
include 'asset/connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT c.product_id, c.quantity, p.name, p.price
        FROM cart c
        JOIN products p ON c.product_id = p.product_id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['valid' => true, 'cart' => $cartItems]);
} catch (PDOException $e) {
    echo json_encode(['valid' => false, 'error' => $e->getMessage()]);
}
?>
