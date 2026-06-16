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

// Define fish categories (This might not be needed on this page, but I'm including it for consistency)
$categories = ['Freshwater', 'Saltwater', 'Brackish', 'Specials', 'Invertebrates'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Tanked Up</title>
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
        .about-section {
            margin-top: 20px;
        }
        .about-section h2 {
            color: #004d40;
            margin-bottom: 10px;
        }
        .about-section p {
            text-align: justify;
            line-height: 1.6;
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
                <li class="nav-item">
                    <a class="nav-link" href="user_orders.php">Orders</a>
                </li>
                <li class="nav-item active">
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
        <h1>About Us</h1>
    </div>

    <div class="about-section">
        <h2>Welcome to Tanked Up!</h2>
        <p>
            We are a passionate team of aquarists based in the beautiful city of Davao, Philippines. 
            Our love for aquatic life inspired us to create Tanked Up, your one-stop shop for all your aquarium needs. 
            Whether you're a seasoned aquarist or just starting your underwater journey, we're here to help you create a thriving and vibrant aquatic world.
        </p>
        <br>
        <p>
            At Tanked Up, we offer a wide variety of freshwater, saltwater, and brackish water fish, as well as invertebrates and plants. 
            We carefully source our livestock from reputable breeders and suppliers, ensuring that you receive healthy and high-quality specimens. 
            Our knowledgeable staff is always on hand to provide expert advice and guidance on fish care, tank setup, and aquascaping.
        </p>
        <br>
        <p>
            We believe that responsible fishkeeping starts with education and awareness. 
            That's why we're committed to providing our customers with the resources they need to make informed decisions about their aquatic pets. 
            We offer a variety of educational materials, including fish compatibility charts, tank calculators, and care guides.
        </p>
        <br>
        <p>
            We're more than just a fish store; we're a community of aquarists who share a passion for the underwater world. 
            We invite you to join us on our journey to promote responsible fishkeeping and create beautiful aquatic environments. 
            Visit our store in Davao or browse our online shop to discover the wonders of the aquatic world.
        </p>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>