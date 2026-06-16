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

// Check if fish ID is set
if (!isset($_GET['id'])) {
    header("Location: user_shop.php");
    exit();
}

$product_id = $_GET['id'];

// Fetch fish details
$sql = "SELECT * FROM inventory WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$fish = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$fish) {
    die("Fish not found.");
}

// CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Quantity check
$quantity = $fish['fish_quantity'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($fish['fish_name']); ?> | Tanked Up</title>
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
        .details {
            display: flex;
            gap: 20px;
        }
        .details img {
            width: 300px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .info {
            flex: 1;
        }
        .info p {
            margin: 10px 0;
        }
        .order-form {
            margin-top: 20px;
            text-align: left;
        }
        .order-button {
            padding: 15px 30px;
            font-size: 18px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .order-button:hover {
            background: #218838;
        }
        .fish-detail {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .bold-text {
            font-weight: bold;
        }
        .regular-text {
            font-weight: normal;
        }
        .total-cost {
            margin-top: 10px;
            margin-bottom: 20px;
            font-weight: normal;
            text-align: left;
        }
        .total-cost span {
            font-weight: bold;
        }
        .fish-detail input {
            width: 50%;
            padding: 5px;
        }
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            max-width: 500px;
            width: 100%;
        }
        .modal-header {
            font-size: 20px;
            font-weight: bold;
        }
        .modal-body {
            margin: 10px 0;
        }
        .modal-footer {
            text-align: right;
        }
        .close-modal {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }
        /* NO STOCK banner styles */
        .image-container {
            position: relative;
            display: inline-block;
        }
        .no-stock-banner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(255, 0, 0, 0.8);
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            z-index: 10;
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
                    <a class="nav-link" href="user_shop.php">Shop</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="user_orders.php">Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about_us.php">About Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="fish_compatibility_chart.php">Fish Compatibility Chart</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="fish_tank_calculator.php">Fish Tank Calculator</a>
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
        <h1><?php echo htmlspecialchars($fish['fish_name']); ?></h1>
    </div>

    <div class="details">
        <div class="image-container">
            <img src="images/<?php echo htmlspecialchars($fish['fish_image']); ?>" alt="<?php echo htmlspecialchars($fish['fish_name']); ?>">
            <?php if ($quantity == 0): ?>
                <div class="no-stock-banner">NO STOCK</div>
            <?php endif; ?>
        </div>
        <div class="info">
            <div class="fish-detail">
                <span class="bold-text">Price:</span>
                <span class="regular-text">₱<?php echo number_format($fish['fish_price'], 2); ?></span>
            </div>

            <div class="fish-detail">
                <span class="bold-text">Quantity Available:</span>
                <span class="regular-text"><?php echo $fish['fish_quantity']; ?></span>
            </div>

            <div class="fish-detail">
                <span class="bold-text">Type:</span>
                <span class="regular-text"><?php echo htmlspecialchars($fish['fish_type']); ?></span>
            </div>

            <div class="fish-detail">
                <span class="bold-text">Origin:</span>
                <span class="regular-text"><?php echo htmlspecialchars($fish['fish_origin']); ?></span>
            </div>

            <div class="fish-detail">
                <span class="bold-text">Size:</span>
                <span class="regular-text"><?php echo $fish['fish_size']; ?> inches</span>
            </div>

            <div class="fish-detail">
                <span class="bold-text">Tank Size:</span>
                <span class="regular-text"><?php echo $fish['tank_size']; ?> gallons</span>
            </div>

            <div class="fish-detail">
                <span class="bold-text">Temperament:</span>
                <span class="regular-text"><?php echo htmlspecialchars($fish['fish_temperament']); ?></span>
            </div>

            <div class="fish-detail">
                <span class="bold-text">pH Range:</span>
                <span class="regular-text"><?php echo htmlspecialchars($fish['ph_range']); ?> pH</span>
            </div>

            <div class="fish-detail">
                <span class="bold-text">Temperature Range:</span>
                <span class="regular-text"><?php echo htmlspecialchars($fish['temp_range']); ?> °C</span>
            </div>

            <div class="fish-detail">
                <span class="bold-text">Description:</span>
                <span class="regular-text"><?php echo nl2br(htmlspecialchars($fish['fish_description'])); ?></span>
            </div>

            <form method="POST" id="orderForm" class="order-form">
                <input type="hidden" name="product_id" value="<?php echo $fish['id']; ?>">

                <div class="fish-detail">
                    <label for="order_quantity"><span class="bold-text">Quantity:</span></label>
                    <input type="number" id="order_quantity" name="order_quantity" min="1" max="<?php echo $fish['fish_quantity']; ?>" required>
                </div>

                <div class="total-cost" id="total_cost"><span>Total Cost:</span> ₱0.00</div>

                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <button type="submit" class="order-button">Order</button>
            </form>
        </div>
    </div>
</div>

<div id="orderModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">Order Status</div>
        <div class="modal-body" id="modalMessage"></div>
        <div class="modal-footer">
            <button class="close-modal" id="closeModal">Close</button>
        </div>
    </div>
</div>

<script>
    const pricePerFish = <?php echo $fish['fish_price']; ?>; // Get price from PHP
    const quantityInput = document.getElementById('order_quantity');
    const totalCostDisplay = document.getElementById('total_cost');
    const orderForm = document.getElementById('orderForm');
    const orderModal = document.getElementById('orderModal');
    const modalMessage = document.getElementById('modalMessage');
    const closeModalButton = document.getElementById('closeModal');

    // Function to update the total cost
    function updateTotalCost() {
        const quantity = parseInt(quantityInput.value) || 0; // Get the quantity, default to 0
        const totalCost = pricePerFish * quantity;
        totalCostDisplay.innerHTML = `<span>Total Cost:</span> ₱${totalCost.toFixed(2)}`; // Update total cost
    }

    // Update total cost on input change
    quantityInput.addEventListener('input', updateTotalCost);

    // Function to handle form submission via AJAX
    orderForm.addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent default form submission

        const formData = new FormData(orderForm); // Create FormData object

        fetch('process_order.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Show the modal with the result message
            modalMessage.innerHTML = data.message;
            orderModal.style.display = 'flex'; // Show the modal
        })
        .catch(error => {
            modalMessage.innerHTML = "An error occurred. Please try again later.";
            orderModal.style.display = 'flex'; // Show the modal
        });
    });

    // Close the modal when the button is clicked
    closeModalButton.addEventListener('click', function() {
        orderModal.style.display = 'none'; // Hide the modal
    });

    // Initialize the total cost display
    updateTotalCost();
</script>
</body>
</html>