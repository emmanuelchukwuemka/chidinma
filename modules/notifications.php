<?php
session_start();
require_once '../db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$full_name = $_SESSION['full_name'];

if(isset($_GET['read'])) {
    $id = (int)$_GET['read'];
    mysqli_query($conn, "UPDATE notifications SET is_read=1 WHERE notification_id=$id AND user_id=$user_id");
    header("Location: notifications.php");
    exit();
}

if(isset($_GET['read_all'])) {
    mysqli_query($conn, "UPDATE notifications SET is_read=1 WHERE user_id=$user_id");
    header("Location: notifications.php");
    exit();
}

$notifications = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id=$user_id ORDER BY created_at DESC");
$unread_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM notifications WHERE user_id=$user_id AND is_read=0"))['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications - Outreach Monitor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="#">
            <i class="fas fa-heartbeat me-2"></i>Outreach Monitor
        </a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <span class="nav-link text-white">
                        <i class="fas fa-user me-1"></i><?php echo $full_name; ?> (<?php echo ucfirst($role); ?>)
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="../logout.php">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar py-3">
            <a href="../dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
            <?php if($role == 'admin'): ?>
            <a href="users.php"><i class="fas fa-users me-2"></i>Manage Users</a>
            <?php endif; ?>
            <?php if($role == 'admin' || $role == 'manager'): ?>
            <a href="programs.php"><i class="fas fa-project-diagram me-2"></i>Programs</a>
            <a href="activities.php"><i class="fas fa-tasks me-2"></i>Activities</a>
            <a href="milestones.php"><i class="fas fa-flag me-2"></i>Milestones</a>
            <a href="reports.php"><i class="fas fa-chart-bar me-2"></i>Reports</a>
            <?php endif; ?>
            <?php if($role == 'supervisor'): ?>
            <a href="evaluations.php"><i class="fas fa-clipboard-check me-2"></i>Evaluations</a>
            <?php endif; ?>
            <?php if($role == 'team_member'): ?>
            <a href="my_activities.php"><i class="fas fa-tasks me-2"></i>My Activities</a>
            <?php endif; ?>
            <a href="notifications.php" class="active"><i class="fas fa-bell me-2"></i>Notifications</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
        </div>
        <div class="col-md-10 main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>
                    <i class="fas fa-bell me-2 text-success"></i>Notifications
                    <?php if($unread_count > 0): ?>
                    <span class="badge bg-danger"><?php echo $unread_count; ?> unread</span>
                    <?php endif; ?>
                </h4>
                <?php if($unread_count > 0): ?>
                <a href="?read_all=1" class="btn btn-sm btn-success">
                    <i class="fas fa-check-double me-1"></i>Mark All as Read
                </a>
                <?php endif; ?>
            </div>
            <?php if(mysqli_num_rows($notifications) > 0): ?>
                <?php while($notif = mysqli_fetch_assoc($notifications)): ?>
                <div class="notification-item <?php echo $notif['is_read'] == 0 ? 'unread' : ''; ?>">
                    <div class="d-flex justify-content-between">
                        <div>
                            <i class="fas fa-<?php echo $notif['type'] == 'deadline' ? 'clock' : ($notif['type'] == 'evaluation' ? 'clipboard-check' : 'flag'); ?> me-2 text-success"></i>
                            <?php echo $notif['message']; ?>
                        </div>
                        <div>
                            <small class="text-muted"><?php echo date('d M Y H:i', strtotime($notif['created_at'])); ?></small>
                            <?php if($notif['is_read'] == 0): ?>
                            <a href="?read=<?php echo $notif['notification_id']; ?>" class="btn btn-sm btn-outline-success ms-2">Mark Read</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>No notifications found.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>