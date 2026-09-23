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
    <link rel="stylesheet" href="css/chat.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="page-header">
            <div>
                <h1><i class="fas fa-comments text-primary me-2"></i>Quản Lý Chat Trực Tuyến</h1>
                <p class="mb-0">Hỗ trợ trực tuyến, tư vấn và phản hồi tin nhắn khách hàng theo thời gian thực.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-outline-primary" onclick="loadSessions()" title="Tải lại danh sách">
                    <i class="fas fa-sync-alt me-1"></i> Làm mới
                </button>
            </div>
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
                    <div class="d-flex align-items-center flex-grow-1 overflow-hidden">
                        <button type="button" class="btn btn-sm btn-light border me-2 d-md-none flex-shrink-0" id="btnBackToSessions" title="Quay lại danh sách">
                            <i class="fas fa-arrow-left"></i>
                        </button>
                        <div class="session-avatar me-2 flex-shrink-0" id="headerAvatar" style="width: 40px; height: 40px; font-size: 1.1rem; margin-right: 10px;">?</div>
                        <div class="overflow-hidden">
                            <h6 class="mb-0 fw-bold text-truncate" id="headerName" style="max-width: 220px;">Khách hàng</h6>
                            <div class="text-muted small text-truncate" style="font-size: 11px;">
                                <span id="headerStatus"></span>
                                <span class="mx-1">|</span>
                                <i class="fas fa-phone-alt me-1"></i><span id="headerPhone">---</span>
                                <span class="mx-1 d-none d-sm-inline">|</span>
                                <span class="d-none d-sm-inline"><i class="fas fa-envelope me-1"></i><span id="headerEmail">---</span></span>
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
    
    <?php
    $adminChatScriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $adminChatScriptDir = preg_replace('#/(admin|includes|api)(/.*)?$#i', '', $adminChatScriptDir);
    $adminChatApiUrl = ($adminChatScriptDir ? $adminChatScriptDir : '') . '/api/contact-chat-api.php';
    ?>
    <script>
    window.FUTA_ADMIN_CHAT_API = '<?php echo htmlspecialchars($adminChatApiUrl, ENT_QUOTES, 'UTF-8'); ?>';
    </script>
    <script src="js/chat.js?v=<?php echo file_exists(__DIR__ . '/js/chat.js') ? filemtime(__DIR__ . '/js/chat.js') : time(); ?>"></script>
</body>
</html>