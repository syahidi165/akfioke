<?php
// views/administrasi/tu_uploads.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
$shortMonths = [
    1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
    5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
    9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
];

$totalSlotsAll = 0;
$totalApprovedAll = 0;
$totalUploadedAll = 0;

foreach ($masterItems as $it) {
    $freq = strtolower($it['frequency']);
    $slots = match($freq) {
        'setiap bulan' => 12,
        '2x setahun'   => 2,
        '3x setahun'   => 3,
        default        => 1
    };
    $totalSlotsAll += $slots;

    $itemUploads = $uploadsByJenisMonth[$it['id']] ?? [];
    for ($s = 1; $s <= $slots; $s++) {
        if (isset($itemUploads[$s])) {
            $totalUploadedAll++;
            if ($itemUploads[$s]['status'] === 'Disetujui') {
                $totalApprovedAll++;
            }
        }
    }
}

$overallPct = $totalSlotsAll > 0 ? round(($totalApprovedAll / $totalSlotsAll) * 100) : 0;
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div>
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
            <span class="badge badge-primary">Tata Usaha</span>
            <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Arsip Dokumen Keuangan &amp; Operasional</span>
        </div>
        <h1 class="page-title" style="margin: 0 0 6px 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-cloud-arrow-up" style="color: #0d9488;"></i> Perangkat Administrasi &amp; Laporan Tata Usaha
        </h1>
        <p class="page-subtitle" style="margin: 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
            Matriks status pengumpulan berkas bulanan, semesteran, dan tahunan yang diverifikasi langsung oleh Kepala Sekolah untuk indikator AKFI TU.
        </p>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0d9488;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Target Berkas Periode</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-main, #0f172a); margin: 6px 0 2px 0;">
            <?= $totalSlotsAll ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Slot</span>
        </div>
        <div style="font-size: 0.8125rem; color: var(--text-muted, #64748b);"><?= count($masterItems) ?> Dokumen Utama TU</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #10b981;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Disetujui Kepsek</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #10b981; margin: 6px 0 2px 0;">
            <?= $totalApprovedAll ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Berkas</span>
        </div>
        <div style="font-size: 0.8125rem; color: #10b981; font-weight: 500;">Valid dan terverifikasi</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #f59e0b;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Telah Diunggah</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #f59e0b; margin: 6px 0 2px 0;">
            <?= $totalUploadedAll ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Berkas</span>
        </div>
        <div style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Menunggu &amp; disetujui</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0284c7;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Capaian Kelengkapan</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #0284c7; margin: 6px 0 2px 0;">
            <?= $overallPct ?>%
        </div>
        <div style="font-size: 0.8125rem; color: #0284c7; font-weight: 500;">Ketercapaian AKFI TU</div>
    </div>
</div>

