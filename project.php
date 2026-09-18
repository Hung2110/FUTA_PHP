<?php 
require_once 'db.php';
$pageTitle = 'Dự Án';
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
    <h2 data-i18n="home.projects_title">Dự Án Tiêu Biểu</h2>
    <div class="project-grid">
      <?php if (!empty($projects)): ?>
        <?php foreach($projects as $index => $project): ?>
          <?php if (empty($project['preview_image'])) continue; // Bỏ qua nếu không có ảnh ?>
          <div class="project-link-wrapper" 
               data-type="project" 
               data-id="<?php echo $project['id']; ?>" 
               onclick="openContentModal(this)"
               style="cursor: pointer;">
            <div class="project fade-in">
              <?php if (!empty($project['preview_image'])): ?>
                <img src="<?php echo htmlspecialchars($project['preview_image']); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>" width="600" height="400" loading="lazy" decoding="async">
              <?php endif; ?>
              <div class="project-content">
                <h3 data-i18n-key="project_title_<?php echo $project['id']; ?>"
                    data-i18n-text="<?php echo htmlspecialchars($project['title']); ?>"><?php echo htmlspecialchars($project['title']); ?></h3>
                <p data-i18n-key="project_client_<?php echo $project['id']; ?>"
                   data-i18n-text="<?php echo htmlspecialchars($project['client'] ?? ''); ?>"><?php echo htmlspecialchars($project['client'] ?? ''); ?></p>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <!-- Hiển thị dự án mẫu nếu chưa có dự án nào được xuất bản -->
        <div class="project fade-in">
          <img src="assets/images/banners/istockphoto-2170390329-612x612.jpg" alt="Quảng cáo trên xe" width="600" height="400" loading="lazy" decoding="async">
          <div class="project-content">
            <h3>Quảng Cáo Trên Xe Khách</h3>
            <p>Hình thức quảng cáo nổi bật, lan tỏa thương hiệu trên hàng trăm tuyến xe FUTA khắp Việt Nam.</p>
            <a href="#" class="btn">Đang update -></a>
          </div>
        </div>

        <div class="project fade-in">
          <img src="assets/images/banners/istockphoto-486255352-612x612.jpg" alt="Quảng cáo trạm dừng" width="600" height="400" loading="lazy" decoding="async">
          <div class="project-content">
            <h3>Quảng Cáo Tại Trạm Dừng</h3>
            <p>Hệ thống trạm dừng chân FUTA được thiết kế hiện đại – nơi quảng bá thương hiệu hiệu quả và bền vững.</p>
            <a href="#" class="btn">Đang update -></a>
          </div>
        </div>

        <div class="project fade-in">
          <img src="assets/images/banners/istockphoto-1401126624-612x612.jpg" alt="Kỹ thuật số" width="600" height="400" loading="lazy" decoding="async">
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
<script src="js/project.js"></script>
<?php include 'includes/footer.php'; ?>