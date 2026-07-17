<?php
// Bật hiển thị lỗi PHP để debug (nên xóa đi khi chạy thực tế)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pageTitle = 'Trang Chủ';
$pageStyles = ['css/home.css'];
require_once 'db.php'; // Đảm bảo đã kết nối CSDL
include 'includes/header.php';

// Lấy cả bài viết và dự án mới nhất cho carousel tin tức & sự kiện
$query_string = "
    (SELECT 
        'post' as item_type, 
        id, 
        title COLLATE utf8mb4_unicode_ci AS title, 
        slug COLLATE utf8mb4_unicode_ci AS slug, 
        image COLLATE utf8mb4_unicode_ci AS image, 
        excerpt COLLATE utf8mb4_unicode_ci AS excerpt, 
        created_at 
     FROM posts 
     WHERE status = 'published')
    UNION ALL
    (SELECT 
        'project' as item_type, 
        id, 
        title COLLATE utf8mb4_unicode_ci AS title, 
        CAST(id AS CHAR) COLLATE utf8mb4_unicode_ci as slug, 
        preview_image COLLATE utf8mb4_unicode_ci as image, 
        client COLLATE utf8mb4_unicode_ci as excerpt, 
        created_at 
     FROM projects 
     WHERE status = 'published')
    ORDER BY created_at DESC 
    LIMIT 6
";
$latest_items_query = $conn->query($query_string);
$latest_items = [];
if ($latest_items_query) {
    while($row = $latest_items_query->fetch_assoc()) {
        // Lấy đoạn trích và rút gọn nếu cần để đảm bảo giao diện nhất quán
        // Dùng strip_tags để loại bỏ HTML có thể có trong 'description' của dự án
        $plain_text = trim(strip_tags($row['excerpt'] ?? ''));
        
        // Rút gọn văn bản nếu nó quá dài
        if (mb_strlen($plain_text) > 120) {
            $row['excerpt'] = mb_substr($plain_text, 0, 120) . '...';
        } else {
            $row['excerpt'] = $plain_text;
        }

        // Đảm bảo hình ảnh rỗng sẽ hiển thị ảnh placeholder
        if (empty($row['image'])) {
            $row['image'] = null;
        }

        $latest_items[] = $row; 
    }
}

// Lấy các slide carousel từ database
$carousel_slides_query = $conn->query("SELECT * FROM carousel_slides WHERE status = 'active' ORDER BY sort_order ASC, created_at DESC");
$carousel_slides = [];
if ($carousel_slides_query) {
    while($row = $carousel_slides_query->fetch_assoc()) {
        $carousel_slides[] = $row;
    }
}


?>
<!-- Slideshow chính - Bootstrap Carousel -->
<section class="slideshow">
    <div id="mainCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
    <!-- Indicators -->
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
        <?php 
            $slide_count = !empty($carousel_slides) ? count($carousel_slides) : 4; // 4 là số slide mặc định
            for ($i = 1; $i < $slide_count; $i++): 
        ?>
            <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="<?php echo $i; ?>" aria-label="Slide <?php echo $i + 1; ?>"></button>
        <?php endfor; ?>
    </div>
    <!-- Carousel items -->
    <div class="carousel-inner">
        <?php if (!empty($carousel_slides)): // Nếu có slide trong CSDL ?>
            <?php foreach ($carousel_slides as $index => $slide): ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>" alt="">
                    <a href="<?php echo htmlspecialchars($slide['link'] ?? '#'); ?>" <?php if(!empty($slide['link'])) echo 'target="_blank"'; ?>>
                        <img src="<?php echo htmlspecialchars($slide['image_path']); ?>" class="d-block w-100" alt="<?php echo htmlspecialchars($slide['title'] ?? 'Carousel Slide'); ?>" width="1920" height="1080" <?php echo $index === 0 ? 'fetchpriority="high"' : 'loading="lazy"'; ?> decoding="async">
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: // Nếu không, hiển thị các slide mặc định ?>
            <!-- Slides mặc định nếu không có dữ liệu trong CSDL -->
            <div class="carousel-item active">
                <img src="assets/images/slideshow/TONG-HOP.png" class="d-block w-100" alt="" width="1920" height="1080" fetchpriority="high" decoding="async">
            </div>
            <div class="carousel-item">
                <img src="assets/images/slideshow/QC_XE.png" class="d-block w-100" alt="" width="1920" height="1080" loading="lazy" decoding="async">
            </div>
            <div class="carousel-item">
                <img src="assets/images/slideshow/QC-TD.png" class="d-block w-100" alt="" width="1920" height="1080" loading="lazy" decoding="async">
            </div>
            <div class="carousel-item">
                <img src="assets/images/slideshow/QC-KTS.png" class="d-block w-100" alt="" width="1920" height="1080" loading="lazy" decoding="async">
            </div>
        <?php endif; ?>
            <!-- Navigation buttons -->
            <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
    </div>
