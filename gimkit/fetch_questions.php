<?php
include("conn.php");

if(isset($_POST['level_id'])) {
    $level_id = $_POST['level_id'];
    
    $fetch_questions_sql = "SELECT question_id, question_text FROM questions WHERE level_id = ? ORDER BY question_text";
    $stmt = $conn->prepare($fetch_questions_sql);
    $stmt->bind_param("i", $level_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo '<option value="">Select a question</option>';
    while($row = $result->fetch_assoc()) {
        echo '<option value="' . $row['question_id'] . '">' . htmlspecialchars($row['question_text']) . '</option>';
    }
    
    $stmt->close();
}

$conn->close();
?>