<?php 
session_start();
//UNSET ALL SESSION VARIABLES
$_SESSION = array();
//DESTROY THE SESSION
session_destroy();
//REDIRECT TO LOGIN PAGE
header("Location: login.php");
exit();
?>
