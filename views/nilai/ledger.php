<?php
// views/nilai/ledger.php
use Core\SecurityHelper;
use Core\Auth;

$baseUrl = Auth::baseUrl();
$months = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$totalSiswa = count($students);
$allAvgs = array_column(array_filter($students, fn($s) => (float)$s['avg'] > 0), 'avg');
$rataKelas = !empty($allAvgs) ? round(array_sum($allAvgs) / count($allAvgs), 1) : 0;
$nilaiTertinggi = !empty($allAvgs) ? max($allAvgs) : 0;
$nilaiTerendah = !empty($allAvgs) ? min($allAvgs) : 0;
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Ledger Rekapitulasi Nilai & Rapor Kelas</h1>
        <div class="page-subtitle">Rekap matriks capaian akademik bulanan seluruh mata pelajaran, kalkulasi nilai rata-rata, dan cetak rapor santri.</div>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <?php if (!empty($classes) && $selectedClassId > 0): ?>
            <a href="<?= $baseUrl ?>/nilai/ledger/export-xlsx?class_id=<?= $selectedClassId ?>&month_num=<?= $selectedMonth ?>" class="btn btn-outline" style="color: #10b981; border-color: rgba(16, 185, 129, 0.4);">
                <i class="fa-solid fa-file-excel"></i> Export Excel
            </a>
            <a href="<?= $baseUrl ?>/nilai/ledger/export-pdf?class_id=<?= $selectedClassId ?>&month_num=<?= $selectedMonth ?>" target="_blank" class="btn btn-outline" style="color: #ef4444; border-color: rgba(239, 68, 68, 0.4);">
                <i class="fa-solid fa-file-pdf"></i> Cetak PDF (Landscape)
            </a>
        <?php endif; ?>
        <?php if (Auth::hasAnyRole(['guru_mapel', 'guru_mapel_kbtk', 'guru_tahta', 'guru_tk', 'guru_diniyah', 'waka_kurikulum', 'kepala_sekolah'])): ?>
            <a href="<?= $baseUrl ?>/nilai/bulanan?class_id=<?= $selectedClassId ?>&month_num=<?= $selectedMonth ?>" class="btn btn-primary">
                <i class="fa-solid fa-pen-to-square"></i> Input Nilai
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($classes)): ?>
    <div class="alert alert-warning" style="margin-bottom: 24px; display: flex; align-items: center; gap: 12px; padding: 14px 18px; border-radius: 8px; background: #fffbeb; border: 1px solid #fde68a; color: #92400e;">
        <i class="fa-solid fa-circle-exclamation" style="font-size: 1.25rem; color: #d97706;"></i>
        <div>
            <strong>Penugasan Belum Ditemukan:</strong> Anda belum ditugaskan sebagai Wali Kelas pada rombongan belajar / kelas manapun untuk tahun ajaran aktif ini. Silakan hubungi Operator Sekolah untuk penempatan wali kelas.
        </div>
    </div>
<?php endif; ?>

