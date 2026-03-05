<?php
session_start();

if (!isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = 'TestSeller';
}

require 'db.php'; // <-- uses $conn (mysqli)

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $desc = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $qty = $_POST['quantity'] ?? 0;

    // -----------------------------------------
    //  IMAGE UPLOAD HANDLING (PUT THIS HERE)
    // -----------------------------------------
    $image_name = 'default.png';

    if (!empty($_FILES['image']['name'])) {
        $uploadDir = "uploads/";

        // Create folder if it does not exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $image_name = basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $image_name;

        // Move file from temp folder → uploads/
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image_name = 'default.png'; // fallback
        }
    }
    // -----------------------------------------
    // END IMAGE UPLOAD
    // -----------------------------------------

        if ($name !== '' && $price > 0) {
        // Prepare insert
        $stmt = $conn->prepare("INSERT INTO items (name, description, price, quantity, image) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            $message = "Error preparing statement: " . $conn->error;
        } else {
            // bind_param types:
            // s = string, d = decimal/float, i = integer
            $stmt->bind_param("ssdis", $name, $desc, $price, $qty, $image_name);

            if ($stmt->execute()) {
                $message = "✅ Item added successfully!";
            } else {
                $message = "Error inserting item: " . $stmt->error;
            }

            $stmt->close();
        }
    } else {
        $message = "⚠️ Please enter a valid name and price.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Seller - HodedahCo</title>
  <link rel="stylesheet" href="<?= '/HodedahCo/styles.css?v=1' ?>">


</head>
<body>
  <a href="index.html">Home</a>
  <a href="edit_items.php">Items Edit</a>
  <a href="Orders.php">New Orders </a>

  <a href="logout.php">Logout</a>

  <h2>Welcome Seller: <?= htmlspecialchars($_SESSION['user_name']) ?></h2>

  <form method="post" action="" enctype="multipart/form-data">
      <input type="text" name="name" placeholder="Item Name" required><br>
      <textarea name="description" placeholder="Description"></textarea><br>
      <input type="number" step="0.01" name="price" placeholder="Price" required><br>
      <input type="number" name="quantity" placeholder="Quantity"><br>

      <label>Image:</label>
      <input type="file" name="image"><br><br>

      <button type="submit">Add Item</button>

  </form>

  <?php if (!empty($message)) echo "<p>$message</p>"; ?>
</body>
</html>
