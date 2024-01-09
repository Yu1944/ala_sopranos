<?php
/**
 * Places an order
 */
function submit_order() {

  // Filter all form input to prevent request forgery in unexpected ways
  $filtered_form_data = array(
    "customer_name" =>
      filter_input(INPUT_POST, "customer_name"),
    "postal_code" =>
      filter_input(INPUT_POST, "postal_code", FILTER_VALIDATE_REGEXP, array(
        "options" => array("regexp" => "/[0-9]{4}[A-Za-z]{2}/")
      )),
    "house_num" =>
      filter_input(INPUT_POST, "house_num", FILTER_VALIDATE_INT, array(
        "options" => array("min_range" => 0)
      )),
    "house_num_add" =>
      filter_input(INPUT_POST, "house_num_add", FILTER_DEFAULT, array(
        "options" => array("default" => "")
      )),
    "time" =>
      filter_input(INPUT_POST, "time", FILTER_VALIDATE_REGEXP, array(
        "options" => array("regexp" => "/(?:(?:(?:1[7-9])|(?:2[0-1])):[0-5]0)|(?:22:00)/")
      ))
  );

/*
  // If any of the input filters returned false, the data was invalid.
  // Abort submitting the order, and return the wrong value.
  $required = array("customer_name", "postal_code", "house_num", "time");
  foreach ($filtered_form_data as $key => $value) {
    if (in_array($key, $required) && !$value) return $key;
  }
  */

  $filtered_form_data["date"] = date("Y-m-d ") . $filtered_form_data["time"];

  // Insert the order
  $success = insert_order($filtered_form_data, $_SESSION["cart"]);

  // If the order was successful, destroy this session
  if ($success) session_destroy();

  return $success;
}

/**
 * Inserts an order into the database
 */
function insert_order(array $order, array $items): bool {

  // Create a connection to the database
  $conn = mysqli_connect("localhost", "root", "", "sopranos");

  // Check if the connection was made successfully
  if (mysqli_connect_errno()) {
    return false;
  }

  // Define and prepare an insert statement
  $query = "INSERT INTO `sopranos`.`order` (
    `order_customer_name`,
    `order_postal_code`,
    `order_house_num`,
    `order_house_num_add`,
    `order_date`
  ) VALUES (?, ?, ?, ?, ?)";
  $stmt = mysqli_prepare($conn, $query);

  // Bind prepared statement parameters
  mysqli_stmt_bind_param(
    $stmt, // Prepared SQL statement
    "ssiss", // Data types of bound parameters (s = string, i = int)
    $order["customer_name"],
    $order["postal_code"],
    $order["house_num"],
    $order["house_num_add"],
    $order["date"]
  );

  // Execute and close the statement
  $success = mysqli_stmt_execute($stmt);
  $order_id = mysqli_insert_id($conn);
  mysqli_stmt_close($stmt);

  if (!$success) {
    // Close the database connection
    mysqli_close($conn);
    return false;
  };

  // Insert order items
  $success = insert_order_items($conn, $order_id, $items);

  // Close the database connection
  mysqli_close($conn);

  return $success;
}

/**
 * Inserts an item of an order
 */
function insert_order_items(mysqli $conn, int $order, array $items): bool {
  // Define and prepare an insert statement
  $query = "INSERT INTO `sopranos`.`order_item` (
    `order_id`,
    `menu_id`
  ) VALUES (?, ?)";
  $stmt = mysqli_prepare($conn, $query);

  // Bind prepared statement parameters
  $item = null;
  mysqli_stmt_bind_param(
    $stmt, // Prepared SQL statement
    "ii", // Data types of bound parameters (i = int)
    $order,
    $item
  );

  $success = true;

  // Execute statement on each item.
  // The parameter is already bound to the pointer, no need to prepare a
  // statement for each item in the cart.
  foreach ($items as $item) {
    $success = $success && mysqli_stmt_execute($stmt);
  }

  // Close the statement
  mysqli_stmt_close($stmt);

  return $success;
}
