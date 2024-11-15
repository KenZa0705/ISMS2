<?php
require_once '../../login/dbh.inc.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

$admin_id = $_SESSION['user']['admin_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $file = $_FILES['profile_picture'];
        $fileName = basename($file['name']);
        $uploadDir = '../uploads/';
            // Create the directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
        $filePath = $uploadDir . $fileName;

        // Move the uploaded file to the desired directory
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Update the database with the new profile picture path
            $query = "UPDATE admin SET profile_picture = :profile_picture WHERE admin_id = :admin_id";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['profile_picture' => $fileName, 'admin_id' => $admin_id]);

            // Redirect back to profile page or wherever needed
            header("Location: ../admin.php");
            exit();
        } else {
            echo "Error uploading file.";
        }
    } else {
        echo "No file uploaded or there was an upload error.";
    }
}
