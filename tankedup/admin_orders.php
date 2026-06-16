<?php
session_start();

// Ensure user is logged in and has an admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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
    die("Connection failed: " . $conn->connect_error);
}

// Pagination setup
$limit = 7; // Number of orders per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$searchTerm = "%" . $search . "%";

// Fetch total orders (for pagination)
$totalOrdersQuery = "SELECT COUNT(*) AS count FROM orders";
$totalOrdersResult = $conn->query($totalOrdersQuery);
$totalOrders = $totalOrdersResult->fetch_assoc()['count'];
$totalPages = ceil($totalOrders / $limit);

// Fetch orders with pagination and search, including fish price
$sql = "SELECT o.id, o.customer_id, o.product_id, o.order_quantity, o.status, o.order_date, u.username, p.fish_name, p.fish_price
        FROM orders o
        JOIN users u ON o.customer_id = u.id
        JOIN inventory p ON o.product_id = p.id
        WHERE u.username LIKE ? OR p.fish_name LIKE ?
        ORDER BY o.order_date DESC
        LIMIT ?, ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssii", $searchTerm, $searchTerm, $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();
$orders = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders | Tanked Up</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
		* {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
        }
		body {
			font-family: Arial, sans-serif;
			background: #ffffff;
			color: #333;
		}
		.container {
			width: 80%;
			margin: 20px auto;
			padding: 20px;
			background: #f8f9fa;
			border-radius: 10px;
			box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
		}
		header h1 {
			font-size: 32px;
			color: #333;
			text-align: center;
			margin-bottom: 20px;
		}
		.nav-buttons-container {
			display: flex;
			justify-content: space-between;
			margin-bottom: 20px;
		}
		.nav-button {
			padding: 10px 20px;
			background: #6c757d;
			color: white;
			border: none;
			border-radius: 5px;
			cursor: pointer;
			font-size: 16px;
			font-weight: bold;
			text-decoration: none;
			display: flex;
			justify-content: center;
			align-items: center;
			flex: 1;
			margin: 0 10px;
			text-align: center;
		}
		.nav-button:hover {
			background-color: #5a6268;
		}
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .status {
            padding: 5px;
            border-radius: 3px;
        }
        .pending {
            background-color: #FFCC00;
            color: white;
			padding: 5px 10px;
			border: none;
			border-radius: 5px;
			cursor: pointer;
        }
        .completed {
            background-color: #4CAF50;
            color: white;
			padding: 5px 10px;
			border: none;
			border-radius: 5px;
			cursor: pointer;
        }
        .shipped {
            background-color: #2196F3;
            color: white;
			padding: 5px 10px;
			border: none;
			border-radius: 5px;
			cursor: pointer;
        }
        .cancelled {
            background-color: #f44336;
            color: white;
			padding: 5px 10px;
			border: none;
			border-radius: 5px;
			cursor: pointer;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
        .actions button {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }
        .actions button.pending {
            background-color: #FFCC00;
        }
        .actions button.shipped {
            background-color: #2196F3;
        }
        .actions button.completed {
            background-color: #4CAF50;
        }
        .actions button.cancelled {
            background-color: #f44336;
        }
        .actions button:hover {
            opacity: 0.8;
        }
		.search-container {
            display: flex;
            align-items: center;
            width: 70%;
        }
        .search-container input {
            padding: 8px;
            margin-right: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            width: auto;
            min-width: 200px;
        }
        .search-container button {
            padding: 8px 16px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        .search-container button:hover {
            background: #218838;
        }
        .pagination {
            display: flex;
            justify-content: flex-end;
            flex-wrap: wrap;
        }
        .pagination a {
            margin: 0 5px;
            padding: 5px 10px;
            background: #007bff;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }
        .pagination a:hover {
            background: #0056b3;
        }
        .pagination .active {
            background: #28a745;
            font-weight: bold;
        }
        .search-pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
			padding-top: 20px;
        }
		.logout {
			padding: 10px 20px;
			background: #dc3545;
			color: white;
			border: none;
			border-radius: 5px;
			cursor: pointer;
			font-size: 16px;
			display: inline-block;
			font-weight: bold;
			margin-top: 20px;
		}
		.logout:hover {
			background: #c82333;
		}
    </style>
</head>
<body>

    <div class="container">
        <header>
            <h1>Orders</h1>
        </header>
        
        <div class="nav-buttons-container">
            <a href="admin_dashboard.php" class="nav-button">Dashboard</a>
            <a href="admin_inventory.php" class="nav-button">Inventory</a>
            <a href="admin_orders.php" class="nav-button">Orders</a>
            <a href="admin_sales.php" class="nav-button">Sales</a>
            <a href="user_shop.php" class="nav-button">Shop</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Total Cost</th>
                    <th>Status</th>
                    <th>Order Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= $order['id']; ?></td>
                        <td><?= htmlspecialchars($order['username']); ?></td>
                        <td><?= htmlspecialchars($order['fish_name']); ?></td>
                        <td><?= htmlspecialchars($order['order_quantity']); ?></td>
                        <td>₱<?= number_format($order['fish_price'] * $order['order_quantity'], 2); ?></td>
                        <td class="status <?= strtolower($order['status']); ?>"><?= htmlspecialchars($order['status']); ?></td>
                        <td><?= htmlspecialchars($order['order_date']); ?></td>
                        <td class="actions">
                            <button class="pending" onclick="updateOrderStatus(<?= $order['id']; ?>, 'Pending')">Pending</button>
                            <button class="shipped" onclick="updateOrderStatus(<?= $order['id']; ?>, 'Shipped')">Shipped</button>
                            <button class="completed" onclick="updateOrderStatus(<?= $order['id']; ?>, 'Completed')">Completed</button>
                            <button class="cancelled" onclick="updateOrderStatus(<?= $order['id']; ?>, 'Cancelled')">Cancelled</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="search-pagination-container">
            <div class="search-container">
                <form method="GET" action="admin_orders.php">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search orders by customer or product" />
                    <button type="submit">Search</button>
                </form>
            </div>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                    <a href="admin_orders.php?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>"
                       class="<?php echo ($i === $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php } ?>
            </div>
        </div>

        <a href="logout.php" class="logout" onclick="return confirm('Are you sure you want to log out?')">Logout</a>

        <script>
            function updateOrderStatus(orderId, status) {
                const confirmUpdate = confirm(`Are you sure you want to update this order to ${status}?`);
                if (confirmUpdate) {
                    const xhr = new XMLHttpRequest();
                    xhr.open("POST", "update_order_status.php", true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    xhr.onreadystatechange = function () {
                        if (xhr.readyState == 4 && xhr.status == 200) {
                            const response = JSON.parse(xhr.responseText);
                            alert(response.message);
                            if (response.status === "success") {
                                location.reload();
                            }
                        }
                    };
                    xhr.send(`order_id=${orderId}&status=${status}`);
                }
            }
        </script>
    </div>

</body>
</html>

<?php
$conn->close();
?>