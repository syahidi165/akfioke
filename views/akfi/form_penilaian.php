<?php
// views/akfi/form_penilaian.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$scLabels = [
    1 => 'Sangat Kurang',
    2 => 'Kurang',
    3 => 'Cukup',
    4 => 'Baik',
    5 => 'Sangat Baik'
];
$posCode = $assessment['position_code'] ?? '';
?>

<style>
.form-layout-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 24px;
    align-items: start;
}
@media (max-width: 1024px) {
    .form-layout-grid {
        grid-template-columns: 1fr;
    }
}
.section-header-row td {
    background: #f1f5f9 !important;
    color: #1e293b !important;
    font-weight: 700 !important;
    font-size: 12px !important;
    letter-spacing: 0.5px !important;
    padding: 10px 16px !important;
    border-top: 2px solid var(--border-color) !important;
    border-bottom: 1px solid var(--border-color) !important;
}
.rubric-item {
    padding: 14px 0;
    border-bottom: 1px solid var(--border-color);
}
.rubric-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
.rubric-radio-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 8px;
    margin-top: 8px;
}
@media (max-width: 640px) {
    .rubric-radio-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
.rubric-radio-label {
    cursor: pointer;
    margin-bottom: 0;
    position: relative;
    user-select: none;
}
.rubric-radio-label input[type="radio"] {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}
.rubric-radio-card {
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 8px 6px;
    text-align: center;
    background: #ffffff;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
}
.rubric-radio-label:hover .rubric-radio-card {
    border-color: var(--primary-light);
    background: #f8fafc;
}
.rubric-radio-label input[type="radio"]:checked + .rubric-radio-card {
    border-color: var(--primary);
    background: #eff6ff;
    color: var(--primary);
    font-weight: 600;
    box-shadow: 0 0 0 1px var(--primary);
}
.rubric-number {
    font-size: 15px;
    font-weight: 700;
}
.rubric-text {
    font-size: 11px;
}
.progress-bar-container {
    height: 7px;
    background: #e2e8f0;
    border-radius: 9999px;
    overflow: hidden;
    margin: 4px 0 6px;
}
.progress-bar-fill {
    height: 100%;
    border-radius: 9999px;
    transition: width 0.3s ease;
}
</style>

<div class="page-header">
    <div>
        <h1 class="page-title"><?= SecurityHelper::escape($pageTitle) ?></h1>
        <div class="page-subtitle">
            Penilaian sasaran indikator kerja dan evaluasi rubrik perilaku Islami untuk <strong><?= SecurityHelper::escape($assessment['nama']) ?></strong> (<?= SecurityHelper::escape($assessment['unit_name']) ?>).
        </div>
    </div>
    <div>
        <a href="<?= Auth::baseUrl() ?>/akfi/penilaian-bawahan" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>
</div>

<div class="form-layout-grid">
    <!-- KOLOM KIRI: FORMULIR PENILAIAN -->
    <div>
        <form action="<?= Auth::baseUrl() ?>/akfi/assess-save" method="POST">
            <?= CSRF::field() ?>
            <input type="hidden" name="assessment_id" value="<?= $assessment['id'] ?>">

            <!-- 1. SASARAN INDIKATOR KINERJA -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-clipboard-check" style="color: var(--accent); margin-right: 8px;"></i>
                        Penilaian Sasaran Indikator Kinerja
                    </h3>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <span class="badge badge-info" id="kinerjaSummaryBadge" style="font-size: 12px; padding: 5px 10px;">
                            Bobot Kinerja (A): <strong id="liveKinerjaTotal">0.00</strong> / <?= $assessment['bobot_kinerja'] ?>%
                        </span>
                        <span class="badge badge-warning" id="absensiSummaryBadge" style="font-size: 12px; padding: 5px 10px; background: #fef08a; color: #854d0e; border: 1px solid #fde047;">
                            Bobot Absensi (C): <strong id="liveAbsensiTotal">0.00</strong> / <?= $assessment['bobot_absensi'] ?>%
                        </span>
                    </div>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width: 45px; text-align: center;">No</th>
                                    <th>Indikator Sasaran Kerja</th>
                                    <th style="width: 105px; text-align: center;">Target</th>
                                    <th style="width: 70px; text-align: center;">Bobot</th>
                                    <th style="width: 125px; text-align: center;">Realisasi Aktual</th>
                                    <th style="width: 125px; text-align: center;">Skor Penilai</th>
                                    <th style="width: 100px; text-align: center;">Poin Kinerja</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $curSec = '';
                                foreach ($items as $idx => $it): 
                                    $itData = $savedItems[$it['id']] ?? [];
                                    $actual = $itData['actual_val'] ?? null;
                                    $hasAuto = !empty($it['auto_source']) && isset($autoData[$it['auto_source']]);
                                    $isSecC = ($it['section'] === 'C' || $it['category'] === 'Absensi');
                                    if ($actual === null) {
                                        if ($hasAuto) {
                                            $actual = $autoData[$it['auto_source']];
                                        } else {
                                            $actual = $isSecC ? 0 : $it['target_val'];
                                        }
                                    }
                                    $supScore = $itData['superior_score'] ?? ($hasAuto ? $autoData[$it['auto_source']] : ($isSecC ? 0 : $it['target_val']));

                                    // Kalkulasi Poin Kinerja awal
                                    $evalVal = (float)$supScore;
                                    $tVal = (float)$it['target_val'];
                                    $wVal = (float)$it['weight'];
                                    if ($it['direction'] === 'lower_better') {
                                        if ($tVal <= 0) {
                                            $rVal = $evalVal <= 0 ? 1.0 : max(0.0, 1.0 - ($evalVal / 100.0));
                                        } else {
                                            if ($isSecC) {
                                                // Bagian C: Absensi & Kegiatan (Datang Terlambat, Pulang Cepat, Lupa Catat, Mangkir)
                                                // Skor 0 = Poin penuh (maksimal)
                                                // Semakin besar nilai skor maka poin semakin berkurang
                                                // Batas maksimal skor = target (misal 6): jika skor >= 6 maka poin otomatis 0
                                                $rVal = max(0.0, ($tVal - $evalVal) / $tVal);
                                            } else {
                                                // Target > 0 toleransi (misal Turn Over Talent target <= 5%)
                                                $rVal = $evalVal <= $tVal ? 1.0 : max(0.0, 1.0 - (($evalVal - $tVal) / $tVal));
                                            }
                                        }
                                    } else {
                                        $rVal = $tVal <= 0 ? 1.0 : min(1.0, max(0.0, $evalVal / $tVal));
                                    }
                                    $curItemScore = round($rVal * $wVal, 2);

                                    if ($curSec !== $it['section']):
                                        $curSec = $it['section'];
                                        if ($curSec === 'A') {
                                            $secTitle = 'BAGIAN A: TARGET PROGRAM & TUGAS POKOK';
                                            $secIcon = 'fa-bullseye';
                                        } elseif ($curSec === 'B') {
                                            $secTitle = 'BAGIAN B: TUGAS TAMBAHAN & PENDUKUNG';
                                            $secIcon = 'fa-layer-group';
                                        } else {
                                            $secTitle = 'BAGIAN C: ADMINISTRASI & KEGIATAN';
                                            $secIcon = 'fa-folder-open';
                                        }
                                ?>
                                    <tr class="section-header-row">
                                        <td colspan="7">
                                            <i class="fa-solid <?= $secIcon ?>" style="margin-right: 6px; color: var(--primary);"></i>
                                            <?= $secTitle ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                    <tr>
                                        <td style="text-align: center; color: var(--text-muted); font-size: 12px;"><?= $idx + 1 ?></td>
                                        <td>
                                            <div style="font-weight: 600; color: var(--text-main); margin-bottom: 4px;">
                                                 <?= SecurityHelper::escape($it['item_name']) ?>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <span class="badge badge-secondary" style="font-size: 10px;">
                                                    <?= SecurityHelper::escape($it['category']) ?>
                                                </span>
                                                <?php if ($hasAuto): ?>
                                                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 4px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #047857; font-size: 11px; font-weight: 600;">
                                                        <i class="fa-solid fa-robot"></i> Terkoneksi Sistem: <?= $autoData[$it['auto_source']] ?> <?= SecurityHelper::escape($it['unit_type']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($it['description'])): ?>
                                                <div style="font-size: 12px; color: var(--text-muted); font-style: italic; margin-top: 4px;">
                                                    <?= SecurityHelper::escape($it['description']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align: center; font-family: monospace; font-size: 13px;">
                                            <strong><?= $it['target_val'] ?></strong>
                                            <div style="font-size: 11px; color: var(--text-muted);"><?= SecurityHelper::escape($it['unit_type']) ?></div>
                                        </td>
                                        <td style="text-align: center; font-weight: 700; font-size: 13px; color: var(--primary);">
                                            <?= $it['weight'] ?>
                                        </td>
                                        <td>
                                            <input type="number" step="0.1" name="actual_val[<?= $it['id'] ?>]" class="form-control" style="text-align: center; font-family: monospace; font-size: 13px; font-weight: 600;" value="<?= $actual ?>" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.1" 
                                                   name="superior_score[<?= $it['id'] ?>]" 
                                                   id="sup_<?= $it['id'] ?>"
                                                   class="form-control sup-score-input" 
                                                   data-item-id="<?= $it['id'] ?>"
                                                   data-target="<?= (float)$it['target_val'] ?>"
                                                   data-weight="<?= (float)$it['weight'] ?>"
                                                   data-direction="<?= $it['direction'] ?>"
                                                   data-section="<?= $it['section'] ?>"
                                                   oninput="recalcAkfiLive()"
                                                   style="text-align: center; font-family: monospace; font-size: 13px; font-weight: 600; border-color: #93c5fd; background: #f8fafc;" 
                                                   value="<?= $supScore ?>" required>
                                        </td>
                                        <td style="text-align: center; font-weight: 700; font-size: 13.5px; color: #0f766e; background: #f0fdfa;">
                                            <span id="score_display_<?= $it['id'] ?>"><?= number_format($curItemScore, 2) ?></span>
                                            <span style="font-size: 10.5px; font-weight: normal; color: var(--text-muted);">pt</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 2. RUBRIK SIKAP & PERILAKU ISLAMI -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-heart" style="color: #ec4899; margin-right: 8px;"></i>
                        Rubrik Sikap & Perilaku Islami (Skala 1 - 5)
                    </h3>
                    <span class="badge badge-warning">Bobot Sikap: <?= $assessment['bobot_sikap'] ?>%</span>
                </div>
                <div class="card-body">
                    <?php 
                    $rubrics = [
                        'sikap_1' => ['label' => 'Kedisiplinan, Kehadiran Tepat Waktu & Ketaatan Regulasi', 'desc' => 'Hadir sebelum jam kerja, mematuhi SOP dan regulasi yayasan.'],
                        'sikap_2' => ['label' => 'Akhlaq, Keteladanan Islami & Busana Syar\'i', 'desc' => 'Menjaga adab Islami, tutur kata santun, dan busana sesuai syariat.'],
                        'sikap_3' => ['label' => 'Loyalitas, Kerjasama Tim & Komitmen Dakwah', 'desc' => 'Mendukung visi misi yayasan dan proaktif membantu sesama rekan kerja.'],
                        'sikap_4' => ['label' => 'Inovasi, Kualitas Kerja & Tanggung Jawab', 'desc' => 'Kerapian dan ketuntasan tugas tanpa perlu diawasi terus menerus.'],
                        'sikap_5' => ['label' => 'Partisipasi Kajian Pembinaan & Kegiatan Yayasan', 'desc' => 'Aktif dalam majelis taklim Sabtu, PHBI, dan kegiatan sosial sekolah.']
                    ];
                    foreach ($rubrics as $key => $r):
                    ?>
                        <div class="rubric-item">
                            <div style="font-weight: 600; font-size: 13px; color: var(--text-main);">
                                <?= $r['label'] ?>
                            </div>
                            <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                                <?= $r['desc'] ?>
                            </div>
                            <div class="rubric-radio-grid">
                                <?php for ($sc = 1; $sc <= 5; $sc++): ?>
                                    <label class="rubric-radio-label">
                                        <input type="radio" name="sikap[<?= $key ?>]" value="<?= $sc ?>" <?= $sc === 5 ? 'checked' : '' ?> required>
                                        <div class="rubric-radio-card">
                                            <span class="rubric-number"><?= $sc ?></span>
                                            <span class="rubric-text"><?= $scLabels[$sc] ?></span>
                                        </div>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 3. CATATAN & REKOMENDASI ATASAN -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-comment-dots" style="color: var(--primary-light); margin-right: 8px;"></i>
                        Catatan & Rekomendasi Pengembangan Pegawai
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group" style="margin-bottom: 0;">
                        <textarea name="superior_notes" rows="4" class="form-control" placeholder="Tuliskan catatan apresiasi capaian, evaluasi kedisiplinan, dan target perbaikan kinerja berikutnya..."><?= SecurityHelper::escape($assessment['superior_notes'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- ACTION BAR -->
            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 12px; margin-bottom: 30px;">
                <a href="<?= Auth::baseUrl() ?>/akfi/penilaian-bawahan" class="btn btn-outline">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 600;">
                    <i class="fa-solid fa-signature"></i> Simpan & Tanda Tangani Penilaian
                </button>
            </div>
        </form>
    </div>

    <!-- KOLOM KANAN: SIDEBAR INFORMASI PEGAWAI & DATA SISTEM -->
    <div>
        <!-- IDENTITAS PEGAWAI -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-id-card" style="color: var(--primary-light); margin-right: 8px;"></i>
                    Identitas Pegawai
                </h3>
            </div>
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 16px;">
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 700; flex-shrink: 0;">
                        <?= strtoupper(mb_substr($assessment['nama'], 0, 1)) ?>
                    </div>
                    <div>
                        <div style="font-weight: 700; font-size: 15px; color: var(--text-main);">
                            <?= SecurityHelper::escape($assessment['nama']) ?>
                        </div>
                        <div style="font-size: 12px; color: var(--text-muted);">
                            NIPY: <?= SecurityHelper::escape($assessment['nipy'] ?? '-') ?>
                        </div>
                    </div>
                </div>

                <div style="font-size: 13px; border-top: 1px solid var(--border-color); padding-top: 12px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="color: var(--text-muted);">Satuan Unit</span>
                        <strong><?= SecurityHelper::escape($assessment['unit_name']) ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="color: var(--text-muted);">Jabatan</span>
                        <span><?= SecurityHelper::escape($assessment['position']) ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="color: var(--text-muted);">Template AKFI</span>
                        <span class="badge badge-secondary"><?= SecurityHelper::escape($assessment['position_name']) ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--text-muted);">Status Saat Ini</span>
                        <span class="badge badge-info"><?= SecurityHelper::escape($assessment['status']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- AUTO-DATA COLLECTOR SISTEM -->
        <div class="card">
            <div class="card-header" style="background: #f0fdf4; border-bottom-color: #bbf7d0;">
                <h3 class="card-title" style="color: #166534;">
                    <i class="fa-solid fa-robot" style="color: #16a34a; margin-right: 8px;"></i>
                    Auto-Data Collector
                </h3>
            </div>
            <div class="card-body">
                <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 16px; line-height: 1.4;">
                    Metrik riil khusus posisi <strong><?= SecurityHelper::escape($assessment['position_name']) ?></strong> yang tercatat otomatis pada sistem:
                </div>

                <!-- 1. Presensi Kehadiran Kerja (Semua Pegawai) -->
                <div style="margin-bottom: 14px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 500;">
                        <span>Presensi Kehadiran Kerja</span>
                        <?php if (!empty($autoData['has_attendance_data'])): ?>
                            <strong style="color: var(--accent);"><?= $autoData['attendance_pct'] ?>%</strong>
                        <?php else: ?>
                            <span class="badge badge-secondary" style="font-size: 10px;">Belum Ada Log Presensi (0%)</span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($autoData['has_attendance_data'])): ?>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['attendance_pct']) ?>%; background: var(--accent);"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                            Akumulasi: <strong><?= $autoData['tot_hadir'] ?> / <?= $autoData['annual_target_workdays'] ?? 264 ?> Hari</strong> (<?= $autoData['attendance_months_count'] ?> dari 12 Bulan) &bull; Rata-rata: <strong><?= $autoData['monthly_attendance_rate'] ?? 0 ?>%</strong>
                        </div>
                        
                        <!-- Rekap Tahunan 4 Status Disiplin (Terlambat, Pulang Cepat, Lupa Catat, Tidak Hadir/Mangkir) -->
                        <div style="margin-top: 8px; padding: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                            <div style="font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 6px; display: flex; align-items: center; gap: 5px;">
                                <i class="fa-solid fa-calendar-check" style="color: var(--primary);"></i>
                                Rekap Disiplin Presensi Tahunan (Hari dalam Setahun):
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px; font-size: 11px;">
                                <div style="padding: 5px 8px; background: #fff; border: 1px solid #fed7aa; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
                                    <span style="color: #9a3412;"><i class="fa-solid fa-clock" style="color: #ea580c; margin-right: 4px;"></i> Terlambat:</span>
                                    <strong style="color: #c2410c; font-weight: 700;"><?= (int)($autoData['att_terlambat'] ?? 0) ?> hari</strong>
                                </div>
                                <div style="padding: 5px 8px; background: #fff; border: 1px solid #fde68a; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
                                    <span style="color: #854d0e;"><i class="fa-solid fa-person-walking-arrow-right" style="color: #d97706; margin-right: 4px;"></i> Pulang Cepat:</span>
                                    <strong style="color: #b45309; font-weight: 700;"><?= (int)($autoData['att_pulang_cepat'] ?? 0) ?> hari</strong>
                                </div>
                                <div style="padding: 5px 8px; background: #fff; border: 1px solid #e9d5ff; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
                                    <span style="color: #6b21a8;"><i class="fa-solid fa-clipboard-question" style="color: #9333ea; margin-right: 4px;"></i> Lupa Catat:</span>
                                    <strong style="color: #7e22ce; font-weight: 700;"><?= (int)($autoData['att_lupa_catat'] ?? 0) ?> hari</strong>
                                </div>
                                <div style="padding: 5px 8px; background: #fff; border: 1px solid #fecaca; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
                                    <span style="color: #991b1b;"><i class="fa-solid fa-circle-xmark" style="color: #dc2626; margin-right: 4px;"></i> Mangkir:</span>
                                    <strong style="color: #b91c1c; font-weight: 700;"><?= (int)($autoData['att_mangkir'] ?? $autoData['att_alpha'] ?? 0) ?> hari</strong>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Rekap Tahunan Default jika belum ada pengisian -->
                        <div style="margin-top: 8px; padding: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                            <div style="font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 6px; display: flex; align-items: center; gap: 5px;">
                                <i class="fa-solid fa-calendar-check" style="color: var(--primary);"></i>
                                Rekap Disiplin Presensi Tahunan (Hari dalam Setahun):
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px; font-size: 11px;">
                                <div style="padding: 5px 8px; background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
                                    <span style="color: var(--text-muted);"><i class="fa-solid fa-clock" style="margin-right: 4px;"></i> Terlambat:</span>
                                    <strong><?= (int)($autoData['att_terlambat'] ?? 0) ?> hari</strong>
                                </div>
                                <div style="padding: 5px 8px; background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
                                    <span style="color: var(--text-muted);"><i class="fa-solid fa-person-walking-arrow-right" style="margin-right: 4px;"></i> Pulang Cepat:</span>
                                    <strong><?= (int)($autoData['att_pulang_cepat'] ?? 0) ?> hari</strong>
                                </div>
                                <div style="padding: 5px 8px; background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
                                    <span style="color: var(--text-muted);"><i class="fa-solid fa-clipboard-question" style="margin-right: 4px;"></i> Lupa Catat:</span>
                                    <strong><?= (int)($autoData['att_lupa_catat'] ?? 0) ?> hari</strong>
                                </div>
                                <div style="padding: 5px 8px; background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
                                    <span style="color: var(--text-muted);"><i class="fa-solid fa-circle-xmark" style="margin-right: 4px;"></i> Mangkir:</span>
                                    <strong><?= (int)($autoData['att_mangkir'] ?? $autoData['att_alpha'] ?? 0) ?> hari</strong>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 2. Kajian Sabtu (Semua Pegawai kecuali Security) -->
                <?php if ($posCode !== 'SECURITY'): ?>
                <div style="margin-bottom: 14px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 500;">
                        <span>Kehadiran Kajian Sabtu</span>
                        <?php if (!empty($autoData['tot_kajian_hadir'])): ?>
                            <strong style="color: var(--primary-light);"><?= $autoData['kajian_pct'] ?>%</strong>
                        <?php else: ?>
                            <span class="badge badge-secondary" style="font-size: 10px;">Belum Ada Rekap Kajian (0%)</span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($autoData['tot_kajian_hadir'])): ?>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['kajian_pct']) ?>%; background: var(--primary-light);"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                            Akumulasi: <strong><?= $autoData['tot_kajian_hadir'] ?> / <?= $autoData['annual_target_kajian'] ?? 48 ?> Pertemuan</strong> &bull; Rata-rata: <strong><?= $autoData['monthly_kajian_rate'] ?? 0 ?>%</strong>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- 3. INDIKATOR ROLE: GURU WALI KELAS (GURU_WALAS) -->
                <?php if ($posCode === 'GURU_WALAS'): ?>
                    <!-- K-ADM-01: Administrasi Walas -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 500;">
                            <span>Terkumpulnya Dokumen Administrasi</span>
                            <strong style="color: <?= $autoData['verified_admin'] > 0 ? 'var(--accent)' : 'var(--text-muted)' ?>;"><?= $autoData['administrasi_completion'] ?>%</strong>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['administrasi_completion']) ?>%; background: var(--accent);"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                            Target: <strong>100%</strong> &bull; Terverifikasi: <strong><?= $autoData['verified_admin'] ?> / <?= $autoData['target_admin_count'] ?></strong> Dokumen Wajib
                        </div>
                    </div>

                    <!-- K-ADM-02: % Penyelesaian Tugas Sesuai Deadline & Kualitas -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 500;">
                            <span>% Penyelesaian Tugas Sesuai Deadline &amp; Kualitas</span>
                            <?php if ($autoData['verified_admin'] > 0): ?>
                                <strong style="color: #16a34a;"><?= $autoData['administrasi_ontime'] ?>%</strong>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="font-size: 10px;">Belum Ada Berkas (0%)</span>
                            <?php endif; ?>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['administrasi_ontime']) ?>%; background: #16a34a;"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                            Target: <strong>100%</strong> Tepat Waktu (<?= $autoData['verified_admin'] ?> berkas terverifikasi)
                        </div>
                    </div>

                    <!-- K-PEM-01: % Capaian Kehadiran Siswa (Target 100%) -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 500;">
                            <span>% Capaian Kehadiran Siswa (Target 100%)</span>
                            <?php if ($autoData['student_attendance'] > 0): ?>
                                <strong style="color: #0d9488;"><?= $autoData['student_attendance'] ?>%</strong>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="font-size: 10px;">Belum Ada Presensi (0%)</span>
                            <?php endif; ?>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['student_attendance']) ?>%; background: #0d9488;"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                            Target: <strong>100%</strong> (Akumulasi: <strong><?= $autoData['student_attendance_months'] ?? 0 ?> / 12 Bulan</strong> &bull; Rata-rata Bulan Berjalan: <strong><?= $autoData['student_attendance_monthly_rate'] ?? 0 ?>%</strong>)
                            <?php if (!empty($autoData['walas_class_names'])): ?>
                                <br>Kelas: <strong><?= SecurityHelper::escape($autoData['walas_class_names']) ?></strong> (<?= $autoData['student_hadir_sum'] ?? 0 ?> / <?= $autoData['student_target_days'] ?? 0 ?> Hari Hadir Siswa)
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- K-PRF-03: % Siswa Lulus Target Tahfidz -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 500;">
                            <span>% Siswa Lulus Target Tahfidz</span>
                            <?php if ($autoData['tahfidz_pass_pct'] > 0): ?>
                                <strong style="color: #6366f1;"><?= $autoData['tahfidz_pass_pct'] ?>%</strong>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="font-size: 10px;">Belum Ada Kelulusan (0%)</span>
                            <?php endif; ?>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['tahfidz_pass_pct']) ?>%; background: #6366f1;"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                            Target: <strong>100%</strong> &bull; Lulus: <strong><?= $autoData['walas_passed_tahfidz'] ?? 0 ?> / <?= $autoData['walas_total_students'] ?? 0 ?></strong> Santri
                        </div>
                    </div>

                    <!-- K-PEM-02: Laporan PTK -->
                    <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; margin-bottom: 14px; font-size: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span>Terkumpulnya PTK (Penelitian Tindakan Kelas):</span>
                            <span class="badge <?= $autoData['upload_ptk'] ? 'badge-success' : 'badge-secondary' ?>">
                                <?= $autoData['upload_ptk'] ? '<i class="fa-solid fa-check" style="margin-right: 4px;"></i> Disetujui' : 'Belum Ada' ?>
                            </span>
                        </div>
                    </div>

                    <!-- K-PEM-03: Capaian Rata-rata Nilai Mapel -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 500;">
                            <span>Capaian Rata-rata Nilai Mapel</span>
                            <?php if (($autoData['grades_count'] ?? 0) > 0): ?>
                                <strong style="color: var(--primary);"><?= $autoData['subject_average'] ?></strong>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="font-size: 10px;">Belum Ada Nilai KBM</span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                            Target: <strong>95.0</strong>
                        </div>
                    </div>

                    <!-- K-PRF-01: Nilai Supervisi Mengajar -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 500;">
                            <span>Capaian Nilai Supervisi Mengajar</span>
                            <?php if (($autoData['sessions_count'] ?? 0) > 0): ?>
                                <strong style="color: #0d9488;"><?= $autoData['avg_supervisi'] ?> / 100</strong>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="font-size: 10px;">Belum Disupervisi (0 / 100)</span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                            Target: <strong>90.0</strong>
                        </div>
                    </div>

                    <!-- K-PRF-02: Juara Lomba Siswa Binaan -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 500;">
                            <span>Prestasi Juara Lomba Siswa (Juara 1)</span>
                            <strong style="color: var(--primary);"><?= $autoData['juara_lomba_walas'] ?> Kejuaraan</strong>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                            Target: <strong>3 Orang</strong>
                        </div>
                    </div>

                    <!-- K-PRF-04: Target Hafalan Guru -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 500;">
                            <span>% Target Hafalan Tahunan Guru (1 Juz)</span>
                            <?php if ($autoData['teacher_tahfidz_target'] > 0): ?>
                                <strong style="color: #6366f1;"><?= $autoData['teacher_tahfidz_target'] ?>%</strong>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="font-size: 10px;">Belum Ada Setoran (0%)</span>
                            <?php endif; ?>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['teacher_tahfidz_target']) ?>%; background: #6366f1;"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                            Target: <strong>100%</strong> (1 Juz / 300 Baris)
                        </div>
                    </div>

                    <!-- K-PRF-05: Kehadiran Kegiatan Sekolah -->
                    <div style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 500;">
                            <span>% Kehadiran Kegiatan Sekolah / Luar KBM</span>
                            <?php if ($autoData['kegiatan_kbm_attendance'] > 0): ?>
                                <strong style="color: #8b5cf6;"><?= $autoData['kegiatan_kbm_attendance'] ?>%</strong>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="font-size: 10px;">Belum Ada Kegiatan (0%)</span>
                            <?php endif; ?>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['kegiatan_kbm_attendance']) ?>%; background: #8b5cf6;"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                            Target: <strong>95%</strong>
                        </div>
                    </div>

                <!-- 4. INDIKATOR ROLE: GURU MATA PELAJARAN (GURU_MAPEL / GURU_MAPEL_KBTK) -->
                <?php elseif ($posCode === 'GURU_MAPEL' || $posCode === 'GURU_MAPEL_KBTK'): ?>
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 500;">
                            <span>Rata-rata Supervisi Mengajar</span>
                            <?php if (($autoData['sessions_count'] ?? 0) > 0): ?>
                                <strong style="color: #0d9488;"><?= $autoData['avg_supervisi'] ?> / 100</strong>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="font-size: 10px;">Belum Disupervisi (0 / 100)</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- K-ADM-01: Administrasi Perencanaan Mapel -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 500;">
                            <span>Terkumpulnya Perangkat Pembelajaran</span>
                            <strong style="color: <?= $autoData['verified_admin'] > 0 ? 'var(--accent)' : 'var(--text-muted)' ?>;"><?= $autoData['administrasi_completion'] ?>%</strong>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['administrasi_completion']) ?>%; background: var(--accent);"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                            Target: <strong>100%</strong> &bull; Terverifikasi: <strong><?= $autoData['verified_admin'] ?> / <?= $autoData['target_admin_count'] ?></strong> Dokumen Wajib
                        </div>
                    </div>

                    <!-- K-ADM-02: % Penyelesaian Tugas Sesuai Deadline & Kualitas -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 500;">
                            <span>% Penyelesaian Tugas Sesuai Deadline &amp; Kualitas</span>
                            <?php if ($autoData['verified_admin'] > 0): ?>
                                <strong style="color: #16a34a;"><?= $autoData['administrasi_ontime'] ?>%</strong>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="font-size: 10px;">Belum Ada Berkas (0%)</span>
                            <?php endif; ?>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['administrasi_ontime']) ?>%; background: #16a34a;"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                            Target: <strong>100%</strong> Tepat Waktu (<?= $autoData['verified_admin'] ?> berkas terverifikasi)
                        </div>
                    </div>

                    <!-- K-PEM-01: % Capaian Kehadiran Siswa -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 500;">
                            <span>% Capaian Kehadiran Siswa di Kelas</span>
                            <?php if ($autoData['student_attendance'] > 0): ?>
                                <strong style="color: #0d9488;"><?= $autoData['student_attendance'] ?>%</strong>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="font-size: 10px;">Belum Ada Presensi (0%)</span>
                            <?php endif; ?>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['student_attendance']) ?>%; background: #0d9488;"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                            Target: <strong>90%</strong> (Akumulasi: <strong><?= $autoData['student_attendance_months'] ?? 0 ?> / 12 Bulan</strong> &bull; Rata-rata Bulan Berjalan: <strong><?= $autoData['student_attendance_monthly_rate'] ?? 0 ?>%</strong>)
                            <?php if (!empty($autoData['walas_class_names'])): ?>
                                <br>Kelas: <strong><?= SecurityHelper::escape($autoData['walas_class_names']) ?></strong>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- K-PEM-02: Best Practice -->
                    <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; margin-bottom: 14px; font-size: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span>Terkumpulnya Best Practice:</span>
                            <span class="badge <?= $autoData['upload_best_practice'] ? 'badge-success' : 'badge-secondary' ?>">
                                <?= $autoData['upload_best_practice'] ? '<i class="fa-solid fa-check" style="margin-right: 4px;"></i> Disetujui' : 'Belum Ada' ?>
                            </span>
                        </div>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 500;">
                            <span>Rata-rata Nilai Mapel Siswa</span>
                            <?php if (($autoData['grades_count'] ?? 0) > 0): ?>
                                <strong style="color: var(--primary);"><?= $autoData['subject_average'] ?></strong>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="font-size: 10px;">Belum Ada Nilai KBM</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 500;">
                            <span>Ketuntasan Tahfidz Pegawai</span>
                            <?php if ($autoData['teacher_tahfidz_target'] > 0): ?>
                                <strong style="color: #6366f1;"><?= $autoData['teacher_tahfidz_target'] ?>%</strong>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="font-size: 10px;">Belum Ada Setoran (0%)</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 500;">
                            <span>Partisipasi Luar KBM</span>
                            <?php if ($autoData['kegiatan_kbm_attendance'] > 0): ?>
                                <strong style="color: #8b5cf6;"><?= $autoData['kegiatan_kbm_attendance'] ?>%</strong>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="font-size: 10px;">Belum Ada Penugasan</span>
                            <?php endif; ?>
                        </div>
                    </div>

                <!-- 5. INDIKATOR ROLE: GURU TAHFIDZ (GURU_TAHTA) -->
                <?php elseif ($posCode === 'GURU_TAHTA'): ?>
                    <!-- % Kehadiran Siswa di Halaqah -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 500;">
                            <span>% Capaian Kehadiran Siswa di Halaqah</span>
                            <?php if ($autoData['student_attendance'] > 0): ?>
                                <strong style="color: #0d9488;"><?= $autoData['student_attendance'] ?>%</strong>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="font-size: 10px;">Belum Ada Presensi (0%)</span>
                            <?php endif; ?>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['student_attendance']) ?>%; background: #0d9488;"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                            Target: <strong>90%</strong> (Akumulasi: <strong><?= $autoData['student_attendance_months'] ?? 0 ?> / 12 Bulan</strong> &bull; Rata-rata Bulan Berjalan: <strong><?= $autoData['student_attendance_monthly_rate'] ?? 0 ?>%</strong>)
                            <?php if (!empty($autoData['walas_class_names'])): ?>
                                <br>Halaqah: <strong><?= SecurityHelper::escape($autoData['walas_class_names']) ?></strong> (<?= $autoData['student_hadir_sum'] ?? 0 ?> / <?= $autoData['student_target_days'] ?? 0 ?> Hari Hadir Santri)
                            <?php else: ?>
                                <br><span style="color: var(--text-muted); font-style: italic;">(Belum ada penugasan halaqah santri yang diampu)</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- % Siswa Lulus Target Tahfidz -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 500;">
                            <span>% Siswa Lulus Target Tahfidz</span>
                            <?php if ($autoData['tahfidz_pass_pct'] > 0): ?>
                                <strong style="color: #6366f1;"><?= $autoData['tahfidz_pass_pct'] ?>%</strong>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="font-size: 10px;">Belum Ada Lulus Target (0%)</span>
                            <?php endif; ?>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['tahfidz_pass_pct']) ?>%; background: #6366f1;"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                            Target: <strong>80%</strong> &bull; Realisasi: <strong><?= $autoData['walas_passed_tahfidz'] ?? 0 ?></strong> / <strong><?= $autoData['walas_total_students'] ?? 0 ?></strong> Santri Halaqah
                            <?php if (!empty($autoData['walas_class_names'])): ?>
                                &bull; <?= SecurityHelper::escape($autoData['walas_class_names']) ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 500;">
                            <span>Supervisi Pembelajaran Al-Qur'an</span>
                            <?php if (($autoData['sessions_count'] ?? 0) > 0): ?>
                                <strong style="color: #0d9488;"><?= $autoData['avg_supervisi'] ?> / 100</strong>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="font-size: 10px;">Belum Disupervisi (0 / 100)</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; margin-bottom: 14px; font-size: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span>Best Practice Pembelajaran:</span>
                            <span class="badge <?= $autoData['upload_best_practice'] ? 'badge-success' : 'badge-secondary' ?>">
                                <?= $autoData['upload_best_practice'] ? '<i class="fa-solid fa-check" style="margin-right: 4px;"></i> Disetujui' : 'Belum Ada' ?>
                            </span>
                        </div>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 500;">
                            <span>Administrasi Guru Tahfidz</span>
                            <strong style="color: <?= $autoData['verified_admin'] > 0 ? 'var(--accent)' : 'var(--text-muted)' ?>;"><?= $autoData['administrasi_completion'] ?>%</strong>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['administrasi_completion']) ?>%; background: var(--accent);"></div>
                        </div>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 500;">
                            <span>Ketuntasan Tahfidz Mandiri Guru</span>
                            <?php if ($autoData['teacher_tahfidz_target'] > 0): ?>
                                <strong style="color: #6366f1;"><?= $autoData['teacher_tahfidz_target'] ?>%</strong>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="font-size: 10px;">Belum Ada Setoran (0%)</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 500;">
                            <span>Partisipasi Luar KBM</span>
                            <?php if ($autoData['kegiatan_kbm_attendance'] > 0): ?>
                                <strong style="color: #8b5cf6;"><?= $autoData['kegiatan_kbm_attendance'] ?>%</strong>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="font-size: 10px;">Belum Ada Penugasan</span>
                            <?php endif; ?>
                        </div>
                    </div>

                <!-- 6. INDIKATOR ROLE: GURU TK / KB (GURU_TK) -->
                <?php elseif ($posCode === 'GURU_TK'): ?>
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 500;">
                            <span>Rata-rata Supervisi Kelas TK</span>
                            <?php if (($autoData['sessions_count'] ?? 0) > 0): ?>
                                <strong style="color: #0d9488;"><?= $autoData['avg_supervisi'] ?> / 100</strong>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="font-size: 10px;">Belum Disupervisi (0 / 100)</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 500;">
                            <span>Administrasi Guru TK</span>
                            <strong style="color: <?= $autoData['verified_admin'] > 0 ? 'var(--accent)' : 'var(--text-muted)' ?>;"><?= $autoData['administrasi_completion'] ?>%</strong>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['administrasi_completion']) ?>%; background: var(--accent);"></div>
                        </div>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 500;">
                            <span>Target Tahfidz Guru TK (1/2 Juz)</span>
                            <?php if ($autoData['teacher_tahfidz_target'] > 0): ?>
                                <strong style="color: #6366f1;"><?= $autoData['teacher_tahfidz_target'] ?>%</strong>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="font-size: 10px;">Belum Ada Setoran (0%)</span>
                            <?php endif; ?>
                        </div>
                    </div>

                <!-- 7. INDIKATOR ROLE: TATA USAHA (TU) -->
                <?php elseif ($posCode === 'TU'): ?>
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>Dokumen Penggajian Bulanan</span>
                            <strong style="color: var(--accent);"><?= $autoData['tu_dokumen_gaji'] ?>%</strong>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['tu_dokumen_gaji']) ?>%; background: var(--accent);"></div>
                        </div>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>Laporan Gaji Tepat Waktu</span>
                            <strong style="color: var(--primary-light);"><?= $autoData['tu_laporan_gaji_ontime'] ?>%</strong>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['tu_laporan_gaji_ontime']) ?>%; background: var(--primary-light);"></div>
                        </div>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>Kesalahan Input Braja</span>
                            <strong style="color: <?= $autoData['tu_braja_errors'] == 0 ? '#16a34a' : '#dc2626' ?>;"><?= $autoData['tu_braja_errors'] ?> Kesalahan</strong>
                        </div>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>Partisipasi Kegiatan Sekolah</span>
                            <strong style="color: #8b5cf6;"><?= $autoData['kegiatan_kbm_attendance'] ?>%</strong>
                        </div>
                    </div>

                <!-- 8. INDIKATOR ROLE: CLEANING SERVICES (CS) -->
                <?php elseif ($posCode === 'CS'): ?>
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>Checklist Pekerjaan Rutin Harian</span>
                            <strong style="color: var(--accent);"><?= $autoData['cs_checklist_rutin'] ?>%</strong>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['cs_checklist_rutin']) ?>%; background: var(--accent);"></div>
                        </div>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>Pelaksanaan Piket Bulanan</span>
                            <strong style="color: var(--primary);"><?= $autoData['cs_checklist_piket'] ?>x Dilaksanakan</strong>
                        </div>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>Gotong Royong Sabtu</span>
                            <strong style="color: #0d9488;"><?= $autoData['cs_gotong_royong'] ?>x Terlaksana</strong>
                        </div>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>Kepuasan Kebersihan Wali Murid</span>
                            <strong style="color: #16a34a;"><?= $autoData['survey_parent_cleanliness'] ?>%</strong>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['survey_parent_cleanliness']) ?>%; background: #16a34a;"></div>
                        </div>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>Partisipasi Kegiatan Sekolah</span>
                            <strong style="color: #8b5cf6;"><?= $autoData['kegiatan_kbm_attendance'] ?>%</strong>
                        </div>
                    </div>

                <!-- 9. INDIKATOR ROLE: SECURITY -->
                <?php elseif ($posCode === 'SECURITY'): ?>
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>Laporan Harian Lengkap</span>
                            <strong style="color: var(--accent);"><?= $autoData['sec_daily_complete'] ?>%</strong>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['sec_daily_complete']) ?>%; background: var(--accent);"></div>
                        </div>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>Laporan Harian Tepat Waktu</span>
                            <strong style="color: var(--primary-light);"><?= $autoData['sec_daily_ontime'] ?>%</strong>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['sec_daily_ontime']) ?>%; background: var(--primary-light);"></div>
                        </div>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>Patroli & Kesiapsiagaan Sesuai Jadwal</span>
                            <strong style="color: #0d9488;"><?= $autoData['sec_patrol_pct'] ?>%</strong>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['sec_patrol_pct']) ?>%; background: #0d9488;"></div>
                        </div>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>Respon Terhadap Insiden</span>
                            <strong style="color: #6366f1;"><?= $autoData['sec_incident_rating'] ?> / 5.0</strong>
                        </div>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>Kejadian Kriminalitas / Kebobolan</span>
                            <strong style="color: #16a34a;"><?= $autoData['sec_breach_count'] ?> Kasus (Nihil)</strong>
                        </div>
                    </div>

                <!-- 10. INDIKATOR ROLE: KEPALA SEKOLAH (KANIT_SD, KANIT_TK, KANIT_SMP_SMA) -->
                <?php elseif (str_starts_with($posCode, 'KANIT')): ?>
                    <!-- K-KS-01: Administrasi Terarsip -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>% Dokumen Administrasi Terarsip</span>
                            <strong style="color: <?= $autoData['ks_admin_archived'] >= 100 ? '#16a34a' : 'var(--primary)' ?>;"><?= $autoData['ks_admin_archived'] ?>%</strong>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['ks_admin_archived']) ?>%; background: <?= $autoData['ks_admin_archived'] >= 100 ? '#16a34a' : 'var(--primary)' ?>;"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted);">
                            Target: <strong>100%</strong> &bull; Terverifikasi: <strong><?= $autoData['ks_admin_count'] ?> / 43 Slot</strong>
                        </div>
                    </div>

                    <!-- K-KS-02: Penyelesaian Deadline -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>% Tugas Sesuai Deadline</span>
                            <strong style="color: #16a34a;"><?= $autoData['ks_admin_ontime'] ?>%</strong>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['ks_admin_ontime']) ?>%; background: #16a34a;"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted);">
                            Target: <strong>100%</strong> Tepat Waktu
                        </div>
                    </div>

                    <!-- K-KS-03: Kehadiran Guru Satuan -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>% Kehadiran Guru & Karyawan Satuan</span>
                            <?php if (!empty($autoData['unit_teacher_hadir_sum'])): ?>
                                <strong style="color: var(--accent);"><?= $autoData['unit_teacher_attendance'] ?>%</strong>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="font-size: 10px;">Belum Ada Data (0%)</span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($autoData['unit_teacher_hadir_sum'])): ?>
                            <div class="progress-bar-container">
                                <div class="progress-bar-fill" style="width: <?= min(100, $autoData['unit_teacher_attendance']) ?>%; background: var(--accent);"></div>
                            </div>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                                Target: <strong>90%</strong> &bull; Akumulasi: <strong><?= $autoData['unit_attendance_months'] ?> / 12 Bln</strong> (Avg: <?= $autoData['unit_teacher_monthly_rate'] ?>%)
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- K-KS-16: Kehadiran Kajian Guru Satuan -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>% Kehadiran Kajian Guru Satuan</span>
                            <?php if (!empty($autoData['unit_kajian_hadir_sum'])): ?>
                                <strong style="color: #0284c7;"><?= $autoData['unit_kajian_guru_pct'] ?>%</strong>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="font-size: 10px;">Belum Ada Data (0%)</span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($autoData['unit_kajian_hadir_sum'])): ?>
                            <div class="progress-bar-container">
                                <div class="progress-bar-fill" style="width: <?= min(100, $autoData['unit_kajian_guru_pct']) ?>%; background: #0284c7;"></div>
                            </div>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                                Target: <strong>100%</strong> &bull; Akumulasi: <strong><?= $autoData['unit_kajian_hadir_sum'] ?> / <?= $autoData['unit_kajian_target_total'] ?> Kehadiran</strong> (Rata-rata: <strong><?= $autoData['unit_kajian_avg_weeks'] ?> / <?= $autoData['annual_target_kajian'] ?? 48 ?> Pekan</strong> &bull; Avg Berjalan: <strong><?= $autoData['unit_kajian_monthly_rate'] ?>%</strong>)
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- K-KS-04: Capaian Tahfidz Siswa Satuan -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>% Capaian Tahfidz Siswa Satuan</span>
                            <strong style="color: #6366f1;"><?= $autoData['unit_tahfidz_pct'] ?>%</strong>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['unit_tahfidz_pct']) ?>%; background: #6366f1;"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted);">
                            Target: <strong>95%</strong> Kelulusan Ujian Tahfidz
                        </div>
                    </div>

                    <!-- K-KS-05: Capaian Tahsin Siswa Satuan -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>% Capaian Tahsin Siswa Satuan</span>
                            <strong style="color: #8b5cf6;"><?= $autoData['unit_tahsin_pct'] ?>%</strong>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['unit_tahsin_pct']) ?>%; background: #8b5cf6;"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted);">
                            Target: <strong>65%</strong> (Sinkron data tahfidz)
                        </div>
                    </div>

                    <!-- K-KS-06: Nilai Rata-rata Diniyah Satuan -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>Capaian Rata-rata Diniyah</span>
                            <strong style="color: #0d9488;"><?= $autoData['unit_diniyah_avg'] ?> / 100</strong>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['unit_diniyah_avg']) ?>%; background: #0d9488;"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted);">
                            Target: <strong>100.0</strong>
                        </div>
                    </div>

                    <!-- K-KS-17: Guru Mencapai Target Hafalan Tahunan -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>% Guru Tuntas Target Hafalan</span>
                            <strong style="color: #10b981;"><?= $autoData['unit_teacher_tahfidz_pass'] ?>%</strong>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['unit_teacher_tahfidz_pass']) ?>%; background: #10b981;"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted);">
                            Target: <strong>50%</strong> (1/2 - 1 Juz / Tahun)
                        </div>
                    </div>

                    <!-- K-KS-09: Guru dengan KPI B atau A -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>% Guru dengan Nilai KPI B atau A</span>
                            <strong style="color: <?= $autoData['unit_guru_kpi_b'] >= 80 ? '#16a34a' : '#d97706' ?>;"><?= $autoData['unit_guru_kpi_b'] ?>%</strong>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['unit_guru_kpi_b']) ?>%; background: <?= $autoData['unit_guru_kpi_b'] >= 80 ? '#16a34a' : '#d97706' ?>;"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted);">
                            Target: <strong>100%</strong> Guru mendapat predikat A/B
                        </div>
                    </div>

                    <!-- K-KS-07: Rasio Tunggakan SPP -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>% Rasio Tunggakan SPP</span>
                            <strong style="color: <?= $autoData['unit_tunggakan_ratio'] <= 5 ? '#16a34a' : '#ef4444' ?>;"><?= $autoData['unit_tunggakan_ratio'] ?>%</strong>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['unit_tunggakan_ratio']) ?>%; background: <?= $autoData['unit_tunggakan_ratio'] <= 5 ? '#16a34a' : '#ef4444' ?>;"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted);">
                            Target: <strong>0%</strong> (Makin rendah makin baik)
                        </div>
                    </div>

                    <!-- K-KS-08: Prestasi Siswa Resmi Kota -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>Prestasi Siswa Kota (OSN/O2SN/FLS2N)</span>
                            <strong style="color: var(--primary);"><?= $autoData['unit_lomba_resmi_count'] ?> Kejuaraan</strong>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted);">
                            Target: <strong>3 Kejuaraan</strong>
                        </div>
                    </div>

                    <!-- K-KS-10: Capaian SPMB vs Target -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>Pencapaian SPMB Satuan vs Target</span>
                            <strong style="color: <?= $autoData['unit_spmb_achievement'] >= 100 ? '#16a34a' : 'var(--primary)' ?>;">
                                <?= $autoData['unit_spmb_achievement'] ?>%
                            </strong>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['unit_spmb_achievement']) ?>%; background: <?= $autoData['unit_spmb_achievement'] >= 100 ? '#16a34a' : 'var(--primary)' ?>;"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted);">
                            Target: <strong>100%</strong> &bull; Realisasi: <strong><?= $autoData['spmb_actual_count'] ?></strong> / Target: <strong><?= $autoData['spmb_target_count'] ?></strong> Santri
                        </div>
                    </div>

                    <!-- K-KS-11: Alumni Favorit -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>Alumni Lolos Sekolah/Kampus Favorit</span>
                            <strong style="color: #16a34a;"><?= $autoData['unit_alumni_favorite'] ?>%</strong>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['unit_alumni_favorite']) ?>%; background: #16a34a;"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted);">
                            Target: <strong>45%</strong>
                        </div>
                    </div>

                    <!-- K-KS-12: Turnover Talent -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>Rata-rata Turn Over Talent</span>
                            <strong style="color: <?= $autoData['unit_turnover_rate'] <= 5 ? '#16a34a' : '#ef4444' ?>;"><?= $autoData['unit_turnover_rate'] ?>%</strong>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['unit_turnover_rate']) ?>%; background: <?= $autoData['unit_turnover_rate'] <= 5 ? '#16a34a' : '#ef4444' ?>;"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted);">
                            Target: <strong>&le; 5.0%</strong> (Makin rendah makin baik)
                        </div>
                    </div>

                    <!-- K-KS-13: Survei Kepuasan Wali Murid -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>Survei Kepuasan Wali Murid</span>
                            <strong style="color: #0d9488;"><?= $autoData['unit_parent_survey_avg'] ?>%</strong>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['unit_parent_survey_avg']) ?>%; background: #0d9488;"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted);">
                            Target: <strong>85%</strong>
                        </div>
                    </div>

                    <!-- K-KS-13B: Survei Kepuasan Siswa -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>Survei Kepuasan Siswa</span>
                            <strong style="color: #0284c7;"><?= $autoData['unit_student_survey_avg'] ?>%</strong>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['unit_student_survey_avg']) ?>%; background: #0284c7;"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted);">
                            Target: <strong>85%</strong>
                        </div>
                    </div>

                    <!-- K-KS-14: Guru Berprestasi Satuan -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>% Guru Berprestasi</span>
                            <strong style="color: #7c3aed;"><?= $autoData['unit_guru_berprestasi_pct'] ?>%</strong>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['unit_guru_berprestasi_pct']) ?>%; background: #7c3aed;"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted);">
                            Target: <strong>5.0%</strong>
                        </div>
                    </div>

                    <!-- K-KS-15: Kepuasan Staf terhadap KS -->
                    <div style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>Kepuasan Guru &amp; Staf thd Kepala Sekolah</span>
                            <strong style="color: #059669;"><?= $autoData['unit_staff_survey_avg'] ?>%</strong>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= min(100, $autoData['unit_staff_survey_avg']) ?>%; background: #059669;"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted);">
                            Target: <strong>90%</strong>
                        </div>
                    </div>

                <!-- 11. FALLBACK JIKA PERAN LAINNYA -->
                <?php else: ?>
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>Supervisi Kerja</span>
                            <strong style="color: #0d9488;"><?= $autoData['avg_supervisi'] ?> / 100</strong>
                        </div>
                    </div>
                    <div style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500;">
                            <span>Partisipasi Kegiatan Sekolah</span>
                            <strong style="color: #8b5cf6;"><?= $autoData['kegiatan_kbm_attendance'] ?>%</strong>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- STANDAR PREDIKAT NILAI -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-award" style="color: var(--warning); margin-right: 8px;"></i>
                    Standar Predikat Nilai
                </h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="table" style="font-size: 12px;">
                    <thead>
                        <tr>
                            <th style="padding: 10px 14px;">Predikat</th>
                            <th style="padding: 10px 14px; text-align: center;">Rentang Skor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 8px 14px;"><span class="badge badge-success">A (Amat Baik)</span></td>
                            <td style="padding: 8px 14px; text-align: center; font-family: monospace;">&ge; <?= $assessment['grade_a_min'] ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 14px;"><span class="badge badge-info">B (Baik)</span></td>
                            <td style="padding: 8px 14px; text-align: center; font-family: monospace;"><?= $assessment['grade_b_min'] ?> - <?= $assessment['grade_a_min'] - 0.01 ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 14px;"><span class="badge badge-warning">C (Cukup)</span></td>
                            <td style="padding: 8px 14px; text-align: center; font-family: monospace;"><?= $assessment['grade_c_min'] ?> - <?= $assessment['grade_b_min'] - 0.01 ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 14px;"><span class="badge badge-secondary">D (Sedang)</span></td>
                            <td style="padding: 8px 14px; text-align: center; font-family: monospace;"><?= $assessment['grade_d_min'] ?> - <?= $assessment['grade_c_min'] - 0.01 ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 14px;"><span class="badge badge-danger">E (Kurang)</span></td>
                            <td style="padding: 8px 14px; text-align: center; font-family: monospace;">&lt; <?= $assessment['grade_d_min'] ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function recalcAkfiLive() {
    var maxBobotKinerja = <?= (float)$assessment['bobot_kinerja'] ?>;
    var maxBobotAbsensi = <?= (float)$assessment['bobot_absensi'] ?>;
    var totalKinerja = 0;
    var totalAbsensi = 0;
    
    document.querySelectorAll('.sup-score-input').forEach(function(input) {
        var itemId = input.dataset.itemId;
        var target = parseFloat(input.dataset.target) || 0;
        var weight = parseFloat(input.dataset.weight) || 0;
        var direction = input.dataset.direction;
        var section = input.dataset.section;
        var val = parseFloat(input.value);
        if (isNaN(val)) val = 0;
        
        var ratio = 1.0;
        if (direction === 'lower_better') {
            if (target <= 0) {
                // Target 0% (misal % Tunggakan sekolah target 0%)
                ratio = val <= 0 ? 1.0 : Math.max(0.0, 1.0 - (val / 100.0));
            } else {
                if (section === 'C') {
                    // Bagian C: Absensi & Kegiatan (Datang Terlambat, Pulang Cepat, Lupa Catat, Mangkir)
                    // Skor 0 = Poin maksimal (100%)
                    // Semakin besar nilai maka poin yang didapat semakin sedikit
                    // Batas maksimal skor = target (misal 6): jika val >= target otomatis poin 0
                    ratio = Math.max(0.0, (target - val) / target);
                } else {
                    // Target > 0 toleransi (misal Turn Over Talent target <= 5%)
                    ratio = val <= target ? 1.0 : Math.max(0.0, 1.0 - ((val - target) / target));
                }
            }
        } else {
            // Higher is better
            ratio = target <= 0 ? 1.0 : Math.min(1.0, Math.max(0.0, val / target));
        }
        
        var itemScore = Math.round(ratio * weight * 100) / 100;
        var displayEl = document.getElementById('score_display_' + itemId);
        if (displayEl) {
            displayEl.textContent = itemScore.toFixed(2);
        }
        
        if (section === 'A') {
            totalKinerja += itemScore;
        } else if (section === 'C') {
            totalAbsensi += itemScore;
        }
    });
    
    var cappedKinerja = Math.min(maxBobotKinerja, Math.round(totalKinerja * 100) / 100);
    var totalKinerjaEl = document.getElementById('liveKinerjaTotal');
    if (totalKinerjaEl) {
        totalKinerjaEl.textContent = cappedKinerja.toFixed(2);
    }
    
    var cappedAbsensi = Math.min(maxBobotAbsensi, Math.round(totalAbsensi * 100) / 100);
    var totalAbsensiEl = document.getElementById('liveAbsensiTotal');
    if (totalAbsensiEl) {
        totalAbsensiEl.textContent = cappedAbsensi.toFixed(2);
    }
}
document.addEventListener('DOMContentLoaded', recalcAkfiLive);
</script>
