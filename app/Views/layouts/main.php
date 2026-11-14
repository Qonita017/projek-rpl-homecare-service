<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="HomeCare Service — Solusi cerdas servis peralatan rumah tangga dengan teknisi profesional dan AI diagnosis.">
    <title><?= $title ?? "HomeCare Service" ?></title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom Styles -->
    <link href="<?= BASE_URL ?>/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include "../app/Views/layouts/header.php"; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include "../app/Views/layouts/sidebar.php"; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-3">
                <!-- Toast Container -->
                <div id="toastContainer" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;"></div>
                <?php require_once $content; ?>
            </main>
        </div>
    </div>
    <?php include "../app/Views/layouts/footer.php"; ?>

    <!-- Floating AI Chatbot Widget (Icon Shape) -->
    <?php 
    $currentUri = $_SERVER['REQUEST_URI'] ?? '';
    $isAiChatPage = (strpos($currentUri, '/customer/ai-chat') !== false);
    if (!$isAiChatPage) {
        include "../app/Views/layouts/ai_chatbot_widget.php"; 
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/js/main.js"></script>
</body>
</html>