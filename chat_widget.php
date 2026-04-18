<style>
    /* Giao diện khung Chat nổi */
    .futa-chat-btn {
        position: fixed;
        bottom: 30px;
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
        bottom: 100px;
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
    }
    
    .futa-chat-header i {
        cursor: pointer;
        font-size: 20px;
    }

    .futa-chat-body {
        flex: 1;
        padding: 15px;
        overflow-y: auto;
        background: #f4f6f9;
        display: flex;
        flex-direction: column;
        gap: 10px;
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
    }

    .futa-chat-input-area {
        display: flex;
        padding: 10px;
        border-top: 1px solid #ddd;
        background: #fff;
    }
    .futa-chat-input-area input {
        flex: 1;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 20px;
        outline: none;
    }
    .futa-chat-input-area button {
        background: transparent;
        color: #004aad;
        border: none;
        font-size: 20px;
        padding: 0 10px;
        cursor: pointer;
    }
</style>

<!-- Khung chứa HTML -->
<div class="futa-chat-btn" id="futaChatBtn"><i class="bi bi-chat-dots-fill"></i></div>

<div class="futa-chat-box" id="futaChatBox">
    <div class="futa-chat-header">
        <span><i class="bi bi-headset me-2"></i> <span data-i18n="about.chat_title">Hỗ trợ trực tuyến</span></span>
        <i class="bi bi-x-lg" id="closeChatBox"></i>
    </div>

    <!-- Form xin thông tin lần đầu -->
    <div id="chatRegisterForm" style="padding: 20px; display: flex; flex-direction: column; gap: 15px;">
        <p class="text-muted small mb-0" data-i18n="about.chat_form_title">Vui lòng để lại thông tin để chúng tôi hỗ trợ bạn tốt nhất:</p>
        <input type="text" id="chatName" class="form-control" data-i18n-placeholder="about.chat_name_placeholder" placeholder="Họ và tên *" required>
        <input type="text" id="chatPhone" class="form-control" data-i18n-placeholder="about.chat_phone_placeholder" placeholder="Số điện thoại *" required>
        <input type="email" id="chatEmail" class="form-control" data-i18n-placeholder="about.chat_email_placeholder" placeholder="Email (Không bắt buộc)">
        <button class="btn btn-primary w-100 mt-2" id="startChatBtn" data-i18n="about.chat_submit">Gửi thông tin</button>
    </div>

    <!-- Khu vực chat chính -->
    <div id="chatActiveArea" style="display: none; flex-direction: column; height: 100%;">
        <div class="futa-chat-body" id="futaChatMessages">
            <!-- Tin nhắn sẽ được load ở đây -->
            <div class="futa-chat-message admin" data-i18n="about.chat_welcome">Xin chào! Chúng tôi là FUTA Advertising. Tôi có thể giúp gì cho bạn?</div>
        </div>
        <div class="futa-chat-input-area">
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
    const registerForm = document.getElementById('chatRegisterForm');
    const activeArea = document.getElementById('chatActiveArea');
    const msgContainer = document.getElementById('futaChatMessages');
    const chatInput = document.getElementById('chatInputMsg');
    const sendBtn = document.getElementById('sendChatBtn');

    let sessionId = localStorage.getItem('futa_chat_session') || null;
    let lastMsgId = 0;
    
    // --- KẾT NỐI WEBSOCKET ---
    let ws = null;
    function connectWebSocket() {
        ws = new WebSocket('ws://localhost:8080');
        ws.onmessage = (e) => {
            const data = JSON.parse(e.data);
            if (data.event === 'new_message' && data.session_id == sessionId) {
                fetchMessages(); // Nhận được tín hiệu có tin nhắn mới -> Tải hiển thị ngay lập tức
            }
        };
        ws.onerror = (e) => console.error('WebSocket Error:', e);
        ws.onclose = () => setTimeout(connectWebSocket, 5000); // Tự động kết nối lại sau 5s nếu mất kết nối
    }
    connectWebSocket();

    // Bật tắt khung chat
    chatBtn.addEventListener('click', () => {
        chatBox.style.display = chatBox.style.display === 'flex' ? 'none' : 'flex';
        if(chatBox.style.display === 'flex') {
            if(sessionId) {
                registerForm.style.display = 'none';
                activeArea.style.display = 'flex';
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
            localStorage.setItem('futa_chat_session', sessionId);
            registerForm.style.display = 'none';
            activeArea.style.display = 'flex';
            if (ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({ event: 'new_session' })); // Bắn tín hiệu cho Admin biết có khách mới
            }
        }
    });

    // Gửi và Lấy tin nhắn
    const sendMessage = async () => {
        const msg = chatInput.value.trim();
        if(!msg || !sessionId) return;
        
        chatInput.value = ''; // Clear ngay để tạo cảm giác mượt
        const fd = new URLSearchParams();
        fd.append('action', 'send_message'); fd.append('session_id', sessionId); fd.append('sender', 'customer'); fd.append('message', msg);
        await fetch('/FUTA_PHP/includes/contact-chat-api.php', { method: 'POST', body: fd });
        fetchMessages();
        if (ws && ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify({ event: 'new_message', session_id: sessionId })); // Bắn tín hiệu WebSocket cho Admin
        }
    };

    sendBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', (e) => e.key === 'Enter' && sendMessage());

    const fetchMessages = async () => {
        if(!sessionId) return;
        const res = await fetch(`/FUTA_PHP/includes/contact-chat-api.php?action=get_messages&session_id=${sessionId}&last_id=${lastMsgId}`).then(r => r.json());
        if(res.success && res.messages.length > 0) {
            res.messages.forEach(m => {
                const div = document.createElement('div');
                div.className = `futa-chat-message ${m.sender}`;
                div.textContent = m.message;
                msgContainer.appendChild(div);
                lastMsgId = m.id;
            });
            msgContainer.scrollTop = msgContainer.scrollHeight;
        }
    };
});
</script>