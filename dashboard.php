<?php
session_start();
require_once 'db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];

$total_programs = $pdo->query("SELECT COUNT(*) as total FROM programs")->fetch()['total'];
$total_activities = $pdo->query("SELECT COUNT(*) as total FROM activities")->fetch()['total'];
$completed_activities = $pdo->query("SELECT COUNT(*) as total FROM activities WHERE status='completed'")->fetch()['total'];
$pending_evaluations = $pdo->query("SELECT COUNT(*) as total FROM evaluations")->fetch()['total'];
$total_users = $pdo->query("SELECT COUNT(*) as total FROM users")->fetch()['total'];

$programs = $pdo->query("SELECT p.*, u.full_name as manager_name FROM programs p LEFT JOIN users u ON p.manager_id = u.user_id ORDER BY p.created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Outreach Monitor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar py-3">
            <a href="dashboard.php" class="active">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
            <?php if($role == 'admin'): ?>
            <a href="modules/users.php">
                <i class="fas fa-users me-2"></i>Manage Users
            </a>
            <?php endif; ?>
            <?php if($role == 'admin' || $role == 'manager'): ?>
            <a href="modules/programs.php">
                <i class="fas fa-project-diagram me-2"></i>Programs
            </a>
            <a href="modules/activities.php">
                <i class="fas fa-tasks me-2"></i>Activities
            </a>
            <a href="modules/milestones.php">
                <i class="fas fa-flag me-2"></i>Milestones
            </a>
            <a href="modules/reports.php">
                <i class="fas fa-chart-bar me-2"></i>Reports
            </a>
            <?php endif; ?>
            <?php if($role == 'supervisor'): ?>
            <a href="modules/evaluations.php">
                <i class="fas fa-clipboard-check me-2"></i>Evaluations
            </a>
            <?php endif; ?>
            <?php if($role == 'team_member'): ?>
            <a href="modules/my_activities.php">
                <i class="fas fa-tasks me-2"></i>My Activities
            </a>
            <?php endif; ?>
            <a href="modules/notifications.php">
                <i class="fas fa-bell me-2"></i>Notifications
            </a>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 main-content">
            <h4 class="mb-4">
                <i class="fas fa-tachometer-alt me-2 text-success"></i>
                Dashboard — Welcome, <?php echo $full_name; ?>!
            </h4>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card bg-stat-1">
                        <h2><?php echo $total_programs; ?></h2>
                        <p><i class="fas fa-project-diagram me-2"></i>Total Programs</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-stat-2">
                        <h2><?php echo $total_activities; ?></h2>
                        <p><i class="fas fa-tasks me-2"></i>Total Activities</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-stat-3">
                        <h2><?php echo $completed_activities; ?></h2>
                        <p><i class="fas fa-check-circle me-2"></i>Completed Activities</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-stat-4">
                        <h2><?php echo $total_users; ?></h2>
                        <p><i class="fas fa-users me-2"></i>Total Users</p>
                    </div>
                </div>
            </div>

            <!-- Recent Programs -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-project-diagram me-2"></i>Recent Programs
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Program Title</th>
                                <th>Manager</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
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
<script src="assets/js/main.js"></script>
</body>
</html>
