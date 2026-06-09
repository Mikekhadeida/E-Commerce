<?php
$data = json_decode(file_get_contents("php://input"), true);

$conn = new mysqli("localhost", "root", "", "my_database");

if ($conn->connect_error) {
    http_response_code(500);
    exit("Connection failed.");
}

$stmt = $conn->prepare("
    INSERT INTO orders (
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
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$order_number = $data['orderID'];
$buyer_name = $data['name'];
$buyer_email = $data['email'];
$total = 49.99;
$status = "NEW";

$shipping_name = $data['shipping_name'];
$shipping_address1 = $data['shipping_address1'];
$shipping_address2 = $data['shipping_address2'];
$shipping_city = $data['shipping_city'];
$shipping_state = $data['shipping_state'];
$shipping_zip = $data['shipping_zip'];
$shipping_country = $data['shipping_country'];
$buyer_phone = $data['buyer_phone'];

$stmt->bind_param(
    "sssdsssssssss",
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

$stmt->close();
$conn->close();

echo "Order saved";
?>