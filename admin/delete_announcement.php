<?php
require_once '../login/dbh.inc.php'; // Database connection

// Check if the announcement ID is set
if (isset($_GET['id'])) {
    $announcement_id = $_GET['id'];
    // Perform the deletion
    try {
        $query = "DELETE FROM announcement WHERE announcement_id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $announcement_id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            echo "<script>
            window.location.href = 'admin.php?deleted=true';
                </script>";
        } else {
            echo "<script>
                alert('There was an error in deleting the announcement.');
                window.location.href = 'admin.php';
                </script>";
        }
    } catch (PDOException $e) {
        echo "<script>
            alert('Error: " . $e->getMessage() . "');
            window.location.href = 'admin.php';
            </script>";
    }
} else {
    echo "<script>
        alert('No announcement ID provided.');
        window.location.href = 'admin.php';
        </script>";
}
