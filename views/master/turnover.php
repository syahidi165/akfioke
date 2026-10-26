<?php
// views/master/turnover.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$canAdd = Auth::hasRole('operator_yayasan');
?>
<div class="page-header">
    <div>
        <h1 class="page-title">Turn Over Talent & Riwayat Mutasi Pegawai</h1>
        <div class="page-subtitle">Pencatatan mutasi antar unit, resign, dan pemberhentian yang secara otomatis menjadi indikator AKFI Kepala Sekolah.</div>
    </div>
    <?php if ($canAdd): ?>
        <div>
            <button type="button" class="btn btn-primary" onclick="document.getElementById('turnoverModal').classList.add('active')">
                <i class="fa-solid fa-people-arrows"></i> Catat Mutasi / Keluar Pegawai
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Kartu Ringkasan Indikator Turn Over per Unit -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header">
        <h3 class="card-title"><i class="fa-solid fa-calculator" style="color: var(--primary);"></i> Rasio Turn Over Pegawai Tahun Berjalan (Target AKFI KS: &le; 5%)</h3>
    </div>
    <div class="card-body">
        <div class="grid-cols-4">
            <?php foreach ($turnoverStats as $uId => $st): ?>
                <div style="border: 1px solid var(--border-color); border-radius: 8px; padding: 16px; background: #ffffff;">
                    <div style="font-weight: 700; font-size: 14px; color: var(--primary);"><?= SecurityHelper::escape($st['unit_name']) ?></div>
                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-top: 10px;">
                        <div style="font-size: 24px; font-weight: 700; color: <?= $st['is_safe'] ? '#10b981' : '#ef4444' ?>;">
                            <?= $st['rate'] ?>%
                        </div>
                        <span class="badge <?= $st['is_safe'] ? 'badge-success' : 'badge-danger' ?>">
                            <?= $st['is_safe'] ? 'Target Tercapai' : 'Melebihi Batas' ?>
                        </span>
                    </div>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 8px;">
                        Pegawai Aktif: <?= $st['active'] ?> | Keluar/Mutasi: <?= $st['out'] ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Riwayat Log Mutasi & Turnover -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fa-solid fa-clock-rotate-left" style="color: var(--accent);"></i> Riwayat Log Mutasi & Turnover</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tgl Efektif</th>
                        <th>Pegawai</th>
                        <th>Jenis Aksi</th>
                        <th>Dari Unit</th>
                        <th>Ke Unit</th>
                        <th>Alasan & Keterangan</th>
                        <th>Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="7" style="text-align: center; padding: 24px; color: var(--text-muted);">Belum ada catatan mutasi atau turnover.</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $l): ?>
                            <tr>
                                <td><?= SecurityHelper::formatTanggalIndo($l['effective_date']) ?></td>
                                <td>
                                    <strong><?= SecurityHelper::escape($l['employee_name']) ?></strong>
                                    <div style="font-size: 11px; color: var(--text-muted);"><?= SecurityHelper::escape($l['nipy']) ?></div>
                                </td>
                                <td>
                                    <?php if ($l['action_type'] === 'Mutasi Keluar'): ?>
                                        <span class="badge badge-warning"><?= $l['action_type'] ?></span>
                                    <?php elseif ($l['action_type'] === 'Resign'): ?>
                                        <span class="badge badge-danger"><?= $l['action_type'] ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-info"><?= $l['action_type'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= SecurityHelper::escape($l['from_unit_name'] ?? '-') ?></td>
                                <td><?= SecurityHelper::escape($l['to_unit_name'] ?? '-') ?></td>
                                <td><?= SecurityHelper::escape($l['reason']) ?></td>
                                <td style="font-size: 11px; color: var(--text-muted);"><?= SecurityHelper::escape($l['creator_name'] ?? 'Sistem') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Pencatatan Turnover -->
