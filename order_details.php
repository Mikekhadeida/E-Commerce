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

$userStmt = $pdo->prepare("
    SELECT name, email, store_credit
    FROM users
    WHERE id = ?
");
$userStmt->execute([$order['user_id']]);
$customer = $userStmt->fetch(PDO::FETCH_ASSOC);

$itemStmt = $pdo->prepare("
    SELECT *
    FROM order_items
    WHERE order_id = ?
");
$itemStmt->execute([$id]);
$items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? 'NEW';
    $carrier = trim($_POST['carrier'] ?? '');
    $tracking = trim($_POST['tracking'] ?? '');

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

    $update->execute([$status, $carrier, $tracking, $tracking, $id]);

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

<div style="display:flex; gap:40px; align-items:flex-start;">

    <!-- LEFT SIDE -->
    <div style="width:45%;">

        <h3>Customer Information</h3>

        <?php if ($customer): ?>
            <p><strong>Name:</strong> <?= htmlspecialchars($customer['name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($customer['email']) ?></p>
            <p><strong>Store Credit:</strong> $<?= number_format($customer['store_credit'], 2) ?></p>
        <?php else: ?>
            <p>No customer account found.</p>
        <?php endif; ?>

        <h3>Order Information</h3>

        <p><b>Order ID:</b> #<?= (int)$order['id'] ?></p>
        <p><b>Order #:</b> <?= htmlspecialchars($order['order_number']) ?></p>
        <p><b>Buyer:</b> <?= htmlspecialchars($order['buyer_name']) ?></p>
        <p><b>Email:</b> <?= htmlspecialchars($order['buyer_email']) ?></p>
        <p><b>Total:</b> $<?= number_format((float)$order['total'], 2) ?></p>
        <p><b>Current Status:</b> <?= htmlspecialchars($order['status']) ?></p>

        <!-- DIscount Display -->
        <!-- <p><b>Original Total:</b> $<?= number_format((float)$order['total'], 2) ?></p>
        <p><b>Store Credit Used:</b> $<?= number_format((float)$order['credit_used'], 2) ?></p>
        <p><b>Customer Paid:</b> $<?= number_format((float)$order['final_total'], 2) ?></p>
        <p><b>Rewards Earned:</b> $<?= number_format((float)$order['credit_earned'], 2) ?></p> -->

        <div style="border:1px solid #ccc;padding:7px;width:310px;background:#f8f8f8;border-radius:10px;">
            <strong>Payment Summary</strong><br><br>

            Original: $<?= number_format($order['total'],2) ?><br>
            Credit Used:
            <span style="color:red;">-$<?= number_format($order['credit_used'],2) ?></span><br>

            Paid: $<?= number_format($order['final_total'],2) ?><br>

            Earned:
            <span style="color:green;">+$<?= number_format($order['credit_earned'],2) ?></span>
        </div>
<!--             -->
        <h3>Items Purchased</h3>

        <?php if (count($items) > 0): ?>
            <?php foreach ($items as $item): ?>
                <div style="
                    border:1px solid #ccc;
                    padding:10px;
                    margin-bottom:10px;
                    width:350px;
                    display:flex;
                    gap:15px;
                    align-items:center;
                    background:#f9f9f9;
                ">
                    <img 
                        src="uploads/<?= htmlspecialchars($item['item_image']) ?>" 
                        width="90"
                        style="object-fit:contain;"
                    >

                    <div>
                        <strong><?= htmlspecialchars($item['item_name']) ?></strong><br>
                        Quantity: <?= (int)$item['quantity'] ?><br>
                        Price: $<?= number_format((float)$item['price'], 2) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No item details saved for this order.</p>
        <?php endif; ?>

    </div>

    <!-- RIGHT SIDE -->
    <div style="width:45%; border-left:1px solid #ccc; padding-left:30px;">

        <h3>Shipping Address</h3>

        <div style="
            border:1px solid #ccc;
            padding:15px;
            width:350px;
            background:#f9f9f9;
        ">
            <?= htmlspecialchars($order['shipping_name'] ?? '') ?><br>
            <?= htmlspecialchars($order['shipping_address1'] ?? '') ?><br>

            <?php if (!empty($order['shipping_address2'])): ?>
                <?= htmlspecialchars($order['shipping_address2']) ?><br>
            <?php endif; ?>

            <?= htmlspecialchars($order['shipping_city'] ?? '') ?>,
            <?= htmlspecialchars($order['shipping_state'] ?? '') ?>
            <?= htmlspecialchars($order['shipping_zip'] ?? '') ?><br>

            <?= htmlspecialchars($order['shipping_country'] ?? '') ?><br><br>

            <strong>Phone:</strong>
            <?= htmlspecialchars($order['buyer_phone'] ?? '') ?>
        </div>

        <h3>Shipping / Tracking</h3>

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

    </div>

</div>

</body>
</html>