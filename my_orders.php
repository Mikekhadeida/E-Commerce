<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT *
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Orders</title>
</head>
<body>

<h1>My Orders</h1>

<a href="index.php">Home</a> |
<a href="Buyers/cart.php">Cart</a>

<br><br>

<?php while ($order = $result->fetch_assoc()): ?>

    <div style="border:1px solid #ccc; padding:15px; margin-bottom:20px;">

        <h2>Order #<?= htmlspecialchars($order['order_number']) ?></h2>

        <p><strong>Total:</strong> $<?= number_format($order['total'], 2) ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>

        <p>
            <strong>Tracking:</strong>
            <?php if (!empty($order['tracking_number'])): ?>
                <?= htmlspecialchars($order['tracking_carrier']) ?>
                <?= htmlspecialchars($order['tracking_number']) ?>
            <?php else: ?>
                Not shipped yet
            <?php endif; ?>
        </p>

        <p><strong>Date:</strong> <?= date("M d, Y g:i A", strtotime($order['created_at'])) ?></p>

        <h3>Items</h3>

        <?php
        $itemStmt = $conn->prepare("
            SELECT *
            FROM order_items
            WHERE order_id = ?
        ");
        $itemStmt->bind_param("i", $order['id']);
        $itemStmt->execute();
        $items = $itemStmt->get_result();
        ?>

        <?php if ($items->num_rows > 0): ?>
            <?php while ($item = $items->fetch_assoc()): ?>
                <div style="display:flex; gap:15px; align-items:center; margin-bottom:10px;">
                    <img src="uploads/<?= htmlspecialchars($item['item_image']) ?>" width="100">

                    <div>
                        <strong><?= htmlspecialchars($item['item_name']) ?></strong><br>
                        Price: $<?= number_format($item['price'], 2) ?><br>
                        Quantity: <?= htmlspecialchars($item['quantity']) ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No item details saved for this order.</p>
        <?php endif; ?>

    </div>

<?php endwhile; ?>

</body>
</html>