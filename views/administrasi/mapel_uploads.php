<?php
// views/administrasi/mapel_uploads.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
$shortMonths = [
    1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
    5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
    9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
];
?>

<div class="page-header">
    <div>
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
            <span class="badge badge-primary">Guru Mata Pelajaran</span>
            <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Perangkat Pembelajaran &amp; Mengajar</span>
        </div>
        <h1 class="page-title">
            <i class="fa-solid fa-book-bookmark" style="color: #0d9488; margin-right: 8px;"></i>
            Upload Perangkat Pembelajaran Guru
        </h1>
        <p class="page-subtitle">
            Format pengumpulan terstruktur per bulan dan per semester untuk penghitungan ketercapaian administrasi mengajar yang akurat.
        </p>
    </div>
</div>

<!-- List of Perangkat Items with Periodic Matrix -->
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
                    <span class="badge" style="background: <?= $freq === 'setiap bulan' ? '#ecfdf5; color: #047857; border: 1px solid #a7f3d0;' : ($freq === '2x setahun' ? '#eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;' : '#fef3c7; color: #b45309; border: 1px solid #fde68a;') ?> font-weight: 600; font-size: 0.75rem;">
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

                <?php elseif ($freq === '2x setahun'): ?>
                    <!-- Kolom Semester: Semester 1 & Semester 2 -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 14px;">
                        <?php 
                        $semesters = [
                            1 => 'Semester 1 (Ganjil)',
                            2 => 'Semester 2 (Genap)'
                        ];
                        foreach ($semesters as $semNum => $semTitle): 
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
                                        <div style="font-size: 0.75rem; color: #94a3b8;">Belum ada berkas diunggah</div>
                                    <?php endif; ?>
                                </div>
                                <div style="display: flex; gap: 6px;">
                                    <?php if ($up): ?>
                                        <a href="<?= $baseUrl ?>/administrasi/download/<?= $up['id'] ?>" class="btn btn-outline btn-sm" style="padding: 6px 10px;" title="Unduh Berkas">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                        <button type="button" class="btn btn-primary btn-sm" style="padding: 6px 12px; font-size: 0.8rem;" onclick='openPeriodModal(<?= json_encode($it) ?>, <?= $semNum ?>, "<?= $semTitle ?>")'>
                                            <i class="fa-solid fa-upload"></i> Ganti
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-primary btn-sm" style="padding: 6px 14px; font-size: 0.8rem;" onclick='openPeriodModal(<?= json_encode($it) ?>, <?= $semNum ?>, "<?= $semTitle ?>")'>
                                            <i class="fa-solid fa-upload"></i> Unggah
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php else: ?>
                    <!-- Kolom 1x Setahun (Tahunan) -->
                    <?php 
                        $up = $itemUploads[1] ?? null;
                        $isApp = ($up && $up['status'] === 'Disetujui');
                        $isPen = ($up && $up['status'] === 'Menunggu Review');
                        $isRev = ($up && $up['status'] === 'Revisi');
                    ?>
                    <div style="border-radius: 8px; padding: 14px 16px; border: 1px solid; display: flex; justify-content: space-between; align-items: center; max-width: 500px;
                                <?= $isApp ? 'background: #f0fdf4; border-color: #86efac;' : ($isPen ? 'background: #fffbeb; border-color: #fde68a;' : ($isRev ? 'background: #fef2f2; border-color: #fca5a5;' : 'background: #fafafa; border-color: #e2e8f0; border-style: dashed;')) ?>">
                        <div>
                            <div style="font-weight: 700; font-size: 0.875rem; color: #0f172a; margin-bottom: 4px;">
                                <i class="fa-solid fa-calendar-check" style="color: #0d9488; margin-right: 4px;"></i> Dokumen 1x Setahun (Tahunan)
                            </div>
                            <?php if ($up): ?>
                                <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 4px;">
                                    <?= SecurityHelper::escape($up['original_filename']) ?> (v<?= $up['version_num'] ?>)
                                </div>
                                <span class="badge <?= $isApp ? 'badge-success' : ($isPen ? 'badge-warning' : 'badge-danger') ?>" style="font-size: 0.7rem;">
                                    <?= $up['status'] ?>
                                </span>
                            <?php else: ?>
                                <div style="font-size: 0.75rem; color: #94a3b8;">Belum ada berkas diunggah</div>
                            <?php endif; ?>
                        </div>
                        <div style="display: flex; gap: 6px;">
                            <?php if ($up): ?>
                                <a href="<?= $baseUrl ?>/administrasi/download/<?= $up['id'] ?>" class="btn btn-outline btn-sm" style="padding: 6px 10px;" title="Unduh Berkas">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                                <button type="button" class="btn btn-primary btn-sm" style="padding: 6px 12px; font-size: 0.8rem;" onclick='openPeriodModal(<?= json_encode($it) ?>, 1, "Dokumen Tahunan")'>
                                    <i class="fa-solid fa-upload"></i> Ganti
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-primary btn-sm" style="padding: 6px 14px; font-size: 0.8rem;" onclick='openPeriodModal(<?= json_encode($it) ?>, 1, "Dokumen Tahunan")'>
                                    <i class="fa-solid fa-upload"></i> Unggah
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modal Upload Dokumen Per Periode -->
<div class="modal-overlay" id="uploadModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div class="modal-container" style="background: white; border-radius: 12px; width: 100%; max-width: 520px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); overflow: hidden;">
        <form method="POST" action="<?= $baseUrl ?>/administrasi/upload" enctype="multipart/form-data">
            <?= CSRF::field() ?>
            <input type="hidden" name="jenis_admin_id" id="upload_jenis_id">
            <input type="hidden" name="month_num" id="upload_month_num" value="1">
            
            <div class="modal-header" style="padding: 16px 20px; border-bottom: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
                <div>
                    <h3 class="card-title" id="uploadModalTitle" style="margin: 0; font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-cloud-arrow-up" style="color: #0d9488;"></i> Unggah Perangkat Pembelajaran
                    </h3>
                    <div id="uploadModalPeriodInfo" style="font-size: 0.8rem; font-weight: 600; color: #0d9488; margin-top: 2px;"></div>
                </div>
                <button type="button" class="btn btn-outline btn-sm" style="border: none; font-size: 1.25rem; line-height: 1; padding: 4px 8px;" onclick="closePeriodModal()">&times;</button>
            </div>

            <div class="modal-body" style="padding: 20px;">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Keterangan / Nama Mapel &amp; Kelas</label>
                    <input type="text" name="sub_title" class="form-control" placeholder="Contoh: Modul Ajar Matematika Kelas 1A">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Pilih Berkas Dokumen (PDF, Excel, Word, ZIP - Max 10MB) <span style="color: #ef4444;">*</span></label>
                    <input type="file" name="file" class="form-control" required accept=".pdf,.xlsx,.xls,.docx,.doc,.zip,.rar">
                </div>

                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px; font-size: 0.8rem; color: #166534; display: flex; align-items: flex-start; gap: 8px;">
                    <i class="fa-solid fa-circle-check" style="margin-top: 2px;"></i>
                    <div>Berkas perangkat akan diverifikasi langsung oleh Kepala Sekolah / Waka Kurikulum.</div>
                </div>
            </div>

            <div class="modal-footer" style="padding: 14px 20px; border-top: 1px solid var(--border-color, #e2e8f0); background: #f8fafc; display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="btn btn-outline" onclick="closePeriodModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="padding: 8px 20px;">
                    <i class="fa-solid fa-arrow-up-from-bracket" style="margin-right: 6px;"></i> Simpan &amp; Unggah
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openPeriodModal(item, periodNum, periodLabel) {
    document.getElementById('upload_jenis_id').value = item.id;
    document.getElementById('upload_month_num').value = periodNum;
    document.getElementById('uploadModalTitle').innerHTML = '<i class="fa-solid fa-cloud-arrow-up" style="color: #0d9488;"></i> ' + escapeHtml(item.title);
    document.getElementById('uploadModalPeriodInfo').textContent = 'Target Periode: ' + periodLabel;

    const modal = document.getElementById('uploadModal');
    modal.style.display = 'flex';
}

function closePeriodModal() {
    const modal = document.getElementById('uploadModal');
    modal.style.display = 'none';
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>
