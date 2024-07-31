<?php
include("conn.php");

// Fetch materials
$sql_materials = "SELECT * FROM materials";
$result_materials = $conn->query($sql_materials);

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Display</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
        .material { border: 1px solid #ccc; padding: 10px; margin-bottom: 20px; }
        .media { margin-top: 10px; }
        img { max-width: 100%; height: auto; }
    </style>
</head>
<body>
    <h1>Media Display</h1>
    
    <?php
    if ($result_materials->num_rows > 0) {
        while($row_material = $result_materials->fetch_assoc()) {
            echo "<div class='material'>";
            echo "<h2>" . htmlspecialchars($row_material["title"]) . "</h2>";
            echo "<p>" . htmlspecialchars($row_material["description"]) . "</p>";
            echo "<p>Created at: " . $row_material["created_at"] . "</p>";
            
            // Fetch media for this material
            $sql_media = "SELECT * FROM media WHERE material_id = " . $row_material["material_id"];
            $result_media = $conn->query($sql_media);
            
            if ($result_media->num_rows > 0) {
                while($row_media = $result_media->fetch_assoc()) {
                    echo "<div class='media'>";
                    if ($row_media["media_type"] == "image") {
                        $image_path = htmlspecialchars($row_media["media_URL"]);
                        echo "<img src='" . $image_path . "' alt='Media'>";

                    } else {
                        echo "<p>Media URL: <a href='" . htmlspecialchars($row_media["media_URL"]) . "' target='_blank'>" . htmlspecialchars($row_media["media_URL"]) . "</a></p>";
                    }
                    echo "</div>";
                }
            } else {
                echo "<p>No media found for this material.</p>";
            }
            
            echo "</div>";
        }
    } else {
        echo "<p>No materials found.</p>";
    }
    
    $conn->close();
    ?>
</body>
</html>