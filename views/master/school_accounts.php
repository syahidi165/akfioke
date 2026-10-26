<?php
// views/master/school_accounts.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$operatorList = [];
$schoolStaffList = [];

if ($isYayasan) {
    foreach ($users as $u) {
        if (strpos($u['role_names'], 'Operator Sekolah') !== false) {
            $operatorList[] = $u;
        } else {
            $schoolStaffList[] = $u;
        }
    }
} else {
    $schoolStaffList = $users;
}
?>
<div class="page-header d-flex justify-content-between align-items-center" style="margin-bottom: 24px;">
    <div>
        <h1 class="page-title"><?= SecurityHelper::escape($pageTitle) ?></h1>
        <div class="page-subtitle">
            <?= $isYayasan ? 'Kelola akun Operator Sekolah dan seluruh pendidik/staf pada setiap unit pendidikan Yayasan Islam Al Kahfi Batam.' : 'Kelola akun guru, wali kelas, tata usaha, dan staf operasional pada unit satuan pendidikan Anda.' ?>
        </div>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <?php if ($isYayasan): ?>
            <a href="<?= $baseUrl ?>/master/employees/template<?= $unitFilter ? '?unit_id='.$unitFilter : '' ?>" class="btn btn-outline">
                <i class="fa-solid fa-download" style="color: var(--primary);"></i> Unduh Template Excel
            </a>
            <button type="button" class="btn btn-outline" onclick="openImportStaffModal()">
                <i class="fa-solid fa-file-import" style="color: #0284c7;"></i> Impor Pegawai Excel
            </button>
            <button type="button" class="btn btn-primary" onclick="openCreateOperatorModal()">
                <i class="fa-solid fa-user-shield"></i> Buat Operator Sekolah
            </button>
        <?php else: ?>
            <button type="button" class="btn btn-primary" onclick="openCreateStaffModal()">
                <i class="fa-solid fa-user-plus"></i> Buat Akun Guru / Staf
            </button>
        <?php endif; ?>
    </div>
</div>

