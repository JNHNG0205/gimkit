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
        // Prepare SQL statement to delete the material
        $delete_sql = "DELETE FROM materials WHERE material_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $material_id);
        
        // Execute the statement
        if ($delete_stmt->execute()) {
            $message = "Material deleted successfully. Associated media have also been deleted.";
        } else {
            $message = "Error deleting material: " . $conn->error;
        }
        
        // Close delete statement
        $delete_stmt->close();

        // Refresh the materials list after deletion
        $result = $conn->query($fetch_sql);
        $materials = $result->fetch_all(MYSQLI_ASSOC);
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