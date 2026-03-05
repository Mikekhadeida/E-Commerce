<?php
require 'db.php';
session_start();

// Get search query safely
$q = trim($_GET['q'] ?? "");

// Fetch items (search or all)
if ($q === "") {
    $result = $conn->query("SELECT * FROM items ORDER BY created_at DESC");
} else {
    $stmt = $conn->prepare("SELECT * FROM items 
                            WHERE name LIKE ? OR description LIKE ? 
                            ORDER BY created_at DESC");
    $like = "%$q%";
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Home - Shopora</title>
  
  <link rel="stylesheet" href="styles.css">
  <link rel="icon" type="LogoPictures/png" href="Logo.png"> <!-- browser tab logo (favicon) -->

  <!-- PayPal SDK -->
  <script src="https://www.paypal.com/sdk/js?client-id=AYIl_R81qs541PWmUPKIZt7xDnEO8p6hOLFoocYuqoEDhy3vXLvtmnQN5yaECv8i3gPiUVkKgKm8YRgl&currency=USD"></script>
</head>

<body>

<header>
  <img src="LogoPictures/Logo copy.png" alt="Shopora Logo" width="150">
  <nav>
     <!-- TOP ROW (links) -->
  <div class="nav-links">
    <a href="index.php">Home</a>
    <a href="#">Products</a>
    <a href="#">Cart</a>
    <a href="adding_items.php">Seller (Adding items)</a>
    <a href="edit_items.php">Edit Items</a>
    <!-- <a href="login.php">Login</a>
    <a href="register.php">Register</a> -->

      <!-- If already log in Login and Register Disapear, else if not log in, Login and register shows! -->
      <?php if (isset($_SESSION['user_name'])): ?>
      <div class="profile">
        <div class="profile-circle"><?= htmlspecialchars(strtoupper($_SESSION['user_name'][0])) ?></div>
        <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
        <form method="post" action="logout.php" style="display:inline;">
          <button type="submit">Logout</button>
        </form>
      </div>
      <?php else: ?>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
      <?php endif; ?>

  </div>

  <!-- BOTTOM ROW (search only) -->
  <div class="nav-search">
    <form method="get" action="index.php">
      <input type="text" name="q" placeholder="Search items..." value="<?= htmlspecialchars($q) ?>">
      <button type="submit">Search</button>
      <?php if ($q !== ""): ?>
        <a class="clear-btn" href="index.php">Clear</a>
      <?php endif; ?>
    </form>
  </div>


    <?php if (isset($_SESSION['user_name'])): ?>
      <div class="profile">
        <div class="profile-circle">
          <?= htmlspecialchars(strtoupper($_SESSION['user_name'][0])) ?>
        </div>
        <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
        <form method="post" action="logout.php" style="display:inline;">
          <button type="submit">Logout</button>
        </form>
      </div>
    <?php else: ?>
      <!-- <a href="login.php">Login</a>
      <a href="register.php">Register</a> -->
    <?php endif; ?>
  </nav>
</header>

<h1>Welcome to Shopora</h1>

<div class="products-container">

<?php
if ($result && $result->num_rows > 0):

    while ($row = $result->fetch_assoc()):
        $image = !empty($row['image']) ? $row['image'] : 'default.png';
        $productId = $row['id'];
        $price = $row['price'];
        $name = htmlspecialchars($row['name']);
        $desc = htmlspecialchars($row['description']);
        $qty = $row['quantity'];
?>

    <div class="product big-product">
        <img src="uploads/<?= $image ?>" alt="<?= $name ?>">
        <h2><?= $name ?></h2>
        <p><?= $desc ?></p>
        <p><strong>Price:</strong> $<?= $price ?></p>

        <?php if ($qty > 0): ?>
            <p><strong>Quantity:</strong> <?= $qty ?></p>
            <div id="paypal-button-container-<?= $productId ?>"></div>
        <?php else: ?>
            <p style="color:red;"><strong>Out of Stock</strong></p>
        <?php endif; ?>
    </div>

    <?php if ($qty > 0): ?>
    <script>
    paypal.Buttons({
        createOrder: function(data, actions) {
            return actions.order.create({
                purchase_units: [{
                    amount: { value: '<?= $price ?>' }
                }]
            });
        },
        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                alert('Transaction completed by ' + details.payer.name.given_name);

                fetch('payment_success.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(details)
                });
            });
        }
    }).render('#paypal-button-container-<?= $productId ?>');
    </script>
    <?php endif; ?>

<?php
    endwhile;

else:
    echo "<p>No items found.</p>";
endif;
?>

</div>

<footer class="site-footer">
  <div class="footer-container">
    <address>
      Warehouse Location: <br>
      
    </address>

    <p>&copy; <?= date('Y') ?> <strong>Shopora</strong>. All rights reserved.</p>

    <nav class="footer-links">
      <a href="about.html">About Us</a>
      <a href="contact.html">Contact</a>
      <a href="terms.html">Terms</a>
      <a href="privacy.html">Privacy</a>
    </nav>
  </div>
</footer>

</body>
</html>