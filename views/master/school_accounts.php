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
                                            <?php if (\Core\Auth::hasRole('operator_yayasan') && (int)$op['id'] !== \Core\Auth::id()): ?>
                                                <!-- Switch User (Khusus Operator Yayasan) -->
                                                <form method="POST" action="<?= $baseUrl ?>/switch-user" style="display: inline;" onsubmit="return confirm('Beralih masuk sebagai operator <?= SecurityHelper::escape($op['full_name']) ?> (@<?= SecurityHelper::escape($op['username']) ?>)?')">
                                                    <?= CSRF::inputField() ?>
                                                    <input type="hidden" name="user_id" value="<?= $op['id'] ?>">
                                                    <button type="submit" class="btn btn-sm" style="background: #0284c7; color: #ffffff; border: 1px solid #0284c7;" title="Masuk Sebagai Operator Ini">
                                                        <i class="fa-solid fa-user-ninja"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <!-- Edit Akun Operator -->
                                            <button type="button" class="btn btn-outline btn-sm" data-user="<?= htmlspecialchars(json_encode($op, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>" onclick="openEditAccountModal(JSON.parse(this.getAttribute('data-user')))" title="Edit Akun Operator">
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
                                    <?php if (\Core\Auth::hasRole('operator_yayasan') && (int)$u['id'] !== \Core\Auth::id()): ?>
                                        <!-- Switch User (Khusus Operator Yayasan) -->
                                        <form method="POST" action="<?= $baseUrl ?>/switch-user" style="display: inline;" onsubmit="return confirm('Beralih masuk sebagai pengguna <?= SecurityHelper::escape($u['full_name']) ?> (@<?= SecurityHelper::escape($u['username']) ?>)?')">
                                            <?= CSRF::inputField() ?>
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <button type="submit" class="btn btn-sm" style="background: #0284c7; color: #ffffff; border: 1px solid #0284c7;" title="Masuk Sebagai Pengguna Ini">
                                                <i class="fa-solid fa-user-ninja"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <!-- Edit Akun -->
                                    <button type="button" class="btn btn-outline btn-sm" data-user="<?= htmlspecialchars(json_encode($u, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>" onclick="openEditAccountModal(JSON.parse(this.getAttribute('data-user')))" title="Edit Akun Pengguna">
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
                <i class="fa-solid fa-file-import"></i> Impor Akun Guru &amp; Karyawan Excel
            </h3>
            <button type="button" onclick="closeImportStaffModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>
        <form method="POST" action="<?= $baseUrl ?>/master/employees/verify-import" enctype="multipart/form-data" onsubmit="return handleVerifyEmployeeImport(event, this)" style="padding: 20px;">
            <?= CSRF::inputField() ?>
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 4px;">Default Unit Satuan Penempatan</label>
                <select name="unit_id" class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    <option value="0">-- Ikuti Kolom Unit di Berkas Excel (Otomatis Multi-Unit) --</option>
                    <option value="-1" <?= ($unitFilter === -1) ? 'selected' : '' ?>>Kantor Yayasan (Lintas Unit)</option>
                    <?php foreach ($allUnits as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($unitFilter == $u['id']) ? 'selected' : '' ?>><?= SecurityHelper::escape($u['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <small style="color: var(--text-muted); font-size: 11px;">Jika memilih "Ikuti Kolom Unit di Berkas Excel", sistem otomatis mencocokkan nama/kode unit pada setiap baris data pegawai.</small>
            </div>

            <div style="margin-bottom: 16px; font-size: 12px; line-height: 1.5; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 12px; border-radius: 6px; color: #166534;">
                <i class="fa-solid fa-shield-halved"></i> <strong>Sistem Validasi &amp; Verifikasi Pra-Impor:</strong><br>
                1. Berkas Excel akan diverifikasi terlebih dahulu sebelum dimasukkan ke database.<br>
                2. Data yang terdeteksi <strong>duplikat (Nama, NIPY, NIK, Username, Email)</strong> otomatis dipisahkan ke tab <strong>Data Bermasalah</strong>.<br>
                3. Anda dapat meninjau, menghapus data bermasalah, dan memastikan hanya data valid yang tersimpan.
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 4px;">Pilih Berkas Excel (.xlsx / .xls) <span style="color:red;">*</span></label>
                <input type="file" name="file" class="form-control" accept=".xlsx, .xls" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 8px; border-top: 1px solid var(--border-color); padding-top: 14px;">
                <button type="button" class="btn btn-outline" onclick="closeImportStaffModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="padding: 8px 20px;">
                    <i class="fa-solid fa-clipboard-check"></i> Verifikasi Data Excel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: KARTU VERIFIKASI PRA-IMPOR DATA GURU & KARYAWAN -->
<div class="modal-overlay" id="modalVerifyImportEmp" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px); padding: 20px;">
    <div class="modal-container" style="max-width: 1100px; width: 100%; max-height: 90vh; display: flex; flex-direction: column; border-radius: 14px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); background: #ffffff;">
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
        closeImportStaffModal();
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
