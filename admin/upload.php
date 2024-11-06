<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set("error_log", "error.log");
error_reporting(E_ALL);

// Database configuration
require_once '../login/dbh.inc.php';
require 'config.php';

// Function to log SMS status in the database
function logSmsStatus($pdo, $announcement_id, $student_id, $status) {
    $query = "INSERT INTO sms_log (announcement_id, student_id, status) VALUES (:announcement_id, :student_id, :status)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':announcement_id' => $announcement_id,
        ':student_id' => $student_id,
        ':status' => $status
    ]);
}

// Function to get the corresponding ID from a table based on a name field
function getIdByName($pdo, $table, $column, $value, $id) {
    $sql = "SELECT $id FROM $table WHERE $column = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$value]);
    $result = $stmt->fetchColumn();
    error_log("getIdByName for $table: Column $column, Value $value, Result: $result");
    return (int) $result; 
}


//  Function to get student contact information for an announcement
function getStudentsForAnnouncement($pdo, $year_levels, $departments, $courses) {
    // Convert descriptive names to their corresponding IDs for filtering
    $year_level_ids = [];
    foreach ($year_levels as $year_level_name) {
        $year_level_id = getIdByName($pdo, 'year_level', 'year_level', $year_level_name, 'year_level_id');
        if ($year_level_id !== null) {
            $year_level_ids[] = $year_level_id; // Store the integer ID
        }
    }

    $department_ids = [];
    foreach ($departments as $department_name) {
        $department_id = getIdByName($pdo, 'department', 'department_name', $department_name, 'department_id');
        if ($department_id !== null) {
            $department_ids[] = $department_id; // Store the integer ID
        }
    }

    $course_ids = [];
    foreach ($courses as $course_name) {
        $course_id = getIdByName($pdo, 'course', 'course_name', $course_name, 'course_id');
        if ($course_id !== null) {
            $course_ids[] = $course_id; // Store the integer ID
        }
    }

    // Construct the query using IN clauses for filtering
    $query = "SELECT DISTINCT s.student_id, s.contact_number
              FROM student s
              WHERE s.year_level_id IN (" . implode(',', array_fill(0, count($year_level_ids), '?')) . ")
              AND s.department_id IN (" . implode(',', array_fill(0, count($department_ids), '?')) . ")
              AND s.course_id IN (" . implode(',', array_fill(0, count($course_ids), '?')) . ")";

    // Prepare the statement
    $stmt = $pdo->prepare($query);

    // Bind the values for year_levels, departments, and courses
    $params = array_merge($year_level_ids, $department_ids, $course_ids);
    $stmt->execute($params);

    // Fetch and return the results
    $output = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log('Result: ' . print_r($output, true)); 

    return $output;
}



