<?php

// Import autoloader của Composer
require_once __DIR__ . '/../vendor/autoload.php';
require_once 'auth_check.php'; // Đã bao gồm db.php và session_start()

// Hàm ghi log hoạt động
function log_import_activity($conn, $action, $module) {
    if (isset($_SESSION['admin_id'])) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, module, ip) VALUES (?, ?, ?, ?)");
        $log_stmt->bind_param("isss", $_SESSION['admin_id'], $action, $module, $ip);
        $log_stmt->execute();
    }
}

function create_slug_from_import($string) {
    $string = mb_strtolower($string, 'UTF-8');
    $char_map = [
        'à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ' => 'a', 'è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ' => 'e',
        'ì|í|ị|ỉ|ĩ' => 'i', 'ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ' => 'o',
        'ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ' => 'u', 'ỳ|ý|ỵ|ỷ|ỹ' => 'y', 'đ' => 'd',
    ];
    foreach ($char_map as $pattern => $replacement) {
        $string = preg_replace("/($pattern)/", $replacement, $string);
    }
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/([\s-]+)/', '-', $string);
    return trim($string, '-');
}

function parse_content_with_tags($content) {
    $data = [];
    // Tách nội dung dựa trên các tag [TAG]
    $parts = preg_split('/\[([A-Z_]+)\]/', $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

    if (count($parts) < 2) { // Nếu không có tag nào, dùng quy ước cũ
        $lines = explode("\n", trim($content));
        $data['TITLE'] = array_shift($lines);
        $data['CONTENT'] = implode("\n", $lines);
        return $data;
    }

    for ($i = 0; $i < count($parts); $i += 2) {
        $data[strtoupper($parts[$i])] = trim($parts[$i + 1] ?? '');
    }
    return $data;
}

if (isset($_POST["submit"])) {
    $importType = $_POST['import_type'] ?? 'project'; // 'project' hoặc 'posts'

    // 1. Kiểm tra file có được tải lên không
    if (!isset($_FILES['fileToUpload']) || $_FILES['fileToUpload']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['import_message'] = "Lỗi khi tải file lên. Vui lòng thử lại. Mã lỗi: " . ($_FILES['fileToUpload']['error'] ?? 'unknown');
        $_SESSION['import_message_type'] = 'danger';
        header('Location: import.php?type=' . $importType); exit();
    }

    $file = $_FILES['fileToUpload'];
    $filePath = $file["tmp_name"];
    $fileName = basename($file["name"]);
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // --- 2. Kiểm tra bảo mật file ---
    $allowed_extensions = ['docx', 'pdf'];
    $allowed_mime_types = [
        'pdf' => 'application/pdf',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];

    // Kiểm tra extension
    if (!in_array($fileType, $allowed_extensions)) {
        $_SESSION['import_message'] = "Lỗi: Chỉ chấp nhận file .docx hoặc .pdf.";
        $_SESSION['import_message_type'] = 'danger';
        header('Location: import.php?type=' . $importType); exit();
    }

    // Kiểm tra MIME type thực tế của file
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $filePath);
    finfo_close($finfo);

    if ($mime_type !== $allowed_mime_types[$fileType]) {
        $_SESSION['import_message'] = "Lỗi: Loại file không hợp lệ hoặc file bị hỏng. Vui lòng chỉ tải lên file .docx hoặc .pdf chuẩn.";
        $_SESSION['import_message_type'] = 'danger';
        header('Location: import.php?type=' . $importType); exit();
    }

    $content = '';

    try {
        // Xử lý file PDF
        if ($fileType == "pdf") {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($filePath);
            $content = $pdf->getText();
        }
        // Xử lý file Word (.docx)
        elseif ($fileType == "docx") {
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);
            $text = '';
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                        foreach($element->getElements() as $textElement) {
                             if ($textElement instanceof \PhpOffice\PhpWord\Element\Text) {
                                $text .= $textElement->getText() . ' ';
                            }
                        }
                    }
                }
            }
            $content = $text;
        }

        // --- Phân tích nội dung ---
        // Sử dụng hàm phân tích mới dựa trên tag [TAG]
        $parsed_data = parse_content_with_tags($content);

        $title = $parsed_data['TITLE'] ?? '';
        $body = $parsed_data['CONTENT'] ?? '';

        if (empty($title)) {
            $_SESSION['import_message'] = "Không thể trích xuất được tiêu đề từ file. Vui lòng kiểm tra lại định dạng file.";
            $_SESSION['import_message_type'] = 'danger';
            header('Location: import.php?type=' . $importType); exit();
        }
        
        // --- Lưu vào cơ sở dữ liệu ---
        $tableName = ($importType === 'project') ? 'projects' : 'posts';
        $admin_id = $_SESSION['admin_id'];

        if ($tableName === 'projects') {
            $client = $parsed_data['CLIENT'] ?? 'Chưa xác định'; // Lấy thông tin client
            $stmt = $conn->prepare("INSERT INTO projects (title, client, description, status, created_by, created_at) VALUES (?, ?, ?, 'draft', ?, NOW())");
            $stmt->bind_param("sssi", $title, $client, $body, $admin_id);
        } elseif ($tableName === 'posts') {
            $slug = create_slug_from_import($title);
            $excerpt = $parsed_data['EXCERPT'] ?? ''; // Lấy thông tin excerpt
            
            // Nếu excerpt trống, tự tạo từ 150 ký tự đầu của content
            if (empty($excerpt) && !empty($body)) {
                $excerpt = mb_substr($body, 0, 150) . '...';
            }

            $stmt = $conn->prepare("INSERT INTO posts (title, slug, excerpt, content, status, created_by, created_at) VALUES (?, ?, ?, ?, 'draft', ?, NOW())");
            $stmt->bind_param("ssssi", $title, $slug, $excerpt, $body, $admin_id);
        } else {
            $_SESSION['import_message'] = "Loại import không hợp lệ.";
            $_SESSION['import_message_type'] = 'danger';
            header('Location: import.php?type=' . $importType); exit();
        }

        if ($stmt->execute()) {
            // Ghi log hoạt động
            $log_action = "Import file '" . basename($_FILES["fileToUpload"]["name"]) . "' với tiêu đề: " . $title;
            log_import_activity($conn, $log_action, ucfirst($importType));

            $_SESSION['import_message'] = "Import thành công file <strong>" . htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) . "</strong> với tiêu đề: <strong>" . htmlspecialchars($title) . "</strong>.";
            $_SESSION['import_message_type'] = 'success';
        } else {
            $_SESSION['import_message'] = "Lỗi khi lưu vào CSDL: " . $stmt->error;
            $_SESSION['import_message_type'] = 'danger';
        }

        $stmt->close();
        $conn->close();
        header('Location: import.php?type=' . $importType); exit();

    } catch (Exception $e) {
        $_SESSION['import_message'] = "Đã xảy ra lỗi khi xử lý file: " . $e->getMessage();
        $_SESSION['import_message_type'] = 'danger';
        header('Location: import.php?type=' . $importType); exit();
    }
} else {
    header('Location: import.php'); // Redirect nếu truy cập trực tiếp
    exit();
}
