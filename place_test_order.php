<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$order_number = "TEST-" . time();
$buyer_name = $_SESSION['user_name'];
$buyer_email = "test@email.com";
$status = "NEW";

$shipping_name = $_POST['shipping_name'];
$shipping_address1 = $_POST['shipping_address1'];
$shipping_address2 = $_POST['shipping_address2'];
$shipping_city = $_POST['shipping_city'];
$shipping_state = $_POST['shipping_state'];
$shipping_zip = $_POST['shipping_zip'];
$shipping_country = $_POST['shipping_country'];
$buyer_phone = $_POST['buyer_phone'];

// Get cart total
$stmt = $conn->prepare("
    SELECT cart.quantity, items.price
    FROM cart
    JOIN items ON cart.item_id = items.id
    WHERE cart.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;
while ($row = $result->fetch_assoc()) {
    $total += $row['price'] * $row['quantity'];
}

// Save order
$stmt = $conn->prepare("
    INSERT INTO orders (
        user_id,
        order_number,
        buyer_name,
        buyer_email,
        total,
        status,
        shipping_name,
        shipping_address1,
        shipping_address2,
        shipping_city,
        shipping_state,
        shipping_zip,
        shipping_country,
        buyer_phone
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "isssdsssssssss",
    $user_id,
    $order_number,
    $buyer_name,
    $buyer_email,
    $total,
    $status,
    $shipping_name,
    $shipping_address1,
    $shipping_address2,
    $shipping_city,
    $shipping_state,
    $shipping_zip,
    $shipping_country,
    $buyer_phone
);

$stmt->execute();

$order_id = $conn->insert_id;

// Save cart items into order_items BEFORE deleting cart
$stmt = $conn->prepare("
    SELECT cart.item_id, cart.quantity, items.name, items.image, items.price
    FROM cart
    JOIN items ON cart.item_id = items.id
    WHERE cart.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cartItems = $stmt->get_result();

while ($item = $cartItems->fetch_assoc()) {
    $stmt2 = $conn->prepare("
        INSERT INTO order_items (
            order_id,
            item_id,
            item_name,
            item_image,
            price,
            quantity
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt2->bind_param(
        "iissdi",
        $order_id,
        $item['item_id'],
        $item['name'],
        $item['image'],
        $item['price'],
        $item['quantity']
    );

    $stmt2->execute();
}

// Empty cart after saving items
$stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

header("Location: my_orders.php");
exit;
?>