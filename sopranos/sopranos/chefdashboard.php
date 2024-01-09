<?php
// Create a connection to the database
$conn = mysqli_connect("localhost", "root", "", "sopranos");

// Check if the connection was made successfully
if (mysqli_connect_errno()) {
    die("Connection failed: " . mysqli_connect_error());
}

function getOrders($conn) {
    $query = "SELECT 
    `order`.order_id AS order_id,
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
GROUP BY 
    `order`.order_id asc";
    $result = $conn->query($query);

    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }

    return $orders;
}

$orders = getOrders($conn);

// Display orders in a table on your backoffice page
echo "<table>
    <tr>
        <th>Order ID</th>
        <th>Order date</th>
        <th>Items Ordered</th>
        <th>Items Sizes</th>
        <th>Order completed</th>
        <th>Actions</th>
    </tr>";

    foreach ($orders as $order) {
        if ($order['order_completed'] == 0) {
            echo "<tr>
                <td>{$order['order_id']}</td>
                <td>{$order['order_date']}</td>
                <td>{$order['items_ordered']}</td>
                <td>{$order['pizza_sizes']}</td>
                <td>{$order['order_completed']}</td>
                <td>
                    <a href='edit.php?id={$order['order_id']}'>Edit</a>
                </td>
            </tr>";
        }
    }
    

echo "</table>";
?>