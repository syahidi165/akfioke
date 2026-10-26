<?php
// config/security.php

return [
    'session_lifetime'       => 1800, // 30 menit idle timeout
    'session_absolute_limit' => 28800, // 8 jam batas sesi absolut
    'max_login_attempts'     => 5,    // Lockout akun per-username (5x)
    'lockout_time'           => 900,  // 15 menit
    'ip_max_login_attempts'  => 15,   // Rate limit IP: max 15x gagal dalam 15 menit lintas akun (Anti-Password Spraying)
    'ip_lockout_time'        => 900,  // 15 menit blokir IP
    'min_password_length'    => 10,   // Kebijakan panjang minimal password
    'banned_passwords'       => [     // Daftar password umum/lemah yang dilarang
        'password', 'password123', 'admin', 'admin123', '12345678', '123456789',
        'qwerty', 'bismillah', 'alkahfi', 'batam2026', 'rahasia'
    ],
    'encryption_key'         => $_ENV['APP_KEY'] ?? getenv('APP_KEY') ?: 'alkahfibatam_secure_key_2026_x7a9c8b2d1e4',
    'cipher'                 => 'AES-256-CBC',
    'allowed_upload_mimes'  => [
        'application/pdf' => 'pdf',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/vnd.ms-excel' => 'xls',
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    ],
    'max_upload_size'       => 5 * 1024 * 1024, // 5MB (Sesuai batasan upload_max_filesize server)
];
