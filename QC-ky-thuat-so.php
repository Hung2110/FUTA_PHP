<?php 
$pageTitle = 'Quảng Cáo Kỹ Thuật Số';
$pageStyles = ['css/QC-KTS.css'];
include 'includes/header.php'; 
?>


<section class="img-section">
    <img src="assets/images/slideshow/QC-KTS.jpg" alt="Quảng Cáo Kỹ Thuật Số" class="img-background" width="1920" height="600" fetchpriority="high" decoding="async">
</section>
<section class="led-section" id="led">
  <!-- Phần giới thiệu -->
  <div class="led-intro">
    <h2 data-i18n="qc_kts.led_title">Màn Hình LED</h2>
    <p data-i18n="qc_kts.led_desc">
      Màn hình LED sắc nét, hiển thị liên tục tại các bến xe và trạm dừng, giúp thương hiệu nổi bật và thu hút ánh nhìn.
      <br> Giải pháp quảng cáo hiện đại, linh hoạt với hình ảnh và video ấn tượng.
    </p>
  </div>
  <div class="led-container">
    <!-- Cột màn hình LED ngang (nằm trên) -->
    <div class="led-frame">
      <div class="screen">
        <video autoplay muted loop playsinline preload="metadata">
          <source src="assets/images/clip/LED.mp4" type="video/mp4">
        </video>
      </div>
    </div>
    <!-- Info item nằm dưới -->
    <div class="led-info">
      <div class="info-item">
        <div class="icon">
          <i class="fa-solid fa-location-dot fa-2xl"></i>
        </div>
        <p><span data-i18n="qc_kts.led_info1_label">Vị Trí Đặt</span><br><strong data-i18n="qc_kts.led_info1_val">Tại Bến Xe & Trạm Dừng</strong></p>
      </div>
      <div class="info-item">
        <div class="icon">
         <i class="fa-solid fa-eye fa-2xl"></i>
        </div>
        <p><span data-i18n="qc_kts.led_info2_label">Tầm Nhìn</span><br><strong data-i18n="qc_kts.led_info2_val">Điểm Nhìn Đắt Giá</strong></p>
      </div>
      <div class="info-item">
        <div class="icon">
          <i class="fa-solid fa-images fa-2xl"></i>
        </div>
        <p><span data-i18n="qc_kts.led_info3_label">Chất Lượng Hình Ảnh</span><br><strong data-i18n="qc_kts.led_info3_val">Sống Động Hiển Thị Tần Suất Cao</strong></p>
      </div>
      <div class="info-item">
        <div class="icon">
          <i class="fa-solid fa-arrows-down-to-people fa-2xl"></i>
        </div>
        <p><span data-i18n="qc_kts.led_info4_label">Tiếp Cận</span><br><strong data-i18n="qc_kts.led_info4_val">Đa Dạng Khách Hàng</strong></p>
      </div>
    </div>
  </div>
</section>
<section class="lcd-section" id="lcd">
  <!-- Nội dung bên trái -->
  <div class="lcd-content">
    <h2 data-i18n="qc_kts.lcd_title">Màn Hình LCD</h2>
    <p data-i18n="qc_kts.lcd_desc">
     Màn hình LCD dọc, độ phân giải cao, hiển thị hình ảnh và video sống động. Đặt tại sảnh, bến xe hay trạm dừng, giúp thương hiệu nổi bật và thu hút khách hàng ngay từ ánh nhìn đầu tiên.
    </p>
    <ul>
      <li data-i18n="qc_kts.lcd_li1">📌 Kích thước: 65 inch</li>
      <li data-i18n="qc_kts.lcd_li2">📌 Độ phân giải: 4K Ultra HD</li>
      <li data-i18n="qc_kts.lcd_li3">📌 Hỗ trợ video / hình ảnh / slideshow</li>
      <li data-i18n="qc_kts.lcd_li4">📌 Lắp đặt tại cái bến xe,trạm dừng chân</li>
    </ul>
  </div>

  <!-- Khung LCD bên phải -->
  <div class="lcd-frame">
    <video autoplay muted loop playsinline preload="metadata" class="lcd-screen">
      <source src="assets/images/clip/LCD STANDEE.mp4" type="video/mp4">
      Trình duyệt của bạn không hỗ trợ video.
    </video>
  </div>
</section>
<section class="wifimkt-section" id="wfmkt">
  <!-- Khung iPhone bên trái -->
 

  <!-- Nội dung mô tả bên phải -->
  <div class="wifimkt-content">
    <h2 data-i18n="qc_kts.wifi_title">WiFi Marketing</h2>
    <p data-i18n="qc_kts.wifi_desc">
      Kết nối WiFi miễn phí quảng cáo thương hiệu trực tiếp đến khách hàng qua trang chào và banner. Giải pháp hiện đại giúp tăng nhận diện thương hiệu và tiếp cận khách hàng hiệu quả.
    </p>
    <ul>
      <li data-i18n="qc_kts.wifi_li1">🎯 Nhắm đúng khách hàng mục tiêu</li>
      <li data-i18n="qc_kts.wifi_li2">📊 Báo cáo & thống kê chi tiết</li>
      <li data-i18n="qc_kts.wifi_li3">⚡ Tăng độ nhận diện thương hiệu</li>
    </ul>
  </div>
   <div class="wifimkt-frame">
    <div class="wifimkt-notch"></div>
    <video autoplay muted loop playsinline preload="metadata" class="wifimkt-screen">
      <source src="assets/images/clip/wifi.mp4" type="video/mp4">
      Trình duyệt không hỗ trợ video.
    </video>
  </div>
</section>

<!-- Nút chuyển trang trên Mobile -->
<a href="QC-tram-dung.php" class="mobile-page-nav-btn prev"><i class="fas fa-chevron-left"></i></a>
<a href="news.php" class="mobile-page-nav-btn next"><i class="fas fa-chevron-right"></i></a>

<?php include 'includes/footer.php'; ?>
