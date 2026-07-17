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
    $initial_message = trim($_POST['initial_message'] ?? '');

    if (empty($name) || empty($phone)) {
        echo json_encode(['success' => false, 'error' => 'Vui lòng cung cấp tên và số điện thoại']);
        exit;
    }

    // Kiểm tra xem khách hàng (số điện thoại) đã có phiên chat nào chưa
    $stmt = $conn->prepare("SELECT id FROM chat_sessions WHERE phone = ? ORDER BY last_message_time DESC LIMIT 1");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Nếu là khách cũ, tái sử dụng session_id và cập nhật thời gian, tên, email mới nhất
        $session_id = $row['id'];
        $update_stmt = $conn->prepare("UPDATE chat_sessions SET name = ?, email = ?, last_message_time = NOW(), last_active_time = NOW(), last_sender = 'customer' WHERE id = ?");
        $update_stmt->bind_param("ssi", $name, $email, $session_id);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        // Khách mới, tạo phiên chat hoàn toàn mới
        $insert_stmt = $conn->prepare("INSERT INTO chat_sessions (name, phone, email, last_message_time, last_active_time, last_sender) VALUES (?, ?, ?, NOW(), NOW(), 'customer')");
        $insert_stmt->bind_param("sss", $name, $phone, $email);
        
        if ($insert_stmt->execute()) {
            $session_id = $conn->insert_id;
        } else {
            echo json_encode(['success' => false, 'error' => $insert_stmt->error]);
            $insert_stmt->close();
            exit;
        }
        $insert_stmt->close();
    }
    $stmt->close();

    // Nếu có tin nhắn ban đầu, lưu nó lại
    if (!empty($initial_message) && $session_id > 0) {
        $msg_stmt = $conn->prepare("INSERT INTO chat_messages (session_id, sender, message, created_at) VALUES (?, 'customer', ?, NOW())");
        $msg_stmt->bind_param("is", $session_id, $initial_message);
        $msg_stmt->execute();
        $msg_stmt->close();
    }

    if ($session_id > 0) {
        echo json_encode(['success' => true, 'session_id' => $session_id]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Không thể tạo hoặc tìm thấy phiên chat.']);
    }
    exit;
}

