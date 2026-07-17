<?php
require_once 'auth_check.php';
$pageTitle = 'Quản Lý Chat Trực Tuyến';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> | FUTA Advertising</title>
    <link rel="icon" href="../assets/images/logo/futa.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { background: #f7f9fc; overflow: hidden; }
        .main-content { padding-bottom: 0 !important; height: 100vh; display: flex; flex-direction: column; }
        
        .chat-wrapper {
            flex: 1;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            overflow: hidden;
            margin-bottom: 20px;
            border: 1px solid #e5e7eb;
        }
        
        /* Sidebar Sessions */
        .chat-sidebar {
            width: 320px;
            background: #fff;
            border-right: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
        }
        .session-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f3f4f6;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            align-items: center;
        }
        .session-item:hover, .session-item.active { background: #f0f7ff; }
        .session-item.active { border-left: 4px solid #007bff; }
        .session-avatar {
            width: 45px; height: 45px;
            border-radius: 50%; background: #e0e7ff; color: #007bff;
            display: flex; align-items: center; justify-content: center;
            font-weight: bold; font-size: 1.2rem; flex-shrink: 0; margin-right: 15px;
        }
        .session-info { overflow: hidden; flex: 1; }
        .session-name { font-weight: 600; color: #1f2937; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 15px; }
        .session-meta { font-size: 12px; color: #6b7280; display: flex; justify-content: space-between; margin-top: 4px; }
        .badge-returning { font-size: 10px; padding: 2px 6px; background: #fff3cd; color: #856404; border-radius: 10px; margin-left: 5px; border: 1px solid #ffeeba; }
        
        /* Main Chat Area */
        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #f4f6f9;
        }
        .chat-header {
            padding: 15px 25px;
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .chat-body {
            flex: 1;
            padding: 25px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 15px;
            scroll-behavior: smooth;
        }
        .chat-empty {
            flex: 1; display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            color: #9ca3af; background: #fff;
        }
        
        /* Message Bubbles */
        .msg-wrapper { display: flex; flex-direction: column; max-width: 75%; }
        .msg-wrapper.admin { align-self: flex-end; align-items: flex-end; }
        .msg-wrapper.customer { align-self: flex-start; align-items: flex-start; }
        
        .msg-bubble {
            padding: 12px 16px;
            border-radius: 18px;
            font-size: 14.5px;
            line-height: 1.5;
            word-wrap: break-word;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .msg-wrapper.admin .msg-bubble {
            background: #007bff; color: white;
            border-bottom-right-radius: 4px;
        }
        .msg-wrapper.customer .msg-bubble {
            background: #f1f3f5; color: #1f2937;
            border-bottom-left-radius: 4px;
        }
        .msg-time { font-size: 11px; margin-top: 4px; color: #9ca3af; }
        .msg-admin-name { font-size: 12px; font-weight: 600; color: #6b7280; margin-bottom: 4px; padding-right: 4px; }
        
    /* Date Separator */
    .chat-date-separator {
        text-align: center;
        font-size: 12px;
        color: #6b7280;
        margin: 15px 0 5px 0;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
    }
    .chat-date-separator::before,
    .chat-date-separator::after {
        content: "";
        flex: 1;
        height: 1px;
        background: #e5e7eb;
        margin: 0 15px;
    }

        /* Input Area */
        .chat-footer {
            padding: 15px 25px;
            background: #fff;
            border-top: 1px solid #e5e7eb;
        }
        .chat-input-group {
            display: flex; align-items: center; gap: 10px;
            background: #f3f4f6; border-radius: 25px; padding: 5px 15px;
        }
        .chat-input-group input[type="text"] {
            border: none; background: transparent; box-shadow: none; flex: 1; padding: 10px 0;
        }
        .chat-input-group input[type="text"]:focus { outline: none; }
        .btn-icon {
            background: transparent; border: none; color: #6b7280; font-size: 1.2rem;
            cursor: pointer; transition: color 0.2s; padding: 5px;
        }
        .btn-icon:hover { color: #007bff; }
        .btn-send {
            background: #007bff; color: white; border: none; border-radius: 50%;
            width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: background 0.2s;
        }
        .btn-send:hover { background: #0056b3; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3 mb-0"><i class="fas fa-comments text-primary me-2"></i>Quản Lý Chat Trực Tuyến</h1>
        </div>

        <div class="chat-wrapper d-flex">
            <!-- Sidebar Sessions -->
            <div class="chat-sidebar">
                <div class="p-3 border-bottom bg-light">
                    <input type="text" class="form-control rounded-pill" id="searchSession" placeholder="Tìm tên, số điện thoại...">
                </div>
                <div class="flex-grow-1 overflow-auto" id="sessionList">
                    <!-- Sessions loaded via JS -->
                    <div class="text-center text-muted mt-4"><i class="fas fa-circle-notch fa-spin"></i> Đang tải...</div>
                </div>
            </div>

            <!-- Main Chat Room -->
            <div class="chat-main" id="chatMain" style="display: none;">
                <div class="chat-header">
                    <div class="d-flex align-items-center">
                        <div class="session-avatar" id="headerAvatar">?</div>
                        <div>
                            <h5 class="mb-0 fw-bold" id="headerName">Khách hàng</h5>
                            <div class="text-muted small">
                                <span id="headerStatus"></span>
                                <span class="mx-2">|</span>
                                <i class="fas fa-phone-alt me-1"></i><span id="headerPhone">---</span>
                                <span class="mx-2">|</span>
                                <i class="fas fa-envelope me-1"></i><span id="headerEmail">---</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="chat-body" id="chatMessages">
                    <!-- Messages loaded via JS -->
                </div>
                
                <div class="chat-footer">
                    <div class="chat-input-group">
                        <input type="file" id="attachFile" style="display: none;">
                        <button class="btn-icon" id="btnAttach" title="Đính kèm file (Ảnh, Video, Tài liệu)"><i class="fas fa-paperclip"></i></button>
                        <input type="text" id="chatInput" placeholder="Nhập tin nhắn trả lời..." autocomplete="off">
                        <button class="btn-send" id="btnSend"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </div>
            </div>
            
            <!-- Empty State -->
            <div class="chat-empty" id="chatEmpty">
                <img src="../assets/images/logo/futa.png" alt="FUTA" style="width: 100px; opacity: 0.2; margin-bottom: 20px;">
                <h4>FUTA Advertising Chat</h4>
                <p>Chọn một cuộc trò chuyện từ danh sách bên trái để bắt đầu.</p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let currentSessionId = null;
        let lastMsgId = 0;
        let lastRenderedDate = '';
        let readTimestamps = JSON.parse(localStorage.getItem('futa_admin_chat_read') || '{}');
        let ws = null;
        const apiUrl = '../includes/contact-chat-api.php';

        // 1. KẾT NỐI WEBSOCKET
        function connectWS() {
            // Tự động nhận diện giao thức cho Production/Local
            const wsProtocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
            const wsUrl = window.location.protocol === 'https:' 
                ? `${wsProtocol}//${window.location.host}/chat/` 
                : `${wsProtocol}//${window.location.hostname}:8080`;
            ws = new WebSocket(wsUrl);
            
            ws.onmessage = function(e) {
                const data = JSON.parse(e.data);
                if(data.event === 'new_session' || data.event === 'new_message') {
                    loadSessions();
                    // Nếu tin nhắn thuộc phiên đang mở -> Tải luôn tin nhắn
                    if(currentSessionId && data.session_id == currentSessionId) {
                        loadMessages(false);
                    }
                }
            };
            
            
            ws.onerror = function() { ws.close(); }
        }
        connectWS();

        // Fallback Polling (Dự phòng nếu WS chết)
        setInterval(() => {
            if(!ws || ws.readyState !== WebSocket.OPEN) {
                loadSessions();
                if(currentSessionId) loadMessages(false);
            }
        }, 3000);

        // 2. LOAD DANH SÁCH SESSIONS
        function loadSessions() {
            $.get(apiUrl, { action: 'get_sessions', t: Date.now() }, function(res) {
                if (res.success) {
                    const list = $('#sessionList');
                    const searchTerm = $('#searchSession').val().toLowerCase();
                    list.empty();
                    
                    if(res.sessions.length === 0) {
                        list.html('<div class="p-4 text-center text-muted">Chưa có cuộc trò chuyện nào.</div>');
                        return;
                    }

                    res.sessions.forEach(s => {
                        // Lọc theo tìm kiếm
                        if(searchTerm && !(s.name.toLowerCase().includes(searchTerm) || s.phone.includes(searchTerm))) return;

                        const isActive = s.id == currentSessionId ? 'active' : '';
                        const avatarChar = s.name ? s.name.charAt(0).toUpperCase() : '?';
                        const returningBadge = s.is_returning ? '<span class="badge-returning">Khách cũ</span>' : '';
                        const time = new Date(s.last_message_time).toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'});
                        
                        // Đánh dấu in đậm nếu khách nhắn cuối cùng và Admin chưa xem
                        if (s.id == currentSessionId) {
                            readTimestamps[s.id] = s.last_message_time;
                            localStorage.setItem('futa_admin_chat_read', JSON.stringify(readTimestamps));

                            // Cập nhật trạng thái header liên tục nếu đang mở phiên chat này
                            const statusHtml = s.is_online 
                                ? '<span class="text-success"><i class="fas fa-circle" style="font-size: 10px;"></i> Đang hoạt động</span>' 
                                : `<span class="text-muted"><i class="far fa-clock" style="font-size: 10px;"></i> ${s.offline_text}</span>`;
                            $('#headerStatus').html(statusHtml);
                        }
                        const isUnread = (s.last_sender === 'customer' && readTimestamps[s.id] !== s.last_message_time);
                        const fw = isUnread ? 'fw-bold text-dark' : '';

                        const html = `
                            <div class="session-item ${isActive}" data-id="${s.id}" data-name="${s.name}" data-phone="${s.phone}" data-email="${s.email || ''}" data-last-time="${s.last_message_time}" data-online="${s.is_online ? 1 : 0}" data-offlinetext="${s.offline_text}">
                                <div class="session-avatar">${avatarChar}</div>
                                <div class="session-info">
                                    <p class="session-name">${s.name} ${returningBadge}</p>
                                    <div class="session-meta ${fw}">
                                        <span>${s.phone}</span>
                                        <span>${time}</span>
                                    </div>
                                </div>
                            </div>
                        `;
                        list.append(html);
                    });
                }
            });
        }

        // Xử lý Click vào session
        $(document).on('click', '.session-item', function() {
            $('.session-item').removeClass('active');
            $(this).addClass('active');
            
            currentSessionId = $(this).data('id');
            $('#headerName').text($(this).data('name'));
            $('#headerPhone').text($(this).data('phone'));
            $('#headerEmail').text($(this).data('email') || 'Không có email');
            $('#headerAvatar').text($(this).data('name').charAt(0).toUpperCase());
            
            // Render trạng thái khi click vào người dùng
            const isOnline = $(this).data('online') == 1;
            const offlineText = $(this).data('offlinetext');
            const statusHtml = isOnline 
                ? '<span class="text-success"><i class="fas fa-circle" style="font-size: 10px;"></i> Đang hoạt động</span>' 
                : `<span class="text-muted"><i class="far fa-clock" style="font-size: 10px;"></i> ${offlineText}</span>`;
            $('#headerStatus').html(statusHtml);
            
            $(this).find('.session-meta').removeClass('fw-bold text-dark');
            readTimestamps[currentSessionId] = $(this).data('last-time');
            localStorage.setItem('futa_admin_chat_read', JSON.stringify(readTimestamps));
            
            lastRenderedDate = '';
            $('#chatEmpty').hide();
            $('#chatMain').show();
            
            loadMessages(true);
        });

        $('#searchSession').on('input', loadSessions);

        // 3. XỬ LÝ HIỂN THỊ TIN NHẮN (ĐỊNH DẠNG FILE)
        function formatChatMsg(rawMsg) {
            if (rawMsg.startsWith('FILE::')) {
                const parts = rawMsg.split('::');
                if (parts.length >= 4) {
                    const mime = parts[1]; const url = parts[2]; const name = parts[3];
                    if (mime.startsWith('image/')) {
                        return `<a href="${url}" target="_blank"><img src="${url}" style="max-width: 100%; max-height: 250px; object-fit: cover; border-radius: 8px;"></a>`;
                    } else if (mime.startsWith('video/')) {
                        return `<video controls src="${url}" style="max-width: 100%; max-height: 250px; border-radius: 8px;"></video>`;
                    } else {
                        return `<a href="${url}" target="_blank" class="text-decoration-none d-flex align-items-center gap-2 text-primary bg-light p-2 rounded border"><i class="fas fa-file-download fa-2x"></i> <span>${name}</span></a>`;
                    }
                }
            }
            return $('<div/>').text(rawMsg).html().replace(/\n/g, '<br>');
        }

        // 4. LOAD TIN NHẮN TRONG PHIÊN
        function loadMessages(isInitial = false) {
            if(!currentSessionId) return;
            
            let url = `${apiUrl}?action=get_messages&session_id=${currentSessionId}&t=${Date.now()}`;
            if (!isInitial && lastMsgId > 0) {
                url += `&last_id=${lastMsgId}`;
            } else {
                lastMsgId = 0; // Reset nếu là lần đầu bấm vào
                lastRenderedDate = '';
            }

            $.get(url, function(res) {
                if (res.success && res.messages.length > 0) {
                    const box = $('#chatMessages');
                    if (isInitial) box.empty();

                    const shouldScroll = isInitial || (box.scrollTop() + box.innerHeight() >= box[0].scrollHeight - 50);

                    res.messages.forEach(m => {
                        const msgDateObj = new Date(m.created_at.replace(' ', 'T'));
                        const msgDateStr = msgDateObj.toLocaleDateString('vi-VN');
                        
                        if (msgDateStr !== lastRenderedDate) {
                            const todayStr = new Date().toLocaleDateString('vi-VN');
                            const yesterdayObj = new Date();
                            yesterdayObj.setDate(yesterdayObj.getDate() - 1);
                            const yesterdayStr = yesterdayObj.toLocaleDateString('vi-VN');

                            let displayDate = msgDateStr;
                            if (msgDateStr === todayStr) displayDate = 'Hôm nay';
                            else if (msgDateStr === yesterdayStr) displayDate = 'Hôm qua';

                            box.append(`<div class="chat-date-separator">${displayDate}</div>`);
                            lastRenderedDate = msgDateStr;
                        }

                        const isCustomer = m.sender === 'customer';
                        const wrapperClass = isCustomer ? 'customer' : 'admin';
                        const time = new Date(m.created_at).toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'});
                        
                        let adminNameHtml = '';
                        if (!isCustomer && m.admin_name) {
                            adminNameHtml = `<div class="msg-admin-name"><i class="fas fa-headset me-1"></i>${m.admin_name}</div>`;
                        }

                        const html = `
                            <div class="msg-wrapper ${wrapperClass}">
                                ${adminNameHtml}
                                <div class="msg-bubble">${formatChatMsg(m.message)}</div>
                                <div class="msg-time">${time}</div>
                            </div>
                        `;
                        box.append(html);
                        lastMsgId = m.id;
                    });

                    if (shouldScroll) box.animate({ scrollTop: box[0].scrollHeight }, 300);
                }
            });
        }

        // 5. GỬI TIN NHẮN
        function sendMessage() {
            const msg = $('#chatInput').val().trim();
            if (!msg || !currentSessionId) return;
            
            $('#chatInput').val(''); // Clear UI immediately
            
            $.post(apiUrl, { action: 'send_message', session_id: currentSessionId, sender: 'admin', message: msg }, function(res) {
                if (res.success) {
                    loadMessages(false);
                    loadSessions();
                    if (ws && ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({ event: 'new_message', session_id: currentSessionId }));
                } else alert('Lỗi: ' + res.error);
            });
        }

        $('#btnSend').click(sendMessage);
        $('#chatInput').keypress(e => { if (e.which == 13) sendMessage(); });

        // 6. GỬI FILE (ẢNH/VIDEO/TÀI LIỆU)
        $('#btnAttach').click(() => $('#attachFile').click());
        $('#attachFile').change(function() {
            if(this.files.length > 0 && currentSessionId) {
                const file = this.files[0];
                const fd = new FormData();
                fd.append('action', 'send_message');
                fd.append('session_id', currentSessionId);
                fd.append('sender', 'admin');
                fd.append('file', file);
                
                // Hỗ trợ hiển thị loading tạm thời nếu cần (để đơn giản ta gọi api luôn)
                $.ajax({
                    url: apiUrl, type: 'POST', data: fd, processData: false, contentType: false,
                    success: function(res) {
                        if(res.success) {
                            loadMessages(false);
                            if (ws && ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({ event: 'new_message', session_id: currentSessionId }));
                        } else alert('Lỗi gửi file: ' + res.error);
                    }
                });
                this.value = ''; // Reset
            }
        });

        // Init
        loadSessions();
    </script>
</body>
</html>