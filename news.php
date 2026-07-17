<?php
require_once 'db.php';

$pageStyles = ['css/news.css'];
$pageTitle = "Tin Tức & Dự Án";
$current_lang = isset($_COOKIE['language']) ? $_COOKIE['language'] : 'vi';
include 'includes/header.php'; 
$type = isset($_GET['type']) ? $_GET['type'] : 'all';

// Xây dựng truy vấn lấy dữ liệu từ cả bảng posts và projects
$sql_posts = "SELECT 'post' as type, id, title COLLATE utf8mb4_unicode_ci AS title, slug COLLATE utf8mb4_unicode_ci AS slug, image COLLATE utf8mb4_unicode_ci AS image, excerpt COLLATE utf8mb4_unicode_ci AS excerpt, created_at FROM posts WHERE status = 'published'";
$sql_projects = "SELECT 'project' as type, id, title COLLATE utf8mb4_unicode_ci AS title, CAST(id AS CHAR) COLLATE utf8mb4_unicode_ci as slug, preview_image COLLATE utf8mb4_unicode_ci as image, client COLLATE utf8mb4_unicode_ci as excerpt, created_at FROM projects WHERE status = 'published'";

// Thêm LIMIT 50 để tránh sập bộ nhớ khi lượng bài viết quá lớn
if ($type === 'post') {
    $sql = "$sql_posts ORDER BY created_at DESC LIMIT 50";
} elseif ($type === 'project') {
    $sql = "$sql_projects ORDER BY created_at DESC LIMIT 50";
} else {
    $sql = "($sql_posts) UNION ALL ($sql_projects) ORDER BY created_at DESC LIMIT 50";
}

$items = [];
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Xử lý đa ngôn ngữ
        if ($current_lang === 'en') {
            $row['title'] = !empty($row['title_en']) ? $row['title_en'] : $row['title'];
            $row['excerpt'] = !empty($row['excerpt_en']) ? $row['excerpt_en'] : $row['excerpt'];
        } elseif ($current_lang === 'cn') {
            $row['title'] = !empty($row['title_cn']) ? $row['title_cn'] : $row['title'];
            $row['excerpt'] = !empty($row['excerpt_cn']) ? $row['excerpt_cn'] : $row['excerpt'];
        }

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
        <h1 data-i18n="news.page_title">Tin Tức & Dự Án</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center mb-0" style="background: transparent; padding: 0;">
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
                            <a href="<?php echo $link; ?>" class="post-card-link text-decoration-none">
                                <div class="card h-100 post-card shadow-sm border-0 <?php if (empty($item['image'])) echo 'no-image'; ?>">
                                    <?php if (!empty($item['image'])): ?>
                                        <div class="img-wrapper position-relative">
                                            <img src="<?php echo htmlspecialchars($item['image']); ?>" class="card-img-top" alt="" width="400" height="250" loading="lazy" decoding="async">
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body d-flex flex-column">
                                        <div class="mb-2">
                                            <span class="badge <?php echo $badge_class; ?>" data-i18n="<?php echo $badge_i18n; ?>"><?php echo $badge_text; ?></span>
                                        </div>
                                        <h5 class="card-title text-truncate-2"
                                            data-i18n-key="<?php echo $item['type']; ?>_title_<?php echo $item['id']; ?>"
                                            data-i18n-text="<?php echo htmlspecialchars($item['title']); ?>"
                                        ><?php echo htmlspecialchars($item['title']); ?></h5>
                                        <p class="card-text text-muted small mb-2">
                                            <i class="fas fa-calendar-alt me-1"></i> <?php echo date('d/m/Y', strtotime($item['created_at'])); ?>
                                        </p>
                                        <p class="card-text flex-grow-1 text-truncate-3"
                                           data-i18n-key="<?php echo $item['type']; ?>_excerpt_<?php echo $item['id']; ?>"
                                           data-i18n-text="<?php echo htmlspecialchars($item['excerpt']); ?>"
                                        ><?php echo htmlspecialchars($item['excerpt']); ?></p>
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
                            $recent_badge_text = ($recent_item['type'] === 'post') ? 'Tin Tức' : 'Dự án';
                        ?>
                        <a href="<?php echo $recent_link; ?>" class="recent-item-link text-decoration-none">
                            <div class="recent-item d-flex mb-3">
                            <?php if (!empty($recent_item['image'])): ?>
                                <div class="flex-shrink-0">
                                    <img src="<?php echo htmlspecialchars($recent_item['image']); ?>" alt="" class="rounded" width="100" height="75" style="object-fit: cover;" loading="lazy" decoding="async">
                                </div>
                            <?php endif; ?>
                            <div class="flex-grow-1 <?php if (!empty($recent_item['image'])) echo 'ms-3'; ?>">
                                <h6 class="mb-1"><span class="text-decoration-none text-dark"
                                    data-i18n-key="<?php echo $recent_item['type']; ?>_title_<?php echo $recent_item['id']; ?>"
                                    data-i18n-text="<?php echo htmlspecialchars($recent_item['title']); ?>"
                                ><?php echo htmlspecialchars($recent_item['title']); ?></span></h6>
                                <small class="text-muted"><i class="far fa-clock me-1"></i><?php echo date('d/m/Y', strtotime($recent_item['created_at'])); ?></small>
                            </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modal hiển thị nội dung chi tiết -->
<div class="modal fade" id="contentModal" tabindex="-1" aria-labelledby="contentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="contentModalLabel">Đang tải...</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="contentModalBody">
        <div class="text-center p-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Nút chuyển trang trên Mobile -->
<a href="QC-ky-thuat-so.php" class="mobile-page-nav-btn prev"><i class="fas fa-chevron-left"></i></a>
<a href="recruitment.php" class="mobile-page-nav-btn next"><i class="fas fa-chevron-right"></i></a>

<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Hàm mở modal và tải nội dung
    window.openContentModal = function(element) {
        const type = element.dataset.type;
        const id = element.dataset.id;
        const slug = element.dataset.slug;

        const modal = new bootstrap.Modal(document.getElementById('contentModal'));
        const modalTitle = document.getElementById('contentModalLabel');
        const modalBody = document.getElementById('contentModalBody');

        // Reset modal
        modalTitle.textContent = 'Đang tải...';
        modalBody.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>`;
        modal.show();

        // Xác định endpoint API
        let apiUrl = 'api.php?resource=';
        if (type === 'post') {
            apiUrl += `posts&slug=${slug}`;
        } else {
            apiUrl += `projects&id=${id}`;
        }

        // Gọi API để lấy dữ liệu
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                if (data && !data.error) {
                    modalTitle.textContent = data.title;
                    // Hiển thị nội dung, ưu tiên 'content' cho bài viết và 'description' cho dự án
                    modalBody.innerHTML = `
                        <img src="${data.image || data.preview_image}" class="img-fluid rounded mb-3" alt="${data.title}">
                        <div>${data.content || data.description}</div>
                    `;
                }
            });
    }
});
</script>