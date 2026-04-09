<?php 
require_once 'db.php';

// Kiểm tra xem ID có được cung cấp và hợp lệ không
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: project.php');
    exit;
}

$project_id = intval($_GET['id']);

// Lấy thông tin dự án cụ thể từ CSDL
$stmt = $conn->prepare("SELECT * FROM projects WHERE id = ? AND status = 'published'");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Nếu không tìm thấy dự án, hiển thị thông báo lỗi
    $pageTitle = "Không tìm thấy dự án";
    $pageStyles = ['css/project-detail.css'];
    include 'includes/header.php';
    echo "<div class='container my-5 text-center'><h1>Dự án không tồn tại hoặc chưa được xuất bản.</h1><a href='project.php' class='btn btn-primary mt-3'>Quay lại danh sách dự án</a></div>";
    include 'includes/footer.php';
    exit;
}

$project = $result->fetch_assoc();
$stmt->close();

// Lấy các dự án khác để hiển thị
$related_projects_stmt = $conn->prepare("SELECT id, title, preview_image, description FROM projects WHERE id != ? AND status = 'published' ORDER BY created_at DESC LIMIT 3");
$related_projects_stmt->bind_param("i", $project_id);
$related_projects_stmt->execute();
$related_projects_result = $related_projects_stmt->get_result();
$related_projects = [];
while($row = $related_projects_result->fetch_assoc()) {
    $related_projects[] = $row;
}
$related_projects_stmt->close();


// Thiết lập tiêu đề trang và file CSS
$pageTitle = "Dự án: " . htmlspecialchars($project['title']);
$pageStyles = ['css/project-detail.css'];
include 'includes/header.php'; 
?>
<script>
    // Lấy URL hiện tại cho các nút chia sẻ
    const currentUrl = window.location.href;
    const projectTitle = "<?php echo htmlspecialchars($project['title'], ENT_QUOTES); ?>";
</script>

<div class="project-detail-container">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="news.php">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="news.php?type=project">Dự án</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($project['title']); ?></li>
            </ol>
        </nav>

        <div class="row">
            <!-- Main Content Column -->
            <div class="col-lg-8">
                <div class="main-content-wrapper">
                    <h1 class="project-title"><?php echo htmlspecialchars($project['title']); ?></h1>
                    
                    <div class="project-meta">
                        <span><i class="fas fa-calendar-alt"></i> <strong>Ngày:</strong> <?php echo date('d/m/Y', strtotime($project['created_at'])); ?></span>
                    </div>

                    <!-- Social Share Buttons -->
                    <div class="social-share">
                        <a id="share-facebook" href="#" target="_blank" class="share-btn facebook"><i class="fab fa-facebook-f"></i> Chia sẻ</a>
                        <a id="share-zalo" href="#" target="_blank" class="share-btn zalo"><img src="https://upload.wikimedia.org/wikipedia/commons/9/91/Icon_of_Zalo.svg" alt="Zalo"> Chia sẻ</a>
                        <button id="copy-link" class="share-btn copy"><i class="fas fa-link"></i> Sao chép</button>
                        <span id="copy-success" style="display:none; margin-left: 10px; color: green;">Đã sao chép!</span>
                    </div>

                    <div class="project-content-body">
                        <?php if (!empty($project['preview_image'])): ?>
                            <img src="<?php echo htmlspecialchars($project['preview_image']); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>" class="img-fluid rounded project-main-image mb-4">
                        <?php endif; ?>
                        
                        <?php if (!empty($project['client'])): ?>
                            <p class="lead fst-italic text-muted">"<?php echo htmlspecialchars($project['client']); ?>"</p>
                        <?php endif; ?>

                        <div class="project-description">
                            <?php echo $project['description']; // Nội dung từ TinyMCE đã là HTML, hiển thị trực tiếp ?>
                        </div>

                        <?php if (!empty($project['preview_video'])): ?>
                            <h4 class="video-title">Video dự án</h4>
                            <div class="video-wrapper">
                                <video src="<?php echo htmlspecialchars($project['preview_video']); ?>" controls class="w-100 rounded"></video>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar Column -->
            <div class="col-lg-4">
                <aside class="sidebar">
                    <!-- Search Widget -->
                    <div class="sidebar-widget">
                        <h3 class="widget-title">Tìm kiếm</h3>
                        <form class="search-form">
                            <input type="text" placeholder="Nhập từ khóa...">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </form>
                    </div>

                    <!-- Latest Projects Widget -->
                    <?php if (!empty($related_projects)): ?>
                    <div class="sidebar-widget">
                        <h3 class="widget-title">Dự án mới nhất</h3>
                        <ul class="latest-posts-list">
                            <?php foreach($related_projects as $related_project): ?>
                            <li>
                                <a href="project-detail.php?id=<?php echo $related_project['id']; ?>">
                                    <img src="<?php echo htmlspecialchars(!empty($related_project['preview_image']) ? $related_project['preview_image'] : 'assets/images/service/billboard.jpg'); ?>" alt="<?php echo htmlspecialchars($related_project['title']); ?>">
                                    <span><?php echo htmlspecialchars($related_project['title']); ?></span>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <!-- Categories Widget -->
                    <div class="sidebar-widget">
                        <h3 class="widget-title">Danh mục</h3>
                        <ul class="category-list">
                            <li><a href="#">Quảng cáo trên xe</a></li>
                            <li><a href="#">Quảng cáo trạm dừng</a></li>
                            <li><a href="#">Quảng cáo kỹ thuật số</a></li>
                        </ul>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</div>

<script src="js/project-detail.js"></script>
<?php include 'includes/footer.php'; ?>