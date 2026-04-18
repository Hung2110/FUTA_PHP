<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'start_session':
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (!$name || !$phone) {
            echo json_encode(['success' => false, 'error' => 'Vui lòng nhập tên và số điện thoại']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO chat_sessions (name, phone, email, last_sender) VALUES (?, ?, ?, 'customer')");
        $stmt->bind_param("sss", $name, $phone, $email);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'session_id' => $stmt->insert_id]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Lỗi tạo phiên chat']);
        }
        $stmt->close();
        break;

    case 'send_message':
        $session_id = intval($_POST['session_id'] ?? 0);
        $sender = $_POST['sender'] ?? 'customer'; // 'customer' hoặc 'admin'
        $message = trim($_POST['message'] ?? '');

        if (!$session_id || !$message) {
            echo json_encode(['success' => false, 'error' => 'Thiếu thông tin gửi']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO chat_messages (session_id, sender, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $session_id, $sender, $message);
        if ($stmt->execute()) {
            // Cập nhật thời gian và người gửi cuối cùng để Admin dễ theo dõi
            $update_stmt = $conn->prepare("UPDATE chat_sessions SET last_message_time = NOW(), last_sender = ? WHERE id = ?");
            $update_stmt->bind_param("si", $sender, $session_id);
            $update_stmt->execute();
            $update_stmt->close();

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        $stmt->close();
        break;

    case 'get_messages':
        $session_id = intval($_GET['session_id'] ?? 0);
        $last_id = intval($_GET['last_id'] ?? 0);

        $stmt = $conn->prepare("SELECT id, sender, message, created_at FROM chat_messages WHERE session_id = ? AND id > ? ORDER BY id ASC");
        $stmt->bind_param("ii", $session_id, $last_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $messages = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        echo json_encode(['success' => true, 'messages' => $messages]);
        break;

    case 'get_sessions':
        $query = "SELECT id, name, phone, last_message_time, last_sender FROM chat_sessions ORDER BY last_message_time DESC LIMIT 50";
        $result = $conn->query($query);
        if ($result) {
            $sessions = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'sessions' => $sessions]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Lỗi truy vấn CSDL: ' . $conn->error]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Hành động không hợp lệ']);
}
