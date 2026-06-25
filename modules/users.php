<?php
session_start();
require_once '../db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$success = '';
$error = '';

// Add new user
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if(mysqli_num_rows($check) > 0) {
        $error = "Email already exists!";
    } else {
        $query = "INSERT INTO users (full_name, email, password, role) VALUES ('$full_name', '$email', '$password', '$role')";
        if(mysqli_query($conn, $query)) {
            $success = "User created successfully!";
        } else {
            $error = "Error creating user!";
        }
    }
}

// Delete user
if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM users WHERE user_id=$id");
    header("Location: users.php");
    exit();
}

// Toggle status
if(isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status FROM users WHERE user_id=$id"));
    $new_status = $user['status'] == 'active' ? 'inactive' : 'active';
    mysqli_query($conn, "UPDATE users SET status='$new_status' WHERE user_id=$id");
    header("Location: users.php");
    exit();
}

$users = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");
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
            <h