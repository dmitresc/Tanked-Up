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

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    // Prepare SQL query to fetch the user
    $stmt = $conn->prepare(
        "SELECT id, username, password, role FROM users WHERE username = ?"
    );
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if username exists
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user["password"])) {
            // Create session variables
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["role"] = $user["role"];

            // Redirect based on role
            if ($user["role"] === "admin") {
                header("Location: admin_dashboard.php");
                exit();
            } else {
                header("Location: user_shop.php");
                exit();
            }
        } else {
            $_SESSION["error"] = "Invalid password!";
        }
    } else {
        $_SESSION["error"] = "Username does not exist!";
    }
    $stmt->close();
    header("Location: login.php", true, 303);
    exit();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form | Tanked Up</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/jquery.ripples@0.6.3/dist/jquery.ripples.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif; }
        body { display: flex; flex-direction: column; justify-content: flex-start; align-items: center; min-height: 100vh; background: url(bg1.jpg) no-repeat; background-size: cover; background-position: center; position: relative; }
        #ripple { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: url('bg1.jpg') no-repeat; background-size: cover; background-position: center; }
        .wrapper { width: 420px; background: transparent; border: 2px solid rgba(255, 255, 255, .2); backdrop-filter: blur(9px); color: #fff; border-radius: 12px; padding: 30px 40px; position: relative; z-index: 1; margin-top: 50px; }
        .wrapper h1 { font-size: 36px; text-align: center; }
        .input-box { position: relative; width: 100%; height: 50px; margin: 30px 0; }
        .input-box input { width: 100%; height: 100%; background: transparent; border: none; outline: none; border: 2px solid rgba(255, 255, 255, .2); border-radius: 40px; font-size: 16px; color: #fff; padding: 20px 45px 20px 20px; }
        .input-box input::placeholder { color: #fff; }
        .input-box i { position: absolute; right: 20px; top: 30%; transform: translate(-50%); font-size: 20px; }
        .remember-me { display: flex; justify-content: space-between; font-size: 14.5px; margin: -15px 0 15px; }
        .remember-me label input { accent-color: #fff; margin-right: 3px; }
        .remember-me a { color: #fff; text-decoration: none; }
        .remember-me a:hover { text-decoration: underline; }
        .btn { width: 100%; height: 45px; background: #fff; border: none; outline: none; border-radius: 40px; box-shadow: 0 0 10px rgba(0, 0, 0, .1); cursor: pointer; font-size: 16px; color: #333; font-weight: 600; }
        .register-link { font-size: 14.5px; text-align: center; margin: 20px 0 15px; }
        .register-link p a { color: #fff; text-decoration: none; font-weight: 600; }
        .register-link p a:hover { text-decoration: underline; }
        .error-message { color: red; text-align: center; font-size: 14px; margin-bottom: 10px; }
    </style>
</head>
<body>
	<div id="ripple"></div>
	<nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(to bottom, rgba(3, 169, 244, 0.95), rgba(2, 119, 189, 0.95)); width: 100%;">
		<div class="container-fluid">
			<a class="navbar-brand" href="user_shop.php">
				<img src="tankedup_smaller.png" alt="Tanked Up Logo" style="width: 125px;">
			</a>
			<div class="navbar-collapse" id="navbarNav">
				<span class="navbar-text" style="color: white; font-size: 1.2em;">
					Stay calm and get tanked up!
				</span>
			</div>
		</div>
	</nav>

    <div class="wrapper">
        <form action="login.php" method="POST">
            <h1>Login</h1>
            <?php if (isset($_SESSION["error"])) {
                echo "<p class='error-message'>{$_SESSION["error"]}</p>";
                unset($_SESSION["error"]);
            } ?>
            <div class="input-box">
                <input type="text" name="username" placeholder="Username" required>
                <i class='bx bx-user'></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
                <i class='bx bxs-lock-alt'></i>
            </div>
            <div class="remember-me">
                <label><input type="checkbox">Remember Me</label>
            </div>
            <button type="submit" class="btn">Login</button>
            <div class="register-link">
                <p>Don't have an account? <a href="register.php">Register Here</a></p>
            </div>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.ripples@0.6.3/dist/jquery.ripples.min.js"></script>
    <script>$('#ripple').ripples({ resolution: 512, dropRadius: 20, perturbance: 0.04 });</script>
</body>
</html>