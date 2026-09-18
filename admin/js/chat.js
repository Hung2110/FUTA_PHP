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

// Nút quay lại danh sách trên mobile
$('#btnBackToSessions').on('click', function() {
    $('#chatMain').hide();
    currentSessionId = null;
    $('.session-item').removeClass('active');
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

