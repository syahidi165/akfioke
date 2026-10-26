<?php
// views/supervisi/jadwal.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
$totalSesi = count($sessions);
$selesai = array_filter($sessions, fn($s) => (float)$s['total_score'] > 0);
$selesaiCount = count($selesai);
$menungguCount = $totalSesi - $selesaiCount;
$rataSkor = $selesaiCount > 0 ? round(array_sum(array_column($selesai, 'total_score')) / $selesaiCount, 1) : 0;
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Jadwal & Agenda Supervisi Mengajar</h1>
        <div class="page-subtitle">Perencanaan observasi kelas pembelajaran, rubrik pedagogik, dan evaluasi ketercapaian guru.</div>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="<?= $baseUrl ?>/supervisi/penilaian" class="btn btn-outline">
            <i class="fa-solid fa-list-check"></i> Daftar Instrumen Penilaian
        </a>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(13, 148, 136, 0.12); color: #0d9488;">
            <i class="fa-solid fa-calendar-days"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $totalSesi ?></div>
            <div class="stat-label">Total Jadwal Sesi</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $selesaiCount ?></div>
            <div class="stat-label">Selesai Diobservasi</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b;">
            <i class="fa-solid fa-clock"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $menungguCount ?></div>
            <div class="stat-label">Menunggu Observasi</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(99, 102, 241, 0.12); color: #6366f1;">
            <i class="fa-solid fa-chart-line"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $rataSkor > 0 ? $rataSkor : '-' ?></div>
            <div class="stat-label">Rata-rata Skor Sesi</div>
        </div>
    </div>
</div>

<!-- Main 2-Column Layout -->
<div style="display: grid; grid-template-columns: minmax(320px, 380px) 1fr; gap: 24px; align-items: start;">
    
    <!-- Kolom Form Penjadwalan -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px;">
            <h3 class="card-title" style="display: flex; align-items: center; gap: 8px; font-size: 1rem; margin: 0;">
                <i class="fa-solid fa-calendar-plus" style="color: #0d9488;"></i> Jadwalkan Supervisi Baru
            </h3>
        </div>
        <div class="card-body" style="padding: 20px;">
            <form action="<?= $baseUrl ?>/supervisi/jadwal/save" method="POST">
                <?= CSRF::field() ?>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Guru yang Disupervisi <span style="color: #ef4444;">*</span></label>
                    <select name="teacher_id" class="form-control" required>
                        <option value="">-- Pilih Guru --</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= SecurityHelper::escape($t['nama']) ?> (<?= SecurityHelper::escape($t['position']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Supervisor / Asessor <span style="color: #ef4444;">*</span></label>
                    <select name="supervisor_id" class="form-control" required>
                        <option value="">-- Pilih Supervisor --</option>
                        <?php foreach ($supervisors as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= SecurityHelper::escape($s['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Mata Pelajaran <span style="color: #ef4444;">*</span></label>
                    <select name="subject_id" class="form-control" required>
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        <?php foreach ($subjects as $sub): ?>
                            <option value="<?= $sub['id'] ?>"><?= SecurityHelper::escape($sub['name']) ?> (<?= SecurityHelper::escape($sub['code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Kelas yang Diobservasi <span style="color: #ef4444;">*</span></label>
                    <select name="class_id" class="form-control" required>
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= SecurityHelper::escape($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Tanggal Pelaksanaan <span style="color: #ef4444;">*</span></label>
                    <input type="date" name="session_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 10px 16px;">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Jadwal Supervisi
                </button>
            </form>
        </div>
    </div>

    <!-- Kolom Tabel Agenda & Riwayat Sesi -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title" style="display: flex; align-items: center; gap: 8px; font-size: 1rem; margin: 0;">
                <i class="fa-solid fa-list-ul" style="color: #0d9488;"></i> Agenda & Pelaksanaan Supervisi
            </h3>
            <span class="badge badge-secondary"><?= $totalSesi ?> Sesi Terdaftar</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">No</th>
                            <th>Guru & Supervisor</th>
                            <th>Mapel & Kelas</th>
                            <th>Tanggal</th>
                            <th style="text-align: center;">Skor</th>
                            <th style="text-align: center;">Status / Predikat</th>
                            <th style="width: 140px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sessions)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 36px; color: var(--text-muted, #64748b);">
                                    <i class="fa-solid fa-calendar-xmark" style="font-size: 2rem; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                                    Belum ada jadwal supervisi mengajar yang didaftarkan.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sessions as $idx => $s): 
                                $isRated = (float)$s['total_score'] > 0;
                                $pred = $s['achievement_predicate'] ?? '';
                                $badgeClass = match(true) {
                                    str_contains($pred, 'SANGAT BAIK') => 'badge-success',
                                    str_contains($pred, 'BAIK')        => 'badge-info',
                                    str_contains($pred, 'CUKUP')       => 'badge-warning',
                                    str_contains($pred, 'PERLU')       => 'badge-danger',
                                    default                            => 'badge-secondary'
                                };
                            ?>
                                <tr>
                                    <td style="text-align: center;"><?= $idx + 1 ?></td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-main, #0f172a);"><?= SecurityHelper::escape($s['teacher_name']) ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted, #64748b);">
                                            <i class="fa-solid fa-user-tie" style="margin-right: 4px; opacity: 0.7;"></i><?= SecurityHelper::escape($s['supervisor_name']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 500;"><?= SecurityHelper::escape($s['subject_name']) ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted, #64748b);">
                                            <i class="fa-solid fa-door-open" style="margin-right: 4px; opacity: 0.7;"></i><?= SecurityHelper::escape($s['class_name']) ?>
                                        </div>
                                    </td>
                                    <td style="font-size: 0.85rem; white-space: nowrap;">
                                        <?= SecurityHelper::formatTanggalIndo($s['session_date']) ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($isRated): ?>
                                            <span style="font-weight: 700; font-size: 1rem; color: #0f766e;"><?= $s['total_score'] ?></span>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted, #94a3b8);">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center; white-space: nowrap;">
                                        <?php if ($isRated): ?>
                                            <span class="badge <?= $badgeClass ?>"><?= SecurityHelper::escape($pred) ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-warning" style="background: rgba(245, 158, 11, 0.15); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.3);">
                                                <i class="fa-solid fa-hourglass-half" style="margin-right: 4px;"></i> Belum Dinilai
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="display: inline-flex; gap: 4px; justify-content: center;">
                                            <a href="<?= $baseUrl ?>/supervisi/form/<?= $s['id'] ?>" class="btn btn-outline btn-sm" title="Beri Penilaian / Edit Skor">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <?php if ($isRated): ?>
                                                <a href="<?= $baseUrl ?>/supervisi/print/<?= $s['id'] ?>" target="_blank" class="btn btn-outline btn-sm" title="Cetak Instrumen PDF">
                                                    <i class="fa-solid fa-print"></i>
                                                </a>
                                            <?php endif; ?>
                                            <form action="<?= $baseUrl ?>/supervisi/jadwal/delete" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal supervisi ini?');">
                                                <?= CSRF::field() ?>
                                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                                <button type="submit" class="btn btn-outline btn-sm" style="color: #ef4444; border-color: rgba(239, 68, 68, 0.3);" title="Hapus Jadwal">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
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
