<?php
// views/akfi/pribadi.php
use Core\SecurityHelper;
use Core\Auth;
use Core\CSRF;

$isDraft = ($assessment['status'] === 'Draft');
$isSubmitted = ($assessment['status'] === 'Diajukan');
$isApproved = ($assessment['status'] === 'Disetujui_Atasan');
$isFinal = ($assessment['status'] === 'Final');
$grade = $assessment['letter_grade'] ?? '-';
$badgeGrade = match($grade) {
    'A' => 'badge-success',
    'B' => 'badge-info',
    'C' => 'badge-warning',
    default => 'badge-danger'
};
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
    <div>
        <h1 class="page-title" style="display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-file-signature text-accent" style="color: var(--accent);"></i>
            Rapor AKFI Saya
        </h1>
        <p class="page-subtitle" style="color: var(--text-muted); font-size: 13px; margin-top: 4px;">
            Rapor Angka Ketercapaian Fungsional Individu & Penilaian Sasaran Kerja Pegawai
        </p>
    </div>
    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
        <?php if (!empty($crossUnitSummary)): ?>
            <a href="<?= Auth::baseUrl() ?>/akfi/print-gabungan/<?= (int)$employee['id'] ?>" target="_blank" class="btn btn-outline" style="border-color: #0f766e; color: #0f766e; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-layer-group"></i> Cetak Rapor Gabungan (Rata-Rata PDF)
            </a>
        <?php endif; ?>
        <?php if ($isApproved || $isFinal): ?>
            <a href="<?= Auth::baseUrl() ?>/akfi/print-rapor/<?= $assessment['id'] ?>" target="_blank" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-print"></i> Cetak Rapor Unit Ini (PDF)
            </a>
        <?php else: ?>
            <span class="badge badge-secondary" style="padding: 8px 14px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-lock"></i> Rapor Unit Ini Belum Disahkan
            </span>
        <?php endif; ?>
    </div>
</div>

