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

// Pagination (Users)
$limit = 4;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search functionality (Users)
$search = isset($_GET['search']) ? $_GET['search'] : '';
$searchTerm = "%" . $search . "%";

// Fetch stats
$userCount = $conn->query("SELECT COUNT(*) AS count FROM users")->fetch_assoc()['count'];
$totalSales = $conn->query("SELECT SUM(total_sale_value) AS total_sale_value FROM sales")->fetch_assoc()['total_sale_value'];
$totalProducts = $conn->query("SELECT COUNT(*) AS count FROM inventory")->fetch_assoc()['count'];

// Fetch users based on search and pagination
$sql = "SELECT id, username, role FROM users WHERE username LIKE ? LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $searchTerm, $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();

// Get total pages (Users)
$totalPages = ceil($userCount / $limit);

// Low Stock Pagination
$lowStockLimit = 4;
$lowStockPage = isset($_GET['low_stock_page']) ? (int)$_GET['low_stock_page'] : 1;
$lowStockOffset = ($lowStockPage - 1) * $lowStockLimit;

// Low Stock Search
$lowStockSearch = isset($_GET['low_stock_search']) ? $_GET['low_stock_search'] : '';
$lowStockSearchTerm = "%" . $lowStockSearch . "%";

// Fetch low stock items based on search and pagination
$lowStockQuery = "SELECT id, fish_name, fish_quantity FROM inventory WHERE fish_quantity < 11 AND fish_name LIKE ? LIMIT ?, ?";
$lowStockStmt = $conn->prepare($lowStockQuery);
$lowStockStmt->bind_param("sii", $lowStockSearchTerm, $lowStockOffset, $lowStockLimit);
$lowStockStmt->execute();
$lowStockResult = $lowStockStmt->get_result();

// Get total pages (Low Stock)
$lowStockCountQuery = "SELECT COUNT(*) FROM inventory WHERE fish_quantity < 11 AND fish_name LIKE ?";
$lowStockCountStmt = $conn->prepare($lowStockCountQuery);
$lowStockCountStmt->bind_param("s", $lowStockSearchTerm);
$lowStockCountStmt->execute();
$lowStockCount = $lowStockCountStmt->get_result()->fetch_assoc()['COUNT(*)'];
$lowStockTotalPages = ceil($lowStockCount / $lowStockLimit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Tanked Up</title>
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
		.dashboard-stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
        }
        .stat {
            background: #f0f0f0;
			color: #333;
            padding: 5px;
            border-radius: 5px;
            text-align: center;
        }
		h3 {
			color: #333;
		}
		.user-list {
            margin-top: 20px;
        }
		.user-list table {
			width: 100%;
			border-collapse: collapse;
			margin-top: 10px;
			margin-bottom: 20px;
		}
		.user-list table, .user-list th, .user-list td {
			border: 1px solid #ddd;
		}
		.user-list th {
			background-color: #e9ecef;
			color: #333;
		}
        .user-list th, .user-list td {
            padding: 10px;
            text-align: left;
        }
		.actions a {
			color: #6c757d;
			text-decoration: none;
		}
		.actions a:hover {
			text-decoration: underline;
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
            <h1>Dashboard</h1>
        </header>

        <!-- Navigation Buttons -->
        <div class="nav-buttons-container">
            <a href="admin_dashboard.php" class="nav-button">Dashboard</a>
			<a href="admin_inventory.php" class="nav-button">Inventory</a>
            <a href="admin_orders.php" class="nav-button">Orders</a>
            <a href="admin_sales.php" class="nav-button">Sales</a>
            <a href="user_shop.php" class="nav-button">Shop</a>
        </div>

        <!-- Dashboard Stats -->
        <div class="dashboard-stats">
            <div class="stat">
                <h4>Total Users</h4>
                <p><?php echo $userCount; ?></p>
            </div>
            <div class="stat">
                <h4>Total Sales</h4>
                <p>₱<?php echo number_format($totalSales, 2); ?></p>
            </div>
            <div class="stat">
                <h4>Total Products</h4>
                <p><?php echo $totalProducts; ?></p>
            </div>
        </div>

        <!-- Manage Users Section -->
        <div class="user-list">
            <h3>Manage Users</h3>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Display all users (including admins)
                    if ($result->num_rows > 0) {
                        while ($user = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>" . htmlspecialchars($user['username']) . "</td>
                                    <td>" . htmlspecialchars($user['role']) . "</td>
                                    <td class='actions'>
                                        <a href='edit_user.php?id=" . $user['id'] . "'>Edit</a> |
                                        <a href='delete_user.php?id=" . $user['id'] . "' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>No users found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
				<div class="search-pagination-container">
					<div class="search-container">
						<form method="GET" action="admin_dashboard.php">
							<input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search users by username" />
							<button type="submit">Search</button>
						</form>
					</div>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                        <a href="admin_dashboard.php?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>"
                            class="<?php echo ($i === $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php } ?>
                </div>
            </div>
        </div>
		
        <div class="user-list">
            <h3>Low Stock Alerts</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($lowStockResult->num_rows > 0) {
                        while ($item = $lowStockResult->fetch_assoc()) {
                            echo "<tr>
                                    <td>" . htmlspecialchars($item['id']) . "</td>
                                    <td>" . htmlspecialchars($item['fish_name']) . "</td>
                                    <td>" . htmlspecialchars($item['fish_quantity']) . "</td>
                                </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>No low stock items</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
		
        <!-- Search and Pagination Section -->
        <div class="search-pagination-container">
            <div class="search-container">
				<form method="GET" action="admin_dashboard.php">
                    <input type="text" name="low_stock_search" value="<?php echo htmlspecialchars($lowStockSearch); ?>" placeholder="Search low stock items" />
                    <button type="submit">Search</button>
                </form>
            </div>
            <div class="pagination">
                <?php for ($i = 1; $i <= $lowStockTotalPages; $i++) { ?>
                    <a href="admin_dashboard.php?low_stock_page=<?php echo $i; ?>&low_stock_search=<?php echo htmlspecialchars($lowStockSearch); ?>"
                        class="<?php echo ($i === $lowStockPage) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php } ?>
                </div>
            </div>

        <a href="logout.php" class="logout" onclick="return confirm('Are you sure you want to log out?')">Logout</a>
    </div>

</body>
</html>

<?php
$conn->close();
?>