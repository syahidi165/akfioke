<?php
// config/database.php

// Jika ada konfigurasi khusus server (VPS), prioritaskan file tersebut
if (file_exists(__DIR__ . '/database.local.php')) {
    return require __DIR__ . '/database.local.php';
}

return [
    'driver'    => 'mysql',
    'host'      => '127.0.0.1',
    'port'      => '3306',
    'database'  => 'akfioke_db',
    'username'  => 'root',
    'password'  => '',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'options'   => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    ],
];
