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
    <title>Edit Announcement</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Include your head CDN links -->
    <?php include '../../cdn/head.html'; ?>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/create.css">
</head>

<body>
    <header>
        <?php include '../../cdn/navbar.php'; ?> 
    </header>

    <main>
        <div class="container-fluid pt-5">
            <div class="row g-4">
                <!-- Sidebar -->
                <?php include '../../cdn/sidebar.php'; ?>

                <!-- Main content -->
                <div class="col-md-6 pt-5 px-5">
                    <h3 class="text-center"><b>Edit Student Record</b></h3>

                    <?php
                    require_once '../../login/dbh.inc.php';

                    if (isset($_GET['id'])) {
                        $student_id = $_GET['id'];

                        // Fetch existing announcement data
                        $query = "SELECT * FROM student WHERE student_id = :id";
                        $stmt = $pdo->prepare($query);
                        $stmt->bindParam(':id', $student_id, PDO::PARAM_INT);
                        $stmt->execute();
                        $student = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($student) {
                            $fname = $student['first_name'];
                            $lname = $student['last_name'];
                            $email = $student['email'];
                            $contactNumber = $student['contactNumber'];
                            $department_id = $student['department_id'];
                            $year_level_id = $student['year_level_id'];
                            $course_id = $student['course_id'];

                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

                                // Update the announcement
                                $update_query = "UPDATE student SET first_name = :fname, last_name = :lname, email = :email, contact_number = :cNumber, year_level_id = :year_level, department_id = :department, course_id = :course  WHERE student_id = :id";
                                $stmt = $pdo->prepare($update_query);
                                $stmt->bindParam(':fname', $s_first_name);
                                $stmt->bindParam(':lname', $s_last_name);
                                $stmt->bindParam(':email', $s_email);
                                $stmt->bindParam(':cNumber', $s_contact_number);
                                $stmt->bindParam(':department', $s_dept);
                                $stmt->bindParam(':course', $s_course);
                                $stmt->bindParam(':year_level', $s_year);
                                $stmt->bindParam(':id', $student_id);

                                if ($stmt->execute()) {
                                    echo "<script>
                                            alert('Student record was updated successfully!');
                                            window.location.href = 'manage_student.php';
                                          </script>";
                                } else {
                                    echo "<div class='alert alert-danger'>Error updating student record.</div>";
                                }
                            }
                        } else {
                            echo "<div class='alert alert-danger'>student not found.</div>";
                        }
                    } else {
                        echo "<div class='alert alert-danger'>No student ID provided.</div>";
                    }
                    ?>

                    <!-- Form to edit the student data -->
                    <?php if ($student): ?>
                        <form id="addStudentForm" method="POST" action="">
                            <div class="form-group">
                                <label for="firstName">First Name</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" placeholder="<?php echo $fname ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" placeholder="<?php echo $lname ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email address</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="<?php echo $email ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="contactNumber">Contact Number</label>
                                <input type="text" class="form-control" id="contactNumber" name="contactNumber" placeholder="<?php echo $contactNumber ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="yearLevel">Year Level</label>
                                <select id="yearLevel" name="yearLevel" class="form-select" required>
                                    <option value="1st Year">1st Year</option>
                                    <option value="2nd Year">2nd Year</option>
                                    <option value="3rd Year">3rd Year</option>
                                    <option value="4th Year">4th Year</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="department">Department</label>
                                <select id="department" name="department" class="form-select" required>
                                    <option value="CECS">CECS</option>
                                    <option value="CABE">CABE</option>
                                    <option value="CAS">CAS</option>
                                    <option value="CE">CE</option>
                                    <option value="CIT">CIT</option>
                                    <option value="CTE">CTE</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="course">Course</label>
                                <select id="course" name="course" class="form-select" required>
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
                            <button type="submit" class="btn btn-primary" form="addStudentForm">Update Student Record</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <script src="../js/create.js"></script>
        <script src="../js/edit.js"></script>
    </main>

    <!-- Body CDN links -->
    <?php include '../cdn/body.html'; ?>
</body>

</html>