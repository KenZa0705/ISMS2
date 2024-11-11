<?php
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include 'dbh.inc.php';
require '../admin/features/config.php';
require '../admin/features/log.php';
require '../admin/features/functions.php';

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
    $email = $_POST['email'];
    $user_found = false;
    $user_type = '';
    $contact_number = '';

    $stmt_student = $pdo->prepare("SELECT * FROM student WHERE email = :email");
    $stmt_student->execute(['email' => $email]);
    $result_student = $stmt_student->fetch(PDO::FETCH_ASSOC);

    if ($result_student) {
        $user_found = true;
        $user_type = 'student';
        $contact_number = $result_student['contact_number'];
    }

    $stmt_staff = $pdo->prepare("SELECT * FROM admin WHERE email = :email");
    $stmt_staff->execute(['email' => $email]);
    $result_staff = $stmt_staff->fetch(PDO::FETCH_ASSOC);

    if ($result_staff) {
        $user_found = true;
        $user_type = 'admin';
        $contact_number = $result_staff['contact_number'];
    }

    if ($user_found) {
        $otp = rand(100000, 999999); 
        $otp_expiry = gmdate("Y-m-d H:i:s", strtotime('+10 minutes'));

        if ($user_type == 'student') {
            $update_stmt = $pdo->prepare("UPDATE student SET otp = :otp, otp_expiry = :otp_expiry WHERE email = :email");
        } else {
            $update_stmt = $pdo->prepare("UPDATE admin SET otp = :otp, otp_expiry = :otp_expiry WHERE email = :email");
        }

        $update_stmt->execute([
            'otp' => $otp,
            'otp_expiry' => $otp_expiry,
            'email' => $email
        ]);

        // Send Email with OTP
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'ranonline1219@gmail.com';
            $mail->Password = 'cavv jhhh onzy rwiu';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            //Recipients
            $mail->setFrom('ranonline1219@gmail.com', 'ISMS - BSU Announcement Portal');
            $mail->addAddress($email);

            //Content
            $mail->isHTML(true);
            $mail->Subject = 'Your Password Reset OTP';
            $mail->Body = "Your OTP is: $otp. It is valid for 10 minutes.";

            $mail->send();

            // Send SMS with OTP
            $smsMessage = "Your OTP is: $otp. It is valid for 10 minutes.";
            sendMessage($contact_number, $smsMessage);

            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Validation OTP</title>
                 <!-- Bootstrap CSS v5.3.2 -->
                <link
                    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
                    rel="stylesheet"
                    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
                    crossorigin="anonymous" />

                <link rel="stylesheet" href="login.css">
            </head>
            <body>
                <section class="login_container py-5 px-4 d-flex justify-content-center align-items-center">
                    <div class="container">
                        <div class="row d-flex justify-content-center align-items-center">
                            <div class="form-container col-12 col-md-6 bg-body-tertiary p-4">
                                <h2 class="text-center">Validate OTP</h2>
                                <div class="form-body p-2">
                                    <form method="POST" action="validate_otp.php">
                                        <?php
                                         echo 'OTP has been sent to your email and phone number.';
                                        ?>
                                        <div class="form-group mb-3">
                                            <label for="email">Enter your email:</label>
                                            <input type="email" name="email" required class="form-control p-3">
                                        </div>
                                        <div class="form-group mb-3 position-relative">
                                            <label for="otp">Enter OTP:</label>
                                            <input type="text" name="otp" required class="form-control p-3">
                                        </div>
                                        <div class="button_container d-flex justify-content-center">
                                            <input type="submit" value="Validate OTP" class="btn btn-warning px-4 mb-2">
                                        </div>   
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </body>
            </html>
            <?php
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Email does not exist in either student or school staff records.";
    }
}
