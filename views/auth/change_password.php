<?php
use Core\SecurityHelper;
use Core\CSRF;
?>
<div style="background: #ffffff; padding: 40px; border-radius: 16px; width: 100%; max-width: 440px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
    <div style="text-align: center; margin-bottom: 24px;">
        <div style="width: 56px; height: 56px; background: #fef3c7; color: #d97706; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 24px;">
            <i class="fa-solid fa-key"></i>
        </div>
        <h2 style="font-size: 18px; font-weight: 700; color: #1e293b; margin-top: 12px;">Ganti Kata Sandi</h2>
        <p style="font-size: 13px; color: #64748b; margin-top: 4px;">Demi keamanan akun Anda, gunakan minimal 8 karakter.</p>
    </div>

    <form method="POST" action="<?= $baseUrl ?>/change-password">
        <?= CSRF::inputField() ?>
        
        <?php if (empty($_SESSION['force_change_password'])): ?>
            <div class="form-group">
                <label class="form-label" for="old_password">Kata Sandi Saat Ini</label>
                <input type="password" name="old_password" id="old_password" class="form-control" required placeholder="••••••••">
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label class="form-label" for="new_password">Kata Sandi Baru</label>
            <input type="password" name="new_password" id="new_password" class="form-control" minlength="8" required placeholder="Minimal 8 karakter">
        </div>

        <div class="form-group" style="margin-bottom: 24px;">
            <label class="form-label" for="confirm_password">Konfirmasi Kata Sandi Baru</label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" minlength="8" required placeholder="Ulangi kata sandi baru">
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; font-weight: 600;">
            <i class="fa-solid fa-check"></i> Simpan Kata Sandi
        </button>
    </form>
</div>
