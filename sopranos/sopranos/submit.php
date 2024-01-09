<?php
session_start();
require_once('orders.php');
$error = false;
$error_message = "";
if (
  $_SERVER["REQUEST_METHOD"] === "POST" &&
  $_POST["action"] === "order" &&
  count($_SESSION["cart"])
) {
  $ret = submit_order();
  if (is_string($ret)) {
    $error = true;
    $error_message = "Het veld " . str_replace("_", "", $ret) . " is fout ingevoerd";
  }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Index</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <?php include "header.html"; ?>
    <div class="splash">
      <a href="order.php">Bestel hier<div class="cart-icon"></div></a>
    </div>
    <?php if ($error): ?>
      <h1>Je bestelling is verzonden!</h1>
    <?php else: ?>
      <?php echo $error_message; ?>
    <?php endif; ?>
    <?php include "footer.html"; ?>
  </body>
</html>