<!-- Filter Bar -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 14px 20px;">
        <h3 class="card-title" style="display: flex; align-items: center; gap: 8px; font-size: 0.95rem; margin: 0;">
            <i class="fa-solid fa-filter" style="color: #0d9488;"></i> Pilih Kelas & Bulan Rekapitulasi
        </h3>
    </div>
    <div class="card-body" style="padding: 18px 20px;">
        <form method="GET" action="<?= $baseUrl ?>/nilai/ledger" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)) auto; gap: 16px; align-items: end;">
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Pilih Kelas</label>
                <select name="class_id" class="form-control" onchange="this.form.submit()" <?= empty($classes) ? 'disabled' : '' ?>>
                    <?php if (empty($classes)): ?>
                        <option value="">(Belum ada kelas yang diampu)</option>
                    <?php else: ?>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $c['id'] == $selectedClassId ? 'selected' : '' ?>>
                                <?= SecurityHelper::escape($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Bulan Rekapitulasi</label>
                <select name="month_num" class="form-control" onchange="this.form.submit()">
                    <?php foreach ($months as $num => $nm): ?>
                        <option value="<?= $num ?>" <?= $num == $selectedMonth ? 'selected' : '' ?>><?= $nm ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <button type="submit" class="btn btn-primary" style="height: 42px; padding: 0 20px;">
                    <i class="fa-solid fa-rotate"></i> Muat Ledger
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 12-Month Visual Timeline Tracker -->
<?php if (!empty($classes) && $selectedClassId > 0): ?>
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header" style="padding: 14px 20px; border-bottom: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title" style="margin: 0; font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-calendar-check" style="color: #0d9488;"></i> Timeline Rekapitulasi Nilai 12 Bulan (Kelas <?= SecurityHelper::escape($className ?? '') ?>)
        </h3>
        <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Klik pada bulan untuk membuka ledger</span>
    </div>
    <div class="card-body" style="padding: 16px 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(85px, 1fr)); gap: 10px;">
            <?php foreach ($months as $mNum => $mName): 
                $isCurrent = ($mNum === (int)$selectedMonth);
                $isFilled = !empty($monthStatus[$mNum]);

                $bg = '#f8fafc';
                $border = '#e2e8f0';
                $textColor = '#64748b';
                $icon = 'fa-regular fa-circle';
                $statusText = 'Belum Ada';

                if ($isFilled) {
                    $bg = '#f0fdf4';
                    $border = '#bbf7d0';
                    $textColor = '#166534';
                    $icon = 'fa-solid fa-circle-check';
                    $statusText = 'Ada Nilai';
                }

                if ($isCurrent) {
                    $border = '#0d9488';
                    $bg = $isFilled ? '#ccfbf1' : '#f0fdfa';
                }
            ?>
                <a href="<?= $baseUrl ?>/nilai/ledger?class_id=<?= $selectedClassId ?>&month_num=<?= $mNum ?>" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 10px 6px; border-radius: 8px; border: 2px solid <?= $border ?>; background: <?= $bg ?>; transition: all 0.2s ease; cursor: pointer; text-align: center; position: relative;">
                    <?php if ($isCurrent): ?>
                        <span style="position: absolute; top: -8px; font-size: 0.65rem; background: #0d9488; color: #fff; padding: 1px 6px; border-radius: 999px; font-weight: 600;">Aktif</span>
                    <?php endif; ?>
                    <div style="font-weight: 700; font-size: 0.875rem; color: <?= $isCurrent ? '#0d9488' : '#1e293b' ?>; margin-top: <?= $isCurrent ? '4px' : '0' ?>;">
                        <?= substr($mName, 0, 3) ?>
                    </div>
                    <div style="display: flex; align-items: center; gap: 4px; font-size: 0.72rem; color: <?= $textColor ?>; margin-top: 4px; font-weight: 500;">
                        <i class="<?= $icon ?>" style="font-size: 0.75rem;"></i> <?= $statusText ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>
<!-- Stats Ringkasan Nilai Kelas -->
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
        <div class="stat-icon" style="background: rgba(99, 102, 241, 0.12); color: #6366f1;">
            <i class="fa-solid fa-chart-line"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $rataKelas > 0 ? $rataKelas : '-' ?></div>
            <div class="stat-label">Rata-rata Kelas</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
            <i class="fa-solid fa-arrow-trend-up"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $nilaiTertinggi > 0 ? $nilaiTertinggi : '-' ?></div>
            <div class="stat-label">Nilai Tertinggi</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b;">
            <i class="fa-solid fa-arrow-trend-down"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $nilaiTerendah > 0 ? $nilaiTerendah : '-' ?></div>
            <div class="stat-label">Nilai Terendah</div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Tabel Ledger Rekap Nilai -->
<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div>
            <h3 class="card-title" style="display: flex; align-items: center; gap: 8px; font-size: 1rem; margin: 0;">
                <i class="fa-solid fa-table" style="color: #0d9488;"></i> Matriks Nilai Santri - Kelas <?= SecurityHelper::escape($className) ?> (<?= $months[$selectedMonth] ?>)
            </h3>
        </div>
        <span class="badge badge-secondary"><?= count($subjects) ?> Mata Pelajaran</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div style="overflow-x: auto;">
            <table class="table" style="text-align: center;">
                <thead>
                    <tr style="background: #f8fafc;">
                        <th style="width: 40px; text-align: center;" rowspan="2">No</th>
                        <th style="width: 110px; text-align: left;" rowspan="2">NIS</th>
                        <th style="text-align: left; min-width: 180px;" rowspan="2">Nama Santri</th>
                        <th colspan="<?= max(1, count($subjects)) ?>" style="text-align: center; border-bottom: 1px solid var(--border-color, #e2e8f0);">Mata Pelajaran</th>
                        <th style="width: 85px; text-align: center;" rowspan="2">Total</th>
                        <th style="width: 85px; text-align: center;" rowspan="2">Rata-rata</th>
                        <th style="width: 130px; text-align: center;" rowspan="2">Cetak Rapor</th>
                    </tr>
                    <tr style="background: #f1f5f9;">
                        <?php if (empty($subjects)): ?>
                            <th style="font-weight: normal; color: var(--text-muted, #94a3b8);">-</th>
                        <?php else: ?>
                            <?php foreach ($subjects as $sub): ?>
                                <th title="<?= SecurityHelper::escape($sub['name']) ?>" style="font-size: 0.75rem; padding: 6px 4px; white-space: nowrap;">
                                    <code><?= SecurityHelper::escape($sub['code']) ?></code>
                                </th>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="<?= count($subjects) + 6 ?>" style="text-align: center; padding: 48px 20px; color: var(--text-muted, #64748b);">
                                <i class="fa-solid fa-folder-open" style="font-size: 2.5rem; margin-bottom: 12px; display: block; opacity: 0.5;"></i>
                                Belum ada data santri aktif pada kelas ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $idx => $st): ?>
                            <tr>
                                <td style="text-align: center;"><?= $idx + 1 ?></td>
                                <td style="text-align: left;"><code style="font-size: 0.85rem;"><?= SecurityHelper::escape($st['nis']) ?></code></td>
                                <td style="text-align: left;">
                                    <strong style="color: var(--text-main, #0f172a);"><?= SecurityHelper::escape($st['nama']) ?></strong>
                                </td>
                                <?php foreach ($subjects as $sub): 
                                    $sc = $st['grades'][$sub['id']] ?? null;
                                    $isRemed = ($sc !== null && $sc < 75);
                                ?>
                                    <td style="font-family: monospace; font-size: 0.85rem; <?= $isRemed ? 'color: #ef4444; font-weight: 700; background: rgba(239, 68, 68, 0.05);' : '' ?>">
                                        <?= $sc !== null ? $sc : '-' ?>
                                    </td>
                                <?php endforeach; ?>
                                <td style="font-weight: 700; color: #0f766e;"><?= $st['total'] ?></td>
                                <td style="font-weight: 700; color: <?= $st['avg'] >= 75 ? '#10b981' : ($st['avg'] > 0 ? '#f59e0b' : 'inherit') ?>;">
                                    <?= $st['avg'] ?>
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: inline-flex; gap: 4px; justify-content: center;">
                                        <a href="<?= $baseUrl ?>/nilai/rapor-bulanan/<?= $st['id'] ?>?month_num=<?= $selectedMonth ?>" target="_blank" class="btn btn-outline btn-sm" title="Cetak Rapor Bulanan PDF">
                                            <i class="fa-solid fa-print"></i> Rapor
                                        </a>
                                        <a href="<?= $baseUrl ?>/nilai/rapor-gabungan/<?= $st['id'] ?>?month_num=<?= $selectedMonth ?>" target="_blank" class="btn btn-outline btn-sm" style="color: #0d9488; border-color: rgba(13, 148, 136, 0.3);" title="Cetak Rapor Gabungan (Nilai & Presensi)">
                                            <i class="fa-solid fa-file-invoice"></i>
                                        </a>
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
