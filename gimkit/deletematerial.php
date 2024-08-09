<?php
include("conn.php");

// Function to safely get POST data
function getPostValue($key) {
    return isset($_POST[$key]) ? htmlspecialchars($_POST[$key]) : '';
}

$message = "";

// Fetch all material titles
$fetch_sql = "SELECT material_id, title FROM materials ORDER BY title";
$result = $conn->query($fetch_sql);
$materials = $result->fetch_all(MYSQLI_ASSOC);

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $material_id = getPostValue('material_id');
    
    if (!empty($material_id)) {
        // Start transaction
        $conn->begin_transaction();

        try {
            // Fetch associated media files
            $media_sql = "SELECT media_URL FROM media WHERE material_id = ?";
            $media_stmt = $conn->prepare($media_sql);
            $media_stmt->bind_param("i", $material_id);
            $media_stmt->execute();
            $media_result = $media_stmt->get_result();
            $media_files = $media_result->fetch_all(MYSQLI_ASSOC);

            // Delete media files from server
            foreach ($media_files as $media) {
                $file_path = __DIR__ . '/' . $media['media_URL'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }

            // Delete media entries from database
            $delete_media_sql = "DELETE FROM media WHERE material_id = ?";
            $delete_media_stmt = $conn->prepare($delete_media_sql);
            $delete_media_stmt->bind_param("i", $material_id);
            $delete_media_stmt->execute();

            // Delete the material
            $delete_material_sql = "DELETE FROM materials WHERE material_id = ?";
            $delete_material_stmt = $conn->prepare($delete_material_sql);
            $delete_material_stmt->bind_param("i", $material_id);
            $delete_material_stmt->execute();

            // Commit transaction
            $conn->commit();

            $message = "Material and associated media deleted successfully.";

            // Refresh the materials list after deletion
            $result = $conn->query($fetch_sql);
            $materials = $result->fetch_all(MYSQLI_ASSOC);

        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $message = "Error deleting material and media: " . $e->getMessage();
        }

        // Close statements
        if (isset($media_stmt)) $media_stmt->close();
        if (isset($delete_media_stmt)) $delete_media_stmt->close();
        if (isset($delete_material_stmt)) $delete_material_stmt->close();

    } else {
        $message = "Please select a material to delete.";
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Material</title>
</head>
<body>
    <h2>Delete Material</h2>
    <?php
    if (!empty($message)) {
        echo "<p>$message</p>";
    }
    ?>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="material_id">Select Material to Delete:</label>
        <select id="material_id" name="material_id" required>
            <option value="">Select a material</option>
            <?php foreach ($materials as $material): ?>
                <option value="<?php echo $material['material_id']; ?>">
                    <?php echo htmlspecialchars($material['title']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>
        <input type="submit" value="Delete Material">
    </form>
</body>
</html>