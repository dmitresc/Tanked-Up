<?php
session_start();

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

// Define fish categories
$categories = ['Freshwater', 'Saltwater', 'Brackish', 'Specials', 'Invertebrates'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop | Tanked Up</title>
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
            margin-top: 40px; /* Reduced margin-top to raise the container */
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
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
        .category-container {
            text-align: center;
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
        .category-item {
            display: inline-block;
            margin: 10px;
            text-align: center;
        }
		.category-image {
			width: 250px;
			height: 250px;
			border-radius: 10px;
			box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
			transition: transform 0.3s ease;
		}
        .category-image:hover {
            transform: scale(1.05);
        }
        .category-text {
            margin-top: 5px;
            font-size: 1.2em;
            color: #004d40;
        }
        .category-item a {
            text-decoration: none;
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
                <li class="nav-item active">
                    <a class="nav-link active" href="user_shop.php">Shop</a>
                </li>
                <li class="nav-item">
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
            <h1>Categories</h1>
        </div>
        <div class="category-container">
            <?php foreach ($categories as $category) : ?>
                <div class="category-item">
                    <a href="category.php?type=<?php echo urlencode($category); ?>">
                        <img src="<?php echo strtolower($category); ?>.jpg" alt="<?php echo htmlspecialchars($category); ?>" class="category-image">
                        <div class="category-text"><?php echo htmlspecialchars($category); ?></div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>