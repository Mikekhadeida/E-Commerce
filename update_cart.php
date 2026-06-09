<?php
require 'db.php';
session_start();

$cart_id = $_GET['cart_id'];
$action = $_GET['action'];

if ($action == "plus") {

    $stmt = $conn->prepare("
        UPDATE cart
        SET quantity = quantity + 1
        WHERE id = ?
    ");
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();

} elseif ($action == "minus") {

    $stmt = $conn->prepare("
        UPDATE cart
        SET quantity = quantity - 1
        WHERE id = ? AND quantity > 1
    ");
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
}

header("Location: Buyers/cart.php");
exit;
?>