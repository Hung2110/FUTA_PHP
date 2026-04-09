<?php
session_start();
require_once 'db.php'; // Đảm bảo đường dẫn đúng tới file db.php

// --- BẢO MẬT ---
// Giả sử bạn lưu role của người dùng trong session sau khi đăng nhập
// Ví dụ: $_SESSION['user_role'] = 'admin';
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'manager', 'collaborator'])) {
    // Nếu không có quyền, chuyển hướng về trang đăng nhập
    // header('Location: /FUTA_PHP/admin/login.php');
    // exit;
    // Dòng trên được comment để bạn có thể test. Hãy mở nó ra khi tích hợp thực tế.
}
// --- KẾT THÚC BẢO MẬT ---

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin Chat - FUTA Advertising</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f4f6f9; }
        .main-content { padding-top: 56px; /* Height of navbar */ }
        .chat-container { display: flex; height: calc(100vh - 56px); }
        .sidebar { width: 300px; background: white; border-right: 1px solid #ddd; overflow-y: auto; }
        .chat-area { flex: 1; display: flex; flex-direction: column; background: #fff; }
        .session-item { padding: 15px; border-bottom: 1px solid #eee; cursor: pointer; }
        .session-item:hover, .session-item.active { background: #f0f2f5; }
        .session-name { font-weight: bold; }
        .session-info { font-size: 12px; color: #666; }
        
        .new-message-indicator {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            width: 10px;
            height: 10px;
            background-color: #0d6efd;
            border-radius: 50%;
        }
        .messages-box { flex: 1; padding: 20px; overflow-y: auto; background: #e9ecef; display: flex; flex-direction: column; gap: 10px; }
        .msg { padding: 10px 15px; border-radius: 15px; max-width: 70%; word-wrap: break-word; }
        .msg-customer { align-self: flex-start; background: white; border: 1px solid #ddd; }
        .msg-admin { align-self: flex-end; background: #007bff; color: white; }
        
        .input-area { padding: 15px; border-top: 1px solid #ddd; display: flex; gap: 10px; }
    </style>
</head>
<body>

<!-- Navbar Admin (Ví dụ) -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">FUTA Admin</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="adminNavbar">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="#">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="/FUTA_PHP/admin_chat.php">Live Chat</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<main class="main-content">
    <div class="chat-container">
        <!-- Danh sách khách hàng -->
        <div class="sidebar">
            <div class="p-3 bg-primary text-white"><h5 class="m-0">Danh sách Chat</h5></div>
            <div id="session-list"></div>
    </div>

    <!-- Khu vực chat -->
    <div class="chat-area">
        <div class="p-3 border-bottom bg-white d-flex justify-content-between align-items-center">
            <h5 class="m-0" id="current-chat-name">Chọn một khách hàng để chat</h5>
            <span id="current-session-id" style="display:none;"></span>
        </div>
        
        <div class="messages-box" id="messages-box">
            <!-- Tin nhắn sẽ hiện ở đây -->
        </div>

        <div class="input-area">
            <input type="text" id="admin-input" class="form-control" placeholder="Nhập tin nhắn..." disabled>
            <button class="btn btn-primary" id="send-btn" disabled>Gửi</button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let currentSessionId = null;
    let pollingInterval = null;
    let lastMessageId = 0;
    // Lưu trạng thái "đã xem" của admin cho các tin nhắn mới từ khách
    let seenSessions = new Set(); 

    $(document).ready(function() {
        loadSessionList();
        setInterval(loadSessionList, 10000); // Tự động làm mới danh sách session mỗi 10s
    });

    function loadSessionList() {
        $.get('/FUTA_PHP/includes/contact-chat-api.php', { action: 'get_sessions' }, function(res) {
            if (res.success) {
                const sessionList = $('#session-list');
                sessionList.html(''); // Xóa danh sách cũ
                res.sessions.forEach(session => {
                    const date = new Date(session.last_message_time);
                    const formattedTime = date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' }) + ' ' + date.toLocaleDateString('vi-VN');

                    let indicator = '';
                    // Hiển thị chấm xanh nếu tin nhắn cuối là của khách và admin chưa click vào session đó
                    if (session.last_sender === 'customer' && !seenSessions.has(session.id)) {
                        indicator = '<div class="new-message-indicator"></div>';
                    }

                    const sessionHtml = `
                        <div class="session-item" id="session-item-${session.id}" onclick="selectSession(${session.id}, '${$('<div/>').text(session.name).html()}')">
                            <div class="session-name">${$('<div/>').text(session.name).html()}</div>
                            <div class="session-info">${session.phone} | ${formattedTime}</div>
                            ${indicator}
                        </div>
                    `;
                    sessionList.append(sessionHtml);
                });
            }
        });
    }

    function selectSession(id, name) {
        currentSessionId = id;
        $('#current-chat-name').text(name);
        $('#current-session-id').text(id);
        $('#admin-input').prop('disabled', false);
        $('#send-btn').prop('disabled', false);
        $('.session-item').removeClass('active');
        $(`#session-item-${id}`).addClass('active');
        // Xóa chấm xanh và đánh dấu đã xem
        $(`#session-item-${id} .new-message-indicator`).remove();
        seenSessions.add(id);

        // Tải lần đầu cho session mới
        lastMessageId = 0;
        $('#messages-box').html('<div class="text-center text-muted p-3">Đang tải tin nhắn...</div>');
        loadMessages(true);

        if (pollingInterval) clearInterval(pollingInterval);
        pollingInterval = setInterval(() => loadMessages(false), 3000);
    }

    function loadMessages(isInitialLoad = false) {
        if (!currentSessionId) return;

        const params = {
            action: 'get_messages',
            session_id: currentSessionId
        };
        if (!isInitialLoad && lastMessageId > 0) {
            params.last_id = lastMessageId;
        }

        $.get('/FUTA_PHP/includes/contact-chat-api.php', params, function(res) {
            if (res.success && res.messages.length > 0) {
                const messagesBox = $('#messages-box');
                if (isInitialLoad) {
                    messagesBox.html('');
                }

                const shouldScroll = messagesBox.scrollTop() + messagesBox.innerHeight() >= messagesBox[0].scrollHeight - 20;

                res.messages.forEach(msg => {
                    const type = msg.sender === 'admin' ? 'msg-admin' : 'msg-customer';
                    const html = `
                        <div class="msg ${type}">
                            <div>${$('<div/>').text(msg.message).html()}</div>
                            <small style="font-size:10px; opacity:0.8">${new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</small>
                        </div>
                    `;
                    messagesBox.append(html);
                    lastMessageId = msg.id; // Cập nhật ID tin nhắn cuối cùng
                });

                if (isInitialLoad || shouldScroll) {
                    messagesBox.animate({ scrollTop: messagesBox[0].scrollHeight }, 500);
                }
            }
        });
    }

    function sendMessage() {
        const msg = $('#admin-input').val().trim();
        if (!msg || !currentSessionId) return;

        $.post('/FUTA_PHP/includes/contact-chat-api.php', {
            action: 'send_message',
            session_id: currentSessionId,
            sender: 'admin',
            message: msg
        }, function(res) {
            if (res.success) {
                $('#admin-input').val('');
                loadMessages(false); // Tải tin nhắn mới ngay sau khi gửi để hiển thị
            } else {
                alert('Lỗi gửi tin');
            }
        });
    }

    $('#send-btn').click(sendMessage);
    $('#admin-input').keypress(function(e) {
        if (e.which == 13) sendMessage();
    });
</script>
</body>
</html>