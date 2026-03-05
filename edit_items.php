<?php
require 'db.php';   // this loads the database connection

// $host = "localhost";
// $user = "root";
// $password = '';
// $database = "my_database";

// $conn = new mysqli($host, $user, $password, $database);
// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }

session_start();

if (!isset($_SESSION['user_name'])) {
    header("Location: seller_login.php");
    exit;
}

/* ---------- DATABASE CONNECTION ---------- */


/* ---------- SEARCH QUERY ---------- */
$q = trim($_GET['q'] ?? "");

/* ---------- FETCH ITEMS ---------- */
if ($q === "") {

    $result = $conn->query("SELECT id, name, description, price, quantity, image 
                            FROM items 
                            ORDER BY id DESC");

    $items = $result->fetch_all(MYSQLI_ASSOC);

} else {

    $stmt = $conn->prepare("SELECT id, name, description, price, quantity, image
                            FROM items
                            WHERE name LIKE ? OR description LIKE ?
                            ORDER BY id DESC");

    $like = "%$q%";
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();

    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>My Items</title>
  <style>
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ccc; padding: 10px; text-align: left; vertical-align: top; }
    img { width: 80px; height: auto; border-radius: 6px; }
    .top { display:flex; gap:12px; align-items:center; justify-content:space-between; flex-wrap:wrap; margin-bottom: 12px; }
  </style>
</head>
<body>

  <div class="top">
    <div>
      <a href="index.html">Home</a>
      <a href="adding_items.php">Adding Items</a>
      <a href="Orders.php">New Orders </a>
      <a href="logout.php">Logout</a>
      <!-- <a href="seller_login.php">Add Items</a> | -->
    </div>

    <h2>Welcome Seller: <?= htmlspecialchars($_SESSION['user_name']) ?></h2>
    <form method="get" action="edit_items.php">
      <input type="text" name="q" placeholder="Search items..." value="<?= htmlspecialchars($q) ?>">
      <button type="submit">Search</button>
      <?php if ($q !== ""): ?>
        <a href="edit_items.php">Clear</a>
      <?php endif; ?>
    </form>
  </div>

  <h2>My Items (<?= count($items) ?>)</h2>

  <table>
    <tr>
      <th>Image</th>
      <th>Item</th>
      <th>Price</th>
      <th>Qty</th>
      <th>Actions</th>
    </tr>

    <?php foreach ($items as $it): ?>
      <tr>
        <td>
          <?php if (!empty($it['image'])): ?>
            <img src="uploads/<?= htmlspecialchars($it['image']) ?>" alt="">
          <?php else: ?>
            No image
          <?php endif; ?>
        </td>
        <td>
          <b><?= htmlspecialchars($it['name']) ?></b><br>
          <small><?= nl2br(htmlspecialchars($it['description'])) ?></small>
        </td>
        <td>$<?= number_format((float)$it['price'], 2) ?></td>
        <td><?= (int)$it['quantity'] ?></td>
        <td>
          <a href="seller_edit.php?id=<?= (int)$it['id'] ?>">Edit</a>
          |
          <a href="seller_delete.php?id=<?= (int)$it['id'] ?>"
             onclick="return confirm('Delete this item?')">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>

</body>
</html>
