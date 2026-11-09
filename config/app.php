<?php
// config/app.php

// Batasan runtime server (php.ini)
@ini_set('memory_limit', '256M');
@ini_set('max_execution_time', '120');

if (file_exists(__DIR__ . '/app.local.php')) {
    return require __DIR__ . '/app.local.php';
}

$envDebug = $_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG');
$isLocal  = (PHP_SAPI === 'cli') || in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
$showErrors = ($envDebug !== false && $envDebug !== null && $envDebug !== '')
    ? filter_var($envDebug, FILTER_VALIDATE_BOOLEAN)
    : $isLocal;

return [
    'name'           => 'Manajemen Sekolah Berbasis Komputer (MASEKO)',
    'short_name'     => 'MASEKO',
    'url'            => 'http://localhost/akfioke',
    'base_path'      => '/akfioke',
    'timezone'       => 'Asia/Jakarta',
    'locale'         => 'id_ID',
    'upload_path'    => dirname(__DIR__) . '/storage/uploads',
    'backup_path'    => dirname(__DIR__) . '/storage/backups',
    'log_path'       => dirname(__DIR__) . '/storage/logs',
    'display_errors' => $showErrors,
];