</section>
<!-- Phần số liệu thống kê -->
<section class="transit-stats" data-aos="fade-up">
    <h2 data-i18n="home.stats_title">TIÊN PHONG TRONG LĨNH VỰC TRANSIT ADVERTISING</h2>
    <h3 data-i18n="home.stats_subtitle">HỆ SINH THÁI ƯU THẾ CỦA CHÚNG TÔI</h3>

    <div class="stats-container">
        <div class="stat-item">
            <i class="fas fa-car statistic-icon"></i>
            <div class="stat-number" data-target="3000" data-prefix="+">0</div>
            <p data-i18n="home.stat1_desc">Hơn 3.000 đầu xe các loại bao gồm: xe tuyến liên tỉnh, xe buýt nội bộ, xe trung chuyển... vận hành liên tục với 6.500 chuyến/ngày giúp thương hiệu của bạn phủ sóng 39 tỉnh thành trên toàn quốc.</p>
        </div>

        <div class="stat-item">
            <i class="fas fa-users statistic-icon"></i>
            <div class="stat-number" data-target="100000" data-prefix="+">0</div>
            <p data-i18n="home.stat2_desc">Tiếp cận hơn 110 nghìn khách/ngày, phục vụ hơn 40 triệu khách/năm giúp gia tăng nhận diện thương hiệu.</p>
        </div>

        <div class="stat-item">
            <i class="fas fa-ticket-alt statistic-icon"></i>
            <div class="stat-number" data-target="250" data-prefix="+">0</div>
            <p data-i18n="home.stat3_desc">Hệ thống hơn 250 phòng vé có mặt khắp cả nước.</p>
        </div>

        <div class="stat-item">
            <i class="fas fa-handshake statistic-icon"></i>
            <div class="stat-number" data-target="100" data-prefix="+">0</div>
            <p data-i18n="home.stat4_desc">Chúng tôi đã hợp tác triển khai với hơn 100 đối tác. Giúp các thương hiệu tiếp cận khách hàng mục tiêu.</p>
        </div>
    </div>
</section>
<!-- Phần sứ mệnh và giá trị -->
<section class="mission-section" data-aos="fade-up">
    <div class="mission-block">
        <h3 class="mission-line">
            <span data-i18n="home.mission_title">VỚI SỨ MỆNH</span> <span class="blue-text">“<span data-i18n="home.mission_quote">LAN TỎA THƯƠNG HIỆU KHẮP MỌI NƠI</span>”</span>
        </h3>
        <h3 class="mission-sub" data-i18n="home.mission_subtitle">
            CHÚNG TÔI MANG LẠI CHO THƯƠNG HIỆU CỦA BẠN
        </h3>
    </div>
        <div class="features-grid">
            <div class="feature-item">
                <img src="assets/images/icon/customer.png" alt="" width="60" height="60" loading="lazy" decoding="async">
                <p data-i18n="home.feature1">Tiếp cận khách hàng mục tiêu</p>
            </div>
            <div class="feature-item">
                <img src="assets/images/icon/shared-vision.png" alt="" width="60" height="60" loading="lazy" decoding="async">
                <p data-i18n="home.feature2">Tầm điểm ánh nhìn</p>
            </div>
            <div class="feature-item">
                <img src="assets/images/icon/medal.png" alt="" width="60" height="60" loading="lazy" decoding="async">
                <p data-i18n="home.feature3">Chuyên nghiệp chất lượng</p>
            </div>
            <div class="feature-item">
                <img src="assets/images/icon/cash-flow.png" alt="" width="60" height="60" loading="lazy" decoding="async">
                <p data-i18n="home.feature4">Chi phí hợp lý</p>
            </div>
            <div class="feature-item">
                <img src="assets/images/icon/action.png" alt="" width="60" height="60" loading="lazy" decoding="async">
                <p data-i18n="home.feature5">Đa dạng dịch vụ</p>
            </div>
        </div>
