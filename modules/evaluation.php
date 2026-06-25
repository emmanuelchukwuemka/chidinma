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
$user_id = $_SESSION['user_id'];

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_evaluation'])) {
    $stmt = $pdo->prepare("INSERT INTO evaluations (activity_id, supervisor_id, score, feedback) VALUES (?, ?, ?, ?)");
    if($stmt->execute([(int)$_POST['activity_id'], $user_id, (int)$_POST['score'], $_POST['feedback']])) {
        $success = "Evaluation submitted successfully!";
    } else {
        $error = "Error submitting evaluation!";
    }
}

$evaluations = $pdo->query("SELECT e.*, a.title as activity_title, u.full_name as supervisor_name FROM evaluations e LEFT JOIN activities a ON e.activity_id = a.activity_id LEFT JOIN users u ON e.supervisor_id = u.user_id ORDER BY e.evaluated_at DESC");
$activities = $pdo->query("SELECT * FROM activities WHERE status != 'completed'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Evaluations - Outreach Monitor</title>
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
            <a href="evaluation.php" class="active"><i class="fas fa-clipboard-check me-2"></i>Evaluations</a>
            <a href="notifications.php"><i class="fas fa-bell me-2"></i>Notifications</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
        </div>
        <div class="col-md-10 main-content">
            <h4 class="mb-4"><i class="fas fa-clipboard-check me-2 text-success"></i>Evaluations</h4>

            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if($role == 'supervisor' || $role == 'admin'): ?>
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-plus me-2"></i>Submit New Evaluation
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Select Activity</label>
                                <select name="activity_id" class="form-select" required>
                                    <option value="">Select Activity</option>
                                    <?php while($a = $activities->fetch()): ?>
                                    <option value="<?php echo $a['activity_id']; ?>"><?php echo $a['title']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Score (out of 100)</label>
                                <input type="number" name="score" class="form-control" min="0" max="100" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Feedback</label>
                                <textarea name="feedback" class="form-control" rows="2" required></textarea>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" name="submit_evaluation" class="btn btn-success">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Evaluation
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-list me-2"></i>All Evaluations
                </div>
                <div class="card-body">
                    <table class="table table-hover">
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
                            <?php while($eval = $evaluations->fetch()): ?>
                            <tr>
                                <td><?php echo $eval['activity_title']; ?></td>
                                <td><?php echo $eval['supervisor_name']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $eval['score'] >= 70 ? 'success' : ($eval['score'] >= 50 ? 'warning' : 'danger'); ?>">
                                        <?php echo $eval['score']; ?>/100
                                    </span>
                                </td>
                                <td><?php echo $eval['feedback']; ?></td>
                                <td><?php echo date('d M Y', strtotime($eval['evaluated_at'])); ?></td>
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
