<link rel="stylesheet" href="css/chat-widget.css">

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