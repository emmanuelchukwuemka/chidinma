<?php
$role = $_SESSION['role'];
$full_name = $_SESSION['full_name'];
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="#">
            <i class="fas fa-heartbeat me-2"></i>Outreach Monitor
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <span class="nav-link text-white">
                        <i class="fas fa-user me-1"></i><?php echo $full_name; ?>
                        (<?php echo ucfirst($role); ?>)
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="javascript:void(0)">
    <i class="fas fa-bell me-1"></i>Notifications
</a>
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