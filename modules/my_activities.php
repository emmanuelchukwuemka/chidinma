<?php
session_start();
require_once '../db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$allowed_statuses = ['pending', 'in_progress', 'completed'];

if(isset($_GET['update'])) {
    $id = (int)$_GET['update'];
    $status = in_array($_GET['status'], $allowed_statuses) ? $_GET['status'] : 'pending';
    $pdo->prepare("UPDATE activities SET status=? WHERE activity_id=? AND assigned_to=?")->execute([$status, $id, $user_id]);
    header("Location: my_activities.php");
    exit();
}

$stmt = $pdo->prepare("SELECT a.*, p.title as program_title FROM activities a LEFT JOIN programs p ON a.program_id = p.program_id WHERE a.assigned_to=? ORDER BY a.deadline ASC");
$stmt->execute([$user_id]);
$activities = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Activities - Outreach Monitor</title>
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
            <a href="my_activities.php" class="active"><i class="fas fa-tasks me-2"></i>My Activities</a>
            <a href="notifications.php"><i class="fas fa-bell me-2"></i>Notifications</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
        </div>
        <div class="col-md-10 main-content">
            <h4 class="mb-4"><i class="fas fa-tasks me-2 text-success"></i>My Activities</h4>

            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-list me-2"></i>Assigned Activities
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Activity</th>
                                <th>Program</th>
                                <th>Deadline</th>
                                <th>Status</th>
                                <th>Update Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($activities as $activity): ?>
                            <tr>
                                <td><?php echo $activity['title']; ?></td>
                                <td><?php echo $activity['program_title']; ?></td>
                                <td><?php echo $activity['deadline']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $activity['status'] == 'completed' ? 'success' : ($activity['status'] == 'in_progress' ? 'primary' : 'warning'); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $activity['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($activity['status'] == 'pending'): ?>
                                    <a href="?update=<?php echo $activity['activity_id']; ?>&status=in_progress" class="btn btn-sm btn-primary">
                                        Start
                                    </a>
                                    <?php elseif($activity['status'] == 'in_progress'): ?>
                                    <a href="?update=<?php echo $activity['activity_id']; ?>&status=completed" class="btn btn-sm btn-success">
                                        Complete
                                    </a>
                                    <?php else: ?>
                                    <span class="text-success"><i class="fas fa-check-circle"></i> Done</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
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
