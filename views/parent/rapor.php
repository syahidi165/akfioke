<?php
// views/parent/rapor.php
use Core\SecurityHelper;
use Core\Auth;
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><?= SecurityHelper::escape($pageTitle) ?></h1>
        <div class="page-subtitle">
            Pantau hasil belajar akademik bulanan, capaian syahadah tahfidz Al-Qur'an, dan unduh berkas rapor ananda.
        </div>
    </div>
</div>

<div class="card" style="background: linear-gradient(135deg, #0d9488 0%, #1e3a8a 100%); color: #ffffff; border: none; margin-bottom: 24px;">
    <div class="card-body" style="padding: 24px;">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 52px; height: 52px; border-radius: 12px; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; font-size: 26px; flex-shrink: 0;">
                <i class="fa-solid fa-child-reaching"></i>
            </div>
            <div>
                <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 4px; color: #ffffff;">
                    Portal Akademik & Rapor Santri
                </h3>
                <p style="font-size: 13px; opacity: 0.9; margin: 0; line-height: 1.5;">
                    Selamat datang Ayah/Bunda di portal resmi Yayasan Islam Al Kahfi Batam. Rapor bulanan dan sertifikat tahfidz diperbarui secara berkala oleh Wali Kelas dan Guru Tahfidz ananda.
                </p>
            </div>
        </div>
    </div>
</div>

<?php if (empty($children)): ?>
    <div class="card" style="text-align: center; padding: 48px 20px;">
        <div style="width: 64px; height: 64px; border-radius: 50%; background: #f1f5f9; color: #94a3b8; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 16px;">
            <i class="fa-solid fa-user-slash"></i>
        </div>
        <h4 style="font-size: 16px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">Data Santri Belum Terhubung</h4>
        <p style="font-size: 13px; color: var(--text-muted); max-width: 480px; margin: 0 auto;">
            Akun Anda belum terhubung dengan data santri aktif di sistem. Pastikan Nomor KK yang didaftarkan saat registrasi siswa baru telah sesuai, atau silakan hubungi bagian Tata Usaha sekolah.
        </p>
    </div>
<?php else: ?>
    <div class="grid-cols-2">
        <?php foreach ($children as $c): ?>
            <div class="card" style="margin-bottom: 0;">
                <div class="card-header" style="background: #f8fafc;">
                    <div class="card-title" style="display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-user-graduate" style="color: var(--primary);"></i>
                        <span><?= SecurityHelper::escape($c['nama']) ?></span>
                    </div>
                    <span class="badge badge-info"><?= SecurityHelper::escape($c['unit_name']) ?></span>
                </div>
                <div class="card-body">
                    <table class="table" style="margin-bottom: 20px;">
                        <tbody>
                            <tr>
                                <td style="width: 35%; color: var(--text-muted); border: none; padding: 6px 0;">NIS / NISN</td>
                                <td style="border: none; padding: 6px 0;">
                                    <strong><?= SecurityHelper::escape($c['nis']) ?></strong>
                                    <?php if (!empty($c['nisn'])): ?>
                                        <span style="color: var(--text-muted);">/ <?= SecurityHelper::escape($c['nisn']) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="color: var(--text-muted); border: none; padding: 6px 0;">Kelas Saat Ini</td>
                                <td style="border: none; padding: 6px 0;">
                                    <span class="badge badge-secondary"><?= SecurityHelper::escape($c['class_name'] ?? 'Belum Diplot Kelas') ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td style="color: var(--text-muted); border: none; padding: 6px 0;">Status Santri</td>
                                <td style="border: none; padding: 6px 0;">
                                    <span class="badge badge-success">
                                        <i class="fa-solid fa-check" style="margin-right: 4px;"></i> Aktif Bersekolah
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div style="display: flex; gap: 8px; flex-wrap: wrap; border-top: 1px solid var(--border-color); padding-top: 16px;">
                        <a href="<?= Auth::baseUrl() ?>/parent/rapor-bulanan/<?= $c['id'] ?>" target="_blank" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-file-lines"></i> Rapor Bulanan (PDF)
                        </a>
                        <a href="<?= Auth::baseUrl() ?>/parent/rapor-tahfidz/<?= $c['id'] ?>" target="_blank" class="btn btn-accent btn-sm">
                            <i class="fa-solid fa-quran"></i> Syahadah Tahfidz
                        </a>
                        <a href="<?= Auth::baseUrl() ?>/parent/rapor-gabungan/<?= $c['id'] ?>" target="_blank" class="btn btn-outline btn-sm">
                            <i class="fa-solid fa-file-pdf"></i> Rapor Gabungan
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
