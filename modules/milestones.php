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

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_milestone'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $program_id = (int)$_POST['program_id'];
    $target_date = $_POST['target_date'];

    $query = "INSERT INTO milestones (title, program_id, target_date) VALUES ('$title', '$program_id', '$target_date')";
    if(mysqli_query($conn, $query)) {
        $success = "Milestone added successfully!";
    } else {
        $error = "Error adding milestone!";
    }
}

if(isset($_GET['achieve'])) {
    $id = (int)$_GET['achieve'];
    mysqli_query($conn, "UPDATE milestones SET status='achieved' WHERE milestone_id=$id");
    header("Location: milestones.php");
    exit();
}

if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM milestones WHERE milestone_id=$id");
    header("Location: milestones.php");
    exit();
}

$milestones = mysqli_query($conn, "SELECT m.*, p.title as program_title FROM milestones m LEFT JOIN programs p ON m.program_id = p.program_id ORDER BY m.created_at DESC");
$programs = mysqli_query($conn, "SELECT * FROM programs WHERE status='active'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Milestones - Outreach Monitor</title>
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
            <a href="milestones.php" class="active"><i class="fas fa-flag me-2"></i>Milestones</a>
            <a href="reports.php"><i class="fas fa-chart-bar me-2"></i>Reports</a>
            <a href="notifications.php"><i class="fas fa-bell me-2"></i>Notifications</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
        </div>
        <div class="col-md-10 main-content">
            <h4 class="mb-4"><i class="fas fa-flag me-2 text-success"></i>Milestones</h4>

            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if($role == 'admin' || $role == 'manager'): ?>
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-plus me-2"></i>Add New Milestone
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Milestone Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Program</label>
                                <select name="program_id" class="form-select" required>
                                    <option value="">Select Program</option>
                                    <?php while($p = mysqli_fetch_assoc($programs)): ?>
                                    <option value="<?php echo $p['program_id']; ?>"><?php echo $p['title']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Target Date</label>
                                <input type="date" name="target_date" class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" name="add_milestone" class="btn btn-success">
                                    <i class="fas fa-plus me-2"></i>Add Milestone
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-list me-2"></i>All Milestones
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Program</th>
                                <th>Target Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($milestone = mysqli_fetch_assoc($milestones)): ?>
                            <tr>
                                <td><?php echo $milestone['title']; ?></td>
                                <td><?php echo $milestone['program_title']; ?></td>
                                <td><?php echo $milestone['target_date']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $milestone['status'] == 'achieved' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $milestone['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($milestone['status'] == 'not_achieved'): ?>
                                    <a href="?achieve=<?php echo $milestone['milestone_id']; ?>" class="btn btn-sm btn-success">
                                        <i class="fas fa-check"></i> Mark Achieved
                                    </a>
                                    <?php endif; ?>
                                    <a href="?delete=<?php echo $milestone['milestone_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete()">
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