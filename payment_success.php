<?php

$data = json_decode(file_get_contents('php://input'), true);

if ($data && isset($data['id'])) {

    $transaction_id = $data['id'];
    $payer_name = $data['payer']['name']['given_name'];
    $payer_email = $data['payer']['email_address'];
    $amount = $data['purchase_units'][0]['amount']['value'];

    // Database connection
    $conn = new mysqli("localhost", "root", "", "my_database");

    // -----------------------------
    // SAVE PAYMENT
    // -----------------------------
    $stmt = $conn->prepare("
        INSERT INTO payments
        (transaction_id, payer_name, payer_email, amount)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sssd",
        $transaction_id,
        $payer_name,
        $payer_email,
        $amount
    );

    $stmt->execute();

    // -----------------------------
    // CREATE ORDER
    // -----------------------------

    $order_number = "ORD-" . time();

    $status = "NEW";

    $order_stmt = $conn->prepare("
        INSERT INTO orders
        (order_number, buyer_name, buyer_email, total, status)
        VALUES (?, ?, ?, ?, ?)
    ");

    $order_stmt->bind_param(
        "sssds",
        $order_number,
        $payer_name,
        $payer_email,
        $amount,
        $status
    );

    $order_stmt->execute();

    $order_id = $con->insert_id;

    // -----------------------------
    // EMAIL NOTIFICATION
    // -----------------------------

    $to = "mike.khadeida@gmail.com";

    $subject = "New Order Received - " . $order_number;

    $message =
        "New order received!\n\n" .
        "Order Number: " . $order_number . "\n" .
        "Customer: " . $payer_name . "\n" .
        "Email: " . $payer_email . "\n" .
        "Amount: $" . $amount . "\n" .
        "Transaction ID: " . $transaction_id;

    $headers = "From: no-reply@hodedahco.com";

    mail($to, $subject, $message, $headers);

    $conn->close();

    echo json_encode([
        "success" => true,
        "message" => "Payment and order saved"
    ]);
}
?>