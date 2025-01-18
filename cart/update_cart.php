<?php
session_start();
include '../asset/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $_SESSION['user_id'];
    $product_id = $data['product_id'];
    $change = $data['change'];

    try {
        if ($change === 0) {
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE cart SET quantity = quantity + ? 
                WHERE user_id = ? AND product_id = ? AND quantity + ? > 0
            ");
            $stmt->execute([$change, $user_id, $product_id, $change]);
        }
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
