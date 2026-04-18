<?php
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
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <?php if (!empty($slide['link'])): ?><a href="<?php echo htmlspecialchars($slide['link']); ?>" target="_blank"><?php endif; ?>
                    <img src="<?php echo htmlspecialchars($slide['image_path']); ?>" class="d-block w-100" alt="<?php echo htmlspecialchars($slide['title'] ?? 'Carousel Slide'); ?>">
                    <?php if (!empty($slide['link'])): ?></a><?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: // Nếu không, hiển thị các slide mặc định ?>
            <!-- Slides mặc định nếu không có dữ liệu trong CSDL -->
            <div class="carousel-item active">
                <img src="assets/images/slideshow/TONG-HOP.png" class="d-block w-100" alt="Banner 1">
            </div>
            <div class="carousel-item">
                <img src="assets/images/slideshow/QC_XE.png" class="d-block w-100" alt="Banner 2">
            </div>
            <div class="carousel-item">
                <img src="assets/images/slideshow/QC-TD.png" class="d-block w-100" alt="Banner 3">
            </div>
            <div class="carousel-item">
                <img src="assets/images/slideshow/QC-KTS.png" class="d-block w-100" alt="Banner 4">
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
<section class="transit-stats">
    <h2 data-i18n="home.stats_title">DẪN ĐẦU LĨNH VỰC TRANSIT ADVERTISING</h2>
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
<section class="mission-section">
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
                <img src="assets/images/icon/customer.png" alt="Icon tiếp cận khách hàng" loading="lazy">
                <p data-i18n="home.feature1">Tiếp cận khách hàng mục tiêu</p>
            </div>
            <div class="feature-item">
                <img src="assets/images/icon/shared-vision.png" alt="Icon tầm nhìn" loading="lazy">
                <p data-i18n="home.feature2">Tầm điểm ánh nhìn</p>
            </div>
            <div class="feature-item">
                <img src="assets/images/icon/medal.png" alt="Icon chuyên nghiệp" loading="lazy">
                <p data-i18n="home.feature3">Chuyên nghiệp chất lượng</p>
            </div>
            <div class="feature-item">
                <img src="assets/images/icon/cash-flow.png" alt="Icon chi phí hợp lý" loading="lazy">
                <p data-i18n="home.feature4">Chi phí hợp lý</p>
            </div>
            <div class="feature-item">
                <img src="assets/images/icon/action.png" alt="Icon đa dạng dịch vụ" loading="lazy">
                <p data-i18n="home.feature5">Đa dạng dịch vụ</p>
            </div>
        </div>
</section>
<!-- Phần Dịch vụ -->
<section class="service-section">
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
        <img src="assets/images/slideshow/QC_XE.png" alt="Quảng Cáo Trên Xe" loading="lazy">
        <div class="product-info">
            <h3 data-i18n="home.service1">QUẢNG CÁO TRÊN XE</h3>
        </div>
    </a>

    <a href="QC-tram-dung.php" class="product-item">
        <img src="assets/images/slideshow/QC-TD.png" alt="Quảng Cáo Trạm Dừng" loading="lazy">
        <div class="product-info">
            <h3 data-i18n="home.service2">QUẢNG CÁO TRẠM DỪNG</h3>
        </div>
    </a>

    <a href="QC-ky-thuat-so.php" class="product-item">
        <img src="assets/images/slideshow/QC-KTS.png" alt="Quảng Cáo Kỹ Thuật Số" loading="lazy">
        <div class="product-info">
            <h3 data-i18n="home.service3">QUẢNG CÁO KỸ THUẬT SỐ</h3>
        </div>
    </a>
    </div>
