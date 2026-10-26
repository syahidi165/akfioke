<?php
// views/akfi/kanit_preview.php
use Core\SecurityHelper;
use Core\CSRF;
?>
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Pratinjau AKFI Kepala Satuan Pendidikan (Kanit)</h1>
        <div class="page-subtitle">Monitoring capaian indikator kinerja, administrasi sekolah, supervisi guru, dan sikap kepemimpinan seluruh Kepala Sekolah.</div>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="<?= $baseUrl ?>/akfi/penilaian-sikap-ks" class="btn btn-primary">
            <i class="fa-solid fa-user-pen"></i> Form Penilaian Sikap KS
        </a>
        <a href="<?= $baseUrl ?>/akfi/rekap-yayasan" class="btn btn-outline">
            <i class="fa-solid fa-chart-pie"></i> Rekap Yayasan
        </a>
    </div>
</div>

<!-- KPI Cards -->
<?php
$totalUnits = count($kanitList);
$totalScores = 0;
$countWithScores = 0;
$gradeACount = 0;
$gradeBCount = 0;

foreach ($kanitList as $k) {
    if (!empty($k['assessment']) && (float)$k['assessment']['total_score'] > 0) {
        $totalScores += (float)$k['assessment']['total_score'];
        $countWithScores++;
        if ($k['assessment']['letter_grade'] === 'A') $gradeACount++;
        if ($k['assessment']['letter_grade'] === 'B') $gradeBCount++;
    }
}
$avgScore = $countWithScores > 0 ? round($totalScores / $countWithScores, 1) : 0;
?>

<div class="row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(15, 118, 110, 0.1); color: var(--primary);">
            <i class="fa-solid fa-school"></i>
        </div>
        <div class="stat-value"><?= $totalUnits ?></div>
        <div class="stat-label">Satuan Pendidikan (Unit)</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(14, 165, 233, 0.1); color: #0284c7;">
            <i class="fa-solid fa-chart-line"></i>
        </div>
        <div class="stat-value"><?= $avgScore ?></div>
        <div class="stat-label">Rata-rata Skor AKFI KS</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(34, 197, 94, 0.1); color: #16a34a;">
            <i class="fa-solid fa-award"></i>
        </div>
        <div class="stat-value"><?= $gradeACount ?> Unit</div>
        <div class="stat-label">Predikat Amat Baik (A)</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #d97706;">
            <i class="fa-solid fa-medal"></i>
        </div>
        <div class="stat-value"><?= $gradeBCount ?> Unit</div>
        <div class="stat-label">Predikat Baik (B)</div>
    </div>
</div>

<!-- Table Preview KS -->
<div class="card">
    <div class="card-header" style="background: #fff; border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <h3 style="font-size: 16px; font-weight: 600; margin: 0;">Daftar Capaian & Penilaian AKFI Kepala Sekolah</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table" style="margin: 0;">
                <thead>
                    <tr>
                        <th>Unit Satuan</th>
                        <th>Kepala Satuan Pendidikan</th>
                        <th class="text-center">Admin KS (43 Slot)</th>
                        <th class="text-center">Avg Supervisi</th>
                        <th class="text-center">Presensi Unit</th>
                        <th class="text-center">Kinerja (60%)</th>
                        <th class="text-center">Sikap (30%)</th>
                        <th class="text-center">Absensi (10%)</th>
                        <th class="text-center">Total Skor</th>
                        <th class="text-center">Predikat</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="min-width: 140px;">Aksi Deputi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kanitList as $k): 
                        $u = $k['unit'];
                        $ks = $k['ks'];
                        $a = $k['assessment'];
                        $auto = $k['autoData'];
                    ?>
                        <tr>
                            <td>
                                <div><strong style="color: var(--primary);"><?= SecurityHelper::escape($u['name']) ?></strong></div>
                                <span class="badge badge-info" style="font-size: 10px;"><?= SecurityHelper::escape($u['level']) ?></span>
                            </td>
                            <td>
                                <?php if ($ks): ?>
                                    <div style="font-weight: 600;"><?= SecurityHelper::escape($ks['nama']) ?></div>
                                    <div style="font-size: 11px; color: var(--text-muted);">NIPY: <?= SecurityHelper::escape($ks['nipy']) ?></div>
                                <?php else: ?>
                                    <span class="text-muted" style="font-style: italic;">Belum ditugaskan</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($ks): ?>
                                    <span class="badge <?= ($auto['ks_admin_count'] ?? 0) >= 30 ? 'badge-success' : 'badge-warning' ?>">
                                        <?= (int)($auto['ks_admin_count'] ?? 0) ?> / 43
                                    </span>
                                <?php else: ?>-<?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?= isset($auto['avg_supervisi']) ? number_format((float)$auto['avg_supervisi'], 1) : '-' ?>
                            </td>
                            <td class="text-center">
                                <?= isset($auto['attendance_pct']) ? number_format((float)$auto['attendance_pct'], 1) . '%' : '-' ?>
                            </td>
                            <td class="text-center">
                                <strong><?= $a ? number_format((float)$a['kinerja_score'], 1) : '0.0' ?></strong>
                            </td>
                            <td class="text-center">
                                <strong><?= $a ? number_format((float)$a['sikap_score'], 1) : '0.0' ?></strong>
                            </td>
                            <td class="text-center">
                                <strong><?= $a ? number_format((float)$a['absensi_score'], 1) : '0.0' ?></strong>
                            </td>
                            <td class="text-center">
                                <span style="font-size: 14px; font-weight: 700; color: var(--primary);">
                                    <?= $a ? number_format((float)$a['total_score'], 2) : '0.00' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if ($a && !empty($a['letter_grade'])): ?>
                                    <?php 
                                        $badgeClass = match($a['letter_grade']) {
                                            'A' => 'badge-success',
                                            'B' => 'badge-info',
                                            'C' => 'badge-warning',
                                            default => 'badge-danger'
                                        };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>" style="font-size: 13px; font-weight: 700; padding: 4px 10px;">
                                        <?= $a['letter_grade'] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($a): ?>
                                    <?php
                                        $st = $a['status'];
                                        $stBadge = match($st) {
                                            'Final' => 'badge-success',
                                            'Disetujui_Atasan' => 'badge-primary',
                                            'Diajukan' => 'badge-warning',
                                            default => 'badge-secondary'
                                        };
                                    ?>
                                    <span class="badge <?= $stBadge ?>" style="font-size: 10px;">
                                        <?= str_replace('_', ' ', $st) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($a): ?>
                                    <div style="display: flex; gap: 4px; justify-content: center; flex-wrap: wrap;">
                                        <a href="<?= $baseUrl ?>/akfi/penilaian-sikap-ks?assessment_id=<?= $a['id'] ?>" class="btn btn-warning btn-sm" title="Nilai Sikap Kepemimpinan KS">
                                            <i class="fa-solid fa-user-pen"></i> Sikap
                                        </a>
                                        <a href="<?= $baseUrl ?>/akfi/assess/<?= $a['id'] ?>" class="btn btn-outline btn-sm" title="Form Penilaian Lengkap">
                                            <i class="fa-solid fa-pen-to-square"></i> Form
                                        </a>
                                        <a href="<?= $baseUrl ?>/akfi/print-rapor/<?= $a['id'] ?>" target="_blank" class="btn btn-outline btn-sm" title="Cetak Rapor A4">
                                            <i class="fa-solid fa-print"></i>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size: 11px;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
