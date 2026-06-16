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

// Check if the product ID is passed in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = $_GET['id'];

    // Fetch existing product details
	$stmt = $conn->prepare("SELECT fish_name, fish_price, fish_quantity, fish_type, fish_origin, fish_size, tank_size, fish_temperament, ph_range, temp_range, fish_description, fish_image FROM inventory WHERE id = ?");
	$stmt->bind_param("i", $product_id);
	$stmt->execute();
	$stmt->bind_result($fish_name, $fish_price, $fish_quantity, $fish_type, $fish_origin, $fish_size, $tank_size, $fish_temperament, $ph_range, $temp_range, $fish_description, $fish_image);
	$stmt->fetch();
	$stmt->close();

    // Split the fish_size into min and max
    list($size_min, $size_max) = explode("-", $fish_size);
} else {
    $_SESSION['error_message'] = "Invalid product ID.";
    header("Location: admin_inventory.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fish_name = $_POST['name'];
    $fish_price = $_POST['price'];
    $fish_quantity = $_POST['quantity'];
    $fish_type = $_POST['type'];
    $fish_origin = $_POST['origin'];
    $size_min = $_POST['size_min'];
    $size_max = $_POST['size_max'];
    $tank_size = $_POST['tank_size'];
    $fish_temperament = $_POST['temperament'];

    // Get separate pH and temperature ranges
    $ph_range = $_POST['ph_range'];
    $temp_range = $_POST['temp_range'];

    $fish_description = $_POST['description'];
    $fish_image = $_FILES['image']['name'];
    $target = "images/" . basename($fish_image);

    // Check if a new image is uploaded
    if (!empty($fish_image)) {
        // Validate image file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['image']['type'];

        if (!in_array($file_type, $allowed_types)) {
            $_SESSION['error_message'] = "Invalid file type. Only JPG, PNG, GIF, and WEBP files are allowed.";
            header("Location: edit_product.php?id=" . $product_id);
            exit();
        }
    }

    // Format fish size as a range (e.g., "2-12 inches")
    $fish_size = $size_min . "-" . $size_max;

    // If a new image is uploaded, update the product with the new image
    if (!empty($fish_image)) {
        $sql = "UPDATE inventory SET fish_name = ?, fish_price = ?, fish_quantity = ?, fish_type = ?, fish_origin = ?, fish_size = ?, tank_size = ?, fish_temperament = ?, ph_range = ?, temp_range = ?, fish_description = ?, fish_image = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdisssssssssi", $fish_name, $fish_price, $fish_quantity, $fish_type, $fish_origin, $fish_size, $tank_size, $fish_temperament, $ph_range, $temp_range, $fish_description, $fish_image, $product_id);
    } else {
        // If no new image is uploaded, update the product without changing the image
        $sql = "UPDATE inventory SET fish_name = ?, fish_price = ?, fish_quantity = ?, fish_type = ?, fish_origin = ?, fish_size = ?, tank_size = ?, fish_temperament = ?, ph_range = ?, temp_range = ?, fish_description = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdisssssssss", $fish_name, $fish_price, $fish_quantity, $fish_type, $fish_origin, $fish_size, $tank_size, $fish_temperament, $ph_range, $temp_range, $fish_description, $product_id);
    }

    if ($stmt->execute()) {
        // Move uploaded image to the target directory if a new image is uploaded
        if (!empty($fish_image) && move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $_SESSION['success_message'] = "Product updated successfully!";
        } else {
            $_SESSION['success_message'] = "Product updated successfully, but failed to upload image.";
        }
    } else {
        $_SESSION['error_message'] = "Error updating product: " . $stmt->error;
    }

    $stmt->close();
    header("Location: admin_inventory.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Fish Product | Tanked Up</title>
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
        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin: 10px 0 5px;
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        input, select {
            padding: 10px;
            width: 100%;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .back-button {
            padding: 10px 20px;
            background: #097969;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        .back-button:hover {
            background-color: #0056b3;
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
		}

		.logout:hover {
			background: #c82333;
		}
    </style>
</head>
<body>

    <div class="container">
        <header>
            <h1>Edit Fish: <?php echo htmlspecialchars($fish_name); ?></h1>
        </header>
		
        <!-- Navigation Buttons -->
        <div class="nav-buttons-container">
            <a href="admin_dashboard.php" class="nav-button">Dashboard</a>
            <a href="admin_inventory.php" class="nav-button">Inventory</a>
            <a href="admin_orders.php" class="nav-button">Orders</a>
            <a href="admin_sales.php" class="nav-button">Sales</a>
            <a href="user_shop.php" class="nav-button">Shop</a>
        </div>

        <!-- Display Success/Error Messages -->
        <?php
        if (isset($_SESSION['success_message'])) {
            echo "<div class='alert alert-success'>" . $_SESSION['success_message'] . "</div>";
            unset($_SESSION['success_message']);
        } elseif (isset($_SESSION['error_message'])) {
            echo "<div class='alert alert-danger'>" . $_SESSION['error_message'] . "</div>";
            unset($_SESSION['error_message']);
        }
        ?>
		
        <!-- Edit User Form -->
        <form action="edit_product.php?id=<?php echo $product_id; ?>" method="post" enctype="multipart/form-data">
            <label for="name">Fish Name:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($fish_name); ?>" required>

            <label for="price">Price (₱):</label>
            <input type="number" name="price" value="<?php echo htmlspecialchars($fish_price); ?>" step="0.01" required>

            <label for="quantity">Quantity:</label>
            <input type="number" name="quantity" value="<?php echo htmlspecialchars($fish_quantity); ?>" required>

            <label for="type">Type:</label>
            <select name="type" required>
                <option value="Freshwater" <?php echo $fish_type == "Freshwater" ? "selected" : ""; ?>>Freshwater</option>
                <option value="Saltwater" <?php echo $fish_type == "Saltwater" ? "selected" : ""; ?>>Saltwater</option>
                <option value="Brackish" <?php echo $fish_type == "Brackish" ? "selected" : ""; ?>>Brackish</option>
                <option value="Specials" <?php echo $fish_type == "Specials" ? "selected" : ""; ?>>Specials</option>
                <option value="Invertebrates" <?php echo $fish_type == "Invertebrates" ? "selected" : ""; ?>>Invertebrates</option>
            </select>

            <label for="origin">Origin:</label>
            <input type="text" name="origin" value="<?php echo htmlspecialchars($fish_origin); ?>" required>

            <label for="size_min">Size (Min in inches):</label>
            <input type="number" name="size_min" value="<?php echo htmlspecialchars($size_min); ?>" required>

            <label for="size_max">Size (Max in inches):</label>
            <input type="number" name="size_max" value="<?php echo htmlspecialchars($size_max); ?>" required>

            <label for="tank_size">Tank Size (in gallons):</label>
            <input type="number" name="tank_size" value="<?php echo htmlspecialchars($tank_size); ?>" required>

            <label for="temperament">Temperament:</label>
            <select name="temperament" required>
                <option value="Peaceful" <?php echo $fish_temperament == "Peaceful" ? "selected" : ""; ?>>Peaceful</option>
                <option value="Semi-Aggressive" <?php echo $fish_temperament == "Semi-Aggressive" ? "selected" : ""; ?>>Semi-Aggressive</option>
                <option value="Aggressive" <?php echo $fish_temperament == "Aggressive" ? "selected" : ""; ?>>Aggressive</option>
                <option value="Highly-Aggressive" <?php echo $fish_temperament == "Highly-Aggressive" ? "selected" : ""; ?>>Highly-Aggressive</option>
            </select>

			<label for="ph_range">pH Range:</label>
			<input type="text" name="ph_range" value="<?php echo htmlspecialchars($ph_range); ?>" required>

			<label for="temp_range">Temperature Range (°C):</label>
			<input type="text" name="temp_range" value="<?php echo htmlspecialchars($temp_range); ?>" required>

            <label for="description">Description:</label>
            <textarea name="description" rows="4" required><?php echo htmlspecialchars($fish_description); ?></textarea>

            <label for="image">Image:</label>
            <input type="file" name="image" accept="image/*">

            <button type="submit">Update Fish</button>
        </form>

        <a href="logout.php" class="logout" onclick="return confirm('Are you sure you want to log out?')">Logout</a>
    </div>
</body>
</html>