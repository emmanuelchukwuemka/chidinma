<?php
session_start();
require_once '../db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$success = '';
$error = '';
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_program'])) {
    $stmt = $pdo->prepare("INSERT INTO programs (title, description, start_date, end_date, manager_id) VALUES (?, ?, ?, ?, ?)");
    if($stmt->execute([$_POST['title'], $_POST['description'], $_POST['start_date'], $_POST['end_date'], $user_id])) {
        $success = "Program created successfully!";
    } else {
        $error = "Error creating program!";
    }
}

if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM programs WHERE program_id=?")->execute([$id]);
    header("Location: programs.php");
    exit();
}

$programs = $pdo->query("SELECT p.*, u.full_name as manager_name FROM programs p LEFT JOIN users u ON p.manager_id = u.user_id ORDER BY p.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Programs - Outreach Monitor</title>
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
            <?php if($role == 'admin'): ?>
            <a href="users.php"><i class="fas fa-users me-2"></i>Manage Users</a>
            <?php endif; ?>
            <a href="programs.php" class="active"><i class="fas fa-project-diagram me-2"></i>Programs</a>
            <a href="activities.php"><i class="fas fa-tasks me-2"></i>Activities</a>
            <a href="milestones.php"><i class="fas fa-flag me-2"></i>Milestones</a>
            <a href="reports.php"><i class="fas fa-chart-bar me-2"></i>Reports</a>
            <a href="notifications.php"><i class="fas fa-bell me-2"></i>Notifications</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
        </div>
        <div class="col-md-10 main-content">
            <h4 class="mb-4"><i class="fas fa-project-diagram me-2 text-success"></i>Outreach Programs</h4>

            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if($role == 'admin' || $role == 'manager'): ?>
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-plus me-2"></i>Create New Program
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Program Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Start Date</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">End Date</label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Description</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" name="add_program" class="btn btn-success">
                                    <i class="fas fa-plus me-2"></i>Create Program
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-list me-2"></i>All Programs
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Manager</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($program = $programs->fetch()): ?>
                            <tr>
                                <td><?php echo $program['title']; ?></td>
                                <td><?php echo $program['manager_name']; ?></td>
                                <td><?php echo $program['start_date']; ?></td>
                                <td><?php echo $program['end_date']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $program['status'] == 'active' ? 'success' : ($program['status'] == 'completed' ? 'primary' : 'warning'); ?>">
                                        <?php echo ucfirst($program['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($role == 'admin' || $role == 'manager'): ?>
                                    <a href="?delete=<?php echo $program['program_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete()">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
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