// 2. Gửi tin nhắn (Cho cả Khách hàng và Admin)
if ($action === 'send_message') {
    $session_id = intval($_POST['session_id'] ?? 0);
    $sender = $_POST['sender'] ?? ''; // 'customer' hoặc 'admin'
    $message = trim($_POST['message'] ?? '');

    // Xử lý upload file nếu có
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['file'];
        $mime_type = $file['type'];
        $max_size = 15 * 1024 * 1024; // Mặc định 15MB cho file thường
        
        if (strpos($mime_type, 'video/') === 0) {
            $max_size = 30 * 1024 * 1024; // 30MB cho video
        } elseif (strpos($mime_type, 'image/') === 0) {
            $max_size = 5 * 1024 * 1024; // 5MB cho ảnh
        }
        
        if ($file['size'] > $max_size) {
            echo json_encode(['success' => false, 'error' => 'Kích thước file vượt quá giới hạn (Ảnh: Tối đa 5MB, Video: Tối đa 30MB, File: Tối đa 15MB)']);
            exit;
        }
        $uploadDir = '../uploads/chat/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Bảo mật: Chặn các đuôi file thực thi nguy hiểm
        $forbidden_exts = ['php', 'php3', 'php4', 'php5', 'phtml', 'exe', 'sh', 'bat', 'js', 'cgi'];
        if (in_array($ext, $forbidden_exts, true) || empty($ext)) {
            echo json_encode(['success' => false, 'error' => 'Định dạng file không được phép tải lên!']);
            exit;
        }

        $filename = time() . '_' . rand(1000, 9999) . '.' . $ext;
        $dest = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $mime = mime_content_type($dest) ?: 'application/octet-stream';
            $url = 'uploads/chat/' . $filename;
            $message = "FILE::{$mime}::{$url}::" . $file['name']; // Tạo chuỗi định danh file
        } else {
            echo json_encode(['success' => false, 'error' => 'Lỗi lưu file trên server']);
            exit;
        }
    }

    if ($session_id <= 0 || empty($message) || !in_array($sender, ['customer', 'admin'])) {
        echo json_encode(['success' => false, 'error' => 'Dữ liệu không hợp lệ']);
        exit;
    }

    $conn->begin_transaction();
    try {
        // Lưu tin nhắn
        $admin_name = ($sender === 'admin' && isset($_SESSION['admin_fullname'])) ? $_SESSION['admin_fullname'] : null;
        $stmt1 = $conn->prepare("INSERT INTO chat_messages (session_id, sender, admin_name, message, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt1->bind_param("isss", $session_id, $sender, $admin_name, $message);
        $stmt1->execute();
        $stmt1->close();

        // Cập nhật thời gian và người gửi cuối cùng trong phiên chat
        if ($sender === 'customer') {
            $stmt2 = $conn->prepare("UPDATE chat_sessions SET last_message_time = NOW(), last_active_time = NOW(), last_sender = ? WHERE id = ?");
        } else {
            $stmt2 = $conn->prepare("UPDATE chat_sessions SET last_message_time = NOW(), last_sender = ? WHERE id = ?");
        }
        $stmt2->bind_param("si", $sender, $session_id);
        $stmt2->execute();
        $stmt2->close();

        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// 3. Khách hàng gửi ping để báo đang online
if ($action === 'ping') {
    $session_id = intval($_POST['session_id'] ?? 0);
    if ($session_id > 0) {
        $stmt = $conn->prepare("UPDATE chat_sessions SET last_active_time = NOW() WHERE id = ?");
        $stmt->bind_param("i", $session_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Dữ liệu không hợp lệ']);
    }
    exit;
}

// 4. Admin lấy danh sách các phiên chat
if ($action === 'get_sessions') {
    $sessions = [];
    $result = $conn->query("
        SELECT cs.*, 
               (SELECT COUNT(*) FROM chat_sessions cs2 WHERE cs2.phone = cs.phone) as phone_count,
               (SELECT MIN(created_at) FROM chat_messages cm WHERE cm.session_id = cs.id) as first_msg_time,
               TIMESTAMPDIFF(SECOND, IFNULL(cs.last_active_time, cs.last_message_time), NOW()) as offline_seconds
        FROM chat_sessions cs 
        ORDER BY cs.last_message_time DESC
    ");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $is_returning = false;
            // Khách cũ nếu tạo nhiều phiên chat hoặc tin nhắn đầu tiên cách đây quá 1 phút (60 giây)
            if ($row['phone_count'] > 1 || (!empty($row['first_msg_time']) && (time() - strtotime($row['first_msg_time']) > 60))) {
                $is_returning = true;
            }
            $row['is_returning'] = $is_returning;

            // Kiểm tra trạng thái online/offline
            $offline_seconds = (int)$row['offline_seconds'];
            if ($offline_seconds < 60) { // Nếu có hoạt động (ping hoặc nhắn) trong 60 giây
                $row['is_online'] = true;
                $row['offline_text'] = "Đang hoạt động";
            } else {
                $row['is_online'] = false;
                if ($offline_seconds < 3600) {
                    $minutes = floor($offline_seconds / 60);
                    $row['offline_text'] = "Hoạt động $minutes phút trước";
                } elseif ($offline_seconds < 86400) {
                    $hours = floor($offline_seconds / 3600);
                    $row['offline_text'] = "Hoạt động $hours giờ trước";
                } else {
                    $days = floor($offline_seconds / 86400);
                    $row['offline_text'] = "Hoạt động $days ngày trước";
                }
            }
            $sessions[] = $row;
        }
    }
    echo json_encode(['success' => true, 'sessions' => $sessions]);
    exit;
}

// 5. Lấy nội dung tin nhắn của một phiên chat cụ thể
if ($action === 'get_messages') {
    $session_id = intval($_GET['session_id'] ?? 0);
    $last_id = intval($_GET['last_id'] ?? 0); // Chỉ lấy những tin nhắn mới hơn ID này

    $messages = [];
    $stmt = $conn->prepare("SELECT * FROM chat_messages WHERE session_id = ? AND id > ? ORDER BY id ASC");
    $stmt->bind_param("ii", $session_id, $last_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    $stmt->close();

    echo json_encode(['success' => true, 'messages' => $messages]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Hành động không hợp lệ']);
?>