</section>
<!-- Phần Dịch vụ -->
<section class="service-section" data-aos="fade-up">
  <!-- Header -->
  <div class="service-header">
    <h1 class="service-title" data-i18n="home.services_title">DỊCH VỤ CỦA CHÚNG TÔI</h1>
    <p class="service-description" data-i18n="home.services_desc">
      Chúng tôi mang đến một <b class="highlight">chiến lược 360 toàn diện</b>, kết nối tất cả các kênh truyền thông để tối ưu hiệu quả.<br>
      Với hệ thống các tuyến xe <b class="highlight">phủ sóng 34 tỉnh thành</b>, chúng tôi sẽ đưa thương hiệu của bạn tiếp cận mọi khu vực địa lý.
    </p>
  </div>

    <!-- Grid dịch vụ -->
    <div class="products-grid">
    <a href="ads-car.php" class="product-item">
        <img src="assets/images/slideshow/QC_XE.jpg" alt="" width="800" height="400" fetchpriority="high" decoding="async">
        <div class="product-info">
            <h3 data-i18n="home.service1">QUẢNG CÁO TRÊN XE</h3>
        </div>
    </a>

    <a href="QC-tram-dung.php" class="product-item">
        <img src="assets/images/slideshow/QC-TD.jpg" alt="" width="800" height="400" fetchpriority="high" decoding="async">
        <div class="product-info">
            <h3 data-i18n="home.service2">QUẢNG CÁO TRẠM DỪNG</h3>
        </div>
    </a>

    <a href="QC-ky-thuat-so.php" class="product-item">
        <img src="assets/images/slideshow/QC-KTS.jpg" alt="" width="800" height="400" fetchpriority="high" decoding="async">
        <div class="product-info">
            <h3 data-i18n="home.service3">QUẢNG CÁO KỸ THUẬT SỐ</h3>
        </div>
    </a>
    </div>
