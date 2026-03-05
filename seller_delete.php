<?php
session_start();

// Make sure user is logged in
if (!isset($_SESSION['user_name'])) {
    header("Location: seller_login.php");
    exit;
}

// Database connection
$pdo = new PDO("mysql:host=localhost;dbname=my_database", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// If invalid ID, go back
if ($id <= 0) {
    header("Location: seller_items.php");
    exit;
}

// Optional: delete image file too
$stmt = $pdo->prepare("SELECT image FROM items WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if ($item) {
    if (!empty($item['image']) && file_exists("uploads/" . $item['image'])) {
        unlink("uploads/" . $item['image']); // delete image file
    }

    // Delete from database
    $del = $pdo->prepare("DELETE FROM items WHERE id = ?");
    $del->execute([$id]);
}

// Redirect back to items page
header("Location: seller_items.php");
exit;
?>
