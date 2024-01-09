<?php
/**
 * Fetches all items in the menu
 */
function fetch_menu(): array {
  // Create a connection to the database
  $conn = mysqli_connect("localhost", "root", "", "sopranos");

  // Check if the connection was made successfully
  if (mysqli_connect_errno()) {
    return false;
  }

  // Define a select statement
  $query = "SELECT
    `menu`.`menu_id`,
    `menu`.`pizza_id`,
    `menu`.`size_id`,
    `menu`.`price`,
    `pizza`.`pizza_name`,
    `size`.`size_name`
  FROM `menu`
    LEFT JOIN `pizza` ON `menu`.`pizza_id` = `pizza`.`pizza_id`
    LEFT JOIN `size` ON `menu`.`size_id` = `size`.`size_id`;";

  // Execute the statement
  $result = mysqli_query($conn, $query);

  // Get data into an array
  $ret = array();
  if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
      $pizza_id = $row["pizza_id"];
      if (!isset($ret[$pizza_id])) {
        $ret[$pizza_id] = array();
      }
      array_push($ret[$row["pizza_id"]], $row);
    }
  }

  // Close the connection to the database
  mysqli_close($conn);

  return $ret;
}
