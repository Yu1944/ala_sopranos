<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION["cart"])) $_SESSION["cart"] = array();

/**
 * Add an item to this session's cart by menu item ID
 */
function add_to_cart(int $menu_id) {
  array_push($_SESSION["cart"], $menu_id);
}

/**
 * Remove an item from this session's cart by index
 */
function remove_from_cart(int $key) {
  array_splice($_SESSION["cart"], $key, 1);
}

/**
 * Fetches all items in this session's cart
 */
function get_cart_items(): array {
  // If there's nothing in the cart, return immidiately
  if (count($_SESSION["cart"]) === 0) return array();

  // Create a connection to the database
  $conn = mysqli_connect("localhost", "root", "", "sopranos");

  // Check if the connection was made successfully
  if (mysqli_connect_errno()) {
    return false;
  }

  // Define a select statement
  $dummy_sql = array();
  foreach ($_SESSION["cart"] as $id) {
    array_push($dummy_sql, "SELECT $id AS `id`");
  }
  $dummy_sql = implode(" UNION ALL ", $dummy_sql);

  $query = "SELECT
    `menu`.`menu_id`,
    `menu`.`pizza_id`,
    `menu`.`size_id`,
    `menu`.`price`,
    `pizza`.`pizza_name`,
    `size`.`size_name`
  FROM ($dummy_sql) `_dummy`
    JOIN `menu` ON `menu`.`menu_id` = `_dummy`.`id`
    LEFT JOIN `pizza` ON `menu`.`pizza_id` = `pizza`.`pizza_id`
    LEFT JOIN `size` ON `menu`.`size_id` = `size`.`size_id`";

  // Execute the statement
  $result = mysqli_query($conn, $query);

  // Get data into an array
  $ret = array();
  if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
      array_push($ret, $row);
    }
  }

  return $ret;
}

/**
 * Calculates the price of all items in this session's cart
 */
function calculate_total_price() {
  // If there's nothing in the cart, return immidiately
  if (count($_SESSION["cart"]) === 0) return 0;

  // Get all items in the cart, and sort by price
  $products = get_cart_items($_SESSION["cart"]);
  $prices = array_column($products, "price");
  sort($prices);

  // Calculate combined price, discounting all items after the first by 50%
  $total = $prices[0];
  foreach ($prices as $price) {
    $total += $price;
  }
  $total /= 2;

  return $total;
}
