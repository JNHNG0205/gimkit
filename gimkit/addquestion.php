<?php
include("conn.php");

$message = ''; // Variable to store messages for the user

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $question_text = $_POST['question_text'];
    $level_id = $_POST['level_id'];
    $media_type = $_POST['media_type'];
    $target_dir = __DIR__ . "/"; // Use absolute path
    
    // Create uploads directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    $original_filename = basename($_FILES["fileToUpload"]["name"]);
    $fileType = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
    $base_filename = pathinfo($original_filename, PATHINFO_FILENAME);
    
    // Generate a unique filename
    $counter = 1;
    $target_file = $target_dir . $base_filename . '.' . $fileType;
    while (file_exists($target_file)) {
        $target_file = $target_dir . $base_filename . '_' . $counter . '.' . $fileType;
        $counter++;
    }

    $uploadOk = 1;

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
            // Insert into questions table first
            $sql_question = "INSERT INTO questions (question_text, level_id) VALUES (?, ?)";
            $stmt_question = $conn->prepare($sql_question);
            $stmt_question->bind_param("si", $question_text, $level_id);
            
            if ($stmt_question->execute()) {
                $question_id = $stmt_question->insert_id;
                $media_URL = basename($target_file);
                
                // Insert media information
                $sql_media = "INSERT INTO media (media_type, media_URL, question_id) VALUES (?, ?, ?)";
                $stmt_media = $conn->prepare($sql_media);
                $stmt_media->bind_param("ssi", $media_type, $media_URL, $question_id);
                
                if ($stmt_media->execute()) {
                    $message = "The question and associated media have been uploaded successfully.";
                } else {
                    $message = "Sorry, there was an error saving the media information. Error: " . $conn->error;
                    error_log("Error saving media information: " . $conn->error);
                }
                $stmt_media->close();
            } else {
                $message = "Sorry, there was an error saving the question information. Error: " . $conn->error;
                error_log("Error saving question information: " . $conn->error);
            }
            $stmt_question->close();
        } else {
            $message = "Sorry, there was an error uploading your file.";
            error_log("File upload error: " . error_get_last()['message']);
        }
    }
}

// Fetch levels
$levels = [];
$level_query = "SELECT level_id, level_name FROM levels";
$level_result = $conn->query($level_query);
if ($level_result->num_rows > 0) {
    while($row = $level_result->fetch_assoc()) {
        $levels[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Question with Media</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
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
        input, select, textarea {
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        textarea {
            resize: vertical;
            min-height: 100px;
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
        <h2>Add Question with Media</h2>
        <?php
        if (!empty($message)) {
            echo "<div class='message'>$message</div>";
        }
        ?>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
            <label for="question_text">Question Text:</label>
            <textarea name="question_text" id="question_text" required></textarea>

            <label for="level_id">Level:</label>
            <select name="level_id" id="level_id" required>
                <option value="">Select a level</option>
                <?php foreach ($levels as $level): ?>
                    <option value="<?php echo $level['level_id']; ?>"><?php echo htmlspecialchars($level['level_name']); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="media_type">Media Type:</label>
            <select name="media_type" id="media_type" required>
                <option value="image">Image</option>
                <option value="video">Video</option>
                <option value="slides">Slides</option>
            </select>

            <label for="fileToUpload">Choose File:</label>
            <input type="file" name="fileToUpload" id="fileToUpload" required>

            <input type="submit" value="Add Question" name="submit">
        </form>
    </div>
</body>
</html>