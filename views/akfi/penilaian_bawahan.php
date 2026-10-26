<?php
// views/akfi/penilaian_bawahan.php
use Core\SecurityHelper;
use Core\Auth;

$totalStaff = count($assessments);
$countDiajukan = 0;
$countSelesai = 0;
$countDraft = 0;

foreach ($assessments as $a) {
    if ($a['status'] === 'Diajukan') $countDiajukan++;
    elseif ($a['status'] === 'Disetujui_Atasan' || $a['status'] === 'Final') $countSelesai++;
    else $countDraft++;
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><?= SecurityHelper::escape($pageTitle) ?></h1>
        <div class="page-subtitle">
            Evaluasi capaian sasaran kerja, instrumen indikator, dan rubrik perilaku Islami bawahan untuk tahun ajaran aktif.
        </div>
    </div>
</div>

<!-- 4 KARTU STATISTIK PENILAIAN BAWAHAN -->
<div class="grid-cols-4" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <div class="stat-value"><?= number_format($totalStaff) ?> <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">Pegawai</span></div>
            <div class="stat-label">Total Bawahan</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber">
            <i class="fa-solid fa-clock"></i>
        </div>
        <div>
            <div class="stat-value" style="color: var(--warning);"><?= number_format($countDiajukan) ?> <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">Pegawai</span></div>
            <div class="stat-label">Siap / Menunggu Dinilai</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon emerald">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div>
            <div class="stat-value" style="color: var(--accent);"><?= number_format($countSelesai) ?> <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">Pegawai</span></div>
            <div class="stat-label">Selesai Dinilai</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon rose">
            <i class="fa-solid fa-file-pen"></i>
        </div>
        <div>
            <div class="stat-value"><?= number_format($countDraft) ?> <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">Pegawai</span></div>
            <div class="stat-label">Draft / Belum Diajukan</div>
        </div>
    </div>
</div>

<!-- DAFTAR PEGAWAI BAWAHAN -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fa-solid fa-user-check" style="color: var(--primary-light); margin-right: 8px;"></i>
            Daftar Penilaian Kinerja AKFI Pegawai
        </h3>
        <span class="badge badge-secondary"><?= $totalStaff ?> Pegawai Terdaftar</span>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">No</th>
                    <th>Pegawai</th>
                    <th>Unit & Posisi</th>
                    <th style="text-align: center;">Status Pengajuan</th>
                    <th style="text-align: center;">Kinerja</th>
                    <th style="text-align: center;">Sikap</th>
                    <th style="text-align: center;">Absensi</th>
                    <th style="text-align: center;">Total & Predikat</th>
                    <th style="width: 140px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($assessments)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px 20px; color: var(--text-muted);">
                            <i class="fa-solid fa-users-slash" style="font-size: 32px; margin-bottom: 10px; display: block; opacity: 0.4;"></i>
                            Belum ada pegawai bawahan yang terdaftar untuk dinilai pada tahun ajaran ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($assessments as $idx => $a): ?>
                        <tr>
                            <td style="text-align: center; color: var(--text-muted);"><?= $idx + 1 ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 36px; height: 36px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0;">
                                        <?= strtoupper(mb_substr($a['nama'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; color: var(--text-main);">
                                            <?= SecurityHelper::escape($a['nama']) ?>
                                        </div>
                                        <div style="font-size: 11px; color: var(--text-muted);">
                                            NIPY: <?= SecurityHelper::escape($a['nipy'] ?? '-') ?> &bull; <?= SecurityHelper::escape($a['position'] ?? 'Guru/Staf') ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 500;"><?= SecurityHelper::escape($a['unit_name']) ?></div>
                                <span class="badge badge-secondary" style="font-size: 10px; margin-top: 2px;">
                                    <?= SecurityHelper::escape($a['position_name']) ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($a['status'] === 'Final'): ?>
                                    <span class="badge badge-success">
                                        <i class="fa-solid fa-check-double" style="margin-right: 4px;"></i> Final HR
                                    </span>
                                <?php elseif ($a['status'] === 'Disetujui_Atasan'): ?>
                                    <span class="badge badge-info">
                                        <i class="fa-solid fa-check" style="margin-right: 4px;"></i> Disetujui
                                    </span>
                                <?php elseif ($a['status'] === 'Diajukan'): ?>
                                    <span class="badge badge-warning">
                                        <i class="fa-solid fa-bell" style="margin-right: 4px;"></i> Siap Dinilai
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Draft Mandiri</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center; font-family: monospace; font-size: 13px; font-weight: 600;">
                                <?= number_format((float)($a['kinerja_score'] ?? 0), 1) ?>
                            </td>
                            <td style="text-align: center; font-family: monospace; font-size: 13px; font-weight: 600;">
                                <?= number_format((float)($a['sikap_score'] ?? 0), 1) ?>
                            </td>
                            <td style="text-align: center; font-family: monospace; font-size: 13px; font-weight: 600;">
                                <?= number_format((float)($a['absensi_score'] ?? 0), 1) ?>
                            </td>
                            <td style="text-align: center;">
                                <div style="font-weight: 700; font-size: 14px; color: var(--primary);">
                                    <?= number_format((float)($a['total_score'] ?? 0), 2) ?>
                                </div>
                                <?php if (!empty($a['letter_grade'])): ?>
                                    <?php 
                                    $grade = $a['letter_grade'];
                                    $badgeClass = ($grade === 'A') ? 'badge-success' : (($grade === 'B') ? 'badge-info' : (($grade === 'C') ? 'badge-warning' : 'badge-danger'));
                                    ?>
                                    <span class="badge <?= $badgeClass ?>" style="font-size: 10px; margin-top: 2px;">
                                        Predikat <?= $grade ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                                    <a href="<?= Auth::baseUrl() ?>/akfi/assess/<?= $a['id'] ?>" class="btn btn-sm btn-primary" title="Buka Formulir Penilaian">
                                        <i class="fa-solid fa-pen-to-square"></i> Nilai
                                    </a>
                                    <?php if ($a['status'] === 'Disetujui_Atasan' || $a['status'] === 'Final'): ?>
                                        <a href="<?= Auth::baseUrl() ?>/akfi/print-rapor/<?= $a['id'] ?>" target="_blank" class="btn btn-sm btn-outline" title="Cetak Rapor AKFI">
                                            <i class="fa-solid fa-print"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
