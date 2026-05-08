<?php
session_start();
if (!isset($_SESSION['user_name'])) {
    header("Location: seller_login.php");
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=my_database", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$filter = $_GET['filter'] ?? 'all';

if ($filter === 'shipped') {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE status = 'SHIPPED' ORDER BY created_at DESC");
    $stmt->execute();
} elseif ($filter === 'unshipped') {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE status != 'SHIPPED' ORDER BY created_at DESC");
    $stmt->execute();
} else {
    $stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
}

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Orders</title>
</head>
<body>

<h2>Orders</h2>

<a href="seller_items.php">My Items</a> |
<a href="Orders.php?filter=all">All Orders</a> |
<a href="Orders.php?filter=unshipped">Unshipped</a> |
<a href="Orders.php?filter=shipped">Shipped</a> |
<a href="logout.php">Logout</a>

<br><br>

<table border="1" cellpadding="10" cellspacing="0">
    <tr>
        <th>Order #</th>
        <th>Buyer</th>
        <th>Email</th>
        <th>Total</th>
        <th>Status</th>
        <th>Tracking</th>
        <th>Date</th>
        <th>Action</th>
    </tr>

    <?php if (count($orders) > 0): ?>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td><?= htmlspecialchars($o['order_number']) ?></td>
                <td><?= htmlspecialchars($o['buyer_name']) ?></td>
                <td><?= htmlspecialchars($o['buyer_email']) ?></td>
                <td>$<?= number_format((float)$o['total'], 2) ?></td>
                <td><?= htmlspecialchars($o['status']) ?></td>
                <td>
                    <?= htmlspecialchars($o['tracking_carrier'] ?? '') ?>
                    <?= htmlspecialchars($o['tracking_number'] ?? '') ?>
                </td>
                <td><?= htmlspecialchars($o['created_at']) ?></td>
                <td>
                    <a href="order_details.php?id=<?= (int)$o['id'] ?>">Open</a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="8">No orders found.</td>
        </tr>
    <?php endif; ?>
</table>

</body>
</html>