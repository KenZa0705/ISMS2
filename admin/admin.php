<?php
require_once '../login/dbh.inc.php';
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
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
$profile_picture = $_SESSION['user']['profile_picture'];
// Initialize filter arrays
$selected_departments = [];
$selected_year_levels = [];
$selected_courses = [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>ISMS Portal</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <?php include '../cdn/head.html'; ?>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/modals.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/feeds-card.css">
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg bg-white text-black fixed-top mb-5">
            <div class="container-fluid">
                <div class="user-left d-flex">
                    <div class="d-md-none ms-0 mt-2 me-3">
                        <button class="navbar-toggler border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                    </div>

                    <a class="navbar-brand d-flex align-items-center" href="#"><img src="img/brand.png" class="img-fluid branding" alt=""></a>
                </div>

                <div class="search-container">
                    <form id="searchForm" class="d-flex">
                        <input type="text" name="search" class="form-control" placeholder="Search announcements..." />
                        <button type="submit" class="btn btn-primary ms-2">Search</button>
                    </form>
                </div>

                <div class="user-right d-flex align-items-center justify-content-center">
                    <p class="username d-flex align-items-center m-0"><?php echo $first_name ?></p>
                    <div class="user-profile">
                        <div class="dropdown">
                            <button class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" style="border: none; background: none; padding: 0;">
                                <img class="img-fluid w-100" src="<?php echo 'uploads/' . htmlspecialchars($profile_picture); ?>" alt="Profile Picture">
                            </button>
                            <ul class="dropdown-menu mt-3" style="left: auto; right:1px;">
                                <li>
                                    <div class="col-md-6 main-content pt-5 px-5 dropdown-item text-center">
                                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                            Change Password
                                        </button>
                                    </div>
                                </li>
                                <li>
                                    <div class="col-md-6 main-content pt-5 px-5 dropdown-item text-center">
                                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#changeProfilePictureModal">
                                            Change Profile Picture
                                        </button>
                                    </div>
                                </li>
                                <li><a class="dropdown-item text-center" onclick="alert('Logged Out Successfully')" href="../login/logout.php">Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
        </nav>
        <nav class="navbar nav-bottom fixed-bottom d-block d-md-none mt-5">
            <div class="container-fluid justify-content-around">
                <a href="admin.php" class="btn nav-bottom-btn active">
                    <i class="bi bi-house"></i>
                    <span class="icon-label">Home</span>
                </a>

                <a class="btn nav-bottom-btn" href="features/manage.php">
                    <i class="bi bi-kanban"></i>
                    <span class="icon-label">Manage</span>
                </a>

                <a class="btn nav-bottom-btn" href="features/create.php">
                    <i class="bi bi-megaphone"></i>
                    <span class="icon-label">Create</span>
                </a>

                <a class="btn nav-bottom-btn" href="features/logPage.php">
                    <i class="bi bi-clipboard"></i>
                    <span class="icon-label">Logs</span>
                </a>

                <a class="btn nav-bottom-btn" href="features/manage_student.php">
                    <i class="bi bi-person-plus"></i>
                    <span class="icon-label">Students</span>
                </a>

            </div>
        </nav>
        <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
        </div>
    </header>

    <main>
        <div class="container-fluid pt-5">
            <div class="row g-4">
                <!-- Left sidebar -->
                <div class="col-lg-3 sidebar sidebar-left d-none d-lg-block">
                    <div class="sticky-sidebar pt-3 m-0 p-2">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a href=""><i class="bi bi-graph-up-arrow"></i>Dashboard</a>
                            </li>

                            <li class="nav-item">
                                <a href="admin.php"><i class="bi bi-house"></i>Feed</a>
                            </li>

                            <li class="nav-item">
                                <a href="features/manage.php"><i class="bi bi-person-circle"></i>My Profile</a>
                            </li>

                            <li class="nav-item">
                                <a href="features/create.php"><i class="bi bi-plus-circle"></i>Create Announcement</a>
                            </li>

                            <li class="nav-item">
                                <a href="features/logPage.php"><i class="bi bi-journal"></i>Logs</a>
                            </li>

                            <li class="nav-item">
                                <a href="features/manage_student.php"><i class="bi bi-person-badge"></i>Manage Accounts</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- Main content -->
                <div class="col-12 col-xxl-9 col-lg-8 main-content pt-4 px-5">
                    <div class="row g-0">
                        <div class="col-xxl-7 col-lg-12 feed-container">
                            <div id="loading" style="display: none;" class="text-center">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <?php include 'filter_announcements.php'; ?>
                        </div>

                        <div class="col-lg-5 announcement-card d-none d-xxl-block">
                            <?php
                            require_once '../login/dbh.inc.php';

                            try {
                                $query = "SELECT a.*, b.first_name, b.last_name 
                                FROM announcement a 
                                JOIN admin b ON a.admin_id = b.admin_id 
                                ORDER BY a.updated_at DESC 
                                LIMIT 3";


                                $stmt = $pdo->prepare($query);
                                $stmt->execute();

                                $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                                <div class="sticky-recent-post">
                                    <div class="filter">
                                        <div class="card latest-card p-2">
                                            <div class="card-body">
                                                <p class="card-title mb-3">RECENT POSTS</p>
                                                <div class="posts">
                                                    <?php
                                                    if ($announcements) {
                                                        foreach ($announcements as $row) {
                                                            $id = $row['announcement_id'];
                                                            $title = $row['title'];
                                                            $image = $row['image'];
                                                            $admin_first_name = $row['first_name'];
                                                            $admin_last_name = $row['last_name'];
                                                            $admin_name =  $admin_first_name . ' ' . $admin_last_name;
                                                    ?>
                                                            <div class="d-flex flex-column recent mb-2">
                                                                <div class="row">
                                                                    <div class="col-md-8 recent-profile-container">
                                                                        <div class="recent-container d-flex">
                                                                            <img class="profile-picture" src="../admin/img/test pic.jpg" alt="">
                                                                            <p class="mt-1 ms-2"><?php echo htmlspecialchars($admin_name) ?></p>
                                                                        </div>

                                                                        <div class="title-container mt-0">
                                                                            <a style="color:black; text-decoration: none;" href="try.php?id=<?php echo $id; ?>"><?php echo htmlspecialchars($title); ?></a>
                                                                        </div>

                                                                    </div>

                                                                    <div class="col-md-4 post-img">
                                                                        <div class="post-img-container">
                                                                            <img class="post-image" src="uploads/<?php echo htmlspecialchars($image); ?>" alt="Post Image" class="img-fluid">
                                                                        </div>
                                                                    </div>
                                                                </div>


                                                            </div>
                                                            <hr>
                                                    <?php
                                                        }
                                                    } else {
                                                        echo '<p class="text-center" >No announcements found.</p>';
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            } catch (PDOException $e) {
                                // Handle any errors that occur during query execution
                                echo "Error: " . htmlspecialchars($e->getMessage());
                            }
                            ?>

                        </div>
                    </div>
                </div>

                <!-- Right sidebar with filters -->
                <div class="col-md-3 d-none d-md-block">
                    <div class="sticky-sidebar pt-5">
                        <div class="filter">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="text-center card-title">Announcements Filter</h5>
                                    <form class="filtered_option d-flex flex-column" id="filterForm">
                                        <label>Choose Department</label>
                                        <div class="checkbox-group mb-3">
                                            <label><input type="checkbox" name="department_filter[]" value="CICS"> CICS</label><br>
                                            <label><input type="checkbox" name="department_filter[]" value="CABE"> CABE</label><br>
                                            <label><input type="checkbox" name="department_filter[]" value="CAS"> CAS</label><br>
                                            <label><input type="checkbox" name="department_filter[]" value="CIT"> CIT</label><br>
                                            <label><input type="checkbox" name="department_filter[]" value="CTE"> CTE</label><br>
                                            <label><input type="checkbox" name="department_filter[]" value="CE"> CE</label><br>
                                        </div>

                                        <label>Select Year Level</label>
                                        <div class="checkbox-group mb-3">
                                            <label><input type="checkbox" name="year_level[]" value="1st Year"> 1st Year</label><br>
                                            <label><input type="checkbox" name="year_level[]" value="2nd Year"> 2nd Year</label><br>
                                            <label><input type="checkbox" name="year_level[]" value="3rd Year"> 3rd Year</label><br>
                                            <label><input type="checkbox" name="year_level[]" value="4th Year"> 4th Year</label><br>
                                        </div>

                                        <label>Courses</label>
                                        <div class="checkbox-group">
                                            <label><input type="checkbox" id="BSBA" name="course[]" value="BSBA" <?php if (in_array('BSBA', $selected_courses)) echo 'checked'; ?>>Bachelor of Science in Business Accounting</label>
                                            <label><input type="checkbox" id="BSMA" name="course[]" value="BSMA" <?php if (in_array('BSMA', $selected_courses)) echo 'checked'; ?>>Bachelor of Science in Management Accounting</label>
                                            <label><input type="checkbox" id="BSP" name="course[]" value="BSP" <?php if (in_array('BSP', $selected_courses)) echo 'checked'; ?>>Bachelor of Science in Psychology</label>
                                            <label><input type="checkbox" id="BAC" name="course[]" value="BAC" <?php if (in_array('BAC', $selected_courses)) echo 'checked'; ?>>Bachelor of Arts in Communication</label>
                                            <label><input type="checkbox" id="BSIE" name="course[]" value="BSIE" <?php if (in_array('BSIE', $selected_courses)) echo 'checked'; ?>>Bachelor of Science in Industrial Engineering</label>
                                            <label><input type="checkbox" id="BSIT-CE" name="course[]" value="BSIT-CE" <?php if (in_array('BSIT-CE', $selected_courses)) echo 'checked'; ?>>Bachelor of Industrial Technology - Computer Technology</label>
                                            <label><input type="checkbox" id="BSIT-Electrical" name="course[]" value="BSIT-Electrical" <?php if (in_array('BSIT-Electrical', $selected_courses)) echo 'checked'; ?>>Bachelor of Industrial Technology - Electrical Technology</label>
                                            <label><input type="checkbox" id="BSIT-Electronic" name="course[]" value="BSIT-Electronic" <?php if (in_array('BSIT-Electronic', $selected_courses)) echo 'checked'; ?>>Bachelor of Industrial Technology - Electronics Technology</label>
                                            <label><input type="checkbox" id="BSIT-ICT" name="course[]" value="BSIT-ICT" <?php if (in_array('BSIT-ICT', $selected_courses)) echo 'checked'; ?>>Bachelor of Industrial Technology - Instrumentation and Control Technology</label>
                                            <label><input type="checkbox" id="BSIT" name="course[]" value="BSIT" <?php if (in_array('BSIT', $selected_courses)) echo 'checked'; ?>>Bachelor of Science in Information Technology</label>
                                            <label><input type="checkbox" id="BSE" name="course[]" value="BSE" <?php if (in_array('BSE', $selected_courses)) echo 'checked'; ?>>Bachelor of Secondary Education</label>
                                        </div>

                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                                            <button type="reset" class="btn btn-secondary">Clear Filters</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php require 'features/changePassMainPage.html'; ?>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.querySelector('#filterForm');
            const searchForm = document.querySelector('#searchForm');
            const searchInput = searchForm.querySelector('input[name="search"]');
            const loadingIndicator = document.getElementById('loading');
            const feedContainer = document.querySelector('.feed-container');

            function fetchAnnouncements(form) {
                loadingIndicator.style.display = 'block';
                feedContainer.style.opacity = '0.5';

                const formData = new FormData(form);

                fetch('filter_announcements.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        feedContainer.innerHTML = data;
                        feedContainer.style.opacity = '1';
                        loadingIndicator.style.display = 'none';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        loadingIndicator.style.display = 'none';
                        feedContainer.style.opacity = '1';
                    });
            }

            // Handle filter form submit
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                fetchAnnouncements(this);
            });

            // Handle search form submit
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                fetchAnnouncements(this);
            });

            // Handle reset button on filter form
            filterForm.addEventListener('reset', function(e) {
                setTimeout(() => {
                    searchInput.value = ''; // Clear the search input
                    fetchAnnouncements(filterForm);
                }, 10);
            });
        });
    </script>
    <script src="js/admin.js"></script>

    <?php include '../cdn/body.html'; ?>
</body>

</html>