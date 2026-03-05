<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Home - HodedahCo</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>

<header>
    <!-- <h1>HodedahCo</h1>  -->
     <!-- <img src="Logo copy.png" alt="HodedahCo Logo" width="150"> -->
  <nav>
    
    <a href="index.php">Home</a>
    <a href="#">Products</a>
    <a href="">Cart</a>
    
    <?php if (isset($_SESSION['user_name'])): ?>
      <span>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
      <form style="display:inline" method="post" action="logout.php">
        <button type="submit">Logout</button>
      </form>
    <?php else: ?>
      <a href="login.html">Login</a>
      <a href="register.html">Register</a>
    <?php endif; ?>
    
  </nav>
</header>


<h1>Welcome to E-Commerce</h1>



</body>
</html>
