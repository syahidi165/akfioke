<?php
// views/attendance/students.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
$months = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$totalSiswa = count($students);
$totHadir = array_sum(array_map(fn($s) => (int)($s['hadir'] ?? 0), $students));
$totSakit = array_sum(array_map(fn($s) => (int)($s['sakit'] ?? 0), $students));
$totIzin = array_sum(array_map(fn($s) => (int)($s['izin'] ?? 0), $students));
$totAlpha = array_sum(array_map(fn($s) => (int)($s['alpha'] ?? 0), $students));
$totHari = $totHadir + $totSakit + $totIzin + $totAlpha;
$isTahfidzOnly = !empty($isTahfidzOnly);
$selectedHalaqahId = (int)($selectedHalaqahId ?? 0);
$halaqahList = $halaqahList ?? [];
$persenKehadiran = $totHari > 0 ? round(($totHadir / $totHari) * 100, 1) : 100;
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><?= $isTahfidzOnly ? 'Presensi Santri Bulanan (Halaqah Tahfidz)' : 'Presensi Kehadiran Santri Bulanan' ?></h1>
        <div class="page-subtitle"><?= $isTahfidzOnly ? 'Pencatatan dan rekapitulasi presensi bulanan santri khusus pada halaqah tahfidz yang Anda ampu.' : 'Pencatatan dan rekapitulasi presensi kehadiran santri (Hadir, Sakit, Izin, Alpha) setiap bulan untuk laporan wali kelas & rapor.' ?></div>
    </div>
    <div style="display: flex; gap: 8px;">
        <?php if ($isTahfidzOnly): ?>
            <a href="<?= $baseUrl ?>/tahfidz/setoran-siswa" class="btn btn-outline">
                <i class="fa-solid fa-quran"></i> Setoran Hafalan Santri
            </a>
        <?php else: ?>
            <a href="<?= $baseUrl ?>/nilai/ledger?class_id=<?= $selectedClassId ?>&month_num=<?= $monthNum ?>" class="btn btn-outline">
                <i class="fa-solid fa-table-list"></i> Lihat Ledger Nilai
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Filter Bar -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 14px 20px;">
        <h3 class="card-title" style="display: flex; align-items: center; gap: 8px; font-size: 0.95rem; margin: 0;">
            <i class="fa-solid fa-filter" style="color: #0d9488;"></i> <?= $isTahfidzOnly ? 'Filter Halaqah & Bulan Presensi' : 'Filter Kelas & Bulan Presensi' ?>
        </h3>
    </div>
    <div class="card-body" style="padding: 18px 20px;">
        <form method="GET" action="<?= $baseUrl ?>/attendance/students" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)) auto; gap: 16px; align-items: end;">
            <?php if ($isTahfidzOnly): ?>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Pilih Halaqah Santri Saya</label>
                    <select name="halaqah_id" class="form-control" onchange="this.form.submit()">
                        <?php foreach ($halaqahList as $h): ?>
                            <option value="<?= $h['id'] ?>" <?= $h['id'] == $selectedHalaqahId ? 'selected' : '' ?>>
                                <?= SecurityHelper::escape($h['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Pilih Kelas</label>
                    <select name="class_id" class="form-control" onchange="this.form.submit()">
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $c['id'] == $selectedClassId ? 'selected' : '' ?>>
                                <?= SecurityHelper::escape($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Bulan Presensi</label>
                <select name="month_num" class="form-control" onchange="this.form.submit()">
                    <?php foreach ($months as $num => $nm): ?>
                        <option value="<?= $num ?>" <?= $num == $monthNum ? 'selected' : '' ?>><?= $nm ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <button type="submit" class="btn btn-primary" style="height: 42px; padding: 0 20px;">
                    <i class="fa-solid fa-magnifying-glass"></i> Tampilkan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 12-Month Timeline / Rekap Status Pengisian Presensi Santri Bulanan -->
<?php if (($isTahfidzOnly && $selectedHalaqahId > 0) || (!empty($classes) && $selectedClassId > 0)): ?>
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; flex-wrap: wrap; gap: 8px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-calendar-days" style="color: #0d9488;"></i>
            <h4 style="margin: 0; font-size: 0.875rem; font-weight: 600; color: var(--text-main, #0f172a);">
                Status Pengisian Presensi Santri per Bulan (Tahun Ajaran Berjalan)
            </h4>
        </div>
        <div style="font-size: 0.75rem; color: var(--text-muted, #64748b);">
            Klik pada kotak bulan untuk berpindah langsung ke bulan tersebut
        </div>
    </div>
    <div class="card-body" style="padding: 14px 16px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(85px, 1fr)); gap: 8px;">
            <?php 
            $shortMonths = [
                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
                9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
            ];
            $timelineParam = $isTahfidzOnly ? "halaqah_id={$selectedHalaqahId}" : "class_id={$selectedClassId}";
            foreach ($shortMonths as $num => $sNm): 
                $st = $monthStatus[$num] ?? ['is_filled' => false, 'count' => 0, 'avg_pct' => 0.0];
                $isSelected = ($num == $monthNum);
                $isFilled = $st['is_filled'];
            ?>
                <a href="<?= $baseUrl ?>/attendance/students?<?= $timelineParam ?>&month_num=<?= $num ?>" 
                   style="text-decoration: none; border-radius: 8px; padding: 10px 6px; text-align: center; transition: all 0.2s ease; display: flex; flex-direction: column; justify-content: space-between; min-height: 80px;
                          <?= $isSelected ? 'outline: 2px solid #0d9488; outline-offset: 1px; box-shadow: 0 4px 6px -1px rgba(13, 148, 136, 0.2);' : 'border: 1px solid var(--border-color, #e2e8f0);' ?>
                          <?= $isFilled ? 'background: #f0fdf4; border-color: #bbf7d0;' : 'background: #f8fafc;' ?>">
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <span style="font-size: 0.75rem; font-weight: 700; color: #334155;"><?= $sNm ?></span>
                        <?php if ($isSelected): ?>
                            <span style="font-size: 0.65rem; background: #0d9488; color: white; border-radius: 4px; padding: 1px 4px; font-weight: 600;">Aktif</span>
                        <?php endif; ?>
                    </div>

                    <div style="margin: 4px 0;">
                        <?php if ($isFilled): ?>
                            <span style="font-size: 0.75rem; font-weight: 700; color: #16a34a; display: block;">
                                <i class="fa-solid fa-circle-check"></i> Sudah Isi
                            </span>
                            <span style="font-size: 0.7rem; font-weight: 600; color: #047857; display: block;">
                                <?= $st['avg_pct'] ?>% Hadir
                            </span>
                        <?php else: ?>
                            <span style="font-size: 0.75rem; color: #94a3b8; display: block;">
                                <i class="fa-regular fa-circle"></i> Belum
                            </span>
                            <span style="font-size: 0.7rem; color: #cbd5e1; display: block;">Kosong</span>
                        <?php endif; ?>
                    </div>

                    <div style="font-size: 0.65rem; color: var(--text-muted, #64748b);">
                        <?= $isFilled ? $st['count'] . ' Santri' : '-' ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Stats Ringkasan Presensi -->
<?php if (!empty($students)): ?>
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(13, 148, 136, 0.12); color: #0d9488;">
            <i class="fa-solid fa-users"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $totalSiswa ?></div>
            <div class="stat-label">Total Santri</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
            <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $persenKehadiran ?>%</div>
            <div class="stat-label">Rata-rata Kehadiran</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b;">
            <i class="fa-solid fa-notes-medical"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $totSakit + $totIzin ?> Hari</div>
            <div class="stat-label">Total Sakit & Izin</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(239, 68, 68, 0.12); color: #ef4444;">
            <i class="fa-solid fa-user-xmark"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $totAlpha ?> Hari</div>
            <div class="stat-label">Total Alpha</div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Card Form Presensi Santri -->
<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div>
            <h3 class="card-title" style="display: flex; align-items: center; gap: 8px; font-size: 1rem; margin: 0;">
                <i class="fa-solid fa-clipboard-user" style="color: #0d9488;"></i> Form Rekapitulasi Presensi Santri
            </h3>
        </div>
        <?php if (!empty($students)): ?>
            <div style="display: flex; gap: 8px; align-items: center;">
                <button type="button" class="btn btn-outline btn-sm" onclick="setAllHadir(22)" title="Isi otomatis 22 hari hadir untuk seluruh siswa yang belum ada absennya">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Set Semua Hadir 22 Hari
                </button>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($students)): ?>
            <div style="text-align: center; padding: 48px 20px; color: var(--text-muted, #64748b);">
                <i class="fa-solid fa-folder-open" style="font-size: 2.5rem; margin-bottom: 12px; display: block; opacity: 0.5;"></i>
                <?= $isTahfidzOnly ? 'Belum ada anggota santri aktif pada halaqah yang dipilih.' : 'Belum ada data santri aktif pada kelas yang dipilih. Silakan pilih kelas santri aktif terlebih dahulu.' ?>
            </div>
        <?php else: ?>
            <form action="<?= $baseUrl ?>/attendance/students/save" method="POST">
                <?= CSRF::field() ?>
                <input type="hidden" name="class_id" value="<?= $selectedClassId ?>">
                <input type="hidden" name="halaqah_id" value="<?= $selectedHalaqahId ?>">
                <input type="hidden" name="month_num" value="<?= $monthNum ?>">

                <div style="overflow-x: auto;">
                    <table class="table" style="text-align: center;">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">No</th>
                                <th style="width: 120px; text-align: left;">NIS</th>
                                <th style="text-align: left;">Nama Lengkap Santri</th>
                                <?php if ($isTahfidzOnly): ?>
                                    <th style="width: 130px; text-align: left;">Kelas Asal</th>
                                <?php endif; ?>
                                <th style="width: 60px; text-align: center;">L/P</th>
                                <th style="width: 130px; text-align: center;">
                                    <span style="color: #10b981;"><i class="fa-solid fa-check"></i> Hadir (H)</span>
                                </th>
                                <th style="width: 110px; text-align: center;">
                                    <span style="color: #f59e0b;"><i class="fa-solid fa-heart-pulse"></i> Sakit (S)</span>
                                </th>
                                <th style="width: 110px; text-align: center;">
                                    <span style="color: #6366f1;"><i class="fa-solid fa-envelope-open-text"></i> Izin (I)</span>
                                </th>
                                <th style="width: 110px; text-align: center;">
                                    <span style="color: #ef4444;"><i class="fa-solid fa-xmark"></i> Alpha (A)</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $idx => $st): 
                                $hadirVal = $st['hadir'] !== null ? (int)$st['hadir'] : 20;
                                $sakitVal = $st['sakit'] !== null ? (int)$st['sakit'] : 0;
                                $izinVal = $st['izin'] !== null ? (int)$st['izin'] : 0;
                                $alphaVal = $st['alpha'] !== null ? (int)$st['alpha'] : 0;
                            ?>
                                <tr>
                                    <td style="text-align: center;">
                                        <input type="hidden" name="att[<?= $st['student_id'] ?>][class_id]" value="<?= (int)($st['class_id'] ?? $selectedClassId) ?>">
                                        <?= $idx + 1 ?>
                                    </td>
                                    <td style="text-align: left;"><code style="font-size: 0.85rem;"><?= SecurityHelper::escape($st['nis']) ?></code></td>
                                    <td style="text-align: left;">
                                        <strong style="color: var(--text-main, #0f172a);"><?= SecurityHelper::escape($st['nama']) ?></strong>
                                    </td>
                                    <?php if ($isTahfidzOnly): ?>
                                        <td style="text-align: left;">
                                            <span class="badge badge-secondary" style="font-size: 0.75rem; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">
                                                <i class="fa-solid fa-graduation-cap" style="margin-right: 4px;"></i><?= SecurityHelper::escape($st['class_name'] ?? '-') ?>
                                            </span>
                                        </td>
                                    <?php endif; ?>
                                    <td style="text-align: center;">
                                        <span class="badge badge-secondary" style="font-size: 0.75rem;"><?= $st['gender'] ?></span>
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="max-width: 90px; margin: 0 auto;">
                                            <input type="number" min="0" max="31" 
                                                   name="att[<?= $st['student_id'] ?>][hadir]" 
                                                   class="form-control hadir-input" 
                                                   style="text-align: center; font-weight: 700; color: #0f766e; border-color: rgba(13, 148, 136, 0.4);" 
                                                   value="<?= $hadirVal ?>">
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="max-width: 90px; margin: 0 auto;">
                                            <input type="number" min="0" max="31" 
                                                   name="att[<?= $st['student_id'] ?>][sakit]" 
                                                   class="form-control" 
                                                   style="text-align: center; font-weight: 600;" 
                                                   value="<?= $sakitVal ?>">
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="max-width: 90px; margin: 0 auto;">
                                            <input type="number" min="0" max="31" 
                                                   name="att[<?= $st['student_id'] ?>][izin]" 
                                                   class="form-control" 
                                                   style="text-align: center; font-weight: 600;" 
                                                   value="<?= $izinVal ?>">
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="max-width: 90px; margin: 0 auto;">
                                            <input type="number" min="0" max="31" 
                                                   name="att[<?= $st['student_id'] ?>][alpha]" 
                                                   class="form-control" 
                                                   style="text-align: center; font-weight: 700; <?= $alphaVal > 0 ? 'color: #ef4444; border-color: rgba(239, 68, 68, 0.5); background: rgba(239, 68, 68, 0.05);' : '' ?>" 
                                                   value="<?= $alphaVal ?>">
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="padding: 16px 20px; border-top: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: flex-end; align-items: center; background: #fafafa;">
                    <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 600;">
                        <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Simpan Presensi Santri
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
function setAllHadir(days) {
    if (confirm('Set kolom Hadir menjadi ' + days + ' hari untuk semua santri?')) {
        const inputs = document.querySelectorAll('.hadir-input');
        inputs.forEach(inp => {
            inp.value = days;
        });
    }
}
</script>
