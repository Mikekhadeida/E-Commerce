<?php
session_start();
if (!isset($_SESSION['user_name'])) {
    header("Location: seller_login.php");
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=my_database", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: seller_items.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM items WHERE id=?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    die("Item not found.");
}

$message = "";

// UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $desc = $_POST['description'] ?? '';
    $price = (float)($_POST['price'] ?? 0);
    $qty = (int)($_POST['quantity'] ?? 0);

    // Keep old image unless a new one is uploaded
    $image_name = $item['image'] ?? '';

    if (!empty($_FILES['image']['name'])) {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $image_name = basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $image_name;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image_name = $item['image'] ?? '';
            $message = "⚠️ Image upload failed (kept old image).";
        }
    }

    $up = $pdo->prepare("UPDATE items SET name=?, description=?, price=?, quantity=?, image=? WHERE id=?");
    $up->execute([$name, $desc, $price, $qty, $image_name, $id]);

    header("Location: seller_items.php");
    exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Edit Item</title>
</head>
<body>
  <a href= "edit_items.php">Go back to Edit items page!</a>
  <h2>Edit Item</h2>

  <?php if (!empty($item['image'])): ?>
    <p><b>Current Image:</b></p>
    <img src="uploads/<?= htmlspecialchars($item['image']) ?>" width="150" style="border-radius:8px;">
  <?php else: ?>
    <p><i>No image uploaded yet.</i></p>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data">
    <p>
      <input name="name" value="<?= htmlspecialchars($item['name']) ?>" required>
    </p>

    <p>
      <textarea name="description" rows="4" cols="30"><?= htmlspecialchars($item['description']) ?></textarea>
    </p>

    <p>
      <input name="price" type="number" step="0.01" value="<?= htmlspecialchars($item['price']) ?>" required>
    </p>

    <p>
      <input name="quantity" type="number" value="<?= (int)$item['quantity'] ?>">
    </p>

    <p>
      <label>Change Image (optional):</label><br>
      <input type="file" name="image">
    </p>

    <button type="submit">Save</button>
  </form>

  <?php if ($message) echo "<p>$message</p>"; ?>

  <p><a href="edit_items.php">Back</a></p>
</body>
</html>
