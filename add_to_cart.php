<?php
require 'db.php';
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    die("Not logged in. user_id missing.");
}

if (!isset($_GET['item_id'])) {
    die("No item_id found.");
}

$user_id = $_SESSION['user_id'];
$item_id = $_GET['item_id'];

echo "User ID: " . $user_id . "<br>";
echo "Item ID: " . $item_id . "<br>";

$stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND item_id = ?");

if (!$stmt) {
    die("Prepare failed on SELECT: " . $conn->error);
}

$stmt->bind_param("ii", $user_id, $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "Item already in cart. Updating quantity...<br>";

    $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND item_id = ?");

    if (!$stmt) {
        die("Prepare failed on UPDATE: " . $conn->error);
    }

    $stmt->bind_param("ii", $user_id, $item_id);
    $stmt->execute();

    echo "Updated successfully.";
} else {
    echo "Item not in cart. Inserting new row...<br>";

    $stmt = $conn->prepare("INSERT INTO cart (user_id, item_id, quantity) VALUES (?, ?, 1)");

    if (!$stmt) {
        die("Prepare failed on INSERT: " . $conn->error);
    }

    $stmt->bind_param("ii", $user_id, $item_id);
    $stmt->execute();

    echo "Inserted successfully.";
}
?>