<!-- PROFILE BANNER CARD -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-body" style="padding: 20px 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
            <div style="display: flex; align-items: center; gap: 18px;">
                <div style="width: 56px; height: 56px; border-radius: 14px; background: linear-gradient(135deg, var(--primary), var(--accent)); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700; flex-shrink: 0; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                    <?= strtoupper(mb_substr($employee['nama'] ?? 'P', 0, 1)) ?>
                </div>
                <div>
                    <h2 style="font-size: 18px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                        <?= SecurityHelper::escape($employee['nama'] ?? 'Pegawai') ?>
                    </h2>
                    <div style="display: flex; flex-wrap: wrap; gap: 12px; font-size: 12.5px; color: var(--text-muted); align-items: center;">
                        <span><i class="fa-solid fa-id-badge" style="margin-right: 4px; color: var(--primary);"></i> NIPY: <strong><?= SecurityHelper::escape($employee['nipy'] ?? '-') ?></strong></span>
                        <span>&bull;</span>
                        <span><i class="fa-solid fa-school" style="margin-right: 4px; color: var(--accent);"></i> Satuan: <strong><?= SecurityHelper::escape($assessment['unit_name'] ?? $employee['unit_name'] ?? '-') ?></strong></span>
                        <span>&bull;</span>
                        <span><i class="fa-solid fa-briefcase" style="margin-right: 4px; color: #6366f1;"></i> Posisi: <strong><?= SecurityHelper::escape($employee['position'] ?? '-') ?></strong></span>
                        <span>&bull;</span>
                        <span><i class="fa-solid fa-calendar-days" style="margin-right: 4px; color: #d97706;"></i> TA: <strong><?= SecurityHelper::escape($assessment['academic_year_name'] ?? 'Tahun Aktif') ?></strong></span>
                    </div>
                </div>
            </div>

            <div style="text-align: right;">
                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 4px; font-weight: 600;">
                    Template Penilaian
                </div>
                <span class="badge badge-info" style="font-size: 12.5px; padding: 6px 12px;">
                    <?= SecurityHelper::escape($assessment['position_name']) ?>
                </span>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($crossUnitSummary) || count($assignedUnits ?? []) > 1): ?>
    <div class="card" style="margin-bottom: 20px; border: 1px solid #99f6e4; background: linear-gradient(135deg, #f0fdfa 0%, #ffffff 100%);">
        <div class="card-body" style="padding: 16px 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <i class="fa-solid fa-diagram-project" style="color: #0f766e;"></i>
                    <strong style="color: #115e59; font-size: 14px;">Rapor AKFI Lintas Unit (Nilai Akhir = Rata-Rata Live Seluruh Unit):</strong>
                    <?php foreach ($assignedUnits ?? [] as $au):
                        $isTabActive = ((int)$au['unit_id'] === (int)$assessment['unit_id']);
                    ?>
                        <a href="<?= Auth::baseUrl() ?>/akfi/pribadi?unit_id=<?= (int)$au['unit_id'] ?>"
                           class="btn btn-sm <?= $isTabActive ? 'btn-primary' : 'btn-outline' ?>"
                           style="<?= $isTabActive ? 'background: #0f766e; border-color: #0f766e;' : 'border-color: #99f6e4; color: #0f766e; background: #fff;' ?> font-weight: 600;">
                            <i class="fa-solid fa-school" style="margin-right: 4px;"></i>
                            <?= SecurityHelper::escape($au['unit_code'] ?: $au['unit_name']) ?>
                            <?= !empty($au['is_homebase']) ? ' (Induk)' : ' (Lintas Unit)' ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php if (!empty($crossUnitSummary)): ?>
                    <div style="display: flex; align-items: center; gap: 10px; background: #ecfdf5; border: 1px solid #6ee7b7; border-radius: 8px; padding: 6px 12px;">
                        <span style="font-size: 12px; color: #065f46; font-weight: 600;">Rata-Rata Live (<?= (int)$crossUnitSummary['unit_count'] ?> Unit):</span>
                        <strong style="font-size: 17px; color: #047857;"><?= number_format((float)$crossUnitSummary['avg_total'], 2) ?></strong>
                        <span class="badge badge-success" style="font-size: 12px;"><?= SecurityHelper::escape($crossUnitSummary['avg_grade']) ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($crossUnitSummary['assessments'])): ?>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <?php foreach ($crossUnitSummary['assessments'] as $cu): ?>
                        <a href="<?= Auth::baseUrl() ?>/akfi/pribadi?unit_id=<?= (int)$cu['unit_id'] ?>"
                           style="text-decoration: none; flex: 1; min-width: 210px; background: <?= (int)$cu['unit_id'] === (int)$assessment['unit_id'] ? '#ccfbf1' : '#fff' ?>; border: 1px solid <?= (int)$cu['unit_id'] === (int)$assessment['unit_id'] ? '#0d9488' : '#e2e8f0' ?>; border-radius: 8px; padding: 8px 12px; color: #1e293b;">
                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px;">
                                <strong><?= SecurityHelper::escape($cu['unit_name']) ?></strong>
                                <span class="badge <?= $cu['status'] === 'Final' ? 'badge-success' : ($cu['status'] === 'Disetujui_Atasan' ? 'badge-info' : 'badge-secondary') ?>" style="font-size: 10px;">
                                    <?= SecurityHelper::escape(str_replace('_', ' ', $cu['status'])) ?>
                                </span>
                            </div>
                            <div style="font-size: 11px; color: #475569; margin-top: 2px;">
                                <?= SecurityHelper::escape($cu['position_name']) ?> &bull; Skor: <strong><?= number_format((float)$cu['total_score'], 2) ?> (<?= SecurityHelper::escape($cu['letter_grade']) ?>)</strong>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php elseif (!empty($crossUnitAssignments)): ?>
    <div class="alert alert-info" style="margin-bottom: 20px; border-left: 4px solid #0284c7; font-size: 13px; line-height: 1.55;">
        <div style="display: flex; align-items: flex-start; gap: 10px;">
            <i class="fa-solid fa-diagram-project" style="margin-top: 3px; color: #0284c7;"></i>
            <div>
                <strong>Penugasan Lintas Unit Aktif:</strong>
                <?php foreach ($crossUnitAssignments as $ca): ?>
                    <span class="badge badge-info" style="margin: 2px 4px 2px 0;">
                        <?= SecurityHelper::escape($ca['role_display']) ?> — <?= SecurityHelper::escape($ca['unit_code'] ?: $ca['unit_name']) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- 5 STAT CARDS (STATS-GRID) -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <!-- Stat 1: Status Alur -->
    <div class="stat-card">
        <div class="stat-icon" style="background: #e0f2fe; color: #0284c7;">
            <i class="fa-solid fa-clipboard-check"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value" style="font-size: 15px;">
                <?php if ($isFinal): ?>
                    <span class="badge badge-success"><i class="fa-solid fa-certificate"></i> Sah HR</span>
                <?php elseif ($isApproved): ?>
                    <span class="badge badge-info"><i class="fa-solid fa-circle-check"></i> Disetujui</span>
                <?php elseif ($isSubmitted): ?>
                    <span class="badge badge-warning"><i class="fa-solid fa-hourglass-half"></i> Review</span>
                <?php else: ?>
                    <span class="badge badge-secondary"><i class="fa-solid fa-file-pen"></i> Draft</span>
                <?php endif; ?>
            </div>
            <div class="stat-label">Status Penilaian</div>
        </div>
    </div>

    <!-- Stat 2: Skor Kinerja -->
    <div class="stat-card">
        <div class="stat-icon" style="background: #dcfce7; color: #16a34a;">
            <i class="fa-solid fa-chart-line"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value">
                <?= number_format((float)($assessment['kinerja_score'] ?? 0), 1) ?>
                <span style="font-size: 12px; font-weight: 500; color: var(--text-muted);">/100</span>
            </div>
            <div class="stat-label">Kinerja (Bobot <?= $assessment['bobot_kinerja'] ?>%)</div>
        </div>
    </div>

    <!-- Stat 3: Skor Sikap -->
    <div class="stat-card">
        <div class="stat-icon" style="background: #fef3c7; color: #d97706;">
            <i class="fa-solid fa-heart-pulse"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value">
                <?= number_format((float)($assessment['sikap_score'] ?? 0), 1) ?>
                <span style="font-size: 12px; font-weight: 500; color: var(--text-muted);">/100</span>
            </div>
            <div class="stat-label">Sikap (Bobot <?= $assessment['bobot_sikap'] ?>%)</div>
        </div>
    </div>

    <!-- Stat 4: Skor Presensi -->
    <div class="stat-card">
        <div class="stat-icon" style="background: #e0e7ff; color: #4f46e5;">
            <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value">
                <?= number_format((float)($assessment['absensi_score'] ?? 0), 1) ?>
                <span style="font-size: 12px; font-weight: 500; color: var(--text-muted);">/100</span>
            </div>
            <div class="stat-label">Absensi (Bobot <?= $assessment['bobot_absensi'] ?>%)</div>
        </div>
    </div>

    <!-- Stat 5: Skor Total Akhir -->
    <div class="stat-card" style="border-left: 4px solid var(--accent); background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);">
        <div class="stat-icon" style="background: #ccfbf1; color: #0f766e;">
            <i class="fa-solid fa-award"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value" style="display: flex; align-items: center; gap: 8px;">
                <?= number_format((float)($assessment['total_score'] ?? 0), 1) ?>
                <span class="badge <?= $badgeGrade ?>" style="font-size: 13px; padding: 2px 8px;"><?= htmlspecialchars($grade) ?></span>
            </div>
            <div class="stat-label">Total Skor & Predikat</div>
        </div>
    </div>
