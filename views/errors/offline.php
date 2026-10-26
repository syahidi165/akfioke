<?php
// views/errors/offline.php
use Core\SecurityHelper;
?>
<div style="background: #ffffff; padding: 40px 30px; border-radius: 16px; text-align: center; max-width: 460px; width: 100%; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
    <div style="width: 80px; height: 80px; background: #e0f2fe; color: #0284c7; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 36px; margin-bottom: 20px; box-shadow: 0 10px 15px -3px rgba(2, 132, 199, 0.2);">
        <i class="fa-solid fa-wifi-slash"></i>
    </div>
    
    <h1 style="font-size: 22px; font-weight: 700; color: #1e293b; margin-bottom: 10px;">
        Koneksi Internet Terputus
    </h1>
    
    <p style="color: #64748b; font-size: 14px; line-height: 1.6; margin-bottom: 24px;">
        Aplikasi <strong><?= SecurityHelper::escape($appShortName ?? 'MASEKO') ?></strong> membutuhkan akses internet untuk sinkronisasi data transaksi, absensi, dan rapor sekolah secara aman.
    </p>

    <div style="display: flex; flex-direction: column; gap: 10px;">
        <button type="button" onclick="window.location.reload();" class="btn btn-primary" style="justify-content: center; width: 100%; padding: 12px; font-weight: 600; cursor: pointer;">
            <i class="fa-solid fa-rotate-right"></i> Coba Hubungkan Kembali
        </button>
        
        <a href="<?= $baseUrl ?>/login" class="btn btn-secondary" style="justify-content: center; width: 100%; padding: 10px; font-size: 13px;">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Halaman Masuk
        </a>
    </div>
</div>
