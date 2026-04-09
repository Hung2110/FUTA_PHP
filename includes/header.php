<?php
$pageStyles = $pageStyles ?? [];
?>
  
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | FUTA Advertising' : 'FUTA Advertising'; ?></title>
    <link rel="icon" href="/FUTA_PHP/assets/images/logo/futa.png" type="image/png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Liên kết đến tệp CSS chung -->
    <?php
    // Hàm để thêm phiên bản vào tệp CSS (cache busting)
    function css_version($path) {
        echo '/FUTA_PHP/' . $path . '?v=' . filemtime($_SERVER['DOCUMENT_ROOT'] . '/FUTA_PHP/' . $path);
    }
    ?>
    <link rel="stylesheet" href="<?php css_version('css/style.css'); ?>">
    <?php foreach ($pageStyles as $stylesheet):
      $filePath = htmlspecialchars($stylesheet, ENT_QUOTES, 'UTF-8'); ?>
      <link rel="stylesheet" href="<?php css_version($filePath); ?>">
    <?php endforeach; ?>
    <!-- Thư viện Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Script Google Translate -->
    <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <!-- i18n Language Script -->
    <script src="/FUTA_PHP/js/i18n.js"></script>
</head>
<body class="<?php echo isset($bodyClass) ? htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8') : ''; ?>">

<?php
include 'includes/navbar.php';
?>
<!-- begin page content -->