</div>

<!-- WORKFLOW STATUS GUIDANCE ALERT -->
<?php if ($isDraft): ?>
    <div class="alert alert-info" style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 24px;">
        <i class="fa-solid fa-circle-info" style="font-size: 18px; margin-top: 2px;"></i>
        <div>
            <strong>Tahap Pengisian Penilaian Mandiri:</strong>
            <p style="margin: 4px 0 0 0; font-size: 13px; line-height: 1.4;">
                Silakan lengkapi perkiraan capaian kinerja mandiri Anda pada kolom <strong>Skor Mandiri</strong> di bawah ini, kemudian klik tombol <strong>"Ajukan Penilaian Mandiri ke Atasan"</strong> untuk dikaji dan dinilai oleh Kepala Sekolah.
            </p>
        </div>
    </div>
<?php elseif ($isSubmitted): ?>
    <div class="alert alert-warning" style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 24px;">
        <i class="fa-solid fa-hourglass-half" style="font-size: 18px; margin-top: 2px;"></i>
        <div>
            <strong>Menunggu Verifikasi & Penilaian Atasan:</strong>
            <p style="margin: 4px 0 0 0; font-size: 13px; line-height: 1.4;">
                Penilaian mandiri Anda telah dikirimkan ke atasan langsung pada <strong><?= !empty($assessment['self_submitted_at']) ? date('d/m/Y H:i', strtotime($assessment['self_submitted_at'])) : date('d/m/Y') ?></strong>. Kepala Sekolah sedang melakukan observasi dan penilaian skor akhir.
            </p>
        </div>
    </div>