<!-- List of Master Administrasi Items with Periodic Matrix -->
<div style="display: flex; flex-direction: column; gap: 20px;">
    <?php foreach ($masterItems as $it): 
        $freq = strtolower($it['frequency']);
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
        $pct = $totalSlots > 0 ? round(($approvedCount / $totalSlots) * 100) : 0;
    ?>
        <div class="card" style="border: 1px solid var(--border-color, #e2e8f0); border-radius: 10px; overflow: hidden;">
            <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="badge badge-secondary" style="font-weight: 700; font-size: 0.8rem; background: #e2e8f0; color: #334155;">
                        <?= SecurityHelper::escape($it['code']) ?>
                    </span>
                    <div>
                        <h3 style="margin: 0; font-size: 1rem; font-weight: 700; color: var(--text-main, #0f172a);">
                            <?= SecurityHelper::escape($it['title']) ?>
                        </h3>
                        <div style="font-size: 0.75rem; color: var(--text-muted, #64748b); margin-top: 2px;">
                            Batas Waktu: <?= SecurityHelper::escape($it['due_info']) ?> | Frekuensi: <span class="badge badge-info" style="font-size: 0.7rem;"><?= SecurityHelper::escape($it['frequency']) ?></span>
                        </div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="text-align: right;">
                        <span style="font-size: 0.8125rem; font-weight: 600; color: #0f172a;"><?= $approvedCount ?>/<?= $totalSlots ?> Disetujui</span>
                        <div style="font-size: 0.75rem; color: var(--text-muted, #64748b);"><?= $pct ?>% Tuntas</div>
                    </div>
                    <div style="width: 80px; height: 8px; background: #e2e8f0; border-radius: 999px; overflow: hidden;">
                        <div style="width: <?= $pct ?>%; height: 100%; background: <?= $pct >= 100 ? '#10b981' : ($pct >= 50 ? '#0284c7' : '#f59e0b') ?>;"></div>
                    </div>
                </div>
            </div>

            <div class="card-body" style="padding: 16px 20px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 12px;">
                    <?php for ($slot = 1; $slot <= $totalSlots; $slot++): 
                        $slotName = match ($totalSlots) {
                            12 => $shortMonths[$slot] ?? "Bln $slot",
                            2  => "Semester $slot",
                            3  => "Term $slot",
                            default => "Tahunan"
                        };
                        
                        $up = $itemUploads[$slot] ?? null;
                        $status = $up['status'] ?? 'Belum Upload';

                        $slotBorder = '#e2e8f0';
                        $slotBg = '#f8fafc';
                        $statusBadgeClass = 'badge-secondary';
                        $statusText = 'Belum Ada';

                        if ($status === 'Disetujui') {
                            $slotBorder = '#bbf7d0';
                            $slotBg = '#f0fdf4';
                            $statusBadgeClass = 'badge-success';
                            $statusText = 'Disetujui';
                        } elseif ($status === 'Menunggu Review') {
                            $slotBorder = '#fed7aa';
                            $slotBg = '#fff7ed';
                            $statusBadgeClass = 'badge-warning';
                            $statusText = 'Review';
                        } elseif ($status === 'Revisi') {
                            $slotBorder = '#fecaca';
                            $slotBg = '#fef2f2';
                            $statusBadgeClass = 'badge-danger';
                            $statusText = 'Revisi';
                        }
                    ?>
                        <div style="border: 1px solid <?= $slotBorder ?>; background: <?= $slotBg ?>; border-radius: 8px; padding: 10px 12px; display: flex; flex-direction: column; justify-content: space-between; gap: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <strong style="font-size: 0.8125rem; color: #1e293b;"><?= $slotName ?></strong>
                                <span class="badge <?= $statusBadgeClass ?>" style="font-size: 0.65rem; padding: 2px 6px;">
                                    <?= $statusText ?>
                                </span>
                            </div>

                            <?php if ($up): ?>
                                <div style="font-size: 0.72rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= SecurityHelper::escape($up['original_filename']) ?>">
                                    <i class="fa-solid fa-file" style="margin-right: 2px;"></i> <?= SecurityHelper::escape($up['original_filename']) ?>
                                </div>
                            <?php else: ?>
                                <div style="font-size: 0.72rem; color: #94a3b8; font-style: italic;">
                                    Belum diunggah
                                </div>
                            <?php endif; ?>

                            <div style="display: flex; gap: 4px; margin-top: 2px;">
                                <?php if ($up): ?>
                                    <a href="<?= $baseUrl ?>/administrasi/download/<?= $up['id'] ?>" class="btn btn-outline btn-sm" style="flex: 1; justify-content: center; padding: 4px; font-size: 0.75rem;" title="Unduh Berkas">
                                        <i class="fa-solid fa-download"></i>
                                    </a>
                                <?php endif; ?>
                                <button type="button" class="btn btn-primary btn-sm" style="flex: 2; justify-content: center; padding: 4px; font-size: 0.75rem;" onclick='openUploadSlotModal(<?= json_encode($it) ?>, <?= $slot ?>, "<?= $slotName ?>")'>
                                    <i class="fa-solid fa-upload"></i> <?= $up ? 'Ganti' : 'Unggah' ?>
                                </button>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modal Upload Dokumen TU -->
<div class="modal-overlay" id="uploadModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 1050; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div class="modal-container" style="background: #fff; width: 100%; max-width: 500px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden;">
        <form method="POST" action="<?= $baseUrl ?>/administrasi/upload" enctype="multipart/form-data">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="jenis_admin_id" id="upload_jenis_id">
            <input type="hidden" name="month_num" id="upload_month_num">
            
            <div class="modal-header" style="padding: 16px 20px; border-bottom: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
                <div>
                    <h3 class="modal-title" id="uploadModalTitle" style="margin: 0; font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-cloud-arrow-up" style="color: #0d9488;"></i> Unggah Berkas Tata Usaha
                    </h3>
                    <div id="uploadModalSubtitle" style="font-size: 0.8125rem; color: #64748b; margin-top: 2px;"></div>
                </div>
                <button type="button" class="btn btn-outline btn-sm" style="border: none; font-size: 1.25rem; line-height: 1; padding: 4px 8px;" onclick="closeUploadModal()">&times;</button>
            </div>

            <div class="modal-body" style="padding: 20px;">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Keterangan / Judul Berkas</label>
                    <input type="text" name="sub_title" id="upload_sub_title" class="form-control" placeholder="Contoh: Laporan Keuangan SPP Bulan Januari">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Pilih Berkas Dokumen (PDF, XLSX, DOCX) <span style="color: #ef4444;">*</span></label>
                    <input type="file" name="file" class="form-control" required accept=".pdf,.xlsx,.xls,.docx,.doc,.jpg,.png">
                    <small style="color: var(--text-muted, #64748b); font-size: 0.75rem;">Maksimal 10MB per berkas.</small>
                </div>
            </div>

            <div class="modal-footer" style="padding: 14px 20px; border-top: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: flex-end; gap: 8px; background: #f8fafc;">
                <button type="button" class="btn btn-outline" onclick="closeUploadModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 600;">
                    <i class="fa-solid fa-cloud-arrow-up" style="margin-right: 6px;"></i> Mulai Unggah
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openUploadSlotModal(it, slot, slotName) {
    document.getElementById('upload_jenis_id').value = it.id;
    document.getElementById('upload_month_num').value = slot;
    document.getElementById('uploadModalTitle').innerHTML = '<i class="fa-solid fa-cloud-arrow-up" style="color: #0d9488;"></i> Unggah: ' + it.title;
    document.getElementById('uploadModalSubtitle').innerText = 'Periode Target: ' + slotName;
    document.getElementById('upload_sub_title').value = it.title + ' - ' + slotName;
    document.getElementById('uploadModal').style.display = 'flex';
}

function closeUploadModal() {
    document.getElementById('uploadModal').style.display = 'none';
}
</script>
