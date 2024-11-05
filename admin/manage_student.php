<?php
require_once '../login/dbh.inc.php'; // DATABASE CONNECTION
require '../login/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $s_first_name = $_POST['firstName'];
    $s_last_name = $_POST['lastName'];
    $s_email = $_POST['email'];
    $s_contact_number = $_POST['contactNumber'];

    $s_year_level_id = $pdo->prepare("SELECT year_level_id FROM year_level WHERE year_level = :ylevel");
    $s_year_level_id->execute([':ylevel' => $_POST['yearLevel']]);
    $s_year = (int)$s_year_level_id->fetchColumn();

    $s_dept_id = $pdo->prepare("SELECT department_id FROM department WHERE department_name = :dname");
    $s_dept_id->execute([':dname' => $_POST['department']]);
    $s_dept = (int)$s_dept_id->fetchColumn();

    $s_course_id = $pdo->prepare("SELECT course_id FROM course WHERE course_name = :cname");
    $s_course_id->execute([':cname' => $_POST['course']]);
    $s_course = (int)$s_course_id->fetchColumn();

    if ($s_year && $s_dept && $s_course) {
        addNewStudent($s_first_name, $s_last_name, $s_email, $s_contact_number, $s_year, $s_dept, $s_course);
    } else {
        echo "<script>alert('Error: One or more of the selected values are invalid.');</script>";
    }
}

