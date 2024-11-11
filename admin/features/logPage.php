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
</head>

<body>
    <header>
        <?php include '../../cdn/navbar.php'; ?>
        <nav class="navbar nav-bottom fixed-bottom d-block d-md-none mt-5">
            <div class="container-fluid justify-content-around">
                <a href="../admin.php" class="btn nav-bottom-btn">
                    <i class="bi bi-house"></i>
                    <span class="icon-label">Home</span>
                </a>

                <a class="btn nav-bottom-btn" href="manage.php">
                    <i class="bi bi-kanban"></i>
                    <span class="icon-label">Manage</span>
                </a>

                <a class="btn nav-bottom-btn" href="create.php">
                    <i class="bi bi-megaphone"></i>
                    <span class="icon-label">Create</span>
                </a>

                <a class="btn nav-bottom-btn active" href="logPage.php">
                    <i class="bi bi-clipboard"></i>
                    <span class="icon-label">Logs</span>
                </a>

                <a class="btn nav-bottom-btn" href="manage_student.php">
                    <i class="bi bi-person-plus"></i>
                    <span class="icon-label">Students</span>
                </a>

            </div>
        </nav>
    </header>
    <main>
        <div class="container-fluid pt-5">
            <div class="row g-4">
                <!-- left sidebar -->
                <div class="col-md-3 d-none d-md-block">
                    <div class="sticky-sidebar pt-5">
                        <div class="sidebar">
                            <div class="card">
                                <div class="card-body d-flex flex-column">
                                    <a href="../admin.php" class="btn mb-3"><i class="bi bi-house"></i> Home</a>
                                    <a class="btn mb-3" href="create.php"><i class="bi bi-megaphone"></i> Create Announcement</a>
                                    <a class="btn mb-3" href="manage.php"><i class="bi bi-kanban"></i> Manage Post</a>
                                    <a class="btn mb-3 active" href="logPage.php"><i class="bi bi-clipboard"></i> Logs</a>
                                    <a class="btn" href="manage_student.php"><i class="bi bi-person-plus"></i> Manage Student Account</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- main content -->
                <div class="col-md-9 pt-5 px-5">
                    <h3 class="text-left"><b>Admin Logs</b></h3>

                    <table class="table table-light table-hover">
                    <thead class="table table-primary">
                        <tr>
                            <th>Log ID</th>
                            <th>User ID</th>
                            <th>User Type</th>
                            <th>Action</th>
                            <th>Affected Table</th>
                            <th>Affected Record ID</th>
                            <th>Description</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        require_once '../../login/dbh.inc.php'; // Database connection

                        try {
                            $query = "SELECT * FROM logs ORDER BY timestamp DESC";
                            $stmt = $pdo->query($query);

                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $log_id = htmlspecialchars($row['log_id'] ?? '');
                                $user_id = htmlspecialchars($row['user_id'] ?? '');
                                $user_type = htmlspecialchars($row['user_type'] ?? '');
                                $action = htmlspecialchars($row['action'] ?? '');
                                $affected_table = htmlspecialchars($row['affected_table'] ?? '');
                                $affected_record_id = htmlspecialchars($row['affected_record_id'] ?? '');
                                $description = htmlspecialchars($row['description'] ?? '');
                                $timestamp = htmlspecialchars($row['timestamp'] ?? '');
                        ?>
                            <tr>
                                <td><?= $log_id ?></td>
                                <td><?= $user_id ?></td>
                                <td><?= $user_type ?></td>
                                <td><?= $action ?></td>
                                <td><?= $affected_table ?></td>
                                <td><?= $affected_record_id ?></td>
                                <td><?= $description ?></td>
                                <td><?= $timestamp ?></td>
                            </tr>
                        <?php
                            }
                        } catch (PDOException $e) {
                            echo "Error fetching logs: " . $e->getMessage();
                        }
                        ?>
                    </tbody>
                </table>

                </div>
                <script src="../js/create.js"></script>
            </div>
        </div>
    </main>
    <!-- Body CDN links -->
    <?php include '../../cdn/body.html'; ?>
</body>

</html>