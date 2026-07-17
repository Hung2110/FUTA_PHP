<style>
    /* --- Giao diện khung Chat nổi --- */
    .futa-chat-btn {
        position: fixed;
        bottom: 60px;
        right: 30px; /* Giữ nguyên vị trí */
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
        box-shadow: 0 6px 20px rgba(0, 74, 173, 0.4);
        z-index: 9999;
        transition: all 0.3s ease;
        animation: futa-pulse 2s infinite;
    }
    .futa-chat-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 8px 25px rgba(0, 74, 173, 0.5);
        animation: none;
    }
    
    .futa-chat-box {
        position: fixed;
        bottom: 130px;
        right: 30px; /* Giữ nguyên vị trí */
        width: 90%; /* Tự co giãn chiều rộng */
        max-width: 360px; /* Nhưng không vượt quá 360px trên PC */
        height: 60vh; /* Chiều cao tự động bằng 60% màn hình */
        min-height: 400px; /* Không được nhỏ hơn 400px để tránh mất nút bấm */
        max-height: calc(100vh - 160px); /* Không vượt quá màn hình để không bị mất viền trên */
        background: #fff;
        border-radius: 16px; /* Bo góc mềm mại hơn */
        box-shadow: 0 12px 40px rgba(0,0,0,0.2); /* Đổ bóng sâu và rõ hơn */
        display: none;
        flex-direction: column;
        z-index: 9999;
        overflow: hidden;
        font-family: Arial, Helvetica, sans-serif;
        transform-origin: bottom right;
        transform: scale(0.95) translateY(10px);
        opacity: 0;
        transition: transform 0.3s ease, opacity 0.3s ease;
    }
    .futa-chat-box.show {
        display: flex;
        transform: scale(1) translateY(0);
        opacity: 1;
    }
    
    .futa-chat-header {
        background: linear-gradient(135deg, #004aad, #007bff);
        color: white;
        padding: 16px 20px; /* Tăng padding cho thoáng */
        font-weight: 600;
        font-size: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top-left-radius: 16px; /* Bo góc tương ứng với box */
        border-top-right-radius: 16px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1); /* Bóng đổ nhẹ cho header */
        z-index: 10; /* Đảm bảo header luôn ở trên */
    }
    
    .futa-chat-header .header-actions i {
        cursor: pointer;
        font-size: 18px; /* Kích thước icon vừa phải */
        margin-left: 15px;
        transition: transform 0.2s, opacity 0.2s;
    }
    .futa-chat-header .header-actions i:hover {
        opacity: 0.8;
        transform: scale(1.1);
    }
    
    .futa-header-title {
        display: flex;
        flex-direction: column;
    }
    .futa-header-title .title-main {
        font-weight: 600;
        font-size: 16px;
        line-height: 1.3;
    }
    .futa-header-title .title-sub {
        font-size: 12px;
        opacity: 0.85;
        font-weight: 400;
        display: flex; /* Thêm flex để căn chỉnh chấm xanh và chữ */
        align-items: center; /* Căn giữa theo chiều dọc */
    }

    .futa-header-title .title-sub::before {
        content: '';
        display: inline-block;
        width: 8px;
        height: 8px;
        background-color: #28a745; /* Màu xanh lá */
        border-radius: 50%;
        margin-right: 6px;
        animation: futa-pulse-green 1.5s infinite; /* Hiệu ứng nhấp nháy */
        box-shadow: 0 0 5px rgba(40, 167, 69, 0.7);
    }

    .futa-chat-body {
        flex: 1;
        padding: 20px 15px; /* Tăng padding dọc */
        overflow-y: auto;
        background: #f8f9fa; /* Nền xám nhạt dễ chịu */
        display: flex;
        flex-direction: column;
        gap: 12px; /* Khoảng cách giữa các tin nhắn */
        scroll-behavior: smooth;
    }
    
    .futa-chat-message {
        max-width: 85%;
        padding: 12px 16px; /* Padding bên trong tin nhắn */
        border-radius: 18px; /* Bo góc tròn trịa */
        font-size: 14.5px;
        line-height: 1.5;
        word-wrap: break-word;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05); /* Bóng đổ nhẹ cho tin nhắn */
    }
    .futa-chat-message.customer {
        background: linear-gradient(135deg, #007bff, #004aad); /* Gradient màu FUTA */
        color: white;
        align-self: flex-end;
        border-bottom-right-radius: 4px; /* Bo góc đặc trưng */
    }
    .futa-chat-message.admin {
        background: #ffffff;
        color: #333;
        align-self: flex-start;
        border-bottom-left-radius: 4px; /* Bo góc đặc trưng */
        border: 1px solid #eaeaea; /* Viền nhẹ cho dễ phân biệt */
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
        padding: 15px; /* Padding cho vùng nhập liệu */
        border-top: 1px solid #eaeaea;
        background: #fff;
        align-items: center;
        gap: 10px;
    }
    .futa-chat-input-area input[type="text"] {
        flex: 1;
        padding: 12px 18px; /* Padding cho input */
        border: 1px solid #e0e0e0;
        border-radius: 25px; /* Bo tròn hoàn toàn */
        background: #f4f6f9; /* Nền xám nhạt */
        outline: none;
        transition: all 0.3s;
        font-size: 14.5px;
    }
    .futa-chat-input-area input[type="text"]:focus {
        background: #fff;
        border-color: #007bff; /* Hiệu ứng focus màu FUTA */
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1); /* Vòng sáng mờ */
    }
    .futa-chat-input-area button {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; /* Kích thước icon nút */
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .futa-chat-input-area #sendChatBtn {
        background: linear-gradient(135deg, #004aad, #007bff); /* Gradient cho nút gửi */
        color: white;
        box-shadow: 0 4px 10px rgba(0, 123, 255, 0.3);
    }
    .futa-chat-input-area #sendChatBtn:hover {
        transform: scale(1.1); /* Hiệu ứng phóng to khi hover */
        box-shadow: 0 6px 15px rgba(0, 123, 255, 0.4);
    }
    .futa-chat-input-area #attachChatBtn {
        background: #f0f2f5;
        color: #666;
    }
    .futa-chat-input-area #attachChatBtn:hover {
        background: #e2e6ea;
        color: #004aad;
    }
    
    /* Tùy chỉnh form nhập thông tin */
    #chatRegisterForm {
        padding: 25px !important; /* Padding cho form đăng ký */
        overflow-y: auto; /* Thêm thanh cuộn khi nội dung bị tràn (quan trọng nhất) */
        flex-shrink: 0; /* Ngăn form bị co lại quá mức */
    }
    #chatRegisterForm .bi-chat-square-text {
        font-size: 42px !important;
        background: -webkit-linear-gradient(135deg, #004aad, #007bff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    #chatRegisterForm input, #chatRegisterForm textarea {
        border-radius: 12px;
        padding: 14px 15px; /* Padding cho các ô input */
        background: #f8f9fa;
        border: 1px solid #e0e0e0;
        font-size: 14.5px;
        transition: all 0.3s;
    }
    #chatRegisterForm input:focus, #chatRegisterForm textarea:focus {
        background: #fff; /* Hiệu ứng focus */
        border-color: #004aad;
        box-shadow: 0 0 0 3px rgba(0, 74, 173, 0.1);
    }
    #startChatBtn {
        border-radius: 12px !important;
        padding: 14px !important; /* Nút bấm to, rõ ràng */
        background: linear-gradient(135deg, #004aad, #007bff) !important;
        border: none !important;
        font-size: 16px;
        box-shadow: 0 4px 15px rgba(0, 74, 173, 0.3);
        font-weight: bold; /* Chữ đậm */
        letter-spacing: 0.5px;
        transition: all 0.3s;
    }
    #startChatBtn:hover {
        transform: translateY(-2px); /* Hiệu ứng nhấc lên khi hover */
        box-shadow: 0 6px 20px rgba(0, 74, 173, 0.4);
    }

    @keyframes futa-pulse {
        0% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7); }
        70% { box-shadow: 0 0 0 15px rgba(0, 123, 255, 0); }
        100% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0); }
    }

    @keyframes futa-pulse-green {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.2); opacity: 0.7; }
        100% { transform: scale(1); opacity: 1; }
    }

    /* Responsive cho Mobile */
    @media (max-width: 576px) {
        .futa-chat-btn {
            bottom: 20px;
            right: 20px;
            width: 55px;
            height: 55px;
            font-size: 24px;
        }
        .futa-chat-box {
            width: calc(100% - 40px);
            right: 20px;
            bottom: 85px;
            height: auto; /* Cho phép chiều cao tự động co giãn */
            max-height: 70vh; /* Giới hạn chiều cao tối đa, tự động co lại khi bàn phím hiện */
        }
        #chatRegisterForm {
            padding: 20px !important;
        }
        #chatRegisterForm .bi-chat-square-text {
            font-size: 38px !important;
        }
        #chatRegisterForm input, #chatRegisterForm textarea {
            padding: 12px 15px;
            font-size: 14px;
        }
    }
