<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin'){
    header("Location: login.php");
    exit();
}
require_once 'config/db.php';
$error = '';
$success_msg = '';
$course_name = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $course_name = trim($_POST['course_name'] ?? '');
    if(empty($course_name)){
        $error = "Course name is required!";
    }else{
        //CHECK COURSE NAME UNIQUENESS
        $stmt = $pdo->prepare("SELECT course_id FROM COURSES WHERE course_name = ?");
        $stmt->execute([$course_name]);
        if($stmt->rowCount() > 0){
            $error = "Course name already taken!";
        }else{
            //INSERT COURSE USING PDO
            $stmt = $pdo->prepare("INSERT INTO COURSES (course_name) VALUES (?)");
            if($stmt->execute([$course_name])){
                header("Location: courses.php?added_msg='course added succesfully!'");
                exit();
            }else{
                $error = "Failed to add course.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses Management</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Add New Course</h1>
        <p><a href="courses.php">Back to Courses</a></p>
        <?php if(!empty($error)): ?>
            <div><?php echo htmlspecialchars($error);?></div>
        <?php endif;?>
        <?php if(!empty($success_msg)): ?>
            <div><?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif;?>
        <form action="add_course.php" method="POST">
            <div>
                <label>Course Name</label>
                <input type="text" name="course_name" value="<?php echo htmlspecialchars($course_name);?>" required>
            </div>
            <div>
                <button type="submit">Add Course</button>
            </div>
        </form>
    </div>
</body>
</html>