<?php if ($canAdd): ?>
<div class="modal-overlay" id="turnoverModal">
    <div class="modal-container" style="max-width: 500px;">
        <form method="POST" action="<?= $baseUrl ?>/master/turnover/save">
            <?= CSRF::inputField() ?>
            <div class="modal-header">
                <h3 class="card-title">Catat Mutasi / Turnover Pegawai</h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('turnoverModal').classList.remove('active')">&times;</button>
            </div>
            <div class="modal-body">
                <!-- Filter Sekolah / Unit Pegawai -->
                <div class="form-group" style="background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; color: #1e293b; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-school" style="color: var(--primary);"></i> 1. Filter Sekolah / Unit Pegawai
                    </label>
                    <select id="to_filter_unit" class="form-control" onchange="filterPegawaiByUnit(this.value)">
                        <option value="">-- Pilih Sekolah / Unit Pegawai --</option>
                        <?php foreach ($units as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= SecurityHelper::escape($u['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px; display: block;">
                        <i class="fa-solid fa-circle-info"></i> Pilih sekolah terlebih dahulu agar daftar nama pegawai hanya memunculkan pegawai di sekolah tersebut dan tidak bisa salah pilih.
                    </small>
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">2. Pilih Nama Pegawai</label>
                    <select name="employee_id" class="form-control" required id="to_emp_select" onchange="autoFillUnit(this)">
                        <option value="">-- Pilih Sekolah / Unit Terlebih Dahulu --</option>
                        <?php foreach ($employees as $e): ?>
                            <option value="<?= $e['id'] ?>" data-unit="<?= $e['unit_id'] ?>" data-unit-name="<?= SecurityHelper::escape($e['unit_name'] ?? '') ?>" data-nipy="<?= SecurityHelper::escape($e['nipy'] ?? '') ?>" data-posisi="<?= SecurityHelper::escape($e['level_posisi'] ?? '') ?>">
                                <?= SecurityHelper::escape($e['nama']) ?> <?= !empty($e['nipy']) ? '(' . SecurityHelper::escape($e['nipy']) . ')' : '' ?> - <?= SecurityHelper::escape($e['level_posisi']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="emp_selected_info" style="display: none; margin-top: 6px; padding: 6px 10px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; font-size: 12px; color: #065f46;"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Jenis Aksi Turnover</label>
                    <select name="action_type" class="form-control" required id="action_type_select" onchange="toggleToUnit(this.value)">
                        <option value="Mutasi Keluar">Mutasi Keluar Antar-Unit Yayasan</option>
                        <option value="Resign">Resign (Mengundurkan Diri)</option>
                        <option value="Diberhentikan">Diberhentikan</option>
                        <option value="Pensiun">Pensiun</option>
                    </select>
                </div>

                <div class="grid-cols-2">
                    <div class="form-group">
                        <label class="form-label">Dari Unit Asal</label>
                        <select name="from_unit_id" id="from_unit_select" class="form-control" required>
                            <?php foreach ($units as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= SecurityHelper::escape($u['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" id="toUnitBox">
                        <label class="form-label">Ke Unit Tujuan</label>
                        <select name="to_unit_id" class="form-control">
                            <option value="">-- Pilih Unit Tujuan --</option>
                            <?php foreach ($units as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= SecurityHelper::escape($u['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Tanggal Efektif</label>
                    <input type="date" name="effective_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Alasan & Dokumen Pendukung</label>
                    <textarea name="reason" class="form-control" rows="3" required placeholder="Jelaskan alasan mutasi / turnover talent..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('turnoverModal').classList.remove('active')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan Catatan</button>
            </div>
        </form>
    </div>
</div>

<script>
const allEmployeeOptions = [];
document.addEventListener('DOMContentLoaded', function() {
    const empSelect = document.getElementById('to_emp_select');
    if (empSelect) {
        for (let i = 0; i < empSelect.options.length; i++) {
            const opt = empSelect.options[i];
            if (opt.value) {
                allEmployeeOptions.push({
                    value: opt.value,
                    text: opt.text,
                    unit: opt.getAttribute('data-unit'),
                    unitName: opt.getAttribute('data-unit-name'),
                    nipy: opt.getAttribute('data-nipy'),
                    posisi: opt.getAttribute('data-posisi')
                });
            }
        }
    }
});

function filterPegawaiByUnit(unitId) {
    const empSelect = document.getElementById('to_emp_select');
    const fromUnitSelect = document.getElementById('from_unit_select');
    const infoBox = document.getElementById('emp_selected_info');
    if (infoBox) infoBox.style.display = 'none';

    empSelect.innerHTML = '';

    if (!unitId) {
        const def = document.createElement('option');
        def.value = '';
        def.textContent = '-- Pilih Sekolah / Unit Terlebih Dahulu --';
        empSelect.appendChild(def);
        return;
    }

    const filtered = allEmployeeOptions.filter(e => String(e.unit) === String(unitId));
    
    if (filtered.length === 0) {
        const def = document.createElement('option');
        def.value = '';
        def.textContent = '-- Tidak ada pegawai aktif di unit sekolah ini --';
        empSelect.appendChild(def);
    } else {
        const def = document.createElement('option');
        def.value = '';
        def.textContent = `-- Pilih Pegawai (${filtered.length} orang tersedia) --`;
        empSelect.appendChild(def);

        filtered.forEach(e => {
            const opt = document.createElement('option');
            opt.value = e.value;
            opt.textContent = e.text;
            opt.setAttribute('data-unit', e.unit);
            opt.setAttribute('data-unit-name', e.unitName);
            empSelect.appendChild(opt);
        });
    }

    // Otomatis sinkronkan unit asal agar konsisten
    if (fromUnitSelect) {
        fromUnitSelect.value = unitId;
    }
}

function autoFillUnit(select) {
    const opt = select.options[select.selectedIndex];
    if (!opt) return;
    const uId = opt.getAttribute('data-unit');
    const infoBox = document.getElementById('emp_selected_info');
    if (uId) {
        document.getElementById('from_unit_select').value = uId;
        if (infoBox) {
            infoBox.innerHTML = '<i class="fa-solid fa-circle-check"></i> Pegawai terkonfirmasi dari unit: <strong>' + (opt.getAttribute('data-unit-name') || 'Unit Terpilih') + '</strong>';
            infoBox.style.display = 'block';
        }
    } else if (infoBox) {
        infoBox.style.display = 'none';
    }
}
function toggleToUnit(val) {
    document.getElementById('toUnitBox').style.display = val === 'Mutasi Keluar' ? 'block' : 'none';
}
</script>
<?php endif; ?>
