<?php
// views/tahfidz/ajukan_ujian.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
$totalExams = count($exams);
$menungguCount = count(array_filter($exams, fn($e) => $e['status'] === 'Menunggu'));
$lulusCount = count(array_filter($exams, fn($e) => $e['status'] === 'Lulus'));
$gagalCount = count(array_filter($exams, fn($e) => $e['status'] === 'Tidak Lulus'));
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Pengajuan Ujian Tahfidz ke Tutor Siswa</h1>
        <div class="page-subtitle">Daftarkan santri yang telah menyelesaikan target hafalan untuk mengikuti ujian kelayakan bersertifikasi oleh Tim Tutor Siswa.</div>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="<?= $baseUrl ?>/tahfidz/setoran-siswa" class="btn btn-outline">
            <i class="fa-solid fa-quran"></i> Setoran Hafalan Santri
        </a>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(13, 148, 136, 0.12); color: #0d9488;">
            <i class="fa-solid fa-graduation-cap"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $totalExams ?></div>
            <div class="stat-label">Total Pengajuan</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b;">
            <i class="fa-solid fa-clock"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $menungguCount ?></div>
            <div class="stat-label">Menunggu Ujian</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
            <i class="fa-solid fa-certificate"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $lulusCount ?></div>
            <div class="stat-label">Lulus Bersertifikat</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(239, 68, 68, 0.12); color: #ef4444;">
            <i class="fa-solid fa-circle-xmark"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $gagalCount ?></div>
            <div class="stat-label">Belum Lulus</div>
        </div>
    </div>
</div>

