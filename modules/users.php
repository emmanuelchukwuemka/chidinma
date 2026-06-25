<?php
session_start();
require_once '../db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $check = $pdo->prepare("SELECT user_id FROM users WHERE email=?");
    $check->execute([$_POST['email']]);
    if($check->fetch()) {
        $error = "Email already exists!";
    } else {
        $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
        if($stmt->execute([$_POST['full_name'], $_POST['email'], $hashed, $_POST['role']])) {
            $success = "User created successfully!";
        } else {
            $error = "Error creating user!";
        }
    }
}

if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM users WHERE user_id=?")->execute([$id]);
    header("Location: users.php");
    exit();
}

if(isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $stmt = $pdo->prepare("SELECT status FROM users WHERE user_id=?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    $new_status = $row['status'] == 'active' ? 'inactive' : 'active';
    $pdo->prepare("UPDATE users SET status=? WHERE user_id=?")->execute([$new_status, $id]);
    header("Location: users.php");
    exit();
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - Outreach Monitor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/navbar.php'; ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar py-3">
            <a href="../dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
            <a href="users.php" class="active"><i class="fas fa-users me-2"></i>Manage Users</a>
            <a href="programs.php"><i class="fas fa-project-diagram me-2"></i>Programs</a>
            <a href="activities.php"><i class="fas fa-tasks me-2"></i>Activities</a>
            <a href="milestones.php"><i class="fas fa-flag me-2"></i>Milestones</a>
            <a href="reports.php"><i class="fas fa-chart-bar me-2"></i>Reports</a>
            <a href="notifications.php"><i class="fas fa-bell me-2"></i>Notifications</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
        </div>
        <div class="col-md-10 main-content">
            <h4 class="mb-4"><i class="fas fa-users me-2 text-success"></i>Manage Users</h4>

            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-plus me-2"></i>Add New User
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Full Name</label>
                                <input type="text" name="full_name" class="form-control" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Role</label>
                                <select name="role" class="form-select" required>
                                    <option value="team_member">Team Member</option>
                                    <option value="supervisor">Supervisor</option>
                                    <option value="manager">Manager</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" name="add_user" class="btn btn-success">
                                    <i class="fas fa-plus me-2"></i>Add User
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-list me-2"></i>All Users
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($user = $users->fetch()): ?>
                            <tr>
                                <td><?php echo $user['full_name']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td><?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $user['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?toggle=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-toggle-on"></i>
                                    </a>
                                    <a href="?delete=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete()">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
