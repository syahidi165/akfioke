<?php
// views/master/cross_unit.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
$assignments = $assignments ?? [];
$employees = $employees ?? [];
$units = $units ?? [];
$roles = $roles ?? [];
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
    <div>
        <h1 class="page-title" style="display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-diagram-project" style="color: var(--primary);"></i>
            Penugasan Peran Lintas Unit
        </h1>
        <p class="page-subtitle" style="color: var(--text-muted); font-size: 13px; margin-top: 4px;">
            Atur penugasan guru atau kepala sekolah yang mengajar di satuan pendidikan lain tanpa membuat akun ganda atau Rapor AKFI ganda.
        </p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="<?= $baseUrl ?>/master/employees" class="btn btn-outline btn-sm">
            <i class="fa-solid fa-users-gear"></i> Master Guru &amp; Pegawai
        </a>
        <a href="<?= $baseUrl ?>/users/school-accounts" class="btn btn-outline btn-sm">
            <i class="fa-solid fa-user-shield"></i> Akun Sekolah
        </a>
    </div>
</div>

<!-- Banner Kebijakan 1 Rapor AKFI -->
<div class="alert alert-info" style="margin-bottom: 20px; border-left: 4px solid #0284c7; display: flex; align-items: flex-start; gap: 12px;">
    <i class="fa-solid fa-circle-info" style="font-size: 18px; margin-top: 2px; color: #0284c7;"></i>
    <div style="font-size: 13px; line-height: 1.55;">
        <strong>Kebijakan Unit Induk &amp; 1 Rapor AKFI (Opsi 1):</strong>
        <ul style="margin: 6px 0 0 18px; padding: 0;">
            <li><strong>1 Pegawai = 1 Akun = 1 Rapor AKFI:</strong> Penilaian AKFI, absensi (Bagian C), dan atasan penilai tetap mengacu pada <strong>Unit Induk</strong> pegawai.</li>
            <li><strong>Kepala Sekolah Mengajar di Unit Lain:</strong> Rapor AKFI tetap menggunakan template Kepala Sekolah Unit Induk; di unit tujuan hanya aktif sebagai peran operasional (misal Guru Mapel).</li>
            <li><strong>Guru Mengajar di 2 Unit:</strong> Kinerja mengajar (nilai KKM, supervisi kelas, administrasi, halaqah) otomatis terakumulasi ke dalam <strong>1 Rapor AKFI</strong> Unit Induknya.</li>
        </ul>
    </div>
</div>

