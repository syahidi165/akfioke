<?php
// views/master/switch_permissions.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$totalStaff = count($eligibleUsers ?? []);
$totalWithPerms = 0;
$totalPermCount = 0;
foreach ($eligibleUsers as $eu) {
    $uid = (int)$eu['id'];
    $cnt = count($permMap[$uid] ?? []);
    if ($cnt > 0) {
        $totalWithPerms++;
        $totalPermCount += $cnt;
    }
}
?>
<div class="page-header d-flex justify-content-between align-items-center" style="margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
    <div>
        <h1 class="page-title">
            <i class="fa-solid fa-user-lock" style="color: var(--primary); margin-right: 8px;"></i><?= SecurityHelper::escape($pageTitle) ?>
        </h1>
        <div class="page-subtitle">
            Atur dan batasi hak alih akun (Switch User) untuk staf Tata Usaha dan Operator Sekolah ke akun pengguna tertentu.
        </div>
    </div>
    <div style="display: flex; gap: 8px;">
        <button type="button" class="btn btn-outline" onclick="openSwitchUserModal()">
            <i class="fa-solid fa-people-arrows" style="color: #0284c7;"></i> Uji Switch User
        </button>
    </div>
</div>

<!-- INFORMASI KEBIJAKAN ALIH PERAN -->
<div class="alert alert-info" style="display: flex; gap: 14px; align-items: flex-start; margin-bottom: 24px; background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 10px; padding: 16px 20px;">
    <i class="fa-solid fa-shield-halved" style="font-size: 22px; color: #16a34a; margin-top: 2px;"></i>
    <div style="font-size: 13px; line-height: 1.6;">
        <strong>Kebijakan Otoritas Switch Akun:</strong><br>
        &bull; <strong>Operator Yayasan:</strong> Memiliki wewenang supervisi mutlak untuk beralih (switch user) ke seluruh akun di sistem tanpa batasan.<br>
        &bull; <strong>Tata Usaha &amp; Operator Sekolah:</strong> Hanya diizinkan beralih ke akun yang secara khusus <strong>dicentang / diberikan izin</strong> pada halaman ini oleh Operator Yayasan. Akun lain di luar daftar izin tidak akan tampil pada daftar switcher mereka.
    </div>
</div>

<!-- STATS CARDS -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 18px 20px; border-left: 4px solid var(--primary); display: flex; align-items: center; gap: 16px;">
        <div style="width: 48px; height: 48px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-solid fa-users-gear"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Total Staf TU &amp; Operator</div>
            <div style="font-size: 24px; font-weight: 700; color: var(--text-main);"><?= number_format($totalStaff) ?></div>
        </div>
    </div>
    <div class="card" style="padding: 18px 20px; border-left: 4px solid #16a34a; display: flex; align-items: center; gap: 16px;">
        <div style="width: 48px; height: 48px; border-radius: 50%; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-solid fa-user-check"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Staf Memiliki Izin Switch</div>
            <div style="font-size: 24px; font-weight: 700; color: #16a34a;"><?= number_format($totalWithPerms) ?></div>
        </div>
    </div>
    <div class="card" style="padding: 18px 20px; border-left: 4px solid #8b5cf6; display: flex; align-items: center; gap: 16px;">
        <div style="width: 48px; height: 48px; border-radius: 50%; background: #ede9fe; color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-solid fa-key"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Total Akun Target Didelegasikan</div>
            <div style="font-size: 24px; font-weight: 700; color: #8b5cf6;"><?= number_format($totalPermCount) ?></div>
        </div>
    </div>
</div>