// Replaces sendSmsToStudents function, using a working sendMessage function
function sendMessage($contact_number, $message) {
    $infobip_url = "https://wg43qy.api.infobip.com/sms/2/text/advanced";
    $api_key = INFOPB_API_KEY;

    $data = [
        "messages" => [
            [
                "from" => "447491163443",
                "destinations" => [
                    ["to" => $contact_number]
                ],
                "text" => $message
            ]
        ]
    ];

    $headers = [
        "Authorization: App $api_key",
        "Content-Type: application/json",
        "Accept: application/json"
    ];

    $options = [
        'http' => [
            'header'  => implode("\r\n", $headers),
            'method'  => 'POST',
            'content' => json_encode($data),
        ],
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($infobip_url, false, $context);
    if ($result === FALSE) {
        error_log("Failed to send SMS to $contact_number");

        return false;
    }
    error_log("Sent SMS to $contact_number: $result");
    return json_decode($result, true);

}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if an image was uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $description = htmlspecialchars($_POST['description']);
        $image = $_FILES['image'];
        $title = htmlspecialchars($_POST['title']);
        $admin_id = $_POST['admin_id'];

        // Handle year levels, departments, and courses
        $year_levels = isset($_POST['year_level']) ? $_POST['year_level'] : [];
        $departments = isset($_POST['department']) ? $_POST['department'] : [];
        $courses = isset($_POST['course']) ? $_POST['course'] : [];

        // Check if admin_id is a valid integer
        if (!empty($admin_id) && filter_var($admin_id, FILTER_VALIDATE_INT)) {
            // Define the upload directory
            $uploadDir = 'uploads/';
            // Create the directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Get the file extension
            $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
            $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];

            // Check if the file extension is allowed
            if (in_array(strtolower($ext), $allowedExt)) {
                // Create a unique filename
                $filename = uniqid('', true) . '.' . $ext;
                $uploadFilePath = $uploadDir . $filename;

                // Move the uploaded file to the upload directory
                if (move_uploaded_file($image['tmp_name'], $uploadFilePath)) {
                    $pdo->beginTransaction();
                    try {
                        // Insert the file details into the database using PDO
                        $stmt = $pdo->prepare("INSERT INTO announcement (image, description, title, admin_id) VALUES (:filename, :description, :title, :admin_id)");
                        $stmt->bindParam(':filename', $filename);
                        $stmt->bindParam(':description', $description);
                        $stmt->bindParam(':title', $title);
                        $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT); 

                        if ($stmt->execute()) {
                            // Get the ID of the last inserted announcement
                            $announcement_id = $pdo->lastInsertId();

                            // Check if SMS notifications should be sent
                            if (isset($_POST['sendSms'])) {
                                echo "Preparing to send SMS notifications..."; // Debug statement
                                $message = substr($description, 0, 100);
                                $students = getStudentsForAnnouncement($pdo, $year_levels, $departments, $courses);
                                echo "Students retrieved for announcement: " . count($students); // Debug statement
                                error_log("Students fetched: " . print_r($students, true));

                                foreach ($students as $student) {
                                    $contact_number = $student['contact_number'];
                                    $content = $title . "\n" . $message;
                                    if (sendMessage($contact_number, $content)) {
                                        logSmsStatus($pdo, $announcement_id, $student['student_id'], 'SENT');
                                    } else {
                                        logSmsStatus($pdo, $announcement_id, $student['student_id'], 'FAILED');
                                    }
                                }
                            } else {
                                error_log("sendSms not set in POST.");
                            }

                            // Insert into the `announcement_year_level` junction table
                            foreach ($year_levels as $year_level_name) {
                                $year_level_id = getIdByName($pdo, 'year_level', 'year_level', $year_level_name, 'year_level_id');
                                if ($year_level_id) {
                                    $sql = "INSERT INTO announcement_year_level (announcement_id, year_level_id) VALUES (?, ?)";
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute([$announcement_id, $year_level_id]);
                                }
                            }

                            // Insert into the `announcement_department` junction table
                            foreach ($departments as $department_name) {
                                $department_id = getIdByName($pdo, 'department', 'department_name', $department_name, 'department_id');
                                if ($department_id) {
                                    $sql = "INSERT INTO announcement_department (announcement_id, department_id) VALUES (?, ?)";
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute([$announcement_id, $department_id]);
                                }
                            }

                            // Insert into the `announcement_course` junction table
                            foreach ($courses as $course_name) {
                                $course_id = getIdByName($pdo, 'course', 'course_name', $course_name, 'course_id');
                                if ($course_id) {
                                    $sql = "INSERT INTO announcement_course (announcement_id, course_id) VALUES (?, ?)";
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute([$announcement_id, $course_id]);
                                }
                            }
                            $pdo->commit();
                            echo "<script>
                                    alert('Announcement posted successfully!');
                                    setTimeout(function() {
                                        window.location.href = 'admin.php';
                                    }, 5000);
                                </script>";
                        } else {
                            $pdo->rollBack();
                            echo "Failed to save details to database.";
                        }
                    } catch (PDOException $e) {
                        $pdo->rollBack();

                        echo "Database error: " . $e->getMessage();
                    }
                } else {
                    echo "Failed to move uploaded file.";
                }
            } else {
                echo "Invalid file extension.";
            }
        } else {
            echo "Invalid admin ID.";
        }
    } else {
        echo "No file uploaded or there was an upload error.";
    }
} else {
    echo "Invalid request.";
}