</section>
<!-- Tin tức & Sự kiện -->
<section class="news-section">
    <div class="container">
        <div class="section-title">
            <h2 data-i18n="home.news_title">TIN TỨC & SỰ KIỆN</h2>
            <p data-i18n="home.news_desc">Cập nhật những thông tin và sự kiện mới nhất từ FUTA ADS</p>
        </div>

        <?php if (!empty($latest_items)): ?>
            <div class="news-slider-wrapper">
                <button class="news-control prev"><i class="fas fa-chevron-left"></i></button>
                <!-- Slider -->
                <div class="news-slider">
                    <?php 
                    // Nhân đôi mảng để tạo hiệu ứng cuộn vô tận
                    foreach (array_merge($latest_items, $latest_items) as $item): 
                        $detail_url = ($item['item_type'] === 'post') 
                            ? "news_single.php?slug=" . htmlspecialchars($item['slug'])
                            : "project-detail.php?id=" . htmlspecialchars($item['id']);
                    ?>
                    <div class="news-slide">
                        <a href="<?= $detail_url ?>" class="news-card-link">
                            <div class="news-card">
                                <div class="news-card-img-wrapper">
                                    <img src="<?= htmlspecialchars($item['image'] ?? 'assets/images/placeholder.png'); ?>" alt="<?= htmlspecialchars($item['title']); ?>">
                                    <span class="news-card-badge">
                                        <?= ($item['item_type'] === 'post') ? 'Tin Tức' : 'Dự án' ?>
                                    </span>
                                </div>

                                <div class="news-card-body">
                                    <h5 class="news-card-title">
                                        <?= htmlspecialchars($item['title']); ?>
                                    </h5>

                                    <p class="news-card-text">
                                        <?= htmlspecialchars($item['excerpt']); ?>
                                    </p>

                                    <div class="news-card-date">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?= date('d-m-Y', strtotime($item['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        </a>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.querySelector('.news-slider');
    const prevBtn = document.querySelector('.news-control.prev');
    const nextBtn = document.querySelector('.news-control.next');
    
    if (slider && prevBtn && nextBtn) {
        // Tắt animation CSS mặc định để chuyển sang điều khiển bằng JS
        slider.style.animation = 'none';
        
        let currentTranslate = 0;
        const speed = 0.5; // Tốc độ tự động chạy (pixel/frame)
        let isHovered = false;
        
        function animate() {
            if (!isHovered) {
                currentTranslate -= speed;
            }
            
            const totalWidth = slider.scrollWidth;
            const halfWidth = totalWidth / 2;
            
            // Logic lặp vô tận: khi chạy hết 1 nửa (do đã nhân đôi item) thì reset về 0
            if (Math.abs(currentTranslate) >= halfWidth) {
                currentTranslate = 0;
            } else if (currentTranslate > 0) {
                currentTranslate = -halfWidth;
            }
            
            slider.style.transform = `translateX(${currentTranslate}px)`;
            requestAnimationFrame(animate);
        }
        
        // Bắt đầu chạy animation
        animate();
        
        // Tạm dừng khi di chuột vào slider hoặc nút bấm
        slider.parentElement.addEventListener('mouseenter', () => isHovered = true);
        slider.parentElement.addEventListener('mouseleave', () => isHovered = false);
        
        const getSlideWidth = () => {
            const slide = slider.querySelector('.news-slide');
            if (!slide) return 300;
            const style = window.getComputedStyle(slide);
            return slide.offsetWidth + parseFloat(style.marginRight || 0) + parseFloat(style.marginLeft || 0);
        };

        prevBtn.addEventListener('click', () => {
            currentTranslate += getSlideWidth();
            // Xử lý wrap-around khi bấm lùi quá đầu
            if (currentTranslate > 0) {
                const halfWidth = slider.scrollWidth / 2;
                currentTranslate = -halfWidth + getSlideWidth();
            }
        });
        
        nextBtn.addEventListener('click', () => {
            currentTranslate -= getSlideWidth();
        });
    }
});
</script>

<!-- Phần logo đối tác mới vào đây -->
<section class="partners-section">
        <div class="section-title">
            <h2 data-i18n="home.partners_title">ĐỐI TÁC / KHÁCH HÀNG</h2>
        </div>
        <div class="partners-logos">
            <div class="partner-logo"><img src="assets/images/client/KIMLONG.png" alt="Logo KimLong" loading="lazy"></div>
            <div class="partner-logo"><img src="assets/images/client/tienphong.png" alt="Logo Tien Phong" loading="lazy"></div>
            <div class="partner-logo"><img src="assets/images/client/shb.png" alt="Logo SHB" loading="lazy"></div>
            <div class="partner-logo"><img src="assets/images/client/unnamed.png" alt="Logo Uniben" loading="lazy"></div>
            <div class="partner-logo"><img src="assets/images/client/SAMSUNG.png" alt="Logo Samsung" loading="lazy"></div>
            <div class="partner-logo"><img src="assets/images/client/hoaphat.webp" alt="Logo Hoa Phat" loading="lazy"></div>
            <div class="partner-logo"><img src="assets/images/client/tâmnh.png" alt="Logo Tam Anh" loading="lazy"></div>
            <div class="partner-logo"><img src="assets/images/client/cholimex.jpg" alt="Logo Cholimex" loading="lazy"></div>
            <div class="partner-logo"><img src="assets/images/client/VNPAY.jpg" alt="Logo VNPAY" loading="lazy"></div>
            <div class="partner-logo"><img src="assets/images/client/DAIKIN.png" alt="Logo Daikin" loading="lazy"></div>
            <div class="partner-logo"><img src="assets/images/client/TE.png" alt="Logo Total Energies" loading="lazy"></div>
            <div class="partner-logo"><img src="assets/images/client/BVMSG.jpg" alt="Logo Benh Vien Mat Sai Gon" loading="lazy"></div>
            <div class="partner-logo"><img src="assets/images/client/ACECOOK.png" alt="Logo Acecook" loading="lazy"></div>
            <div class="partner-logo"><img src="assets/images/client/MOMO.png" alt="Logo Momo" loading="lazy"></div>
            <div class="partner-logo"><img src="assets/images/client/CELLPHONES.jpeg" alt="Logo Cellphone S" loading="lazy"></div>
            <div class="partner-logo"><img src="assets/images/client/DULUX.png" alt="Logo Dulux" loading="lazy"></div>         
        </div>
</section>
<section class="advertising">
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
    <img src="assets/images/logo/BusLines.png" alt="FUTA Bus Lines">
  </a>
  <a href="https://futacitybus.vn/" target="_blank">
    <img src="assets/images/logo/CityBus.png" alt="FUTA City Bus">
  </a>
  <a href="https://futaexpress.vn" target="_blank">
    <img src="assets/images/logo/Express.png" alt="FUTA Express">
  </a>
  <a >
    <img src="assets/images/logo/Advertising.png" alt="FUTA Advertising">
  </a>
  <a href="https://futaland.vn" target="_blank">
    <img src="assets/images/logo/Land.png" alt="FUTA Land">
  </a>
   <a >
    <img src="assets/images/logo/RestStop.png" alt="FUTA Rest Stop">
  </a>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
