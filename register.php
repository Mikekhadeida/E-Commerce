<?php
// Database credentials
require 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        // Connect to DB
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Get user input
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $rawPassword = $_POST['password'] ?? '';

        // Validate
        if (empty($name) || empty($email) || empty($rawPassword)) {
            $message = "⚠️ Please fill in all fields.";
        } else {
            // Check if email already exists
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->execute([$email]);
            if ($checkStmt->rowCount() > 0) {
                $message = "❌ Email already registered. Try logging in.";
            } else {
                // Hash password
                $hashedPassword = password_hash($rawPassword, PASSWORD_DEFAULT);

                // Insert user
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$name, $email, $hashedPassword]);

                $message = "✅ Registration successful! <a href='login.php'>Log in here</a>.";
            }
        }
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="icon" type="LogoPictures/png" href="Logo.png"> <!-- browser tab logo (favicon) -->

  <meta charset="UTF-8">
  <title>Register</title>
  <link rel="stylesheet" href="styles.css">
</head>

<header>
    <!-- <h1>HodedahCo</h1>  -->
     <img src="LogoPictures/Logo copy.png" alt="HodedahCo Logo" width="150">
  <nav>
    <a href="index.php">Home</a>
  </nav>
</header>

<body>
  <div class="container">
    <h2 class="form-title">Register</h2>

    <?php if (!empty($message)): ?>
      <p><?= $message ?></p>
    <?php endif; ?>

    <form method="post" action="">
      <div class="input-group">
        <input type="text" name="name" placeholder=" " required>
        <label>Name</label>
      </div>

      <div class="input-group">
        <input type="email" name="email" placeholder=" " required>
        <label>Email</label>
      </div>

      <div class="input-group">
        <input type="password" name="password" placeholder=" " required>
        <label>Password</label>
      </div>

      <button type="submit" class="btn">Register</button>
    </form>

    <div class="links">
      <p>Already have an account?</p>
      <a href="login.php"><button type="button">Login</button></a>
    </div>
    
  </div>
</body>
</html>
