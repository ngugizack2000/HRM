<?php
require_once "importance.php";
$token = $_GET['token'];
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// require './PHPMailer/src/Exception.php';
// require './PHPMailer/src/PHPMailer.php';
// require './PHPMailer/src/SMTP.php';

$host = "localhost";
$dbname = "ntsystem";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the token exists in the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE password_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Token not found, display an error message or redirect
        Messages::error("Invalid token. Please try again or request a new password reset.");
    } else {
        $emailAddress =$user['email'];
        $subject="pasword changed";
        $message="
        <html>
        <head>
            <style>
                body {
                    font-family: 'Arial', sans-serif;
                    background-color: #f4f4f4;
                    color: #333;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 50px auto;
                    background-color: #fff;
                    padding: 20px;
                    border-radius: 5px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                }
                h1 {
                    color: #3498db;
                }
                p {
                    line-height: 1.6;
                }
                a {
                    color: #3498db;
                    text-decoration: none;
                    font-weight: bold;
                }
                a:hover {
                    text-decoration: underline;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <h1>Password Reset</h1>
                <p>Your password has been changed successfully.</p>
            </div>
        </body>
        </html>
    ";
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle the form submission to update the password
            $newPassword = $_POST['Password'];
            $confirmPassword = $_POST['cpassword'];

            // Validate the new password
            if (strlen($newPassword) < 5) {
                Messages::error("Password must be at least 5 characters long.");
            } elseif ($newPassword !== $confirmPassword) {
                Messages::error("Passwords do not match.");
            } else {
                // Hash the new password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);


// Create a new PHPMailer instance
                $mail = new PHPMailer(true); // Set true to enable exceptions

                try {
                    // Server settings
                    $mail->SMTPDebug = 0; // 0 = no output, 2 = verbose output
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com'; // Your SMTP server
                    $mail->Port = 587; // Your SMTP server port
                    $mail->SMTPAuth = true;
                    $mail->Username = 'ngugizack2000@gmail.com'; // Your SMTP username
                    $mail->Password = 'xtlg jxjj scxi rxqp'; // Your SMTP password
                    $mail->SMTPSecure = 'tls'; // tls or ssl

                    // Recipients
                    $mail->setFrom('ngugizack2000@gmail.com', 'HRM '); // Sender email and name
                    $mail->addAddress($emailAddress); // Recipient email

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = $subject;
                    $mail->Body = $message;
                    $mail->send();
                    $error = "Verification code sent to $emailAddress. Please check your email.";
                } catch (Exception $e) {
                    echo "Error sending reset link: {$mail->ErrorInfo}";
                }
                // Update the password in the database
                $stmt = $pdo->prepare("UPDATE users SET password = ?, password_token = NULL WHERE password_token = ?");
                $stmt->execute([$hashedPassword, $token]);

                // Display success message or redirect
                Messages::success("Password updated successfully. You can now login with your new password.");
                // Config::redir("login.php"); // Uncomment if you want to redirect after successful password reset
            }
        }
    }
} catch (Exception $e) {
    Messages::error($e->getMessage());
}
?>
<html>
<title><?php echo CONFIG::SYSTEM_NAME; ?> : Reset Password</title>
<?php require_once "inc/head.inc.php"; ?>

<head>
</head>

<body>
    <div class='container-fluid'>
        <div class='row'>
            <div class='col-md-1'></div>
            <div class='col-md-4'></div>
            <div class='col-md-15'>
                <div class='content-area'>
                    <div class='content-header'></div>
                    <div class='content-body'>
                        <center>
                            <div class='badge-header'>Reset Password</div>
                        </center>
                        <div class='row'>
                            <div class='col-md-3'></div>
                            <div class='container'>
                                <div class='row'>
                                    <div class='col-md-6 mx-auto'>
                                        <div class='form-holder'>
                                            <?php Db::form(array("New Password", "Confirm Password"), 3, array("Password", "cpassword"), array("password", "password"), "Reset"); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class='col-md-3'></div>
                        </div><!-- end of the content area -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'inc/footer.inc.php'; ?>
</body>

</html>