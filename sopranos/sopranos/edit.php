<?php
// Create a connection to the database
$conn = mysqli_connect("localhost", "root", "", "sopranos");

// Check if the connection was made successfully
if (mysqli_connect_errno()) {
    die("Connection failed: " . mysqli_connect_error());
}
$order_id = $_GET['id'];
//repare the SQL query
$query = "SELECT 
    `order`.order_id AS order_id,
    `order`.order_customer_name AS order_name,
    `order`.order_postal_code AS order_postalcode,
    `order`.order_house_num AS order_housenum,
    `order`.order_house_num_add AS order_add,
    `order`.order_date AS order_date,
    `order`.order_complete AS order_completed,
    GROUP_CONCAT(pizza.pizza_name) AS items_ordered,
    GROUP_CONCAT(size.size_name) as pizza_sizes
FROM 
    `order`
JOIN 
    order_item ON `order`.order_id = order_item.order_id
JOIN 
    menu ON order_item.menu_id = menu.menu_id
JOIN
    pizza ON menu.pizza_id = pizza.pizza_id
JOIN
    size ON menu.size_id = size.size_id
WHERE
    `order`.order_id = ?";

// Prepare the statement
$stmt = $conn->prepare($query);

// Bind the parameter
$stmt->bind_param('i', $order_id);

// Execute the query
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Fetch the data
while ($row = $result->fetch_assoc()) {
    // Process the data
    $order_id = $row['order_id'];
    $order_name = $row['order_name'];
    $order_postalcode = $row['order_postalcode'];
    $order_housenum = $row['order_housenum'];
    $order_add = $row['order_add'];
    $order_date = $row['order_date'];
    $order_completed = $row['order_completed'];
    $items_ordered = $row['items_ordered'];
    $pizza_sizes = $row['pizza_sizes'];
}

// Close the statement
$stmt->close();

if (isset($_POST['submit'])){
    
    // Prepare the UPDATE statements
    $queryOrder = "UPDATE `order`
              SET order_customer_name = ?, 
                  order_postal_code = ?, 
                  order_house_num = ?, 
                  order_house_num_add = ?, 
                  order_date = ?, 
                  order_complete = ?
              WHERE order_id = ?";
    
    $queryPizza = "UPDATE pizza
              SET pizza_name = ?
              WHERE pizza_id = ?";
    
    $querySize = "UPDATE size
              SET size_name = ?
              WHERE size_id = ?";

    $conn->begin_transaction();
    
    try {
        // Update order
        $stmtOrder = $conn->prepare($queryOrder);
        $stmtOrder->bind_param("ssisssi", 
            $_POST['order_customer_name'], 
            $_POST['order_postalcode'], 
            $_POST['order_housenum'], 
            $_POST['order_add'], 
            $_POST['order_date'], 
            $_POST['order_completed'], 
            $_POST['order_id']);
        $stmtOrder->execute();
        $stmtOrder->close();
    
        // Update pizzas
        $pizzaData = explode(",", $_POST['items_ordered']);
        foreach ($pizzaData as $pizzaId => $pizzaName) {
            $stmtPizza = $conn->prepare($queryPizza);
            $stmtPizza->bind_param("si", $pizzaName, $pizzaId);
            $stmtPizza->execute();
            $stmtPizza->close();
        }
    
        // Update sizes
        $sizeData = explode(",", $_POST['pizza_sizes']);
        foreach ($sizeData as $sizeId => $sizeName) {
            $stmtSize = $conn->prepare($querySize);
            $stmtSize->bind_param("si", $sizeName, $sizeId);
            $stmtSize->execute();
            $stmtSize->close();
        }
    
        // Commit transaction
        $conn->commit();
    
        echo "Order and related information updated successfully";
        header("Location: dashboard.php");
    }  catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo "Error updating order and related information: " . $e->getMessage();
    }
      
}

// Close the connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order</title>
</head>
<body>

<h2>Edit Order</h2>

<form action="" method="post">
    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
    
    <label for="order_name">Customer Name:</label>
    <input type="text" id="order_name" name="order_customer_name" value="<?php echo $order_name; ?>" required><br>

    <label for="order_postalcode">Postal Code:</label>
    <input type="text" id="order_postalcode" name="order_postalcode" value="<?php echo $order_postalcode; ?>" required><br>

    <label for="order_housenum">House Number:</label>
    <input type="text" id="order_housenum" name="order_housenum" value="<?php echo $order_housenum; ?>" required><br>

    <label for="order_add">Additional House Info:</label>
    <input type="text" id="order_add" name="order_add" value="<?php echo $order_add; ?>"><br>

    <label for="order_date">Order Date:</label>
    <input type="text" id="order_date" name="order_date" value="<?php echo $order_date; ?>" readonly><br>

    <label for="order_completed">Order Completed:</label>
    <input type="text" id="order_completed" name="order_completed" value="<?php echo $order_completed; ?>" required><br>

    <label for="items_ordered">Items Ordered:</label>
    <textarea id="items_ordered" name="items_ordered" rows="4" required><?php echo $items_ordered; ?></textarea><br>

    <label for="pizza_sizes">Pizza Sizes:</label>
    <textarea id="pizza_sizes" name="pizza_sizes" rows="4" required><?php echo $pizza_sizes; ?></textarea><br>

    <input type="submit" name="submit" value="Update Order">
</form>

</body>
</html>
