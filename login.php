<?php 
session_start();
if(isset($_SESSION['user_id'])){
    header("Location: dashboard.php");
    exit();
}
//include the database connection file
require_once 'config/db.php';
$error ='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['pass'] ?? '');

    if(empty($username)||empty($password)){
        $error = "Please fill in all fields.";
    }else{
        try{
            $stmt = $pdo->prepare("SELECT user_id,username,pass,user_role FROM USERS WHERE username= :username LIMIT 1");
            $stmt->execute([':username'=> $username]);
            $user = $stmt->fetch();
           /* echo "<pre>";  
            var_dump($user);
            echo "/<pre>";
            echo "username: " . $user['username'] . "<br>";
            echo "password: " . $user['pass'] . "<br>";
            echo "user id: " . $user['user_id'] . "<br>"; */ // just to test and see the info inside the $user array
            
           // $test_hash = password_hash("123456", PASSWORD_DEFAULT); // try to hash a password here in the login page
           /* put a password inside the password_hash function then use it down in the password_verify function to test
           if the verification is working and we are accessing the database correctly */ 

            if($user && password_verify($password,$user['pass'] /*$test_hash*/)){ 
                session_regenerate_id(true);

                $_SESSION['user_id']= $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['user_role'];

                header("Location: dashboard.php");
                exit();
            }else{
                $error = "Invalid username or password.";
            }
        }catch(PDOException $e){
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Student Management System</title>
    <link rel="stylesheet" href="css/styles.css"> 
</head>
<body>
    <div class="login-container">
        <h2>Student Management System</h2>
        <h3>Login</h3>
        <?php if (!empty($error)): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div class="form-grp">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required placeholder="Enter your username">
            </div>
            <div class="form-grp">
                <label for="pass">Password:</label>
                <input type="password" id="pass" name ="pass" required placeholder="Enter your password">
            </div>
            <button type="submit" class="btn">Login</button>
            <div class="form-links">
                <a href="forgot_password.php">Forgot Password</a>
                <a href="register.php">Create Account</a>
            </div>
        </form>
    </div>
</body>
</html>    
