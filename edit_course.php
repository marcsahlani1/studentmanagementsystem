<?php 
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin'){
    header("Location: login.php");
    exit();
}
require_once 'config/db.php';
//GET COURSE ID FROM URL
$course_id = $_GET['id'] ?? null;
if(!$course_id){
    header("Location: courses.php");
    exit();
}
//FETCH COURSE DETAILS FROM DATABASE
$stmt = $pdo->prepare("SELECT * FROM COURSES WHERE course_id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();
if(!$course){
    header("Location: courses.php?added_msg='course not found!'");//will use same added_msg get variable as the add_course page
                                                                  // bcz the 2 pages wont work in the same time anyway
    exit();
}
$error = '';
$success_msg = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $course_name = trim($_POST['course_name'] ?? '');
    if(empty($course_name)){
        $error = "Course name required!";
    }else{
        //CHECK COURSE NAME UNIQUENESS(EXCLUDE THE CURRENT ONE BEING EDITED)
        $stmt = $pdo->prepare("SELECT course_id FROM COURSES WHERE course_name = ? AND course_id != ?");
        $stmt->execute([$course_name,$course_id]);
        if($stmt->rowCount() > 0){
            $error = "Course name already taken!";
        }else{
            //UPDATE COURSE USING PDO
            $stmt = $pdo->prepare("UPDATE COURSES SET course_name = ? WHERE course_id = ?");
            if($stmt->execute([$course_name,$course_id])){
                $success_msg = "Course updated successfully!";
                //REFRESH COURSE DETAILS
                $stmt = $pdo->prepare("SELECT * FROM COURSES WHERE course_id = ?");
                $stmt->execute([$course_id]);
                $course = $stmt->fetch();
            }else{
                $error = "Failed to update course.";
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
    <title>Edit Course</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Edit Course</h1>
        <p><a href="courses.php">Back to Courses</a></p>
        <?php if(!empty($error)): ?>
            <div><?php echo htmlspecialchars($error);?></div>
        <?php endif;?>
        <?php if(!empty($success_msg)): ?>
            <div><?php echo htmlspecialchars($success_msg);?></div>
        <?php endif;?>
        <form action="edit_course.php?id=<?php echo $course_id;?>" method="POST">
            <div>
                <label>Course Name</label>
                <input type="text" name="course_name" value="<?php echo htmlspecialchars($course['course_name']);?>" required>
            </div>
            <div>
                <button type="submit">Update Course</button>
            </div>
        </form>
    </div>
</body>
</html>