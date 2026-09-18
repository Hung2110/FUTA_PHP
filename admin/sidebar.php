<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($conn)) {
    require_once __DIR__ . '/../db.php';
}
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Check if notifications table exists to prevent fatal errors
$table_exists_query = isset($conn) && $conn ? $conn->query("SHOW TABLES LIKE 'notifications'") : false;
$table_exists = $table_exists_query && $table_exists_query->num_rows > 0;

$unread_count = 0;
$notifications = [];

if ($table_exists) {
    // Fetch notifications for the logged-in user
    $current_user_id = $_SESSION['admin_id'];

    // Get unread count
    $count_stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
    $count_stmt->bind_param("i", $current_user_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    if ($count_row = $count_result->fetch_assoc()) {
        $unread_count = $count_row['unread_count'];
    }
    $count_stmt->close();

    // Lấy 20 thông báo gần nhất để danh sách đủ dài và kích hoạt thanh cuộn
    $notif_stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
    $notif_stmt->bind_param("i", $current_user_id);
    $notif_stmt->execute();
    $notif_result = $notif_stmt->get_result();
    while ($row = $notif_result->fetch_assoc()) {
        $notifications[] = $row;
    }
    $notif_stmt->close();
}

// Lấy danh sách quyền của người dùng để hiển thị menu
$user_roles = !empty($_SESSION['admin_role']) ? explode(',', $_SESSION['admin_role']) : [];
$user_roles = array_map('trim', $user_roles); // Loại bỏ khoảng trắng thừa

// Sử dụng cấu hình từ auth_check.php (biến global $role_config)
global $role_config;

// Fallback: Nếu $role_config chưa tồn tại (do include order), tự định nghĩa lại để tránh lỗi hiển thị
if (!isset($role_config) || empty($role_config)) {
    $role_config = [
        'user' => ['label' => 'Người Dùng', 'color' => 'secondary'],
        'admin' => ['label' => 'Quản Trị Viên', 'color' => 'danger'],
        'user_manager' => ['label' => 'Quản Lý Người Dùng', 'color' => 'dark'],
        'project_manager' => ['label' => 'Quản Lý Dự Án', 'color' => 'primary'],
        'carousel_manager' => ['label' => 'Quản Lý Carousel', 'color' => 'info'],
        'news_manager' => ['label' => 'Quản Lý Tin Tức', 'color' => 'success'],
        'recruitment_manager' => ['label' => 'Quản Lý Tuyển Dụng', 'color' => 'warning'],
        'contact_manager' => ['label' => 'Quản Lý Liên Hệ', 'color' => 'secondary'],
        'chat_manager' => ['label' => 'Quản Lý Chat', 'color' => 'primary']
    ];
}

$display_roles = array_map(function($r) use ($role_config) {
    if (isset($role_config) && isset($role_config[$r])) {
        return $role_config[$r]['label'];
    }
    return ucfirst($r);
}, $user_roles);
$display_role_str = !empty($display_roles) ? implode(', ', $display_roles) : 'Chưa phân quyền';
?>
<link rel="stylesheet" href="css/admin.css">
<style>
    /* ==========================================================================
       CHUNG CHO TẤT CẢ PAGE-HEADER TRONG HỆ THỐNG ADMIN
       ========================================================================== */
    .page-header {
        background: #ffffff;
        padding: 20px 24px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        border: 1px solid rgba(226, 232, 240, 0.8);
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        transition: all 0.2s ease;
    }
    .page-header > div:first-child {
        flex: 1 1 auto;
    }
    .page-header h1 {
        font-weight: 700;
        font-size: 1.55rem;
        line-height: 1.3;
        margin: 0;
        color: #1e293b;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }
    .page-header h1 i {
        font-size: 1.4rem;
    }
    .page-header p {
        color: #64748b;
        margin: 5px 0 0 0;
        font-size: 13.5px;
        line-height: 1.5;
    }
    .page-header .btn {
        font-weight: 600;
        font-size: 14px;
        padding: 8px 16px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }
    .page-header .btn:hover {
        transform: translateY(-1px);
    }
    @media (max-width: 576px) {
        .page-header {
            padding: 16px;
            margin-bottom: 18px;
            gap: 14px;
        }
        .page-header h1 {
            font-size: 1.3rem;
        }
        .page-header p {
            font-size: 12.5px;
        }
        .page-header .btn {
            font-size: 13px;
            padding: 7px 12px;
        }
    }
    @media (max-width: 399.98px) {
        .page-header {
            padding: 12px 14px;
            margin-bottom: 14px;
            gap: 10px;
        }
        .page-header h1 {
            font-size: 1.18rem;
        }
        .page-header p {
            font-size: 12px;
        }
    }

    /* ==========================================================================
       UNIVERSAL MULTI-DEVICE RESPONSIVE SYSTEM - FUTA ADMIN
       Supports: Extra Small Mobile (<400px), Mobile (400-575px), Phablets (576-767px),
                 Tablets Portrait (768-991px), Laptops/Tablet Landscape (992-1199px),
                 Standard Desktop (1200-1399px), Large Screens (>=1400px)
       ========================================================================== */

    /* Chống tràn ngang cấp trang triệt để trên mọi thiết bị */
    html, body {
        max-width: 100% !important;
        width: 100% !important;
        overflow-x: hidden !important;
        box-sizing: border-box !important;
        margin: 0 !important;
        padding: 0 !important;
        position: relative;
    }
    *, *::before, *::after {
        box-sizing: border-box !important;
    }
    .main-content {
        max-width: 100% !important;
        overflow-x: hidden !important;
        box-sizing: border-box !important;
    }
    .main-content .row {
        margin-left: 0 !important;
        margin-right: 0 !important;
        max-width: 100% !important;
    }
    .main-content .row > [class*="col"] {
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    .card, .card-body, .table-responsive {
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    img, video, iframe {
        max-width: 100%;
        height: auto;
    }

    /* Sidebar máy tính để bàn (>= 1200px) */
    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        width: 260px;
        height: 100vh;        
        background: #f8f9fa;
        color: #343a40;
        padding: 20px 0;
        overflow-y: auto;
        z-index: 1050;
        border-right: 1px solid #dee2e6;
        transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease;
    }
    .sidebar-header { 
        padding: 15px 20px; 
        border-bottom: 1px solid #dee2e6; 
        margin-bottom: 15px; 
    }
    .sidebar-logo { max-width: 180px; display: block; margin-bottom: 8px; }
    .sidebar-menu { list-style: none; padding: 0; margin: 0; }
    .sidebar-menu a { display: flex; align-items: center; padding: 12px 20px; color: #212529; text-decoration: none; transition: all 0.2s ease; }
    .sidebar-menu a:hover, .sidebar-menu a.active { background: #e9ecef; border-left: 4px solid #007bff; color: #007bff; font-weight: 600; }
    .sidebar-menu i { width: 24px; margin-right: 10px; text-align: center; }
    .sidebar .small { color: #6c757d; }
    .sidebar-menu .dropdown-toggle::after {
        display: inline-block;
        margin-left: auto;
        vertical-align: .255em;
        content: "";
        border-top: .3em solid;
        border-right: .3em solid transparent;
        border-bottom: 0;
        border-left: .3em solid transparent;
        transition: transform .2s ease-in-out;
    }
    .sidebar-menu .dropdown-toggle[aria-expanded="true"]::after {
        transform: rotate(180deg);
    }
    .sidebar-submenu {
        background-color: rgba(0,0,0,0.04);
    }
    .notification-dropdown-menu {
        width: 340px;
        max-width: calc(100% - 20px);
        max-height: 420px;
        overflow-y: auto;
    }
    .notification-dropdown-menu::-webkit-scrollbar {
        width: 6px;
    }
    .notification-dropdown-menu::-webkit-scrollbar-track {
        background: #f8f9fa;
    }
    .notification-dropdown-menu::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }
    .notification-dropdown-menu::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    .notification-item small {
        white-space: normal;
    }

    /* Nội dung chính mặc định trên máy tính (>= 1200px) */
    .main-content { 
        margin-left: 260px; 
        padding: 25px 30px; 
        padding-top: 85px;
        min-height: 100vh;
        max-width: 100% !important;
        width: auto;
        box-sizing: border-box !important;
        overflow-x: hidden !important;
        transition: margin-left 0.3s ease;
    }

    /* Thanh tác vụ góc trên bên phải */
    .top-right-actions {
        position: fixed;
        top: 18px;
        right: 28px;
        z-index: 1040;
        display: flex;
        align-items: center;
        gap: 18px;
        background: rgba(255, 255, 255, 0.95);
        padding: 7px 22px;
        border-radius: 50px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }
    .top-right-actions .top-right-icon {
        font-size: 1.3rem;
        color: #4a5568;
        position: relative;
    }
    .top-right-actions .notification-badge {
        position: absolute;
        top: -5px;
        right: -10px;
        font-size: 0.6em;
        padding: 2px 5px;
        border: 1px solid white;
    }

    /* Lớp phủ mờ khi mở Sidebar Drawer */
    .sidebar-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.45);
        z-index: 1045;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
        backdrop-filter: blur(2px);
    }
    .sidebar-backdrop.show {
        opacity: 1;
        visibility: visible;
    }

    /* Tối ưu hóa thu gọn diện tích bảng toàn cục (Compact Table Layout) */
    .table-responsive {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        margin-bottom: 0;
    }
    .table {
        margin-bottom: 0;
        vertical-align: middle;
    }
    .table th {
        padding: 8px 10px !important;
        font-size: 0.82rem !important;
        font-weight: 600;
        vertical-align: middle;
        background-color: #f8fafc;
        line-height: 1.4;
    }
    .table td {
        padding: 8px 10px !important;
        font-size: 0.84rem !important;
        vertical-align: middle;
        line-height: 1.4;
    }
    .table .badge {
        font-size: 0.72rem !important;
        padding: 0.25em 0.55em !important;
        font-weight: 600;
    }
    .table .btn-sm {
        padding: 3px 7px !important;
        font-size: 0.76rem !important;
        line-height: 1.2;
    }
    .table .action-btn {
        width: 28px !important;
        height: 28px !important;
        font-size: 0.76rem !important;
    }

    /* Tóm tắt nội dung dài & Click để xem đầy đủ / thu gọn */
    .text-expandable {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        cursor: pointer;
        position: relative;
        max-width: 100%;
        word-break: break-word;
        overflow-wrap: anywhere;
        transition: background-color 0.2s ease;
    }
    .text-expandable:not(.expanded):hover {
        color: #007bff;
        text-decoration: underline dotted;
    }
    .text-expandable:not(.expanded)::after {
        content: " ▾";
        color: #007bff;
        font-size: 0.72rem;
        font-weight: bold;
        display: inline-block;
        margin-left: 2px;
        opacity: 0.8;
    }
    .text-expandable.expanded {
        display: block !important;
        -webkit-line-clamp: unset !important;
        overflow: visible !important;
        white-space: normal !important;
        word-break: break-word !important;
        overflow-wrap: anywhere !important;
        background-color: #f8fafc;
        padding: 4px 8px;
        border-radius: 6px;
        border-left: 3px solid #007bff;
        margin: 2px 0;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    }
    .text-expandable.expanded::after {
        content: " ▴ (Thu gọn)";
        color: #dc3545;
        font-size: 0.65rem;
        font-weight: 600;
        display: block;
        margin-top: 3px;
        cursor: pointer;
    }

    /* Cấu hình lưới Thống kê mặc định (>= 1200px) */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
        margin-bottom: 24px;
    }

    /* --------------------------------------------------------------------------
       BREAKPOINT: Laptop nhỏ & Tablet xoay ngang (992px - 1199.98px)
       Mục tiêu: Tối ưu sidebar gọn hơn để mở rộng diện tích nội dung chính
       -------------------------------------------------------------------------- */
    @media (min-width: 992px) and (max-width: 1199.98px) {
        .sidebar {
            width: 230px !important;
        }
        .main-content {
            margin-left: 230px !important;
            padding: 18px 20px !important;
            padding-top: 76px !important;
        }
        .top-right-actions {
            top: 14px;
            right: 20px;
            padding: 6px 18px;
            gap: 14px;
        }
        .stat-grid {
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 12px !important;
            margin-bottom: 18px !important;
        }
        .stat-card {
            padding: 14px 16px !important;
        }
        .stat-number,
        .stat-value {
            font-size: 1.45rem !important;
        }
        .page-header {
            padding: 16px 20px !important;
            margin-bottom: 18px !important;
        }
    }

    /* --------------------------------------------------------------------------
       BREAKPOINT: Tablet & Mobile (< 992px) - Kích hoạt Drawer Sidebar
       -------------------------------------------------------------------------- */
    @media (max-width: 991.98px) { 
        .sidebar {
            left: -270px;
            width: 270px;
            box-shadow: none;
        }
        .sidebar.show {
            left: 0;
            box-shadow: 5px 0 25px rgba(0,0,0,0.18);
        }
        .main-content { 
            margin-left: 0 !important; 
            padding: 14px 16px !important; 
            padding-top: 68px !important; 
            width: 100% !important;
            max-width: 100% !important;
            overflow-x: hidden !important;
            box-sizing: border-box !important;
            font-size: 14px;
        } 
        .top-right-actions {
            background: #fff;
            padding: 8px 16px;
            width: 100% !important;
            max-width: 100% !important;
            height: 54px;
            left: 0;
            top: 0;
            right: 0;
            justify-content: space-between;
            box-shadow: 0 1px 6px rgba(0,0,0,0.06);
            border-radius: 0;
            backdrop-filter: none;
            box-sizing: border-box !important;
        }
        .top-right-actions img {
            height: 24px !important;
            max-width: 120px !important;
            object-fit: contain;
        }
        .top-right-actions .top-right-icon {
            font-size: 1.15rem;
        }
        .btn-back-subpage {
            width: 36px;
            height: 36px;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            border-radius: 8px !important;
            color: #1e293b !important;
            background: #f1f5f9;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .btn-back-subpage:hover,
        .btn-back-subpage:active {
            background: #e2e8f0 !important;
            color: #0f172a !important;
        }

        /* Tiêu đề trang */
        .page-header {
            flex-wrap: wrap !important;
            gap: 12px !important;
            padding: 14px 18px !important;
            margin-bottom: 16px !important;
            border-radius: 10px !important;
        }
        /* Thanh tác vụ / Toolbar */
        .main-content > .d-flex.justify-content-between.align-items-center {
            padding: 0 !important;
        }
        .main-content h1,
        .page-header h1 {
            font-size: 1.35rem !important;
            font-weight: 700 !important;
            margin-bottom: 0 !important;
        }
        .page-header p {
            font-size: 0.88rem !important;
            color: #475569 !important;
        }

        /* Card và Card Header */
        .card {
            border-radius: 10px !important;
            margin-bottom: 16px !important;
        }
        .card-header {
            padding: 12px 16px !important;
        }
        .card-header h5,
        .card-header h6 {
            font-size: 1rem !important;
            font-weight: 600 !important;
        }
        .card-body {
            padding: 14px 16px !important;
        }

        /* Thẻ thống kê */
        .stat-grid {
            gap: 14px !important;
            margin-bottom: 16px !important;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)) !important;
        }
        .stat-card {
            padding: 14px 16px !important;
            border-radius: 10px !important;
        }
        .stat-card h6 {
            font-size: 0.85rem !important;
            color: #475569 !important;
            font-weight: 600 !important;
            margin-bottom: 4px !important;
        }
        .stat-card .stat-icon {
            font-size: 1.5rem !important;
            margin-bottom: 4px !important;
        }
        .stat-number,
        .stat-value {
            font-size: 1.45rem !important;
            font-weight: 700 !important;
            margin: 2px 0 !important;
        }
        .stat-label,
        .stat-trend {
            font-size: 0.85rem !important;
            color: #475569 !important;
            font-weight: 500 !important;
        }

        /* ======================================================================
           CHẾ ĐỘ HIỂN THỊ KHÔNG CUỘN NGANG & ĐẦY ĐỦ NỘI DUNG (< 992px)
           Chuyển đổi toàn bộ bảng dữ liệu thành dạng Thẻ Card Thông Minh
           ====================================================================== */
        .table-responsive {
            width: 100% !important;
            max-width: 100% !important;
            overflow-x: hidden !important;
            overflow-y: visible !important;
            border: none !important;
            box-sizing: border-box !important;
        }

        /* Hỗ trợ thanh cuộn ngang cho bảng ngang thuần túy (.table-horizontal) */
        .table-responsive.table-responsive-horizontal,
        .table-responsive:has(.table-horizontal) {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f8fafc;
        }
        .table-responsive.table-responsive-horizontal::-webkit-scrollbar,
        .table-responsive:has(.table-horizontal)::-webkit-scrollbar {
            height: 6px;
        }
        .table-responsive.table-responsive-horizontal::-webkit-scrollbar-track,
        .table-responsive:has(.table-horizontal)::-webkit-scrollbar-track {
            background: #f8fafc;
            border-radius: 4px;
        }
        .table-responsive.table-responsive-horizontal::-webkit-scrollbar-thumb,
        .table-responsive:has(.table-horizontal)::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .table-responsive.table-responsive-horizontal::-webkit-scrollbar-thumb:hover,
        .table-responsive:has(.table-horizontal)::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Định dạng Bảng ngang thuần túy (.table-horizontal) - Giữ nguyên 1 bảng ngang */
        .table.table-horizontal {
            display: table !important;
            width: 100% !important;
            min-width: 650px;
            border-collapse: collapse !important;
            background: #ffffff !important;
            margin-bottom: 0 !important;
        }
        .table.table-horizontal thead {
            display: table-header-group !important;
        }
        .table.table-horizontal thead tr {
            display: table-row !important;
            background: #f8fafc !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }
        .table.table-horizontal thead th {
            display: table-cell !important;
            font-size: 0.80rem !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            color: #64748b !important;
            background-color: #f8fafc !important;
            border: none !important;
            border-bottom: 2px solid #e2e8f0 !important;
            padding: 10px 12px !important;
            vertical-align: middle !important;
            white-space: nowrap !important;
        }
        .table.table-horizontal tbody {
            display: table-row-group !important;
            padding: 0 !important;
        }
        .table.table-horizontal tbody tr {
            display: table-row !important;
            background: transparent !important;
            border: none !important;
            border-bottom: 1px solid #f1f5f9 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            padding: 0 !important;
            transition: background-color 0.15s ease !important;
        }
        .table.table-horizontal tbody tr:hover {
            background-color: #f8fafc !important;
            border-color: #f1f5f9 !important;
            box-shadow: none !important;
        }
        .table.table-horizontal tbody td {
            display: table-cell !important;
            padding: 10px 12px !important;
            border: none !important;
            border-bottom: 1px solid #f1f5f9 !important;
            font-size: 0.88rem !important;
            color: #1e293b !important;
            text-align: left !important;
            vertical-align: middle !important;
            line-height: 1.4 !important;
            white-space: normal !important;
            word-break: break-word !important;
        }
        .table.table-horizontal tbody td::before {
            display: none !important;
            content: none !important;
        }
        .table.table-horizontal thead th:last-child {
            position: sticky !important;
            right: 0 !important;
            z-index: 5 !important;
            background-color: #f8fafc !important;
            box-shadow: -2px 0 5px rgba(0,0,0,0.05);
            text-align: right !important;
            white-space: nowrap !important;
        }
        .table.table-horizontal tbody td:last-child {
            text-align: right !important;
            white-space: nowrap !important;
            position: sticky !important;
            right: 0 !important;
            z-index: 4 !important;
            background-color: #ffffff !important;
            box-shadow: -2px 0 5px rgba(0,0,0,0.05);
        }
        .table.table-horizontal tbody tr:hover td:last-child {
            background-color: #f8fafc !important;
        }

        /* Gỡ bỏ toàn bộ min-width cứng trên các cột bảng card */
        .table:not(.table-horizontal) th, .table:not(.table-horizontal) td {
            min-width: 0 !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }
        
        .table-responsive .table:not(.table-horizontal) {
            display: block !important;
            width: 100% !important;
            max-width: 100% !important;
            border: none !important;
            background: transparent !important;
            margin-bottom: 0 !important;
            box-sizing: border-box !important;
        }
        
        .table-responsive .table:not(.table-horizontal) thead {
            display: none !important;
        }
        
        .table-responsive .table:not(.table-horizontal) tbody {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) !important;
            gap: 14px !important;
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            padding: 4px 0 !important;
        }

        .table-responsive .table:not(.table-horizontal) tr {
            display: flex !important;
            flex-direction: column !important;
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 10px !important;
            padding: 14px 16px !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
            margin-bottom: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            min-width: 0 !important;
            overflow: hidden !important;
            transition: border-color 0.2s, box-shadow 0.2s !important;
        }

        .table-responsive .table:not(.table-horizontal) tr:hover {
            border-color: #cbd5e1 !important;
            box-shadow: 0 3px 8px rgba(0,0,0,0.08) !important;
        }

        .table-responsive .table:not(.table-horizontal) td {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            flex-wrap: wrap !important;
            gap: 4px 8px !important;
            padding: 8px 0 !important;
            border: none !important;
            border-bottom: 1px dashed #f1f5f9 !important;
            font-size: 0.90rem !important;
            color: #1e293b !important;
            text-align: right !important;
            min-width: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            white-space: normal !important;
            word-break: break-word !important;
            overflow-wrap: anywhere !important;
            line-height: 1.5 !important;
        }

        .table-responsive .table:not(.table-horizontal) td:last-child {
            border-bottom: none !important;
            padding-top: 10px !important;
            margin-top: 4px !important;
            justify-content: flex-end !important;
            flex-wrap: wrap !important;
        }

        /* Nhãn tự động từ data-label */
        .table-responsive .table:not(.table-horizontal) td::before {
            content: attr(data-label);
            font-weight: 600 !important;
            color: #475569 !important;
            font-size: 0.86rem !important;
            text-align: left !important;
            margin-right: 12px !important;
            flex-shrink: 0 !important;
        }

        .table-responsive .table:not(.table-horizontal) td:not([data-label])::before,
        .table-responsive .table:not(.table-horizontal) td[data-label=""]::before {
            display: none !important;
        }

        /* Hàng thông báo trống (colspan) */
        .table-responsive .table:not(.table-horizontal) td[colspan] {
            display: block !important;
            text-align: center !important;
            justify-content: center !important;
            padding: 24px 12px !important;
            border-bottom: none !important;
            font-size: 0.90rem !important;
            width: 100% !important;
        }
        .table-responsive .table:not(.table-horizontal) td[colspan]::before {
            display: none !important;
        }

        /* Các ô nội dung: Nhãn nằm trên, nội dung dàn 100% bên dưới để hiển thị trọn vẹn không bị co ép */
        .table-responsive .table:not(.table-horizontal) td.cell-block,
        .table-responsive .table:not(.table-horizontal) td[data-label="Tin nhắn"],
        .table-responsive .table:not(.table-horizontal) td[data-label="Mô tả"],
        .table-responsive .table:not(.table-horizontal) td[data-label="Chủ đề"],
        .table-responsive .table:not(.table-horizontal) td[data-label="Bài viết"],
        .table-responsive .table:not(.table-horizontal) td[data-label="Tiêu đề"],
        .table-responsive .table:not(.table-horizontal) td[data-label="Công việc"],
        .table-responsive .table:not(.table-horizontal) td[data-label="Hành động"],
        .table-responsive .table:not(.table-horizontal) td[data-label="Email"],
        .table-responsive .table:not(.table-horizontal) td[data-label="Họ tên"],
        .table-responsive .table:not(.table-horizontal) td[data-label="Người dùng"],
        .table-responsive .table:not(.table-horizontal) td[data-label="Vị trí"],
        .table-responsive .table:not(.table-horizontal) td[data-label="Tên đăng nhập"],
        .table-responsive .table:not(.table-horizontal) td[data-label="Tác giả"],
        .table-responsive .table:not(.table-horizontal) td[data-label="Nơi làm việc"] {
            flex-direction: column !important;
            align-items: flex-start !important;
            text-align: left !important;
        }

        .table-responsive .table:not(.table-horizontal) td.cell-block::before,
        .table-responsive .table:not(.table-horizontal) td[data-label="Tin nhắn"]::before,
        .table-responsive .table:not(.table-horizontal) td[data-label="Mô tả"]::before,
        .table-responsive .table:not(.table-horizontal) td[data-label="Chủ đề"]::before,
        .table-responsive .table:not(.table-horizontal) td[data-label="Bài viết"]::before,
        .table-responsive .table:not(.table-horizontal) td[data-label="Tiêu đề"]::before,
        .table-responsive .table:not(.table-horizontal) td[data-label="Công việc"]::before,
        .table-responsive .table:not(.table-horizontal) td[data-label="Hành động"]::before,
        .table-responsive .table:not(.table-horizontal) td[data-label="Email"]::before,
        .table-responsive .table:not(.table-horizontal) td[data-label="Họ tên"]::before,
        .table-responsive .table:not(.table-horizontal) td[data-label="Người dùng"]::before,
        .table-responsive .table:not(.table-horizontal) td[data-label="Vị trí"]::before,
        .table-responsive .table:not(.table-horizontal) td[data-label="Tên đăng nhập"]::before,
        .table-responsive .table:not(.table-horizontal) td[data-label="Tác giả"]::before,
        .table-responsive .table:not(.table-horizontal) td[data-label="Nơi làm việc"]::before {
            margin-bottom: 4px !important;
        }

        /* Hiển thị đầy đủ 100% nội dung trên màn hình nhỏ, không cắt bớt chữ hay ẩn dấu ba chấm */
        .text-expandable {
            display: block !important;
            -webkit-line-clamp: unset !important;
            overflow: visible !important;
            white-space: normal !important;
            word-break: break-word !important;
            overflow-wrap: anywhere !important;
            text-overflow: clip !important;
            max-width: 100% !important;
            cursor: default !important;
        }
        .text-expandable::after {
            display: none !important;
        }
        .text-truncate {
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: clip !important;
            max-width: 100% !important;
            word-break: break-word !important;
        }
        .description-snippet {
            font-size: 0.90rem !important;
            line-height: 1.5 !important;
            white-space: normal !important;
            word-break: break-word !important;
        }
        .post-title-cell {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            max-width: 100% !important;
            width: 100% !important;
            min-width: 0 !important;
            text-align: left !important;
        }
        .post-title-cell > div {
            min-width: 0 !important;
            flex: 1 1 auto !important;
            max-width: 100% !important;
        }
        .post-title-cell .post-image {
            flex-shrink: 0 !important;
        }
        .table-responsive .table td .slide-image {
            max-width: 80px !important;
            height: auto !important;
        }

        /* Bảng dữ liệu Dashboard giữ giao diện bảng chuẩn rõ ràng */
        .table.dashboard-table th {
            padding: 8px 10px !important;
            font-size: 0.82rem !important;
        }
        .table.dashboard-table td {
            padding: 8px 10px !important;
            font-size: 0.88rem !important;
            line-height: 1.45 !important;
        }

        /* Nút bấm & Ô nhập */
        .btn {
            font-size: 0.88rem !important;
            padding: 6px 14px !important;
            border-radius: 6px !important;
            font-weight: 500 !important;
        }
        .btn-sm {
            font-size: 0.82rem !important;
            padding: 4px 10px !important;
            font-weight: 500 !important;
        }
        .btn-lg {
            font-size: 0.95rem !important;
            padding: 8px 18px !important;
        }
        .cta-button {
            font-size: 0.88rem !important;
            padding: 8px 16px !important;
            border-radius: 8px !important;
        }
        .action-btn, .table .action-btn {
            width: 34px !important;
            height: 34px !important;
            font-size: 0.85rem !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .badge, .table .badge, .dashboard-table .badge {
            font-size: 0.80rem !important;
            padding: 0.35em 0.65em !important;
            font-weight: 600 !important;
        }
        .form-control,
        .form-select {
            font-size: 0.92rem !important;
            padding: 8px 12px !important;
            border-radius: 6px !important;
        }
        .form-label {
            font-size: 0.88rem !important;
            font-weight: 600 !important;
            margin-bottom: 4px !important;
        }

        /* Chi tiết hồ sơ */
        .profile-card .card-body {
            padding: 16px !important;
        }
        .profile-card .card-title {
            font-size: 1.2rem !important;
            font-weight: 700 !important;
            margin-bottom: 14px !important;
        }
        .profile-card .list-group-item strong {
            min-width: 140px !important;
            font-size: 0.88rem !important;
            color: #475569 !important;
        }
        .profile-card .list-group-item .value {
            font-size: 0.95rem !important;
            color: #0f172a !important;
        }
        .info-label {
            font-size: 0.85rem !important;
            color: #475569 !important;
            margin-bottom: 3px !important;
        }
        .info-value {
            font-size: 0.95rem !important;
            color: #0f172a !important;
            margin-bottom: 12px !important;
        }
        .message-box,
        .bio-text {
            padding: 12px 14px !important;
            font-size: 0.92rem !important;
            line-height: 1.6 !important;
            white-space: pre-wrap !important;
            word-break: break-word !important;
        }
        .cv-container {
            min-height: 60vh !important;
        }
    }

    /* Tablet (640px - 991.98px): Hiển thị 2 thẻ Card trên mỗi hàng */
    @media (min-width: 640px) and (max-width: 991.98px) {
        .table-responsive .table:not(.table-horizontal) tbody {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 14px !important;
        }
    }

    /* --------------------------------------------------------------------------
       BREAKPOINT: Mobile lớn & Phablet (576px - 767.98px)
       -------------------------------------------------------------------------- */
    @media (max-width: 767.98px) {
        .main-content {
            padding: 12px 12px !important;
            padding-top: 64px !important;
            font-size: 14px;
        }
        .top-right-actions {
            height: 52px;
            padding: 6px 14px;
        }
        .page-header {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 10px !important;
            padding: 12px 14px !important;
            margin-bottom: 12px !important;
        }
        .main-content h1,
        .page-header h1 {
            font-size: 1.25rem !important;
        }
        .page-header p {
            font-size: 0.85rem !important;
        }
        .page-header .cta-button,
        .page-header .btn,
        .main-content > .d-flex.justify-content-between.align-items-center > .btn {
            width: 100%;
            text-align: center;
            justify-content: center;
        }
        .main-content > .d-flex.justify-content-between.align-items-center > .d-flex {
            width: 100%;
            flex-wrap: wrap;
        }
        .main-content > .d-flex.justify-content-between.align-items-center > .d-flex .btn {
            flex: 1 1 auto;
            text-align: center;
            justify-content: center;
        }

        /* Thẻ thống kê 2 cột */
        .stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 10px !important;
        }
        .stat-card {
            padding: 10px 12px !important;
            border-radius: 8px !important;
            border-left-width: 4px !important;
        }
        .stat-card .stat-icon {
            font-size: 1.35rem !important;
            margin-bottom: 2px !important;
        }
        .stat-number,
        .stat-value {
            font-size: 1.35rem !important;
        }
        .stat-label,
        .stat-trend {
            font-size: 0.82rem !important;
        }

        /* Bảng & Thẻ hiển thị cô đọng & rõ chữ */
        .card,
        .card-body,
        .table-responsive {
            max-width: 100% !important;
            box-sizing: border-box !important;
        }
        .table-responsive:not(.table-responsive-horizontal):not(:has(.table-horizontal)) {
            overflow-x: hidden !important;
        }
        .table-responsive.table-responsive-horizontal,
        .table-responsive:has(.table-horizontal) {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
        }
        .card-body {
            padding: 10px 12px !important;
        }
        .card-header form {
            width: 100% !important;
        }
        .card-header select,
        .card-header .form-select {
            max-width: 100% !important;
            width: 100% !important;
            min-width: 0 !important;
        }
        .table th {
            padding: 8px 10px !important;
            font-size: 0.80rem !important;
        }
        .table td {
            padding: 8px 10px !important;
            font-size: 0.88rem !important;
        }
        .table.table-horizontal {
            min-width: 580px !important;
        }
        .table.table-horizontal th,
        .table.table-horizontal td {
            padding: 8px 10px !important;
        }
        .table-responsive .table:not(.table-horizontal) td.text-nowrap,
        .table-responsive .table:not(.table-horizontal) td .text-nowrap {
            white-space: normal !important;
        }
        .badge {
            font-size: 0.78rem !important;
            padding: 0.30em 0.60em !important;
        }
        .table .btn-sm {
            padding: 3px 8px !important;
            font-size: 0.80rem !important;
        }
        .table .action-btn {
            width: 32px !important;
            height: 32px !important;
            font-size: 0.82rem !important;
        }

        /* Phân trang không tràn ngang */
        .pagination {
            flex-wrap: wrap !important;
            justify-content: center !important;
            gap: 4px !important;
        }
        .pagination .page-item .page-link {
            padding: 4px 8px !important;
            font-size: 0.82rem !important;
        }

        /* Trình soạn thảo văn bản Quill */
        .ql-toolbar {
            max-width: 100% !important;
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 2px !important;
            box-sizing: border-box !important;
        }
        .ql-container {
            max-width: 100% !important;
            box-sizing: border-box !important;
        }

        /* Nút bấm & Ô nhập */
        .btn {
            font-size: 0.85rem !important;
            padding: 6px 12px !important;
        }
        .btn-sm {
            font-size: 0.80rem !important;
            padding: 3px 8px !important;
        }
        .btn-lg {
            font-size: 0.90rem !important;
            padding: 8px 16px !important;
        }
        .action-btn {
            width: 32px !important;
            height: 32px !important;
            font-size: 0.82rem !important;
        }
        .modal-dialog {
            max-width: calc(100% - 16px) !important;
            margin: 8px auto !important;
        }

        /* Chi tiết hồ sơ */
        .profile-card .card-body {
            padding: 14px !important;
        }
        .profile-card .card-title {
            font-size: 1.15rem !important;
            margin-bottom: 12px !important;
        }
        .profile-card .list-group-item {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 3px !important;
            padding: 8px 0 !important;
        }
        .profile-card .list-group-item strong {
            min-width: auto !important;
            font-size: 0.86rem !important;
        }
        .profile-card .list-group-item .value {
            font-size: 0.92rem !important;
            word-break: break-word;
        }
        .info-label {
            font-size: 0.82rem !important;
            margin-bottom: 2px !important;
        }
        .info-value {
            font-size: 0.92rem !important;
            margin-bottom: 10px !important;
        }
        .message-box,
        .bio-text {
            padding: 10px 12px !important;
            font-size: 0.90rem !important;
        }
        .cv-container {
            min-height: 50vh !important;
        }
        .profile-header {
            padding: 1rem 0.5rem !important;
        }
        .profile-header i.fa-6x {
            font-size: 3.5rem !important;
        }
    }

    /* --------------------------------------------------------------------------
       BREAKPOINT: Mobile chuẩn & nhỏ (400px - 575.98px)
       -------------------------------------------------------------------------- */
    @media (max-width: 575.98px) {
        .main-content {
            padding: 10px 10px !important;
            padding-top: 60px !important;
            font-size: 13.5px;
        }
        .top-right-actions {
            height: 50px;
            padding: 5px 12px;
        }
        .top-right-actions img {
            height: 22px !important;
            max-width: 110px !important;
        }
        .stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 8px !important;
        }
        .stat-card {
            padding: 8px 10px !important;
        }
        .stat-number,
        .stat-value {
            font-size: 1.25rem !important;
        }
        .stat-label,
        .stat-trend {
            font-size: 0.80rem !important;
        }
        .table-responsive .table:not(.table-horizontal) tr {
            padding: 12px 14px !important;
        }
        .notification-dropdown-menu {
            width: calc(100vw - 20px) !important;
            max-width: calc(100vw - 20px) !important;
            right: 0 !important;
        }
        .table th {
            padding: 7px 8px !important;
            font-size: 0.78rem !important;
        }
        .table td {
            padding: 7px 8px !important;
            font-size: 0.86rem !important;
        }
        .table.table-horizontal {
            min-width: 510px !important;
        }
        .table.table-horizontal th,
        .table.table-horizontal td {
            padding: 7px 8px !important;
            font-size: 0.82rem !important;
        }
        .badge {
            font-size: 0.76rem !important;
        }
        .btn {
            font-size: 0.84rem !important;
        }
        .btn-sm {
            font-size: 0.78rem !important;
        }
        .action-btn, .table .action-btn {
            width: 32px !important;
            height: 32px !important;
            font-size: 0.80rem !important;
        }
    }

    /* --------------------------------------------------------------------------
       BREAKPOINT: Điện thoại cực nhỏ (< 400px, e.g. 320px - 399px, iPhone SE)
       -------------------------------------------------------------------------- */
    @media (max-width: 399.98px) {
        .main-content {
            padding: 8px 8px !important;
            padding-top: 56px !important;
            font-size: 13px;
        }
        .top-right-actions {
            height: 48px;
            padding: 4px 8px;
        }
        .top-right-actions img {
            max-width: 95px !important;
            height: 19px !important;
        }
        .top-right-actions #sidebarToggle {
            padding: 2px 6px !important;
            margin-right: 4px !important;
        }
        .top-right-actions .btn-back-subpage {
            width: 30px !important;
            height: 30px !important;
            font-size: 0.85rem !important;
            padding: 2px 4px !important;
            margin-right: 4px !important;
        }
        .top-right-actions .top-right-icon {
            font-size: 1.05rem !important;
        }
        .top-right-actions i.fa-user-circle {
            font-size: 1.4rem !important;
        }
        .top-right-actions .gap-3 {
            gap: 8px !important;
        }
        .page-header {
            padding: 10px 10px !important;
            margin-bottom: 10px !important;
        }
        .page-header h1,
        .main-content h1 {
            font-size: 1.15rem !important;
        }
        .page-header p {
            font-size: 0.80rem !important;
        }
        .stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 6px !important;
        }
        .stat-card {
            padding: 7px 8px !important;
        }
        .stat-icon {
            font-size: 1.2rem !important;
        }
        .stat-number,
        .stat-value {
            font-size: 1.20rem !important;
        }
        .stat-label,
        .stat-trend {
            font-size: 0.78rem !important;
        }
        .table-responsive .table:not(.table-horizontal) tr {
            padding: 10px 10px !important;
        }
        .table-responsive .table:not(.table-horizontal) td {
            font-size: 0.84rem !important;
            padding: 5px 0 !important;
        }
        .table th {
            padding: 6px 7px !important;
            font-size: 0.76rem !important;
        }
        .table td {
            padding: 6px 7px !important;
            font-size: 0.84rem !important;
        }
        .table.table-horizontal {
            min-width: 460px !important;
        }
        .table.table-horizontal th,
        .table.table-horizontal td {
            padding: 6px 6px !important;
            font-size: 0.78rem !important;
        }
        .badge {
            font-size: 0.74rem !important;
            padding: 0.25em 0.50em !important;
        }
        .table .btn-sm {
            padding: 3px 6px !important;
            font-size: 0.76rem !important;
        }
        .table .action-btn {
            width: 30px !important;
            height: 30px !important;
            font-size: 0.80rem !important;
        }
        .btn {
            font-size: 0.82rem !important;
            padding: 5px 10px !important;
        }
        .btn-sm {
            font-size: 0.76rem !important;
            padding: 3px 6px !important;
        }
    }
