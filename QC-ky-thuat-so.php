<?php 
$pageStyles = ['css/QC-KTS.css'];
include 'includes/header.php'; 
?>


<section class="img-section">
    <img src="assets/images/slideshow/QC-KTS.png" alt="Quảng Cáo Kỹ Thuật Số" class="img-background">
</section>
<section class="led-section" id="led">
  <!-- Phần giới thiệu -->
  <div class="led-intro">
    <h2>Màn Hình LED</h2>
    <p>
      Màn hình LED sắc nét, hiển thị liên tục tại các bến xe và trạm dừng, giúp thương hiệu nổi bật và thu hút ánh nhìn.
      <br> Giải pháp quảng cáo hiện đại, linh hoạt với hình ảnh và video ấn tượng.
    </p>
  </div>
  <div class="led-container">
    <!-- Cột màn hình LED ngang (nằm trên) -->
    <div class="led-frame">
      <div class="screen">
        <video autoplay muted loop>
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
        <p>Vị Trí Đặt<br><strong>Tại Bến Xe & Trạm Dừng</strong></p>
      </div>
      <div class="info-item">
        <div class="icon">
         <i class="fa-solid fa-eye fa-2xl"></i>
        </div>
        <p>Tầm Nhìn<br><strong>Điểm Nhìn Đắt Giá</strong></p>
      </div>
      <div class="info-item">
        <div class="icon">
          <i class="fa-solid fa-images fa-2xl"></i>
        </div>
        <p>Chất Lượng Hình Ảnh<br><strong>Sống Động Hiển Thị Tần Suất Cao</strong></p>
      </div>
      <div class="info-item">
        <div class="icon">
          <i class="fa-solid fa-arrows-down-to-people fa-2xl"></i>
        </div>
        <p>Tiếp Cận<br><strong>Đa Dạng Khách Hàng</strong></p>
      </div>
    </div>
  </div>
</section>
<section class="lcd-section" id="lcd">
  <!-- Nội dung bên trái -->
  <div class="lcd-content">
    <h2>Màn Hình LCD</h2>
    <p>
     Màn hình LCD dọc, độ phân giải cao, hiển thị hình ảnh và video sống động. Đặt tại sảnh, bến xe hay trạm dừng, giúp thương hiệu nổi bật và thu hút khách hàng ngay từ ánh nhìn đầu tiên.
    </p>
    <ul>
      <li>📌 Kích thước: 65 inch</li>
      <li>📌 Độ phân giải: 4K Ultra HD</li>
      <li>📌 Hỗ trợ video / hình ảnh / slideshow</li>
      <li>📌 Lắp đặt tại cái bến xe,trạm dừng chân</li>
    </ul>
  </div>

  <!-- Khung LCD bên phải -->
  <div class="lcd-frame">
    <video autoplay muted loop class="lcd-screen">
      <source src="assets/images/clip/LCD STANDEE.mp4" type="video/mp4">
      Trình duyệt của bạn không hỗ trợ video.
    </video>
  </div>
</section>
<section class="wifimkt-section" id="wfmkt">
  <!-- Khung iPhone bên trái -->
  <div class="wifimkt-frame">
    <div class="wifimkt-notch"></div>
    <video autoplay muted loop class="wifimkt-screen">
      <source src="assets/images/clip/wifi.mp4" type="video/mp4">
      Trình duyệt không hỗ trợ video.
    </video>
  </div>

  <!-- Nội dung mô tả bên phải -->
  <div class="wifimkt-content">
    <h2>WiFi Marketing</h2>
    <p>
      Kết nối WiFi miễn phí quảng cáo thương hiệu trực tiếp đến khách hàng qua trang chào và banner. Giải pháp hiện đại giúp tăng nhận diện thương hiệu và tiếp cận khách hàng hiệu quả.
    </p>
    <ul>
      <li>🎯 Nhắm đúng khách hàng mục tiêu</li>
      <li>📊 Báo cáo & thống kê chi tiết</li>
      <li>⚡ Tăng độ nhận diện thương hiệu</li>
    </ul>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
