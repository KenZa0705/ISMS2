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

// Database queries for analytics
try {
    // Posts by department
    $departmentQuery = $pdo->query("SELECT d.department_name, COUNT(ad.announcement_id) AS post_count
                                    FROM department d
                                    LEFT JOIN announcement_department ad ON d.department_id = ad.department_id
                                    GROUP BY d.department_name");
    $departments = $departmentQuery->fetchAll(PDO::FETCH_ASSOC);

    // Posts by course
    $courseQuery = $pdo->query("SELECT c.course_name, COUNT(ac.announcement_id) AS post_count
                                FROM course c
                                LEFT JOIN announcement_course ac ON c.course_id = ac.course_id
                                GROUP BY c.course_name");
    $courses = $courseQuery->fetchAll(PDO::FETCH_ASSOC);

    // Posts by year level
    $yearLevelQuery = $pdo->query("SELECT yl.year_level, COUNT(ay.announcement_id) AS post_count
                                   FROM year_level yl
                                   LEFT JOIN announcement_year_level ay ON yl.year_level_id = ay.year_level_id
                                   GROUP BY yl.year_level");
    $yearLevels = $yearLevelQuery->fetchAll(PDO::FETCH_ASSOC);

    // Posts by admin
    $adminQuery = $pdo->query("SELECT CONCAT(a.first_name, ' ', a.last_name) AS admin_name, COUNT(ann.announcement_id) AS post_count
                               FROM admin a
                               LEFT JOIN announcement ann ON a.admin_id = ann.admin_id
                               GROUP BY a.admin_id, a.first_name, a.last_name");
    $admins = $adminQuery->fetchAll(PDO::FETCH_ASSOC);

    // Active student and admin counts
    $activeStudents = $pdo->query("SELECT COUNT(*) AS active_students FROM student")->fetch(PDO::FETCH_ASSOC)['active_students'];
    $activeAdmins = $pdo->query("SELECT COUNT(*) AS active_admins FROM admin")->fetch(PDO::FETCH_ASSOC)['active_admins'];
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
}
?>
<script>
    const analyticsData = {
        departments: <?php echo json_encode($departments); ?>,
        courses: <?php echo json_encode($courses); ?>,
        yearLevels: <?php echo json_encode($yearLevels); ?>,
        admins: <?php echo json_encode($admins); ?>,
        activeStudents: <?php echo json_encode($activeStudents); ?>,
        activeAdmins: <?php echo json_encode($activeAdmins); ?>
    };
</script>
<!doctype html>
<html lang="en">

<head>
    <title>Manage Posts</title>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- head CDN links -->
    <?php include '../../cdn/head.html'; ?>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/modals.css">
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

                <a class="btn nav-bottom-btn active" href="manage.php">
                    <i class="bi bi-kanban"></i>
                    <span class="icon-label">Manage</span>
                </a>

                <a class="btn nav-bottom-btn" href="create.php">
                    <i class="bi bi-megaphone"></i>
                    <span class="icon-label">Create</span>
                </a>

                <a class="btn nav-bottom-btn" href="logPage.php">
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
                                    <a class="btn active mb-3" href=""><i class="bi bi-kanban"></i> Manage Post</a>
                                    <a class="btn" href="logPage.php"><i class="bi bi-clipboard"></i> Logs</a>
                                    <a class="btn" href="manage_student.php"><i class="bi bi-person-plus"></i> Manage Student Account</a>
                                    <!-- Button to Open Analytics Modal -->
                                    <div class="col-md-6 main-content pt-5 px-5">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#analyticsModal">
                                            View Analytics
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            
                <!-- main content -->
                <div class="col-md-6 main-content pt-5 px-5">
                    <div class="feed-container">
                        <?php
                        require_once '../../login/dbh.inc.php';

                        try {
                            // Query to get the announcements along with the year level, department, and course
                            $query = "
                            SELECT a.*, ad.first_name, ad.last_name,
                                STRING_AGG(DISTINCT yl.year_level, ', ') AS year_levels,
                                STRING_AGG(DISTINCT d.department_name, ', ') AS departments,
                                STRING_AGG(DISTINCT c.course_name, ', ') AS courses
                            FROM announcement a
                            JOIN admin ad ON a.admin_id = ad.admin_id
                            LEFT JOIN announcement_year_level ayl ON a.announcement_id = ayl.announcement_id
                            LEFT JOIN year_level yl ON ayl.year_level_id = yl.year_level_id
                            LEFT JOIN announcement_department adp ON a.announcement_id = adp.announcement_id
                            LEFT JOIN department d ON adp.department_id = d.department_id
                            LEFT JOIN announcement_course ac ON a.announcement_id = ac.announcement_id
                            LEFT JOIN course c ON ac.course_id = c.course_id 
							WHERE a.admin_id = 1
                            GROUP BY a.announcement_id, ad.first_name, ad.last_name 
                            ORDER BY a.updated_at DESC";

                            $stmt = $pdo->prepare($query);
                            $stmt->execute();

                            $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            if ($announcements) {
                                foreach ($announcements as $row) {
                                    $announcement_id = $row['announcement_id'];
                                    $title = $row['title'];
                                    $description = $row['description'];
                                    $image = $row['image'];
                                    $announcement_admin_id = $row['admin_id'];
                                    $admin_first_name = $row['first_name'];
                                    $admin_last_name = $row['last_name'];
                                    $admin_name =  $admin_first_name . ' ' . $admin_last_name;
                                    $updated_at = date('F d, Y', strtotime($row['updated_at']));

                                    $year_levels = !empty($row['year_levels']) ? explode(',', $row['year_levels']) : ['None'];
                                    $departments = !empty($row['departments']) ? explode(',', $row['departments']) : ['None'];
                                    $courses = !empty($row['courses']) ? explode(',', $row['courses']) : ['None'];
                        ?>


                                    <div class="card mb-3">
                                        <div class="profile-container d-flex px-3 pt-3">
                                            <div class="profile-pic">
                                                <img class="img-fluid" src="img/test pic.jpg" alt="">
                                            </div>
                                            <p class="ms-1 mt-1"><?php echo htmlspecialchars($admin_name); ?></p>
                                            <?php if ($admin_id === $announcement_admin_id) : ?>
                                                <div class="dropdown ms-auto">
                                                    <span id="dropdownMenuButton<?php echo $announcement_id; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="bi bi-three-dots"></i>
                                                    </span>
                                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $announcement_id; ?>">
                                                        <li><a class="dropdown-item" href="edit_announcement.php?id=<?php echo $announcement_id; ?>">Edit</a></li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="#"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#deletePost"
                                                                data-announcement-id="<?php echo $announcement_id; ?>">Delete</a>
                                                        </li>

                                                    </ul>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="image-container mx-3">
                                            <img src="../uploads/<?php echo htmlspecialchars($image); ?>" alt="Post Image" class="img-fluid">
                                        </div>

                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($title); ?></h5>
                                            <div class="card-text">
                                                <p class="mb-2"><?php echo htmlspecialchars($description); ?></p>

                                                Tags:
                                                <?php

                                                $all_tags = array_merge($year_levels, $departments, $courses);


                                                foreach ($all_tags as $tag) : ?>
                                                    <span class="badge rounded-pill bg-danger mb-2"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                                <?php endforeach; ?>
                                            </div>

                                            <small>Updated at <?php echo htmlspecialchars($updated_at); ?></small>
                                        </div>
                                    </div>

                        <?php
                                }
                            } else {
                                echo '<p class="text-center">No announcements found.</p>';
                            }
                        } catch (PDOException $e) {
                            // Handle any errors that occur during query execution
                            echo "Error: " . $e->getMessage();
                        }
                        ?>
                    </div>
                </div>

                <!-- Delete modal -->
                <div class="modal fade" id="deletePost" tabindex="-1" aria-labelledby="deletePost" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-sm">
                        <div class="modal-content custom" style="border-radius: 15px;">
                            <div class="modal-header pb-1" style="border: none">
                                <h1 class="modal-title fs-5" id="exampleModalLabel">Delete Post?</h1>
                                <button type="button" class="btn-close delete-modal-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body py-0" style="border: none;">
                                <p style="font-size: 15px;">Once you delete this post, it can't be restored.</p>
                            </div>
                            <div class="modal-footer pt-0" style="border: none;">
                                <button type="button" class="btn go-back-btn" data-bs-dismiss="modal">Go Back</button>
                                <button type="button" class="btn delete-btn" id="confirm-delete-btn">Yes, Delete</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- after deletion modal -->
                <div class="modal fade" id="postDelete" tabindex="-1" aria-labelledby="post-deleted" aria-hidden="true">
                    <div class="modal-dialog modal-md">
                        <div class="modal-content delete-message">
                            <div class="modal-header" style="border: none;">
                                <p class="modal-title" id="exampleModalLabel">Announcement deleted succesfully.</p>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Analytics Modal -->
                <div class="modal fade" id="analyticsModal" tabindex="-1" aria-labelledby="analyticsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="analyticsModalLabel">Analytics</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Analytics Section -->
                                <div class="analytics-section">
                                    <!-- Counters for Active Users -->
                                    <div>
                                        <p>Active Students: <span id="activeStudents"></span></p>
                                        <p>Active Admins: <span id="activeAdmins"></span></p>
                                    </div>
                                    
                                    <!-- Bar Charts -->
                                    <canvas id="departmentChart"></canvas>
                                    <canvas id="courseChart"></canvas>
                                    <canvas id="yearLevelChart"></canvas>

                                    <!-- Pie Chart for Admins -->
                                    <canvas id="adminChart"></canvas>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
    </main>
    <!-- Body CDN links -->
    <?php include '../../cdn/body.html'; ?>
    <script src="../js/admin.js"></script>
    <script src="../js/manage.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", () => {
        // Set up data for each chart using analyticsData object

        // Department Chart
        const departmentLabels = analyticsData.departments.map(dept => dept.department_name);
        const departmentCounts = analyticsData.departments.map(dept => dept.post_count);
        new Chart(document.getElementById('departmentChart'), {
            type: 'bar',
            data: {
                labels: departmentLabels,
                datasets: [{
                    label: 'Posts per Department',
                    data: departmentCounts,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)'
                }]
            }
        });

        // Course Chart
        const courseLabels = analyticsData.courses.map(course => course.course_name);
        const courseCounts = analyticsData.courses.map(course => course.post_count);
        new Chart(document.getElementById('courseChart'), {
            type: 'bar',
            data: {
                labels: courseLabels,
                datasets: [{
                    label: 'Posts per Course',
                    data: courseCounts,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)'
                }]
            }
        });

        // Year Level Chart
        const yearLevelLabels = analyticsData.yearLevels.map(level => level.year_level);
        const yearLevelCounts = analyticsData.yearLevels.map(level => level.post_count);
        new Chart(document.getElementById('yearLevelChart'), {
            type: 'bar',
            data: {
                labels: yearLevelLabels,
                datasets: [{
                    label: 'Posts per Year Level',
                    data: yearLevelCounts,
                    backgroundColor: 'rgba(255, 206, 86, 0.6)'
                }]
            }
        });

        // Admin Chart
        const adminLabels = analyticsData.admins.map(admin => admin.admin_name);
        const adminCounts = analyticsData.admins.map(admin => admin.post_count);
        new Chart(document.getElementById('adminChart'), {
            type: 'pie',
            data: {
                labels: adminLabels,
                datasets: [{
                    label: 'Posts per Admin',
                    data: adminCounts,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(153, 102, 255, 0.6)'
                    ]
                }]
            }
        });

        // Display active user counts
        document.getElementById('activeStudents').textContent = analyticsData.activeStudents;
        document.getElementById('activeAdmins').textContent = analyticsData.activeAdmins;
    });
    </script>


</body>

</html>