</style>

<!-- Lớp phủ mờ cho mobile/tablet drawer -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<div class="sidebar" id="adminSidebar">
    <div class="sidebar-header d-flex justify-content-between align-items-start">
        <div class="flex-grow-1">
            <a href="dashboard.php">
                <img src="../assets/images/logo/Advertising.png" class="sidebar-logo" alt="FUTA Advertising">
            </a>
            <div class="mt-2">
                <div class="fw-bold text-dark small"><?php echo htmlspecialchars($_SESSION['admin_fullname'] ?? 'User'); ?></div>
                <div class="text-muted" style="font-size: 11px; line-height: 1.2;"><?php echo htmlspecialchars($display_role_str); ?></div>
            </div>
        </div>
        <button type="button" class="btn-close d-lg-none mt-1" id="sidebarClose" aria-label="Close"></button>
    </div>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-home"></i> Dashboard</a></li>
        <?php if (!empty(array_intersect(['admin', 'user_manager'], $user_roles))): ?>
        <li><a href="users.php" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['users.php', 'view_user.php']) ? 'active' : ''; ?>"><i class="fas fa-users"></i> Quản Lý Người Dùng</a></li>
        <?php endif; ?>

        <?php if (!empty(array_intersect(['admin', 'project_manager'], $user_roles))): ?>
        <li><a href="projects.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'projects.php' ? 'active' : ''; ?>"><i class="fas fa-project-diagram"></i> Quản Lý Dự Án</a></li>
        <?php endif; ?>

        <?php if (!empty(array_intersect(['admin', 'news_manager'], $user_roles))): ?>
        <li><a href="news.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'news.php' ? 'active' : ''; ?>"><i class="fas fa-newspaper"></i> Quản Lý Tin Tức</a></li>
        <?php endif; ?>
        
        <?php if (!empty(array_intersect(['admin', 'carousel_manager'], $user_roles))): ?>
        <li><a href="carousel_slides.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'carousel_slides.php' ? 'active' : ''; ?>"><i class="fas fa-images"></i> Quản Lý Carousel</a></li>
        <?php endif; ?>
        
        
        <?php if (!empty(array_intersect(['admin', 'recruitment_manager'], $user_roles))): ?>
        <?php 
            $isRecruitmentActive = in_array(basename($_SERVER['PHP_SELF']), ['recruitments.php', 'applications.php', 'view_application.php']);
        ?>
        <li class="sidebar-dropdown">
            <a href="#recruitmentSubmenu" data-bs-toggle="collapse" aria-expanded="<?php echo $isRecruitmentActive ? 'true' : 'false'; ?>" class="dropdown-toggle <?php echo $isRecruitmentActive ? 'active' : ''; ?>">
                <i class="fas fa-briefcase"></i> Quản Lý Tuyển Dụng
            </a>
            <ul class="collapse list-unstyled sidebar-submenu <?php echo $isRecruitmentActive ? 'show' : ''; ?>" id="recruitmentSubmenu">
                <li><a href="recruitments.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'recruitments.php' ? 'active' : ''; ?>"><i class="fas fa-list"></i> Danh sách tin</a></li>
                <li><a href="applications.php" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['applications.php', 'view_application.php']) ? 'active' : ''; ?>"><i class="fas fa-file-alt"></i> Đơn ứng tuyển</a></li>
            </ul>
        </li>
        <?php endif; ?>

        <?php if (in_array('admin', $user_roles)): ?>
        <li><a href="activity_logs.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'activity_logs.php' ? 'active' : ''; ?>"><i class="fas fa-history"></i> Nhật Ký Hoạt Động</a></li>
        <?php endif; ?>

        <?php if (!empty(array_intersect(['admin', 'project_manager', 'news_manager'], $user_roles))): ?>
        <li><a href="import.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'import.php' ? 'active' : ''; ?>"><i class="fas fa-file-import"></i> Import Dữ liệu</a></li>
        <?php endif; ?>

        <?php if (!empty(array_intersect(['admin', 'contact_manager'], $user_roles))): ?>
        <li><a href="contacts.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'contacts.php' ? 'active' : ''; ?>"><i class="fas fa-envelope"></i> Liên hệ</a></li>
        <?php endif; ?>
        
        <?php if (!empty(array_intersect(['admin', 'chat_manager'], $user_roles))): ?>
        <li><a href="chat.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'chat.php' ? 'active' : ''; ?>"><i class="fas fa-comments"></i> Quản Lý Chat</a></li>
        <?php endif; ?>
    </ul>
