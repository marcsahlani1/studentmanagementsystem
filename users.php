<?php 
session_start();
require_once 'config/db.php';
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}
$admin_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT user_role FROM USERS WHERE user_id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();
if(!$admin || $admin['user_role'] !== 'admin'){
    header("Location: dashboard.php");
    exit();
}
$success_msg = '';
$error = "";



//DELETE USERS
if(isset($_GET['delete_id'])){
    $delete_id = intval($_GET['delete_id']);
    if($delete_id === $admin_id){
        $error = "Admin can't delete himself!";
    }else{
        //fetch profile picture
        $stmt_pic = $pdo->prepare("SELECT pfp FROM USERS WHERE user_id = ?");
        $stmt_pic->execute([$delete_id]);
        $target_user = $stmt_pic->fetch();
        //delete
        $del_stmt = $pdo->prepare("DELETE FROM USERS WHERE user_id = ?");
        if($del_stmt->execute([$delete_id])){
            if(!empty($target_user['pfp']) && file_exists('uploads/' . $target_user['pfp'])){
                unlink('uploads/' . $target_user['pfp']);
            }
            $message = "User deleted successfully!";
        }else{
            $error = "Failed to delete user.";
        }
    }
}

//SEARCH USERS
$search = trim($_GET['search'] ?? '');
if(!empty($search)){
    $search_query = "%" . $search . "%";
    $stmt = $pdo->prepare("SELECT * FROM USERS WHERE username LIKE ? OR fname LIKE ?  OR lname LIKE ? OR email LIKE ?
                          ORDER BY user_id");
    $stmt->execute([$search_query,$search_query,$search_query,$search_query]);                      
}else{
    $stmt = $pdo->query("SELECT * FROM USERS ORDER BY user_id");
}
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Users Management</h2>
        <a href="dashboard.php" class="btn">Back to dashboard</a>
    </div>
    <?php if(!empty($error)): ?>
        <div><?php echo htmlspecialchars($error);?></div>
    <?php endif;?>
    <?php if(!empty($success_msg)): ?>
        <div><?php echo htmlspecialchars($success_msg);?></div>
    <?php endif;?>
    <a href="add_users.php" class="btn">Add User</a>
    <form method="GET" action="users.php">
        <input type="text" name="search" placeholder="Search user" value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn" type="submit">Search</button>
        <?php if(!empty($search)): ?>
            <a href="users.php" class="btn">Reset</a>
        <?php endif;?>
    </form>
    <div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Picture</th>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>DOB</th>
                    <th>Gender</th>
                    <th>Phone</th>
                    <th>Description</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($users) > 0): ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u['user_id']);?></td>
                            <td><img src="uploads/<?php echo htmlspecialchars($u['pfp']);?>" alt="Profile" onerror="this.onerror=null; this.src='images/default.jpeg';" width="40" height="40"></td>
                            <td><?php echo htmlspecialchars($u['username']);?></td>
                            <td><?php echo htmlspecialchars($u['fname'] . ' ' . $u['lname']);?></td>
                            <td><?php echo htmlspecialchars($u['email']);?></td>
                            <td><?php echo htmlspecialchars($u['user_role']);?></td>
                            <td><?php echo htmlspecialchars($u['dob']);?></td>
                            <td><?php echo htmlspecialchars($u['gender']);?></td>
                            <td><?php echo htmlspecialchars($u['phone']);?></td>
                            <td><?php echo htmlspecialchars($u['user_desc']);?></td>
                            <td><?php echo htmlspecialchars($u['user_address']);?></td>
                            <td>
                                <a href="edit_user.php?id=<?php echo $u['user_id'];?>">Edit</a>
                                <?php if($u['user_id'] != $admin_id): ?>
                                    <a href="users.php?delete_id=<?php echo $u['user_id'];?>" class="btn" 
                                                                  onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                <?php else: ?>
                                    <span style="color:#404040; font-style:italic;">Current Admin</span>
                                <?php endif;?> 
                            </td>
                        </tr>
                    <?php endforeach;?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
                
        





    