</style>

<!-- Khung chứa HTML -->
<div class="futa-chat-btn" id="futaChatBtn" title="Hỗ trợ trực tuyến"><i class="bi bi-chat-dots-fill"></i></div>

<div class="futa-chat-box" id="futaChatBox">
    <div class="futa-chat-header">
        <div class="futa-header-title">
            <div class="title-main" data-i18n="about.chat_title">FUTA ADVERTISING</div>
            <div class="title-sub" data-i18n="about.chat_subtitle">Chúng tôi sẽ trả lời sớm nhất có thể</div>
        </div>
        <div class="header-actions">
            <i class="bi bi-arrow-clockwise" id="resetChatBtn" title="Bắt đầu phiên chat mới" style="display:none;"></i>
            <i class="bi bi-x-lg" id="closeChatBox"></i>
        </div>
    </div>

    <!-- Khu vực nội dung chung (bao gồm cả form và chat) -->
    <div class="futa-chat-body" id="futaChatBody">
        <!-- Form xin thông tin lần đầu (sẽ bị ẩn sau khi đăng ký) -->
        <div id="chatRegisterContainer" style="display: flex; flex-direction: column; gap: 15px;">
            <div class="futa-chat-message admin" data-i18n="about.chat_welcome">Để được FUTA Advertising hỗ trợ nhanh nhất, bạn vui lòng để lại thông tin bên dưới nhé.</div>
            <input type="text" id="chatName" class="form-control" data-i18n-placeholder="about.chat_name_placeholder" placeholder="Họ và tên *" required>
            <input type="text" id="chatPhone" class="form-control" data-i18n-placeholder="about.chat_phone_placeholder" placeholder="Số điện thoại *" required>
            <input type="email" id="chatEmail" class="form-control" data-i18n-placeholder="about.chat_email_placeholder" placeholder="Email (Không bắt buộc)">
            <textarea id="chatInitialMessage" class="form-control" rows="2" data-i18n-placeholder="about.chat_initial_message_placeholder" placeholder="Nội dung cần tư vấn..."></textarea>
            <button class="btn btn-primary w-100" id="startChatBtn" data-i18n="about.chat_submit">Gửi yêu cầu</button>
        </div>

        <!-- Vùng hiển thị tin nhắn (sẽ hiện sau khi đăng ký) -->
        <div id="futaChatMessages" style="display: none; flex-direction: column; gap: 12px; padding: 0 5px;">
            <!-- Tin nhắn sẽ được load ở đây -->
        </div>
    </div>

    <!-- Vùng nhập liệu (sẽ hiện sau khi đăng ký) -->
    <div class="futa-chat-input-area" id="chatInputArea" style="display: none;">
        <input type="file" id="chatAttachFile" style="display: none;">
        <button id="attachChatBtn" title="Đính kèm file"><i class="bi bi-paperclip"></i></button>
        <input type="text" id="chatInputMsg" data-i18n-placeholder="about.chat_input_placeholder" placeholder="Nhập tin nhắn..." autocomplete="off">
        <button id="sendChatBtn"><i class="bi bi-send-fill"></i></button>
    </div>
</div>