</div>

<div class="top-right-actions">
    <!-- Nút Thoát trang con & Nút Menu cho Mobile & Tablet (< 992px) -->
    <div class="d-flex align-items-center d-lg-none">
        <?php 
            $current_page = basename($_SERVER['PHP_SELF']);
            $is_subpage = ($current_page !== 'dashboard.php');
            if ($is_subpage):
                $parent_page = 'dashboard.php';
                $parent_title = 'Dashboard';
                if (in_array($current_page, ['view_user.php'])) {
                    $parent_page = 'users.php';
                    $parent_title = 'Danh sách người dùng';
                } elseif (in_array($current_page, ['view_contact.php'])) {
                    $parent_page = 'contacts.php';
                    $parent_title = 'Danh sách liên hệ';
                } elseif (in_array($current_page, ['view_application.php'])) {
                    $parent_page = 'applications.php';
                    $parent_title = 'Danh sách tuyển dụng';
                } elseif (in_array($current_page, ['post-edit.php'])) {
                    $parent_page = 'news.php';
                    $parent_title = 'Danh sách tin tức';
                } elseif (in_array($current_page, ['project-edit.php'])) {
                    $parent_page = 'projects.php';
                    $parent_title = 'Danh sách dự án';
                }
        ?>
            <a href="<?php echo $parent_page; ?>" class="btn btn-light border-0 px-2 py-1 me-2 text-dark shadow-none btn-back-subpage" title="Thoát về <?php echo $parent_title; ?>" aria-label="Thoát trang con">
                <i class="fas fa-arrow-left fa-lg"></i>
            </a>
        <?php endif; ?>
        <button type="button" class="btn btn-light border-0 px-2 py-1 text-dark shadow-none" id="sidebarToggle" aria-label="Mở menu quản trị">
            <i class="fas fa-bars fa-lg"></i>
        </button>
    </div>

    <!-- Dropdown thông báo & Người dùng -->
    <div class="d-flex align-items-center gap-3 ms-auto">
        <!-- Notification Dropdown -->
        <div class="dropdown">
            <a href="#" class="text-secondary top-right-icon" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Thông báo">
                <i class="fas fa-bell"></i>
                <?php if ($unread_count > 0): ?>
                    <span class="badge rounded-pill bg-danger notification-badge"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end notification-dropdown-menu shadow border-0" aria-labelledby="notificationDropdown">
                <li class="dropdown-header fw-bold text-dark">Bạn có <?php echo $unread_count; ?> thông báo mới</li>
                <li><hr class="dropdown-divider"></li>
                <?php if (!$table_exists): ?>
                    <li class="text-center text-danger p-2 small">Lỗi: Bảng `notifications` không tồn tại.</li>
                <?php elseif (empty($notifications)): ?>
                    <li class="text-center text-muted p-3">
                        <i class="far fa-bell-slash fa-2x d-block mb-2 opacity-50"></i>
                        Không có thông báo mới
                    </li>
                <?php else: ?>
                    <?php foreach ($notifications as $notification): ?>
                        <li>
                            <a class="dropdown-item notification-item py-2 <?php echo $notification['is_read'] ? '' : 'fw-bold bg-light'; ?>" href="<?php echo htmlspecialchars($notification['link']); ?>" data-id="<?php echo $notification['id']; ?>">
                                <small><i class="fas <?php echo $notification['type'] == 'contact' ? 'fa-envelope text-primary' : 'fa-file-alt text-success'; ?> me-2"></i><?php echo htmlspecialchars($notification['message']); ?></small>
                                <small class="d-block text-muted mt-1" style="font-size: 11px;"><i class="far fa-clock me-1"></i><?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?></small>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>

        <!-- User Dropdown -->
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none text-dark" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="me-2 d-none d-sm-inline fw-semibold"><?php echo htmlspecialchars($_SESSION['admin_fullname'] ?? 'User'); ?></span>
                <i class="fas fa-user-circle fa-2x text-secondary"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="userDropdown">
                <li><h6 class="dropdown-header">Xin chào, <?php echo htmlspecialchars($_SESSION['admin_fullname'] ?? 'User'); ?></h6></li>
                <li><a class="dropdown-item py-2" href="profile.php"><i class="fas fa-user-edit fa-fw me-2 text-primary"></i>Hồ sơ</a></li>
                <li><a class="dropdown-item py-2" href="../index.php" target="_blank"><i class="fas fa-globe fa-fw me-2 text-success"></i>Xem website</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class="fas fa-sign-out-alt fa-fw me-2"></i>Đăng xuất</a></li>
            </ul>
        </div>
    </div>
</div>

<script src="js/sidebar.js"></script>