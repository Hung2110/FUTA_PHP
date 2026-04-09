<?php 
require_once 'db.php';
$pageStyles = ['css/project.css'];
include 'includes/header.php'; 

// Lấy tất cả dự án đã xuất bản từ database
$projects_query = $conn->query("SELECT * FROM projects WHERE status = 'published' ORDER BY created_at DESC");
$projects = [];
if ($projects_query) {
    while($row = $projects_query->fetch_assoc()) {
        $projects[] = $row;
    }
}
?>


<div class="futa-project-page">
  <section class="futa-banner">
    <div class="overlay"></div>
    <div class="banner-content">
      <h1 data-i18n="project.banner_title">DỰ ÁN CỦA CHÚNG TÔI</h1>
      <p data-i18n="project.banner_desc">Khám phá những dự án quảng cáo ấn tượng mà chúng tôi đã thực hiện</p>
    </div>
  </section>

  <section class="projects">
    <h2>Dự Án Tiêu Biểu</h2>
    <div class="project-grid">
      <?php if (!empty($projects)): ?>
        <?php foreach($projects as $index => $project): ?>
          <a href="project-detail.php?id=<?php echo $project['id']; ?>" class="project-link-wrapper">
            <div class="project fade-in">
              <?php if (!empty($project['preview_image'])): ?>
                <img src="<?php echo htmlspecialchars($project['preview_image']); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>">
              <?php else: ?>
                <img src="assets/images/service/billboard.jpg" alt="<?php echo htmlspecialchars($project['title']); ?>">
              <?php endif; ?>
              <div class="project-content">
                <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                <p><?php echo htmlspecialchars($project['client'] ?? ''); ?></p>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <!-- Hiển thị dự án mẫu nếu chưa có dự án nào được xuất bản -->
        <div class="project fade-in">
          <img src="assets/images/banners/istockphoto-2170390329-612x612.jpg" alt="Quảng cáo trên xe">
          <div class="project-content">
            <h3>Quảng Cáo Trên Xe Khách</h3>
            <p>Hình thức quảng cáo nổi bật, lan tỏa thương hiệu trên hàng trăm tuyến xe FUTA khắp Việt Nam.</p>
            <a href="#" class="btn">Đang update -></a>
          </div>
        </div>

        <div class="project fade-in">
          <img src="assets/images/banners/istockphoto-486255352-612x612.jpg" alt="Quảng cáo trạm dừng">
          <div class="project-content">
            <h3>Quảng Cáo Tại Trạm Dừng</h3>
            <p>Hệ thống trạm dừng chân FUTA được thiết kế hiện đại – nơi quảng bá thương hiệu hiệu quả và bền vững.</p>
            <a href="#" class="btn">Đang update -></a>
          </div>
        </div>

        <div class="project fade-in">
          <img src="assets/images/banners/istockphoto-1401126624-612x612.jpg" alt="Kỹ thuật số">
          <div class="project-content">
            <h3>Quảng Cáo Kỹ Thuật Số</h3>
            <p>Hiển thị quảng cáo số trên các màn hình LED và hệ thống kỹ thuật số tại các trạm dừng và bến xe.</p>
            <a href="#" class="btn">Đang update -></a>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>
</div>

<script>
// Hiệu ứng fade-in khi cuộn
window.addEventListener('scroll', () => {
  document.querySelectorAll('.futa-project-page .fade-in').forEach(el => {
    const rect = el.getBoundingClientRect();
    if (rect.top < window.innerHeight - 100) {
      el.classList.add('show');
    }
  });
});

// Trigger animation on load
window.addEventListener('load', () => {
  document.querySelectorAll('.futa-project-page .fade-in').forEach((el, index) => {
    setTimeout(() => {
      const rect = el.getBoundingClientRect();
      if (rect.top < window.innerHeight) {
        el.classList.add('show');
      }
    }, index * 100);
  });
});
</script>

<?php include 'includes/footer.php'; ?>