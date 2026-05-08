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
    header("Location: Orders.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Order not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? 'NEW';
    $carrier = trim($_POST['carrier'] ?? '');
    $tracking = trim($_POST['tracking'] ?? '');

    /*
      Logic:
      - If tracking is pasted AND shipped_at is empty, set shipped_at = NOW()
      - If tracking already existed, keep original shipped_at
      - If tracking is empty, do not set shipped_at
    */
    $update = $pdo->prepare("
        UPDATE orders
        SET
            status = ?,
            tracking_carrier = ?,
            tracking_number = ?,
            shipped_at =
                CASE
                    WHEN ? <> '' AND shipped_at IS NULL THEN NOW()
                    ELSE shipped_at
                END
        WHERE id = ?
    ");

    $update->execute([
        $status,
        $carrier,
        $tracking,
        $tracking,
        $id
    ]);

    header("Location: order_details.php?id=" . $id);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Order Details</title>
</head>
<body>

<a href="Orders.php">Back to Orders</a>

<h2>Order Details</h2>

<p><b>Order ID:</b> #<?= (int)$order['id'] ?></p>
<p><b>Order #:</b> <?= htmlspecialchars($order['order_number']) ?></p>
<p><b>Buyer:</b> <?= htmlspecialchars($order['buyer_name']) ?></p>
<p><b>Email:</b> <?= htmlspecialchars($order['buyer_email']) ?></p>
<p><b>Total:</b> $<?= number_format((float)$order['total'], 2) ?></p>
<p><b>Current Status:</b> <?= htmlspecialchars($order['status']) ?></p>

<p>
    <b>Order Date:</b>
    <?= date("M d, Y g:i A", strtotime($order['created_at'])) ?>
</p>

<p>
    <b>Shipped Date:</b>
    <?php
    if (!empty($order['shipped_at'])) {
        echo date("M d, Y g:i A", strtotime($order['shipped_at']));
    } else {
        echo "Not shipped yet";
    }
    ?>
</p>

<p>
    <b>Tracking:</b>
    <?php
    if (!empty($order['tracking_number'])) {
        echo htmlspecialchars($order['tracking_carrier'] ?? '') . " ";
        echo htmlspecialchars($order['tracking_number']);
    } else {
        echo "No tracking added yet";
    }
    ?>
</p>

<hr>

<h3>Add / Update Tracking</h3>

<form method="post">
    <label>Status:</label><br>
    <select name="status">
        <option value="NEW" <?= $order['status'] === 'NEW' ? 'selected' : '' ?>>NEW</option>
        <option value="PAID" <?= $order['status'] === 'PAID' ? 'selected' : '' ?>>PAID</option>
        <option value="SHIPPED" <?= $order['status'] === 'SHIPPED' ? 'selected' : '' ?>>SHIPPED</option>
        <option value="CANCELLED" <?= $order['status'] === 'CANCELLED' ? 'selected' : '' ?>>CANCELLED</option>
    </select>

    <br><br>

    <label>Carrier:</label><br>
    <input type="text" name="carrier" placeholder="FedEx / UPS / USPS"
           value="<?= htmlspecialchars($order['tracking_carrier'] ?? '') ?>">

    <br><br>

    <label>Tracking Number:</label><br>
    <input type="text" name="tracking" placeholder="Enter tracking number"
           value="<?= htmlspecialchars($order['tracking_number'] ?? '') ?>">

    <br><br>

    <button type="submit">Save Tracking</button>
</form>

</body>
</html>