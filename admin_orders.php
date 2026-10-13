<?php
session_start();
include 'config.php';

/* Admin login check */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Access Denied. Admin login required.");
}

/* Update order status */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $order_id = (int) $_POST['order_id'];
    $status = $_POST['status'];

    $sql = "UPDATE orders SET status = ? WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();

    header("Location: admin_orders.php");
    exit();
}


/* Get all orders */
$sql = "SELECT orders.id,
               orders.user_id,
               orders.total_amount,
               orders.status,
               orders.created_at,
               users.email
        FROM orders
        JOIN users ON orders.user_id = users.id
        ORDER BY orders.id DESC";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>NetMart - Admin Orders</title>

<style>

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f8;
}

.header {
    background: #222;
    color: white;
    padding: 20px 40px;
}

.header h1 {
    margin: 0;
}

.container {
    max-width: 1100px;
    margin: 40px auto;
    padding: 20px;
}

.box {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 3px 10px #ccc;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #333;
    color: white;
    padding: 14px;
}

td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #ddd;
}

select {
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

button {
    padding: 8px 14px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

button:hover {
    background: #0056b3;
}

</style>

</head>

<body>


<div class="header">

    <h1>📚 NetMart</h1>

    <p>Admin Order Management</p>

</div>


<div class="container">

<div class="box">

<h2>📦 All Orders</h2>


<?php if ($result && $result->num_rows > 0) { ?>

<table>

<tr>

    <th>Order ID</th>

    <th>Customer</th>

    <th>Total</th>

    <th>Status</th>

    <th>Date</th>

    <th>Update</th>

</tr>


<?php while ($row = $result->fetch_assoc()) { ?>

<tr>

    <td>
        #<?php echo $row['id']; ?>
    </td>

    <td>
        <?php echo htmlspecialchars($row['email']); ?>
    </td>

    <td>
        ₹<?php echo number_format($row['total_amount'], 2); ?>
    </td>

    <td>

        <form method="POST">

            <input
                type="hidden"
                name="order_id"
                value="<?php echo $row['id']; ?>"
            >

            <select name="status">

                <option value="Pending"
                    <?php if ($row['status'] == 'Pending') echo 'selected'; ?>>
                    Pending
                </option>

                <option value="Confirmed"
                    <?php if ($row['status'] == 'Confirmed') echo 'selected'; ?>>
                    Confirmed
                </option>

                <option value="Shipped"
                    <?php if ($row['status'] == 'Shipped') echo 'selected'; ?>>
                    Shipped
                </option>

                <option value="Delivered"
                    <?php if ($row['status'] == 'Delivered') echo 'selected'; ?>>
                    Delivered
                </option>

                <option value="Cancelled"
                    <?php if ($row['status'] == 'Cancelled') echo 'selected'; ?>>
                    Cancelled
                </option>

            </select>

    </td>

    <td>
        <?php echo htmlspecialchars($row['created_at']); ?>
    </td>

    <td>

            <button type="submit">
                Update
            </button>

        </form>

    </td>

</tr>

<?php } ?>

</table>

<?php } else { ?>

<p>No orders found.</p>

<?php } ?>


</div>

</div>

</body>

</html>