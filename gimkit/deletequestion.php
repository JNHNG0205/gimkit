<?php
include("conn.php");

// Function to safely get POST data
function getPostValue($key) {
    return isset($_POST[$key]) ? htmlspecialchars($_POST[$key]) : '';
}

$message = "";

// Fetch all level names
$fetch_levels_sql = "SELECT level_id, level_name FROM levels ORDER BY level_name";
$level_result = $conn->query($fetch_levels_sql);
$levels = $level_result->fetch_all(MYSQLI_ASSOC);

// Fetch questions for a specific level
function fetchQuestionsForLevel($conn, $level_id) {
    $fetch_questions_sql = "SELECT question_id, question_text FROM questions WHERE level_id = ? ORDER BY question_text";
    $stmt = $conn->prepare($fetch_questions_sql);
    $stmt->bind_param("i", $level_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $questions = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $questions;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $question_id = getPostValue('question_id');
    
    if (!empty($question_id)) {
        // Prepare SQL statement to delete the question
        $delete_sql = "DELETE FROM questions WHERE question_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $question_id);
        
        // Execute the statement
        if ($delete_stmt->execute()) {
            $message = "Question deleted successfully. Associated media have also been deleted.";
        } else {
            $message = "Error deleting question: " . $conn->error;
        }
        
        // Close delete statement
        $delete_stmt->close();
    } else {
        $message = "Please select a question to delete.";
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
    <title>Delete Question</title>
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
        select, input[type="submit"] {
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #f44336;
            color: white;
            border: none;
            cursor: pointer;
            padding: 10px;
        }
        input[type="submit"]:hover {
            background-color: #d32f2f;
        }
        .message {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #e7f3fe;
            border-left: 6px solid #2196F3;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#level_id').change(function() {
                var level_id = $(this).val();
                if(level_id) {
                    $.ajax({
                        url: 'fetch_questions.php',
                        type: 'POST',
                        data: {level_id: level_id},
                        success: function(data) {
                            $('#question_id').html(data);
                        }
                    });
                } else {
                    $('#question_id').html('<option value="">Select a level first</option>');
                }
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <h2>Delete Question</h2>
        <?php
        if (!empty($message)) {
            echo "<div class='message'>$message</div>";
        }
        ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="level_id">Select Level:</label>
            <select id="level_id" name="level_id" required>
                <option value="">Select a level</option>
                <?php foreach ($levels as $level): ?>
                    <option value="<?php echo $level['level_id']; ?>">
                        <?php echo htmlspecialchars($level['level_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="question_id">Select Question to Delete:</label>
            <select id="question_id" name="question_id" required>
                <option value="">Select a level first</option>
            </select>

            <input type="submit" value="Delete Question">
        </form>
    </div>
</body>
</html>