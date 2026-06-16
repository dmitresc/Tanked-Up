<?php
session_start();

// Ensure the user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "You must be an admin to update orders."]);
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
    echo json_encode(["status" => "error", "message" => "Database connection failed."]);
    exit();
}

// Check if order_id and status are set
if (!isset($_POST['order_id']) || !isset($_POST['status'])) {
    echo json_encode(["status" => "error", "message" => "Invalid request. Order ID or status missing."]);
    exit();
}

$order_id = intval($_POST['order_id']);
$status = $_POST['status'];

// Validate the status
$valid_statuses = ['Pending', 'Completed', 'Shipped', 'Cancelled'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(["status" => "error", "message" => "Invalid status."]);
    exit();
}

// Fetch the current status before updating
$old_status_query = $conn->prepare("SELECT status FROM orders WHERE id = ?");
$old_status_query->bind_param("i", $order_id);
$old_status_query->execute();
$old_status_query->bind_result($old_status);
$old_status_query->fetch();
$old_status_query->close();

// Update the order status
$sql = "UPDATE orders SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $status, $order_id);

if ($stmt->execute()) {
    // Log the status change in the order_history table
    $log_sql = "INSERT INTO order_history (order_id, old_status, new_status, changed_by) VALUES (?, ?, ?, ?)";
    $log_stmt = $conn->prepare($log_sql);
    $log_stmt->bind_param("issi", $order_id, $old_status, $status, $_SESSION['user_id']);
    $log_stmt->execute();
    $log_stmt->close();

    echo json_encode(["status" => "success", "message" => "Order status updated successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update order status."]);
}

$conn->close();
?>
