<?php
// views/administrasi/ks_uploads.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
$shortMonths = [
    1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
    5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
    9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
];

$stages = [
    1 => 'Tahap 1 (Awal Tahun)',
    2 => 'Tahap 2 (Tengah Tahun)',
    3 => 'Tahap 3 (Akhir Tahun)'
];

$semesters = [
    1 => 'Semester 1 (Ganjil)',
    2 => 'Semester 2 (Genap)'
];

// Hitung total slot & capaian riil
$totalAllSlots = 0;
$totalApprovedSlots = 0;
$totalUploadedSlots = 0;

foreach ($masterItems as $it) {
    $freq = $it['frequency'];
    $slots = match ($freq) {
        'setiap bulan' => 12,
        '2x setahun'   => 2,
        '3x setahun'   => 3,
        default        => 1
    };
    $totalAllSlots += $slots;

    $itemUploads = $uploadsByJenisMonth[$it['id']] ?? [];
    for ($s = 1; $s <= $slots; $s++) {
        if (isset($itemUploads[$s])) {
            $totalUploadedSlots++;
            if ($itemUploads[$s]['status'] === 'Disetujui') {
                $totalApprovedSlots++;
            }
        }
    }
}
$overallPct = $totalAllSlots > 0 ? round(($totalApprovedSlots / $totalAllSlots) * 100, 1) : 0;
?>

<div class="page-header">
    <div>
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
            <span class="badge badge-primary">Kepala Sekolah</span>
            <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Pengumpulan Dokumen &amp; Arsip Sekolah</span>
        </div>
        <h1 class="page-title">
            <i class="fa-solid fa-folder-tree" style="color: #0d9488; margin-right: 8px;"></i>
            Dokumen Administrasi Kepala Sekolah (18 Item)
        </h1>
        <p class="page-subtitle">
            Format pengumpulan periodik terstruktur (Tahunan, Semester, 3 Tahap Baseline, &amp; 12 Bulan) untuk akurasi AKFI Indikator K-KS-01 &amp; K-KS-02.
        </p>
    </div>
</div>

<!-- Ringkasan Statistik Slot Dokumen -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon" style="background: #e0f2fe; color: #0284c7;">
            <i class="fa-solid fa-folder-closed"></i>
        </div>
        <div>
            <div class="stat-label">Total Item Dokumen</div>
            <div class="stat-val"><?= count($masterItems) ?> <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">Item</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #ede9fe; color: #7c3aed;">
            <i class="fa-solid fa-cubes-stacked"></i>
        </div>
        <div>
            <div class="stat-label">Total Slot Pengumpulan</div>
            <div class="stat-val"><?= $totalAllSlots ?> <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">Slot Wajib</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #fef3c7; color: #d97706;">
            <i class="fa-solid fa-cloud-arrow-up"></i>
        </div>
        <div>
            <div class="stat-label">Slot Terunggah</div>
            <div class="stat-val"><?= $totalUploadedSlots ?> <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">/ <?= $totalAllSlots ?></span></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #dcfce7; color: #16a34a;">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div>
            <div class="stat-label">Slot Disetujui (AKFI)</div>
            <div class="stat-val" style="color: #16a34a;"><?= $totalApprovedSlots ?> <span style="font-size: 13px; font-weight: 600; color: #16a34a;">(<?= $overallPct ?>%)</span></div>
        </div>
    </div>
</div>

