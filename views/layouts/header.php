<?php
// views/layouts/header.php
use Core\SecurityHelper;
use Core\CSRF;

SecurityHelper::sendSecurityHeaders();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SecurityHelper::escape(!empty($pageTitle) ? ($pageTitle . ' - ' . ($appShortName ?? 'MASEKO')) : (($appShortName ?? 'MASEKO') . ' - ' . ($appSubName ?? 'Manajemen Sekolah Berbasis Komputer'))) ?></title>
    <?= CSRF::metaTag() ?>
    
    <!-- Favicon -->
    <?php if (!empty($appFaviconUrl)): ?>
        <link rel="icon" href="<?= $appFaviconUrl ?>">
    <?php elseif (!empty($appLogoUrl)): ?>
        <link rel="icon" href="<?= $appLogoUrl ?>">
    <?php else: ?>
        <link rel="icon" href="<?= $baseUrl ?>/public/assets/img/icon-192.png">
    <?php endif; ?>
    
    <!-- PWA Manifest & Mobile Meta Tags -->
    <link rel="manifest" href="<?= $baseUrl ?>/manifest.json">
    <meta name="theme-color" content="#1e3a8a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?= SecurityHelper::escape($appShortName ?? 'MASEKO') ?>">
    <link rel="apple-touch-icon" href="<?= $baseUrl ?>/public/assets/img/apple-touch-icon.png">
    
    <!-- Local FontAwesome CSS -->
    <link rel="stylesheet" href="<?= $baseUrl ?>/public/assets/css/fontawesome.min.css">
    
    <!-- Main App Stylesheet -->
    <link rel="stylesheet" href="<?= $baseUrl ?>/public/assets/css/app.css?v=1.0.4">
    
    <!-- Local Chart.js for Dashboards -->
    <script src="<?= $baseUrl ?>/public/assets/js/chart.min.js"></script>
</head>
<body>
<div class="app-wrapper">
