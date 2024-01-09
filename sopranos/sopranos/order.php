<?php
require "menu.php";
require "orders.php";
require "cart.php";

$menu = fetch_menu();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  switch ($_POST["action"]) {
    case "add":
      add_to_cart((int) $_POST["item"]);
      break;
    case "remove":
      remove_from_cart((int) $_POST["item"]);
      break;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Sopranos Pizza &middot; Bestel</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <?php include "header.html"; ?>
    <div class="splash">
      <a href="index.php">Home<div class="cart-icon"></div></a>
    </div>
    <div class="order">
      <div class="menu-container">
        <div class="menu">
          <?php foreach ($menu as $item): ?>
            <form class="menu-item" method="POST">
              <input type="hidden" name="action" value="add">
              <img src="assets/pizza-<?php echo $item[0]["pizza_id"] ?>.png" alt>
              <span class="menu-item-name">
                <strong><?php echo $item[0]["pizza_name"]; ?></strong>
              </span>
              <select name="item" onchange="this.form.submit();">
                <option disabled selected>Kies een optie</option>
                <?php foreach ($item as $size): ?>
                  <option value="<?php echo $size["menu_id"]; ?>">
                    <?php echo htmlspecialchars($size["size_name"]); ?>
                    &middot;
                    &euro;<?php echo number_format($size["price"] / 100, 2); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </form>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="cart-container">
        <div class="cart">
          <div class="cart-title">
            <strong>Winkelwagen</strong>
          </div>
          <?php foreach (get_cart_items($_SESSION["cart"]) as $key => $item): ?>
            <form class="cart-item" method="POST">
              <div class="cart-item-info">
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="item" value="<?php echo $key; ?>">
                <div class="cart-item-name">
                  <strong><?php echo htmlspecialchars($item["pizza_name"]); ?></strong>
                </div>
                <div class="cart-item-size">
                  <?php echo htmlspecialchars($item["size_name"]); ?>
                </div>
                <div class="cart-item-price">
                  &euro;<?php echo number_format($item["price"] / 100, 2); ?>
                </div>
              </div>
              <div class="cart-item-remove">
                <a onclick="this.parentElement.parentElement.submit();">&times;</a>
              </div>
            </form>
          <?php endforeach; ?>
          <div class="cart-total">
            Totaal: &euro;<?php echo number_format(calculate_total_price($_SESSION["cart"]) / 100, 2) ?>
          </div>
        </div>
        <form class="order-form" method="post" action="submit.php">
          <input type="hidden" name="action" value="order">
          <div class="input-row">
            <label for="customer_name">Naam</label>
            <input type="text" name="customer_name" id="customer_name">
          </div>
          <div class="input-row">
            <label for="postal_code">Postcode</label>
            <input type="text" name="postal_code" id="postal_code" placeholder="1234AB">
          </div>
          <div class="input-row">
            <label for="house_num">Huisnummer</label>
            <input type="text" name="house_num" id="house_num">
          </div>
          <div class="input-row">
            <label for="house_num_add">Toevoeging</label>
            <input type="text" name="house_num_add" id="house_num_add">
          </div>
          <div class="input-row">
            <label for="time">Gewenste tijd</label>
            <input type="time" name="time" id="time" min="17:00" max="22:00" step="600">
          </div>
          <a onclick="this.parentElement.submit();">Bestel<div class="cart-icon"></div></a>
        </form>
      </div>
    </div>
    <?php include "footer.html"; ?>
    <script src="order.js"></script>
  </body>
</html>