<?php elseif ($isApproved): ?>
    <div class="alert alert-info" style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 24px;">
        <i class="fa-solid fa-circle-check" style="font-size: 18px; margin-top: 2px;"></i>
        <div>
            <strong>Telah Disetujui Atasan Langsung:</strong>
            <p style="margin: 4px 0 0 0; font-size: 13px; line-height: 1.4;">
                Penilaian kinerja Anda telah selesai dinilai dan ditandatangani oleh <strong><?= SecurityHelper::escape($assessment['superior_name'] ?? 'Kepala Sekolah') ?></strong>. Berkas saat ini sedang dalam antrean pengesahan akhir oleh Direktur Pendidikan Yayasan.
            </p>
        </div>
    </div>
<?php elseif ($isFinal): ?>
    <div class="alert alert-success" style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 24px;">
        <i class="fa-solid fa-shield-halved" style="font-size: 18px; margin-top: 2px;"></i>
        <div>
            <strong>Penilaian Sah & Final:</strong>
            <p style="margin: 4px 0 0 0; font-size: 13px; line-height: 1.4;">
                Rapor Kinerja AKFI Anda telah disahkan secara resmi oleh Direktur Pendidikan Yayasan (<strong><?= SecurityHelper::escape($assessment['hr_name'] ?? 'Direktur Pendidikan') ?></strong>). Anda dapat mengunduh berkas fisik PDF resmi kapan saja melalui tombol cetak di atas.
            </p>
        </div>
    </div>
<?php endif; ?>

