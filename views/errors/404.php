<?php
use Core\SecurityHelper;
?>
<div style="background: #ffffff; padding: 40px; border-radius: 16px; text-align: center; max-width: 480px; width: 100%; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
    <div style="width: 72px; height: 72px; background: #fee2e2; color: #ef4444; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 32px; margin-bottom: 20px;">
        <i class="fa-solid fa-triangle-exclamation"></i>
    </div>
    <h1 style="font-size: 24px; font-weight: 700; color: #1e293b; margin-bottom: 8px;">Halaman Tidak Ditemukan</h1>
    <p style="color: #64748b; font-size: 14px; margin-bottom: 24px;">
        Halaman dengan alamat <code><?= SecurityHelper::escape($uri ?? '') ?></code> tidak tersedia atau telah dipindahkan.
    </p>
    <a href="<?= $baseUrl ?>/dashboard" class="btn btn-primary" style="justify-content: center; width: 100%;">
        <i class="fa-solid fa-house"></i> Kembali ke Dashboard
    </a>
</div>
