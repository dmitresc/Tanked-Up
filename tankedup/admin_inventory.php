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

// Pagination
$limit = 7;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$searchTerm = "%" . $search . "%";

// Get total fish count securely
$totalFishStmt = $conn->prepare("SELECT COUNT(*) AS count FROM inventory WHERE fish_name LIKE ?");
$totalFishStmt->bind_param("s", $searchTerm);
$totalFishStmt->execute();
$totalFishResult = $totalFishStmt->get_result();
$totalFish = $totalFishResult->fetch_assoc()['count'];
$totalFishStmt->close();

// Fetch fish with search and pagination using prepared statements
$sql = "SELECT id, fish_name, fish_quantity, fish_price FROM inventory WHERE fish_name LIKE ? LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $searchTerm, $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();

// Calculate total pages
$totalPages = ceil($totalFish / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory | Tanked Up</title>
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
        .add-product {
            display: inline-block;
            padding: 10px 20px;
            background: #28a745;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .add-product:hover {
            background: #218838;
        }
        .inventory-list {
            margin-top: 20px;
        }
        .inventory-list table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .inventory-list table, .inventory-list th, .inventory-list td {
            border: 1px solid #ddd;
        }
        .inventory-list th {
            background-color: #e9ecef;
            color: #333;
        }
        .inventory-list th, .inventory-list td {
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
            <h1>Inventory</h1>
        </header>

        <div class="nav-buttons-container">
            <a href="admin_dashboard.php" class="nav-button">Dashboard</a>
            <a href="admin_inventory.php" class="nav-button">Inventory</a>
            <a href="admin_orders.php" class="nav-button">Orders</a>
            <a href="admin_sales.php" class="nav-button">Sales</a>
            <a href="user_shop.php" class="nav-button">Shop</a>
        </div>

        <a href="add_product.php" class="add-product">Add New Product</a>

        <div class="inventory-list">
            <table>
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
					<?php
                    if ($result->num_rows > 0) {
                        while ($fish = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>" . htmlspecialchars($fish['id']) . "</td>
                                    <td>" . htmlspecialchars($fish['fish_name']) . "</td>
                                    <td>$" . number_format($fish['fish_price'], 2) . "</td>
                                    <td>" . $fish['fish_quantity'] . "</td>
                                    <td class='actions'>
                                        <a href='edit_product.php?id=" . $fish['id'] . "'>Edit</a> |
                                        <a href='delete_product.php?id=" . $fish['id'] . "' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                                    </td>
                                </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No fish found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="search-pagination-container">
            <div class="search-container">
                <form method="GET" action="admin_inventory.php">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search fish by name" />
                    <button type="submit">Search</button>
                </form>
            </div>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                    <a href="admin_inventory.php?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>"
                        class="<?php echo ($i === $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
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