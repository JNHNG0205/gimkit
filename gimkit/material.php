<?php
include("conn.php");

$message = ''; // Variable to store messages for the user

// File upload handling
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $media_type = $_POST["media_type"];
    $material_id = intval($_POST["material_id"]);
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    // Check if file already exists
    if (file_exists($target_file)) {
        $message = "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Check file size (limit to 5MB)
    if ($_FILES["fileToUpload"]["size"] > 5000000) {
        $message = "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    $allowed_extensions = array("jpg", "jpeg", "png", "gif", "pdf", "mp4", "avi", "mov");
    if (!in_array($fileType, $allowed_extensions)) {
        $message = "Sorry, only JPG, JPEG, PNG, GIF, PDF, MP4, AVI, and MOV files are allowed.";
        $uploadOk = 0;
    }

    // Upload file and save to database
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            $media_URL = "http://" . $_SERVER['HTTP_HOST'] . "/uploads/" . basename($_FILES["fileToUpload"]["name"]);
            
            $sql = "INSERT INTO media (media_type, media_URL, material_id) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $media_type, $media_URL, $material_id);
            
            if ($stmt->execute()) {
                $message = "The file " . basename($_FILES["fileToUpload"]["name"]) . " has been uploaded and associated with the material.";
            } else {
                $message = "Sorry, there was an error uploading your file and saving to the database.";
            }
            $stmt->close();
        } else {
            $message = "Sorry, there was an error uploading your file.";
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Material Media Upload</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 10px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select {
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            padding: 10px;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .message {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #e7f3fe;
            border-left: 6px solid #2196F3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Upload Material Media</h2>
        <?php
        if (!empty($message)) {
            echo "<div class='message'>$message</div>";
        }
        ?>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
            <label for="material_id">Material ID:</label>
            <input type="number" name="material_id" id="material_id" required>

            <label for="media_type">Media Type:</label>
            <select name="media_type" id="media_type" required>
                <option value="image">Image</option>
                <option value="video">Video</option>
                <option value="slides">Slides</option>
            </select>

            <label for="fileToUpload">Choose File:</label>
            <input type="file" name="fileToUpload" id="fileToUpload" required>

            <input type="submit" value="Upload Media" name="submit">
        </form>
    </div>
</body>
</html>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Materials</title>
</head>
<body>
    
</body>
</html>