<!-- List of Administrasi Items with Periodic Matrix -->
<div style="display: flex; flex-direction: column; gap: 20px;">
    <?php foreach ($masterItems as $it): 
        $freq = $it['frequency'];
        $itemUploads = $uploadsByJenisMonth[$it['id']] ?? [];
        
        $totalSlots = match ($freq) {
            'setiap bulan' => 12,
            '2x setahun'   => 2,
            '3x setahun'   => 3,
            default        => 1
        };

        $approvedCount = 0;
        $uploadedCount = 0;
        for ($slot = 1; $slot <= $totalSlots; $slot++) {
            if (isset($itemUploads[$slot])) {
                $uploadedCount++;
                if ($itemUploads[$slot]['status'] === 'Disetujui') {
                    $approvedCount++;
                }
            }
        }
        $pct = round(($approvedCount / $totalSlots) * 100);
    ?>
        <div class="card" style="border: 1px solid var(--border-color, #e2e8f0); border-radius: 10px; overflow: hidden;">
            <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="badge badge-secondary" style="font-weight: 700; font-size: 0.8rem; background: #e2e8f0; color: #334155;">
                        <?= SecurityHelper::escape($it['code']) ?>
                    </span>
                    <h3 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--text-main, #0f172a);">
                        <?= SecurityHelper::escape($it['title']) ?>
                    </h3>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="badge" style="background: <?= $freq === 'setiap bulan' ? '#ecfdf5; color: #047857; border: 1px solid #a7f3d0;' : ($freq === '2x setahun' ? '#eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;' : ($freq === '3x setahun' ? '#f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe;' : '#fef3c7; color: #b45309; border: 1px solid #fde68a;')) ?> font-weight: 600; font-size: 0.75rem;">
                        <i class="fa-solid fa-clock-rotate-left" style="margin-right: 4px;"></i> <?= ucfirst($freq) ?>
                    </span>
                    <span style="font-size: 0.8rem; font-weight: 600; color: <?= $approvedCount === $totalSlots ? '#10b981' : '#64748b' ?>;">
                        <?= $approvedCount ?> / <?= $totalSlots ?> Disetujui (<?= $pct ?>%)
                    </span>
                </div>
            </div>

            <div class="card-body" style="padding: 18px 20px;">
                <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); margin-bottom: 14px;">
                    <i class="fa-solid fa-circle-info" style="margin-right: 4px;"></i> Batas Waktu: <strong><?= SecurityHelper::escape($it['due_info']) ?></strong>
                </div>

                <?php if ($freq === 'setiap bulan'): ?>
                    <!-- Kolom Bulanan: 12 Bulan Grid -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(78px, 1fr)); gap: 8px;">
                        <?php for ($m = 1; $m <= 12; $m++): 
                            $up = $itemUploads[$m] ?? null;
                            $isApp = ($up && $up['status'] === 'Disetujui');
                            $isPen = ($up && $up['status'] === 'Menunggu Review');
                            $isRev = ($up && $up['status'] === 'Revisi');
                        ?>
                            <div style="border-radius: 8px; padding: 8px 6px; text-align: center; border: 1px solid; display: flex; flex-direction: column; justify-content: space-between; min-height: 82px;
                                        <?= $isApp ? 'background: #f0fdf4; border-color: #86efac;' : ($isPen ? 'background: #fffbeb; border-color: #fde68a;' : ($isRev ? 'background: #fef2f2; border-color: #fca5a5;' : 'background: #fafafa; border-color: #e2e8f0; border-style: dashed;')) ?>">
                                <div style="font-size: 0.75rem; font-weight: 700; color: #334155;"><?= $shortMonths[$m] ?></div>
                                
                                <div style="margin: 4px 0;">
                                    <?php if ($isApp): ?>
                                        <span style="font-size: 0.7rem; font-weight: 700; color: #16a34a; display: block;">
                                            <i class="fa-solid fa-check"></i> Disetujui
                                        </span>
                                    <?php elseif ($isPen): ?>
                                        <span style="font-size: 0.7rem; font-weight: 600; color: #d97706; display: block;">
                                            <i class="fa-solid fa-clock"></i> Review
                                        </span>
                                    <?php elseif ($isRev): ?>
                                        <span style="font-size: 0.7rem; font-weight: 600; color: #dc2626; display: block;">
                                            <i class="fa-solid fa-rotate-left"></i> Revisi
                                        </span>
                                    <?php else: ?>
                                        <span style="font-size: 0.7rem; color: #94a3b8; display: block;">Kosong</span>
                                    <?php endif; ?>
                                </div>

                                <div style="display: flex; gap: 4px; justify-content: center; align-items: center;">
                                    <?php if ($up): ?>
                                        <a href="<?= $baseUrl ?>/administrasi/download/<?= $up['id'] ?>" class="btn btn-outline btn-sm" style="padding: 2px 6px; font-size: 0.7rem;" title="<?= SecurityHelper::escape($up['original_filename']) ?>">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline btn-sm" style="padding: 2px 6px; font-size: 0.7rem;" title="Upload Ulang" onclick='openPeriodModal(<?= json_encode($it) ?>, <?= $m ?>, "Bulan <?= $shortMonths[$m] ?>")'>
                                            <i class="fa-solid fa-arrow-up-from-bracket"></i>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-outline btn-sm" style="padding: 2px 8px; font-size: 0.75rem; border-color: #cbd5e1; color: #0d9488;" onclick='openPeriodModal(<?= json_encode($it) ?>, <?= $m ?>, "Bulan <?= $shortMonths[$m] ?>")'>
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>

                <?php elseif ($freq === '3x setahun'): ?>
                    <!-- Kolom 3 Tahap Baseline -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px;">
                        <?php foreach ($stages as $stageNum => $stageTitle): 
                            $up = $itemUploads[$stageNum] ?? null;
                            $isApp = ($up && $up['status'] === 'Disetujui');
                            $isPen = ($up && $up['status'] === 'Menunggu Review');
                            $isRev = ($up && $up['status'] === 'Revisi');
                        ?>
                            <div style="border-radius: 8px; padding: 14px 16px; border: 1px solid; display: flex; justify-content: space-between; align-items: center;
                                        <?= $isApp ? 'background: #f0fdf4; border-color: #86efac;' : ($isPen ? 'background: #fffbeb; border-color: #fde68a;' : ($isRev ? 'background: #fef2f2; border-color: #fca5a5;' : 'background: #fafafa; border-color: #e2e8f0; border-style: dashed;')) ?>">
                                <div>
                                    <div style="font-weight: 700; font-size: 0.875rem; color: #0f172a; margin-bottom: 4px;">
                                        <i class="fa-solid fa-chart-line" style="color: #7c3aed; margin-right: 4px;"></i> <?= $stageTitle ?>
                                    </div>
                                    <?php if ($up): ?>
                                        <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 4px;">
                                            <?= SecurityHelper::escape($up['original_filename']) ?> (v<?= $up['version_num'] ?>)
                                        </div>
                                        <span class="badge <?= $isApp ? 'badge-success' : ($isPen ? 'badge-warning' : 'badge-danger') ?>" style="font-size: 0.7rem;">
                                            <?= $up['status'] ?>
                                        </span>
                                    <?php else: ?>
                                        <div style="font-size: 0.75rem; color: #94a3b8; font-style: italic;">Belum ada berkas</div>
                                    <?php endif; ?>
                                </div>
                                <div style="display: flex; gap: 6px;">
                                    <?php if ($up): ?>
                                        <a href="<?= $baseUrl ?>/administrasi/download/<?= $up['id'] ?>" class="btn btn-outline btn-sm" title="Unduh Berkas">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline btn-sm" onclick='openPeriodModal(<?= json_encode($it) ?>, <?= $stageNum ?>, "<?= $stageTitle ?>")' title="Unggah Ulang">
                                            <i class="fa-solid fa-arrow-up-from-bracket"></i>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-primary btn-sm" onclick='openPeriodModal(<?= json_encode($it) ?>, <?= $stageNum ?>, "<?= $stageTitle ?>")'>
                                            <i class="fa-solid fa-upload"></i> Unggah
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php elseif ($freq === '2x setahun'): ?>
                    <!-- Kolom Semester: Semester 1 & Semester 2 -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 14px;">
                        <?php foreach ($semesters as $semNum => $semTitle): 
                            $up = $itemUploads[$semNum] ?? null;
                            $isApp = ($up && $up['status'] === 'Disetujui');
                            $isPen = ($up && $up['status'] === 'Menunggu Review');
                            $isRev = ($up && $up['status'] === 'Revisi');
                        ?>
                            <div style="border-radius: 8px; padding: 14px 16px; border: 1px solid; display: flex; justify-content: space-between; align-items: center;
                                        <?= $isApp ? 'background: #f0fdf4; border-color: #86efac;' : ($isPen ? 'background: #fffbeb; border-color: #fde68a;' : ($isRev ? 'background: #fef2f2; border-color: #fca5a5;' : 'background: #fafafa; border-color: #e2e8f0; border-style: dashed;')) ?>">
                                <div>
                                    <div style="font-weight: 700; font-size: 0.875rem; color: #0f172a; margin-bottom: 4px;">
                                        <i class="fa-solid fa-graduation-cap" style="color: #0d9488; margin-right: 4px;"></i> <?= $semTitle ?>
                                    </div>
                                    <?php if ($up): ?>
                                        <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 4px;">
                                            <?= SecurityHelper::escape($up['original_filename']) ?> (v<?= $up['version_num'] ?>)
                                        </div>
                                        <span class="badge <?= $isApp ? 'badge-success' : ($isPen ? 'badge-warning' : 'badge-danger') ?>" style="font-size: 0.7rem;">
                                            <?= $up['status'] ?>
                                        </span>
                                    <?php else: ?>
                                        <div style="font-size: 0.75rem; color: #94a3b8; font-style: italic;">Belum ada berkas</div>
                                    <?php endif; ?>
                                </div>
                                <div style="display: flex; gap: 6px;">
                                    <?php if ($up): ?>
                                        <a href="<?= $baseUrl ?>/administrasi/download/<?= $up['id'] ?>" class="btn btn-outline btn-sm" title="Unduh Berkas">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline btn-sm" onclick='openPeriodModal(<?= json_encode($it) ?>, <?= $semNum ?>, "<?= $semTitle ?>")' title="Unggah Ulang">
                                            <i class="fa-solid fa-arrow-up-from-bracket"></i>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-primary btn-sm" onclick='openPeriodModal(<?= json_encode($it) ?>, <?= $semNum ?>, "<?= $semTitle ?>")'>
                                            <i class="fa-solid fa-upload"></i> Unggah
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php else: ?>
                    <!-- Dokumen Tahunan (1x Setahun) -->
                    <?php 
                        $up = $itemUploads[1] ?? null;
                        $isApp = ($up && $up['status'] === 'Disetujui');
                        $isPen = ($up && $up['status'] === 'Menunggu Review');
                        $isRev = ($up && $up['status'] === 'Revisi');
                    ?>
                    <div style="border-radius: 8px; padding: 14px 16px; border: 1px solid; display: flex; justify-content: space-between; align-items: center;
                                <?= $isApp ? 'background: #f0fdf4; border-color: #86efac;' : ($isPen ? 'background: #fffbeb; border-color: #fde68a;' : ($isRev ? 'background: #fef2f2; border-color: #fca5a5;' : 'background: #fafafa; border-color: #e2e8f0; border-style: dashed;')) ?>">
                        <div>
                            <div style="font-weight: 700; font-size: 0.875rem; color: #0f172a; margin-bottom: 4px;">
                                <i class="fa-solid fa-file-lines" style="color: #0d9488; margin-right: 4px;"></i> Berkas Arsip Tahunan
                            </div>
                            <?php if ($up): ?>
                                <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 4px;">
                                    <?= SecurityHelper::escape($up['original_filename']) ?> (v<?= $up['version_num'] ?> - <?= round($up['file_size']/1024) ?> KB)
                                </div>
                                <span class="badge <?= $isApp ? 'badge-success' : ($isPen ? 'badge-warning' : 'badge-danger') ?>" style="font-size: 0.7rem;">
                                    <?= $up['status'] ?>
                                </span>
                                <?php if (!empty($up['review_notes'])): ?>
                                    <div style="font-size: 0.75rem; color: #ef4444; margin-top: 4px;">
                                        Catatan Review: <?= SecurityHelper::escape($up['review_notes']) ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div style="font-size: 0.75rem; color: #94a3b8; font-style: italic;">Belum diunggah untuk tahun ajaran ini</div>
                            <?php endif; ?>
                        </div>
                        <div style="display: flex; gap: 6px;">
                            <?php if ($up): ?>
                                <a href="<?= $baseUrl ?>/administrasi/download/<?= $up['id'] ?>" class="btn btn-outline btn-sm" title="Unduh Berkas">
                                    <i class="fa-solid fa-download"></i> Unduh
                                </a>
                                <button type="button" class="btn btn-outline btn-sm" onclick='openPeriodModal(<?= json_encode($it) ?>, 1, "Tahunan")' title="Unggah Ulang">
                                    <i class="fa-solid fa-arrow-up-from-bracket"></i> Unggah Ulang
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-primary btn-sm" onclick='openPeriodModal(<?= json_encode($it) ?>, 1, "Tahunan")'>
                                    <i class="fa-solid fa-upload"></i> Unggah Berkas
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modal Upload Dokumen Periodik -->
<div class="modal-overlay" id="uploadPeriodModal">
    <div class="modal-container" style="max-width: 520px;">
        <form method="POST" action="<?= $baseUrl ?>/administrasi/upload" enctype="multipart/form-data">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="jenis_admin_id" id="modal_jenis_id">
            <input type="hidden" name="month_num" id="modal_month_num">
            
            <div class="modal-header">
                <div>
                    <h3 class="card-title" id="modalDocTitle" style="font-size: 1rem; margin-bottom: 2px;">Unggah Dokumen Administrasi</h3>
                    <div style="font-size: 0.8rem; color: #0d9488; font-weight: 600;" id="modalPeriodLabel">Periode: -</div>
                </div>
                <button type="button" class="btn btn-outline btn-sm" onclick="closePeriodModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Keterangan / Catatan Tambahan (Opsional)</label>
                    <input type="text" name="sub_title" id="modal_sub_title" class="form-control" placeholder="Contoh: Dokumen Final / Revisi ke-2">
                </div>

                <div class="form-group">
                    <label class="form-label">Pilih Berkas Dokumen (PDF, XLSX, DOCX, JPG, maks 10MB) *</label>
                    <input type="file" name="file" class="form-control" required accept=".pdf,.xlsx,.xls,.docx,.doc,.jpg,.png">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closePeriodModal()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-cloud-arrow-up"></i> Unggah Berkas</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPeriodModal(item, periodNum, periodLabel) {
    document.getElementById('modal_jenis_id').value = item.id;
    document.getElementById('modal_month_num').value = periodNum;
    document.getElementById('modalDocTitle').innerText = item.title;
    document.getElementById('modalPeriodLabel').innerText = 'Target Periode: ' + periodLabel;
    document.getElementById('modal_sub_title').value = '';
    document.getElementById('uploadPeriodModal').classList.add('active');
}

function closePeriodModal() {
    document.getElementById('uploadPeriodModal').classList.remove('active');
}
</script>