<!-- TABEL PENGATURAN IZIN -->
<div class="card" style="border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 30px;">
    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div>
            <h3 style="font-size: 16px; font-weight: 700; margin: 0; color: var(--primary);">
                <i class="fa-solid fa-list-check"></i> Daftar Staf, Guru &amp; Pimpinan Sekolah
            </h3>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                Tentukan akun siapa saja yang dapat diakses oleh masing-masing staf/guru di bawah ini (Orang tua & santri dikecualikan).
            </div>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
            <select id="filterUnitSelect" onchange="filterStaffTable()" class="form-control" style="font-size: 12.5px; padding: 6px 12px; width: 170px; border-radius: 8px;">
                <option value="">-- Semua Unit --</option>
                <?php foreach ($allUnits as $u): ?>
                    <option value="<?= (int)$u['id'] ?>"><?= SecurityHelper::escape($u['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select id="filterRoleSelect" onchange="filterStaffTable()" class="form-control" style="font-size: 12.5px; padding: 6px 12px; width: 160px; border-radius: 8px;">
                <option value="">-- Semua Peran --</option>
                <option value="tu">Tata Usaha</option>
                <option value="operator">Operator Sekolah</option>
                <option value="kepsek">Kepala Sekolah</option>
                <option value="guru">Guru &amp; Waka</option>
            </select>
            <input type="text" id="filterStaffTable" placeholder="Cari nama atau username..." oninput="filterStaffTable()" class="form-control" style="font-size: 12.5px; padding: 6px 14px; width: 200px; border-radius: 8px;">
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table" style="margin: 0;" id="staffTable">
                <thead style="background: #f1f5f9;">
                    <tr>
                        <th width="4%" class="text-center">No</th>
                        <th width="18%">Pengguna</th>
                        <th width="14%">Peran Sistem</th>
                        <th width="14%">Unit Satuan</th>
                        <th width="35%">Akun Target Yang Diizinkan</th>
                        <th width="15%" class="text-center">Aksi Pengaturan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($eligibleUsers)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted" style="padding: 36px 20px;">
                                <i class="fa-solid fa-user-xmark" style="font-size: 36px; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>
                                Belum ada akun staf atau guru yang memenuhi kriteria terdaftar.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($eligibleUsers as $eu): 
                            $uid = (int)$eu['id'];
                            $userPerms = $permMap[$uid] ?? [];
                            $assignedTargetIds = array_column($userPerms, 'target_user_id');
                            $rName = strtolower($eu['role_name'] ?? '');
                            $roleCat = 'lainnya';
                            $roleBadgeBg = '#f1f5f9';
                            $roleBadgeColor = '#475569';
                            $roleIcon = 'fa-user';

                            if ($rName === 'tata_usaha') {
                                $roleCat = 'tu';
                                $roleBadgeBg = '#e0f2fe';
                                $roleBadgeColor = '#0369a1';
                                $roleIcon = 'fa-calculator';
                            } elseif ($rName === 'operator_sekolah') {
                                $roleCat = 'operator';
                                $roleBadgeBg = '#ede9fe';
                                $roleBadgeColor = '#6d28d9';
                                $roleIcon = 'fa-user-shield';
                            } elseif ($rName === 'kepala_sekolah') {
                                $roleCat = 'kepsek';
                                $roleBadgeBg = '#fef3c7';
                                $roleBadgeColor = '#b45309';
                                $roleIcon = 'fa-user-tie';
                            } elseif (str_contains($rName, 'guru') || str_contains($rName, 'waka')) {
                                $roleCat = 'guru';
                                $roleBadgeBg = '#ecfdf5';
                                $roleBadgeColor = '#047857';
                                $roleIcon = 'fa-chalkboard-user';
                            }
                        ?>
                            <tr class="staff-row" 
                                data-unit-id="<?= (int)$eu['unit_id'] ?>" 
                                data-role-cat="<?= $roleCat ?>" 
                                data-search="<?= strtolower(htmlspecialchars($eu['full_name'] . ' ' . $eu['username'] . ' ' . $eu['unit_name'] . ' ' . $eu['role_display'], ENT_QUOTES, 'UTF-8')) ?>">
                                <td class="text-center text-muted"><?= $no++ ?></td>
                                <td>
                                    <strong style="color: var(--text-main); font-size: 13.5px;"><?= SecurityHelper::escape($eu['full_name']) ?></strong>
                                    <div style="font-size: 11px; color: #0284c7; font-family: monospace;">@<?= SecurityHelper::escape($eu['username']) ?></div>
                                </td>
                                <td>
                                    <span class="badge" style="background: <?= $roleBadgeBg ?>; color: <?= $roleBadgeColor ?>; font-size: 11px; padding: 3px 8px; border: 1px solid rgba(0,0,0,0.06);">
                                        <i class="fa-solid <?= $roleIcon ?>"></i> 
                                        <?= SecurityHelper::escape($eu['role_display']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="font-size: 12.5px; font-weight: 600; color: #334155;">
                                        <i class="fa-solid fa-school" style="color: #64748b; font-size: 11px; margin-right: 4px;"></i>
                                        <?= SecurityHelper::escape($eu['unit_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (empty($userPerms)): ?>
                                        <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 11px; border: 1px dashed #cbd5e1; padding: 4px 8px;">
                                            <i class="fa-solid fa-lock"></i> Tidak ada izin (Fitur Switch Dinonaktifkan)
                                        </span>
                                    <?php else: ?>
                                        <div style="margin-bottom: 4px;">
                                            <span class="badge badge-success" style="font-size: 11px; padding: 3px 8px;">
                                                <i class="fa-solid fa-check"></i> <?= count($userPerms) ?> Akun Diizinkan
                                            </span>
                                        </div>
                                        <div style="display: flex; gap: 4px; flex-wrap: wrap; max-height: 70px; overflow-y: auto; padding: 2px;">
                                            <?php foreach ($userPerms as $p): ?>
                                                <span class="badge" style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; font-size: 11px; font-weight: 500; padding: 2px 7px;">
                                                    <?= SecurityHelper::escape($p['target_name']) ?> <span style="opacity: 0.75;">(<?= SecurityHelper::escape($p['target_role_display']) ?> - <?= SecurityHelper::escape($p['target_unit_name']) ?>)</span>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div style="display: flex; gap: 6px; justify-content: center;">
                                        <button type="button" class="btn btn-primary btn-sm" 
                                                onclick="openEditPermModal(<?= $uid ?>, '<?= SecurityHelper::escape($eu['full_name']) ?>', '<?= SecurityHelper::escape($eu['role_display']) ?>', '<?= SecurityHelper::escape($eu['unit_name']) ?>', <?= htmlspecialchars(json_encode(array_map('intval', $assignedTargetIds)), ENT_QUOTES, 'UTF-8') ?>)">
                                            <i class="fa-solid fa-user-pen"></i> Atur Izin
                                        </button>

                                        <?php if (!empty($userPerms)): ?>
                                            <form method="POST" action="<?= $baseUrl ?>/settings/switch-permissions/revoke" style="margin: 0; display: inline;" onsubmit="return confirm('Cabut seluruh izin switch akun untuk <?= SecurityHelper::escape($eu['full_name']) ?>?')">
                                                <?= CSRF::field() ?>
                                                <input type="hidden" name="user_id" value="<?= $uid ?>">
                                                <button type="submit" class="btn btn-outline btn-sm" style="color: #ef4444; border-color: #fca5a5;" title="Cabut Semua Izin">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
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
<!-- MODAL: ATUR IZIN TARGET SWITCH AKUN                             -->
<!-- ============================================================== -->
<div class="modal fade" id="modalEditPerm" tabindex="-1" style="display: none; position: fixed; inset: 0; z-index: 1050; background: rgba(15,23,42,0.6); align-items: center; justify-content: center; padding: 20px;">
    <div class="modal-dialog" style="max-width: 720px; width: 100%; margin: auto;">
        <div class="modal-content" style="background: #ffffff; border-radius: 14px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); max-height: 90vh; display: flex; flex-direction: column;">
            <form method="POST" action="<?= $baseUrl ?>/settings/switch-permissions/save" style="margin: 0; display: flex; flex-direction: column; height: 100%; overflow: hidden;">
                <?= CSRF::field() ?>
                <input type="hidden" name="user_id" id="permUserId">

                <div class="modal-header" style="background: linear-gradient(135deg, #0284c7, #0369a1); color: #ffffff; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h4 style="font-weight: 700; margin: 0; font-size: 16px; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-key"></i> Atur Izin Switch Akun
                        </h4>
                        <div style="font-size: 12px; opacity: 0.9; margin-top: 4px;">
                            Staf: <strong id="permStaffNameText"></strong> &bull; <span id="permStaffRoleText"></span> (<span id="permStaffUnitText"></span>)
                        </div>
                    </div>
                    <button type="button" onclick="closeEditPermModal()" style="border: none; background: rgba(255,255,255,0.2); color: #ffffff; width: 32px; height: 32px; border-radius: 8px; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center;">&times;</button>
                </div>

                <div class="modal-body" style="padding: 18px 24px; overflow-y: auto; flex: 1;">
                    <!-- Filter bar in modal -->
                    <div style="display: flex; gap: 10px; margin-bottom: 14px; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 200px;">
                            <input type="text" id="targetSearchInModal" placeholder="Cari nama, username, atau peran target..." class="form-control" style="font-size: 12.5px;" oninput="filterModalTargets()">
                        </div>
                        <div style="width: 180px;">
                            <select id="targetUnitInModal" class="form-control form-select" style="font-size: 12.5px;" onchange="filterModalTargets()">
                                <option value="">-- Semua Unit --</option>
                                <?php foreach ($allUnits as $u): ?>
                                    <option value="<?= strtolower(htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8')) ?>"><?= SecurityHelper::escape($u['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="display: flex; gap: 6px;">
                            <button type="button" class="btn btn-outline btn-sm" onclick="toggleSelectAllVisible(true)" style="font-size: 11.5px;">
                                <i class="fa-solid fa-check-double"></i> Pilih Semua
                            </button>
                            <button type="button" class="btn btn-outline btn-sm" onclick="toggleSelectAllVisible(false)" style="font-size: 11.5px;">
                                <i class="fa-solid fa-xmark"></i> Batal Semua
                            </button>
                        </div>
                    </div>

                    <div style="font-size: 11.5px; color: var(--text-muted); margin-bottom: 10px;">
                        Centang akun pengguna yang diizinkan untuk diakses oleh staf ini:
                    </div>

                    <!-- Target Accounts List -->
                    <div id="targetUsersListContainer" style="border: 1px solid #e2e8f0; border-radius: 10px; max-height: 380px; overflow-y: auto; padding: 8px;">
                        <?php if (empty($allTargetUsers)): ?>
                            <div class="text-center text-muted" style="padding: 24px;">Tidak ada akun target aktif yang tersedia.</div>
                        <?php else: ?>
                            <?php foreach ($allTargetUsers as $tu): 
                                $tId = (int)$tu['id'];
                            ?>
                                <label class="target-user-item" data-id="<?= $tId ?>" data-unit="<?= strtolower(htmlspecialchars($tu['unit_name'], ENT_QUOTES, 'UTF-8')) ?>" data-search="<?= strtolower(htmlspecialchars($tu['full_name'] . ' ' . $tu['username'] . ' ' . $tu['role_display'] . ' ' . $tu['unit_name'], ENT_QUOTES, 'UTF-8')) ?>" style="display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; border-radius: 8px; margin-bottom: 4px; cursor: pointer; transition: background 0.15s; border-bottom: 1px solid #f1f5f9;">
                                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                        <input type="checkbox" name="target_user_ids[]" value="<?= $tId ?>" class="target-checkbox" id="chk_target_<?= $tId ?>" style="width: 17px; height: 17px; cursor: pointer;">
                                        <div style="min-width: 0;">
                                            <div style="font-weight: 600; font-size: 13px; color: #1e293b;">
                                                <?= SecurityHelper::escape($tu['full_name']) ?>
                                                <span style="font-size: 11px; color: #0284c7; font-family: monospace; font-weight: normal; margin-left: 4px;">@<?= SecurityHelper::escape($tu['username']) ?></span>
                                            </div>
                                            <div style="font-size: 11px; color: #64748b; margin-top: 1px;">
                                                <span class="badge badge-secondary" style="font-size: 10px; padding: 2px 6px;"><?= SecurityHelper::escape($tu['role_display']) ?></span>
                                                <span style="margin-left: 4px;"><i class="fa-solid fa-school" style="font-size: 9px;"></i> <?= SecurityHelper::escape($tu['unit_name']) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <span style="font-size: 11px; color: #94a3b8; font-family: monospace;">ID: <?= $tId ?></span>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 24px; display: flex; justify-content: space-between; align-items: center;">
                    <span id="selectedCountDisplay" style="font-size: 12px; font-weight: 600; color: #0284c7;">0 akun dipilih</span>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" class="btn btn-outline" onclick="closeEditPermModal()">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Pengaturan Izin
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.target-user-item:hover {
    background: #f8fafc;
}
.target-user-item input:checked + div {
    color: #0284c7;
}
</style>

<script>
function filterStaffTable() {
    const q = (document.getElementById('filterStaffTable')?.value || '').toLowerCase().trim();
    const unitId = document.getElementById('filterUnitSelect')?.value || '';
    const roleCat = document.getElementById('filterRoleSelect')?.value || '';

    document.querySelectorAll('.staff-row').forEach(row => {
        const text = row.getAttribute('data-search') || '';
        const rowUnit = row.getAttribute('data-unit-id') || '';
        const rowRole = row.getAttribute('data-role-cat') || '';

        const matchQ = !q || text.includes(q);
        const matchUnit = !unitId || rowUnit === unitId;
        const matchRole = !roleCat || rowRole === roleCat;

        row.style.display = (matchQ && matchUnit && matchRole) ? '' : 'none';
    });
}

function openEditPermModal(userId, name, role, unit, assignedIds) {
    document.getElementById('permUserId').value = userId;
    document.getElementById('permStaffNameText').innerText = name;
    document.getElementById('permStaffRoleText').innerText = role;
    document.getElementById('permStaffUnitText').innerText = unit;

    // Reset search inputs
    document.getElementById('targetSearchInModal').value = '';
    document.getElementById('targetUnitInModal').value = '';

    // Check target checkboxes
    const assignedSet = new Set(assignedIds || []);
    document.querySelectorAll('.target-checkbox').forEach(chk => {
        const id = parseInt(chk.value);
        // Do not allow assigning self
        if (id === parseInt(userId)) {
            chk.checked = false;
            chk.closest('.target-user-item').style.display = 'none';
        } else {
            chk.checked = assignedSet.has(id);
            chk.closest('.target-user-item').style.display = 'flex';
        }
    });

    updateSelectedCount();

    const modal = document.getElementById('modalEditPerm');
    modal.style.display = 'flex';
    document.body.classList.add('modal-open');
}

function closeEditPermModal() {
    const modal = document.getElementById('modalEditPerm');
    modal.style.display = 'none';
    document.body.classList.remove('modal-open');
}

function filterModalTargets() {
    const q = (document.getElementById('targetSearchInModal')?.value || '').toLowerCase().trim();
    const unit = (document.getElementById('targetUnitInModal')?.value || '').toLowerCase().trim();
    const curUserId = parseInt(document.getElementById('permUserId').value || '0');

    document.querySelectorAll('.target-user-item').forEach(item => {
        const id = parseInt(item.getAttribute('data-id'));
        if (id === curUserId) {
            item.style.display = 'none';
            return;
        }
        const text = item.getAttribute('data-search') || '';
        const itemUnit = item.getAttribute('data-unit') || '';

        const matchQ = !q || text.includes(q);
        const matchUnit = !unit || itemUnit.includes(unit);

        item.style.display = (matchQ && matchUnit) ? 'flex' : 'none';
    });
}

function toggleSelectAllVisible(selectState) {
    document.querySelectorAll('.target-user-item').forEach(item => {
        if (item.style.display !== 'none') {
            const chk = item.querySelector('.target-checkbox');
            if (chk) chk.checked = selectState;
        }
    });
    updateSelectedCount();
}

function updateSelectedCount() {
    const checkedCount = document.querySelectorAll('.target-checkbox:checked').length;
    document.getElementById('selectedCountDisplay').innerText = checkedCount + ' akun dipilih';
}

document.querySelectorAll('.target-checkbox').forEach(chk => {
    chk.addEventListener('change', updateSelectedCount);
});

window.onclick = function(e) {
    const m = document.getElementById('modalEditPerm');
    if (e.target === m) closeEditPermModal();
};
</script>
