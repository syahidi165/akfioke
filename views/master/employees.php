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
                                            <button type="button" class="btn btn-outline btn-sm" data-emp="<?= htmlspecialchars(json_encode($e, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>" onclick="editEmployee(JSON.parse(this.getAttribute('data-emp')))" title="Edit Biodata Pegawai & Akun Login">
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
        <form method="POST" action="<?= $baseUrl ?>/master/employees/verify-import" enctype="multipart/form-data" onsubmit="return handleVerifyEmployeeImport(event, this)">
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
                    <i class="fa-solid fa-shield-halved"></i> <strong>Sistem Validasi &amp; Verifikasi Pra-Impor:</strong><br>
                    1. Berkas Excel akan diverifikasi terlebih dahulu sebelum dimasukkan ke database.<br>
                    2. Data yang terdeteksi <strong>duplikat (Nama, NIPY, NIK, Username, Email)</strong> otomatis dipisahkan ke tab <strong>Data Bermasalah</strong>.<br>
                    3. Anda dapat meninjau, menghapus data bermasalah, dan memastikan hanya data valid yang tersimpan.
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Pilih Berkas Excel (.xlsx / .xls) <span style="color:red;">*</span></label>
                    <input type="file" name="file" class="form-control" accept=".xlsx, .xls" required style="padding: 8px;">
                </div>
            </div>
            <div class="modal-footer" style="padding: 12px 20px; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="btn btn-outline" onclick="closeImportEmpModal()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-clipboard-check"></i> Verifikasi Data Excel</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL: KARTU VERIFIKASI PRA-IMPOR DATA GURU & KARYAWAN           -->