<?php if ($isYayasan): ?>
    <!-- ========================================== -->
    <!-- BAGIAN 1: KHUSUS KELOLA OPERATOR SEKOLAH    -->
    <!-- ========================================== -->
    <div class="card" style="margin-bottom: 28px; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="font-size: 16px; font-weight: 700; margin: 0; color: var(--primary);">
                    <i class="fa-solid fa-user-shield"></i> Daftar Akun Operator Sekolah (Seluruh Unit)
                </h3>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                    Operator Sekolah bertugas mengelola siswa, kelas, mapel, SPMB, dan akun guru/karyawan pada unitnya masing-masing.
                </div>
            </div>
            <button type="button" class="btn btn-primary btn-sm" onclick="openCreateOperatorModal()">
                <i class="fa-solid fa-plus"></i> Tambah Operator Sekolah
            </button>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table" style="margin: 0;">
                    <thead style="background: #f1f5f9;">
                        <tr>
                            <th width="15%">Unit Satuan</th>
                            <th width="15%">Username</th>
                            <th width="20%">Nama Operator</th>
                            <th width="20%">Kontak (Email / HP)</th>
                            <th width="12%">Login Terakhir</th>
                            <th width="8%" class="text-center">Status</th>
                            <th width="10%" class="text-center">Aksi Yayasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($operatorList)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted" style="padding: 24px;">
                                    Belum ada akun Operator Sekolah yang terdaftar. Klik tombol <strong>Tambah Operator Sekolah</strong> di atas.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($operatorList as $op): ?>
                                <tr>
                                    <td>
                                        <span class="badge badge-primary" style="font-size: 12px; padding: 5px 10px;">
                                            <i class="fa-solid fa-school"></i> <?= SecurityHelper::escape($op['unit_name'] ?: 'Unit ' . $op['actual_unit_id']) ?>
                                        </span>
                                    </td>
                                    <td><code><?= SecurityHelper::escape($op['username']) ?></code></td>
                                    <td>
                                        <strong style="font-size: 13px;"><?= SecurityHelper::escape($op['full_name']) ?></strong>
                                        <?php if (!empty($op['nipy'])): ?>
                                            <div style="font-size: 11px; color: var(--text-muted);">NIPY: <?= SecurityHelper::escape($op['nipy']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div><?= SecurityHelper::escape($op['email'] ?: '-') ?></div>
                                        <div style="font-size: 11px; color: var(--text-muted);"><?= SecurityHelper::escape($op['phone'] ?: '') ?></div>
                                    </td>
                                    <td>
                                        <?= $op['last_login_at'] ? date('d M Y H:i', strtotime($op['last_login_at'])) : '<span style="color:#94a3b8; font-size: 11px;">Belum login</span>' ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $op['is_active'] ? 'badge-success' : 'badge-danger' ?>">
                                            <?= $op['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div style="display: flex; gap: 4px; justify-content: center;">
                                            <!-- Edit Akun Operator -->
                                            <button type="button" class="btn btn-outline btn-sm" onclick='openEditAccountModal(<?= json_encode($op) ?>)' title="Edit Akun Operator">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>

                                            <!-- Reset Password -->
                                            <form method="POST" action="<?= $baseUrl ?>/users/school-accounts/save" onsubmit="return confirm('Reset kata sandi operator <?= SecurityHelper::escape($op['username']) ?> ke password123?')">
                                                <?= CSRF::inputField() ?>
                                                <input type="hidden" name="id" value="<?= $op['id'] ?>">
                                                <input type="hidden" name="action" value="reset_password">
                                                <button type="submit" class="btn btn-warning btn-sm" title="Reset Password ke password123">
                                                    <i class="fa-solid fa-key"></i>
                                                </button>
                                            </form>

                                            <!-- Toggle Status -->
                                            <form method="POST" action="<?= $baseUrl ?>/users/school-accounts/save">
                                                <?= CSRF::inputField() ?>
                                                <input type="hidden" name="id" value="<?= $op['id'] ?>">
                                                <input type="hidden" name="action" value="toggle_active">
                                                <button type="submit" class="btn <?= $op['is_active'] ? 'btn-outline' : 'btn-success' ?> btn-sm" title="<?= $op['is_active'] ? 'Nonaktifkan Akun' : 'Aktifkan Akun' ?>">
                                                    <i class="fa-solid fa-power-off"></i>
                                                </button>
                                            </form>

                                            <!-- Hapus Akun Operator Sekolah -->
                                            <form method="POST" action="<?= $baseUrl ?>/users/school-accounts/delete" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin MENGHAPUS PERMANEN akun operator <?= SecurityHelper::escape($op['username']) ?>?')">
                                                <?= CSRF::inputField() ?>
                                                <input type="hidden" name="id" value="<?= $op['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus Permanen Akun Operator">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- ========================================== -->
<!-- BAGIAN 2: DAFTAR PENGGUNA SEKOLAH          -->
<!-- ========================================== -->
<?php if ($isYayasan): ?>
    <!-- Filter Unit Satuan Pendidikan untuk Operator Yayasan -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body" style="padding: 12px 20px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <span style="font-weight: 600; font-size: 13px; color: var(--text-muted);">
                <i class="fa-solid fa-filter"></i> Filter Unit Guru & Pegawai:
            </span>
            <a href="<?= $baseUrl ?>/users/school-accounts" class="btn btn-sm <?= $unitFilter === null ? 'btn-primary' : 'btn-outline' ?>">
                Semua Unit
            </a>
            <?php foreach ($allUnits as $u): ?>
                <a href="<?= $baseUrl ?>/users/school-accounts?unit_id=<?= $u['id'] ?>" 
                   class="btn btn-sm <?= $unitFilter == $u['id'] ? 'btn-primary' : 'btn-outline' ?>">
                    <?= SecurityHelper::escape($u['code']) ?> - <?= SecurityHelper::escape($u['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header" style="background: #fff; border-bottom: 1px solid var(--border-color); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 16px; font-weight: 600; margin: 0;">
            <i class="fa-solid fa-users"></i> <?= $isYayasan ? 'Daftar Guru, Karyawan & Kepala Sekolah' : 'Daftar Akun Pengguna Unit Satuan Pendidikan' ?>
        </h3>
        <span class="badge badge-info" style="font-size: 12px;">Total: <?= count($schoolStaffList) ?> Pengguna</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table" style="margin: 0;">
                <thead>
                    <tr>
                        <th width="12%">Username</th>
                        <th width="20%">Nama Lengkap & NIPY</th>
                        <th width="12%">Unit Satuan</th>
                        <th width="22%">Peran (Role) yang Diemban</th>
                        <th width="14%">Email & Kontak</th>
                        <th width="10%">Login Terakhir</th>
                        <th width="5%" class="text-center">Status</th>
                        <th width="5%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($schoolStaffList)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted" style="padding: 30px;">
                                <i class="fa-solid fa-users-slash" style="font-size: 28px; margin-bottom: 8px; display: block; color: #cbd5e1;"></i>
                                Tidak ada akun pengguna yang ditemukan untuk kriteria unit ini.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($schoolStaffList as $u): ?>
                        <tr>
                            <td><code><?= SecurityHelper::escape($u['username']) ?></code></td>
                            <td>
                                <strong style="font-size: 13px;"><?= SecurityHelper::escape($u['full_name']) ?></strong>
                                <?php if (!empty($u['nipy'])): ?>
                                    <div style="font-size: 11px; color: var(--text-muted);">NIPY: <?= SecurityHelper::escape($u['nipy']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($u['level_posisi'])): ?>
                                    <div style="font-size: 11px; color: #0f766e; font-style: italic;"><?= SecurityHelper::escape($u['level_posisi']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-info" style="font-size: 11px;">
                                    <?= SecurityHelper::escape($u['unit_name'] ?: 'Unit ' . $u['actual_unit_id']) ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; flex-wrap: wrap; gap: 4px; align-items: center;">
                                    <?php 
                                        $roleItems = explode('; ', $u['role_data'] ?? '');
                                        foreach ($roleItems as $ri):
                                            if (empty($ri)) continue;
                                            $parts = explode(':', $ri);
                                            $rId = $parts[0] ?? '';
                                            $rName = $parts[1] ?? '';
                                            $rDisplay = $parts[2] ?? $rName;
                                            
                                            $isOpSekolah = ($rName === 'operator_sekolah');
                                            $badgeBg = $isOpSekolah ? 'badge-primary' : 'badge-secondary';
                                    ?>
                                        <span class="badge <?= $badgeBg ?>" style="font-size: 11px; display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px;">
                                            <?= SecurityHelper::escape($rDisplay) ?>
                                            
                                            <?php 
                                                $canUnassign = false;
                                                if ($isYayasan) {
                                                    $canUnassign = true;
                                                } elseif (!$isOpSekolah && !in_array($rName, ['operator_yayasan', 'direktur_pendidikan', 'deputi_pendidikan', 'pembina_yayasan'])) {
                                                    $canUnassign = true;
                                                }
                                            ?>
                                            <?php if ($canUnassign && count($roleItems) > 1): ?>
                                                <form method="POST" action="<?= $baseUrl ?>/users/school-accounts/unassign-role" style="display: inline;" onsubmit="return confirm('Cabut peran <?= SecurityHelper::escape($rDisplay) ?> dari <?= SecurityHelper::escape($u['username']) ?>?')">
                                                    <?= CSRF::inputField() ?>
                                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                    <input type="hidden" name="role_id" value="<?= $rId ?>">
                                                    <button type="submit" style="background: none; border: none; color: inherit; padding: 0; cursor: pointer; font-size: 11px; line-height: 1; margin-left: 2px;" title="Cabut peran ini">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </span>
                                    <?php endforeach; ?>

                                    <!-- Tombol Tambah Peran -->
                                    <button type="button" class="btn btn-outline btn-sm" style="padding: 2px 8px; font-size: 11px;" 
                                            onclick="openAssignRoleModal(<?= $u['id'] ?>, '<?= SecurityHelper::escape($u['username']) ?>', '<?= SecurityHelper::escape($u['full_name']) ?>')" 
                                            title="Tambah Peran untuk pengguna ini">
                                        <i class="fa-solid fa-plus"></i> Peran
                                    </button>
                                </div>
                            </td>
                            <td>
                                <div><?= SecurityHelper::escape($u['email'] ?: '-') ?></div>
                                <div style="font-size: 11px; color: var(--text-muted);"><?= SecurityHelper::escape($u['phone'] ?: '') ?></div>
                            </td>
                            <td>
                                <?= $u['last_login_at'] ? date('d M Y H:i', strtotime($u['last_login_at'])) : '<span style="color:#94a3b8; font-size: 11px;">Belum login</span>' ?>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= $u['is_active'] ? 'badge-success' : 'badge-danger' ?>" style="font-size: 11px;">
                                    <?= $u['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div style="display: flex; gap: 4px; justify-content: center;">
                                    <!-- Edit Akun -->
                                    <button type="button" class="btn btn-outline btn-sm" onclick='openEditAccountModal(<?= json_encode($u) ?>)' title="Edit Akun Pengguna">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <!-- Reset Password -->
                                    <form method="POST" action="<?= $baseUrl ?>/users/school-accounts/save" onsubmit="return confirm('Reset kata sandi akun <?= SecurityHelper::escape($u['username']) ?> ke password123?')">
                                        <?= CSRF::inputField() ?>
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <input type="hidden" name="action" value="reset_password">
                                        <button type="submit" class="btn btn-warning btn-sm" title="Reset Password ke password123">
                                            <i class="fa-solid fa-key"></i>
                                        </button>
                                    </form>

                                    <!-- Toggle Active -->
                                    <form method="POST" action="<?= $baseUrl ?>/users/school-accounts/save">
                                        <?= CSRF::inputField() ?>
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <input type="hidden" name="action" value="toggle_active">
                                        <button type="submit" class="btn <?= $u['is_active'] ? 'btn-outline' : 'btn-success' ?> btn-sm" title="<?= $u['is_active'] ? 'Nonaktifkan Akun' : 'Aktifkan Akun' ?>">
                                            <i class="fa-solid fa-power-off"></i>
                                        </button>
                                    </form>

                                    <?php if ($isYayasan): ?>
                                        <!-- Hapus Akun (Khusus Operator Yayasan) -->
                                        <form method="POST" action="<?= $baseUrl ?>/users/school-accounts/delete" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin menghapus akun pengguna <?= SecurityHelper::escape($u['username']) ?>?')">
                                            <?= CSRF::inputField() ?>
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus Pengguna">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: BUAT AKUN OPERATOR SEKOLAH          -->
<!-- ========================================== -->
<?php if ($isYayasan): ?>
<div id="modalCreateOperator" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 1050; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; width: 100%; max-width: 540px; border-radius: 8px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3); overflow: hidden; max-height: 90vh; display: flex; flex-direction: column;">
        <div style="padding: 16px 20px; background: #0f172a; color: #fff; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0; color: #38bdf8;">
                <i class="fa-solid fa-user-shield"></i> Buat Akun Operator Sekolah
            </h3>
            <button type="button" onclick="closeCreateOperatorModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>
        
        <form method="POST" action="<?= $baseUrl ?>/users/school-accounts/create" style="padding: 20px; overflow-y: auto;">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="level_posisi" value="Operator Sekolah">

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 4px;">Pilih dari Daftar Guru & Karyawan yang Ada <span style="color:#0284c7;">(Rekomendasi)</span></label>
                <select name="employee_id" id="op_employee_id" class="form-control" onchange="onOperatorEmployeeSelected(this)" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    <option value="0">-- Pilih Guru/Karyawan yang sudah terdaftar --</option>
                    <?php if (!empty($existingEmployees)): ?>
                        <?php foreach ($existingEmployees as $emp): ?>
                            <option value="<?= $emp['id'] ?>" 
                                    data-name="<?= SecurityHelper::escape($emp['nama']) ?>" 
                                    data-unit="<?= $emp['unit_id'] ?>" 
                                    data-email="<?= SecurityHelper::escape($emp['email'] ?? '') ?>" 
                                    data-phone="<?= SecurityHelper::escape($emp['contact_phone'] ?? '') ?>" 
                                    data-username="<?= SecurityHelper::escape($emp['username'] ?? '') ?>"
                                    data-hasuser="<?= !empty($emp['user_id']) ? '1' : '0' ?>">
                                [<?= SecurityHelper::escape($emp['unit_name'] ?? 'Unit ' . $emp['unit_id']) ?>] <?= SecurityHelper::escape($emp['nama']) ?> - <?= SecurityHelper::escape($emp['level_posisi']) ?> <?= !empty($emp['username']) ? '(Akun: ' . SecurityHelper::escape($emp['username']) . ')' : '(Belum ada akun)' ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <small style="color: var(--text-muted); font-size: 11px;">Pilih pegawai di atas untuk menghubungkan akun tanpa membuat data pegawai duplikat.</small>
            </div>

            <div id="op_user_notice" style="display: none; background: #e0f2fe; padding: 10px 14px; border-radius: 6px; font-size: 12px; color: #0369a1; margin-bottom: 14px;"></div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 4px;">Unit Satuan Pendidikan <span style="color:red;">*</span></label>
                <select name="unit_id" id="op_unit_id" class="form-control" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    <option value="">-- Pilih Satuan Pendidikan --</option>
                    <?php foreach ($allUnits as $u): ?>
                        <option value="<?= $u['id'] ?>">
                            <?= SecurityHelper::escape($u['code']) ?> - <?= SecurityHelper::escape($u['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 4px;">Nama Lengkap <span style="color:red;">*</span></label>
                    <input type="text" name="full_name" id="op_full_name" class="form-control" placeholder="cth: Rian Hidayat, S.Kom" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>
                <div>
                    <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 4px;">Username Login <span style="color:red;">*</span></label>
                    <input type="text" name="username" id="op_username" class="form-control" placeholder="cth: operator.sd" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 4px;">Kata Sandi</label>
                    <input type="text" name="password" id="op_password" class="form-control" value="password123" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    <small style="color: var(--text-muted); font-size: 11px;">Default: <code>password123</code></small>
                </div>
                <div>
                    <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 4px;">No. WhatsApp</label>
                    <input type="text" name="phone" id="op_phone" class="form-control" placeholder="0812xxxxxxxx" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 4px;">Alamat Email</label>
                <input type="email" name="email" id="op_email" class="form-control" placeholder="operator@alkahfi.sch.id" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>

            <!-- Role otomatis operator_sekolah -->
            <?php 
                $opRole = null;
                foreach ($roles as $r) {
                    if ($r['name'] === 'operator_sekolah') {
                        $opRole = $r;
                        break;
                    }
                }
            ?>
            <?php if ($opRole): ?>
                <input type="hidden" name="roles[]" value="<?= $opRole['id'] ?>">
                <div style="background: #e0f2fe; padding: 10px 14px; border-radius: 6px; font-size: 12px; color: #0369a1; margin-bottom: 16px;">
                    <i class="fa-solid fa-circle-check"></i> Hak akses yang diberikan: <strong>Operator Sekolah</strong>.
                </div>
            <?php endif; ?>

            <div style="display: flex; justify-content: flex-end; gap: 8px; border-top: 1px solid var(--border-color); padding-top: 14px;">
                <button type="button" class="btn btn-outline" onclick="closeCreateOperatorModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="padding: 8px 20px;">
                    <i class="fa-solid fa-check"></i> Buat / Tetapkan Operator
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- ========================================== -->
<!-- MODAL: BUAT AKUN GURU & KARYAWAN           -->
<!-- ========================================== -->
<div id="modalCreateStaff" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 1050; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; width: 100%; max-width: 580px; border-radius: 8px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3); overflow: hidden; max-height: 90vh; display: flex; flex-direction: column;">
        <div style="padding: 16px 20px; background: #f8fafc; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0; color: var(--primary);">
                <i class="fa-solid fa-user-plus"></i> <?= $isYayasan ? 'Buat Akun Guru & Karyawan Sekolah' : 'Buat Akun Guru & Karyawan Unit' ?>
            </h3>
            <button type="button" onclick="closeCreateStaffModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #64748b;">&times;</button>
        </div>
        
        <form method="POST" action="<?= $baseUrl ?>/users/school-accounts/create" style="padding: 20px; overflow-y: auto;">
            <?= CSRF::inputField() ?>

            <?php if (!$isYayasan): ?>
                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 10px 14px; margin-bottom: 14px; font-size: 12px; color: #1e40af; line-height: 1.5;">
                    <i class="fa-solid fa-circle-info"></i> <strong>Pembuatan Akun Terintegrasi:</strong><br>
                    Pilih guru/karyawan yang ingin dibuatkan akun. Seluruh kolom profil (nama lengkap, username NIPY, posisi, email, dan WhatsApp) otomatis terisi dan terkunci dari data kepegawaian. Anda cukup memilih <strong>Peran (Role)</strong> dan menentukan <strong>Kata Sandi</strong>.
                </div>
            <?php endif; ?>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 4px;">
                    Pilih Guru / Karyawan dari Daftar Unit <span style="color:red;">*</span>
                </label>
                <select name="employee_id" id="staff_employee_id" class="form-control" onchange="onStaffEmployeeSelected(this)" <?= !$isYayasan ? 'required' : '' ?> style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-weight: 500;">
                    <option value="">-- Pilih Guru / Karyawan --</option>
                    <?php 
                        $eligibleEmployees = [];
                        if (!empty($existingEmployees)) {
                            foreach ($existingEmployees as $emp) {
                                if (empty($emp['user_id'])) {
                                    $eligibleEmployees[] = $emp;
                                }
                            }
                        }
                    ?>
                    <?php if (!empty($eligibleEmployees)): ?>
                        <?php foreach ($eligibleEmployees as $emp): ?>
                            <option value="<?= $emp['id'] ?>" 
                                    data-name="<?= SecurityHelper::escape($emp['nama']) ?>" 
                                    data-nipy="<?= SecurityHelper::escape($emp['nipy']) ?>"
                                    data-unit="<?= $emp['unit_id'] ?>" 
                                    data-posisi="<?= SecurityHelper::escape($emp['level_posisi']) ?>"
                                    data-email="<?= SecurityHelper::escape($emp['email'] ?? '') ?>" 
                                    data-phone="<?= SecurityHelper::escape($emp['contact_phone'] ?? '') ?>">
                                <?= SecurityHelper::escape($emp['nama']) ?> (NIPY: <?= SecurityHelper::escape($emp['nipy'] ?: '-') ?>) - <?= SecurityHelper::escape($emp['level_posisi']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>Seluruh pegawai di unit ini telah memiliki akun pengguna</option>
                    <?php endif; ?>
                </select>
            </div>

            <?php if ($isYayasan): ?>
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 4px;">Pilih Unit Satuan Pendidikan <span style="color:red;">*</span></label>
                    <select name="unit_id" id="staff_unit_id" class="form-control" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                        <option value="">-- Pilih Unit Sekolah --</option>
                        <?php foreach ($allUnits as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= $unitFilter == $u['id'] ? 'selected' : '' ?>>
                                <?= SecurityHelper::escape($u['code']) ?> - <?= SecurityHelper::escape($u['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <input type="hidden" name="unit_id" id="staff_unit_id" value="<?= Auth::unitId() ?>">
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: flex; justify-content: space-between; align-items: center; font-weight: 600; font-size: 13px; margin-bottom: 4px;">
                        <span>Nama Lengkap <span style="color:red;">*</span></span>
                        <?php if (!$isYayasan): ?><span style="font-size: 11px; color: #64748b; font-weight: normal;"><i class="fa-solid fa-lock"></i> Terkunci</span><?php endif; ?>
                    </label>
                    <input type="text" name="full_name" id="staff_full_name" class="form-control" placeholder="Otomatis dari pegawai" required <?= !$isYayasan ? 'readonly style="width: 100%; padding: 8px 12px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; cursor: not-allowed; color: #334155; font-weight: 600;"' : 'style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;"' ?>>
                </div>
                <div>
                    <label style="display: flex; justify-content: space-between; align-items: center; font-weight: 600; font-size: 13px; margin-bottom: 4px;">
                        <span>Username Login <span style="color:red;">*</span></span>
                        <span style="font-size: 11px; color: #64748b; font-weight: normal;"><i class="fa-solid fa-lock"></i> Auto NIPY</span>
                    </label>
                    <input type="text" name="username" id="staff_username" class="form-control" placeholder="Otomatis dari NIPY" required <?= !$isYayasan ? 'readonly style="width: 100%; padding: 8px 12px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; cursor: not-allowed; color: #334155; font-weight: 600;"' : 'style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;"' ?>>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 4px;">Kata Sandi (Password) <span style="color:red;">*</span></label>
                    <input type="text" name="password" class="form-control" value="password123" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    <small style="color: var(--text-muted); font-size: 11px;">Default: <code>password123</code> (Dapat diubah)</small>
                </div>
                <div>
                    <label style="display: flex; justify-content: space-between; align-items: center; font-weight: 600; font-size: 13px; margin-bottom: 4px;">
                        <span>Posisi / Jabatan</span>
                        <?php if (!$isYayasan): ?><span style="font-size: 11px; color: #64748b; font-weight: normal;"><i class="fa-solid fa-lock"></i> Terkunci</span><?php endif; ?>
                    </label>
                    <input type="text" name="level_posisi" id="staff_level_posisi" class="form-control" placeholder="Otomatis dari pegawai" <?= !$isYayasan ? 'readonly style="width: 100%; padding: 8px 12px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; cursor: not-allowed; color: #334155;"' : 'style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;"' ?>>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: flex; justify-content: space-between; align-items: center; font-weight: 600; font-size: 13px; margin-bottom: 4px;">
                        <span>Alamat Email</span>
                        <?php if (!$isYayasan): ?><span style="font-size: 11px; color: #64748b; font-weight: normal;"><i class="fa-solid fa-lock"></i> Terkunci</span><?php endif; ?>
                    </label>
                    <input type="email" name="email" id="staff_email" class="form-control" placeholder="Otomatis dari pegawai" <?= !$isYayasan ? 'readonly style="width: 100%; padding: 8px 12px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; cursor: not-allowed; color: #334155;"' : 'style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;"' ?>>
                </div>
                <div>
                    <label style="display: flex; justify-content: space-between; align-items: center; font-weight: 600; font-size: 13px; margin-bottom: 4px;">
                        <span>No. WhatsApp / HP</span>
                        <?php if (!$isYayasan): ?><span style="font-size: 11px; color: #64748b; font-weight: normal;"><i class="fa-solid fa-lock"></i> Terkunci</span><?php endif; ?>
                    </label>
                    <input type="text" name="phone" id="staff_phone" class="form-control" placeholder="Otomatis dari pegawai" <?= !$isYayasan ? 'readonly style="width: 100%; padding: 8px 12px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; cursor: not-allowed; color: #334155;"' : 'style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;"' ?>>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                    Pilih Peran (Roles) Pengguna di Sekolah <span style="color:red;">*</span>
                </label>
                <div style="max-height: 150px; overflow-y: auto; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px; display: grid; grid-template-columns: 1fr 1fr; gap: 6px; background: #f8fafc;">
                    <?php foreach ($roles as $r): ?>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; cursor: pointer; padding: 5px 8px; border-radius: 4px; background: #fff; border: 1px solid #e2e8f0;">
                            <input type="checkbox" name="roles[]" value="<?= $r['id'] ?>" <?= ($r['name'] === 'guru_mapel') ? 'checked' : '' ?>>
                            <span><?= SecurityHelper::escape($r['display_name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <small style="color: var(--text-muted); font-size: 11px;">Peran pertama yang dipilih akan menjadi peran aktif utama akun.</small>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 8px; border-top: 1px solid var(--border-color); padding-top: 14px;">
                <button type="button" class="btn btn-outline" onclick="closeCreateStaffModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="padding: 8px 20px;">
                    <i class="fa-solid fa-check"></i> Simpan Akun Pengguna
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: EDIT AKUN PENGGUNA                  -->
<!-- ========================================== -->
<div id="modalEditAccount" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 1050; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; width: 100%; max-width: 560px; border-radius: 8px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3); overflow: hidden; max-height: 90vh; display: flex; flex-direction: column;">
        <div style="padding: 16px 20px; background: #0f172a; color: #fff; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0; color: #38bdf8;">
                <i class="fa-solid fa-user-pen"></i> Edit Data Akun Pengguna
            </h3>
            <button type="button" onclick="closeEditAccountModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>
        
        <form method="POST" action="<?= $baseUrl ?>/users/school-accounts/save" style="padding: 20px; overflow-y: auto;">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="id" id="edit_acc_id" value="">
            <input type="hidden" name="action" value="edit_account">

            <?php if (!$isYayasan): ?>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px; margin-bottom: 14px; font-size: 12px; color: #475569; line-height: 1.5;">
                    <i class="fa-solid fa-lock" style="color: #64748b;"></i> <strong>Data Profil Terkunci:</strong><br>
                    Username, nama lengkap, dan alamat email dikunci untuk Operator Sekolah. Anda hanya dapat menyesuaikan <strong>Peran (Role)</strong> yang diemban dan mengganti kata sandi.
                </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: flex; justify-content: space-between; align-items: center; font-weight: 600; font-size: 13px; margin-bottom: 4px;">
                        <span>Username Login <span style="color:red;">*</span></span>
                        <?php if (!$isYayasan): ?><span style="font-size: 11px; color: #64748b; font-weight: normal;"><i class="fa-solid fa-lock"></i> Terkunci</span><?php endif; ?>
                    </label>
                    <input type="text" name="username" id="edit_acc_username" class="form-control" required <?= !$isYayasan ? 'readonly style="width: 100%; padding: 8px 12px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; cursor: not-allowed; color: #475569; font-weight: 600;"' : 'style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;"' ?>>
                </div>
                <div>
                    <label style="display: flex; justify-content: space-between; align-items: center; font-weight: 600; font-size: 13px; margin-bottom: 4px;">
                        <span>Nama Lengkap <span style="color:red;">*</span></span>
                        <?php if (!$isYayasan): ?><span style="font-size: 11px; color: #64748b; font-weight: normal;"><i class="fa-solid fa-lock"></i> Terkunci</span><?php endif; ?>
                    </label>
                    <input type="text" name="full_name" id="edit_acc_fullname" class="form-control" required <?= !$isYayasan ? 'readonly style="width: 100%; padding: 8px 12px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; cursor: not-allowed; color: #475569; font-weight: 600;"' : 'style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;"' ?>>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 4px;">Status Akun</label>
                    <select name="is_active" id="edit_acc_status" class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 4px;">Unit Satuan Pendidikan</label>
                    <?php if ($isYayasan): ?>
                        <select name="unit_id" id="edit_acc_unit_id" class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                            <?php foreach ($allUnits as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= SecurityHelper::escape($u['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <input type="hidden" name="unit_id" id="edit_acc_unit_id" value="">
                        <input type="text" id="edit_acc_unit_name" class="form-control" readonly style="width: 100%; padding: 8px 12px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px;">
                    <?php endif; ?>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: flex; justify-content: space-between; align-items: center; font-weight: 600; font-size: 13px; margin-bottom: 4px;">
                        <span>Alamat Email</span>
                        <?php if (!$isYayasan): ?><span style="font-size: 11px; color: #64748b; font-weight: normal;"><i class="fa-solid fa-lock"></i> Terkunci</span><?php endif; ?>
                    </label>
                    <input type="email" name="email" id="edit_acc_email" class="form-control" <?= !$isYayasan ? 'readonly style="width: 100%; padding: 8px 12px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; cursor: not-allowed; color: #475569;"' : 'style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;"' ?>>
                </div>
                <div>
                    <label style="display: flex; justify-content: space-between; align-items: center; font-weight: 600; font-size: 13px; margin-bottom: 4px;">
                        <span>No. WhatsApp / HP</span>
                        <?php if (!$isYayasan): ?><span style="font-size: 11px; color: #64748b; font-weight: normal;"><i class="fa-solid fa-lock"></i> Terkunci</span><?php endif; ?>
                    </label>
                    <input type="text" name="phone" id="edit_acc_phone" class="form-control" <?= !$isYayasan ? 'readonly style="width: 100%; padding: 8px 12px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; cursor: not-allowed; color: #475569;"' : 'style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;"' ?>>
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 4px;">Ganti Kata Sandi (Opsional)</label>
                <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
                    Peran (Roles) Pengguna
                </label>
                <div style="max-height: 140px; overflow-y: auto; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px; display: grid; grid-template-columns: 1fr 1fr; gap: 6px; background: #f8fafc;">
                    <?php foreach ($roles as $r): ?>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; cursor: pointer; padding: 5px 8px; border-radius: 4px; background: #fff; border: 1px solid #e2e8f0;">
                            <input type="checkbox" name="roles[]" class="edit-acc-role-cb" value="<?= $r['id'] ?>">
                            <span><?= SecurityHelper::escape($r['display_name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 8px; border-top: 1px solid var(--border-color); padding-top: 14px;">
                <button type="button" class="btn btn-outline" onclick="closeEditAccountModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="padding: 8px 20px;">
                    <i class="fa-solid fa-save"></i> Simpan Perubahan Akun
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: ASSIGN PERAN TAMBAHAN               -->
<!-- ========================================== -->
<div id="modalAssignRole" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 1050; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; width: 100%; max-width: 440px; border-radius: 8px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3); overflow: hidden;">
        <div style="padding: 16px 20px; background: #f8fafc; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 15px; font-weight: 700; margin: 0; color: var(--primary);">
                <i class="fa-solid fa-user-tag"></i> Tambah Peran Pengguna
            </h3>
            <button type="button" onclick="closeAssignRoleModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #64748b;">&times;</button>
        </div>
        
        <form method="POST" action="<?= $baseUrl ?>/users/school-accounts/assign-role" style="padding: 20px;">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="user_id" id="assign_user_id" value="">

            <div style="margin-bottom: 14px; font-size: 13px; background: #f1f5f9; padding: 10px 12px; border-radius: 6px;">
                Pengguna: <strong id="assign_user_info" style="color: var(--primary);">-</strong>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 4px;">Pilih Peran yang Ingin Ditambahkan <span style="color:red;">*</span></label>
                <select name="role_id" class="form-control" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    <option value="">-- Pilih Peran --</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= $r['id'] ?>"><?= SecurityHelper::escape($r['display_name']) ?> (<?= $r['name'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 8px; border-top: 1px solid var(--border-color); padding-top: 14px;">
                <button type="button" class="btn btn-outline" onclick="closeAssignRoleModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="padding: 8px 20px;">
                    <i class="fa-solid fa-plus"></i> Tetapkan Peran
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function onOperatorEmployeeSelected(sel) {
    let opt = sel.options[sel.selectedIndex];
    if (!opt || opt.value === '0') {
        document.getElementById('op_user_notice').style.display = 'none';
        return;
    }
    let unitId = opt.getAttribute('data-unit');
    let name = opt.getAttribute('data-name');
    let email = opt.getAttribute('data-email');
    let phone = opt.getAttribute('data-phone');
    let username = opt.getAttribute('data-username');
    let hasUser = opt.getAttribute('data-hasuser') === '1';

    if (unitId && document.getElementById('op_unit_id')) document.getElementById('op_unit_id').value = unitId;
    if (name) document.getElementById('op_full_name').value = name;
    if (email) document.getElementById('op_email').value = email;
    if (phone) document.getElementById('op_phone').value = phone;

    let noticeEl = document.getElementById('op_user_notice');
    if (hasUser && username) {
        document.getElementById('op_username').value = username;
        noticeEl.style.display = 'block';
        noticeEl.innerHTML = '<i class="fa-solid fa-circle-info"></i> Pegawai ini telah memiliki akun <strong>(' + username + ')</strong>. Menyimpan form ini akan menambahkan peran <strong>Operator Sekolah</strong> ke akun tersebut.';
    } else {
        noticeEl.style.display = 'none';
        let cleanName = (name || '').toLowerCase().replace(/[^a-z0-9]/g, '');
        document.getElementById('op_username').value = 'op.' + cleanName.substring(0, 8);
    }
}

function onStaffEmployeeSelected(sel) {
    let opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value || opt.value === '0') {
        if (document.getElementById('staff_full_name')) document.getElementById('staff_full_name').value = '';
        if (document.getElementById('staff_username')) document.getElementById('staff_username').value = '';
        if (document.getElementById('staff_level_posisi')) document.getElementById('staff_level_posisi').value = '';
        if (document.getElementById('staff_email')) document.getElementById('staff_email').value = '';
        if (document.getElementById('staff_phone')) document.getElementById('staff_phone').value = '';
        return;
    }
    let unitId = opt.getAttribute('data-unit');
    let name = opt.getAttribute('data-name');
    let nipy = opt.getAttribute('data-nipy');
    let posisi = opt.getAttribute('data-posisi');
    let email = opt.getAttribute('data-email');
    let phone = opt.getAttribute('data-phone');

    if (unitId && document.getElementById('staff_unit_id')) document.getElementById('staff_unit_id').value = unitId;
    if (name && document.getElementById('staff_full_name')) document.getElementById('staff_full_name').value = name;
    
    // Gunakan NIPY huruf kecil tanpa spasi sebagai username auto-generated
    if (document.getElementById('staff_username')) {
        if (nipy && nipy.trim() !== '') {
            document.getElementById('staff_username').value = nipy.trim().toLowerCase().replace(/\s+/g, '');
        } else if (name) {
            let cleanName = name.toLowerCase().replace(/[^a-z0-9]/g, '');
            document.getElementById('staff_username').value = cleanName.substring(0, 12);
        }
    }
    
    if (document.getElementById('staff_level_posisi')) document.getElementById('staff_level_posisi').value = posisi || 'Guru Mapel';
    if (document.getElementById('staff_email')) document.getElementById('staff_email').value = email || '';
    if (document.getElementById('staff_phone')) document.getElementById('staff_phone').value = phone || '';
}

function openEditAccountModal(user) {
    document.getElementById('edit_acc_id').value = user.id;
    document.getElementById('edit_acc_username').value = user.username || '';
    document.getElementById('edit_acc_fullname').value = user.full_name || '';
    document.getElementById('edit_acc_status').value = (user.is_active !== undefined) ? user.is_active : 1;
    if (document.getElementById('edit_acc_unit_id')) {
        document.getElementById('edit_acc_unit_id').value = user.actual_unit_id || user.user_unit_id || user.unit_id || '';
    }
    if (document.getElementById('edit_acc_unit_name')) {
        document.getElementById('edit_acc_unit_name').value = user.unit_name || '';
    }
    document.getElementById('edit_acc_email').value = user.email || '';
    document.getElementById('edit_acc_phone').value = user.phone || '';

    // Check matching roles
    let roleIds = [];
    if (user.role_data) {
        let parts = user.role_data.split(';');
        parts.forEach(p => {
            let rParts = p.trim().split(':');
            if (rParts[0]) roleIds.push(parseInt(rParts[0]));
        });
    }
    document.querySelectorAll('.edit-acc-role-cb').forEach(cb => {
        cb.checked = roleIds.includes(parseInt(cb.value));
    });

    let m = document.getElementById('modalEditAccount');
    if (m) m.style.display = 'flex';
}

function closeEditAccountModal() {
    let m = document.getElementById('modalEditAccount');
    if (m) m.style.display = 'none';
}

function openCreateOperatorModal() {
    let m = document.getElementById('modalCreateOperator');
    if (m) m.style.display = 'flex';
}
function closeCreateOperatorModal() {
    let m = document.getElementById('modalCreateOperator');
    if (m) m.style.display = 'none';
}
function openCreateStaffModal() {
    let empSel = document.getElementById('staff_employee_id');
    if (empSel) {
        empSel.selectedIndex = 0;
        onStaffEmployeeSelected(empSel);
    }
    let m = document.getElementById('modalCreateStaff');
    if (m) m.style.display = 'flex';
}
function closeCreateStaffModal() {
    let m = document.getElementById('modalCreateStaff');
    if (m) m.style.display = 'none';
}
function openAssignRoleModal(userId, username, fullName) {
    document.getElementById('assign_user_id').value = userId;
    document.getElementById('assign_user_info').textContent = fullName + ' (' + username + ')';
    let m = document.getElementById('modalAssignRole');
    if (m) m.style.display = 'flex';
}
function closeAssignRoleModal() {
    let m = document.getElementById('modalAssignRole');
    if (m) m.style.display = 'none';
}
function openImportStaffModal() {
    let m = document.getElementById('modalImportStaff');
    if (m) m.style.display = 'flex';
}
function closeImportStaffModal() {
    let m = document.getElementById('modalImportStaff');
    if (m) m.style.display = 'none';
}
</script>

<?php if ($isYayasan): ?>
<!-- ========================================== -->
<!-- MODAL: IMPORT PEGAWAI & AKUN DARI EXCEL    -->
<!-- ========================================== -->
<div id="modalImportStaff" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 1050; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; width: 100%; max-width: 520px; border-radius: 8px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3); overflow: hidden; display: flex; flex-direction: column;">
        <div style="padding: 16px 20px; background: #0f172a; color: #fff; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0; color: #38bdf8;">
                <i class="fa-solid fa-file-import"></i> Impor Akun Guru & Karyawan Excel
            </h3>
            <button type="button" onclick="closeImportStaffModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>
        <form method="POST" action="<?= $baseUrl ?>/master/employees/import" enctype="multipart/form-data" style="padding: 20px;">
            <?= CSRF::inputField() ?>
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 4px;">Unit Satuan Pendidikan Penempatan <span style="color:red;">*</span></label>
                <select name="unit_id" class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    <?php foreach ($allUnits as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($unitFilter == $u['id']) ? 'selected' : '' ?>><?= SecurityHelper::escape($u['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom: 16px; font-size: 12px; line-height: 1.5; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 12px; border-radius: 6px; color: #166534;">
                <i class="fa-solid fa-shield-halved"></i> <strong>Sistem Validasi Aman Terproteksi:</strong><br>
                1. Unduh template resmi melalui tombol <strong>Unduh Template Excel</strong>.<br>
                2. Kolom <strong>Peran Utama (Role)</strong> dan <strong>Jenis Kelamin (L/P)</strong> dilengkapi <em>fixed dropdown otomatis</em> untuk mencegah kesalahan input peran sistem.<br>
                3. Akun pengguna dan baris biodata pegawai akan dibuat dan ditautkan secara otomatis (Password default: <code>password123</code>).
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 4px;">Pilih Berkas Excel (.xlsx / .xls) <span style="color:red;">*</span></label>
                <input type="file" name="file" class="form-control" accept=".xlsx, .xls" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 8px; border-top: 1px solid var(--border-color); padding-top: 14px;">
                <button type="button" class="btn btn-outline" onclick="closeImportStaffModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="padding: 8px 20px;">
                    <i class="fa-solid fa-upload"></i> Unggah & Impor Pegawai
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
