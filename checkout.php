<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmtCredit = $conn->prepare("SELECT store_credit FROM users WHERE id = ?");
$stmtCredit->bind_param("i", $user_id);
$stmtCredit->execute();
$creditResult = $stmtCredit->get_result();
$creditRow = $creditResult->fetch_assoc();

$store_credit = $creditRow['store_credit'] ?? 0;

$stmt = $conn->prepare("
    SELECT cart.quantity, items.price
    FROM cart
    JOIN items ON cart.item_id = items.id
    WHERE cart.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;

while ($row = $result->fetch_assoc()) {
    $total += $row['price'] * $row['quantity'];
}
$credit_used_preview = min($store_credit, $total);
$pay_now_preview = $total - $credit_used_preview;
$rewards_earned_preview = $pay_now_preview * 0.10;

if ($total <= 0) {
    die("Your cart is empty. <a href='index.php'>Go shopping</a>");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
    <script src="https://www.paypal.com/sdk/js?client-id=sb&currency=USD"></script>
</head>
<body>

<h2>Checkout</h2>

<h3>Total: $<?= number_format($total, 2) ?></h3>

<h3>Store Credit</h3>

<p>Available Credit: $<?= number_format($store_credit, 2) ?></p>

<label>
    <input type="checkbox" name="use_store_credit">
    Use my store credit
</label>

<p>If used, you pay: $<?= number_format($pay_now_preview, 2) ?></p>

<p>Rewards earned after order: $<?= number_format($rewards_earned_preview, 2) ?></p>

<h3>Shipping Information</h3>

<input type="text" id="shipping_name" placeholder="Full Name" required><br><br>
<input type="text" id="shipping_address1" placeholder="Address Line 1" required><br><br>
<input type="text" id="shipping_address2" placeholder="Apartment / Unit"><br><br>
<input type="text" id="shipping_city" placeholder="City" required><br><br>
<input type="text" id="shipping_state" placeholder="State" required><br><br>
<input type="text" id="shipping_zip" placeholder="ZIP Code" required><br><br>
<input type="text" id="shipping_country" placeholder="Country" required><br><br>
<input type="text" id="buyer_phone" placeholder="Phone Number"><br><br>

<form method="post" action="place_test_order.php">
    <input type="hidden" name="use_store_credit" id="test_use_store_credit">

    <input type="hidden" name="shipping_name" id="test_shipping_name">
    <input type="hidden" name="shipping_address1" id="test_shipping_address1">
    <input type="hidden" name="shipping_address2" id="test_shipping_address2">
    <input type="hidden" name="shipping_city" id="test_shipping_city">
    <input type="hidden" name="shipping_state" id="test_shipping_state">
    <input type="hidden" name="shipping_zip" id="test_shipping_zip">
    <input type="hidden" name="shipping_country" id="test_shipping_country">
    <input type="hidden" name="buyer_phone" id="test_buyer_phone">

    <button type="submit" onclick="copyShipping()">Place Test Order</button>
</form>

<hr>

<h2>Pay with PayPal</h2>
<div id="paypal-button-container"></div>

<script>
function copyShipping() {
document.getElementById('test_use_store_credit').value =
    document.querySelector('input[name="use_store_credit"]').checked ? "1" : "";

    document.getElementById('test_shipping_name').value = document.getElementById('shipping_name').value;
    document.getElementById('test_shipping_address1').value = document.getElementById('shipping_address1').value;
    document.getElementById('test_shipping_address2').value = document.getElementById('shipping_address2').value;
    document.getElementById('test_shipping_city').value = document.getElementById('shipping_city').value;
    document.getElementById('test_shipping_state').value = document.getElementById('shipping_state').value;
    document.getElementById('test_shipping_zip').value = document.getElementById('shipping_zip').value;
    document.getElementById('test_shipping_country').value = document.getElementById('shipping_country').value;
    document.getElementById('test_buyer_phone').value = document.getElementById('buyer_phone').value;
}

paypal.Buttons({
    createOrder: function(data, actions) {
        return actions.order.create({
            purchase_units: [{
                amount: {
                    value: '<?= number_format($total, 2, '.', '') ?>'
                }
            }]
        });
    },

    onApprove: function(data, actions) {
        return actions.order.capture().then(function(details) {

            fetch('save_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    name: details.payer.name.given_name,
                    email: details.payer.email_address,
                    orderID: data.orderID,
                    total: '<?= number_format($total, 2, '.', '') ?>',

                    shipping_name: document.getElementById('shipping_name').value,
                    shipping_address1: document.getElementById('shipping_address1').value,
                    shipping_address2: document.getElementById('shipping_address2').value,
                    shipping_city: document.getElementById('shipping_city').value,
                    shipping_state: document.getElementById('shipping_state').value,
                    shipping_zip: document.getElementById('shipping_zip').value,
                    shipping_country: document.getElementById('shipping_country').value,
                    buyer_phone: document.getElementById('buyer_phone').value
                })
            }).then(() => {
                alert('Transaction completed!');
                window.location.href = 'thank_you.php';
            });
        });
    }
}).render('#paypal-button-container');
</script>

</body>
</html>