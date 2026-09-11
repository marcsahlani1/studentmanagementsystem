<?php 
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin'){
    header("Location: login.php");
    exit();
}
require_once 'config/db.php';
$error = '';
$grade_id = $_GET['id'] ?? '';
if(empty($grade_id)){
    header("Location: grades_admin.php");
    exit();
}
//FETCH EXISTING GRADE
$stmt = $pdo->prepare("
SELECT GRADES.grade_id,GRADES.grade,USERS.username,COURSES.course_name
FROM GRADES
JOIN USERS ON GRADES.user_id = USERS.user_id
JOIN COURSES ON GRADES.course_id = COURSES.course_id
WHERE GRADES.grade_id = ?");
$stmt->execute([$grade_id]);
$grade_record = $stmt->fetch();
if(!$grade_record){
    header("Location: grades_admin.php");
    exit();
}
$grade = $grade_record['grade'];
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $grade = $_POST['grade'] ?? '';
    if($grade === ''){
        $error = "Grade is required!";
    }elseif(!is_numeric($grade) || $grade < 0 || $grade > 20){
        $error = "Grade between 0 and 20!";
    }else{
        $updatestmt = $pdo->prepare("UPDATE GRADES SET grade = ? WHERE grade_id = ?");
        if($updatestmt->execute([$grade,$grade_id])){
            header("Location: grades_admin.php?message='Grade edited successfully!'");
            exit();
        }else{
            $error = "Failed to update grade.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Grade</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Edit Grade</h1>
        <p><a href="grades_admin.php">Back to Grades</a></p>
        <?php if(!empty($error)): ?>
            <div><?php echo htmlspecialchars($error);?></div>
        <?php endif;?>
        <form action="edit_grade.php?id=<?php echo $grade_id;?>" method="POST">
            <div>
                <label>Student</label>
                <input type="text" value="<?php echo htmlspecialchars($grade_record['username']);?>" disabled style="background-color: #eee;">
            </div>
            <div>
                <label>Course</label>
                <input type="text" value="<?php echo htmlspecialchars($grade_record['course_name']);?>" disabled style="background-color: #eee;">
            </div>
            <div>
                <label>Grade</label>
                <input type="number" step="0.01" min="0" max="20" name="grade" value="<?php echo htmlspecialchars($grade);?>" required>
            </div>
            <div>
                <button type="submit">Update Grade</button>
            </div>
        </form>
    </div>
</body>
</html>
