<?php
require '../db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT cart.id AS cart_id, cart.quantity, items.name, items.price, items.image
    FROM cart
    JOIN items ON cart.item_id = items.id
    WHERE cart.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Cart</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>

<h1>My Cart</h1>

<a href="../index.php">Continue Shopping</a>

<?php if ($result->num_rows > 0): ?>

    <?php $total = 0; ?>

    <?php while ($row = $result->fetch_assoc()): ?>
        <?php
            $subtotal = $row['price'] * $row['quantity'];
            $total += $subtotal;
            $image = !empty($row['image']) ? $row['image'] : 'default.png';
        ?>

        <div style="border:1px solid #ccc; padding:15px; margin:15px;">
            <img src="../uploads/<?= htmlspecialchars($image) ?>" width="120">

            <h2><?= htmlspecialchars($row['name']) ?></h2>

            <p>Price: $<?= number_format($row['price'], 2) ?></p>
            <p>
                <a href="../update_cart.php?cart_id=<?= $row['cart_id'] ?>&action=minus">➖</a>

                <?= $row['quantity'] ?>

                <a href="../update_cart.php?cart_id=<?= $row['cart_id'] ?>&action=plus">➕</a>
            </p>
            <a href="../remove_from_cart.php?cart_id=<?= $row['cart_id'] ?>">Remove</a>
            <p>Subtotal: $<?= number_format($subtotal, 2) ?></p>
        </div>

    <?php endwhile; ?>

    <h2>Total: $<?= number_format($total, 2) ?></h2>

    <a href="../checkout.php">Checkout</a>

<?php else: ?>

    <p>Your cart is empty.</p>

<?php endif; ?>

</body>
</html>