<!-- ============================================================== -->
<div class="modal-overlay" id="modalVerifyImportEmp" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px); padding: 20px;">
    <div class="modal-container" style="max-width: 1100px; width: 100%; max-height: 90vh; display: flex; flex-direction: column; border-radius: 14px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); background: #ffffff;">
        
        <!-- Header Modal Popup -->
        <div class="modal-header" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.15rem; color: #ffffff; font-weight: 700;">
                        Kartu Verifikasi Data Guru &amp; Karyawan (Pra-Impor)
                    </h3>
                    <p style="margin: 2px 0 0 0; font-size: 0.8125rem; color: #e0f2fe;">
                        Periksa kecocokan data pegawai sebelum disimpan. Data duplikat pada Nama, NIPY, NIK, Username, atau Email masuk ke tab bermasalah.
                    </p>
                </div>
            </div>
            <button type="button" class="btn btn-outline btn-sm" onclick="closeVerifyEmpModal()" style="color: #ffffff; border-color: rgba(255,255,255,0.4); background: rgba(255,255,255,0.1); font-size: 20px; line-height: 1; padding: 4px 12px; border-radius: 8px;" title="Tutup">&times;</button>
        </div>

        <!-- Ringkasan Status & Toolbar Tombol Aksi -->
        <div style="padding: 14px 24px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; flex-shrink: 0;">
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 6px 14px; font-size: 12.5px;">
                    Total Data: <strong id="vEmpTotalCount" style="color: #0f172a;">0</strong> Pegawai
                </div>
                <div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 6px 14px; font-size: 12.5px; color: #166534;">
                    <i class="fa-solid fa-circle-check" style="color: #16a34a;"></i> Siap Masuk: <strong id="vEmpValidCount">0</strong>
                </div>
                <div id="vEmpInvalidBadgeBox" style="background: #fef2f2; border: 1px solid #fca5a5; border-radius: 8px; padding: 6px 14px; font-size: 12.5px; color: #991b1b;">
                    <i class="fa-solid fa-triangle-exclamation" style="color: #dc2626;"></i> Bermasalah / Duplikat: <strong id="vEmpInvalidCount">0</strong>
                </div>
            </div>

            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                <button type="button" id="btnDropInvalidEmp" class="btn btn-sm btn-outline-danger" onclick="dropAllInvalidEmpRows()" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600; padding: 6px 12px; font-size: 12px; border-radius: 6px; border: 1px solid #ef4444; color: #ef4444; background: #fff;">
                    <i class="fa-solid fa-trash-can"></i> Hapus Semua Data Bermasalah
                </button>
                <div class="btn-group" style="display: inline-flex; border: 1px solid #cbd5e1; border-radius: 6px; overflow: hidden;">
                    <button type="button" class="btn btn-sm" id="btnEmpFilterAll" onclick="filterVerifyEmpRows('all')" style="padding: 5px 12px; font-size: 12px; background: #0284c7; color: #fff; border: none; cursor: pointer;">Semua</button>
                    <button type="button" class="btn btn-sm" id="btnEmpFilterValid" onclick="filterVerifyEmpRows('valid')" style="padding: 5px 12px; font-size: 12px; background: #fff; color: #475569; border: none; border-left: 1px solid #cbd5e1; cursor: pointer;">Hanya Valid</button>
                    <button type="button" class="btn btn-sm" id="btnEmpFilterInvalid" onclick="filterVerifyEmpRows('invalid')" style="padding: 5px 12px; font-size: 12px; background: #fff; color: #475569; border: none; border-left: 1px solid #cbd5e1; cursor: pointer;">Bermasalah</button>
                </div>
            </div>
        </div>

        <!-- Tabel Baris Data Terverifikasi (Scrollable) -->
        <div class="table-responsive" style="flex: 1; overflow-y: auto; max-height: 55vh; padding: 0;">
            <table class="table" style="margin-bottom: 0; font-size: 12px; width: 100%; border-collapse: collapse;">
                <thead style="position: sticky; top: 0; background: #f1f5f9; z-index: 2; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                    <tr>
                        <th style="width: 45px; text-align: center; padding: 10px 8px;">No</th>
                        <th style="padding: 10px 12px;">Nama Pegawai &amp; NIPY / NIK</th>
                        <th style="width: 70px; text-align: center; padding: 10px 8px;">JK</th>
                        <th style="padding: 10px 12px;">Unit Satuan</th>
                        <th style="padding: 10px 12px;">Peran &amp; Jabatan</th>
                        <th style="padding: 10px 12px;">Akun Login (Username &amp; Email)</th>
                        <th style="width: 250px; text-align: center; padding: 10px 12px;">Status Validasi</th>
                        <th style="width: 80px; text-align: center; padding: 10px 8px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="verifyEmpTableBody">
                    <!-- Diinjeksi oleh JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Footer Modal Popup -->
        <div class="modal-footer" style="padding: 16px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; flex-shrink: 0;">
            <div style="font-size: 12px; color: #64748b; line-height: 1.4;">
                <i class="fa-solid fa-circle-info" style="color: #0284c7; margin-right: 4px;"></i>
                Hanya data pegawai berstatus <strong>Siap Masuk (Valid)</strong> yang akan disimpan ke database.
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <button type="button" class="btn btn-outline" onclick="closeVerifyEmpModal()" style="padding: 8px 18px; border-radius: 8px;">
                    Batalkan
                </button>
                <button type="button" class="btn btn-success" id="btnCommitEmpImport" onclick="commitVerifiedEmpImport()" style="padding: 9px 22px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; background: #10b981; border-color: #10b981; color: #ffffff; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.25); cursor: pointer;">
                    <i class="fa-solid fa-cloud-arrow-down"></i> Masukkan Data ke Sistem (<span id="btnCommitEmpCount">0</span> Pegawai Valid)
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ==============================================================
// VERIFIKASI PRA-IMPOR GURU & KARYAWAN
// ==============================================================
let verifiedEmpRows = [];
let currentVerifyEmpFilter = 'all';

function escapeEmpHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function handleVerifyEmployeeImport(event, formElement) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    const submitBtn = formElement.querySelector('button[type="submit"]');
    const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memvalidasi Data Pegawai...';
    }

    const formData = new FormData(formElement);

    fetch('<?= $baseUrl ?>/master/employees/verify-import', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        }

        if (!data.success) {
            alert(data.message || 'Gagal memvalidasi berkas.');
            return;
        }

        verifiedEmpRows = data.rows || [];
        closeImportEmpModal();
        formElement.reset();

        openVerifyEmpModal();
        currentVerifyEmpFilter = 'all';
        renderVerifyEmpTable();
    })
    .catch(err => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        }
        alert('Terjadi kesalahan saat memproses unggahan: ' + err.message);
    });

    return false;
}

function filterVerifyEmpRows(type) {
    currentVerifyEmpFilter = type;
    renderVerifyEmpTable();
}

