<?php 
session_start();
require_once 'config/db.php';
$message = '';
$error = '';
$email = '';
//Function to generate a password that contains: 
//length between 6 and 10 chars + has uppercase and lowercase letters + digits
function generatePass(){
    $uppercaseSet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lowercaseSet = 'abcdefghijklmnopqrstuvwxyz';
    $digitsSet = '0123456789' ;
    //choose random length 6-10
    $length = rand(6,10);
    $generatedPass = '';

    //guarantee at least one uppercase, lowercase and digit
    $generatedPass .= $uppercaseSet[rand(0, strlen($uppercaseSet) - 1)];
    $generatedPass .= $lowercaseSet[rand(0, strlen($lowercaseSet) - 1)];
    $generatedPass .= $digitsSet[rand(0, strlen($digitsSet) - 1)];
    $allcharacters = $uppercaseSet . $lowercaseSet . $digitsSet ;//contains all letters and digits
    // fill the rest of the generated password with random characters from all sets combined
    for($i = strlen($generatedPass); $i < $length ; $i++){
        $generatedPass .= $allcharacters[rand(0, strlen($allcharacters) - 1)];
    }
    //shuffle the string
    return str_shuffle($generatedPass);
}
if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $email = trim($_POST["email"]);
    if(empty($email)){
        $error = "Please enter your email!";
    }elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error = "Invalid email format!";
    }else{
        //check if email exists using pdo
        $stmt = $pdo->prepare("SELECT user_id, username FROM USERS WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if($user){
            // call the function we created to generate a random password
            $temp_pass = generatePass();
            //HASH THE PASSWORD
            $hashed_pass = password_hash($temp_pass, PASSWORD_DEFAULT);
            //Update the database and insert the new password
            $stmt1 = $pdo->prepare("UPDATE USERS SET pass = ? WHERE user_id = ?");
            if($stmt1->execute([$hashed_pass,$user["user_id"]])){
                /* $to = $email;
                $subject = "Your temporary password";
                $email_msg = "Hello" . $user["username"] . ",\n\n" . 
                             "Your temporary password is: " . $temp_pass ;
                $headers = "From: no-reply@studentms.com";
                if(mail($to,$subject,$email_msg,$headers)){
                    $message = "A temporary password has been sent to your email.<a href='login.php'> Login here.</a>";
                }else*/{$message = "Password was reset! Your temporary password is: ". htmlspecialchars($temp_pass) .
                                   ". <a href='login.php'>Login here.</a>";}
            }else{$error = "Something went wrong, try again.";}
        }else{$error = "No account with that email address";}
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Forgot Password</title>
        <link rel="stylesheet" href="css/styles.css">
    </head>
    <body>
        <div class="container" >
            <h2>Password recovery</h2>
            <p>Enter your email to receive a temporary password</p>
            <?php if(!empty($error)): ?>
                <div><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if(!empty($message)): ?>
                <div><?php echo $message; ?></div>
            <?php endif; ?>

            <form action="forgot_password.php" method="POST">
                <div class="form-grp">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <button type="submit" class="btn">Send Password</button>
            </form>
            <p><a href="login.php">Back to Login:</a></p>
        </div>
    </body>
</html>