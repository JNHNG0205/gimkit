<?php
require __DIR__ . '/vendor/autoload.php';
include("session.php");
include("conn.php");
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST["email"])) {
        $otp = generateOTP();
        

        $sql = "UPDATE user SET OTP = '$otp' WHERE user_id = ".$_SESSION['mySession'].";";
        if ($con->query($sql) === TRUE) {
        } 
        else {
            echo "Error updating OTP: " . $con->error;
        }

        $to = $_POST["email"];
        $subject = "Your One-Time Password (OTP)";
        $message = "Your OTP is: " . $otp;

        if (sendEmail($to, $subject, $message)) {
            echo '<script>alert("OTP sent successful");
            window.location.href="verifyOTP.php";
            </script>';
      
        } else {
            echo '<script>alert("Failed to sent OTP ");
            </script>';
        }
    } else {
        echo '<script>alert("Email not provided");
        </script>';
    }
}

function generateOTP($length = 6) {
    $characters = '0123456789';
    $otp = '';
    $max = strlen($characters) - 1;
    for ($i = 0; $i < $length; $i++) {
        $otp .= $characters[mt_rand(0, $max)];
    }
    return $otp;
}

function sendEmail($to, $subject, $message) {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true); // Passing `true` enables exceptions

    try {
        $mail->SMTPDebug = 0; // Enable verbose debug output
        $mail->isSMTP(); // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com'; // Specify main and backup SMTP servers
        $mail->SMTPAuth = true; // Enable SMTP authentication
        $mail->Username = 'tehcoding@gmail.com'; // SMTP username
        $mail->Password = 'omsh iklb msnu kjud'; // SMTP password
        $mail->SMTPSecure = 'tls'; // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587; // TCP port to connect to

        //Recipients
        $mail->setFrom('tehcoding@gmail.com', 'HyperLingual');
        $mail->addAddress($to); // Add a recipient

        //Content
        $mail->isHTML(false); // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Send OTP</title>
    <link rel="icon" type="image/x-icon" href="icon1.png">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=ADLaM+Display&display=swap");

body {
  font-family: "ADLaM Display";
  background-color: #f0f0f0;
  margin: 0;
  padding: 0;
}

h1 {
  text-align: center;
  color: #333;
  margin-top: 150px;
}

form {
  width: 300px;
  margin: 0 auto;
  background-color: #fff;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

label {
  display: block;
  margin-bottom: 10px;  
}

input[type="email"] {
  width: 100%;
  padding: 10px;
  margin-bottom: 20px;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
}

button[type="submit"] {
  width: 100%;
  padding: 10px;
  background-color: #007bff;
  color: #fff;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s ease;
  font-family: "ADLaM Display";
}

button[type="submit"]:hover {
  background-color: #0056b3;
}

    </style>
  </head>
  <body>
    
    <h1>Send OTP</h1>
    <form method="post">
      <label for="email">Email:</label>
      <input type="email" name="email" required />
      <button type="submit">Send OTP</button>
    </form>
  </body>
</html>

