<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); // Cho phép truy cập từ mọi domain, có thể thay đổi thành domain của bạn

require_once 'db.php';

$resource = $_GET['resource'] ?? null;
$id = $_GET['id'] ?? null;
$slug = $_GET['slug'] ?? null;

$response = [];

try {
    switch ($resource) {
        case 'projects':
            if ($id) {
                $stmt = $conn->prepare("SELECT id, title, client, description, preview_image, preview_video, status, created_at FROM projects WHERE id = ? AND status = 'published' LIMIT 1");
                $stmt->bind_param("i", $id);
            } else {
                $stmt = $conn->prepare("SELECT id, title, client as excerpt, description, preview_image, preview_video, status, created_at FROM projects WHERE status = 'published' ORDER BY created_at DESC");
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            if ($id) {
                $response = $result->fetch_assoc();
            } else {
                $response = $result->fetch_all(MYSQLI_ASSOC);
            }
            $stmt->close();
            break;

        case 'posts':
            if ($slug) {
                $stmt = $conn->prepare("SELECT p.id, p.title, p.slug, p.excerpt, p.content, p.image, p.tags, p.created_at, u.fullname as author FROM posts p LEFT JOIN users u ON p.created_by = u.id WHERE p.slug = ? AND p.status = 'published' LIMIT 1");
                $stmt->bind_param("s", $slug);
            } elseif ($id) {
                $stmt = $conn->prepare("SELECT p.id, p.title, p.slug, p.excerpt, p.content, p.image, p.tags, p.created_at, u.fullname as author FROM posts p LEFT JOIN users u ON p.created_by = u.id WHERE p.id = ? AND p.status = 'published' LIMIT 1");
                $stmt->bind_param("i", $id);
            } else {
                $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
                $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
                $stmt = $conn->prepare("SELECT p.id, p.title, p.slug, p.excerpt, p.image, p.tags, p.created_at, u.fullname as author FROM posts p LEFT JOIN users u ON p.created_by = u.id WHERE p.status = 'published' ORDER BY p.created_at DESC LIMIT ? OFFSET ?");
                $stmt->bind_param("ii", $limit, $offset);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            if ($id || $slug) {
                $response = $result->fetch_assoc();
            } else {
                $response = $result->fetch_all(MYSQLI_ASSOC);
            }
            $stmt->close();
            break;

        case 'jobs':
            if ($id) {
                $stmt = $conn->prepare("SELECT id, title, industry, position, branch, description, description_file, created_at FROM jobs WHERE id = ? AND status = 'open' LIMIT 1");
                $stmt->bind_param("i", $id);
            } else {
                $stmt = $conn->prepare("SELECT id, title, industry, position, branch, created_at FROM jobs WHERE status = 'open' ORDER BY created_at DESC");
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            if ($id) {
                $response = $result->fetch_assoc();
            } else {
                $response = $result->fetch_all(MYSQLI_ASSOC);
            }
            $stmt->close();
            break;

        case 'users':
            if ($id) {
                $stmt = $conn->prepare("SELECT id, username, fullname, email, phone, bio, role, status FROM users WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $response = $result->fetch_assoc();
                $stmt->close();
            }
            break;

        default:
            http_response_code(404);
            $response = ['error' => 'Resource not found.'];
            break;
    }

    if ($response === null) {
        http_response_code(404);
        $response = ['error' => 'Item not found.'];
    }

} catch (Exception $e) {
    http_response_code(500);
    $response = ['error' => 'An internal server error occurred.'];
    // Ghi log lỗi ra file để debug, không hiển thị chi tiết cho người dùng
    // error_log($e->getMessage());
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

?>