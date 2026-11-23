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
                            <td>
                                <span class="badge" style="background: <?= $m['frequency'] === 'setiap bulan' ? '#ecfdf5; color: #047857; border: 1px solid #a7f3d0;' : ($m['frequency'] === '2x setahun' ? '#eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;' : '#fef3c7; color: #b45309; border: 1px solid #fde68a;') ?>">
                                    <?= SecurityHelper::escape(ucfirst($m['frequency'])) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($m['frequency'] === '2x setahun'): ?>
                                    <div style="font-size: 12.5px; margin-bottom: 4px;">
                                        <span class="badge badge-primary" style="font-size: 10px;">Sem 1</span>
                                        <strong><?= SecurityHelper::escape($m['sem1_due_info'] ?? 'Pekan Ujian Sem 1') ?></strong>
                                        <?php if (!empty($m['sem1_datetime'])): ?>
                                            <div style="font-size: 11px; color: var(--text-muted); margin-left: 4px;">
                                                <i class="fa-regular fa-clock" style="color: #f59e0b;"></i> <?= date('d M Y, H:i', strtotime($m['sem1_datetime'])) ?> WIB
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div style="font-size: 12.5px;">
                                        <span class="badge badge-info" style="font-size: 10px;">Sem 2</span>
                                        <strong><?= SecurityHelper::escape($m['sem2_due_info'] ?? 'Pekan Ujian Sem 2') ?></strong>
                                        <?php if (!empty($m['sem2_datetime'])): ?>
                                            <div style="font-size: 11px; color: var(--text-muted); margin-left: 4px;">
                                                <i class="fa-regular fa-clock" style="color: #f59e0b;"></i> <?= date('d M Y, H:i', strtotime($m['sem2_datetime'])) ?> WIB
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php elseif ($m['frequency'] === 'setiap bulan'): ?>
                                    <div>
                                        <strong style="color: #047857; font-size: 13px;">
                                            <i class="fa-solid fa-repeat"></i> <?= SecurityHelper::escape($m['monthly_due_info'] ?? 'Bulanan') ?>
                                        </strong>
                                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                                            Cutoff: Tanggal <strong><?= $m['cutoff_day'] ?? 5 ?></strong> setiap bulan (23:59 WIB)
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div>
                                        <strong style="color: var(--primary); font-size: 13.5px;">
                                            <?= SecurityHelper::escape($m['annual_due_info'] ?? $m['effective_due_info']) ?>
                                        </strong>
                                        <?php if (!empty($m['annual_datetime'])): ?>
                                            <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 3px;">
                                                <i class="fa-regular fa-clock" style="color: #f59e0b;"></i> 
                                                Batas: <strong style="color: var(--text-main);"><?= date('d M Y, H:i', strtotime($m['annual_datetime'])) ?> WIB</strong>
                                            </div>
                                        <?php elseif (!empty($m['deadline_date'])): ?>
                                            <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 3px;">
                                                <i class="fa-regular fa-calendar" style="color: #f59e0b;"></i> 
                                                Batas: <strong style="color: var(--text-main);"><?= date('d M Y', strtotime($m['deadline_date'])) ?> 23:59 WIB</strong>
                                            </div>
                                        <?php endif; ?>
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