function renderVerifyEmpTable() {
    const total = verifiedEmpRows.length;
    const validRows = verifiedEmpRows.filter(r => r.is_valid);
    const validCount = validRows.length;
    const invalidCount = total - validCount;

    const elTotal = document.getElementById('vEmpTotalCount');
    const elValid = document.getElementById('vEmpValidCount');
    const elInvalid = document.getElementById('vEmpInvalidCount');
    const elCommit = document.getElementById('btnCommitEmpCount');
    const btnCommit = document.getElementById('btnCommitEmpImport');
    const btnDropInvalid = document.getElementById('btnDropInvalidEmp');
    const badgeInvalidBox = document.getElementById('vEmpInvalidBadgeBox');

    if (elTotal) elTotal.innerText = total;
    if (elValid) elValid.innerText = validCount;
    if (elInvalid) elInvalid.innerText = invalidCount;
    if (elCommit) elCommit.innerText = validCount;

    if (badgeInvalidBox) badgeInvalidBox.style.display = invalidCount > 0 ? 'block' : 'none';
    if (btnDropInvalid) btnDropInvalid.style.display = invalidCount > 0 ? 'inline-flex' : 'none';

    if (btnCommit) {
        btnCommit.disabled = (validCount === 0);
        btnCommit.style.opacity = validCount === 0 ? '0.5' : '1';
        btnCommit.style.cursor = validCount === 0 ? 'not-allowed' : 'pointer';
    }

    const btnAll = document.getElementById('btnEmpFilterAll');
    const btnV = document.getElementById('btnEmpFilterValid');
    const btnInv = document.getElementById('btnEmpFilterInvalid');
    if (btnAll && btnV && btnInv) {
        btnAll.style.background = currentVerifyEmpFilter === 'all' ? '#0284c7' : '#fff';
        btnAll.style.color = currentVerifyEmpFilter === 'all' ? '#fff' : '#475569';
        btnV.style.background = currentVerifyEmpFilter === 'valid' ? '#10b981' : '#fff';
        btnV.style.color = currentVerifyEmpFilter === 'valid' ? '#fff' : '#475569';
        btnInv.style.background = currentVerifyEmpFilter === 'invalid' ? '#ef4444' : '#fff';
        btnInv.style.color = currentVerifyEmpFilter === 'invalid' ? '#fff' : '#475569';
    }

    const tbody = document.getElementById('verifyEmpTableBody');
    if (!tbody) return;

    let displayRows = verifiedEmpRows;
    if (currentVerifyEmpFilter === 'valid') {
        displayRows = verifiedEmpRows.filter(r => r.is_valid);
    } else if (currentVerifyEmpFilter === 'invalid') {
        displayRows = verifiedEmpRows.filter(r => !r.is_valid);
    }

    if (displayRows.length === 0) {
        if (total === 0) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 32px 20px; color: #64748b;">
                <i class="fa-solid fa-folder-open" style="font-size: 2rem; color: #cbd5e1; margin-bottom: 8px; display: block;"></i>
                Semua baris pratinjau telah dihapus.
            </td></tr>`;
        } else {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 24px 20px; color: #64748b;">
                Tidak ada data pegawai untuk filter status ini.
            </td></tr>`;
        }
        return;
    }

    let html = '';
    displayRows.forEach((row, index) => {
        const bgRow = row.is_valid ? (index % 2 === 0 ? '#ffffff' : '#fcfdfd') : '#fff5f5';
        const borderRow = row.is_valid ? 'border-bottom: 1px solid #f1f5f9;' : 'border-bottom: 1px solid #fed7d7;';

        const genderBadge = row.gender === 'P'
            ? '<span class="badge" style="background: #fdf2f8; color: #db2777; border: 1px solid #fbcfe8;">P</span>'
            : '<span class="badge" style="background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe;">L</span>';

        let statusCell = '';
        if (row.is_valid) {
            statusCell = `<span class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; padding: 4px 10px; font-weight: 600;">
                <i class="fa-solid fa-circle-check"></i> Siap Masuk
            </span>`;
        } else {
            const errList = (row.errors || []).map(err => `<li>${escapeEmpHtml(err)}</li>`).join('');
            statusCell = `<div>
                <span class="badge" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; padding: 3px 8px; margin-bottom: 4px; display: inline-block; font-weight: 700;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Bermasalah
                </span>
                <ul style="margin: 0; padding-left: 14px; text-align: left; font-size: 11px; color: #dc2626; line-height: 1.35;">
                    ${errList}
                </ul>
            </div>`;
        }

        html += `<tr style="${bgRow} ${borderRow}">
            <td style="text-align: center; color: #64748b; font-weight: 600;">${index + 1}</td>
            <td>
                <div style="font-weight: 700; color: #0f172a; font-size: 12.5px;">${escapeEmpHtml(row.nama)}</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                    NIPY: <code style="background: #f1f5f9; padding: 1px 4px; border-radius: 4px; color: #0284c7;">${escapeEmpHtml(row.nipy)}</code>
                    ${row.nik ? ` | NIK: <code>${escapeEmpHtml(row.nik)}</code>` : ''}
                </div>
            </td>
            <td style="text-align: center;">${genderBadge}</td>
            <td>
                <span style="font-weight: 600; color: #334155;"><i class="fa-solid fa-school" style="font-size: 10px; color: #64748b;"></i> ${escapeEmpHtml(row.unit_name)}</span>
            </td>
            <td>
                <div style="font-weight: 600; color: #0284c7;">${escapeEmpHtml(row.role_display)}</div>
                <div style="font-size: 11px; color: #64748b;">${escapeEmpHtml(row.posisi)} &bull; ${escapeEmpHtml(row.pendidikan)}</div>
            </td>
            <td>
                <div style="font-family: monospace; font-weight: 600; color: #0f172a;">@${escapeEmpHtml(row.username)}</div>
                ${row.email ? `<div style="font-size: 11px; color: #0284c7;"><i class="fa-solid fa-envelope"></i> ${escapeEmpHtml(row.email)}</div>` : ''}
                ${row.phone ? `<div style="font-size: 11px; color: #166534;"><i class="fa-brands fa-whatsapp"></i> ${escapeEmpHtml(row.phone)}</div>` : ''}
            </td>
            <td style="text-align: center; vertical-align: middle;">${statusCell}</td>
            <td style="text-align: center; vertical-align: middle;">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deletePreviewEmpRow(${row.temp_id})" title="Hapus baris ini dari pratinjau" style="padding: 4px 8px; font-size: 11px; border-radius: 6px;">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </td>
        </tr>`;
    });

    tbody.innerHTML = html;
}

