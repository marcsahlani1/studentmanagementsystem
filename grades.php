<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}
require_once 'config/db.php';
$user_id = $_SESSION['user_id'];
//FETCH ONLY THE GRADES OF THE LOGGED PERSON
$stmt = $pdo->prepare("
      SELECT COURSES.course_name,GRADES.grade
      FROM GRADES
      JOIN COURSES ON GRADES.course_id = COURSES.course_id
      WHERE GRADES.user_id = ?");
$stmt->execute([$user_id]);
$grades = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Grades</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h1>My Grades</h1>
        <p><a href="dashboard.php">Back to dashboard</a></p>
        <?php if(count($grades) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Course Name</th>
                        <th>Grade (/20)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($grades as $grade): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($grade['course_name']);?></td>
                            <td><?php echo htmlspecialchars($grade['grade']);?></td>
                        </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You have no grades</p>
        <?php endif;?>
    </div>
</body>
</html>