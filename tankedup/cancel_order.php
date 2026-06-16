<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "tankedup";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Ensure order_id is provided
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    header("Location: user_orders.php");
    exit();
}

$order_id = intval($_GET['order_id']);
$customer_id = $_SESSION['user_id'];

// Verify the order belongs to the logged-in user and is still pending
$sql = "SELECT status FROM orders WHERE id = ? AND customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order || strtolower($order['status']) !== 'pending') {
    header("Location: user_orders.php?error=Cannot cancel this order");
    exit();
}

// Update order status to "Cancelled"
$update_sql = "UPDATE orders SET status = 'Cancelled' WHERE id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("i", $order_id);
$update_stmt->execute();

$conn->close();

// Redirect back with a success message
header("Location: user_orders.php?success=Order cancelled successfully");
exit();
?>
