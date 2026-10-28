<?php
// views/panitia/guru_prestasi_mandiri.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
$totalPrestasi = count($myAchievements ?? []);
$terverifikasiCount = 0;
$menungguCount = 0;

foreach ($myAchievements as $item) {
    if (($item['status'] ?? '') === 'Terverifikasi') {
        $terverifikasiCount++;
    } elseif (($item['status'] ?? '') === 'Menunggu Verifikasi') {
        $menungguCount++;
    }
}
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span class="badge badge-primary">Portofolio Pendidik</span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Pengembangan Keprofesian Berkelanjutan (PKB)</span>
            </div>
            <h1 class="page-title" style="margin: 0 0 6px 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-medal" style="color: #f59e0b;"></i> Prestasi &amp; Penghargaan Guru
            </h1>
            <p class="page-subtitle" style="margin: 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Catat dan ajukan prestasi, sertifikat kejuaraan, atau penghargaan Anda. Prestasi yang diajukan akan diverifikasi langsung oleh Kepala Sekolah.
            </p>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 16px 20px; border-left: 4px solid #3b82f6;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Total Diajukan</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-main, #0f172a); margin: 6px 0 2px 0;">
            <?= $totalPrestasi ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Prestasi</span>
        </div>
        <div style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Tahun Ajaran Aktif</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #10b981;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Terverifikasi Kepsek</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #10b981; margin: 6px 0 2px 0;">
            <?= $terverifikasiCount ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Prestasi</span>
        </div>
        <div style="font-size: 0.8125rem; color: #10b981; font-weight: 500;">
            <i class="fa-solid fa-circle-check"></i> Disetujui
        </div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #f59e0b;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Menunggu Verifikasi</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #f59e0b; margin: 6px 0 2px 0;">
            <?= $menungguCount ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Prestasi</span>
        </div>
        <div style="font-size: 0.8125rem; color: #f59e0b; font-weight: 500;">
            <i class="fa-solid fa-hourglass-half"></i> Sedang Ditinjau
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 380px 1fr; gap: 24px; align-items: start;">
    <!-- Form Card Input Prestasi Mandiri -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px;">
            <h3 class="card-title" style="margin: 0; font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-circle-plus" style="color: #10b981;"></i> Ajukan Prestasi Baru
            </h3>
        </div>
        <div class="card-body" style="padding: 20px;">
            <form action="<?= $baseUrl ?>/guru/prestasi/save" method="POST" enctype="multipart/form-data">
                <?= CSRF::field() ?>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Nama Pendidik</label>
                    <input type="text" class="form-control" value="<?= SecurityHelper::escape($employee['nama'] ?? 'Guru') ?>" disabled style="background: #f8fafc; font-weight: 600;">
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Kategori / Nama Prestasi &amp; Penghargaan <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="Contoh: Juara 1 Lomba Inovasi Pembelajaran Digital" required>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Tingkat Prestasi <span style="color: #ef4444;">*</span></label>
                    <select name="level" class="form-control" required>
                        <option value="Satuan Unit">Tingkat Sekolah / Unit</option>
                        <option value="Tingkat Yayasan">Tingkat Yayasan Al Kahfi</option>
                        <option value="Tingkat Kota / Wilayah" selected>Tingkat Kota / Wilayah</option>
                        <option value="Tingkat Provinsi">Tingkat Provinsi</option>
                        <option value="Tingkat Nasional">Tingkat Nasional</option>
                        <option value="Tingkat Internasional">Tingkat Internasional</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Tahun Perolehan <span style="color: #ef4444;">*</span></label>
                    <input type="number" name="year" class="form-control" value="<?= date('Y') ?>" min="2020" max="<?= date('Y') + 1 ?>" required>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Unggah Berkas Bukti / Piagam / Sertifikat <span style="color: #ef4444;">*</span></label>
                    <input type="file" name="certificate" class="form-control" accept=".pdf,image/png,image/jpeg,image/jpg" required>
                    <small style="color: var(--text-muted, #64748b); font-size: 0.75rem;">Format: PDF, JPG, PNG (Maks 5MB).</small>
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Uraian Singkat / Penyelenggara</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Contoh: Diselenggarakan oleh Dinas Pendidikan Kota Batam dalam rangka Hari Guru Nasional."></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 10px 16px; font-weight: 600;">
                    <i class="fa-solid fa-paper-plane" style="margin-right: 6px;"></i> Kirim Prestasi untuk Verifikasi
                </button>
            </form>
        </div>
    </div>

    <!-- Table Card List Prestasi Guru -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <h3 class="card-title" style="margin: 0; font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-list-check" style="color: #3b82f6;"></i> Riwayat Pengajuan Prestasi Saya
            </h3>
            <span class="badge badge-info"><?= $totalPrestasi ?> Catatan</span>
        </div>

        <div class="card-body" style="padding: 0;">
            <div style="overflow-x: auto;">
                <table class="table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">No</th>
                            <th>Prestasi &amp; Penghargaan</th>
                            <th style="width: 130px; text-align: center;">Tingkat</th>
                            <th style="width: 140px; text-align: center;">Status Verifikasi</th>
                            <th style="width: 90px; text-align: center;">Bukti</th>
                            <th style="width: 160px;">Catatan Verifikator</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($myAchievements)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 36px; color: var(--text-muted, #64748b);">
                                    <i class="fa-solid fa-award" style="font-size: 2.2rem; margin-bottom: 8px; display: block; opacity: 0.4;"></i>
                                    Anda belum mengajukan catatan prestasi guru. Silakan isi form di sebelah kiri.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($myAchievements as $idx => $m): 
                                $status = $m['status'] ?? 'Menunggu Verifikasi';
                                $statusBadge = match($status) {
                                    'Terverifikasi' => 'badge-success',
                                    'Ditolak'       => 'badge-danger',
                                    default         => 'badge-warning'
                                };
                            ?>
                                <tr>
                                    <td style="text-align: center; color: var(--text-muted, #64748b);"><?= $idx + 1 ?></td>
                                    <td>
                                        <div style="font-weight: 700; color: var(--text-main, #0f172a); font-size: 0.9375rem;">
                                            <?= SecurityHelper::escape($m['title']) ?>
                                        </div>
                                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                                            Tahun: <strong><?= SecurityHelper::escape($m['year']) ?></strong>
                                            <?php if (!empty($m['notes'])): ?>
                                                &bull; <?= SecurityHelper::escape($m['notes']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge badge-primary"><?= SecurityHelper::escape($m['level']) ?></span>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge <?= $statusBadge ?>" style="display: inline-flex; align-items: center; gap: 4px;">
                                            <?php if ($status === 'Terverifikasi'): ?>
                                                <i class="fa-solid fa-check"></i> Terverifikasi
                                            <?php elseif ($status === 'Ditolak'): ?>
                                                <i class="fa-solid fa-xmark"></i> Ditolak
                                            <?php else: ?>
                                                <i class="fa-solid fa-clock"></i> Menunggu Verifikasi
                                            <?php endif; ?>
                                        </span>
                                        <?php if (!empty($m['verified_at'])): ?>
                                            <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 2px;">
                                                <?= date('d/m/Y H:i', strtotime($m['verified_at'])) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if (!empty($m['proof_document'])): ?>
                                            <a href="<?= $baseUrl ?>/<?= SecurityHelper::escape($m['proof_document']) ?>" target="_blank" class="btn btn-outline btn-sm" style="padding: 3px 8px; font-size: 0.75rem; color: #0d9488;" title="Lihat Berkas Piagam / Sertifikat">
                                                <i class="fa-solid fa-file-shield"></i> Berkas
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #94a3b8; font-size: 0.75rem;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($m['verification_notes'])): ?>
                                            <div style="font-size: 0.8125rem; color: var(--text-main); font-style: italic;">
                                                &ldquo;<?= SecurityHelper::escape($m['verification_notes']) ?>&rdquo;
                                            </div>
                                        <?php else: ?>
                                            <span style="color: #94a3b8; font-size: 0.75rem;">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
