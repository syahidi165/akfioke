<?php
// views/layouts/main.php
include __DIR__ . '/header.php';
include __DIR__ . '/sidebar.php';
?>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
<div class="app-main">
    <?php include __DIR__ . '/navbar.php'; ?>
    
    <main class="app-content">
        <!-- Flash Alert Messages -->
        <?php if (!empty($_SESSION['flash'])): ?>
            <?php 
                $flash = $_SESSION['flash'];
                unset($_SESSION['flash']);
                $alertClass = match($flash['type']) {
                    'success' => 'alert-success',
                    'error'   => 'alert-danger',
                    'warning' => 'alert-warning',
                    default   => 'alert-info'
                };
                $iconClass = match($flash['type']) {
                    'success' => 'fa-circle-check',
                    'error'   => 'fa-triangle-exclamation',
                    'warning' => 'fa-circle-exclamation',
                    default   => 'fa-circle-info'
                };
            ?>
            <div class="alert <?= $alertClass ?>">
                <i class="fa-solid <?= $iconClass ?>" style="font-size: 16px;"></i>
                <div><?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>

        <!-- Dynamic Page View Content -->
        <?= $content ?>
    </main>
<?php include __DIR__ . '/footer.php'; ?>
