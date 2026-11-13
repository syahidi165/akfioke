<?php
// views/master/parent_accounts.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;
?>
<div class="page-header d-flex justify-content-between align-items-center" style="margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-user-group" style="color: var(--primary); margin-right: 8px;"></i><?= SecurityHelper::escape($pageTitle) ?></h1>
        <div class="page-subtitle">
            <?= $isYayasan ? 'Kelola akun login wali murid di seluruh satuan pendidikan Yayasan Islam Al Kahfi Batam.' : 'Kelola akun login wali murid pada unit sekolah Anda untuk akses rapor, tagihan, dan tahfidz.' ?>
        </div>
    </div>
    <div>
        <a href="<?= $baseUrl ?>/students" class="btn btn-outline">
            <i class="fa-solid fa-users"></i> Ke Data Siswa
        </a>
    </div>
</div>

<!-- INFO BANNER -->
<div class="alert alert-info" style="display: flex; gap: 14px; align-items: flex-start; margin-bottom: 24px; background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 8px; padding: 14px 18px;">
    <i class="fa-solid fa-circle-info" style="font-size: 20px; color: #16a34a; margin-top: 2px;"></i>
    <div style="font-size: 13px; line-height: 1.5;">
        <strong>Informasi Pembuatan Akun Orang Tua:</strong><br>
        Akun orang tua terhubung otomatis berdasarkan <strong>Nomor Kartu Keluarga (No. KK)</strong> saat data siswa diinputkan atau diimpor.
        Username default adalah <strong>No. KK</strong> dengan kata sandi awal <strong><code>password123</code></strong>. Jika orang tua memiliki lebih dari 1 anak di sekolah, akun akan otomatis terhubung ke seluruh anaknya.
    </div>
</div>

<!-- STATS CARDS -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 16px 20px; border-left: 4px solid var(--primary); display: flex; align-items: center; gap: 16px;">
        <div style="width: 48px; height: 48px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Total Akun Wali</div>
            <div style="font-size: 24px; font-weight: 700; color: var(--text-main);"><?= number_format($statTotal) ?></div>
        </div>
    </div>
    <div class="card" style="padding: 16px 20px; border-left: 4px solid #16a34a; display: flex; align-items: center; gap: 16px;">
        <div style="width: 48px; height: 48px; border-radius: 50%; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-solid fa-user-check"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Akun Aktif</div>
            <div style="font-size: 24px; font-weight: 700; color: #16a34a;"><?= number_format($statActive) ?></div>
        </div>
    </div>
    <div class="card" style="padding: 16px 20px; border-left: 4px solid #8b5cf6; display: flex; align-items: center; gap: 16px;">
        <div style="width: 48px; height: 48px; border-radius: 50%; background: #ede9fe; color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-solid fa-children"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Terhubung ke Siswa</div>
            <div style="font-size: 24px; font-weight: 700; color: #8b5cf6;"><?= number_format($statWithStudents) ?></div>
        </div>
    </div>
</div>

