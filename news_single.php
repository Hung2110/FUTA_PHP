<?php 
require_once 'db.php';
$pageStyles = ['css/project-detail.css'];
// Kiểm tra xem Slug có được cung cấp không
if (!isset($_GET['slug']) || empty($_GET['slug'])) {
    header('Location: news.php');
    exit;
}

$slug = $_GET['slug'];

// Lấy thông tin bài viết từ CSDL
// Lưu ý: Tôi giả định cột nội dung chi tiết tên là 'content'. Nếu trong DB của bạn tên khác (ví dụ: body, description), hãy sửa lại.
$stmt = $conn->prepare("SELECT * FROM posts WHERE slug = ? AND status = 'published'");
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Nếu không tìm thấy bài viết
    $pageTitle = "Không tìm thấy bài viết";
    $pageStyles = ['css/project-detail.css'];
    include 'includes/header.php';
    echo "<div class='container my-5 text-center'><h1 data-i18n='news.not_found_desc'>Bài viết không tồn tại hoặc đã bị xóa.</h1><a href='news.php' class='btn btn-primary mt-3' data-i18n='news.back_to_news'>Quay lại trang Tin Tức</a></div>";
    include 'includes/footer.php';
    exit;
}

$post = $result->fetch_assoc();
$stmt->close();

// Lấy các bài viết mới nhất cho Sidebar (trừ bài hiện tại)
$recent_posts_stmt = $conn->prepare("SELECT title, slug, image, created_at FROM posts WHERE slug != ? AND status = 'published' ORDER BY created_at DESC LIMIT 5");
$recent_posts_stmt->bind_param("s", $slug);
$recent_posts_stmt->execute();
$recent_posts_result = $recent_posts_stmt->get_result();
$recent_posts = [];
while($row = $recent_posts_result->fetch_assoc()) {
    $recent_posts[] = $row;
}
$recent_posts_stmt->close();

// Thiết lập tiêu đề trang và file CSS (Tái sử dụng CSS của project-detail để giống giao diện)
$pageTitle = htmlspecialchars($post['title']);
$pageStyles = ['css/project-detail.css']; 
include 'includes/header.php'; 
?>

<script>
    // Lấy URL hiện tại cho các nút chia sẻ
    const currentUrl = window.location.href;
    const projectTitle = "<?php echo htmlspecialchars($post['title'], ENT_QUOTES); ?>";
</script>
<!-- Sử dụng class project-detail-container để kế thừa CSS của trang dự án -->
<div class="project-detail-container">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php" data-i18n="news.breadcrumb_home">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="news.php" data-i18n="news.breadcrumb_news">Tin Tức</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($post['title']); ?></li>
            </ol>
        </nav>

        <div class="row">
            <!-- Main Content Column -->
            <div class="col-lg-8">
                <div class="main-content-wrapper">
                    <h1 class="project-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                    
                    <div class="project-meta">
                        <!-- Ẩn phần Khách hàng vì đây là Tin Tức, chỉ hiện ngày tháng -->
                        <span><i class="fas fa-calendar-alt"></i> <strong data-i18n="news.post_date">Ngày đăng:</strong> <?php echo date('d/m/Y', strtotime($post['created_at'])); ?></span>
                        <?php if(!empty($post['author'])): ?>
                            <span class="ms-3"><i class="fas fa-user"></i> <strong data-i18n="news.author">Tác giả:</strong> <?php echo htmlspecialchars($post['author']); ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Social Share Buttons -->
                    <div class="social-share">
                        <a id="share-facebook" href="#" target="_blank" class="share-btn facebook"><i class="fab fa-facebook-f"></i> <span data-i18n="news.share">Chia sẻ</span></a>
                        <a id="share-zalo" href="#" target="_blank" class="share-btn zalo"><img src="https://upload.wikimedia.org/wikipedia/commons/9/91/Icon_of_Zalo.svg" alt="Zalo"> <span data-i18n="news.share">Chia sẻ</span></a>
                        <button id="copy-link" class="share-btn copy"><i class="fas fa-link"></i> <span data-i18n="news.copy">Sao chép</span></button>
                        <span id="copy-success" style="display:none; margin-left: 10px; color: #28a745; align-items: center; font-weight: 500;" data-i18n="news.copied"><i class="fas fa-check-circle me-1"></i> Đã sao chép!</span>
                    </div>

                    <div class="project-content-body">
                        <?php if (!empty($post['image'])): ?>
                            <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="img-fluid rounded project-main-image mb-4">
                        <?php endif; ?>
                        
                        <div class="project-description">
                            <?php 
                            // Hiển thị nội dung bài viết. 
                            // Nếu nội dung trong DB là HTML (từ CKEditor/Summernote), hãy dùng echo trực tiếp.
                            // Nếu là text thuần, hãy dùng nl2br(). Dưới đây giả định là HTML hoặc Text có xuống dòng.
                            echo $post['content'] ?? nl2br(htmlspecialchars($post['excerpt'] ?? '')); 
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Column -->
            <div class="col-lg-4">
                <aside class="sidebar">
                    <!-- Search Widget -->
                    <div class="sidebar-widget">
                        <h3 class="widget-title" data-i18n="news.search">Tìm kiếm</h3>
                        <form class="search-form" action="news.php" method="GET">
                            <input type="text" name="search" data-i18n-placeholder="news.search_placeholder" placeholder="Nhập từ khóa...">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </form>
                    </div>

                    <!-- Latest Posts Widget -->
                    <?php if (!empty($recent_posts)): ?>
                    <div class="sidebar-widget">
                        <h3 class="widget-title" data-i18n="news.latest_posts">Bài viết mới nhất</h3>
                        <ul class="latest-posts-list">
                            <?php foreach($recent_posts as $item): ?>
                            <li>
                                <a href="news_single.php?slug=<?php echo htmlspecialchars($item['slug']); ?>">
                                    <img src="<?php echo htmlspecialchars(!empty($item['image']) ? $item['image'] : 'assets/img/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                    <span><?php echo htmlspecialchars($item['title']); ?></span>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </aside>
            </div>
        </div>
    </div>
</div>

<!-- Tái sử dụng JS của project detail cho chức năng share -->
<script src="js/project-detail.js"></script>
<?php include 'includes/footer.php'; ?>