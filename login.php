<?php
session_start();

// Database connection details
require 'db.php';

// Connect to DB
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = ""; // store error messages only

// Process login only on POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $password_input = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $user_name, $hashed_password);
        $stmt->fetch();

        if (password_verify($password_input, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $user_name;

            // ✅ Redirect to home/dashboard
            header("Location: index.php");
            exit();
        } else {
            $message = "❌ Incorrect password.";
        }
    } else {
        $message = "⚠️ No user found with that email.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
      <!-- Favicon MUST be inside head -->
      <link rel="icon" type="LogoPictures/png" href="Logo.png"> <!-- browser tab logo (favicon) -->

      <meta charset="UTF-8">
      <title>Login - HodedahCo</title>
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
      <h2 class="form-title">Login</h2>
      <?php if (!empty($message)) echo "<p>$message</p>"; ?>

      <form method="post" action="">
        <div class="input-group">
          <input type="email" name="email" placeholder=" " required>
          <label>Email</label>
        </div>

        <div class="input-group">
          <input type="password" name="password" placeholder=" " required>
          <label>Password</label>
        </div>

        <button type="submit" class="btn">Login</button>
      </form>

      <div class="links">
        <p>Don't have an account? create a new account</p>
        <a href="register.php"><button type="button">Register</button></a>
      </div>

    </div>
  </body>
</html>
