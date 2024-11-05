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
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="modals.css">
</head>
<body>
    <header>
        <?php include '../cdn/navbar.php' ?>
        <nav class="navbar nav-bottom fixed-bottom d-block d-md-none mt-5">
            <div class="container-fluid justify-content-around">
                <a href="admin.php" class="btn nav-bottom-btn active">
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

                <a class="btn nav-bottom-btn" href="#">
                    <i class="bi bi-clipboard"></i>
                    <span class="icon-label">Logs</span>
                </a>

                <a class="btn nav-bottom-btn" href="manage_student.php">
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
                <div class="col-md-3 d-none d-md-block">
                    <!-- Your existing sidebar code -->
                    <div class="sticky-sidebar pt-5">
                        <div class="sidebar">
                            <div class="card">
                                <div class="card-body d-flex flex-column">
                                    <a href="admin.php" class="btn active mb-3"><i class="bi bi-house"></i> Home</a>
                                    <a class="btn mb-3" href="create.php"><i class="bi bi-megaphone"></i> Create Announcement</a>
                                    <a class="btn mb-3" href="manage.php"><i class="bi bi-kanban"></i> Manage Post</a>
                                    <a class="btn mb-3" href="#"><i class="bi bi-clipboard"></i> Logs</a>
                                    <a class="btn" href="manage_student.php"><i class="bi bi-person-plus"></i> Manage Student Account</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main content -->
                <div class="col-md-6 main-content pt-5 px-5">
                    <div class="feed-container">
                        <div id="loading" style="display: none;" class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <?php include 'filter_announcements.php'; ?>
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
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.querySelector('#filterForm');
        const loadingIndicator = document.getElementById('loading');
        const feedContainer = document.querySelector('.feed-container');
        
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            loadingIndicator.style.display = 'block';
            feedContainer.style.opacity = '0.5';
            
            const formData = new FormData(this);
            
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
        });

        // Handle reset button
        filterForm.addEventListener('reset', function(e) {
            setTimeout(() => {
                filterForm.dispatchEvent(new Event('submit'));
            }, 10);
        });
    });
    </script>
    <script src="admin.js"></script>

    <?php include '../cdn/body.html'; ?>
</body>
</html>