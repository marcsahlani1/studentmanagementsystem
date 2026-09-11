<?php 
session_start();
require_once 'config/db.php';

if(!isset($_SESSION["user_id"]) || $_SESSION['user_role'] !== 'user'){
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <nav class="nav-bar">
        <ul>
            <li><a href="dashboard_user.php">Dashboard</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="grades.php">Grades</a></li>
            <li><a href="logout.php">Logout</a></li>
            <li><a href="change_password.php">Change Password</a></li>
        </ul>
    </nav>
</body>
</html>
