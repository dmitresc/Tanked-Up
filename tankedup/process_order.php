<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "You must be logged in to place an order."]);
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

// Input validation and sanitization
$product_id = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);
$order_quantity = filter_var($_POST['order_quantity'], FILTER_VALIDATE_INT);

if ($product_id === false || $order_quantity === false || $order_quantity <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid product ID or quantity."]);
    exit();
}

$customer_id = $_SESSION['user_id'];

// Start transaction
$conn->begin_transaction();

try {
    // Fetch product details (quantity & price)
    $sql = "SELECT fish_quantity, fish_price, fish_name FROM inventory WHERE id = ? FOR UPDATE";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if (!$product || $product['fish_quantity'] < $order_quantity) {
        throw new Exception("Not enough stock available.");
    }

    // Calculate total cost
    $total_cost = $product['fish_price'] * $order_quantity;

    // Insert new order with quantity and total cost
    $order_sql = "INSERT INTO orders (customer_id, product_id, order_quantity, total_cost, status) VALUES (?, ?, ?, ?, 'Pending')";
    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->bind_param("iiid", $customer_id, $product_id, $order_quantity, $total_cost);

    if (!$order_stmt->execute()) {
        throw new Exception("Failed to place order.");
    }

    // Reduce stock in inventory
    $update_stock_sql = "UPDATE inventory SET fish_quantity = fish_quantity - ? WHERE id = ?";
    $update_stock_stmt = $conn->prepare($update_stock_sql);
    $update_stock_stmt->bind_param("ii", $order_quantity, $product_id);

    if (!$update_stock_stmt->execute()) {
        throw new Exception("Failed to update inventory.");
    }

    // Commit transaction
    $conn->commit();
    echo json_encode([
        "status" => "success", 
        "message" => "Order placed successfully for " . $order_quantity . " " . htmlspecialchars($product['fish_name']) . "(s).", 
        "total_cost" => "₱" . number_format($total_cost, 2)
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => "An error occurred. Please try again later."]);
}

$conn->close();
?>
