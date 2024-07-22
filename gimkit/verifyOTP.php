<?php
include("session.php");
include("conn.php");
$sql = "SELECT * FROM user WHERE user_id = ".$_SESSION['mySession'].";";
$result = mysqli_query($con, $sql);
$row = mysqli_fetch_array($result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST["password"]) && isset($_POST["newpassword"]) && isset($_POST["otp"]) ) {
        $otp = $_POST["otp"];
        $password = $_POST["password"];
        $newpassword = $_POST["newpassword"];
        
        $expectedOTP = $row['OTP']; 

        if ($_POST["password"] == $row['user_password']){
            if ($otp == $expectedOTP) {
                $sql2 = "UPDATE user SET user_password = '$newpassword' WHERE user_id = ".$_SESSION['mySession'].";";
                if(mysqli_query($con, $sql2)) {
                    echo '<script>alert("Password changed successfully!");
                    window.location.href="login.php";
                    </script>';
                } else {
                    echo "Error updating password: " . mysqli_error($con);
                }
            } else {
                echo '<script>alert("Invaild OTP");</script>';
            }
        }
        else {
            echo '<script>alert("Incorrect Password")</script>;';
        }    
    } 
    else {
        echo '<script>alert("Password or OTP not provided")</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <style>
    @import url("https://fonts.googleapis.com/css2?family=ADLaM+Display&display=swap");
    body {
    font-family: "ADLaM Display";
    background-color: #f0f0f0;
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    }

    h1 {
    text-align: center;
    margin-top: 100px;
    }

    form {
    width: 300px;
    margin: 20px auto;
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    label {
    display: block;
    margin-bottom: 8px;
    }

    input[type="text"],
    input[type="password"] {
    width: calc(100% - 22px);   
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
    }

    button {
    width: 100%;
    padding: 10px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    }

    button:hover {
    background-color: #0056b3;
    }

    button:active {
    background-color: #004080;
    }

    </style>
    <link rel="icon" type="image/x-icon" href="icon1.png">
</head>
<body>
    <h1>Verify OTP</h1>
    <form  method="post">
        <label for="">Current Password:</label>
        <input type="password" name="password" required>
        <label for="">New Password:</label>
        <input type="password" name="newpassword">
        <label for="otp">OTP:</label>
        <input type="text" name="otp" required>
        <button type="submit">Verify OTP</button>
    </form>
</body>
</html>
