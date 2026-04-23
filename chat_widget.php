<style>
    /* Giao diện khung Chat nổi */
    .futa-chat-btn {
        position: fixed;
        bottom: 60px;
        right: 30px;
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #004aad, #007bff);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(0,74,173,0.4);
        z-index: 9999;
        transition: transform 0.3s ease;
    }
    .futa-chat-btn:hover {
        transform: scale(1.1);
    }
    
    .futa-chat-box {
        position: fixed;
        bottom: 130px;
        right: 30px;
        width: 350px;
        height: 480px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        display: none;
        flex-direction: column;
        z-index: 9999;
        overflow: hidden;
        font-family: inherit;
    }
    
    .futa-chat-header {
        background: linear-gradient(135deg, #004aad, #007bff);
        color: white;
        padding: 15px 20px;
        font-weight: 600;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }
    
    .futa-chat-header .header-actions i {
        cursor: pointer;
        font-size: 18px;
        margin-left: 15px;
        transition: transform 0.2s, opacity 0.2s;
    }
    .futa-chat-header .header-actions i:hover {
        opacity: 0.8;
        transform: scale(1.1);
    }

    .futa-chat-body {
        flex: 1;
        padding: 15px;
        overflow-y: auto;
        background: #f4f6f9;
        display: flex;
        flex-direction: column;
        gap: 10px;
        scroll-behavior: smooth;
    }
    
    .futa-chat-message {
        max-width: 80%;
        padding: 10px 14px;
        border-radius: 15px;
        font-size: 14.5px;
        line-height: 1.4;
        word-wrap: break-word;
    }
    .futa-chat-message.customer {
        background: #004aad;
        color: white;
        align-self: flex-end;
        border-bottom-right-radius: 4px;
    }
    .futa-chat-message.admin {
        background: #e9ecef;
        color: #333;
        align-self: flex-start;
        border-bottom-left-radius: 4px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    .futa-chat-time {
        font-size: 10px;
        margin-top: 5px;
        opacity: 0.7;
        text-align: right;
    }
    
    .futa-chat-date-separator {
        text-align: center;
        font-size: 11px;
        color: #888;
        margin: 10px 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .futa-chat-date-separator::before,
    .futa-chat-date-separator::after {
        content: "";
        flex: 1;
        height: 1px;
        background: #e0e0e0;
        margin: 0 10px;
    }

    .futa-chat-input-area {
        display: flex;
        padding: 10px;
        border-top: 1px solid #ddd;
        background: #fff;
        align-items: center;
        gap: 5px;
    }
    .futa-chat-input-area input[type="text"] {
        flex: 1;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 20px;
        outline: none;
        transition: border-color 0.3s;
    }
    .futa-chat-input-area input[type="text"]:focus {
        border-color: #007bff;
    }
    .futa-chat-input-area button {
        background: transparent;
        color: #004aad;
        border: none;
        font-size: 20px;
        padding: 0 5px;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .futa-chat-input-area button:hover {
        transform: scale(1.1);
    }
    
    /* Tùy chỉnh form nhập thông tin */
    #chatRegisterForm input {
        border-radius: 8px;
        padding: 12px 15px;
        border: 1px solid #e0e0e0;
        font-size: 14px;
        transition: all 0.3s;
    }
    #chatRegisterForm input:focus {
        border-color: #004aad;
        box-shadow: 0 0 0 3px rgba(0, 74, 173, 0.1);
    }
    #startChatBtn {
        border-radius: 8px;
        padding: 12px;
        font-weight: bold;
        letter-spacing: 0.5px;
    }

    /* Responsive cho Mobile */
    @media (max-width: 576px) {
        .futa-chat-btn {
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            font-size: 24px;
        }
        .futa-chat-box {
            width: calc(100% - 40px);
            right: 20px;
            bottom: 80px;
            height: 450px;
            max-height: calc(100vh - 100px);
        }
    }
</style>

<!-- Khung chứa HTML -->
<div class="futa-chat-btn" id="futaChatBtn"><i class="bi bi-chat-dots-fill"></i></div>

<div class="futa-chat-box" id="futaChatBox">
    <div class="futa-chat-header">
        <span><i class="bi bi-headset me-2"></i> <span data-i18n="about.chat_title">Hỗ trợ trực tuyến</span></span>
        <div class="header-actions">
            <i class="bi bi-arrow-clockwise" id="resetChatBtn" title="Bắt đầu phiên chat mới" style="display:none;"></i>
            <i class="bi bi-x-lg" id="closeChatBox"></i>
        </div>
    </div>

    <!-- Form xin thông tin lần đầu -->
    <div id="chatRegisterForm" style="padding: 25px 20px; display: flex; flex-direction: column; gap: 15px; background: #fff; flex: 1;">
        <div class="text-center mb-2">
            <i class="bi bi-chat-square-text text-primary" style="font-size: 40px;"></i>
            <p class="text-muted small mt-2 mb-0" data-i18n="about.chat_form_title">Vui lòng để lại thông tin để chúng tôi hỗ trợ bạn tốt nhất:</p>
        </div>
        <input type="text" id="chatName" class="form-control" data-i18n-placeholder="about.chat_name_placeholder" placeholder="Họ và tên *" required>
        <input type="text" id="chatPhone" class="form-control" data-i18n-placeholder="about.chat_phone_placeholder" placeholder="Số điện thoại *" required>
        <input type="email" id="chatEmail" class="form-control" data-i18n-placeholder="about.chat_email_placeholder" placeholder="Email (Không bắt buộc)">
        <button class="btn btn-primary w-100 mt-3" id="startChatBtn" data-i18n="about.chat_submit">Bắt đầu trò chuyện</button>
    </div>

    <!-- Khu vực chat chính -->
    <div id="chatActiveArea" style="display: none; flex-direction: column; flex: 1; min-height: 0;">
        <div class="futa-chat-body" id="futaChatMessages">
            <!-- Tin nhắn sẽ được load ở đây -->
            <div class="futa-chat-message admin" data-i18n="about.chat_welcome">Xin chào! Chúng tôi là FUTA Advertising. Tôi có thể giúp gì cho bạn?</div>
        </div>
        <div class="futa-chat-input-area">
            <input type="file" id="chatAttachFile" style="display: none;">
            <button id="attachChatBtn" title="Đính kèm file"><i class="bi bi-paperclip"></i></button>
            <input type="text" id="chatInputMsg" data-i18n-placeholder="about.chat_input_placeholder" placeholder="Nhập tin nhắn..." autocomplete="off">
            <button id="sendChatBtn"><i class="bi bi-send-fill"></i></button>
        </div>
    </div>
</div>

<!-- Logic JavaScript Vanilla -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const chatBtn = document.getElementById('futaChatBtn');
    const chatBox = document.getElementById('futaChatBox');
    const closeBox = document.getElementById('closeChatBox');
    const resetBtn = document.getElementById('resetChatBtn');
    const registerForm = document.getElementById('chatRegisterForm');
    const activeArea = document.getElementById('chatActiveArea');
    const msgContainer = document.getElementById('futaChatMessages');
    const chatInput = document.getElementById('chatInputMsg');
    const sendBtn = document.getElementById('sendChatBtn');
    const attachBtn = document.getElementById('attachChatBtn');
    const attachInput = document.getElementById('chatAttachFile');

    let sessionId = localStorage.getItem('futa_chat_session') || null;
    let customerName = localStorage.getItem('futa_chat_name') || '';
    let lastMsgId = 0;
    let lastRenderedDate = '';
    
    // --- KẾT NỐI WEBSOCKET ---
    let ws = null;
    let isWsConnected = false;
    function connectWebSocket() {
        ws = new WebSocket('ws://localhost:8080');
        ws.onopen = () => { isWsConnected = true; };
        ws.onmessage = (e) => {
            const data = JSON.parse(e.data);
            if (data.event === 'new_message' && data.session_id == sessionId) {
                fetchMessages(); // Nhận được tín hiệu có tin nhắn mới -> Tải hiển thị ngay lập tức
            }
        };
        ws.onerror = (e) => console.error('WebSocket Error:', e);
        ws.onclose = () => {
            isWsConnected = false;
            setTimeout(connectWebSocket, 5000); // Tự động kết nối lại sau 5s nếu mất kết nối
        };
    }
    connectWebSocket();

    // --- FALLBACK POLLING ---
    // Nếu WebSocket chưa chạy hoặc bị lỗi, tự động lấy tin nhắn mới mỗi 3 giây
    setInterval(() => {
        if (sessionId && !isWsConnected) {
            fetchMessages();
        }
    }, 3000);

    // Bật tắt khung chat
    chatBtn.addEventListener('click', () => {
        chatBox.style.display = chatBox.style.display === 'flex' ? 'none' : 'flex';
        if(chatBox.style.display === 'flex') {
            if(sessionId) {
                registerForm.style.display = 'none';
                activeArea.style.display = 'flex';
                // Hiển thị tên khách hàng lên tiêu đề nếu đã từng nhập
                if (customerName) {
                    document.querySelector('.futa-chat-header span').innerHTML = `<i class="bi bi-person-circle me-2"></i> Xin chào, ${customerName}`;
                }
                resetBtn.style.display = 'inline-block';
                fetchMessages();
            } else {
                registerForm.style.display = 'flex';
                activeArea.style.display = 'none';
            }
        }
    });

    closeBox.addEventListener('click', () => {
        chatBox.style.display = 'none';
    });

    // Xử lý nút Làm mới (Reset) phiên chat
    resetBtn.addEventListener('click', () => {
        if (confirm('Bạn có muốn kết thúc phiên chat hiện tại và bắt đầu lại không?')) {
            localStorage.removeItem('futa_chat_session');
            localStorage.removeItem('futa_chat_name');
            sessionId = null;
            customerName = '';
            lastRenderedDate = '';
            msgContainer.innerHTML = '<div class="futa-chat-message admin" data-i18n="about.chat_welcome">Xin chào! Chúng tôi là FUTA Advertising. Tôi có thể giúp gì cho bạn?</div>';
            registerForm.style.display = 'flex';
            activeArea.style.display = 'none';
            resetBtn.style.display = 'none';
            document.querySelector('.futa-chat-header span').innerHTML = `<i class="bi bi-headset me-2"></i> <span data-i18n="about.chat_title">Hỗ trợ trực tuyến</span>`;
            if (window.i18n) window.i18n.setLanguage(window.i18n.getLanguage()); // Cập nhật lại ngôn ngữ
        }
    });

    // Bắt đầu phiên chat mới
    document.getElementById('startChatBtn').addEventListener('click', async () => {
        const name = document.getElementById('chatName').value.trim();
        const phone = document.getElementById('chatPhone').value.trim();
        const email = document.getElementById('chatEmail').value.trim();
        if(!name || !phone) return alert('Vui lòng nhập Họ tên và Số điện thoại!');

        const fd = new URLSearchParams();
        fd.append('action', 'start_session'); fd.append('name', name); fd.append('phone', phone); fd.append('email', email);
        
        const res = await fetch('/FUTA_PHP/includes/contact-chat-api.php', { method: 'POST', body: fd }).then(r => r.json());
        if(res.success) {
            sessionId = res.session_id;
            customerName = name; // Lưu lại tên khách
            localStorage.setItem('futa_chat_session', sessionId);
            localStorage.setItem('futa_chat_name', customerName);
            registerForm.style.display = 'none';
            activeArea.style.display = 'flex';
            document.querySelector('.futa-chat-header span').innerHTML = `<i class="bi bi-person-circle me-2"></i> Xin chào, ${customerName}`;
            resetBtn.style.display = 'inline-block';
            if (ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({ event: 'new_session' })); // Bắn tín hiệu cho Admin biết có khách mới
            }
            
            fetchMessages(true); // Load ngay lịch sử chat cũ (nếu có)
        }
    });

    // Gửi và Lấy tin nhắn
    const sendMessage = async () => {
        const msg = chatInput.value.trim();
        if(!msg || !sessionId) return;
        
        chatInput.value = ''; // Clear ngay để tạo cảm giác mượt
        
        // Hiển thị tin nhắn tạm thời (Optimistic UI)
        const tempDiv = document.createElement('div');
        tempDiv.className = 'futa-chat-message customer temp-msg';
        tempDiv.textContent = msg;
        tempDiv.style.opacity = '0.6';
        msgContainer.appendChild(tempDiv);
        msgContainer.scrollTop = msgContainer.scrollHeight;

        const fd = new URLSearchParams();
        fd.append('action', 'send_message'); fd.append('session_id', sessionId); fd.append('sender', 'customer'); fd.append('message', msg);
        
        try {
            const res = await fetch('/FUTA_PHP/includes/contact-chat-api.php', { method: 'POST', body: fd }).then(r => r.json());
            if(res.success) {
                tempDiv.remove(); // Xóa tin tạm
                await fetchMessages(true); // Load tin thật & force scroll
                if (ws && ws.readyState === WebSocket.OPEN) {
                    ws.send(JSON.stringify({ event: 'new_message', session_id: sessionId })); // Bắn tín hiệu WebSocket cho Admin
                }
            }
        } catch (e) {
            console.error(e);
            tempDiv.style.border = '1px solid red';
        }
    };

    sendBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', (e) => e.key === 'Enter' && sendMessage());

    // Xử lý gửi file
    attachBtn.addEventListener('click', () => attachInput.click());
    attachInput.addEventListener('change', async function() {
        if(this.files.length > 0 && sessionId) {
            const file = this.files[0];
            
            let maxSize = 15 * 1024 * 1024; // Mặc định 15MB
            if (file.type.startsWith('video/')) maxSize = 30 * 1024 * 1024;
            else if (file.type.startsWith('image/')) maxSize = 5 * 1024 * 1024;
            
            if (file.size > maxSize) return alert('File quá lớn (Ảnh: Tối đa 5MB, Video: Tối đa 30MB, File khác: Tối đa 15MB)');
            
            const tempDiv = document.createElement('div');
            tempDiv.className = 'futa-chat-message customer temp-msg';
            tempDiv.innerHTML = '<i class="bi bi-hourglass-split"></i> Đang gửi file...';
            tempDiv.style.opacity = '0.6';
            msgContainer.appendChild(tempDiv);
            msgContainer.scrollTop = msgContainer.scrollHeight;

            const fd = new FormData();
            fd.append('action', 'send_message');
            fd.append('session_id', sessionId);
            fd.append('sender', 'customer');
            fd.append('file', file);
            
            try {
                const res = await fetch('/FUTA_PHP/includes/contact-chat-api.php', { method: 'POST', body: fd }).then(r => r.json());
                if(res.success) {
                    tempDiv.remove();
                    await fetchMessages(true);
                    if (ws && ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({ event: 'new_message', session_id: sessionId }));
                } else {
                    tempDiv.innerHTML = '<i class="bi bi-exclamation-circle text-danger"></i> ' + res.error;
                }
            } catch (e) {
                tempDiv.style.border = '1px solid red';
            }
            this.value = ''; 
        }
    });

    // Hàm Format hiển thị ảnh/video/tài liệu
    const formatChatMessage = (rawMsg) => {
        if (rawMsg.startsWith('FILE::')) {
            const parts = rawMsg.split('::');
            if (parts.length >= 4) {
                const mime = parts[1]; const url = parts[2]; const name = parts[3];
                if (mime.startsWith('image/')) {
                    return `<a href="${url}" target="_blank"><img src="${url}" alt="${name}" style="max-width: 100%; border-radius: 8px; margin-top: 5px;"></a>`;
                } else if (mime.startsWith('video/')) {
                    return `<video controls src="${url}" style="max-width: 100%; border-radius: 8px; margin-top: 5px;"></video>`;
                } else {
                    return `<a href="${url}" target="_blank" style="text-decoration: none; color: inherit; display:flex; align-items:center; gap:5px;"><i class="bi bi-file-earmark-arrow-down fs-5"></i> ${name}</a>`;
                }
            }
        }
        const div = document.createElement('div');
        div.textContent = rawMsg;
        return div.innerHTML.replace(/\n/g, '<br>');
    };

    const fetchMessages = async (forceScroll = false) => {
        if(!sessionId) return;
        const t = new Date().getTime(); // Chống cache
        const res = await fetch(`/FUTA_PHP/includes/contact-chat-api.php?action=get_messages&session_id=${sessionId}&last_id=${lastMsgId}&t=${t}`).then(r => r.json());
        if(res.success && res.messages.length > 0) {
            const shouldScroll = forceScroll || (msgContainer.scrollTop + msgContainer.clientHeight >= msgContainer.scrollHeight - 50);
            
            // Nếu đang tải lần đầu và có lịch sử tin nhắn cũ, xóa câu chào mặc định đi
            if (lastMsgId === 0) {
                msgContainer.innerHTML = '';
                lastRenderedDate = '';
            }

            res.messages.forEach(m => {
                const msgDateObj = new Date(m.created_at.replace(' ', 'T'));
                const msgDateStr = msgDateObj.toLocaleDateString('vi-VN');
                const msgTimeStr = msgDateObj.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'});

                if (msgDateStr !== lastRenderedDate) {
                    const dateDiv = document.createElement('div');
                    dateDiv.className = 'futa-chat-date-separator';
                    
                    const todayStr = new Date().toLocaleDateString('vi-VN');
                    const yesterdayObj = new Date();
                    yesterdayObj.setDate(yesterdayObj.getDate() - 1);
                    const yesterdayStr = yesterdayObj.toLocaleDateString('vi-VN');

                    if (msgDateStr === todayStr) dateDiv.textContent = 'Hôm nay';
                    else if (msgDateStr === yesterdayStr) dateDiv.textContent = 'Hôm qua';
                    else dateDiv.textContent = msgDateStr;
                    
                    msgContainer.appendChild(dateDiv);
                    lastRenderedDate = msgDateStr;
                }

                const div = document.createElement('div');
                div.className = `futa-chat-message ${m.sender}`;
                div.innerHTML = formatChatMessage(m.message) + `<div class="futa-chat-time">${msgTimeStr}</div>`;
                msgContainer.appendChild(div);
                lastMsgId = m.id;
            });
            if (shouldScroll) {
                msgContainer.scrollTop = msgContainer.scrollHeight;
            }
        }
    };
});
</script>