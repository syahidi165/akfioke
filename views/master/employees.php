<?php
// views/master/employees.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$canEdit = Auth::hasRole('operator_yayasan');
?>
<div class="page-header">
    <div>
        <h1 class="page-title">Data Guru & Karyawan Yayasan</h1>
        <div class="page-subtitle">Master data pendidik dan tenaga kependidikan untuk administrasi dan instrumen penilaian AKFI.</div>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="<?= $baseUrl ?>/master/employees/export?unit_id=<?= $unitFilter ?>" class="btn btn-outline">
            <i class="fa-solid fa-file-excel" style="color: #10b981;"></i> Ekspor Excel (.xlsx)
        </a>
        <a href="<?= $baseUrl ?>/master/employees/template?unit_id=<?= $unitFilter ?>" class="btn btn-outline">
            <i class="fa-solid fa-download" style="color: var(--primary);"></i> Unduh Template Excel
        </a>
        <?php if ($canEdit): ?>
            <button type="button" class="btn btn-outline" onclick="openImportEmpModal()">
                <i class="fa-solid fa-file-import" style="color: #0284c7;"></i> Impor Pegawai Excel
            </button>
            <button type="button" class="btn btn-primary" onclick="openEmployeeModal()">
                <i class="fa-solid fa-user-plus"></i> Tambah Pegawai
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Filter Unit -->
<div class="card" style="margin-bottom: 16px;">
    <div class="card-body" style="padding: 12px 20px;">
        <form method="GET" action="<?= $baseUrl ?>/master/employees" style="display: flex; align-items: center; gap: 12px;">
            <label style="font-size: 13px; font-weight: 600; color: var(--text-muted);">Filter Unit Tugas:</label>
            <select name="unit_id" class="header-select" onchange="this.form.submit()">
                <option value="0">-- Seluruh Unit Yayasan --</option>
                <option value="-1" <?= $unitFilter === -1 ? 'selected' : '' ?>>Kantor Yayasan (Lintas Unit)</option>
                <?php foreach ($units as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= $unitFilter === (int)$u['id'] ? 'selected' : '' ?>>
                        <?= SecurityHelper::escape($u['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th width="10%">NIPY</th>
                        <th width="18%">Nama & Posisi</th>
                        <th width="12%">Unit Satuan</th>
                        <th width="20%">Akun Login & Hak Akses</th>
                        <th width="15%">Atasan Langsung</th>
                        <th width="13%">Kontak & NIK</th>
                        <?php if ($canEdit): ?><th width="12%" class="text-center">Aksi</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($employees)): ?>
                        <tr><td colspan="7" style="text-align: center; padding: 24px; color: var(--text-muted);">Tidak ada data pegawai.</td></tr>
                    <?php else: ?>
                        <?php foreach ($employees as $e): ?>
                            <tr>
                                <td><span class="badge badge-secondary"><?= SecurityHelper::escape($e['nipy']) ?></span></td>
                                <td>
                                    <strong><?= SecurityHelper::escape($e['nama']) ?></strong>
                                    <div style="display: flex; align-items: center; gap: 6px; margin-top: 2px;">
                                        <span style="font-size: 11px; color: var(--primary); font-weight: 600;"><?= SecurityHelper::escape($e['level_posisi']) ?></span>
                                        <span class="badge badge-outline" style="font-size: 10px; padding: 1px 5px; color: #4338ca; border-color: #c7d2fe; background: #eef2ff;">
                                            <i class="fa-solid fa-graduation-cap"></i> <?= SecurityHelper::escape($e['tingkat_pendidikan'] ?? 'S1') ?>
                                        </span>
                                    </div>
                                </td>
                                <td><span class="badge <?= empty($e['unit_name']) ? 'badge-warning' : 'badge-info' ?>"><?= SecurityHelper::escape($e['unit_name'] ?: 'Kantor Yayasan') ?></span></td>
                                <td>
                                    <?php if (!empty($e['account_user_id'])): ?>
                                        <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
                                            <code><?= SecurityHelper::escape($e['username']) ?></code>
                                            <span class="badge <?= $e['user_is_active'] ? 'badge-success' : 'badge-danger' ?>" style="font-size: 10px; padding: 2px 6px;">
                                                <?= $e['user_is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                            </span>
                                        </div>
                                        <div style="font-size: 11px; color: #475569;">
                                            <i class="fa-solid fa-id-badge" style="color: var(--primary); font-size: 10px;"></i>
                                            <?= SecurityHelper::escape($e['role_names'] ?: 'Belum ada peran') ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge badge-warning" style="font-size: 11px; padding: 4px 8px;">
                                            <i class="fa-solid fa-triangle-exclamation"></i> Belum ada akun
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><?= SecurityHelper::escape($e['atasan_nama'] ?? '-') ?></div>
                                    <div style="font-size: 11px; color: var(--text-muted);"><?= SecurityHelper::escape($e['no_telp_atasan'] ?? '') ?></div>
                                </td>
                                <td>
                                    <div><?= SecurityHelper::escape($e['contact_phone'] ?? '-') ?></div>
                                    <div style="font-size: 11px; color: var(--text-muted);">NIK: <?= SecurityHelper::maskNik($e['nik'], $canEdit) ?></div>
                                </td>
                                <?php if ($canEdit): ?>
                                    <td class="text-center">
                                        <div style="display: flex; gap: 4px; justify-content: center; align-items: center;">
                                            <!-- Edit Pegawai & Akun -->
                                            <button type="button" class="btn btn-outline btn-sm" onclick='editEmployee(<?= json_encode($e) ?>)' title="Edit Biodata Pegawai & Akun Login">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>

                                            <?php if (!empty($e['account_user_id'])): ?>
                                                <!-- Reset Password Akun -->
                                                <form method="POST" action="<?= $baseUrl ?>/users/school-accounts/save" onsubmit="return confirm('Reset kata sandi akun <?= SecurityHelper::escape($e['username']) ?> ke password123?')">
                                                    <?= CSRF::inputField() ?>
                                                    <input type="hidden" name="id" value="<?= $e['account_user_id'] ?>">
                                                    <input type="hidden" name="action" value="reset_password">
                                                    <input type="hidden" name="return_to" value="employees">
                                                    <button type="submit" class="btn btn-warning btn-sm" title="Reset Password ke password123">
                                                        <i class="fa-solid fa-key"></i>
                                                    </button>
                                                </form>

                                                <!-- Toggle Aktif/Nonaktif Akun -->
                                                <form method="POST" action="<?= $baseUrl ?>/users/school-accounts/save">
                                                    <?= CSRF::inputField() ?>
                                                    <input type="hidden" name="id" value="<?= $e['account_user_id'] ?>">
                                                    <input type="hidden" name="action" value="toggle_active">
                                                    <input type="hidden" name="return_to" value="employees">
                                                    <button type="submit" class="btn <?= $e['user_is_active'] ? 'btn-outline' : 'btn-success' ?> btn-sm" title="<?= $e['user_is_active'] ? 'Nonaktifkan Akun' : 'Aktifkan Akun' ?>">
                                                        <i class="fa-solid fa-power-off"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <!-- Soft Delete Pegawai -->
                                            <form method="POST" action="<?= $baseUrl ?>/master/employees/delete" onsubmit="return confirm('Nonaktifkan pegawai ini (soft delete)? Histori penilaian AKFI akan tetap tersimpan.')">
                                                <?= CSRF::inputField() ?>
                                                <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" title="Nonaktifkan Pegawai"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form Pegawai & Akun -->
<?php if ($canEdit): ?>
<div class="modal-overlay" id="employeeModal">
    <div class="modal-container" style="max-width: 720px; max-height: 90vh; display: flex; flex-direction: column;">
        <form method="POST" action="<?= $baseUrl ?>/master/employees/save" style="display: flex; flex-direction: column; overflow: hidden;">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="id" id="emp_id" value="0">
            
            <div class="modal-header">
                <h3 class="card-title" id="modalEmpTitle">Tambah Guru / Karyawan & Akun</h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeEmployeeModal()">&times;</button>
            </div>
            <div class="modal-body" style="overflow-y: auto; padding: 20px;">
                <!-- BAGIAN 1: BIODATA PEGAWAI -->
                <div style="font-weight: 700; font-size: 13px; color: var(--primary); margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-address-card"></i> 1. Biodata Pendidik / Tenaga Kependidikan
                </div>

                <div class="grid-cols-2">
                    <div class="form-group">
                        <label class="form-label">NIPY (Nomor Induk Pegawai) <span style="color:red;">*</span></label>
                        <input type="text" name="nipy" id="emp_nipy" class="form-control" required placeholder="Contoh: SD-GUR-012">
                    </div>
                    <div class="form-group">
                        <label class="form-label">NIK (Nomor Induk Kependudukan)</label>
                        <input type="text" name="nik" id="emp_nik" class="form-control" placeholder="16 digit NIK">
                    </div>
                </div>

                <div class="grid-cols-3">
                    <div class="form-group" style="grid-column: span 1;">
                        <label class="form-label">Nama Lengkap & Gelar <span style="color:red;">*</span></label>
                        <input type="text" name="nama" id="emp_nama" class="form-control" required placeholder="Contoh: Aisyah Wardani, S.Pd">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="gender" id="emp_gender" class="form-control">
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tingkat Pendidikan <span style="color:red;">*</span></label>
                        <select name="tingkat_pendidikan" id="emp_tingkat_pendidikan" class="form-control" required>
                            <option value="S3">S3 (Doktor)</option>
                            <option value="S2">S2 (Magister)</option>
                            <option value="S1" selected>S1 / D4 (Sarjana)</option>
                            <option value="D3">D3 (Diploma 3)</option>
                            <option value="D1/D2">D1 / D2</option>
                            <option value="SMA/SMK">SMA / SMK / Sederajat</option>
                        </select>
                    </div>
                </div>

                <div class="grid-cols-2">
                    <div class="form-group">
                        <label class="form-label">Tanggal Masuk Tugas (TMT)</label>
                        <input type="date" name="tmt" id="emp_tmt" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Level / Posisi Tugas</label>
                        <input type="text" name="level_posisi" id="emp_level" class="form-control" required placeholder="Guru Mapel / Walas / TU / CS / Security">
                    </div>
                </div>

                <div class="grid-cols-2">
                    <div class="form-group">
                        <label class="form-label">Unit Satuan Pendidikan</label>
                        <select name="unit_id" id="emp_unit_id" class="form-control">
                            <option value="">-- Kantor Yayasan (Lintas Unit) --</option>
                            <?php foreach ($units as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= SecurityHelper::escape($u['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Atasan Langsung (Untuk Rapor AKFI)</label>
                        <select name="atasan_langsung_id" id="emp_atasan_id" class="form-control">
                            <option value="">-- Pilih Atasan Langsung --</option>
                            <?php foreach ($superiors as $sup): ?>
                                <option value="<?= $sup['id'] ?>"><?= SecurityHelper::escape($sup['nama']) ?> (<?= SecurityHelper::escape($sup['level_posisi']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid-cols-3">
                    <div class="form-group">
                        <label class="form-label">No. Telepon Atasan</label>
                        <input type="text" name="no_telp_atasan" id="emp_telp_atasan" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. WhatsApp / HP</label>
                        <input type="text" name="contact_phone" id="emp_contact" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Alamat Email</label>
                        <input type="email" name="email" id="emp_email" class="form-control">
                    </div>
                </div>

                <!-- BAGIAN 2: AKUN PENGGUNA SISTEM -->
                <div style="margin-top: 18px; padding-top: 16px; border-top: 2px dashed #e2e8f0;">
                    <div style="font-weight: 700; font-size: 13px; color: #0284c7; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-user-lock"></i> 2. Akun Pengguna & Hak Akses (Login Sistem)
                        </div>
                        <div id="emp_account_badge"></div>
                    </div>

                    <div class="grid-cols-2">
                        <div class="form-group">
                            <label class="form-label" style="display: flex; justify-content: space-between; align-items: center;">
                                <span>Username Login</span>
                                <span style="font-size: 11px; color: #64748b; font-weight: normal;"><i class="fa-solid fa-lock"></i> Auto dari NIPY</span>
                            </label>
                            <input type="text" name="username" id="emp_username" class="form-control" placeholder="Otomatis mengikuti NIPY" readonly style="background-color: #f1f5f9; cursor: not-allowed; font-weight: 600; color: #334155;">
                            <small style="color: var(--text-muted); font-size: 11px;">Akun otomatis dibuat dari NIPY (huruf kecil) agar tidak terjadi nama akun ganda.</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kata Sandi (Password)</label>
                            <input type="password" name="password" id="emp_password" class="form-control" placeholder="Kosongkan jika tidak ingin diubah">
                            <small style="color: var(--text-muted); font-size: 11px;">Default untuk akun baru: <code>password123</code></small>
                        </div>
                    </div>

                    <div class="grid-cols-2">
                        <div class="form-group">
                            <label class="form-label">Status Akun Login</label>
                            <select name="user_is_active" id="emp_user_is_active" class="form-control">
                                <option value="1">Aktif (Bisa Login)</option>
                                <option value="0">Nonaktif (Dilarang Login)</option>
                            </select>
                        </div>
                        <div class="form-group" style="display: flex; align-items: flex-end; padding-bottom: 8px;">
                            <small style="color: var(--text-muted); font-size: 12px;">
                                Mengisi username secara otomatis membuat atau menyinkronkan akun tanpa duplikasi input.
                            </small>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 10px;">
                        <label class="form-label">Pilih Peran (Roles) Pengguna di Aplikasi:</label>
                        <div style="max-height: 140px; overflow-y: auto; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px; display: grid; grid-template-columns: 1fr 1fr; gap: 6px; background: #f8fafc;">
                            <?php foreach ($roles as $r): ?>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; cursor: pointer; padding: 5px 8px; border-radius: 4px; background: #fff; border: 1px solid #e2e8f0;">
                                    <input type="checkbox" name="roles[]" class="emp-role-cb" value="<?= $r['id'] ?>" <?= ($r['name'] === 'guru_mapel') ? 'data-default="1"' : '' ?>>
                                    <span><?= SecurityHelper::escape($r['display_name']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <small style="color: var(--text-muted); font-size: 11px;">Peran pertama yang dipilih akan menjadi peran aktif utama akun.</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="padding: 14px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0;">
                <button type="button" class="btn btn-outline" onclick="closeEmployeeModal()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan Data & Akun</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEmployeeModal() {
    document.getElementById('emp_id').value = '0';
    document.getElementById('modalEmpTitle').innerText = 'Tambah Guru / Karyawan & Akun Pengguna';
    document.getElementById('emp_nipy').value = '';
    document.getElementById('emp_nik').value = '';
    document.getElementById('emp_nama').value = '';
    document.getElementById('emp_gender').value = 'L';
    document.getElementById('emp_tingkat_pendidikan').value = 'S1';
    document.getElementById('emp_tmt').value = '<?= date('Y-m-d') ?>';
    document.getElementById('emp_level').value = 'Guru Mapel';
    document.getElementById('emp_unit_id').value = '';
    document.getElementById('emp_telp_atasan').value = '';
    document.getElementById('emp_contact').value = '';
    document.getElementById('emp_email').value = '';
    document.getElementById('emp_username').value = '';
    document.getElementById('emp_password').value = 'password123';
    document.getElementById('emp_user_is_active').value = '1';
    document.getElementById('emp_account_badge').innerHTML = '<span class="badge badge-info" style="font-size:11px;">Akun baru otomatis dibuat jika username diisi</span>';
    
    document.querySelectorAll('.emp-role-cb').forEach(cb => {
        cb.checked = (cb.getAttribute('data-default') === '1');
    });

    document.getElementById('employeeModal').classList.add('active');
}

function editEmployee(e) {
    document.getElementById('emp_id').value = e.id;
    document.getElementById('modalEmpTitle').innerText = 'Edit Data Pegawai & Akun: ' + e.nama;
    document.getElementById('emp_nipy').value = e.nipy;
    document.getElementById('emp_nik').value = e.nik || '';
    document.getElementById('emp_nama').value = e.nama;
    document.getElementById('emp_gender').value = e.gender;
    document.getElementById('emp_tingkat_pendidikan').value = e.tingkat_pendidikan || 'S1';
    document.getElementById('emp_tmt').value = e.tmt || '';
    document.getElementById('emp_level').value = e.level_posisi;
    document.getElementById('emp_unit_id').value = e.unit_id || '';
    document.getElementById('emp_atasan_id').value = e.atasan_langsung_id || '';
    document.getElementById('emp_telp_atasan').value = e.no_telp_atasan || '';
    document.getElementById('emp_contact').value = e.contact_phone || '';
    document.getElementById('emp_email').value = e.email || '';

    // Account fields
    let initialUsername = e.username || (e.nipy ? e.nipy.trim().toLowerCase().replace(/\s+/g, '') : '');
    document.getElementById('emp_username').value = initialUsername;
    document.getElementById('emp_password').value = '';
    document.getElementById('emp_user_is_active').value = (e.user_is_active !== null && e.user_is_active !== undefined) ? e.user_is_active : '1';
    
    if (e.account_user_id || initialUsername) {
        document.getElementById('emp_account_badge').innerHTML = '<span class="badge badge-success" style="font-size:11px;"><i class="fa-solid fa-circle-check"></i> Akun Tertaut: ' + initialUsername + '</span>';
    } else {
        document.getElementById('emp_account_badge').innerHTML = '<span class="badge badge-warning" style="font-size:11px;"><i class="fa-solid fa-triangle-exclamation"></i> Belum Memiliki Akun</span>';
    }

    // Parse role_data: e.g. "1:admin:Administrator; 2:guru_mapel:Guru Mapel"
    let roleIds = [];
    if (e.role_data) {
        let parts = e.role_data.split(';');
        parts.forEach(p => {
            let rParts = p.trim().split(':');
            if (rParts[0]) roleIds.push(parseInt(rParts[0]));
        });
    }
    document.querySelectorAll('.emp-role-cb').forEach(cb => {
        cb.checked = roleIds.includes(parseInt(cb.value));
    });

    document.getElementById('employeeModal').classList.add('active');
}

function closeEmployeeModal() {
    document.getElementById('employeeModal').classList.remove('active');
}
function openImportEmpModal() {
    document.getElementById('modalImportEmployees').classList.add('active');
}
function closeImportEmpModal() {
    document.getElementById('modalImportEmployees').classList.remove('active');
}

document.addEventListener('DOMContentLoaded', function() {
    const nipyInput = document.getElementById('emp_nipy');
    const usernameInput = document.getElementById('emp_username');
    if (nipyInput && usernameInput) {
        nipyInput.addEventListener('input', function() {
            usernameInput.value = this.value.trim().toLowerCase().replace(/\s+/g, '');
        });
    }
});
</script>

<!-- Modal: Import Pegawai Excel -->
<div class="modal-overlay" id="modalImportEmployees">
    <div class="modal-container" style="max-width: 520px; display: flex; flex-direction: column;">
        <form method="POST" action="<?= $baseUrl ?>/master/employees/import" enctype="multipart/form-data">
            <?= CSRF::inputField() ?>
            <div class="modal-header">
                <h3 class="card-title"><i class="fa-solid fa-file-import" style="color: #0284c7;"></i> Impor Pegawai & Akun dari Excel</h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeImportEmpModal()">&times;</button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <div style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600;">Default Unit Satuan Penempatan</label>
                    <select name="unit_id" class="form-control" style="width: 100%;">
                        <option value="0">-- Ikuti Kolom Unit di Berkas Excel (Otomatis Multi-Unit) --</option>
                        <option value="-1" <?= ($unitFilter === -1) ? 'selected' : '' ?>>Kantor Yayasan (Lintas Unit)</option>
                        <?php foreach ($units as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= ($unitFilter == $u['id']) ? 'selected' : '' ?>><?= SecurityHelper::escape($u['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: var(--text-muted); font-size: 11px;">Jika memilih "Ikuti Kolom Unit di Berkas Excel", sistem otomatis mencocokkan nama/kode unit pada setiap baris data pegawai.</small>
                </div>

                <div class="alert alert-info" style="margin-bottom: 16px; font-size: 12px; line-height: 1.5; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 12px; border-radius: 6px; color: #166534;">
                    <i class="fa-solid fa-shield-halved"></i> <strong>Sistem Validasi Format Terintegrasi:</strong><br>
                    1. Unduh template resmi melalui tombol <strong>Unduh Template Excel</strong> di atas.<br>
                    2. Template mendukung pengisian <strong>Unit Satuan Tugas</strong>, <strong>Tingkat Pendidikan</strong>, dan <strong>TMT (Tanggal Mulai Tugas)</strong>.<br>
                    3. Kolom <strong>Peran Sistem (Role)</strong>, <strong>Unit Satuan</strong>, <strong>Tingkat Pendidikan</strong>, dan <strong>Jenis Kelamin (L/P)</strong> dilengkapi dropdown otomatis.<br>
                    4. Username & akun login dibuat otomatis dari NIPY/Nama jika dikosongkan (Password default: <code>password123</code>).
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Pilih Berkas Excel (.xlsx / .xls) <span style="color:red;">*</span></label>
                    <input type="file" name="file" class="form-control" accept=".xlsx, .xls" required style="padding: 8px;">
                </div>
            </div>
            <div class="modal-footer" style="padding: 12px 20px; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="btn btn-outline" onclick="closeImportEmpModal()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-upload"></i> Unggah & Impor Pegawai</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
