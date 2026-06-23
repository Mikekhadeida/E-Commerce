<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['cart_id'])) {
    $cart_id = (int)$_GET['cart_id'];

    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();

} elseif (isset($_GET['item_id'])) {
    $item_id = (int)$_GET['item_id'];

    $stmt = $conn->prepare("DELETE FROM cart WHERE item_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $item_id, $user_id);
    $stmt->execute();
}

header("Location: cart.php");
exit;
?>