<div style="display: grid; grid-template-columns: minmax(300px, 400px) 1fr; gap: 20px; align-items: start;">
    <!-- KOLOM KIRI: FORM TAMBAH PENUGASAN LINTAS UNIT -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
            <h3 class="card-title" style="margin: 0; font-size: 1rem;">
                <i class="fa-solid fa-plus-circle" style="color: var(--primary); margin-right: 6px;"></i>
                Tambah Tugas Lintas Unit
            </h3>
        </div>
        <form method="POST" action="<?= $baseUrl ?>/master/cross-unit/save">
            <?= CSRF::inputField() ?>
            <div class="card-body" style="padding: 20px;">
                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600;">Cari Pegawai</label>
                    <input type="text" id="empSearchInput" class="form-control" placeholder="Ketik nama atau NIPY pegawai..." onkeyup="filterEmployeeOptions()">
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600;">Pilih Pegawai (Unit Induk) <span style="color: #ef4444;">*</span></label>
                    <select name="employee_id" id="crossEmpSelect" class="form-control" required onchange="syncCrossUnitHint()">
                        <option value="">-- Pilih Pegawai Aktif --</option>
                        <?php foreach ($employees as $emp): ?>
                            <?php $uLabel = $emp['unit_code'] ?: ($emp['unit_name'] ?: 'Yayasan'); ?>
                            <option value="<?= (int)$emp['id'] ?>" data-home-unit="<?= (int)($emp['unit_id'] ?? 0) ?>" data-search="<?= SecurityHelper::escape(strtolower($emp['nama'] . ' ' . ($emp['nipy'] ?? '') . ' ' . $uLabel)) ?>">
                                <?= SecurityHelper::escape($emp['nama']) ?> (Induk: <?= SecurityHelper::escape($uLabel) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="homeUnitHint" style="font-size: 11.5px; color: var(--text-muted); margin-top: 4px;">
                        Hanya menampilkan pegawai aktif yang sudah memiliki akun login.
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600;">Unit Tujuan Penugasan <span style="color: #ef4444;">*</span></label>
                    <select name="target_unit_id" id="crossTargetUnitSelect" class="form-control" required>
                        <option value="">-- Pilih Unit Tujuan --</option>
                        <?php foreach ($units as $u): ?>
                            <option value="<?= (int)$u['id'] ?>">
                                <?= SecurityHelper::escape($u['name']) ?> (<?= SecurityHelper::escape($u['code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 18px;">
                    <label class="form-label" style="font-weight: 600;">Peran di Unit Tujuan <span style="color: #ef4444;">*</span></label>
                    <select name="role_id" class="form-control" required>
                        <option value="">-- Pilih Peran Operasional --</option>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= (int)$r['id'] ?>">
                                <?= SecurityHelper::escape($r['display_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Penugasan Lintas Unit
                </button>
            </div>
        </form>
    </div>

    <!-- KOLOM KANAN: DAFTAR PENUGASAN LINTAS UNIT -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
                <h3 class="card-title" style="margin: 0; font-size: 1rem;">
                    <i class="fa-solid fa-list-check" style="color: #0d9488; margin-right: 6px;"></i>
                    Daftar Penugasan Lintas Unit Aktif (<?= count($assignments) ?>)
                </h3>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                    Pegawai di bawah ini otomatis muncul di pemetaan mapel, jadwal, supervisi, &amp; switcher unit tujuan.
                </div>
            </div>
            <input type="text" id="crossTableSearch" class="form-control" placeholder="Cari nama / unit..." style="width: 210px; font-size: 13px;" onkeyup="filterCrossTable()">
        </div>
        <div class="card-body" style="padding: 0;">
            <div style="overflow-x: auto;">
                <table class="table" id="crossAssignTable">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">No</th>
                            <th>Pegawai &amp; Posisi Induk</th>
                            <th>Unit Induk (AKFI)</th>
                            <th>Unit Tujuan</th>
                            <th>Peran Lintas Unit</th>
                            <th style="width: 110px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($assignments)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 36px; color: var(--text-muted);">
                                    <i class="fa-solid fa-diagram-project" style="font-size: 2rem; opacity: 0.4; display: block; margin-bottom: 8px;"></i>
                                    Belum ada penugasan peran lintas unit yang ditambahkan.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($assignments as $idx => $asg): ?>
                                <tr class="cross-row">
                                    <td style="text-align: center;"><?= $idx + 1 ?></td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-main);"><?= SecurityHelper::escape($asg['nama']) ?></div>
                                        <div style="font-size: 11.5px; color: var(--text-muted);">
                                            NIPY: <?= SecurityHelper::escape($asg['nipy'] ?: '-') ?> &bull; <?= SecurityHelper::escape($asg['position'] ?: '-') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            <?= SecurityHelper::escape($asg['homebase_code'] ?: ($asg['homebase_name'] ?: 'Yayasan')) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            <i class="fa-solid fa-school" style="margin-right: 4px;"></i>
                                            <?= SecurityHelper::escape($asg['target_code'] ?: $asg['target_name']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-success"><?= SecurityHelper::escape($asg['role_display']) ?></span>
                                    </td>
                                    <td style="text-align: center;">
                                        <form method="POST" action="<?= $baseUrl ?>/master/cross-unit/delete" style="display: inline;" onsubmit="return confirm('Cabut peran <?= htmlspecialchars(addslashes($asg['role_display'])) ?> milik <?= htmlspecialchars(addslashes($asg['nama'])) ?> di <?= htmlspecialchars(addslashes($asg['target_name'])) ?>?');">
                                            <?= CSRF::inputField() ?>
                                            <input type="hidden" name="user_id" value="<?= (int)$asg['user_id'] ?>">
                                            <input type="hidden" name="role_id" value="<?= (int)$asg['role_id'] ?>">
                                            <input type="hidden" name="target_unit_id" value="<?= (int)$asg['target_unit_id'] ?>">
                                            <button type="submit" class="btn btn-outline btn-sm" style="color: #ef4444; border-color: rgba(239,68,68,0.35);">
                                                <i class="fa-solid fa-trash-can"></i> Cabut
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
@media (max-width: 960px) {
    .page-header + .alert + div {
        grid-template-columns: 1fr !important;
    }
}
</style>

<script>
function filterEmployeeOptions() {
    const q = (document.getElementById('empSearchInput').value || '').toLowerCase().trim();
    const sel = document.getElementById('crossEmpSelect');
    for (let i = 1; i < sel.options.length; i++) {
        const opt = sel.options[i];
        const txt = opt.getAttribute('data-search') || opt.text.toLowerCase();
        opt.style.display = (!q || txt.includes(q)) ? '' : 'none';
    }
}

function syncCrossUnitHint() {
    const empSel = document.getElementById('crossEmpSelect');
    const unitSel = document.getElementById('crossTargetUnitSelect');
    const opt = empSel.options[empSel.selectedIndex];
    const homeUnit = opt ? opt.getAttribute('data-home-unit') : '';
    for (let i = 1; i < unitSel.options.length; i++) {
        const uOpt = unitSel.options[i];
        const isSame = (homeUnit && uOpt.value === homeUnit);
        uOpt.disabled = isSame;
        if (isSame && uOpt.selected) {
            unitSel.value = '';
        }
    }
}

function filterCrossTable() {
    const q = (document.getElementById('crossTableSearch').value || '').toLowerCase().trim();
    document.querySelectorAll('#crossAssignTable .cross-row').forEach(tr => {
        const txt = tr.innerText.toLowerCase();
        tr.style.display = (!q || txt.includes(q)) ? '' : 'none';
    });
}
</script>
