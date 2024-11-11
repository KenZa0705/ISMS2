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
    <title>Create Announcement</title>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- head CDN links -->
    <?php include '../../cdn/head.html'; ?>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/create.css">
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

                <a class="btn nav-bottom-btn active" href="create.php">
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
                <?php include '../../cdn/sidebar.php'; ?>

                <!-- main content -->
                <div class="col-md-6 pt-5 px-5">
                    <h3 class="text-center"><b>Create Announcement</b></h3>
                    <div class="form-container d-flex justify-content-center">
                        <form action="upload.php" method="POST" enctype="multipart/form-data">
                            <input type="text" id="admin_id" name="admin_id" value="<?php echo $admin_id; ?>" style="display: none;">

                            <div class="form-group mb-3">
                                <label for="title">Title</label>
                                <input type="text" class="form-control title py-3 px-3" id="title" name="title" placeholder="Enter title" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="description">Description</label>
                                <textarea class="form-control custom-class py-3 px-3" id="description" name="description" rows="5" placeholder="Enter description" required style="border-radius: 20px;"></textarea>
                            </div>


                            <div class="modal-container d-flex justify-content-between">
                                <!-- Button to trigger Tags modal -->
                                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#tagsModal">
                                    Select Tag
                                </button>

                                <!-- Tags Modal -->
                                <div class="modal fade" id="tagsModal" tabindex="-1" aria-labelledby="tagsModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="tagsModalLabel">Tags</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <!-- Year Levels -->
                                                <h6>Year Levels</h6>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="1stYear" name="year_level[]" value="1st Year">
                                                    <label for="1stYear" class="form-check-label">1st Year</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="2ndYear" name="year_level[]" value="2nd Year">
                                                    <label for="2ndYear" class="form-check-label">2nd Year</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="3rdYear" name="year_level[]" value="3rd Year">
                                                    <label for="3rdYear" class="form-check-label">3rd Year</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="4thYear" name="year_level[]" value="4th Year">
                                                    <label for="4thYear" class="form-check-label">4th Year</label>
                                                </div>

                                                <!-- Departments -->
                                                <h6 class="mt-3">Departments</h6>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="CICS" name="department[]" value="CICS">
                                                    <label for="CICS" class="form-check-label">CICS</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="CABE" name="department[]" value="CABE">
                                                    <label for="CABE" class="form-check-label">CABE</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="CAS" name="department[]" value="CAS">
                                                    <label for="CAS" class="form-check-label">CAS</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="CIT" name="department[]" value="CIT">
                                                    <label for="CIT" class="form-check-label">CIT</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="CTE" name="department[]" value="CTE">
                                                    <label for="CTE" class="form-check-label">CTE</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="CE" name="department[]" value="CE">
                                                    <label for="CE" class="form-check-label">CE</label>
                                                </div>

                                                <!-- Courses -->
                                                <h6 class="mt-3">Courses</h6>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="BSBA" name="course[]" value="BSBA">
                                                    <label for="BSBA" class="form-check-label">Bachelor of Science in Business Accounting</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="BSMA" name="course[]" value="BSMA">
                                                    <label for="BSMA" class="form-check-label">Bachelor of Science in Management Accounting</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="BSP" name="course[]" value="BSP">
                                                    <label for="BSP" class="form-check-label">Bachelor of Science in Psychology</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="BAC" name="course[]" value="BAC">
                                                    <label for="BAC" class="form-check-label">Bachelor of Arts in Communication</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="BSIE" name="course[]" value="BSIE">
                                                    <label for="BSIE" class="form-check-label">Bachelor of Science in Industrial Engineering</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="BSIT-CE" name="course[]" value="BSIT-CE">
                                                    <label for="BSIT-CE" class="form-check-label">Bachelor of Industrial Technology - Computer Technology</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="BSIT-Electrical" name="course[]" value="BSIT-Electrical">
                                                    <label for="BSIT-Electrical" class="form-check-label">Bachelor of Industrial Technology - Electrical Technology</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="BSIT-Electronic" name="course[]" value="BSIT-Electronic">
                                                    <label for="BSIT-Electronic" class="form-check-label">Bachelor of Industrial Technology - Electronics Technology</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="BSIT-ICT" name="course[]" value="BSIT-ICT">
                                                    <label for="BSIT-ICT" class="form-check-label">Bachelor of Industrial Technology - Instrumentation and Control Technology</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="BSIT" name="course[]" value="BSIT">
                                                    <label for="BSIT" class="form-check-label">Bachelor of Science in Information Technology</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="BSE" name="course[]" value="BSE">
                                                    <label for="BSE" class="form-check-label">Bachelor of Secondary Education</label>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <div class="upload-image-container d-flex flex-column align-items-center justify-content-center bg-white image-preview-container">
                                    <div class="d-flex">
                                        <p id="upload-text" class="mt-3">Upload Photo</p>
                                        <input type="file" class="form-control-file" id="image" name="image" style="display: none;" onchange="imagePreview()">
                                        <button class="btn btn-light" id="file-upload-btn">
                                            <i class="bi bi-upload"></i>
                                        </button>
                                        <img class="img-fluid" id="image-preview" src="#" alt="Image Preview" style="display: none; max-width: 100%; position: relative; z-index: 1;">
                                    </div>
                                    <div class="blur-background" style="display: none;"></div>
                                    <i id="delete-icon" class="bi bi-trash" style="position: absolute; top: 5px; right: 5px; display: none; cursor: pointer;" onclick="deleteImage()"></i>
                                </div>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="sendSms" name="sendSms" value="1">
                                <label class="form-check-label" for="sendSms">
                                    Send SMS notifications
                                </label>
                            </div>

                            <div id="smsInfo" style="display: none;" class="alert alert-info mb-3">
                                Estimated number of SMS recipients: <span id="recipientCount">0</span>
                            </div>

                            <div class="button-container d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary px-3 mb-3" id="submitBtn">Post</button>
                            </div>
                        </form>
                    </div>

                </div>
                
            </div>
        </div>
    </main>
    <!-- Body CDN links -->
    <?php include '../../cdn/body.html'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/create.js"></script>
</body>

</html>