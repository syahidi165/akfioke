<?php
// views/panitia/best_practice.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><?= SecurityHelper::escape($pageTitle) ?></h1>
        <div class="page-subtitle">
            <?= $isWalas 
                ? 'Pengunggahan Laporan Penelitian Tindakan Kelas (PTK) untuk pemenuhan indikator kinerja AKFI Wali Kelas.' 
                : 'Pengunggahan karya tulis Best Practice Pembelajaran untuk pemenuhan indikator kinerja AKFI Guru.' ?>
        </div>
    </div>
</div>

<div class="card" style="background: linear-gradient(135deg, #0f766e 0%, #1e3a8a 100%); color: #ffffff; border: none; margin-bottom: 24px;">
    <div class="card-body" style="padding: 20px 24px;">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0;">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div>
                <h4 style="font-size: 16px; font-weight: 700; margin-bottom: 4px; color: #ffffff;">
                    Ketentuan Karya Tulis Ilmiah Yayasan Islam Al Kahfi
                </h4>
                <p style="font-size: 13px; opacity: 0.9; margin: 0; line-height: 1.5;">
                    Guru Mata Pelajaran & Tahfidz mengunggah <strong>Best Practice Pembelajaran</strong>, sedangkan Guru Wali Kelas mengunggah <strong>Laporan PTK (Penelitian Tindakan Kelas)</strong>. Dokumen yang disetujui Kepala Sekolah otomatis memberikan skor 100% pada instrumen penilaian AKFI.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="grid-cols-2">
    <!-- FORM UPLOAD -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-cloud-arrow-up" style="color: var(--primary); margin-right: 8px;"></i>
                Formulir Unggah Berkas
            </div>
        </div>
        <div class="card-body">
            <form action="<?= Auth::baseUrl() ?>/guru/best-practice/upload" method="POST" enctype="multipart/form-data">
                <?= CSRF::field() ?>

                <div class="form-group">
                    <label class="form-label">Jenis Dokumen Karya Tulis <span style="color: var(--danger);">*</span></label>
                    <select name="type" class="form-control" required>
                        <option value="PTK" <?= $defaultType === 'PTK' ? 'selected' : '' ?>>Laporan PTK (Khusus Wali Kelas)</option>
                        <option value="Best Practice" <?= $defaultType === 'Best Practice' ? 'selected' : '' ?>>Best Practice Pembelajaran (Guru Mapel/Tahfidz)</option>
                    </select>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                        Pilih jenis yang sesuai dengan penugasan dan instrumen AKFI Anda.
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Judul Karya Tulis Ilmiah <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="Contoh: Peningkatan Kemampuan Tahfidz Siswa Melalui Metode Talaqqi..." required>
                </div>

                <div class="form-group">
                    <label class="form-label">Berkas Dokumen PDF (Maks. 10MB) <span style="color: var(--danger);">*</span></label>
                    <input type="file" name="doc_file" class="form-control" accept=".pdf" required>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                        Pastikan berkas telah ditandatangani dan dalam format .pdf.
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 10px; margin-top: 10px;">
                    <i class="fa-solid fa-upload"></i> Unggah Berkas & Ajukan ke Kepala Sekolah
                </button>
            </form>
        </div>
    </div>

    <!-- DAFTAR DOKUMEN TERSIMPAN -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-clock-rotate-left" style="color: var(--accent); margin-right: 8px;"></i>
                Riwayat Dokumen Terunggah
            </div>
            <span class="badge badge-secondary"><?= count($karyaList ?? []) ?> Berkas</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($karyaList)): ?>
                <div style="text-align: center; padding: 48px 20px;">
                    <div style="width: 56px; height: 56px; border-radius: 50%; background: #f1f5f9; color: #94a3b8; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 12px;">
                        <i class="fa-solid fa-file-circle-question"></i>
                    </div>
                    <h4 style="font-size: 15px; font-weight: 600; color: var(--text-main); margin-bottom: 4px;">Belum Ada Berkas</h4>
                    <p style="font-size: 12px; color: var(--text-muted); max-width: 320px; margin: 0 auto;">
                        Silakan unggah dokumen karya tulis Anda di formulir sebelah kiri untuk diverifikasi Kepala Sekolah.
                    </p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 40px;">No</th>
                                <th>Jenis</th>
                                <th>Judul Karya Tulis</th>
                                <th>Status</th>
                                <th style="width: 80px; text-align: center;">Berkas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($karyaList as $idx => $k): ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td>
                                        <span class="badge <?= $k['type'] === 'PTK' ? 'badge-info' : 'badge-secondary' ?>">
                                            <?= SecurityHelper::escape($k['type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?= SecurityHelper::escape($k['title']) ?></strong>
                                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                                            Diunggah: <?= SecurityHelper::formatTanggalIndo(date('Y-m-d', strtotime($k['created_at']))) ?>
                                        </div>
                                        <?php if (!empty($k['review_notes'])): ?>
                                            <div style="font-size: 11px; color: #b91c1c; margin-top: 4px; background: #fef2f2; padding: 4px 8px; border-radius: 4px;">
                                                <i class="fa-solid fa-comment-dots me-1"></i> Catatan KS: <?= SecurityHelper::escape($k['review_notes']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($k['status'] === 'Disetujui'): ?>
                                            <span class="badge badge-success">
                                                <i class="fa-solid fa-circle-check" style="margin-right: 4px;"></i> Disetujui
                                            </span>
                                            <div style="font-size: 10px; color: var(--success); margin-top: 2px; font-weight: 600;">Skor AKFI: 100%</div>
                                        <?php elseif ($k['status'] === 'Revisi'): ?>
                                            <span class="badge badge-danger">
                                                <i class="fa-solid fa-triangle-exclamation" style="margin-right: 4px;"></i> Perlu Revisi
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">
                                                <i class="fa-solid fa-clock" style="margin-right: 4px;"></i> Menunggu Review
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="<?= Auth::baseUrl() ?>/karya-ilmiah/download/<?= $k['id'] ?>" target="_blank" class="btn btn-outline btn-sm" title="Buka PDF">
                                            <i class="fa-solid fa-file-pdf" style="color: #dc2626;"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
