<?php
require_once '../../login/dbh.inc.php'; // DATABASE CONNECTION
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../login/login.php");
    exit();
}

//Get info from admin session
$user = $_SESSION['user'];
$user_id = $_SESSION['user']['student_id'];
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
    <link rel="stylesheet" href="../css/user.css">
    <link rel="stylesheet" href="../../admin/css/tables.css">
    <link rel="stylesheet" href="../../admin/css/sidebar.css">
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
                                <a href="../user.php">
                                    <i class="fas fa-newspaper me-2"></i>
                                    <span class="menu-text">Feed</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="active" href="logPage.php">
                                    <i class="fas fa-clipboard-list me-2"></i>
                                    <span class="menu-text">Logs</span>
                                </a>
                            </li>

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
                                            $query = "SELECT * FROM logs WHERE user_id = :student_id ORDER BY timestamp DESC";
                                            $stmt = $pdo->prepare($query);
                                            $stmt->bindParam(':student_id', $user_id, PDO::PARAM_INT);
                                            $stmt->execute();

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
        <?php include 'changePassOtherPage.html'; ?>
    </main>
    <!-- Body CDN links -->
    <?php include '../../cdn/body.html'; ?>
    <script>
        $(document).ready(function() {
            $('.table').DataTable({
                responsive: false,
                order: [
                    [5, 'desc']
                ], // Sort by timestamp (column index 5) by default
                pageLength: 15, // Show 17 entries per page
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
                        width: "20%",
                        targets: 4 // Make description column wider
                    },
                    {
                        className: "text-nowrap",
                        targets: [0, 1, 2, 3, 5] // Prevent text wrapping in other columns
                    }
                ],
                dom: '<"top"f>rt<"bottom"ip><"clear">', // Removed 'l' for length menu
                initComplete: function() {
                    // Add custom styling to search
                    $('.dataTables_filter input').addClass('form-control form-control-sm');
                }
            });
        });
    </script>
</body>

</html>