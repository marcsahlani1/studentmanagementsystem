<?php 
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin'){
    header("Location: login.php");
    exit();
}
require_once 'config/db.php';
$error = '';
$success_msg = $_GET['message'] ?? '';
//DELETE GRADES
if(isset($_GET['delete_id'])){
    $delete_id = $_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM GRADES WHERE grade_id = ?");
    if(!($stmt->execute([$delete_id]))){
        $error = "Failed to delete grade.";
    }
    else{
       header("Location: grades_admin.php?message='Grade deleted successfully!'");
       exit();
    }
}
//SEARCH
$search = trim($_GET['search'] ?? '');
if($search !== ''){
    $stmt = $pdo->prepare("
    SELECT GRADES.*,USERS.username,COURSES.course_name
    FROM GRADES
    JOIN USERS ON GRADES.user_id = USERS.user_id
    JOIN COURSES ON GRADES.course_id = COURSES.course_id
    WHERE USERS.username LIKE ? 
    OR COURSES.course_name LIKE ?");
    $stmt->execute(["%$search%","%$search%"]);
}else{
    $stmt = $pdo->query("
    SELECT GRADES.*,USERS.username,COURSES.course_name
    FROM GRADES
    JOIN USERS ON GRADES.user_id = USERS.user_id
    JOIN COURSES ON GRADES.course_id = COURSES.course_id");
}
$grades = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Grades</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Manage Grades</h1>
        <p><a href="dashboard.php">Back to Dashboard</a> | <a href="add_grade.php">Add New Grade</a></p>
        <?php if(!empty($success_msg)): ?>
            <div><?php echo htmlspecialchars($success_msg);?></div>
        <?php endif;?>
        <?php if(!empty($error)): ?>
            <div><?php echo htmlspecialchars($error);?></div>
        <?php endif;?>
        <form method="GET" action="grades_admin.php">
            <input type="text" name="search" placeholder="search by student or course" value="<?php echo htmlspecialchars($search)?>">
            <button type="submit">Search</button>
            <?php if($search !== ''): ?>
                <a href="grades_admin.php">Reset</a>
            <?php endif;?>
        </form>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Course</th>
                    <th>Grade</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($grades) > 0): ?>
                    <?php foreach ($grades as $grade): ?>
                        <tr>
                            <td><?php echo $grade['grade_id'];?></td>
                            <td><?php echo htmlspecialchars($grade['username']);?></td>
                            <td><?php echo htmlspecialchars($grade['course_name']);?></td>
                            <td><?php echo htmlspecialchars($grade['grade']);?></td>
                            <td>
                                <a href="edit_grade.php?id=<?php echo $grade['grade_id'];?>">Edit</a> 
                                <a href="grades_admin.php?delete_id=<?php echo $grade['grade_id'];?>"
                                 onclick="return confirm('Are you sure you want to delete this grade?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach;?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No grades found.</td>
                    </tr>
                <?php endif;?>
            </tbody>
        </table>
    </div>
</body></html>