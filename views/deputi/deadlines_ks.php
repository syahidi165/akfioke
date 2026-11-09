<?php
// views/deputi/deadlines_ks.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
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
<form method="POST" action="<?= $baseUrl ?>/deputi/deadlines-ks/save" id="formDeadlines">
    <?= CSRF::field() ?>
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
                        <th style="width: 270px;">Batas Waktu (Tanggal &amp; Jam)</th>
                        <th style="width: 200px;">Status Kepatuhan KS</th>
                        <th style="width: 110px; text-align: center;">Berkas KS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($masterItems as $idx => $m): 
                        $slots = $m['slots'] ?? [1 => ['slot_num' => 1, 'slot_label' => 'Batas Waktu', 'due_info' => $m['due_info'] ?? '', 'deadline_datetime' => '', 'upload' => null, 'status_badge' => 'NO_DEADLINE']];
                        $isMultiSlot = count($slots) > 1;
                    ?>
                        <tr style="transition: background-color 0.2s;">
                            <td style="text-align: center; color: var(--text-muted); font-weight: 600;"><?= $idx + 1 ?></td>
                            <td style="text-align: center;">
                                <code style="font-size: 11.5px; font-weight: 700; color: #475569; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;"><?= SecurityHelper::escape($m['code']) ?></code>
                            </td>
                            <td>
                                <strong style="color: var(--text-main); font-size: 13.5px;"><?= SecurityHelper::escape($m['title'] ?? $m['name'] ?? 'Dokumen Administrasi KS') ?></strong>
                                <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 3px;">
                                    Frekuensi: <span class="badge" style="background: <?= $m['frequency'] === '3x setahun' ? '#f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe;' : ($m['frequency'] === '2x setahun' ? '#eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;' : '#f1f5f9; color: #475569;') ?> font-weight: 700; font-size: 11px; padding: 2px 6px;"><?= SecurityHelper::escape($m['frequency'] ?? '1x setahun') ?></span> &bull;
                                    Target Default: <span><?= SecurityHelper::escape($m['due_info'] ?? '-') ?></span>
                                </div>
                            </td>
                            <td>
                                <?php if (!$isMultiSlot): ?>
                                    <?php $singleSlot = reset($slots); ?>
                                    <input type="text" 
                                           name="deadlines[<?= $m['id'] ?>][1][due_info]" 
                                           style="width: 100%; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 12.5px;" 
                                           value="<?= SecurityHelper::escape($singleSlot['due_info'] ?? $m['due_info']) ?>" 
                                           placeholder="Contoh: Awal Semester">
                                <?php else: ?>
                                    <div style="font-size: 12px; font-weight: 600; color: #475569;">
                                        <?= SecurityHelper::escape($m['due_info']) ?>
                                    </div>
                                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                                        (<?= count($slots) ?> Batas Waktu Terpisah)
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <?php foreach ($slots as $sNum => $s): ?>
                                        <div style="<?= $isMultiSlot ? 'background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 8px;' : '' ?>">
                                            <?php if ($isMultiSlot): ?>
                                                <div style="font-size: 11px; font-weight: 700; color: #6d28d9; margin-bottom: 3px; display: flex; align-items: center; gap: 4px;">
                                                    <i class="fa-solid fa-chart-line"></i> <?= SecurityHelper::escape($s['slot_label']) ?>
                                                </div>
                                                <input type="hidden" name="deadlines[<?= $m['id'] ?>][<?= $sNum ?>][due_info]" value="<?= SecurityHelper::escape($s['due_info'] ?? $s['slot_label']) ?>">
                                            <?php endif; ?>
                                            <input type="datetime-local" 
                                                   name="deadlines[<?= $m['id'] ?>][<?= $sNum ?>][deadline_datetime]" 
                                                   class="deadline-input" 
                                                   style="width: 100%; padding: 5px 8px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 12px; color: var(--text-main);" 
                                                   value="<?= $s['deadline_datetime'] ?>">
                                            <?php if (!empty($s['deadline_datetime'])): ?>
                                                <div style="font-size: 10.5px; color: var(--text-muted); margin-top: 2px;">
                                                    <i class="fa-regular fa-clock" style="color: #f59e0b; margin-right: 3px;"></i>
                                                    <?= date('d M Y, H:i', strtotime($s['deadline_datetime'])) ?> WIB
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <?php foreach ($slots as $sNum => $s): 
                                        $up = $s['upload'];
                                    ?>
                                        <div style="<?= $isMultiSlot ? 'padding: 4px 0; min-height: 48px; display: flex; flex-direction: column; justify-content: center;' : '' ?>">
                                            <?php if ($isMultiSlot): ?>
                                                <div style="font-size: 10px; font-weight: 700; color: #64748b; margin-bottom: 2px; text-transform: uppercase;">
                                                    Slot <?= $sNum ?>:
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($s['status_badge'] === 'ON_TIME'): ?>
                                                <span class="badge badge-success" style="font-size: 11px; padding: 3px 8px;">
                                                    <i class="fa-solid fa-circle-check"></i> Tepat Waktu
                                                </span>
                                                <div style="font-size: 10.5px; color: #166534; margin-top: 2px;">
                                                    Unggah: <?= date('d/m/Y H:i', strtotime($up['created_at'])) ?>
                                                </div>
                                            <?php elseif ($s['status_badge'] === 'LATE_SUBMITTED'): ?>
                                                <span class="badge badge-warning" style="font-size: 11px; padding: 3px 8px;">
                                                    <i class="fa-solid fa-triangle-exclamation"></i> Terlambat
                                                </span>
                                                <div style="font-size: 10.5px; color: #92400e; margin-top: 2px;">
                                                    Unggah: <?= date('d/m/Y H:i', strtotime($up['created_at'])) ?>
                                                </div>
                                            <?php elseif ($s['status_badge'] === 'OVERDUE_MISSING'): ?>
                                                <span class="badge badge-danger" style="font-size: 11px; padding: 3px 8px; font-weight: 700;">
                                                    <i class="fa-solid fa-circle-xmark"></i> Lewat Batas!
                                                </span>
                                                <div style="font-size: 10.5px; color: #dc2626; font-weight: 600; margin-top: 2px;">
                                                    Belum Dikumpulkan
                                                </div>
                                            <?php elseif ($s['status_badge'] === 'PENDING_APPROVAL'): ?>
                                                <span class="badge badge-info" style="font-size: 11px; padding: 3px 8px;">
                                                    <i class="fa-solid fa-clock"></i> Menunggu Approval
                                                </span>
                                                <div style="font-size: 10.5px; color: var(--text-muted); margin-top: 2px;">
                                                    Unggah: <?= date('d/m/Y H:i', strtotime($up['created_at'])) ?>
                                                </div>
                                            <?php elseif ($s['status_badge'] === 'AWAITING_SUBMISSION'): ?>
                                                <?php 
                                                    $diffDays = ceil(($s['deadline_ts'] - $now) / 86400);
                                                ?>
                                                <span class="badge badge-secondary" style="font-size: 11px; padding: 3px 8px;">
                                                    Sisa <?= max(0, $diffDays) ?> hari
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary" style="font-size: 11px; padding: 3px 8px; opacity: 0.7;">
                                                    Belum Ada Batas
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <?php foreach ($slots as $sNum => $s): 
                                        $up = $s['upload'];
                                    ?>
                                        <div style="<?= $isMultiSlot ? 'min-height: 48px; display: flex; align-items: center; justify-content: center;' : '' ?>">
                                            <?php if ($up && !empty($up['file_path'])): ?>
                                                <a href="<?= $baseUrl . '/' . ltrim($up['file_path'], '/') ?>" target="_blank" class="btn btn-outline btn-sm" style="padding: 3px 8px; font-size: 11px;" title="Lihat Dokumen KS (<?= SecurityHelper::escape($s['slot_label']) ?>)">
                                                    <i class="fa-solid fa-eye"></i> Lihat
                                                </a>
                                            <?php else: ?>
                                                <span style="color: var(--text-muted); font-size: 12px;">-</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
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
