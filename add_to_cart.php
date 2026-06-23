<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['item_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$item_id = (int)$_GET['item_id'];

// Check if item already exists in cart
$stmt = $conn->prepare("
    SELECT id
    FROM cart
    WHERE user_id = ? AND item_id = ?
");
$stmt->bind_param("ii", $user_id, $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {

    // Increase quantity
    $stmt = $conn->prepare("
        UPDATE cart
        SET quantity = quantity + 1
        WHERE user_id = ? AND item_id = ?
    ");
    $stmt->bind_param("ii", $user_id, $item_id);
    $stmt->execute();

} else {

    // Add new item
    $stmt = $conn->prepare("
        INSERT INTO cart (user_id, item_id, quantity)
        VALUES (?, ?, 1)
    ");
    $stmt->bind_param("ii", $user_id, $item_id);
    $stmt->execute();
}

// Go directly to cart page
header("Location: cart.php");
exit;
?>