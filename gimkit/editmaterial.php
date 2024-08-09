<?php
include("conn.php");

// Fetch all materials for the dropdown
$sql = "SELECT material_id, title FROM materials";
$result = $conn->query($sql);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $material_id = $_POST['material_id'];
    $new_title = $_POST['new_title'];
    $new_description = $_POST['new_description'];
    
    // Fetch all current media files for this material
    $sql = "SELECT media_id, media_type, media_URL FROM media WHERE material_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $material_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_media_files = $result->fetch_all(MYSQLI_ASSOC);

    // Handle file upload
    $target_dir = __DIR__ . '/';  // Current directory
    $target_file = $target_dir . basename($_FILES["new_media"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    
    // Determine media type
    if(in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        $media_type = 'image';
    } elseif(in_array($fileType, ['mp4', 'avi', 'mov', 'wmv'])) {
        $media_type = 'video';
    } elseif($fileType == 'pdf') {
        $media_type = 'slides';
    } else {
        echo "Invalid file type. Please upload an image, video, or PDF.";
        $uploadOk = 0;
    }
    
    // Check if file is valid
    if($media_type == 'image') {
        $check = getimagesize($_FILES["new_media"]["tmp_name"]);
        if($check === false) {
            echo "File is not a valid image.";
            $uploadOk = 0;
        }
    } elseif($media_type == 'video' || $media_type == 'slides') {
        if(!file_exists($_FILES["new_media"]["tmp_name"])) {
            echo "File is not valid.";
            $uploadOk = 0;
        }
    }
    
    // Check file size (limit to 50MB)
    if ($_FILES["new_media"]["size"] > 50000000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }
    
    // If everything is ok, try to upload file
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["new_media"]["tmp_name"], $target_file)) {
            // File uploaded successfully, update database
            $sql = "UPDATE materials SET title = ?, description = ? WHERE material_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $new_title, $new_description, $material_id);
            
            if ($stmt->execute()) {
                echo "Material updated successfully. ";
            } else {
                echo "Error updating material: " . $conn->error;
            }
            
            // Delete all previous media entries from the database
            $sql = "DELETE FROM media WHERE material_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $material_id);
            $stmt->execute();
            
            // Insert new media entry
            $relative_path = basename($target_file);  // Store only the filename in the database
            $sql = "INSERT INTO media (media_type, media_URL, material_id) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $media_type, $relative_path, $material_id);
            
            if ($stmt->execute()) {
                echo "New media added successfully. ";
                
                // Delete all old media files
                foreach ($current_media_files as $file) {
                    if (file_exists($target_dir . $file['media_URL'])) {
                        if (unlink($target_dir . $file['media_URL'])) {
                            echo "Deleted old file: " . $file['media_URL'] . ". ";
                        } else {
                            echo "Failed to delete old file: " . $file['media_URL'] . ". ";
                        }
                    }
                }
            } else {
                echo "Error adding new media: " . $conn->error;
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Material</title>
</head>
<body>
    <h2>Edit Material</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
        <label for="material_id">Select Material:</label>
        <select name="material_id" id="material_id" required>
            <option value="">Select a material</option>
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row["material_id"] . "'>" . $row["title"] . "</option>";
                }
            }
            ?>
        </select><br><br>
        
        <label for="new_title">New Title:</label>
        <input type="text" id="new_title" name="new_title" required><br><br>
        
        <label for="new_description">New Description:</label><br>
        <textarea id="new_description" name="new_description" rows="4" cols="50" required></textarea><br><br>
        
        <label for="new_media">New Media (Image, Video, or PDF):</label>
        <input type="file" id="new_media" name="new_media" accept="image/*,video/*,application/pdf" required><br><br>
        
        <input type="submit" value="Update Material">
    </form>
</body>
</html>