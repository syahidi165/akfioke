<?php
// views/deputi/deadlines_ks.php
use Core\SecurityHelper;

$now = time();
?>

<div class="page-header" style="margin-bottom: 20px;">
    <div>
        <h1 class="page-title" style="display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-clock-rotate-left" style="color: var(--primary);"></i>
            Atur Batas Waktu (Due Date) Administrasi Kepala Sekolah
        </h1>
        <div class="page-subtitle">
            Unit Binaan: <strong><?= SecurityHelper::escape($unit['name']) ?></strong> &bull;
            Keterlambatan atau dokumen belum diserahkan langsung memengaruhi <strong>Penilaian AKFI KS</strong> dan <strong>Rapor AKFI Deputi (Indikator 3.1)</strong>.
        </div>
    </div>
    <?php if (!empty($units) && count($units) > 1): ?>
        <div>
            <form method="GET" action="" style="display: flex; align-items: center; gap: 8px;">
                <label class="form-label mb-0" style="font-size: 13px; font-weight: 600; white-space: nowrap; margin-bottom: 0;">Pilih Unit Binaan:</label>
                <select name="unit_id" class="header-select" onchange="this.form.submit()" style="padding: 7px 14px; border-radius: 8px; border: 1px solid var(--border-color); background: #ffffff; font-weight: 600; font-size: 13px; color: var(--text-main); cursor: pointer;">
                    <?php foreach ($units as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= $u['id'] == $selectedUnitId ? 'selected' : '' ?>>
                            <?= SecurityHelper::escape($u['code'] . ' - ' . $u['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    <?php endif; ?>
</div>

<!-- STAT CARDS (4 KOLOM DENGAN APP.CSS NATIVE) -->
<div class="grid-cols-4" style="margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fa-solid fa-folder-closed"></i>
        </div>
        <div>
            <div class="stat-value"><?= $totalDocs ?></div>
            <div class="stat-label">Dokumen Wajib KS</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon emerald">
            <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div>
            <div class="stat-value"><?= $totalDeadlinesSet ?> / <?= $totalDocs ?></div>
            <div class="stat-label">Batas Waktu Terpasang</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon emerald">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div>
            <div class="stat-value" style="color: #10b981;"><?= $totalSubmittedOnTime ?></div>
            <div class="stat-label">Terkumpul Tepat Waktu</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon rose">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div>
            <div class="stat-value" style="color: #ef4444;"><?= $totalLateOrOverdue ?></div>
            <div class="stat-label">Lewat Batas / Penalti</div>
        </div>
    </div>
</div>

<!-- QUICK BATCH SETTER BOX -->
<div class="card" style="margin-bottom: 20px; background: #ffffff; border: 1px dashed #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
    <div class="card-body" style="padding: 16px 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: #e0f2fe; color: #0369a1; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </div>
                <div>
                    <strong style="font-size: 14px; color: var(--text-main);">Atur Batas Waktu Cepat (Batch Setter)</strong>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                        Terapkan tanggal dan jam yang sama ke seluruh 18 instrumen di bawah secara otomatis.
                    </div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <input type="datetime-local" id="batchDateTimeInput" style="padding: 7px 12px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 13px; color: var(--text-main);" value="<?= date('Y-m-d\T23:59', strtotime('+14 days')) ?>">
                <button type="button" class="btn btn-outline" id="btnApplyBatch" style="padding: 7px 14px; font-weight: 600; font-size: 12px;">
                    <i class="fa-solid fa-angles-down"></i> Terapkan ke Semua Baris
                </button>
            </div>
        </div>
    </div>
</div>

<!-- FORM UTAMA PENGATURAN DUE DATE -->
<form method="POST" action="<?= SecurityHelper::baseUrl() ?>/deputi/deadlines-ks/save" id="formDeadlines">
    <input type="hidden" name="unit_id" value="<?= $selectedUnitId ?>">

    <div class="card">
        <div class="card-header" style="background: #ffffff; display: flex; justify-content: space-between; align-items: center; padding: 16px 20px;">
            <h3 class="card-title" style="margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-list-check" style="color: var(--primary);"></i>
                18 Dokumen Wajib Kepala Sekolah - <?= SecurityHelper::escape($unit['name']) ?>
            </h3>
            <button type="submit" class="btn btn-primary" style="padding: 8px 20px; font-weight: 600;">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Seluruh Batas Waktu
            </button>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">No</th>
                        <th style="width: 85px; text-align: center;">Kode</th>
                        <th>Bentuk Administrasi KS</th>
                        <th style="width: 170px;">Periode / Catatan</th>
                        <th style="width: 250px;">Batas Waktu (Tanggal &amp; Jam)</th>
                        <th style="width: 200px;">Status Kepatuhan KS</th>
                        <th style="width: 110px; text-align: center;">Berkas KS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($masterItems as $idx => $m): 
                        $dtVal = '';
                        if (!empty($m['deadline_datetime'])) {
                            $dtVal = date('Y-m-d\TH:i', strtotime($m['deadline_datetime']));
                        } elseif (!empty($m['deadline_date'])) {
                            $dtVal = $m['deadline_date'] . 'T23:59';
                        }
                        $up = $m['upload'];
                    ?>
                        <tr style="transition: background-color 0.2s;">
                            <td style="text-align: center; color: var(--text-muted); font-weight: 600;"><?= $idx + 1 ?></td>
                            <td style="text-align: center;">
                                <code style="font-size: 11.5px; font-weight: 700; color: #475569; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;"><?= SecurityHelper::escape($m['code']) ?></code>
                            </td>
                            <td>
                                <strong style="color: var(--text-main); font-size: 13.5px;"><?= SecurityHelper::escape($m['name']) ?></strong>
                                <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 3px;">
                                    Format: <span style="font-weight: 600;"><?= SecurityHelper::escape($m['file_format'] ?? 'PDF') ?></span> &bull; 
                                    Frekuensi: <span><?= SecurityHelper::escape($m['frequency'] ?? '1x setahun') ?></span>
                                </div>
                            </td>
                            <td>
                                <input type="text" 
                                       name="deadlines[<?= $m['id'] ?>][due_info]" 
                                       style="width: 100%; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 12.5px;" 
                                       value="<?= SecurityHelper::escape($m['effective_due_info'] ?? $m['due_info']) ?>" 
                                       placeholder="Contoh: Awal Semester">
                            </td>
                            <td>
                                <input type="datetime-local" 
                                       name="deadlines[<?= $m['id'] ?>][deadline_datetime]" 
                                       class="deadline-input" 
                                       style="width: 100%; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 12.5px; color: var(--text-main);" 
                                       value="<?= $dtVal ?>">
                                <?php if (!empty($dtVal)): ?>
                                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                                        <i class="fa-regular fa-clock" style="color: #f59e0b; margin-right: 4px;"></i>
                                        <?= date('d M Y, H:i', strtotime($dtVal)) ?> WIB
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($m['status_badge'] === 'ON_TIME'): ?>
                                    <span class="badge badge-success" style="font-size: 11px; padding: 4px 10px;">
                                        <i class="fa-solid fa-circle-check"></i> Tepat Waktu
                                    </span>
                                    <div style="font-size: 11px; color: #166534; margin-top: 3px;">
                                        Unggah: <?= date('d/m/Y H:i', strtotime($up['created_at'])) ?>
                                    </div>
                                <?php elseif ($m['status_badge'] === 'LATE_SUBMITTED'): ?>
                                    <span class="badge badge-warning" style="font-size: 11px; padding: 4px 10px;">
                                        <i class="fa-solid fa-triangle-exclamation"></i> Terlambat
                                    </span>
                                    <div style="font-size: 11px; color: #92400e; margin-top: 3px;">
                                        Unggah: <?= date('d/m/Y H:i', strtotime($up['created_at'])) ?>
                                    </div>
                                <?php elseif ($m['status_badge'] === 'OVERDUE_MISSING'): ?>
                                    <span class="badge badge-danger" style="font-size: 11px; padding: 4px 10px; font-weight: 700;">
                                        <i class="fa-solid fa-circle-xmark"></i> Lewat Batas Waktu!
                                    </span>
                                    <div style="font-size: 11px; color: #dc2626; font-weight: 600; margin-top: 3px;">
                                        Belum Dikumpulkan (Penalti)
                                    </div>
                                <?php elseif ($m['status_badge'] === 'PENDING_APPROVAL'): ?>
                                    <span class="badge badge-info" style="font-size: 11px; padding: 4px 10px;">
                                        <i class="fa-solid fa-clock"></i> Menunggu Approval
                                    </span>
                                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                                        Unggah: <?= date('d/m/Y H:i', strtotime($up['created_at'])) ?>
                                    </div>
                                <?php elseif ($m['status_badge'] === 'AWAITING_SUBMISSION'): ?>
                                    <?php 
                                        $diffDays = ceil(($m['deadline_ts'] - $now) / 86400);
                                    ?>
                                    <span class="badge badge-secondary" style="font-size: 11px; padding: 4px 10px;">
                                        Belum Dikumpul (Sisa <?= max(0, $diffDays) ?> hari)
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-secondary" style="font-size: 11px; padding: 4px 10px; opacity: 0.7;">
                                        Belum Ada Batas
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($up && !empty($up['file_path'])): ?>
                                    <a href="<?= SecurityHelper::baseUrl() . '/' . ltrim($up['file_path'], '/') ?>" target="_blank" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 11px;" title="Lihat Dokumen KS">
                                        <i class="fa-solid fa-eye"></i> Lihat
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 12px;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="padding: 16px 20px; background: #f8fafc; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="font-size: 12px; color: var(--text-muted);">
                <i class="fa-solid fa-shield-halved" style="color: var(--primary); margin-right: 4px;"></i>
                Setiap perubahan batas waktu dicatat pada Audit Trail dan langsung mengikat kalkulasi indikator AKFI KS &amp; Deputi.
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 8px 24px; font-weight: 600;">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Seluruh Batas Waktu
            </button>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnApplyBatch = document.getElementById('btnApplyBatch');
    const batchInput = document.getElementById('batchDateTimeInput');
    const deadlineInputs = document.querySelectorAll('.deadline-input');

    if (btnApplyBatch && batchInput) {
        btnApplyBatch.addEventListener('click', function() {
            const val = batchInput.value;
            if (!val) {
                alert('Silakan tentukan tanggal dan jam batas waktu terlebih dahulu.');
                return;
            }
            if (confirm('Terapkan batas waktu (' + val.replace('T', ' ') + ') ke seluruh 18 instrumen dokumen KS?')) {
                deadlineInputs.forEach(input => {
                    input.value = val;
                });
            }
        });
    }
});
</script>