<!-- FILTER & PENCARIAN -->
<div class="card" style="margin-bottom: 24px; padding: 18px 20px;">
    <form method="GET" action="<?= $baseUrl ?>/users/parent-accounts" class="d-flex align-items-center" style="gap: 12px; flex-wrap: wrap;">
        <?php if ($isYayasan): ?>
            <div style="min-width: 220px; flex: 1;">
                <label style="font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; display: block;">Unit Sekolah:</label>
                <select name="unit_id" class="form-control form-select" onchange="this.form.submit()">
                    <option value="">-- Semua Unit Sekolah --</option>
                    <?php foreach ($allUnits as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($unitFilter == $u['id']) ? 'selected' : '' ?>>
                            <?= SecurityHelper::escape($u['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <div style="min-width: 160px;">
            <label style="font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; display: block;">Status Akun:</label>
            <select name="status" class="form-control form-select" onchange="this.form.submit()">
                <option value="">-- Semua Status --</option>
                <option value="active" <?= ($statusFilter === 'active') ? 'selected' : '' ?>>Aktif Saja</option>
                <option value="inactive" <?= ($statusFilter === 'inactive') ? 'selected' : '' ?>>Nonaktif Saja</option>
            </select>
        </div>

        <div style="flex: 2; min-width: 240px;">
            <label style="font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; display: block;">Cari Akun / Siswa:</label>
            <div style="position: relative;">
                <input type="text" name="q" value="<?= SecurityHelper::escape($search) ?>" class="form-control" placeholder="Cari No. KK, nama orang tua, NIS, atau nama anak..." style="padding-left: 36px;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 11px; color: var(--text-muted);"></i>
            </div>
        </div>

        <div style="align-self: flex-end; display: flex; gap: 8px;">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
            <?php if (!empty($search) || !empty($statusFilter) || (!empty($unitFilter) && $isYayasan)): ?>
                <a href="<?= $baseUrl ?>/users/parent-accounts" class="btn btn-outline" title="Reset Filter">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- DAFTAR AKUN ORANG TUA -->
<div class="card" style="border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 16px; font-weight: 700; margin: 0; color: var(--primary);">
            <i class="fa-solid fa-id-card"></i> Daftar Akun Orang Tua Siswa (<?= count($parents) ?> Akun)
        </h3>
        <span class="text-muted" style="font-size: 13px;">
            Menampilkan <?= count($parents) ?> dari <?= $statTotal ?> total akun
        </span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table" style="margin: 0;">
                <thead style="background: #f1f5f9;">
                    <tr>
                        <th width="4%" class="text-center">No</th>
                        <th width="15%">No. KK (Username)</th>
                        <th width="18%">Nama Orang Tua / Wali</th>
                        <th width="24%">Anak / Siswa Terhubung</th>
                        <th width="15%">Kontak</th>
                        <th width="10%">Login Terakhir</th>
                        <th width="6%" class="text-center">Status</th>
                        <th width="8%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($parents)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted" style="padding: 36px 20px;">
                                <i class="fa-solid fa-folder-open" style="font-size: 36px; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>
                                Tidak ada data akun orang tua yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($parents as $p): ?>
                            <tr>
                                <td class="text-center text-muted"><?= $no++ ?></td>
                                <td>
                                    <div style="font-weight: 600; font-family: monospace; font-size: 13px; color: #0f172a;">
                                        <i class="fa-solid fa-id-card text-muted" style="margin-right: 4px;"></i><?= SecurityHelper::escape($p['username']) ?>
                                    </div>
                                </td>
                                <td>
                                    <strong style="color: var(--text-main); font-size: 13.5px;">
                                        <?= SecurityHelper::escape($p['full_name']) ?>
                                    </strong>
                                </td>
                                <td>
                                    <?php if (!empty($p['student_details'])): ?>
                                        <div style="font-size: 12.5px; line-height: 1.4;">
                                            <i class="fa-solid fa-graduation-cap" style="color: #0284c7; margin-right: 4px;"></i>
                                            <?= SecurityHelper::escape($p['student_details']) ?>
                                        </div>
                                        <?php if (!empty($p['student_units'])): ?>
                                            <div style="margin-top: 4px;">
                                                <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 11px; padding: 2px 7px;">
                                                    <i class="fa-solid fa-school"></i> <?= SecurityHelper::escape($p['student_units']) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge" style="background: #fef3c7; color: #b45309; font-size: 11px;">
                                            <i class="fa-solid fa-triangle-exclamation"></i> Belum tertaut data siswa
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-size: 12px;">
                                        <?php if (!empty($p['phone'])): ?>
                                            <div><i class="fa-solid fa-phone" style="width: 14px; color: var(--text-muted);"></i> <?= SecurityHelper::escape($p['phone']) ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($p['email'])): ?>
                                            <div style="color: var(--text-muted);"><i class="fa-regular fa-envelope" style="width: 14px;"></i> <?= SecurityHelper::escape($p['email']) ?></div>
                                        <?php endif; ?>
                                        <?php if (empty($p['phone']) && empty($p['email'])): ?>
                                            <span class="text-muted" style="font-style: italic;">Tidak ada kontak</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td style="font-size: 12px; color: var(--text-muted);">
                                    <?php if (!empty($p['last_login_at'])): ?>
                                        <span title="<?= SecurityHelper::escape($p['last_login_at']) ?>">
                                            <i class="fa-regular fa-clock"></i> <?= date('d/m/Y H:i', strtotime($p['last_login_at'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-style: italic;">Belum pernah</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($p['is_active'])): ?>
                                        <span class="badge badge-success" style="font-size: 11px;">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger" style="font-size: 11px;">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div style="display: flex; gap: 4px; justify-content: center;">
                                        <!-- Tombol Edit Profil -->
                                        <button type="button" class="btn btn-outline btn-sm" 
                                                title="Edit Akun Orang Tua"
                                                onclick="openEditParentModal(<?= htmlspecialchars(json_encode([
                                                    'id' => (int)$p['id'],
                                                    'username' => $p['username'],
                                                    'full_name' => $p['full_name'],
                                                    'email' => $p['email'] ?? '',
                                                    'phone' => $p['phone'] ?? '',
                                                    'is_active' => (int)$p['is_active']
                                                ]), ENT_QUOTES, 'UTF-8') ?>)">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>

                                        <!-- Tombol Reset Password -->
                                        <button type="button" class="btn btn-outline btn-sm" 
                                                style="color: #b45309; border-color: #fde68a;"
                                                title="Reset Kata Sandi ke default (password123)"
                                                onclick="openResetPasswordModal(<?= (int)$p['id'] ?>, '<?= SecurityHelper::escape($p['username']) ?>', '<?= SecurityHelper::escape($p['full_name']) ?>')">
                                            <i class="fa-solid fa-key"></i>
                                        </button>
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

<!-- ============================================================== -->
<!-- MODAL: EDIT AKUN ORANG TUA                                      -->
<!-- ============================================================== -->
<div class="modal fade" id="modalEditParent" tabindex="-1" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
            <form method="POST" action="<?= $baseUrl ?>/users/parent-accounts/save">
                <?= CSRF::field() ?>
                <input type="hidden" name="id" id="editParentId">
                <input type="hidden" name="action" value="save_profile">

                <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 20px;">
                    <h5 class="modal-title" style="font-weight: 700; color: var(--primary); margin: 0;">
                        <i class="fa-solid fa-user-pen"></i> Edit Akun Orang Tua
                    </h5>
                    <button type="button" class="btn-close" onclick="closeEditParentModal()" style="border: none; background: none; font-size: 18px; cursor: pointer;">&times;</button>
                </div>
                <div class="modal-body" style="padding: 20px;">
                    <div class="form-group" style="margin-bottom: 14px;">
                        <label style="font-weight: 600; font-size: 13px;">No. KK / Username (Login)</label>
                        <input type="text" id="editParentUsername" class="form-control" readonly style="background: #f1f5f9; cursor: not-allowed; font-family: monospace;">
                        <small class="text-muted" style="font-size: 11px;">Username dikunci ke No. Kartu Keluarga untuk integrasi data anak.</small>
                    </div>

                    <div class="form-group" style="margin-bottom: 14px;">
                        <label style="font-weight: 600; font-size: 13px;">Nama Lengkap Orang Tua / Wali <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" id="editParentFullName" class="form-control" required placeholder="Contoh: Budi Santoso">
                    </div>

                    <div class="form-group" style="margin-bottom: 14px;">
                        <label style="font-weight: 600; font-size: 13px;">Nomor HP / WhatsApp</label>
                        <input type="text" name="phone" id="editParentPhone" class="form-control" placeholder="Contoh: 08123456789">
                    </div>

                    <div class="form-group" style="margin-bottom: 14px;">
                        <label style="font-weight: 600; font-size: 13px;">Alamat Email</label>
                        <input type="email" name="email" id="editParentEmail" class="form-control" placeholder="Contoh: ortu@example.com">
                    </div>

                    <div class="form-group" style="margin-bottom: 14px;">
                        <label style="font-weight: 600; font-size: 13px;">Ganti Password Baru (Opsional)</label>
                        <input type="password" name="new_password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                        <small class="text-muted" style="font-size: 11px;">Jika diisi, orang tua akan diwajibkan mengganti password baru saat login berikutnya.</small>
                    </div>

                    <div class="form-check" style="margin-top: 10px;">
                        <label style="font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="is_active" id="editParentIsActive" value="1" style="width: 18px; height: 18px;">
                            Status Akun Aktif (Dapat Login)
                        </label>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 20px; display: flex; justify-content: flex-end; gap: 8px;">
                    <button type="button" class="btn btn-outline" onclick="closeEditParentModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL: RESET PASSWORD                                          -->
<!-- ============================================================== -->
<div class="modal fade" id="modalResetPassword" tabindex="-1" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
            <form method="POST" action="<?= $baseUrl ?>/users/parent-accounts/reset-password">
                <?= CSRF::field() ?>
                <input type="hidden" name="id" id="resetParentId">

                <div class="modal-header" style="background: #fffbeb; border-bottom: 1px solid #fef3c7; padding: 16px 20px;">
                    <h5 class="modal-title" style="font-weight: 700; color: #b45309; margin: 0;">
                        <i class="fa-solid fa-key"></i> Konfirmasi Reset Kata Sandi
                    </h5>
                    <button type="button" class="btn-close" onclick="closeResetPasswordModal()" style="border: none; background: none; font-size: 18px; cursor: pointer;">&times;</button>
                </div>
                <div class="modal-body" style="padding: 20px;">
                    <p style="font-size: 14px; margin-bottom: 12px;">
                        Apakah Anda yakin ingin mereset kata sandi akun orang tua berikut?
                    </p>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px;">
                        <div style="font-size: 13px;"><strong>No. KK / Username:</strong> <span id="resetParentUsernameText" style="font-family: monospace;"></span></div>
                        <div style="font-size: 13px;"><strong>Nama Lengkap:</strong> <span id="resetParentNameText"></span></div>
                    </div>
                    <div class="alert alert-warning" style="font-size: 12px; margin: 0; background: #fffbeb; border: 1px solid #fef3c7; color: #92400e; padding: 10px 14px; border-radius: 6px;">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        Kata sandi akan direset menjadi: <strong><code>password123</code></strong>. Pengguna wajib mengganti password saat berhasil masuk kembali.
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 20px; display: flex; justify-content: flex-end; gap: 8px;">
                    <button type="button" class="btn btn-outline" onclick="closeResetPasswordModal()">Batal</button>
                    <button type="submit" class="btn btn-primary" style="background: #b45309; border-color: #b45309;">
                        <i class="fa-solid fa-rotate"></i> Reset Kata Sandi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditParentModal(data) {
    document.getElementById('editParentId').value = data.id;
    document.getElementById('editParentUsername').value = data.username;
    document.getElementById('editParentFullName').value = data.full_name;
    document.getElementById('editParentPhone').value = data.phone || '';
    document.getElementById('editParentEmail').value = data.email || '';
    document.getElementById('editParentIsActive').checked = (parseInt(data.is_active) === 1);

    var modal = document.getElementById('modalEditParent');
    modal.classList.add('show');
    modal.style.display = 'block';
    document.body.classList.add('modal-open');
}

function closeEditParentModal() {
    var modal = document.getElementById('modalEditParent');
    modal.classList.remove('show');
    modal.style.display = 'none';
    document.body.classList.remove('modal-open');
}

function openResetPasswordModal(id, username, name) {
    document.getElementById('resetParentId').value = id;
    document.getElementById('resetParentUsernameText').innerText = username;
    document.getElementById('resetParentNameText').innerText = name;

    var modal = document.getElementById('modalResetPassword');
    modal.classList.add('show');
    modal.style.display = 'block';
    document.body.classList.add('modal-open');
}

function closeResetPasswordModal() {
    var modal = document.getElementById('modalResetPassword');
    modal.classList.remove('show');
    modal.style.display = 'none';
    document.body.classList.remove('modal-open');
}

window.onclick = function(event) {
    var m1 = document.getElementById('modalEditParent');
    var m2 = document.getElementById('modalResetPassword');
    if (event.target === m1) closeEditParentModal();
    if (event.target === m2) closeResetPasswordModal();
};
</script>

