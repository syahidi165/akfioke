<?php
use Core\SecurityHelper;
use Core\CSRF;
?>
<div class="login-card">
    <div style="text-align: center; margin-bottom: 24px;">
        <?php if (!empty($appLogoUrl)): ?>
            <div style="width: 72px; height: 72px; margin: 0 auto; display: flex; align-items: center; justify-content: center; background: #ffffff; border: 1px solid var(--border-color); border-radius: 16px; padding: 6px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);">
                <img src="<?= $appLogoUrl ?>" alt="<?= SecurityHelper::escape($appShortName ?? 'MASEKO') ?>" style="max-height: 100%; max-width: 100%; object-fit: contain;">
            </div>
        <?php else: ?>
            <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #1e3a8a, #059669); border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; color: white; font-size: 28px; box-shadow: 0 10px 15px -3px rgba(5, 150, 105, 0.3);">
                <i class="fa-solid fa-desktop"></i>
            </div>
        <?php endif; ?>
        <h2 style="font-size: 22px; font-weight: 800; color: #0f172a; margin-top: 14px; letter-spacing: -0.5px;"><?= SecurityHelper::escape($appShortName ?? 'MASEKO') ?></h2>
        <p style="font-size: 13px; color: #64748b; margin-top: 4px;"><?= SecurityHelper::escape($appSubName ?? 'Manajemen Sekolah Berbasis Komputer') ?></p>
    </div>

    <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <div><?= SecurityHelper::escape($errorMsg) ?></div>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= $baseUrl ?>/login">
        <?= CSRF::inputField() ?>
        
        <div class="form-group">
            <label class="form-label" for="username">Nama Pengguna (Username / No. KK)</label>
            <div style="position: relative;">
                <input type="text" name="username" id="username" class="form-control" style="padding-left: 38px; height: 42px;" 
                       value="<?= SecurityHelper::escape($oldUser ?? '') ?>" required autofocus autocomplete="username" placeholder="Masukkan username Anda">
                <i class="fa-regular fa-user" style="position: absolute; left: 12px; top: 13px; color: #94a3b8;"></i>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 22px;">
            <label class="form-label" for="password">Kata Sandi (Password)</label>
            <div style="position: relative;">
                <input type="password" name="password" id="password" class="form-control" style="padding-left: 38px; height: 42px;" 
                       required autocomplete="current-password" placeholder="••••••••">
                <i class="fa-solid fa-lock" style="position: absolute; left: 12px; top: 13px; color: #94a3b8;"></i>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; font-size: 14px; font-weight: 700; border-radius: 10px; box-shadow: 0 4px 6px -1px rgba(5,150,105,0.3);">
            <i class="fa-solid fa-right-to-bracket"></i> Masuk ke Sistem
        </button>
    </form>

    <!-- Banner Install Aplikasi PWA di HP -->
    <div class="pwa-install-banner pwa-install-btn" id="pwaInstallBtn" onclick="window.promptPwaInstall()" title="Install Aplikasi" style="justify-content: center; padding: 10px 16px;">
        <div class="pwa-banner-left" style="justify-content: center; gap: 10px;">
            <div class="pwa-banner-icon" style="width: 32px; height: 32px; font-size: 14px; border-radius: 8px;">
                <i class="fa-solid fa-download"></i>
            </div>
            <div class="pwa-banner-text">
                <div class="pwa-banner-title" style="font-size: 13.5px; font-weight: 700;">Install Aplikasi</div>
            </div>
        </div>
    </div>

</div>

