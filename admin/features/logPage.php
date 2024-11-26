<?php
require_once '../../login/dbh.inc.php'; // DATABASE CONNECTION
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../login/login.php");
    exit();
}

//Get info from admin session
$user = $_SESSION['user'];
$admin_id = $_SESSION['user']['admin_id'];
$first_name = $_SESSION['user']['first_name'];
$last_name = $_SESSION['user']['last_name'];
$email = $_SESSION['user']['email'];
$contact_number = $_SESSION['user']['contact_number'];
$department_id = $_SESSION['user']['department_id'];
?>

<!doctype html>
<html lang="en">

<head>
    <title>Title</title>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- head CDN links -->
    <?php include '../../cdn/head.html'; ?>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/tables.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/nav-bottom.css">
</head>

<body>
    <header>
        <?php include '../../cdn/navbar.php'; ?>
    </header>
    <main>
        <div class="container-fluid">
            <div class="row g-4 pt-4">
                <!-- left sidebar -->
                <div class="col-lg-3 sidebar sidebar-left d-none d-xl-block mt-5" id="sidebar">
                    <div class="sticky-sidebar">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a href="dashboard.php">
                                    <i class="fas fa-chart-line me-2"></i>
                                    <span class="menu-text">Dashboard</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="../admin.php">
                                    <i class="fas fa-newspaper me-2"></i>
                                    <span class="menu-text">Feed</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="manage.php">
                                    <i class="fas fa-user me-2"></i>
                                    <span class="menu-text">My Profile</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="create.php">
                                    <i class="fas fa-bullhorn me-2"></i>
                                    <span class="menu-text">Create Announcement</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="active" href="logPage.php">
                                    <i class="fas fa-clipboard-list me-2"></i>
                                    <span class="menu-text">Logs</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="manage_student.php">
                                    <i class="fas fa-users-cog me-2"></i>
                                    <span class="menu-text">Manage Accounts</span>
                                </a>
                            </li>

                            <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'superadmin'): ?>
                                <li class="nav-item">
                                    <a href="manage_admin.php"><i class="fas fa-user-shield me-2"></i>Manage Admins</a>
                                </li>
                                <li class="nav-item">
                                    <a href="feedbackPage.php">
                                        <i class="fas fa-comments me-2"></i>
                                        <span class="menu-text">Feedback</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <!-- main content -->
                <div class="col-12 col-lg-9 main-content px-5 mt-5" style="margin: 0 auto;">

                    <div class="card shadow mt-5">
                        <div class="card-body">
                            <div class="table-responsive log-table">
                                <table class="table table-bordered table-hover display nowrap">
                                    <thead>
                                        <tr class="bg-primary text-white">
                                            <th class="align-middle">Log ID</th>
                                            <th class="align-middle">User ID</th>
                                            <th class="align-middle">User Type</th>
                                            <th class="align-middle">Action</th>
                                            <th class="align-middle">Description</th>
                                            <th class="align-middle">Timestamp</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        require_once '../../login/dbh.inc.php';

                                        try {

                                            if ($_SESSION['user']['role'] === 'superadmin') {
                                                $query = "SELECT * FROM logs ORDER BY timestamp DESC";
                                                $stmt = $pdo->prepare($query);
                                                $stmt->execute();
                                            } else {
                                                $query = "SELECT * FROM logs WHERE user_id = :admin_id ORDER BY timestamp DESC";
                                                $stmt = $pdo->prepare($query);
                                                $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
                                                $stmt->execute();
                                            }

                                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                $log_id = htmlspecialchars($row['log_id'] ?? '');
                                                $user_id = htmlspecialchars($row['user_id'] ?? '');
                                                $user_type = strtoupper(htmlspecialchars($row['user_type'] ?? ''));
                                                $action = strtoupper(htmlspecialchars($row['action'] ?? ''));
                                                $affected_table = htmlspecialchars($row['affected_table'] ?? '');
                                                $affected_record_id = htmlspecialchars($row['affected_record_id'] ?? '');
                                                $description = htmlspecialchars($row['description'] ?? '');
                                                $timestamp = htmlspecialchars($row['timestamp'] ?? '');
                                        ?>
                                                <tr>
                                                    <td class="align-middle"><?= $log_id ?></td>
                                                    <td class="align-middle"><?= $user_id ?></td>
                                                    <td class="align-middle">
                                                        <span class="badge bg-info text-dark"><?= $user_type ?></span>
                                                    </td>
                                                    <td class="align-middle">
                                                        <span class="badge <?= getActionBadgeClass($action) ?>"><?= $action ?></span>
                                                    </td>
                                                    <td class="align-middle"><?= $description ?></td>
                                                    <td class="align-middle"><?= formatTimestamp($timestamp) ?></td>
                                                </tr>
                                        <?php
                                            }
                                        } catch (PDOException $e) {
                                            echo '<tr><td colspan="8" class="text-center text-danger">Error fetching logs: ' . $e->getMessage() . '</td></tr>';
                                        }

                                        function getActionBadgeClass($action)
                                        {
                                            switch (strtolower($action)) {
                                                case 'create':
                                                    return 'bg-success';
                                                case 'update':
                                                    return 'bg-warning text-dark';
                                                case 'delete':
                                                    return 'bg-danger';
                                                default:
                                                    return 'bg-secondary';
                                            }
                                        }

                                        function formatTimestamp($timestamp)
                                        {
                                            return date('M d, Y h:i A', strtotime($timestamp));
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <nav class="navbar nav-bottom fixed-bottom d-block d-xl-none mt-5">
            <div class="container-fluid d-flex justify-content-around">
                <a href="dashboard.php" class="btn nav-bottom-btn">
                    <i class="fas fa-chart-line"></i>
                </a>

                <a href="../admin.php" class="btn nav-bottom-btn">
                    <i class="fas fa-newspaper"></i>
                </a>

                <a href="create.php" class="btn nav-bottom-btn">
                    <i class="fas fa-bullhorn"></i>
                </a>

                <a href="logPage.php" class="btn nav-bottom-btn active-btn">
                    <i class="fas fa-clipboard-list"></i>
                </a>

                <a href="manage_student.php" class="btn nav-bottom-btn">
                    <i class="fas fa-users-cog"></i>
                </a>

                <a href="manage.php" class="btn nav-bottom-btn">
                    <i class="fas fa-user"></i>
                </a>
            </div>
        </nav>
        <?php include 'changePassOtherPage.html'; ?>
    </main>
    <!-- Body CDN links -->
    <?php include '../../cdn/body.html'; ?>
    <script>
        function confirmLogout() {
            if (confirm('Are you sure you want to sign out?')) {
                window.location.href = '../../login/logout.php';
            }
            return false;
        }

        $(document).ready(function() {
            $('.table').DataTable({
                responsive: false,
                order: [
                    [5, 'desc'] // Sort by timestamp (column index 5) by default
                ],
                pageLength: 15, // Show 15 entries per page
                lengthChange: false, // Remove "Show entries" dropdown
                language: {
                    search: "Search logs:",
                    info: "Showing _START_ to _END_ of _TOTAL_ logs",
                    infoEmpty: "Showing 0 to 0 of 0 logs",
                    infoFiltered: "(filtered from _MAX_ total logs)",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                columnDefs: [{
                        orderable: false,
                        targets: [4] // Disable sorting for description column
                    },
                    {
                        width: "15%",
                        targets: [4, 5] // Make description column wider
                    },
                    {
                        width: "5%",
                        targets: [0, 1, 2, 3]
                    },
                    {
                        className: "text-nowrap",
                        targets: [0, 1, 2, 3, 5] // Prevent text wrapping in other columns
                    },
                    {
                        targets: [5],
                        type: 'date',
                    }
                ],
                dom: '<"top"f>rt<"bottom"ip><"clear">', // Removed 'l' for length menu
                initComplete: function() {
                    $('.dataTables_filter input').addClass('form-control form-control-sm');
                }
            });
        });
    </script>
</body>

</html>