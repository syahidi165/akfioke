<?php
// config/app.php

// Batasan runtime server (php.ini)
@ini_set('memory_limit', '256M');
@ini_set('max_execution_time', '120');

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
    'display_errors' => true, // Set false in production
];
