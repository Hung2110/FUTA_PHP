<?php
require_once 'auth_check.php';
$pageTitle = 'Chi Tiết Liên Hệ';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: contacts.php');
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM contact WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: contacts.php?error=notfound');
    exit();
}

$contact = $result->fetch_assoc();
$stmt->close();

$status_text = ['pending' => 'Chưa tư vấn', 'done' => 'Đã tư vấn'];
$status_colors = ['pending' => 'secondary', 'done' => 'success'];

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | FUTA Advertising' : 'FUTA Advertising'; ?></title>
    <link rel="icon" href="../assets/images/logo/futa.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { background: #f7f9fc; }
        .profile-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .profile-card .card-body { padding: 2rem; }
        .profile-card .list-group-item { 
            border: none; 
            padding: .85rem 0;
            display: flex;
            align-items: flex-start;
        }
        .profile-card .list-group-item strong { 
            min-width: 180px; 
            display: inline-block; 
            color: #6b7280;
        }
        .profile-card .list-group-item .value {
            font-weight: 500;
            color: #1f2a37;
        }
        .message-box {
            white-space: pre-wrap;
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0"><i class="fas fa-envelope-open-text text-primary"></i> Chi Tiết Liên Hệ</h1>
            <a href="contacts.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại danh sách</a>
        </div>
        
        <div class="card profile-card">
            <div class="card-body">
                <h3 class="card-title mb-4">Thông tin từ: <?php echo htmlspecialchars($contact['name']); ?></h3>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>ID:</strong> <span class="value">#<?php echo $contact['id']; ?></span></li>
                    <li class="list-group-item"><strong>Họ và tên:</strong> <span class="value"><?php echo htmlspecialchars($contact['name']); ?></span></li>
                    <li class="list-group-item"><strong>Email:</strong> <span class="value"><a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>"><?php echo htmlspecialchars($contact['email']); ?></a></span></li>
                    <li class="list-group-item"><strong>Số điện thoại:</strong> <span class="value"><a href="tel:<?php echo htmlspecialchars($contact['phone']); ?>"><?php echo htmlspecialchars($contact['phone']); ?></a></span></li>
                    <li class="list-group-item"><strong>Chủ đề:</strong> <span class="value"><?php echo htmlspecialchars($contact['subject'] ?: 'Không có chủ đề'); ?></span></li>
                    <li class="list-group-item"><strong>Ngày gửi:</strong> <span class="value"><?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?></span></li>
                    <li class="list-group-item"><strong>Trạng thái:</strong> <span class="value"><span class="badge bg-<?php echo $status_colors[$contact['status'] ?? 'new'] ?? 'secondary'; ?>"><?php echo $status_text[$contact['status'] ?? 'new'] ?? ucfirst($contact['status'] ?? 'new'); ?></span></span></li>
                    <li class="list-group-item d-block"><strong>Nội dung tin nhắn:</strong> <div class="value mt-2 message-box"><?php echo nl2br(htmlspecialchars($contact['message'])); ?></div></li>
                </ul>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>