function addNewStudent($s_first_name, $s_last_name, $s_email, $s_contact_number, $s_year, $s_dept, $s_course)
{
    global $pdo;

    // Check if email already exists in the student table
    $checkEmailQuery = "SELECT COUNT(*) FROM student WHERE email = :email";
    $checkStmt = $pdo->prepare($checkEmailQuery);
    $checkStmt->bindParam(':email', $s_email);
    $checkStmt->execute();

    $emailExists = $checkStmt->fetchColumn();

    if ($emailExists > 0) {
        echo "<script>alert('Error: This email address is already registered.');</script>";
        return;
    } else {
        // Check if email exists in the admin table
        $checkEmailQuery = "SELECT COUNT(*) FROM admin WHERE email = :email";
        $checkStmt = $pdo->prepare($checkEmailQuery);
        $checkStmt->bindParam(':email', $s_email);
        $checkStmt->execute();

        $emailExists = $checkStmt->fetchColumn();
        if ($emailExists > 0) {
            echo "<script>alert('Error: This email address is already registered.');</script>";
            return;
        }
    }

    // Generate a password using first name and last 4 digits of the contact number
    $formattedFirstName = ucfirst(strtolower($s_first_name));
    $lastFourDigits = substr($s_contact_number, -4); // Extract last four digits
    $password = $formattedFirstName . $lastFourDigits;
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepare the SQL statement with the generated password
    $sql = "INSERT INTO student (password, first_name, last_name, email, contact_number, year_level_id, department_id, course_id) 
            VALUES (:password, :first_name, :last_name, :email, :contact_number, :ylevel, :dept, :course)";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':first_name', $s_first_name);
    $stmt->bindParam(':last_name', $s_last_name);
    $stmt->bindParam(':email', $s_email);
    $stmt->bindParam(':contact_number', $s_contact_number);
    $stmt->bindParam(':ylevel', $s_year);
    $stmt->bindParam(':dept', $s_dept);
    $stmt->bindParam(':course', $s_course);

    if ($stmt->execute()) {
        // Send email with password setup link
        $mail = new PHPMailer(true);
        // Server settings
        $mail->isSMTP();                                 
        $mail->Host = 'smtp.gmail.com';                  
        $mail->SMTPAuth = true;                          
        $mail->Username = 'ranonline1219@gmail.com';      
        $mail->Password = 'cavv jhhh onzy rwiu';         
        $mail->SMTPSecure = 'tls';                       
        $mail->Port = 587;                               

        // Recipients
        $mail->setFrom('ranonline1219@gmail.com', 'ISMS - BSU Announcement Portal');
        $mail->addAddress($s_email);                     

        // Content
        $mail->isHTML(true);                             
        $mail->Subject = 'Your Account for the ISMS Portal was created successfully';
        $setupLink = "localhost/I-SMS/login/login.php";
        $mail->Body = "Your account was created successfully. <br> 
                        You can login using your email address and your password will be your first name + the last four digits of your contact number. <br>
                        Example: if first name is 'John' and contact number is '09635242249', password will be 'John2249' <br>
                        Note: The first letter of of your first name is uppercase. 
                        Log in to the website by clicking on the link below.<br>
                        <a href='" . $setupLink . "'>Login Here</a>";

        $mail->send();
        echo "<script>alert('New record created successfully.');</script>";
    } else {
        echo "<script>alert('Error: Could not add student.');</script>";
    }
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>ISMS Portal</title>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- head CDN links -->
    <?php include '../cdn/head.html'; ?>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="modals.css">
</head>

<body>
    <header>
        <?php include '../cdn/navbar.php' ?>

        <nav class="navbar nav-bottom fixed-bottom d-block d-md-none mt-5">
            <div class="container-fluid justify-content-around">
                <a href="admin.php" class="btn nav-bottom-btn">
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

                <a class="btn nav-bottom-btn active" href="manage_student.php">
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
                                    <a href="admin.php" class="btn mb-3"><i class="bi bi-house"></i> Home</a>
                                    <a class="btn mb-3" href="create.php"><i class="bi bi-megaphone"></i> Create Announcement</a>
                                    <a class="btn mb-3" href="manage.php"><i class="bi bi-kanban"></i> Manage Post</a>
                                    <a class="btn mb-3" href="#"><i class="bi bi-clipboard"></i> Logs</a>
                                    <a class="btn active mb-3" href="#"><i class="bi bi-person-plus"></i> Manage Student Account</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- main content -->
                <div class="col-md-8 pt-5 px-5">
                    <div class="feed-container">
                        <button class="btn btn-primary" id="addNewStudent" data-bs-toggle="modal" data-bs-target="#studentModal">Add new student</button>

                        <?php
                        require_once '../login/dbh.inc.php';

                        try {
                            $query = "SELECT s.*, yl.year_level, d.department_name, c.course_name 
                                    FROM student s
                                    JOIN year_level yl ON s.year_level_id = yl.year_level_id
                                    JOIN department d ON d.department_id = s.department_id
                                    JOIN course c ON c.course_id = s.course_id
                                    ORDER BY last_name ASC";

                            $stmt = $pdo->prepare($query);
                            $stmt->execute();
                            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                            <?php if (count($students) > 0): ?>
                                <table class="table table-light table-hover">
                                    <thead class="table table-primary">
                                        <tr>
                                            <th>Student Number</th>
                                            <th>Full Name</th>
                                            <th>Email</th>
                                            <th>Contact Number</th>
                                            <th>Year Level</th>
                                            <th>Department</th>
                                            <th>Course</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $row):
                                            $student_id = $row['student_id'];
                                            $fname = $row['first_name'];
                                            $lname = $row['last_name'];
                                            $email = $row['email'];
                                            $contact = $row['contact_number'];
                                            $year_level = $row['year_level'];
                                            $department = $row['department_name'];
                                            $course = $row['course_name'];
                                            $student_name = $fname . ' ' . $lname;
                                        ?>
                                            <tr>
                                                <td><?= $student_id ?></td>
                                                <td><?= $student_name ?></td>
                                                <td><?= $email ?></td>
                                                <td><?= $contact ?></td>
                                                <td><?= $year_level ?></td>
                                                <td><?= $department ?></td>
                                                <td><?= $course ?></td>
                                                <td>
                                                    <div class='dropdown ms-auto'>
                                                        <span id='dropdownMenuButton <?= $student_id ?>' data-bs-toggle='dropdown' aria-expanded='false'>
                                                            <i class='bi bi-three-dots'></i>
                                                        </span>
                                                        <ul class='dropdown-menu' aria-labelledby='dropdownMenuButton <?= $student_id ?>'>
                                                            <li><a class='dropdown-item d-none' href='edit_announcement.php?id=<?= $student_id ?>'>Edit</a></li>
                                                            <li>
                                                                <a class='dropdown-item text-danger' href='#'
                                                                    data-bs-toggle='modal'
                                                                    data-bs-target='#deleteStudent'
                                                                    data-student-id='<?= $student_id ?>'>Delete</a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No students found.</p>
                            <?php endif; ?>

                        <?php
                        } catch (PDOException $e) {
                            echo "Error: " . $e->getMessage();
                        }
                        ?>


                    </div>
                </div>

                <!-- Form Modal -->
                <div class="modal fade" id="studentModal" tabindex="-1" aria-labelledby="studentModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="studentModalLabel">Add New Student</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="addStudentForm" method="POST" action="">
                                    <div class="form-group">
                                        <label for="firstName">First Name</label>
                                        <input type="text" class="form-control" id="firstName" name="firstName" placeholder="Enter first name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="lastName">Last Name</label>
                                        <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Enter last name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Email address</label>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="contactNumber">Contact Number</label>
                                        <input type="text" class="form-control" id="contactNumber" name="contactNumber" placeholder="Enter contact number" required>
                                        <span id="errorMsg" style="color:red; display:none;">Invalid contact number</span>

                                    </div>
                                    <div class="form-group">
                                        <label for="yearLevel">Year Level</label>
                                        <select id="yearLevel" name="yearLevel" class="form-select">
                                            <option value="1st Year">1st Year</option>
                                            <option value="2nd Year">2nd Year</option>
                                            <option value="3rd Year">3rd Year</option>
                                            <option value="4th Year">4th Year</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="department">Department</label>
                                        <select id="department" name="department" class="form-select">
                                            <option value="CICS">CICS</option>
                                            <option value="CABE">CABE</option>
                                            <option value="CAS">CAS</option>
                                            <option value="CE">CE</option>
                                            <option value="CIT">CIT</option>
                                            <option value="CTE">CTE</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="course">Course</label>
                                        <select id="course" name="course" class="form-select">
                                            <option value="BSBA">Bachelor of Science Business Administration</option>
                                            <option value="BSMA">Bachelor of Science in Management Accounting</option>
                                            <option value="BSP">Bachelor of Science in Psychology</option>
                                            <option value="BAC">Bachelor of Arts in Communication</option>
                                            <option value="BSIE">Bachelor of Science in Industrial Engineering</option>
                                            <option value="BSIT-CE">Bachelor of Industrial Technology - Computer Technology</option>
                                            <option value="BSIT-Electrical">Bachelor of Industrial Technology - Electrical Technology</option>
                                            <option value="BSIT-Electronic">Bachelor of Industrial Technology - Electronics Technology</option>
                                            <option value="BSIT-ICT">Bachelor of Industrial Technology - Instrumentation and Control Technology</option>
                                            <option value="BSIT">Bachelor of Science in Information Technology</option>
                                            <option value="BSE">Bachelor of Secondary Education</option>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary" form="addStudentForm">Save Student</button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>

                <script>
                    $(document).ready(function() {
                        $('#addStudentForm').on('submit', function(e) {
                            var contactNumber = $('#contactNumber').val();
                            // Regular expression for validating PH mobile numbers starting with 09 or +639
                            var regex = /^(09|\+639)\d{9}$/;

                            if (!regex.test(contactNumber)) {
                                e.preventDefault(); // Prevent form submission
                                $('#errorMsg').show().text('Invalid contact number');
                            } else {
                                $('#errorMsg').hide(); // Hide error if valid
                            }
                        });
                    });
                </script>


                <!-- Delete Post Modal -->
                <div class="modal fade" id="deleteStudent" tabindex="-1" aria-labelledby="deleteStudent" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-sm">
                        <div class="modal-content custom" style="border-radius: 15px;">
                            <div class="modal-header pb-1" style="border: none">
                                <h1 class="modal-title fs-5" id="exampleModalLabel">Delete Student Data?</h1>
                                <button type="button" class="btn-close delete-modal-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body py-0" style="border: none;">
                                <p style="font-size: 15px;">Once you proceed, this can't be restored.</p>
                            </div>
                            <div class="modal-footer pt-0" style="border: none;">
                                <button type="button" class="btn go-back-btn" data-bs-dismiss="modal">Go Back</button>
                                <button type="button" class="btn delete-btn" id="confirm-delete-student-btn">Confirm Delete</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- after deletion modal -->
                <div class="modal fade" id="studentDelete" tabindex="-1" aria-labelledby="student-deleted" aria-hidden="true">
                    <div class="modal-dialog modal-md">
                        <div class="modal-content delete-message">
                            <div class="modal-header" style="border: none;">
                                <p class="modal-title" id="exampleModalLabel">Student record was deleted succesfully.</p>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- offcanvas  -->

                <script src="admin.js"></script>
    </main>
    <!-- Body CDN links -->
    <?php include '../cdn/body.html'; ?>
</body>

</html>