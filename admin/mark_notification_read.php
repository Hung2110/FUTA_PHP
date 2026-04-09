<?php
require_once 'auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $notification_id = intval($_POST['id']);
    $user_id = $_SESSION['admin_id'];

    // Mark specific notification as read
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notification_id, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    $stmt->close();
} else {
    // Invalid request
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>