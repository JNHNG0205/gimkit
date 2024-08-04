<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gimkit";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'login') {
            // Login logic
            $user_name = $conn->real_escape_string($_POST['user_name']);
            $user_password = $conn->real_escape_string($_POST['user_password']);

            $sql = "SELECT * FROM user WHERE user_name = '$user_name' AND user_password = '$user_password'";
            $result = $conn->query($sql);

            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['user_name'] = $row['user_name'];
                $_SESSION['user_role'] = $row['user_role'];
                
                header("Location: hello.php"); // Redirect to a protected page after login
                exit();
            } else {
                echo "Invalid username or password";
            }
        } elseif ($_POST['action'] == 'register') {
            // Registration logic
            $user_name = $conn->real_escape_string($_POST['user_name']);
            $user_password = $conn->real_escape_string($_POST['user_password']);
            $user_email = $conn->real_escape_string($_POST['user_email']);
            $user_role = 'player';

            // Check if username already exists
            $check_sql = "SELECT * FROM user WHERE user_name = '$user_name'";
            $check_result = $conn->query($check_sql);

            if ($check_result->num_rows > 0) {
                echo "Username already exists. Please choose a different username.";
            } else {
                $sql = "INSERT INTO user (user_name, user_password, user_email, user_role) 
                        VALUES ('$user_name', '$user_password', '$user_email', '$user_role')";

                if ($conn->query($sql) === TRUE) {
                    echo "Registration successful. You can now login.";
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            }
        }
    }
}

$conn->close();
?>
