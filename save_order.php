<?php
$data = json_decode(file_get_contents("php://input"), true);
$conn = new mysqli("localhost", "root", "", "my_database");

if ($conn->connect_error) {
    http_response_code(500);
    exit("Connection failed.");
}

$stmt = $conn->prepare("INSERT INTO orders (name, email, street, city, state, postal_code, country, order_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssss",
    $data['name'],
    $data['email'],
    $data['address']['address_line_1'],
    $data['address']['admin_area_2'],
    $data['address']['admin_area_1'],
    $data['address']['postal_code'],
    $data['address']['country_code'],
    $data['orderID']
);
$stmt->execute();
$stmt->close();
$conn->close();
?>
