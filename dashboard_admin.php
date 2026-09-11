<?php 
session_start();
require_once 'config/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin'){
    header("Location: login.php");
    exit();
}
//TOTAL USERS
$total_users = $pdo->query("SELECT COUNT(*) FROM USERS")->fetchColumn();
//TOTAL ADMINS
$total_admins = $pdo->query("SELECT COUNT(*) FROM USERS WHERE user_role ='admin'")->fetchColumn();
//TOTAL STUDENTS
$total_students = $pdo->query("SELECT COUNT(*) FROM USERS WHERE user_role ='user'")->fetchColumn();
//TOTAL COURSES
$total_courses = $pdo->query("SELECT COUNT(*) FROM COURSES")->fetchColumn();
//TOTAL GRADES
$total_grades = $pdo->query("SELECT COUNT(*) FROM GRADES")->fetchColumn();
//AVG,HIGHEST AND LOWEST GRADE
$grade_stats = $pdo->query("SELECT AVG(grade) as avg_grade, MAX(grade) as max_grade, MIN(grade) as min_grade FROM GRADES")->fetch();
$avg_grade = $grade_stats['avg_grade'] !== null ? round($grade_stats['avg_grade'],2) : 'N/A';
$highest_grade = $grade_stats['max_grade'] !== null ? $grade_stats['max_grade'] : 'N/A';
$lowest_grade = $grade_stats['min_grade'] !== null ? $grade_stats['min_grade'] : 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <nav class="nav-bar">
        <ul>
            <li><a href="dashboard_admin.php">Dashboard</a></li>
            <li><a href="users.php">Users</a></li>
            <li><a href="courses.php">Courses</a></li>
            <li><a href="grades_admin.php">Grades</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="change_password.php">Change Password</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
    <h2>System Statistics</h2>
    <div style="display:grid;grid-template-columns: repeat(auto-fit,minmax(200px,1fr));gap: 15px;margin-bottom:30px;">
        <div style="border: 1px solid #ccc;padding: 15px; border-radius: 5px;background: #f9f9f9;">
            <h3>Total Users</h3>
            <p style="font-size: 24px;font-weight: bold;"><?php echo $total_users;?></p>
        </div>
        <div style="border: 1px solid #ccc;padding: 15px; border-radius: 5px;background: #f9f9f9;">
            <h3>Total Admins</h3>
            <p style="font-size: 24px;font-weight: bold;"><?php echo $total_admins;?></p>
        </div>
        <div style="border: 1px solid #ccc;padding: 15px; border-radius: 5px;background: #f9f9f9;">
            <h3>Total Students</h3>
            <p style="font-size: 24px;font-weight: bold;"><?php echo $total_students;?></p>
        </div>
        <div style="border: 1px solid #ccc;padding: 15px; border-radius: 5px;background: #f9f9f9;">
            <h3>Total Courses</h3>
            <p style="font-size: 24px;font-weight: bold;"><?php echo $total_courses;?></p>
        </div>
        <div style="border: 1px solid #ccc;padding: 15px; border-radius: 5px;background: #f9f9f9;">
            <h3>Total Grades</h3>
            <p style="font-size: 24px;font-weight: bold;"><?php echo $total_grades;?></p>
        </div>
        <div style="border: 1px solid #ccc;padding: 15px; border-radius: 5px;background: #f9f9f9;">
            <h3>Average Grade</h3>
            <p style="font-size: 24px;font-weight: bold;"><?php echo $avg_grade;?></p>
        </div>
        <div style="border: 1px solid #ccc;padding: 15px; border-radius: 5px;background: #f9f9f9;">
            <h3>Highest Grade</h3>
            <p style="font-size: 24px;font-weight: bold;"><?php echo $highest_grade;?></p>
        </div>
        <div style="border: 1px solid #ccc;padding: 15px; border-radius: 5px;background: #f9f9f9;">
            <h3>Lowest Grade</h3>
            <p style="font-size: 24px;font-weight: bold;"><?php echo $lowest_grade;?></p>
        </div>
    </div>
</body>
</html>
