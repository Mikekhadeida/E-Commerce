<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
    <script src="https://www.paypal.com/sdk/js?client-id=YOUR_CLIENT_ID"></script>
</head>
<body>
    <h2>Pay with PayPal</h2>
    <div id="paypal-button-container"></div>

    <script>
        paypal.Buttons({
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: '49.99' // Replace with dynamic total
                        }
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    // Send order info to server to save
                    fetch('save_order.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            name: details.payer.name.given_name,
                            email: details.payer.email_address,
                            address: details.purchase_units[0].shipping.address,
                            orderID: data.orderID
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
