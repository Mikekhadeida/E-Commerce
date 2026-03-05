<?php
$data = json_decode(file_get_contents('php://input'), true);
if ($data && isset($data['id'])) {
    $transaction_id = $data['id'];
    $payer_name = $data['payer']['name']['given_name'];
    $payer_email = $data['payer']['email_address'];
    $amount = $data['purchase_units'][0]['amount']['value'];

    $conn = new mysqli("localhost", "root", "", "my_database");
    $stmt = $conn->prepare("INSERT INTO payments (transaction_id, payer_name, payer_email, amount) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssd", $transaction_id, $payer_name, $payer_email, $amount);
    $stmt->execute();

    mail("mike.khadeida@gmail.com", "New PayPal Payment", "New payment from $payer_name of $$amount.");
    $conn->close();
}
?>

<!-- PayPal provides a sandbox environment specifically for testing payments without touching real money. -->
 <!-- 1️⃣ Create Sandbox Accounts
Go to: PayPal Developer Dashboard
Log in with your real PayPal account.
Navigate to Sandbox → Accounts.
Create two accounts:
Buyer: A fake customer account to simulate purchases.
Seller: Your business account (if not already created).
These accounts act like real PayPal accounts but use sandbox money. -->

<!-- 1️⃣ Create Sandbox Accounts
Go to: PayPal Developer Dashboard
Log in with your real PayPal account.
Navigate to Sandbox → Accounts.
Create two accounts:
Buyer: A fake customer account to simulate purchases.
Seller: Your business account (if not already created).
These accounts act like real PayPal accounts but use sandbox money. -->


<!--Make sure your payments table exists in my_database with these columns: and add notes-->

<!-- 
CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  transaction_id VARCHAR(255),
  payer_name VARCHAR(255),
  payer_email VARCHAR(255),
  amount DECIMAL(10,2),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
 -->
