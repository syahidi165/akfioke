<?php
// views/administrasi/deadlines.php
use Core\SecurityHelper;
use Core\CSRF;
?>
<div class="page-header">
    <div>
        <h1 class="page-title">Pengaturan Batas Waktu (Deadline) Administrasi</h1>
        <div class="page-subtitle">
            Unit Kerja: <strong><?= SecurityHelper::escape($unit['name'] ?? 'Unit Sekolah') ?></strong> | 
            Ketepatan waktu mengunggah dokumen diukur dari batas waktu unit ini dan menjadi indikator AKFI.
        </div>
    </div>
    <?php if (!empty($units) && count($units) > 1): ?>
        <div>
            <form method="GET" action="<?= $baseUrl ?>/administrasi/deadlines" style="display: flex; align-items: center; gap: 8px;">
                <label class="form-label mb-0" style="font-size: 13px;">Pilih Unit:</label>
                <select name="unit_id" class="header-select" onchange="this.form.submit()">
                    <?php foreach ($units as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= $u['id'] == $selectedUnitId ? 'selected' : '' ?>>
                            <?= SecurityHelper::escape($u['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fa-solid fa-calendar-check" style="color: var(--primary); margin-right: 8px;"></i>
            Daftar Instrumen & Batas Waktu - <?= SecurityHelper::escape($unit['name'] ?? 'Unit Terpilih') ?>
        </h3>
        <div>
            <span class="badge badge-info" style="font-size: 12px;">
                <?= count($masterItems) ?> Dokumen Terdaftar
            </span>
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 140px;">Peran Sasaran</th>
                    <th style="width: 100px;">Kode</th>
                    <th>Bentuk Administrasi</th>
                    <th style="width: 130px;">Frekuensi</th>
                    <th style="width: 220px;">Batas Waktu (Unit Deadline)</th>
                    <th style="width: 130px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($masterItems)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 24px; color: var(--text-muted);">
                            Tidak ada instrumen administrasi yang dapat dikelola untuk unit ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($masterItems as $m): ?>
                        <tr>
                            <td><span class="badge badge-secondary"><?= SecurityHelper::escape($m['target_role']) ?></span></td>
                            <td><code><?= SecurityHelper::escape($m['code']) ?></code></td>
                            <td><strong><?= SecurityHelper::escape($m['title']) ?></strong></td>
                            <td><?= SecurityHelper::escape($m['frequency']) ?></td>
                            <td>
                                <div>
                                    <strong style="color: var(--primary); font-size: 13.5px;">
                                        <?= SecurityHelper::escape($m['effective_due_info']) ?>
                                    </strong>
                                    <?php if (!empty($m['unit_due_info'])): ?>
                                        <span class="badge badge-success" style="font-size: 10px; margin-left: 4px;">Khusus Unit</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary" style="font-size: 10px; margin-left: 4px;">Standar</span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($m['deadline_datetime'])): ?>
                                    <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 3px;">
                                        <i class="fa-regular fa-clock" style="color: #f59e0b;"></i> 
                                        Batas: <strong style="color: var(--text-main);"><?= date('d M Y, H:i', strtotime($m['deadline_datetime'])) ?> WIB</strong>
                                    </div>
                                <?php elseif (!empty($m['deadline_date'])): ?>
                                    <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 3px;">
                                        <i class="fa-regular fa-calendar" style="color: #f59e0b;"></i> 
                                        Batas: <strong style="color: var(--text-main);"><?= date('d M Y', strtotime($m['deadline_date'])) ?> 23:59 WIB</strong>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <button type="button" class="btn btn-outline btn-sm" onclick='openDeadlineModal(<?= json_encode($m) ?>)'>
                                    <i class="fa-solid fa-pen-to-square"></i> Atur
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Ubah Deadline -->
<div class="modal-overlay" id="deadlineModal">
    <div class="modal-container" style="max-width: 500px;">
        <form method="POST" action="<?= $baseUrl ?>/administrasi/deadlines/save">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="jenis_admin_id" id="deadline_jenis_id">
            <input type="hidden" name="unit_id" value="<?= (int)$selectedUnitId ?>">
            
            <div class="modal-header">
                <h3 class="card-title">Atur Batas Waktu Khusus Unit</h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('deadlineModal').classList.remove('active')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Unit Sekolah</label>
                    <div style="font-weight: 600; color: var(--primary);"><?= SecurityHelper::escape($unit['name'] ?? 'Unit') ?></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Dokumen Administrasi</label>
                    <div id="deadline_doc_title" style="font-weight: 600; color: var(--text-main); margin-bottom: 6px;"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Batas Waktu Pengumpulan (Deskripsi Due)</label>
                    <input type="text" name="due_info" id="deadline_due_info" class="form-control" required placeholder="Contoh: Pekan ke-2 Semester Ganjil / Tanggal 5 setiap bulan">
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Deskripsi batas waktu ini akan tampil di panduan instrumen guru unit <?= SecurityHelper::escape($unit['name'] ?? '') ?>.</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Tanggal & Jam Batas Akhir (Due Date Timestamp)</label>
                    <input type="datetime-local" name="deadline_datetime" id="deadline_datetime" class="form-control">
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                        Jika dokumen diunggah melewati waktu ini, sistem otomatis menetapkan status <strong>Terlambat</strong> dan persentase ketepatan waktu berkurang pada Auto Data Collector AKFI.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('deadlineModal').classList.remove('active')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan Batas Waktu</button>
            </div>
        </form>
    </div>
</div>

<script>
function openDeadlineModal(m) {
    document.getElementById('deadline_jenis_id').value = m.id;
    document.getElementById('deadline_doc_title').innerText = m.title;
    document.getElementById('deadline_due_info').value = m.unit_due_info || m.due_info || '';
    
    if (m.deadline_datetime) {
        let dt = m.deadline_datetime.replace(' ', 'T');
        if (dt.length > 16) dt = dt.substring(0, 16);
        document.getElementById('deadline_datetime').value = dt;
    } else if (m.deadline_date) {
        document.getElementById('deadline_datetime').value = m.deadline_date + 'T23:59';
    } else {
        document.getElementById('deadline_datetime').value = '';
    }

    document.getElementById('deadlineModal').classList.add('active');
}
</script>