function deletePreviewEmpRow(tempId) {
    const idx = verifiedEmpRows.findIndex(r => r.temp_id === tempId);
    if (idx !== -1) {
        verifiedEmpRows.splice(idx, 1);
        renderVerifyEmpTable();
    }
}

function dropAllInvalidEmpRows() {
    const invalidCount = verifiedEmpRows.filter(r => !r.is_valid).length;
    if (invalidCount === 0) return;

    if (!confirm('Hapus seluruh ' + invalidCount + ' baris data pegawai yang bermasalah?\n\nHanya data yang valid yang akan tersisa untuk dimasukkan ke sistem.')) {
        return;
    }

    verifiedEmpRows = verifiedEmpRows.filter(r => r.is_valid);
    currentVerifyEmpFilter = 'all';
    renderVerifyEmpTable();
}

function openVerifyEmpModal() {
    const modal = document.getElementById('modalVerifyImportEmp');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('active');
        modal.classList.add('show');
    }
}

function closeVerifyEmpModal() {
    if (verifiedEmpRows && verifiedEmpRows.length > 0) {
        if (!confirm('Tutup kartu verifikasi? Data pratinjau yang belum disimpan tidak akan masuk ke sistem database.')) {
            return;
        }
    }
    const modal = document.getElementById('modalVerifyImportEmp');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('active');
        modal.classList.remove('show');
    }
    verifiedEmpRows = [];
}

function commitVerifiedEmpImport() {
    const validRows = verifiedEmpRows.filter(r => r.is_valid);
    if (validRows.length === 0) {
        alert('Tidak ada data pegawai yang valid untuk disimpan.');
        return;
    }

    if (!confirm('Simpan ' + validRows.length + ' data pegawai valid ke sistem database? Akun login dan data master akan dibuat otomatis.')) {
        return;
    }

    const btnCommit = document.getElementById('btnCommitEmpImport');
    const originalHtml = btnCommit ? btnCommit.innerHTML : '';
    if (btnCommit) {
        btnCommit.disabled = true;
        btnCommit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan ke Database...';
    }

    const formData = new FormData();
    formData.append('employees_json', JSON.stringify(validRows));
    formData.append('csrf_token', '<?= CSRF::token() ?>');

    fetch('<?= $baseUrl ?>/master/employees/confirm-import', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (btnCommit) {
            btnCommit.disabled = false;
            btnCommit.innerHTML = originalHtml;
        }

        if (data.success) {
            window.location.reload();
        } else {
            alert('Gagal menyimpan data pegawai: ' + (data.message || 'Terjadi kesalahan'));
        }
    })
    .catch(err => {
        if (btnCommit) {
            btnCommit.disabled = false;
            btnCommit.innerHTML = originalHtml;
        }
        alert('Terjadi kesalahan koneksi saat menyimpan data: ' + err.message);
    });
}

// Inisialisasi jika ada data sesi
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($pendingImport) && !empty($pendingImport['rows'])): ?>
        const initialImport = <?= json_encode($pendingImport) ?>;
        if (initialImport && initialImport.rows && initialImport.rows.length > 0) {
            verifiedEmpRows = initialImport.rows;
            openVerifyEmpModal();
            currentVerifyEmpFilter = 'all';
            renderVerifyEmpTable();
        }
    <?php endif; ?>
});
</script>
<?php endif; ?>
