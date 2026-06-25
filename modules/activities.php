<?php
session_start();
require_once '../db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$success = '';
$error = '';
$role = $_SESSION['role'];

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_activity'])) {
    $stmt = $pdo->prepare("INSERT INTO activities (title, description, program_id, assigned_to, deadline) VALUES (?, ?, ?, ?, ?)");
    if($stmt->execute([$_POST['title'], $_POST['description'], (int)$_POST['program_id'], (int)$_POST['assigned_to'], $_POST['deadline']])) {
        $assigned_to = (int)$_POST['assigned_to'];
        $msg = "You have been assigned a new activity: " . $_POST['title'];
        $pdo->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'deadline')")->execute([$assigned_to, $msg]);
        $success = "Activity created successfully!";
    } else {
        $error = "Error creating activity!";
    }
}

if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM activities WHERE activity_id=?")->execute([$id]);
    header("Location: activities.php");
    exit();
}

$activities = $pdo->query("SELECT a.*, p.title as program_title, u.full_name as assigned_name FROM activities a LEFT JOIN programs p ON a.program_id = p.program_id LEFT JOIN users u ON a.assigned_to = u.user_id ORDER BY a.created_at DESC");
$programs = $pdo->query("SELECT * FROM programs WHERE status='active'");
$team_members = $pdo->query("SELECT * FROM users WHERE role='team_member' AND status='active'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activities - Outreach Monitor</title>
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
            <a href="programs.php"><i class="fas fa-project-diagram me-2"></i>Programs</a>
            <a href="activities.php" class="active"><i class="fas fa-tasks me-2"></i>Activities</a>
            <a href="milestones.php"><i class="fas fa-flag me-2"></i>Milestones</a>
            <a href="reports.php"><i class="fas fa-chart-bar me-2"></i>Reports</a>
            <a href="notifications.php"><i class="fas fa-bell me-2"></i>Notifications</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
        </div>
        <div class="col-md-10 main-content">
            <h4 class="mb-4"><i class="fas fa-tasks me-2 text-success"></i>Activities</h4>

            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if($role == 'admin' || $role == 'manager'): ?>
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-plus me-2"></i>Add New Activity
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Activity Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Program</label>
                                <select name="program_id" class="form-select" required>
                                    <option value="">Select Program</option>
                                    <?php while($p = $programs->fetch()): ?>
                                    <option value="<?php echo $p['program_id']; ?>"><?php echo $p['title']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Assign To</label>
                                <select name="assigned_to" class="form-select" required>
                                    <option value="">Select Team Member</option>
                                    <?php while($tm = $team_members->fetch()): ?>
                                    <option value="<?php echo $tm['user_id']; ?>"><?php echo $tm['full_name']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Deadline</label>
                                <input type="date" name="deadline" class="form-control" required>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold">Description</label>
                                <textarea name="description" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" name="add_activity" class="btn btn-success">
                                    <i class="fas fa-plus me-2"></i>Add Activity
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-list me-2"></i>All Activities
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Program</th>
                                <th>Assigned To</th>
                                <th>Deadline</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($activity = $activities->fetch()): ?>
                            <tr>
                                <td><?php echo $activity['title']; ?></td>
                                <td><?php echo $activity['program_title']; ?></td>
                                <td><?php echo $activity['assigned_name']; ?></td>
                                <td><?php echo $activity['deadline']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $activity['status'] == 'completed' ? 'success' : ($activity['status'] == 'in_progress' ? 'primary' : 'warning'); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $activity['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?delete=<?php echo $activity['activity_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete()">
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
