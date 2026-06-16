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

$customer_id = $_SESSION['user_id'];

// Pagination
$limit = 5; // Adjust as needed
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$searchTerm = "%" . $search . "%";

// Fetch user's orders with total price and search
$sql = "SELECT o.id, o.product_id, o.order_quantity, o.status, o.order_date, p.fish_name, p.fish_price
        FROM orders o
        JOIN inventory p ON o.product_id = p.id
        WHERE o.customer_id = ? AND p.fish_name LIKE ?
        LIMIT ?, ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isii", $customer_id, $searchTerm, $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

// Get total orders for pagination
$countSql = "SELECT COUNT(*) FROM orders o JOIN inventory p ON o.product_id = p.id WHERE o.customer_id = ? AND p.fish_name LIKE ?";
$countStmt = $conn->prepare($countSql);
$countStmt->bind_param("is", $customer_id, $searchTerm);
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalOrders = $countResult->fetch_assoc()['COUNT(*)'];
$totalPages = ceil($totalOrders / $limit);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders | Tanked Up</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
        }
        body {
            min-height: 100vh;
            background-color: #e0f2f7;
            position: relative;
            font-size: 16px;
            padding-bottom: 50px;
        }
        .container {
            margin-top: 40px;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .navbar-text-center {
            text-align: center;
            width: 100%;
        }
        .navbar {
            background: linear-gradient(to bottom, rgba(3, 169, 244, 0.95), rgba(2, 119, 189, 0.95)) !important;
        }
        .navbar-brand img {
            width: 125px;
        }
        .navbar-nav .nav-link, .navbar-text, .page-header h1 {
            color: white !important;
        }
        .navbar-nav .nav-link {
            font-size: 1.2rem !important;
        }
        .page-header {
            text-align: center;
            margin: 30px auto;
            color: white;
            font-size: 2.5em;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
            padding: 15px 30px;
            border: 2px solid #29b6f6;
            border-radius: 20px;
            background-color: #4fc3f7;
            width: fit-content;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .status {
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .pending { background-color: #FFCC00; color: white; }
        .completed { background-color: #4CAF50; color: white; }
        .shipped { background-color: #2196F3; color: white; }
        .cancelled { background-color: #f44336; color: white; }
        .nav-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4fc3f7;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            margin-top: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s ease;
        }
        .nav-button:hover {
            background-color: #29b6f6;
        }
        .cancel-btn {
            background-color: #f44336;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }
        .cancel-btn:hover {
            opacity: 0.8;
        }
        .search-pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .search-container {
            display: flex;
            align-items: center;
        }
        .search-container input {
            padding: 8px;
            margin-right: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
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
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid align-items-center">
            <a class="navbar-brand" href="#">
                <img src="tankedup_smaller.png" alt="Tanked Up Logo">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="user_shop.php">Shop</a>
                </li>
                <li class="nav-item active">
                    <a class="nav-link" href="user_orders.php">Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about_us.php">About Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="fish_tank_calculator.php">Fish Tank Calculator</a>
                </li>
				<li class="nav-item">
                    <a class="nav-link" href="fish_compatibility_chart.php">Fish Compatibility Chart</a>
                </li>
            </ul>
                <span class="navbar-text">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="admin_dashboard.php" class="btn btn-primary btn-sm">Admin Dashboard</a> |
                        <?php endif; ?>
                        Logged in as: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong> |
                        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-success btn-sm">Login</a> |
                        <a href="register.php" class="btn btn-primary btn-sm">Register</a>
                    <?php endif; ?>
                </span>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>Orders</h1>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Order Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
				<?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">No orders found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['id']); ?></td>
                            <td><?= htmlspecialchars($order['fish_name']); ?></td>
                            <td><?= htmlspecialchars($order['order_quantity']); ?></td>
                            <td>$<?= number_format($order['fish_price'] * $order['order_quantity'], 2); ?></td>
                            <td class="status <?= strtolower($order['status']); ?>"><?= htmlspecialchars($order['status']); ?></td>
                            <td><?= htmlspecialchars($order['order_date']); ?></td>
                            <td>
                                <?php if (strtolower($order['status']) === 'pending'): ?>
                                    <button class="cancel-btn" onclick="cancelOrder(<?= $order['id']; ?>)">Cancel</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
		
		<div class="search-pagination-container" style="margin-top: 20px;">
            <div class="search-container">
                <form method="GET" action="user_orders.php">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search orders by product name" />
                    <button type="submit">Search</button>
                </form>
            </div>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                    <a href="user_orders.php?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>"
                       class="<?php echo ($i === $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php } ?>
            </div>
        </div>
		
        <a href="user_shop.php" class="nav-button">Back to Shop</a>
	</div>

    <script>
        function cancelOrder(orderId) {
            if (confirm("Are you sure you want to cancel this order?")) {
                window.location.href = `cancel_order.php?order_id=${orderId}`;
            }
        }
    </script>
</body>
</html>