<!-- Main 2-Column Layout -->
<div style="display: grid; grid-template-columns: minmax(320px, 380px) 1fr; gap: 24px; align-items: start;">
    
    <!-- Kolom Form Pengajuan -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px;">
            <h3 class="card-title" style="display: flex; align-items: center; gap: 8px; font-size: 1rem; margin: 0;">
                <i class="fa-solid fa-file-signature" style="color: #0d9488;"></i> Ajukan Santri Baru
            </h3>
        </div>
        <div class="card-body" style="padding: 20px;">
            <?php if (!empty($myHalaqahs)): ?>
                <div style="background: rgba(13, 148, 136, 0.08); border-left: 3px solid #0d9488; padding: 10px 14px; border-radius: 4px; font-size: 0.85rem; margin-bottom: 16px; color: #0f766e;">
                    <i class="fa-solid fa-users-rectangle"></i> <strong>Kelompok Halaqah Anda:</strong><br>
                    <?= implode(', ', array_map(fn($h) => SecurityHelper::escape($h['name']), $myHalaqahs)) ?>
                </div>
            <?php elseif (!empty($isGuruTahfidz)): ?>
                <div style="background: #fef2f2; border-left: 3px solid #ef4444; padding: 12px 14px; border-radius: 4px; font-size: 0.85rem; margin-bottom: 16px; color: #991b1b; line-height: 1.5;">
                    <i class="fa-solid fa-triangle-exclamation"></i> <strong>Perhatian:</strong><br>
                    Anda belum terdaftar mengampu kelompok halaqah santri pada tahun ajaran ini. Silakan hubungi Koordinator Tahfidz untuk pembagian halaqah.
                </div>
            <?php endif; ?>

            <form action="<?= $baseUrl ?>/tahfidz/ajukan-ujian" method="POST">
                <?= CSRF::field() ?>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">
                        Pilih Santri <?= !empty($isGuruTahfidz) ? '(Halaqah Anda)' : '' ?> <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="student_id" class="form-control" required <?= empty($students) ? 'disabled' : '' ?>>
                        <option value="">-- <?= empty($students) ? 'Tidak ada santri di halaqah Anda' : 'Pilih Santri' ?> --</option>
                        <?php foreach ($students as $st): ?>
                            <option value="<?= $st['id'] ?>">
                                <?= SecurityHelper::escape($st['nama']) ?> (NIS: <?= SecurityHelper::escape($st['nis']) ?> - <?= SecurityHelper::escape($st['class_name'] ?? 'Non-Kelas') ?>)
                                <?php if (!empty($st['halaqah_name'])): ?> &bull; [<?= SecurityHelper::escape($st['halaqah_name']) ?>]<?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($students) && !empty($isGuruTahfidz)): ?>
                        <small style="color: #ef4444; font-size: 0.75rem; display: block; margin-top: 4px;">
                            Tidak ada santri aktif dalam halaqah Anda untuk diajukan.
                        </small>
                    <?php endif; ?>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Juz yang Akan Diujikan <span style="color: #ef4444;">*</span></label>
                    <select name="juz_number" class="form-control" required <?= empty($students) ? 'disabled' : '' ?>>
                        <?php for ($j = 30; $j >= 1; $j--): ?>
                            <option value="<?= $j ?>" <?= $j === 30 ? 'selected' : '' ?>>Juz <?= $j ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Catatan Rekomendasi Guru</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Contoh: Hafalan juz 30 sudah mutqin, kelancaran dan tajwid sudah layak uji..." <?= empty($students) ? 'disabled' : '' ?>></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 10px 16px;" <?= empty($students) ? 'disabled' : '' ?>>
                    <i class="fa-solid fa-paper-plane" style="margin-right: 6px;"></i> Kirim Pengajuan Ujian
                </button>
            </form>
        </div>
    </div>

    <!-- Kolom Tabel Riwayat Pengajuan -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title" style="display: flex; align-items: center; gap: 8px; font-size: 1rem; margin: 0;">
                <i class="fa-solid fa-list-check" style="color: #0d9488;"></i> Riwayat & Status Pengajuan Ujian <?= !empty($isGuruTahfidz) ? '(Halaqah Anda)' : '' ?>
            </h3>
            <span class="badge badge-secondary"><?= $totalExams ?> Pengajuan</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">No</th>
                            <th>Nama Santri</th>
                            <th style="text-align: center;">Juz</th>
                            <th>Penguji (Tutor)</th>
                            <th style="text-align: center;">Nilai</th>
                            <th style="text-align: center;">Status</th>
                            <th style="width: 100px; text-align: center;">Sertifikat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($exams)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 36px; color: var(--text-muted, #64748b);">
                                    <i class="fa-solid fa-graduation-cap" style="font-size: 2rem; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                                    <?= !empty($isGuruTahfidz) ? 'Belum ada santri dari halaqah Anda yang diajukan ujian tahfidz.' : 'Belum ada santri yang diajukan ujian tahfidz.' ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($exams as $idx => $ex): 
                                $status = $ex['status'];
                                $badgeClass = match($status) {
                                    'Lulus' => 'badge-success',
                                    'Tidak Lulus' => 'badge-danger',
                                    default => 'badge-warning'
                                };
                            ?>
                                <tr>
                                    <td style="text-align: center;"><?= $idx + 1 ?></td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-main, #0f172a);"><?= SecurityHelper::escape($ex['student_name']) ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted, #64748b);">
                                            NIS: <?= SecurityHelper::escape($ex['nis']) ?>
                                            <?php if (!empty($ex['halaqah_name'])): ?>
                                                &bull; <span style="color: #0d9488; font-weight: 500;"><?= SecurityHelper::escape($ex['halaqah_name']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <span style="font-weight: 700; color: #0f766e;">Juz <?= $ex['juz_number'] ?></span>
                                    </td>
                                    <td>
                                        <?= SecurityHelper::escape($ex['examiner_name'] ?: 'Menunggu Tutor Siswa') ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <?= $ex['score'] !== null ? '<strong>' . $ex['score'] . '</strong>' : '-' ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge <?= $badgeClass ?>"><?= $status ?></span>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($status === 'Lulus'): ?>
                                            <a href="<?= $baseUrl ?>/tahfidz/ujian/sertifikat/<?= $ex['id'] ?>" target="_blank" class="btn btn-outline btn-sm" title="Unduh Sertifikat PDF" style="color: #10b981; border-color: rgba(16, 185, 129, 0.4);">
                                                <i class="fa-solid fa-award"></i>
                                            </a>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted, #94a3b8);">-</span>
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
