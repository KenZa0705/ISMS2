<?php
// Include database connection
require_once '../../login/dbh.inc.php';
session_start();

// Verify user session
if (!isset($_SESSION['user'])) {
    header("Location: ../../login/login.php");
    exit();
}

// Check if student ID is set
if (isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];

    // Fetch current student data
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = :student_id");
    $stmt->execute(['student_id' => $student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        echo "Student not found.";
        exit();
    }
} else {
    echo "No student ID provided.";
    exit();
}

// Update student data if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $s_first_name = $_POST['firstName'];
    $s_last_name = $_POST['lastName'];
    $s_email = $_POST['email'];
    $s_contact_number = $_POST['contactNumber'];
    $s_year_level_id = $_POST['yearLevel'];

    // Update student record in the database
    $stmt = $pdo->prepare("UPDATE students SET first_name = :first_name, last_name = :last_name, email = :email, contact_number = :contact_number, year_level_id = :year_level_id WHERE student_id = :student_id");
    $stmt->execute([
        'first_name' => $s_first_name,
        'last_name' => $s_last_name,
        'email' => $s_email,
        'contact_number' => $s_contact_number,
        'year_level_id' => $s_year_level_id,
        'student_id' => $student_id
    ]);

    // Redirect or notify after successful update
    echo "Student record updated successfully.";
    header("Location: manage_student.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Student</title>
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
                    <h2 class="text-center">Edit Student Information</h2>
                    <div class="form-container d-flex justify-content-center">
                        <form method="POST">
                            <div class="form-group mb-3">
                                <label>First Name:</label>
                                <input type="text" name="firstName" value="<?php echo htmlspecialchars($student['first_name']); ?>" placeholder="<?php echo htmlspecialchars($student['first_name']); ?>" required><br>
                            </div>
                            <div class="form-group mb-3">
                                <label>Last Name:</label>
                                <input type="text" name="lastName" value="<?php echo htmlspecialchars($student['last_name']); ?>" placeholder="<?php echo htmlspecialchars($student['last_name']); ?>" required><br>
                            </div>
                            <div class="form-group mb-3">
                                <label>Email:</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" placeholder="<?php echo htmlspecialchars($student['email']); ?>" required><br>
                            </div>
                            <div class="form-group mb-3">
                                <label>Contact Number:</label>
                                <input type="text" name="contactNumber" value="<?php echo htmlspecialchars($student['contact_number']); ?>" placeholder="<?php echo htmlspecialchars($student['contact_number']); ?>" required><br>
                            </div>
                            <div class="form-group mb-3">
                                <label>Year Level:</label>
                                <input type="text" name="yearLevel" value="<?php echo htmlspecialchars($student['year_level_id']); ?>" placeholder="<?php echo htmlspecialchars($student['year_level_id']); ?>" required><br>
                            </div>
                            <div class="button-container d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary px-3">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
