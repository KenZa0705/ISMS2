<?php
require '../login/dbh.inc.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $query = "SELECT * FROM student WHERE token = :token";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':token', $token);
    $stmt->execute();

    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        // Display form for password setup
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
            $newPassword = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $updateQuery = "UPDATE student SET password = :password, token = NULL WHERE student_id = :id";
            $updateStmt = $pdo->prepare($updateQuery);
            $updateStmt->bindParam(':password', $newPassword);
            $updateStmt->bindParam(':id', $student['id']);
            if ($updateStmt->execute()) {
                echo "<script>alert('Password updated successfully.');</script>";
            } else {
                echo "<script>alert('Error: Could not update password.');</script>";
            }
        }
    } else {
        echo "<script>alert('Invalid or expired token.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Up New Password</title>
</head>
<body>
    <form method="POST" action="">
        <label for="password">New Password:</label>
        <input type="password" name="password" id="password" required>
        <button type="submit">Set Password</button>
    </form>
</body>
</html>
