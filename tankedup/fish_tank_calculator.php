<?php
session_start();

// Database connection (You might not need this for the calculator itself)
$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "tankedup";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fish Tank Calculator | Tanked Up</title>
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
        /* ... (Rest of the CSS from user_shop.php) ... */

        .input-row {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .input-row label {
            width: 120px;
            text-align: right;
            margin-right: 10px;
            font-weight: bold;
            color: #555;
        }

        .input-row input[type="number"] {
            flex: 0.9; /* 90% width */
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
            margin-right: 5px; /* Add a small margin between input and select */
        }

        .input-row select {
            flex: 0.1; /* 10% width */
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
        }

		button {
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
			display: block;
			margin-left: auto;
			margin-right: auto;
			font-weight: 600; /* Make the button bolder */
		}

		button:hover {
			background-color: #29b6f6;
		}

        .calculator-container #result {
            margin-top: 20px;
            font-weight: bold;
            text-align: center;
            font-size: 1.2em;
            color: #333;
        }

        .calculator-container h2 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.8em;
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
                <li class="nav-item">
                    <a class="nav-link" href="about_us.php">About Us</a>
                </li>
                <li class="nav-item active">
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
            <h1>Fish Tank Calculator</h1>
        </div>

        <div class="input-row">
            <label for="length">Length:</label>
            <input type="number" id="length" name="length" min="1">
            <select id="lengthUnit">
                <option value="mm">mm</option>
                <option value="cm" selected>cm</option>
                <option value="m">m</option>
                <option value="in">in</option>
                <option value="ft">ft</option>
            </select>
        </div>

        <div class="input-row">
            <label for="width">Width:</label>
            <input type="number" id="width" name="width" min="1">
            <select id="widthUnit">
                <option value="mm">mm</option>
                <option value="cm" selected>cm</option>
                <option value="m">m</option><option value="in">in</option>
                <option value="ft">ft</option>
            </select>
        </div>

        <div class="input-row">
            <label for="height">Height:</label>
            <input type="number" id="height" name="height" min="1">
            <select id="heightUnit">
                <option value="mm">mm</option>
                <option value="cm" selected>cm</option>
                <option value="m">m</option>
                <option value="in">in</option>
                <option value="ft">ft</option>
            </select>
        </div>

        <button onclick="calculateVolume()">Calculate</button>

        <div class="input-row">
            <label for="volumeUnit">Volume:</label>
            <select id="volumeUnit">
                <option value="liters" selected>liters (L)</option>
                <option value="gallons-us">gallons (US)</option>
                <option value="gallons-uk">gallons (UK)</option>
            </select>
        </div>

        <p id="result"></p>

    </div>

    <script>
        function calculateVolume() {
            var length = document.getElementById("length").value;
            var width = document.getElementById("width").value;
            var height = document.getElementById("height").value;
            var lengthUnit = document.getElementById("lengthUnit").value;
            var widthUnit = document.getElementById("widthUnit").value;
            var heightUnit = document.getElementById("heightUnit").value;
            var volumeUnit = document.getElementById("volumeUnit").value;

            // Convert all dimensions to centimeters
            length = convertToCentimeters(length, lengthUnit);
            width = convertToCentimeters(width, widthUnit);
            height = convertToCentimeters(height, heightUnit);

            var volumeLiters = (length * width * height) / 1000;

            // Convert to selected volume unit
            var volume = convertVolume(volumeLiters, volumeUnit);

            document.getElementById("result").innerHTML = "Volume: " + volume.toFixed(2) + " " + volumeUnit;
        }

        function convertToCentimeters(value, unit) {
            switch (unit) {
                case "mm": return value / 10;
                case "cm": return value;
                case "m": return value * 100;
                case "in": return value * 2.54;
                case "ft": return value * 30.48;
            }
        }

        function convertVolume(liters, unit) {
            switch (unit) {
                case "liters": return liters;
                case "gallons-us": return liters * 0.264172;
                case "gallons-uk": return liters * 0.219969;
            }
        }
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>