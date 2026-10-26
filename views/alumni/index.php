<?php
// views/alumni/index.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$totalAlumni = count($alumniList);
$pctFavorite = $totalAlumni > 0 ? round(($favoriteCount / $totalAlumni) * 100, 1) : 0;
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><?= SecurityHelper::escape($pageTitle) ?></h1>
        <div class="page-subtitle">
            Pelacakan studi lanjut alumni ke sekolah unggulan atau PTN favorit untuk pemenuhan indikator mutu AKFI.
        </div>
    </div>
</div>

<!-- 3 KARTU STATISTIK ALUMNI -->
<div class="grid-cols-3" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fa-solid fa-user-graduate"></i>
        </div>
        <div>
            <div class="stat-value"><?= number_format($totalAlumni) ?> <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">Alumni</span></div>
            <div class="stat-label">Total Alumni Terlacak</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon emerald">
            <i class="fa-solid fa-star"></i>
        </div>
        <div>
            <div class="stat-value" style="color: var(--accent);"><?= number_format($favoriteCount) ?> <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">Alumni</span></div>
            <div class="stat-label">Lolos Favorit / PTN</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber">
            <i class="fa-solid fa-chart-pie"></i>
        </div>
        <div>
            <div class="stat-value"><?= $pctFavorite ?>%</div>
            <div class="stat-label">Persentase Capaian Favorit</div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 360px 1fr; gap: 24px; align-items: start;">
    <!-- FORM INPUT JEJAK ALUMNI -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fa-solid fa-graduation-cap" style="color: var(--primary-light); margin-right: 8px;"></i>
                Input Jejak Studi Alumni
            </h3>
        </div>
        <div class="card-body">
            <form action="<?= Auth::baseUrl() ?>/alumni/save" method="POST">
                <?= CSRF::field() ?>

                <div class="form-group">
                    <label class="form-label">Pilih Siswa / Alumni (Khusus Siswa Lulus / Pindah) <span style="color: var(--danger);">*</span></label>
                    <select name="student_id" class="form-control" required>
                        <option value="">-- Pilih Siswa Lulus / Pindah --</option>
                        <?php if (empty($students)): ?>
                            <option value="" disabled>⚠️ Belum ada data siswa berstatus Lulus atau Pindah</option>
                        <?php else: ?>
                            <?php foreach ($students as $st): ?>
                                <option value="<?= $st['id'] ?>">
                                    <?= SecurityHelper::escape($st['nama']) ?> (NIS: <?= SecurityHelper::escape($st['nis'] ?? '-') ?> - [Status: <?= SecurityHelper::escape($st['status']) ?>])
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?php if (empty($students)): ?>
                        <small style="color: var(--danger); font-size: 11px; margin-top: 4px; display: block;">
                            <i class="fa-solid fa-circle-info"></i> Hanya siswa berstatus <strong>Lulus</strong> atau <strong>Pindah</strong> yang dapat dicatat di pelacakan alumni. Siswa dapat dikelola di menu <a href="<?= Auth::baseUrl() ?>/students/alumni-mutasi" style="text-decoration: underline; font-weight: 600;">Siswa Lulus & Pindah</a>.
                        </small>
                    <?php else: ?>
                        <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px; display: block;">
                            <i class="fa-solid fa-circle-check" style="color: var(--primary);"></i> Menampilkan <?= count($students) ?> siswa berstatus Lulus atau Pindah dari unit ini.
                        </small>
                    <?php endif; ?>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                    <div>
                        <label class="form-label">Tahun Lulus <span style="color: var(--danger);">*</span></label>
                        <input type="number" name="graduation_year" class="form-control" style="text-align: center;" value="<?= date('Y') ?>" required min="2000" max="2099">
                    </div>
                    <div>
                        <label class="form-label">Kategori</label>
                        <select name="destination_type" class="form-control">
                            <option value="Sekolah">Sekolah Lanjutan</option>
                            <option value="Universitas">Universitas / PTN</option>
                            <option value="Kerja/Lainnya">Kerja / Lainnya</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Sekolah / Kampus Tujuan <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="destination_name" class="form-control" placeholder="Contoh: Universitas Indonesia / SMA Taruna" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Jurusan / Program Studi</label>
                    <input type="text" name="major" class="form-control" placeholder="Contoh: Kedokteran / IPA / Teknik">
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan Tambahan</label>
                    <input type="text" name="notes" class="form-control" placeholder="Jalur SNBP, beasiswa, dsb (opsional)">
                </div>

                <div class="form-group" style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 12px; margin-top: 8px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin-bottom: 0; font-weight: 500; font-size: 13px;">
                        <input type="checkbox" name="is_favorite" value="1" checked style="width: 16px; height: 16px; accent-color: var(--primary);">
                        <span>Kategori <strong>Sekolah / Kampus Favorit / PTN</strong></span>
                    </label>
                    <div style="font-size: 11px; color: var(--text-muted); margin-left: 26px; margin-top: 2px;">
                        Centang jika tujuan studi termasuk kategori rujukan unggulan yayasan.
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 10px; margin-top: 10px;">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Jejak Alumni
                </button>
            </form>
        </div>
    </div>

    <!-- TABEL REKAPITULASI PENELUSURAN -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fa-solid fa-list-check" style="color: var(--accent); margin-right: 8px;"></i>
                Rekapitulasi Penelusuran Jejak Alumni
            </h3>
            <span class="badge badge-secondary"><?= $totalAlumni ?> Catatan</span>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">No</th>
                        <th>Nama Alumni</th>
                        <th style="width: 110px; text-align: center;">Tahun Lulus</th>
                        <th>Lembaga & Jurusan Lanjutan</th>
                        <th style="width: 140px; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($alumniList)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px 20px; color: var(--text-muted);">
                                <i class="fa-solid fa-user-slash" style="font-size: 32px; margin-bottom: 10px; display: block; opacity: 0.4;"></i>
                                Belum ada data penelusuran jejak alumni yang tercatat.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($alumniList as $idx => $a): ?>
                            <tr>
                                <td style="text-align: center; color: var(--text-muted);"><?= $idx + 1 ?></td>
                                <td>
                                    <div style="font-weight: 600; color: var(--text-main);">
                                        <?= SecurityHelper::escape($a['student_name']) ?>
                                    </div>
                                    <div style="font-size: 11px; color: var(--text-muted);">
                                        NIS: <?= SecurityHelper::escape($a['nis'] ?? '-') ?>
                                    </div>
                                </td>
                                <td style="text-align: center; font-weight: 600; font-family: monospace;">
                                    <?= SecurityHelper::escape($a['graduation_year']) ?>
                                </td>
                                <td>
                                    <div style="font-weight: 600;">
                                        <?= SecurityHelper::escape($a['destination_name']) ?>
                                    </div>
                                    <?php if (!empty($a['major'])): ?>
                                        <div style="font-size: 12px; color: var(--accent); font-weight: 500;">
                                            <i class="fa-solid fa-book-open" style="font-size: 10px; margin-right: 4px;"></i>
                                            <?= SecurityHelper::escape($a['major']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($a['notes'])): ?>
                                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                                            <?= SecurityHelper::escape($a['notes']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if (!empty($a['is_favorite'])): ?>
                                        <span class="badge badge-success">
                                            <i class="fa-solid fa-star" style="font-size: 9px; margin-right: 4px;"></i> Favorit / PTN
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Reguler</span>
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
