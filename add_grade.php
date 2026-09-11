<?php 
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin'){
    header("Location: login.php");
    exit();
}
require_once 'config/db.php';
//FETCH students and courses
$students = $pdo->query("SELECT user_id,username FROM USERS")->fetchAll();
$courses = $pdo->query("SELECT course_id,course_name FROM COURSES")->fetchAll();
$error = '';
$user_id = '';
$course_id = '';
$grade = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $user_id = $_POST['user_id'] ?? '';
    $course_id = $_POST['course_id'] ?? '';
    $grade = $_POST['grade'] ?? '';
    if(empty($user_id) || empty($course_id) || $grade === ''){
        $error = "All fields are required!";
    }elseif(!is_numeric($grade) || $grade < 0 || $grade > 20){
        $error = "Grade should be between 0 and 20.";
    }else{
        $stmt = $pdo->prepare("INSERT INTO GRADES (user_id,course_id,grade) VALUES (?,?,?)");
        if($stmt->execute([$user_id,$course_id,$grade])){
            header("Location: grades_admin.php?message='Grade added successfully!'");
            exit();
        }else{
            $error = "Failed to add grade.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Grade</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Add Grade</h1>
        <p><a href="grades_admin.php">Back to Grades</a></p>
        <?php if(!empty($error)): ?>
            <div><?php echo htmlspecialchars($error);?></div>
        <?php endif;?>
        <form action="add_grade.php" method="POST">
            <div>
                <label>Student</label>
                <select name="user_id" required>
                    <option value="">Select Student</option>
                    <?php foreach($students as $student): ?>
                        <option value="<?php echo $student['user_id'];?>" <?php echo($user_id == $student['user_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($student['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Course</label>
                <select name="course_id" required>
                    <option value="">Select Course</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo htmlspecialchars($course['course_id']);?>" <?php echo($course_id == $course['course_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course['course_name']); ?>
                        </option>
                    <?php endforeach;?>
                </select>
            </div>
            <div>
                <label>Grade (/20): </label>
                <input type="number" step="0.01" min="0" max="20" name="grade" value="<?php echo htmlspecialchars($grade); ?>" required>
            </div>
            <div>
                <button type="submit">Add Grade</button>
            </div>
        </form>
    </div>
</body>
</html>