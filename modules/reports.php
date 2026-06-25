<?php
session_start();
require_once '../db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

$programs = mysqli_query($conn, "SELECT * FROM programs");
$selected_program = null;
$activities = null;
$milestones = null;
$evaluations = null;

if(isset($_GET['program_id'])) {
    $pid = (int)$_GET['program_id'];
    $selected_program = mysqli_fetch_assoc(mysqli_query($conn, "SELECT p.*, u.full_name as manager_name FROM programs p LEFT JOIN users u ON p.manager_id=u.user_id WHERE p.program_id=$pid"));
    $activities = mysqli_query($conn, "SELECT a.*, u.full_name as assigned_name FROM activities a LEFT JOIN users u ON a.assigned_to=u.user_id WHERE a.program_id=$pid");
    $milestones = mysqli_query($conn, "SELECT * FROM milestones WHERE program_id=$pid");
    $evaluations = mysqli_query($conn, "SELECT e.*, a.title as activity_title, u.full_name as supervisor_name FROM evaluations e LEFT JOIN activities a ON e.activity_id=a.activity_id LEFT JOIN users u ON e.supervisor_id=u.user_id WHERE a.program_id=$pid");

    $total_activities = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM activities WHERE program_id=$pid"))['total'];
    $completed_activities = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM activities WHERE program_id=$pid AND status='completed'"))['total'];
    $total_milestones = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM milestones WHERE program_id=$pid"))['total'];
    $achieved_milestones = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM milestones WHERE program_id=$pid AND status='achieved'"))['total'];
    $progress = $total_activities > 0 ? round(($completed_activities / $total_activities) * 100) : 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports - Outreach Monitor</title>
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
            <a href="activities.php"><i class="fas fa-tasks me-2"></i>Activities</a>
            <a href="milestones.php"><i class="fas fa-flag me-2"></i>Milestones</a>
            <a href="reports.php" class="active"><i class="fas fa-chart-bar me-2"></i>Reports</a>
            <a href="notifications.php"><i class="fas fa-bell me-2"></i>Notifications</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
        </div>
        <div class="col-md-10 main-content">
            <h4 class="mb-4"><i class="fas fa-chart-bar me-2 text-success"></i>Program Reports</h4>

            <!-- Select Program -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-search me-2"></i>Select Program to Generate Report
                </div>
                <div class="card-body">
                    <form method="GET">
                        <div class="row">
                            <div class="col-md-8">
                                <select name="program_id" class="form-select" required>
                                    <option value="">Select a Program</option>
                                    <?php while($p = mysqli_fetch_assoc($programs)): ?>
                                    <option value="<?php echo $p['program_id']; ?>" <?php echo isset($_GET['program_id']) && $_GET['program_id'] == $p['program_id'] ? 'selected' : ''; ?>>
                                        <?php echo $p['title']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-file-alt me-2"></i>Generate Report
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php if($selected_program): ?>
            <!-- Report Output -->
            <div id="reportContent">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white d-flex justify-content-between">
                        <span><i class="fas fa-file-alt me-2"></i>Progress Report — <?php echo $selected_program['title']; ?></span>
                        <button onclick="window.print()" class="btn btn-sm btn-light">
                            <i class="fas fa-print me-1"></i>Print Report
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Program Info -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p><strong>Program Title:</strong> <?php echo $selected_program['title']; ?></p>
                                <p><strong>Program Manager:</strong> <?php echo $selected_program['manager_name']; ?></p>
                                <p><strong>Status:</strong> <?php echo ucfirst($selected_program['status']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Start Date:</strong> <?php echo $selected_program['start_date']; ?></p>
                                <p><strong>End Date:</strong> <?php echo $selected_program['end_date']; ?></p>
                                <p><strong>Report Generated:</strong> <?php echo date('d M Y H:i'); ?></p>
                            </div>
                        </div>

                        <!-- Progress Summary -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="stat-card bg-stat-1">
                                    <h2><?php echo $total_activities; ?></h2>
                                    <p>Total Activities</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card bg-stat-2">
                                    <h2><?php echo $completed_activities; ?></h2>
                                    <p>Completed Activities</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card bg-stat-3">
                                    <h2><?php echo $total_milestones; ?></h2>
                                    <p>Total Milestones</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card bg-stat-4">
                                    <h2><?php echo $achieved_milestones; ?></h2>
                                    <p>Achieved Milestones</p>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mb-4">
                            <h6 class="fw-bold">Overall Progress: <?php echo $progress; ?>%</h6>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: <?php echo $progress; ?>%"></div>
                            </div>
                        </div>

                        <!-- Activities Table -->
                        <h6 class="fw-bold mb-3">Activities</h6>
                        <table class="table table-bordered mb-4">
                            <thead>
                                <tr>
                                    <th>Activity</th>
                                    <th>Assigned To</th>
                                    <th>Deadline</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($a = mysqli_fetch_assoc($activities)): ?>
                                <tr>
                                    <td><?php echo $a['title']; ?></td>
                                    <td><?php echo $a['assigned_name']; ?></td>
                                    <td><?php echo $a['deadline']; ?></td>
                                    <td><?php echo ucfirst(str_replace('_', ' ', $a['status'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

                        <!-- Milestones Table -->
                        <h6 class="fw-bold mb-3">Milestones</h6>
                        <table class="table table-bordered mb-4">
                            <thead>
                                <tr>
                                    <th>Milestone</th>
                                    <th>Target Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($m = mysqli_fetch_assoc($milestones)): ?>
                                <tr>
                                    <td><?php echo $m['title']; ?></td>
                                    <td><?php echo $m['target_date']; ?></td>
                                    <td><?php echo ucfirst(str_replace('_', ' ', $m['status'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

                        <!-- Evaluations Table -->
                        <h6 class="fw-bold mb-3">Evaluations</h6>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Activity</th>
                                    <th>Supervisor</th>
                                    <th>Score</th>
                                    <th>Feedback</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($e = mysqli_fetch_assoc($evaluations)): ?>
                                <tr>
                                    <td><?php echo $e['activity_title']; ?></td>
                                    <td><?php echo $e['supervisor_name']; ?></td>
                                    <td><?php echo $e['score']; ?>/100</td>
                                    <td><?php echo $e['feedback']; ?></td>
                                    <td><?php echo date('d M Y', strtotime($e['evaluated_at'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>