<?php
include '../asset/connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

try {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$data['user_id'], $data['product_id']]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
