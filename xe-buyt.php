<?php 
$pageTitle = 'Xe Buýt Nội Ô';
$pageStyles = ['css/xe-buyt.css'];
include 'includes/header.php'; 
?>
<section class="img-section">
    <img src="assets/images/slideshow/QC_Xe.png"
         alt="Quảng Cáo Trên Xe"
         class="img-background"
         width="1920" height="600" fetchpriority="high" decoding="async">
</section>
</section>
<section class="adsbus-intro-section">
  <div class="adsbus-intro-container">
    <div class="adsbus-intro-content">
      <h2 class="adsbus-intro-title" data-i18n="xe_buyt.title">
       <span>Quảng Cáo Trên Xe Buýt Nội Ô</span>
      </h2>
      <p class="adsbus-intro-text" data-i18n="xe_buyt.intro_desc1">
        Quảng cáo trên xe buýt là một trong những kênh truyền thông ngoài trời
        hiệu quả, giúp thương hiệu của bạn tiếp cận khách hàng một cách trực tiếp
        và liên tục tại các khu vực trung tâm thành phố.
      </p>
      <p class="adsbus-intro-text" data-i18n="xe_buyt.intro_desc2">
        Với tần suất hoạt động cao, lộ trình cố định và lượng hành khách đông đảo mỗi ngày,
        xe buýt nội ô mang lại độ phủ rộng khắp và khả năng ghi nhớ thương hiệu vượt trội.
      </p>
      <ul class="adsbus-intro-list">
        <li data-i18n="xe_buyt.intro_feat1">Hiển thị nổi bật trên các tuyến đường đông đúc.</li>
        <li data-i18n="xe_buyt.intro_feat2">Tiếp cận đa dạng đối tượng khách hàng hàng ngày.</li>
        <li data-i18n="xe_buyt.intro_feat3">Tạo sự lặp lại và ghi nhớ mạnh mẽ thương hiệu.</li>
      </ul>
      <a href="#contact" class="adsbus-intro-cta" data-i18n="xe_buyt.contact_btn">Liên hệ tư vấn ngay</a>
    </div>
    <div class="adsbus-intro-image">
      <img src="assets/images/service/Xe buýt.jpg" alt="Quảng cáo xe buýt nội ô" width="600" height="400" decoding="async">
    </div>
  </div>
</section>
<section class="bus-ads-section">
  <div class="container">
    <!-- Tiêu đề & mô tả -->
    <div class="intro">
      <h2 data-i18n="xe_buyt.inside_title">Quảng Cáo Bên Trong Xe Buýt Nội Ô</h2>
      <p data-i18n="xe_buyt.inside_desc">
        Dịch vụ quảng cáo trên xe buýt nội ô giúp thương hiệu tiếp cận hàng ngàn lượt người 
        mỗi ngày. Với phạm vi phủ sóng rộng khắp các tuyến đường, hình ảnh của bạn sẽ trở 
        nên quen thuộc và dễ ghi nhớ đối với khách hàng.
      </p>
    </div>

    <!-- Danh sách ảnh -->
    <div class="bus-ads-gallery">
      <div class="ads-item">
        <img src="assets/images/service/sau-ghế.jpg" alt="Dán Sau Ghế" width="400" height="300" decoding="async">
        <div class="overlay">
          <p data-i18n="xe_buyt.img1_desc">Quảng cáo sau ghế xe buýt – vị trí vàng ngay tầm mắt hành khách, tăng nhận diện thương hiệu mỗi ngày</p>
        </div>
      </div>
      <div class="ads-item">
        <img src="assets/images/service/post 2-01.png" alt="LCD Trong Xe" width="400" height="300" decoding="async">
        <div class="overlay">
          <p data-i18n="xe_buyt.img2_desc">Quảng cáo trên màn hình trong xe buýt – nội dung hiển thị sinh động, tiếp cận hành khách liên tục trong suốt chuyến đi</p>
        </div>
      </div>
      <div class="ads-item">
        <img src="assets/images/service/tay-nam.jpg" alt="Dán Trên Tay Nắm" width="400" height="300" decoding="async">
        <div class="overlay">
          <p data-i18n="xe_buyt.img3_desc">Quảng cáo trên tay nắm xe buýt – vị trí nhỏ nhưng hiệu quả lớn, giúp thương hiệu tiếp cận hành khách mỗi ngày.</p>
        </div>
      </div>
    </div>
  </div>
</section>
<section class="banner-intro">
    <div class="banner-wrapper">
        <img src="assets/images/banners/BCR.jpg" class="banner-img" alt="Banner Intro" width="1920" height="600" loading="lazy" decoding="async">

        <div class="banner-text">
            <p class="line line-1" data-i18n="xe_trung_chuyen.line1">
                Một trong những hình thức quảng cáo ngoài trời mạnh mẽ nhất, quảng cáo trên xe giúp tiếp cận khu vực địa lý rộng lớn hơn.
            </p>
            <p class="line line-2" data-i18n="xe_trung_chuyen.line2">
                Từ xe tuyến, xe trung chuyển đến cả xe buýt, các chiến lược tiếp thị của bạn sẽ luôn xuất hiện.
            </p>
            <p class="line line-3 highlight" data-i18n="xe_trung_chuyen.line3">
                Đúng lúc - Đúng người
            </p>
        </div>
    </div>
</section>

<!-- Nút chuyển trang trên Mobile -->
<a href="xe-tuyen.php" class="mobile-page-nav-btn prev"><i class="fas fa-chevron-left"></i></a>
<a href="xe-trung-chuyen.php" class="mobile-page-nav-btn next"><i class="fas fa-chevron-right"></i></a>

<?php include 'includes/footer.php'; ?>
