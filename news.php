<?php
require_once 'db.php';

$pageStyles = ['css/news.css'];
$pageTitle = "Tin Tức & Dự án";
include 'includes/header.php'; 
$type = isset($_GET['type']) ? $_GET['type'] : 'all';

// Xây dựng truy vấn lấy dữ liệu từ cả bảng posts và projects
$sql_posts = "SELECT 'post' as type, id, title COLLATE utf8mb4_unicode_ci AS title, slug COLLATE utf8mb4_unicode_ci AS slug, image COLLATE utf8mb4_unicode_ci AS image, excerpt COLLATE utf8mb4_unicode_ci AS excerpt, created_at FROM posts WHERE status = 'published'";
$sql_projects = "SELECT 'project' as type, id, title COLLATE utf8mb4_unicode_ci AS title, CAST(id AS CHAR) COLLATE utf8mb4_unicode_ci as slug, preview_image COLLATE utf8mb4_unicode_ci as image, client COLLATE utf8mb4_unicode_ci as excerpt, created_at FROM projects WHERE status = 'published'";

if ($type === 'post') {
    $sql = "$sql_posts ORDER BY created_at DESC";
} elseif ($type === 'project') {
    $sql = "$sql_projects ORDER BY created_at DESC";
} else {
    $sql = "($sql_posts) UNION ALL ($sql_projects) ORDER BY created_at DESC";
}

$items = [];
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Xử lý nội dung tóm tắt
        $plain_text = trim(strip_tags($row['excerpt'] ?? ''));
        if (mb_strlen($plain_text) > 120) {
            $row['excerpt'] = mb_substr($plain_text, 0, 120) . '...';
        } else {
            $row['excerpt'] = $plain_text;
        }
        $items[] = $row;
    }
}
?>

<div class="news-banner">
    <div class="container">
        <h1 data-i18n="news.page_title">Tin Tức & Dự án</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a href="news.php" class="text-white" data-i18n="news.breadcrumb_home">Trang chủ</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page" data-i18n="news.page_title">Tin Tức & Dự án</li>
            </ol>
        </nav>
    </div>
</div>

<main class="container my-5">
    <div class="row">
        <div class="col-lg-8">
            <!-- Menu phân loại -->
            <div class="filter-menu mb-4 d-flex justify-content-center gap-2">
                <a href="?type=all" class="btn <?php echo $type == 'all' ? 'btn-primary' : 'btn-outline-primary'; ?> rounded-pill px-4" data-i18n="news.filter_all">Tất cả</a>
                <a href="?type=post" class="btn <?php echo $type == 'post' ? 'btn-primary' : 'btn-outline-primary'; ?> rounded-pill px-4" data-i18n="news.filter_news">Tin Tức</a>
                <a href="?type=project" class="btn <?php echo $type == 'project' ? 'btn-primary' : 'btn-outline-primary'; ?> rounded-pill px-4" data-i18n="news.filter_project">Dự án</a>
            </div>

            <div class="row">
                <?php if (empty($items)): ?>
                    <div class="col-12 text-center">
                        <p data-i18n="news.empty_content">Chưa có nội dung nào.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($items as $item): 
                        $link = ($item['type'] === 'post') ? 'news_single.php?slug=' . htmlspecialchars($item['slug']) : 'project-detail.php?id=' . htmlspecialchars($item['id']);
                        $badge_text = ($item['type'] === 'post') ? 'Tin Tức' : 'Dự án';
                        $badge_class = ($item['type'] === 'post') ? 'bg-info' : 'bg-success';
                        $badge_i18n = ($item['type'] === 'post') ? 'news.filter_news' : 'news.filter_project';
                    ?>
                        <div class="col-md-6 mb-4">
                            <a href="<?php echo $link; ?>" class="post-card-link">
                                <div class="card h-100 post-card shadow-sm border-0">
                                    <div class="img-wrapper position-relative">
                                        <span class="badge <?php echo $badge_class; ?> position-absolute top-0 start-0 m-2" data-i18n="<?php echo $badge_i18n; ?>"><?php echo $badge_text; ?></span>
                                        <img src="<?php echo htmlspecialchars($item['image'] ?? 'assets/img/placeholder.png'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title text-truncate-2"><?php echo htmlspecialchars($item['title']); ?></h5>
                                        <p class="card-text text-muted small mb-2">
                                            <i class="fas fa-calendar-alt me-1"></i> <?php echo date('d/m/Y', strtotime($item['created_at'])); ?>
                                        </p>
                                        <p class="card-text flex-grow-1 text-truncate-3"><?php echo htmlspecialchars($item['excerpt']); ?></p>
                                        <span class="read-more text-primary fw-bold"><span data-i18n="news.read_more">Xem chi tiết</span> <i class="fas fa-arrow-right small"></i></span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="sidebar ps-lg-4">
                <div class="widget mb-5">
                    <h4 class="widget-title" data-i18n="news.search">Tìm kiếm</h4>
                    <form action="" class="search-form">
                        <div class="input-group">
                            <input type="text" class="form-control" data-i18n-placeholder="news.search_placeholder" placeholder="Nhập từ khóa...">
                            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                </div>

                <div class="widget mb-5">
                    <h4 class="widget-title" data-i18n="news.recently_updated">Mới cập nhật</h4>
                    <div class="recent-posts">
                        <?php 
                        $recent = array_slice($items, 0, 5);
                        foreach($recent as $recent_item): 
                            $recent_link = ($recent_item['type'] === 'post') ? 'news_single.php?slug=' . htmlspecialchars($recent_item['slug']) : 'project-detail.php?id=' . htmlspecialchars($recent_item['id']);
                        ?>
                        <div class="recent-item d-flex mb-3">
                            <div class="flex-shrink-0">
                                <img src="<?php echo htmlspecialchars($recent_item['image'] ?? 'assets/img/placeholder.png'); ?>" alt="" class="rounded" width="80" height="60" style="object-fit: cover;">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1"><a href="<?php echo $recent_link; ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($recent_item['title']); ?></a></h6>
                                <small class="text-muted"><i class="far fa-clock me-1"></i><?php echo date('d/m/Y', strtotime($recent_item['created_at'])); ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>