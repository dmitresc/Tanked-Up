<?php
session_start();

// Ensure user is logged in and has an admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); // Redirect to login page if not logged in or not admin
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

// Check if the user ID is passed in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = $_GET['id'];

    // Fetch user data to check if the user is an admin
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        $_SESSION['error_message'] = "User not found!";
        header("Location: admin_dashboard.php");
        exit();
    }

    // If trying to delete an admin, check how many admins are left
    if ($user['role'] === 'admin') {
        // Query to count the number of admins
        $adminCountResult = $conn->query("SELECT COUNT(*) AS admin_count FROM users WHERE role = 'admin'");
        $adminCount = $adminCountResult->fetch_assoc()['admin_count'];

        // If there's only 1 admin left, prevent deletion
        if ($adminCount <= 1) {
            $_SESSION['error_message'] = "You cannot delete the last admin user!";
            header("Location: admin_dashboard.php");
            exit();
        }
    }

    // Prepare SQL query to delete the user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id); // 'i' for integer
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // User deleted successfully
        $_SESSION['success_message'] = "User deleted successfully!";
    } else {
        // Error deleting user
        $_SESSION['error_message'] = "Error deleting user.";
    }

    $stmt->close();
} else {
    $_SESSION['error_message'] = "Invalid user ID.";
}

// Redirect back to the admin dashboard
header("Location: admin_dashboard.php");
exit();
?>
