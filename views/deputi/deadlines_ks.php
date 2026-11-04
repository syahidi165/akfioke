<?php
// views/deputi/deadlines_ks.php
use Core\SecurityHelper;

$now = time();
?>

<div class="page-header d-print-none mb-4">
    <div class="row align-items-center">
        <div class="col">
            <div class="page-pretitle text-muted">Manajemen Kontrak Kinerja & Administrasi Satuan</div>
            <h2 class="page-title text-primary d-flex align-items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-clock-edit" width="28" height="28" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M21 12a9 9 0 1 0 -9.972 8.948c.32 .034 .644 .052 .972 .052" /><path d="M12 7v5l2 2" /><path d="M18.42 15.61a2.1 2.1 0 0 1 2.97 2.97l-3.39 3.42h-3v-3l3.42 -3.39z" /></svg>
                Atur Batas Waktu (Due Date) Administrasi Kepala Sekolah
            </h2>
            <div class="text-muted small mt-1">
                Tentukan batas tanggal &amp; jam pengumpulan 18 dokumen wajib Kepala Sekolah. Keterlambatan atau dokumen belum terkumpul otomatis memengaruhi <strong>Penilaian AKFI KS</strong> dan <strong>Rapor AKFI Deputi Pendidikan (Indikator 3.1)</strong>.
            </div>
        </div>
        <div class="col-auto ms-auto">
            <form method="GET" action="" class="d-flex align-items-center gap-2">
                <label class="form-label mb-0 fw-bold small text-muted">Pilih Unit Binaan:</label>
                <select name="unit_id" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 220px;">
                    <?php foreach ($units as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= $u['id'] == $selectedUnitId ? 'selected' : '' ?>>
                            <?= SecurityHelper::escape($u['code'] . ' - ' . $u['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>
</div>

<!-- STATISTIK KEPATUHAN & DAMPAK AKFI -->
<div class="row row-cards mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-primary text-white avatar">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /></svg>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">Total Dokumen KS</div>
                        <div class="text-muted small"><?= $totalDocs ?> Dokumen Wajib</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-info text-white avatar">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9" /><polyline points="12 7 12 12 15 15" /></svg>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">Deadline Terpasang</div>
                        <div class="text-muted small"><strong><?= $totalDeadlinesSet ?></strong> dari <?= $totalDocs ?> Berkas</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-success text-white avatar">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">Tepat Waktu (On-Time)</div>
                        <div class="text-success small fw-bold"><?= $totalSubmittedOnTime ?> Berkas Terverifikasi</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-danger text-white avatar">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v2m0 4v.01" /><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" /></svg>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">Terlewat / Terlambat</div>
                        <div class="text-danger small fw-bold"><?= $totalLateOrOverdue ?> Berkas (Penalti AKFI)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QUICK BATCH SETTER CARD -->
<div class="card mb-4 border-dashed bg-white shadow-sm">
    <div class="card-body p-3">
        <div class="row align-items-center g-3">
            <div class="col-auto">
                <div class="avatar bg-primary-lt">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5h3.5a5 5 0 0 1 0 10h-8.5" /><path d="M15 15l-3 -3l3 -3" /></svg>
                </div>
            </div>
            <div class="col-md-4">
                <strong class="d-block text-dark">Atur Batas Waktu Cepat (Batch Setter)</strong>
                <span class="text-muted small">Terapkan tanggal &amp; jam yang sama ke seluruh baris dokumen di bawah ini.</span>
            </div>
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fa-solid fa-calendar-clock"></i></span>
                    <input type="datetime-local" id="batchDateTimeInput" class="form-control" value="<?= date('Y-m-d\T23:59', strtotime('+14 days')) ?>">
                </div>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-primary btn-sm w-100" id="btnApplyBatch">
                    Terapkan ke Semua
                </button>
            </div>
        </div>
    </div>
</div>

<!-- FORM UTAMA PENGATURAN DUE DATE -->
<form method="POST" action="<?= SecurityHelper::baseUrl() ?>/deputi/deadlines-ks/save" id="formDeadlines">
    <input type="hidden" name="unit_id" value="<?= $selectedUnitId ?>">

    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center py-3 bg-light">
            <h3 class="card-title mb-0 text-dark fw-bold">
                18 Dokumen Wajib Kepala Sekolah - <?= SecurityHelper::escape($unit['name']) ?>
            </h3>
            <button type="submit" class="btn btn-primary btn-sm px-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><circle cx="12" cy="14" r="2" /><polyline points="14 4 14 8 8 8 8 4" /></svg>
                Simpan Seluruh Batas Waktu
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter table-bordered table-hover mb-0">
                <thead class="bg-light text-center small">
                    <tr>
                        <th style="width: 35px;">No</th>
                        <th style="width: 75px;">Kode</th>
                        <th>Nama Dokumen Administrasi KS</th>
                        <th style="width: 140px;">Periode / Keterangan</th>
                        <th style="width: 220px;">Batas Waktu (Tanggal &amp; Jam)</th>
                        <th style="width: 180px;">Status Kepatuhan KS</th>
                        <th style="width: 130px;">Bukti Berkas</th>
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
                        <tr>
                            <td class="text-center text-muted small"><?= $idx + 1 ?></td>
                            <td class="text-center fw-bold text-secondary small"><?= SecurityHelper::escape($m['code']) ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= SecurityHelper::escape($m['name']) ?></div>
                                <div class="text-muted small" style="font-size: 11px;">
                                    Format: <?= SecurityHelper::escape($m['file_format'] ?? 'PDF') ?> &bull; Bobot: <?= SecurityHelper::escape($m['frequency'] ?? '1x setahun') ?>
                                </div>
                            </td>
                            <td>
                                <input type="text" 
                                       name="deadlines[<?= $m['id'] ?>][due_info]" 
                                       class="form-control form-control-sm" 
                                       value="<?= SecurityHelper::escape($m['effective_due_info'] ?? $m['due_info']) ?>" 
                                       placeholder="Contoh: Awal Semester">
                            </td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <input type="datetime-local" 
                                           name="deadlines[<?= $m['id'] ?>][deadline_datetime]" 
                                           class="form-control form-control-sm deadline-input" 
                                           value="<?= $dtVal ?>">
                                </div>
                                <?php if (!empty($dtVal)): ?>
                                    <div class="text-muted small mt-1" style="font-size: 10.5px;">
                                        <i class="fa-regular fa-clock me-1"></i>
                                        <?= date('d M Y, H:i', strtotime($dtVal)) ?> WIB
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($m['status_badge'] === 'ON_TIME'): ?>
                                    <span class="badge bg-success text-white p-1 px-2 d-inline-flex align-items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><polyline points="9 11 12 14 20 6" /><path d="M20 12v6a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h9" /></svg>
                                        Tepat Waktu
                                    </span>
                                    <div class="text-muted small mt-1" style="font-size: 10px;">
                                        Disetujui: <?= date('d/m/Y H:i', strtotime($up['created_at'])) ?>
                                    </div>
                                <?php elseif ($m['status_badge'] === 'LATE_SUBMITTED'): ?>
                                    <span class="badge bg-warning text-dark p-1 px-2 d-inline-flex align-items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9" /><line x1="12" y1="8" x2="12" y2="12" /><line x1="12" y1="16" x2="12.01" y2="16" /></svg>
                                        Terlambat Diserahkan
                                    </span>
                                    <div class="text-danger small mt-1" style="font-size: 10px;">
                                        Unggah: <?= date('d/m/Y H:i', strtotime($up['created_at'])) ?>
                                    </div>
                                <?php elseif ($m['status_badge'] === 'OVERDUE_MISSING'): ?>
                                    <span class="badge bg-danger text-white p-1 px-2 d-inline-flex align-items-center gap-1 animate-pulse">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9" /><line x1="12" y1="8" x2="12" y2="12" /><line x1="12" y1="16" x2="12.01" y2="16" /></svg>
                                        Lewat Batas Waktu!
                                    </span>
                                    <div class="text-danger small mt-1 fw-bold" style="font-size: 10px;">
                                        Belum Dikumpulkan (Penalti)
                                    </div>
                                <?php elseif ($m['status_badge'] === 'PENDING_APPROVAL'): ?>
                                    <span class="badge bg-info text-white p-1 px-2">
                                        Menunggu Approval
                                    </span>
                                    <div class="text-muted small mt-1" style="font-size: 10px;">
                                        Oleh KS: <?= date('d/m/Y H:i', strtotime($up['created_at'])) ?>
                                    </div>
                                <?php elseif ($m['status_badge'] === 'AWAITING_SUBMISSION'): ?>
                                    <?php 
                                        $diffDays = ceil(($m['deadline_ts'] - $now) / 86400);
                                    ?>
                                    <span class="badge bg-secondary-lt p-1 px-2 text-dark">
                                        Belum Dikumpul (Sisa <?= max(0, $diffDays) ?> hari)
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted p-1 px-2">
                                        Belum Ada Batas
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($up && !empty($up['file_path'])): ?>
                                    <a href="<?= SecurityHelper::baseUrl() . '/' . ltrim($up['file_path'], '/') ?>" target="_blank" class="btn btn-ghost-primary btn-sm py-1 px-2" title="Lihat Berkas Unggahan KS">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="15" height="15" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="2" /><path d="M22 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                                        Lihat
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-light d-flex justify-content-between align-items-center py-3">
            <span class="text-muted small">
                Perubahan batas waktu akan dicatat dalam Audit Trail dan langsung mengikat kalkulasi AKFI KS &amp; Deputi.
            </span>
            <button type="submit" class="btn btn-primary px-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><circle cx="12" cy="14" r="2" /><polyline points="14 4 14 8 8 8 8 4" /></svg>
                Simpan Seluruh Batas Waktu
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
                alert('Silakan pilih tanggal dan jam batas waktu terlebih dahulu.');
                return;
            }
            if (confirm('Apakah Anda yakin ingin menerapkan batas waktu (' + val.replace('T', ' ') + ') ke seluruh 18 dokumen administrasi KS di bawah ini?')) {
                deadlineInputs.forEach(input => {
                    input.value = val;
                });
            }
        });
    }
});
</script>