</section>
<!-- Tin tức & Sự kiện -->
<section class="news-section" data-aos="fade-up">
    <div class="container">
        <div class="section-title">
            <h2 data-i18n="home.news_title">TIN TỨC & SỰ KIỆN</h2>
            <p data-i18n="home.news_desc">Cập nhật những thông tin và sự kiện mới nhất từ FUTA ADS</p>
        </div>

        <?php if (!empty($latest_items)): ?>
            <div class="news-slider-wrapper" data-aos="fade-up" data-aos-delay="200">
                <button class="news-control prev"><i class="fas fa-chevron-left"></i></button>
                <!-- Slider -->
                <div class="news-slider">
                    <?php 
                    // Nhân bản mảng để đảm bảo luôn đủ độ dài cuộn xoay vòng trên mọi màn hình
                    foreach (array_merge($latest_items, $latest_items) as $item): 
                        $detail_url = ($item['item_type'] === 'post') 
                            ? "news_single.php?slug=" . htmlspecialchars($item['slug'])
                            : "project-detail.php?id=" . htmlspecialchars($item['id']);
                        if (empty($item['image'])) continue; // Bỏ qua nếu không có ảnh
                    ?>
                    <div class="news-slide" 
                         data-type="<?= $item['item_type'] ?>" 
                         data-id="<?= $item['id'] ?>" 
                         data-slug="<?= htmlspecialchars($item['slug']) ?>"
                         onclick="openContentModal(this)"
                         style="cursor: pointer;">
                        <div class="news-card-link">
                            <div class="news-card">
                                <?php if (!empty($item['image'])): ?>
                                    <div class="news-card-img-wrapper">
                                        <img src="<?= htmlspecialchars($item['image']); ?>" alt="" width="350" height="220" loading="lazy" decoding="async">
                                    </div>
                                <?php endif; ?>

                                <div class="news-card-body">
                                    <div class="mb-2">
                                        <span 
                                            class="news-card-badge <?= ($item['item_type'] === 'post') ? 'badge-news' : 'badge-project' ?>"
                                            data-i18n="<?= ($item['item_type'] === 'post') ? 'news.filter_news' : 'news.filter_project' ?>">
                                            <?= ($item['item_type'] === 'post') ? 'Tin Tức' : 'Dự án' ?>
                                        </span>
                                    </div>
                                    <h5 class="news-card-title" 
                                        data-i18n-key="<?= $item['item_type'] ?>_title_<?= $item['id'] ?>" 
                                        data-i18n-text="<?= htmlspecialchars($item['title']); ?>">
                                        <?= htmlspecialchars($item['title']); ?>
                                    </h5>

                                    <p class="news-card-text"
                                       data-i18n-key="<?= $item['item_type'] ?>_excerpt_<?= $item['id'] ?>"
                                       data-i18n-text="<?= htmlspecialchars($item['excerpt']); ?>">
                                        <?= htmlspecialchars($item['excerpt']); ?>
                                    </p>

                                    <div class="news-card-date">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?= date('d-m-Y', strtotime($item['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button class="news-control next"><i class="fas fa-chevron-right"></i></button>
            </div>
        <?php else: ?>
            <p class="text-center">Chưa có tin tức nào để hiển thị.</p>
        <?php endif; ?>
    </div>
</section>

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
<!-- Phần logo đối tác mới vào đây -->
<section class="partners-section" data-aos="fade-up">
        <div class="section-title">
            <h2 data-i18n="home.partners_title">ĐỐI TÁC / KHÁCH HÀNG</h2>
        </div>
        <div class="partners-logos-wrapper">
        <div class="partners-logos">
            <div class="partner-logo"><img src="assets/images/client/KIMLONG.png" alt="" width="160" height="80" loading="lazy" decoding="async"></div>
            <div class="partner-logo"><img src="assets/images/client/tienphong.png" alt="" width="160" height="80" loading="lazy" decoding="async"></div>
            <div class="partner-logo"><img src="assets/images/client/SHB.png" alt="" width="160" height="80" loading="lazy" decoding="async"></div>
            <div class="partner-logo"><img src="assets/images/client/uniben.png" alt="" width="160" height="80" loading="lazy" decoding="async"></div>
            <div class="partner-logo"><img src="assets/images/client/samsung.png" alt="" width="160" height="80" loading="lazy" decoding="async"></div>
            <div class="partner-logo"><img src="assets/images/client/hoaphat.png" alt="" width="160" height="80" loading="lazy" decoding="async"></div>
            <div class="partner-logo"><img src="assets/images/client/tâmnh.png" alt="" width="160" height="80" loading="lazy" decoding="async"></div>
            <div class="partner-logo"><img src="assets/images/client/cholimex.png" alt="" width="160" height="80" loading="lazy" decoding="async"></div>
            <div class="partner-logo"><img src="assets/images/client/vnpay.png" alt="" width="160" height="80" loading="lazy" decoding="async"></div>
            <div class="partner-logo"><img src="assets/images/client/daikin.png" alt="" width="160" height="80" loading="lazy" decoding="async"></div>
            <div class="partner-logo"><img src="assets/images/client/TE.png" alt="" width="160" height="80" loading="lazy" decoding="async"></div>
            <div class="partner-logo"><img src="assets/images/client/bvmsg.png" alt="" width="160" height="80" loading="lazy" decoding="async"></div>
            <div class="partner-logo"><img src="assets/images/client/acecook.png" alt="" width="160" height="80" loading="lazy" decoding="async"></div>
            <div class="partner-logo"><img src="assets/images/client/momo.png" alt="" width="160" height="80" loading="lazy" decoding="async"></div>
            <div class="partner-logo"><img src="assets/images/client/cellphones.jpg" alt="" width="160" height="80" loading="lazy" decoding="async"></div>
            <div class="partner-logo"><img src="assets/images/client/DULUX.png" alt="" width="160" height="80" loading="lazy" decoding="async"></div>         
        </div>
        </div>
</section>
<section class="advertising" data-aos="fade-up">
   <!-- Banner -->
   <div class="banner-container">
      <div class="banner-content">
        <p class="sub-title" data-i18n="home.banner_subtitle">HÃY ĐỂ CHÚNG TÔI KIẾN TẠO</p>
        <h2 class="main-title" data-i18n="home.banner_title">CHIẾN DỊCH TRUYỀN THÔNG CỦA BẠN</h2>
        <a href="contact.php" class="btn-register" data-i18n="home.banner_btn">ĐĂNG KÝ TƯ VẤN</a>
      </div>
    </div>
    <!-- Logo Section -->
    <div class="logo-section">
  <a href="https://futabus.vn" target="_blank">
    <img src="assets/images/logo/BusLines.png" alt="" width="300" height="150" loading="lazy" decoding="async">
  </a>
  <a href="https://futacitybus.vn/" target="_blank">
    <img src="assets/images/logo/CityBus.png" alt="" width="300" height="150" loading="lazy" decoding="async">
  </a>
  <a href="https://futaexpress.vn" target="_blank">
    <img src="assets/images/logo/Express.png" alt="" width="300" height="150" loading="lazy" decoding="async">
  </a>
  <a >
    <img src="assets/images/logo/Advertising.png" alt="" width="300" height="150" loading="lazy" decoding="async">
  </a>
  <a href="https://futaland.vn" target="_blank">
    <img src="assets/images/logo/Land.png" alt="" width="300" height="150" loading="lazy" decoding="async">
  </a>
   <a >
    <img src="assets/images/logo/RestStop.png" alt="" width="300" height="150" loading="lazy" decoding="async">
  </a>
    </div>
</section>

<!-- Nút chuyển trang trên Mobile -->
<a href="contact.php" class="mobile-page-nav-btn prev"><i class="fas fa-chevron-left"></i></a>
<a href="about.php" class="mobile-page-nav-btn next"><i class="fas fa-chevron-right"></i></a>

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
