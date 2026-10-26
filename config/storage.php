<?php
// config/storage.php
// Konfigurasi Sistem Penyimpanan Multi-Tier (Hot & Cold Storage)
// Yayasan Islam Al Kahfi Batam - Sistem Manajemen Sekolah Terpadu (MASEKO)

use Core\Setting;

// Helper untuk membaca pengaturan dinamis dari database (app_settings) dengan fallback ke file/env
$getColdSetting = function(string $key, $default) {
    if (class_exists('Core\Setting')) {
        $val = Setting::get("storage_cold_{$key}", null);
        if ($val !== null && $val !== '') {
            if ($val === 'true') return true;
            if ($val === 'false') return false;
            return $val;
        }
    }
    return $default;
};

return [
    /*
    |--------------------------------------------------------------------------
    | Hot Storage (Hosting Server Aktif)
    |--------------------------------------------------------------------------
    | Digunakan untuk operasional harian Tahun Ajaran yang AKTIF BERJALAN.
    | Karakteristik: Cepat, latensi rendah, media NVMe/SSD server hosting.
    */
    'hot' => [
        'name'        => 'Hosting Web (Hot Storage Aktif)',
        'driver'      => 'local',
        'path'        => dirname(__DIR__) . '/storage/uploads',
        'public_url'  => null, // Di-stream aman melalui Uploader::stream()
        'description' => 'Penyimpanan berkecepatan tinggi di server web untuk berkas tahun ajaran aktif.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cold Storage (NAS Lokal Sekolah / Arsip Jangka Panjang)
    |--------------------------------------------------------------------------
    | Digunakan untuk mengarsipkan seluruh data & berkas Tahun Ajaran yang telah
    | SELESAI / BERLALU. Menghemat kuota hosting server dan mengamankan arsip
    | jangka panjang di hardware milik yayasan (Synology, TrueNAS, QNAP, PC Server).
    |
    | Pilihan driver:
    | - 'local_path': Folder lokal, Windows UNC share (\\192.168.x.x\arsip), atau Mount NFS/SMB (/mnt/nas)
    | - 'webdav'    : HTTP/HTTPS WebDAV ke Synology DSM, Nextcloud, QNAP
    */
    'cold' => [
        'name'        => 'NAS Lokal Sekolah (Cold Storage)',
        'enabled'     => (bool)$getColdSetting('enabled', true),
        'driver'      => (string)$getColdSetting('driver', 'local_path'),

        // Driver: local_path (Windows UNC / Linux Mount / Directory)
        // Default direktori fallback jika NAS belum terpasang: storage/nas_cold
        'local_path'  => (string)$getColdSetting('local_path', dirname(__DIR__) . '/storage/nas_cold'),

        // Driver: webdav (Akses remote ke NAS Synology / TrueNAS via WebDAV)
        'webdav' => [
            'base_url'   => (string)$getColdSetting('webdav_url', 'http://192.168.1.100:5005'),
            'path'       => (string)$getColdSetting('webdav_path', '/akfi_cold_storage'),
            'username'   => (string)$getColdSetting('webdav_user', 'admin_nas'),
            'password'   => (string)$getColdSetting('webdav_pass', ''),
            'timeout'    => 30,
        ],

        // Fitur Tambahan Cold Storage
        'database_snapshot' => true,  // Ekspor & kompresi data SQL per tahun ajaran ke NAS
        'compress_archive'  => true,  // Kompresi arsip zip/gz saat pemindahan
        'description'       => 'Penyimpanan berkapasitas besar di NAS lokal sekolah untuk arsip tahun ajaran lampau.',
    ],
];
