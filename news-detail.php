<?php
require_once 'db.php';

if (!isset($_GET['slug'])) {
    header("Location: news.php");
    exit;
}

$slug = $_GET['slug'];
$stmt = $conn->prepare("SELECT p.*, u.fullname as author_name FROM posts p LEFT JOIN users u ON p.created_by = u.id WHERE p.slug = ? AND p.status = 'published' LIMIT 1");
$stmt->bind_param('s', $slug);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

if (!$post) {
    http_response_code(404);
    echo "Bài viết không tồn tại hoặc chưa được xuất bản.";
    exit;
}

// Lấy các bài viết liên quan (các bài viết mới nhất trừ bài hiện tại)
$related_posts = [];
$related_stmt = $conn->prepare("SELECT id, title, slug, image, excerpt, created_at FROM posts WHERE status = 'published' AND id != ? ORDER BY created_at DESC LIMIT 3");
$related_stmt->bind_param("i", $post['id']);
$related_stmt->execute();
$related_result = $related_stmt->get_result();
while($row = $related_result->fetch_assoc()) {
    $related_posts[] = $row;
}
$related_stmt->close();

$pageStyles = ['css/news-detail.css'];
include 'includes/header.php';
?>

<main class="container my-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="news.php">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="news.php?type=post">Tin Tức</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($post['title']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <article class="news-post">
                <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                <p class="post-meta text-muted">
                    <i class="fas fa-user"></i> Đăng bởi <strong><?php echo htmlspecialchars($post['author_name'] ?? 'FUTA Admin'); ?></strong> | 
                    <i class="fas fa-calendar-alt"></i> Ngày <?php echo date('d/m/Y', strtotime($post['created_at'])); ?>
                </p>

                <?php if (!empty($post['image'])): ?>
                    <img src="/FUTA_PHP/<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="img-fluid rounded my-4 post-main-image">
                <?php endif; ?>

                <div class="post-content">
                    <?php echo $post['content']; // Dữ liệu từ TinyMCE đã là HTML, nên echo trực tiếp để trình duyệt render đúng định dạng. ?>
                </div>

                <!-- Tags -->
                <?php 
                    $tags = !empty($post['tags']) ? json_decode($post['tags'], true) : [];
                    if (!empty($tags)):
                ?>
                <div class="post-tags mt-4">
                    <strong><i class="fas fa-tags"></i> Thẻ:</strong>
                    <?php foreach($tags as $tag): ?>
                        <a href="news.php?tag=<?php echo urlencode($tag); ?>" class="badge bg-secondary text-decoration-none"><?php echo htmlspecialchars($tag); ?></a>
                    <?php endforeach; ?> 
                </div>
                <?php endif; ?>
            </article>
        </div>
    </div>

    <!-- Related Posts Section -->
    <?php if (!empty($related_posts)): ?>
    <div class="related-posts mt-5">
        <h3 class="related-posts-title">Bài viết liên quan</h3>
        <div class="row">
            <?php foreach($related_posts as $related): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <a href="news-detail.php?slug=<?php echo htmlspecialchars($related['slug']); ?>" class="card-link-wrapper">
                        <div class="card post-card post-card-list h-100">
                            <img src="/FUTA_PHP/<?php echo htmlspecialchars($related['image'] ?? 'assets/img/placeholder.png'); ?>" class="card-img-top post-card-list-img" alt="<?php echo htmlspecialchars($related['title']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($related['title']); ?></h5>
                                <p class="card-text text-muted small mb-2"><i class="fas fa-calendar-alt me-1"></i> <?php echo date('d/m/Y', strtotime($related['created_at'])); ?></p>
                                <p class="card-text excerpt-text"><?php echo htmlspecialchars($related['excerpt']); ?></p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>