<?php 
session_start();
if(!isset($_SESSION["user_id"])){
    header("Location: login.php");
    exit();
}
$role = $_SESSION["user_role"];
if($role === "admin"){
    header("Location: dashboard_admin.php");
    exit();
}elseif($role === "user"){
    header("Location: dashboard_user.php");
    exit();
}else{
    session_destroy();
    header("Location: login.php");
    exit();
}
?>