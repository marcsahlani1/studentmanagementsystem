<?php 
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin'){
    header("Location: login.php");
    exit();
}
$error ='';
$success_msg = '';
$course_added_msg = $_GET['added_msg'] ?? '';

require_once 'config/db.php';
//DELETE COURSE
if(isset($_GET['delete_id'])){
    $delete_id = $_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM COURSES WHERE course_id = ?");
    if($stmt->execute([$delete_id])){
        $success_msg = "Course deleted successfully!";
    }else{
        $error = "Failed to delete course.";
    }
    header("Location: courses.php");
    exit();
}
//SEARCH
$search = trim($_GET['search'] ?? '');
if($search !== ''){
    $stmt = $pdo->prepare("SELECT * FROM COURSES WHERE course_name LIKE ?");
    $stmt->execute(["%$search%"]);
}else{
    $stmt = $pdo->query("SELECT * FROM COURSES");
}
$courses = $stmt->fetchAll();
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
        <h1>Manage Courses</h1>
        <p><a href="dashboard.php">Back to Dashboard</a> | <a href="add_course.php">Add New Course</a></p>

        <?php if(!empty($course_added_msg)): ?>
            <div><?php echo htmlspecialchars($course_added_msg);?></div>
        <?php endif;?>
        <?php if(!empty($success_msg)): ?>
            <div><?php echo htmlspecialchars($success_msg);?></div>
        <?php endif;?>
        <?php if(!empty($error)): ?>
            <div><?php echo htmlspecialchars($error);?></div>
        <?php endif;?>

        <form method="GET" action="courses.php">
            <input type="text" name="search" placeholder="Search by course name" value="<?php echo htmlspecialchars($search);?>">
            <button type="submit">Search</button>
            <?php if($search !== ''): ?>
                <a href="courses.php">Reset</a>
            <?php endif;?>
        </form>
        <table>
            <thead>
                <tr>
                    <th>Course ID</th>
                    <th>Course Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($courses) > 0): ?>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><?php echo $course['course_id'];?></td>
                            <td><?php echo htmlspecialchars($course['course_name']);?></td>
                            <td>
                                <a href="edit_course.php?id=<?php echo $course['course_id'];?>">Edit</a>
                                <a href="courses.php?delete_id=<?php echo $course['course_id'];?>" 
                                   onclick="return confirm(Are you sure you want to delete this course?);">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach;?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">No courses found.</td>
                    </tr>
                <?php endif;?>          
            </tbody>
        </table>
    </div>
</body>
</html>

