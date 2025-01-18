<?php
// Database connection
include '../asset/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $image = $_FILES['image'];

    // Handle image upload
    $imagePath = null;
    if ($image && $image['tmp_name']) {
        $imagePath = '../uploads/' . uniqid() . '-' . basename($image['name']);
        move_uploaded_file($image['tmp_name'], $imagePath);
    }

    // Update product in the database
    $stmt = $pdo->prepare("
        UPDATE products 
        SET name = :name, 
            price = :price, 
            image = COALESCE(:image, image) 
        WHERE product_id = :product_id
    ");
    $stmt->execute([
        'name' => $name,
        'price' => $price,
        'image' => $imagePath,
        'product_id' => $product_id,
    ]);

    // Redirect back to admin page
    header('Location: amp.php');
    exit();
}
?>