<!-- Modal Ubah Deadline (Diferensiasi: 1x setahun, 2x setahun, setiap bulan) -->
<div class="modal-overlay" id="deadlineModal">
    <div class="modal-container" style="max-width: 540px;">
        <form method="POST" action="<?= $baseUrl ?>/administrasi/deadlines/save">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="jenis_admin_id" id="deadline_jenis_id">
            <input type="hidden" name="unit_id" value="<?= (int)$selectedUnitId ?>">
            
            <div class="modal-header">
                <h3 class="card-title">Atur Batas Waktu Khusus Unit</h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('deadlineModal').classList.remove('active')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group" style="margin-bottom: 10px;">
                    <label class="form-label">Unit Sekolah &amp; Frekuensi Dokumen</label>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-weight: 700; color: var(--primary); font-size: 14px;"><?= SecurityHelper::escape($unit['name'] ?? 'Unit') ?></span>
                        <span id="deadline_badge_freq" class="badge badge-info" style="font-size: 11px;">-</span>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label">Nama Dokumen Administrasi</label>
                    <div id="deadline_doc_title" style="font-weight: 700; color: var(--text-main); font-size: 13.5px; background: #f8fafc; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0;"></div>
                </div>

                <!-- FORM VARIAN 1: KHUSUS 2X SETAHUN (SEMESTER 1 & SEMESTER 2) -->
                <div id="sectionForm2x" style="display: none;">
                    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px; margin-bottom: 14px;">
                        <div style="font-weight: 700; color: #1d4ed8; font-size: 13px; margin-bottom: 8px;">
                            <i class="fa-solid fa-calendar-days"></i> Semester 1 (Ganjil)
                        </div>
                        <div class="form-group" style="margin-bottom: 8px;">
                            <label class="form-label" style="font-size: 12px; font-weight: 600;">Deskripsi Batas Waktu Sem 1</label>
                            <input type="text" name="due_info_sem1" id="deadline_due_info_sem1" class="form-control" placeholder="Contoh: Pekan Ujian Semester Ganjil" style="font-size: 12.5px;">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-size: 12px; font-weight: 600;">Tanggal &amp; Jam Batas Akhir Sem 1 (Due Date)</label>
                            <input type="datetime-local" name="deadline_datetime_sem1" id="deadline_datetime_sem1" class="form-control" style="font-size: 12.5px;">
                        </div>
                    </div>

                    <div style="background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 8px; padding: 12px; margin-bottom: 10px;">
                        <div style="font-weight: 700; color: #0f766e; font-size: 13px; margin-bottom: 8px;">
                            <i class="fa-solid fa-calendar-days"></i> Semester 2 (Genap)
                        </div>
                        <div class="form-group" style="margin-bottom: 8px;">
                            <label class="form-label" style="font-size: 12px; font-weight: 600;">Deskripsi Batas Waktu Sem 2</label>
                            <input type="text" name="due_info_sem2" id="deadline_due_info_sem2" class="form-control" placeholder="Contoh: Pekan Ujian Semester Genap" style="font-size: 12.5px;">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-size: 12px; font-weight: 600;">Tanggal &amp; Jam Batas Akhir Sem 2 (Due Date)</label>
                            <input type="datetime-local" name="deadline_datetime_sem2" id="deadline_datetime_sem2" class="form-control" style="font-size: 12.5px;">
                        </div>
                    </div>
                </div>

                <!-- FORM VARIAN 2: KHUSUS SETIAP BULAN -->
                <div id="sectionFormMonthly" style="display: none;">
                    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 14px; margin-bottom: 10px;">
                        <div style="font-weight: 700; color: #047857; font-size: 13px; margin-bottom: 6px;">
                            <i class="fa-solid fa-calendar-check"></i> Pengaturan Batas Waktu Bulanan
                        </div>
                        <div class="form-group" style="margin-bottom: 12px;">
                            <label class="form-label" style="font-size: 12px; font-weight: 600;">Batas Tanggal Pengumpulan Setiap Bulan (Cutoff Day)</label>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 12.5px; font-weight: 600; color: #334155;">Tanggal:</span>
                                <input type="number" name="monthly_cutoff_day" id="deadline_monthly_cutoff_day" class="form-control" min="1" max="31" value="5" style="width: 80px; font-size: 13px; font-weight: 700; text-align: center;">
                                <span style="font-size: 12px; color: #64748b;">setiap bulan (Pukul 23:59 WIB)</span>
                            </div>
                            <div style="font-size: 11px; color: #059669; margin-top: 4px;">
                                <i class="fa-solid fa-circle-check"></i> Sistem otomatis menerapkan cutoff tanggal ini untuk seluruh 12 bulan (Juli sampai Juni).
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-size: 12px; font-weight: 600;">Deskripsi Tampilan Batas Waktu (Due Info)</label>
                            <input type="text" name="monthly_due_info" id="deadline_monthly_due_info" class="form-control" placeholder="Contoh: Tanggal 5 setiap bulan berjalan" style="font-size: 12.5px;">
                        </div>
                    </div>
                </div>

                <!-- FORM VARIAN 3: 1X SETAHUN & DEFAULT -->
                <div id="sectionForm1x" style="display: none;">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label class="form-label" style="font-size: 12.5px; font-weight: 600;">Batas Waktu Pengumpulan (Deskripsi Due)</label>
                        <input type="text" name="due_info" id="deadline_due_info" class="form-control" placeholder="Contoh: Awal Tahun Ajaran / 31 Agustus">
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Deskripsi batas waktu ini akan tampil di panduan instrumen guru unit <?= SecurityHelper::escape($unit['name'] ?? '') ?>.</div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 12.5px; font-weight: 600;">Tanggal &amp; Jam Batas Akhir (Due Date Timestamp)</label>
                        <input type="datetime-local" name="deadline_datetime" id="deadline_datetime" class="form-control">
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                            Jika dokumen diunggah melewati waktu ini, sistem otomatis menetapkan status <strong>Terlambat</strong> pada Auto Data Collector AKFI.
                        </div>
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
    document.getElementById('deadline_doc_title').innerText = (m.code ? '[' + m.code + '] ' : '') + m.title;
    
    const freq = (m.frequency || '').toLowerCase().trim();
    document.getElementById('deadline_badge_freq').innerText = m.frequency ? m.frequency.toUpperCase() : 'TAHUNAN';

    const sec2x = document.getElementById('sectionForm2x');
    const secMonthly = document.getElementById('sectionFormMonthly');
    const sec1x = document.getElementById('sectionForm1x');

    if (freq === '2x setahun') {
        sec2x.style.display = 'block';
        secMonthly.style.display = 'none';
        sec1x.style.display = 'none';

        document.getElementById('deadline_due_info_sem1').value = m.sem1_due_info || 'Pekan Ujian Semester Ganjil';
        document.getElementById('deadline_due_info_sem2').value = m.sem2_due_info || 'Pekan Ujian Semester Genap';

        let dt1 = m.sem1_datetime ? m.sem1_datetime.replace(' ', 'T').substring(0, 16) : '';
        let dt2 = m.sem2_datetime ? m.sem2_datetime.replace(' ', 'T').substring(0, 16) : '';
        document.getElementById('deadline_datetime_sem1').value = dt1;
        document.getElementById('deadline_datetime_sem2').value = dt2;

    } else if (freq === 'setiap bulan') {
        sec2x.style.display = 'none';
        secMonthly.style.display = 'block';
        sec1x.style.display = 'none';

        document.getElementById('deadline_monthly_cutoff_day').value = m.cutoff_day || 5;
        document.getElementById('deadline_monthly_due_info').value = m.monthly_due_info || ('Tanggal ' + (m.cutoff_day || 5) + ' setiap bulan');

    } else {
        // 1x setahun atau default
        sec2x.style.display = 'none';
        secMonthly.style.display = 'none';
        sec1x.style.display = 'block';

        document.getElementById('deadline_due_info').value = m.annual_due_info || m.unit_due_info || m.due_info || '';
        
        let dt = m.annual_datetime || m.deadline_datetime;
        if (dt) {
            let dtStr = dt.replace(' ', 'T');
            if (dtStr.length > 16) dtStr = dtStr.substring(0, 16);
            document.getElementById('deadline_datetime').value = dtStr;
        } else if (m.deadline_date) {
            document.getElementById('deadline_datetime').value = m.deadline_date + 'T23:59';
        } else {
            document.getElementById('deadline_datetime').value = '';
        }
    }

    document.getElementById('deadlineModal').classList.add('active');
}
</script>