<!-- INSTRUMEN INDIKATOR CARD -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title">
            <i class="fa-solid fa-list-check" style="color: var(--accent); margin-right: 8px;"></i>
            Instrumen Indikator Sasaran Kerja (<?= SecurityHelper::escape($assessment['position_name']) ?>)
        </h3>
        <span class="badge badge-secondary" style="font-size: 12px;">
            Total <?= count($items) ?> Indikator
        </span>
    </div>

    <div class="card-body">
        <form action="<?= Auth::baseUrl() ?>/akfi/self-submit" method="POST">
            <?= CSRF::field() ?>
            <input type="hidden" name="assessment_id" value="<?= (int)$assessment['id'] ?>">

            <div class="table-responsive">
                <table class="table" style="vertical-align: middle;">
                    <thead>
                        <tr>
                            <th style="width: 48px; text-align: center;">No</th>
                            <th style="width: 140px;">Kategori</th>
                            <th>Sasaran Kerja & Indikator Kinerja</th>
                            <th style="width: 120px; text-align: center;">Target</th>
                            <th style="width: 80px; text-align: center;">Bobot</th>
                            <th style="width: 150px; text-align: center;">Skor Mandiri</th>
                            <?php if (!$isDraft): ?>
                                <th style="width: 150px; text-align: center;">Skor Atasan</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $curSec = '';
                        $rowCounter = 1;
                        foreach ($items as $it): 
                            $itData = $savedItems[$it['id']] ?? [];
                            $hasAuto = !empty($it['auto_source']) && isset($autoData[$it['auto_source']]);
                            $defaultSelf = $hasAuto ? $autoData[$it['auto_source']] : (($it['section'] === 'C' || $it['category'] === 'Absensi') ? 0 : $it['target_val']);
                            $selfScore = $itData['self_score'] ?? $defaultSelf;
                            $supScore = $itData['superior_score'] ?? '-';

                            if ($curSec !== $it['section']):
                                $curSec = $it['section'];
                                $secTitle = match($curSec) {
                                    'A' => '<i class="fa-solid fa-bullseye" style="margin-right: 6px;"></i> BAGIAN A: TARGET PROGRAM & TUGAS POKOK',
                                    'B' => '<i class="fa-solid fa-puzzle-piece" style="margin-right: 6px;"></i> BAGIAN B: TUGAS TAMBAHAN & PENDUKUNG',
                                    default => '<i class="fa-solid fa-folder-tree" style="margin-right: 6px;"></i> BAGIAN C: ADMINISTRASI & KEGIATAN'
                                };
                        ?>
                            <tr style="background: #f8fafc; font-weight: 700; color: var(--primary-dark); font-size: 12px; letter-spacing: 0.5px;">
                                <td colspan="<?= $isDraft ? 6 : 7 ?>" style="padding: 10px 16px; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
                                    <?= $secTitle ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                            <tr>
                                <td style="text-align: center; color: var(--text-muted); font-weight: 600;"><?= $rowCounter++ ?></td>
                                <td>
                                    <span class="badge badge-secondary" style="font-size: 11px;">
                                        <?= SecurityHelper::escape($it['category']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--text-main); font-size: 13.5px; margin-bottom: 3px;">
                                        <?= SecurityHelper::escape($it['item_name']) ?>
                                    </div>
                                    <?php if ($hasAuto): ?>
                                        <div style="margin-bottom: 4px;">
                                            <span class="badge badge-info" style="font-size: 10.5px; padding: 2px 7px;">
                                                <i class="fa-solid fa-robot" style="margin-right: 4px;"></i> Data Riil Sistem: <strong><?= $autoData[$it['auto_source']] ?> <?= htmlspecialchars($it['unit_type']) ?></strong>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($it['description'])): ?>
                                        <div style="font-size: 12px; color: var(--text-muted); line-height: 1.4;">
                                            <?= SecurityHelper::escape($it['description']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <span style="font-family: monospace; font-weight: 700; font-size: 13px;">
                                        <?= $it['target_val'] ?>
                                    </span>
                                    <span style="font-size: 11px; color: var(--text-muted); margin-left: 2px;">
                                        <?= SecurityHelper::escape($it['unit_type']) ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge badge-secondary" style="font-weight: 600;">
                                        <?= $it['weight'] ?>%
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($isDraft): ?>
                                        <?php if ($it['section'] === 'A'): ?>
                                            <div style="display: inline-flex; align-items: center; gap: 6px;">
                                                <input type="number" step="0.1" name="self_score[<?= $it['id'] ?>]" 
                                                       class="form-control" 
                                                       style="width: 85px; text-align: center; font-weight: 700; font-family: monospace; padding: 6px;" 
                                                       value="<?= htmlspecialchars((string)$selfScore) ?>" required>
                                                <span style="font-size: 11px; color: var(--text-muted);"><?= SecurityHelper::escape($it['unit_type']) ?></span>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge" style="background: #f8fafc; color: #64748b; font-size: 11px; padding: 4px 8px; border: 1px dashed #cbd5e1;">
                                                <i class="fa-solid fa-user-tie" style="margin-right: 4px;"></i> Dinilai Murni oleh Atasan
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php if ($it['section'] === 'A'): ?>
                                            <span style="font-family: monospace; font-weight: 700; font-size: 14px; color: var(--text-main);">
                                                <?= htmlspecialchars((string)$selfScore) ?>
                                            </span>
                                            <span style="font-size: 11px; color: var(--text-muted); margin-left: 2px;">
                                                <?= SecurityHelper::escape($it['unit_type']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted); font-size: 12px;">-</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <?php if (!$isDraft): ?>
                                    <td style="text-align: center;">
                                        <?php if ($supScore !== '-'): ?>
                                            <span style="font-family: monospace; font-weight: 700; font-size: 14px; color: var(--primary);">
                                                <?= htmlspecialchars((string)$supScore) ?>
                                            </span>
                                            <span style="font-size: 11px; color: var(--text-muted); margin-left: 2px;">
                                                <?= SecurityHelper::escape($it['unit_type']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary" style="font-size: 11px;">Belum Dinilai</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($isDraft): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border-color);">
                    <div style="font-size: 13px; color: var(--text-muted);">
                        <i class="fa-solid fa-circle-info" style="color: var(--primary); margin-right: 4px;"></i>
                        Pengajuan mandiri difokuskan pada <strong>Bagian A (Target Program & Tugas Pokok)</strong>. Bagian B (Sikap & Perilaku) dan Bagian C (Presensi & Kedisiplinan) dinilai secara murni oleh atasan langsung.
                    </div>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 600;">
                        <i class="fa-solid fa-paper-plane"></i> Ajukan Penilaian Mandiri ke Atasan
                    </button>
                </div>
            <?php endif; ?>
        </form>

        <?php if (!empty($assessment['superior_notes'])): ?>
            <div style="margin-top: 24px; background: #f8fafc; border: 1px solid var(--border-color); border-left: 4px solid var(--primary); border-radius: 8px; padding: 16px 20px;">
                <div style="font-weight: 700; color: var(--primary); font-size: 13px; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-comment-dots"></i> Catatan & Rekomendasi Atasan Langsung:
                </div>
                <div style="font-size: 13px; color: var(--text-main); font-style: italic; line-height: 1.5;">
                    "<?= nl2br(SecurityHelper::escape($assessment['superior_notes'])) ?>"
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
