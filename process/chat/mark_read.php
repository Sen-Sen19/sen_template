<?php
include '../conn.php';
$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['employee_id'] ?? null;
$messageIds = $data['messageIds'] ?? [];

if($user_id && !empty($messageIds)) {
    foreach($messageIds as $msgId){
        $sql = "IF NOT EXISTS (SELECT 1 FROM message_reads WHERE message_id = ? AND employee_id = ?) 
                INSERT INTO message_reads (message_id, employee_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$msgId, $user_id, $msgId, $user_id]);
    }
}
echo json_encode(['status'=>'success']);
