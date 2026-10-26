<?php
// views/tahfidz/halaqah_siswa.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
$totalHalaqah = count($halaqahs);
$totalSantriHalaqah = array_sum(array_column($halaqahs, 'member_count'));
$totalBelumHalaqah = count($unassignedStudents);
$totalTutor = count($tutors);
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Kelola Halaqah Tahfidz Santri</h1>
        <div class="page-subtitle">Pembagian kelompok halaqah santri dan penugasan guru tutor pembimbing tahfidz.</div>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="<?= $baseUrl ?>/tahfidz/halaqah-guru" class="btn btn-outline">
            <i class="fa-solid fa-chalkboard-user"></i> Halaqah Guru & Tutor Sebaya
        </a>
        <a href="<?= $baseUrl ?>/tahfidz/rekap-siswa" class="btn btn-outline">
            <i class="fa-solid fa-chart-pie"></i> Rekap Capaian
        </a>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(13, 148, 136, 0.12); color: #0d9488;">
            <i class="fa-solid fa-users-rectangle"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $totalHalaqah ?></div>
            <div class="stat-label">Kelompok Halaqah</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
            <i class="fa-solid fa-user-check"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $totalSantriHalaqah ?></div>
            <div class="stat-label">Santri Berkelompok</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b;">
            <i class="fa-solid fa-user-clock"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $totalBelumHalaqah ?></div>
            <div class="stat-label">Belum Berkelompok</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(99, 102, 241, 0.12); color: #6366f1;">
            <i class="fa-solid fa-chalkboard-teacher"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $totalTutor ?></div>
            <div class="stat-label">Guru Tutor Tersedia</div>
        </div>
    </div>
</div>

<!-- Main 2-Column Layout -->
<div style="display: grid; grid-template-columns: minmax(320px, 380px) 1fr; gap: 24px; align-items: start;">
    
    <!-- Kolom Form Buat Halaqah Baru -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px;">
            <h3 class="card-title" style="display: flex; align-items: center; gap: 8px; font-size: 1rem; margin: 0;">
                <i class="fa-solid fa-circle-plus" style="color: #0d9488;"></i> Tambah Halaqah Santri Baru
            </h3>
        </div>
        <div class="card-body" style="padding: 20px;">
            <form action="<?= $baseUrl ?>/tahfidz/halaqah-siswa/save" method="POST">
                <?= CSRF::field() ?>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Nama Halaqah <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Halaqah Zaid Bin Tsabit" required>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Guru Pengampu / Tutor <span style="color: #ef4444;">*</span></label>
                    <select name="tutor_employee_id" class="form-control" required>
                        <option value="">-- Pilih Guru Tahfidz --</option>
                        <?php foreach ($tutors as $tutor): ?>
                            <option value="<?= $tutor['id'] ?>"><?= SecurityHelper::escape($tutor['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.875rem; margin: 0;">Pilih Santri Belum Berkelompok</label>
                        <span style="font-size: 0.75rem; color: var(--text-muted, #64748b);" id="unassignedCountBadge"><?= $totalBelumHalaqah ?> Santri</span>
                    </div>

                    <?php if (!empty($unassignedStudents)): ?>
                        <div style="display: flex; gap: 8px; margin-bottom: 8px; align-items: center;">
                            <div style="position: relative; flex: 1;">
                                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; color: #94a3b8;"></i>
                                <input type="text" id="searchUnassigned" class="form-control" placeholder="Cari nama santri / kelas..." style="padding-left: 28px; font-size: 0.8125rem; height: 34px;">
                            </div>
                            <div style="display: flex; gap: 4px; white-space: nowrap; font-size: 0.75rem;">
                                <button type="button" id="btnSelectAll" class="btn btn-secondary" style="padding: 4px 8px; font-size: 0.75rem; height: 34px;">Semua</button>
                                <button type="button" id="btnUnselectAll" class="btn btn-secondary" style="padding: 4px 8px; font-size: 0.75rem; height: 34px;">Batal</button>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div id="unassignedListContainer" style="border: 1px solid var(--border-color, #e2e8f0); border-radius: 6px; padding: 10px 12px; max-height: 220px; overflow-y: auto; background: #fafafa;">
                        <?php if (empty($unassignedStudents)): ?>
                            <div style="text-align: center; padding: 14px; font-size: 0.85rem; color: var(--text-muted, #64748b);">
                                <i class="fa-solid fa-circle-check" style="color: #10b981; margin-right: 4px;"></i> Semua santri telah terbagi ke dalam kelompok halaqah.
                            </div>
                        <?php else: ?>
                            <?php foreach ($unassignedStudents as $st): ?>
                                <label class="unassigned-item" data-search="<?= strtolower(SecurityHelper::escape($st['nama'] . ' ' . ($st['class_name'] ?? ''))) ?>" style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; margin-bottom: 8px; cursor: pointer; padding: 4px 6px; border-radius: 4px; transition: background 0.15s ease;">
                                    <input type="checkbox" name="student_ids[]" value="<?= $st['id'] ?>" class="student-checkbox" style="accent-color: #0d9488;">
                                    <span>
                                        <strong><?= SecurityHelper::escape($st['nama']) ?></strong>
                                        <small style="color: var(--text-muted, #64748b);">(<?= SecurityHelper::escape($st['class_name'] ?? 'Non-Kelas') ?>)</small>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                            <div id="noMatchMessage" style="display: none; text-align: center; padding: 12px; font-size: 0.825rem; color: var(--text-muted, #64748b);">
                                <i class="fa-solid fa-circle-exclamation" style="margin-right: 4px;"></i> Santri tidak ditemukan
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 10px 16px;">
                    <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Simpan Kelompok Halaqah
                </button>
            </form>
        </div>
    </div>

    <!-- Kolom Tabel Daftar Halaqah Aktif -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title" style="display: flex; align-items: center; gap: 8px; font-size: 1rem; margin: 0;">
                <i class="fa-solid fa-users" style="color: #0d9488;"></i> Daftar Halaqah Santri Aktif
            </h3>
            <span class="badge badge-secondary"><?= $totalHalaqah ?> Halaqah</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">No</th>
                            <th>Nama Halaqah</th>
                            <th>Guru Tutor Pengampu</th>
                            <th style="width: 130px; text-align: center;">Jumlah Santri</th>
                            <th style="width: 90px; text-align: center;">Tipe</th>
                            <th style="width: 130px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($halaqahs)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 36px; color: var(--text-muted, #64748b);">
                                    <i class="fa-solid fa-users-slash" style="font-size: 2rem; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                                    Belum ada kelompok halaqah santri yang dibuat.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($halaqahs as $idx => $hq): ?>
                                <tr>
                                    <td style="text-align: center;"><?= $idx + 1 ?></td>
                                    <td>
                                        <strong style="color: var(--text-main, #0f172a); font-size: 0.95rem;"><?= SecurityHelper::escape($hq['name']) ?></strong>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <i class="fa-solid fa-chalkboard-user" style="color: #0d9488;"></i>
                                            <span style="font-weight: 500;"><?= SecurityHelper::escape($hq['tutor_name'] ?? 'Belum ditentukan') ?></span>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge badge-info" style="font-weight: 600;">
                                            <?= (int)$hq['member_count'] ?> Santri
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge badge-secondary"><?= SecurityHelper::escape($hq['type']) ?></span>
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="display: flex; gap: 4px; justify-content: center; align-items: center;">
                                            <button type="button" class="btn btn-outline btn-sm" onclick="openEditHalaqahModal(<?= $hq['id'] ?>)" title="Kelola / Edit Halaqah" style="color: #0d9488; border-color: rgba(13,148,136,0.3);">
                                                <i class="fa-solid fa-pen-to-square"></i> Edit
                                            </button>
                                            <form action="<?= $baseUrl ?>/tahfidz/halaqah-siswa/delete" method="POST" style="display: inline;" onsubmit="return confirm('Hapus halaqah ini? Seluruh santri anggota akan kembali ke daftar belum berkelompok.')">
                                                <?= CSRF::field() ?>
                                                <input type="hidden" name="id" value="<?= $hq['id'] ?>">
                                                <button type="submit" class="btn btn-outline btn-sm" style="color: #ef4444; border-color: rgba(239,68,68,0.3);" title="Hapus Halaqah">
                                                    <i class="fa-solid fa-trash"></i>
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
</div>

<!-- Modal Edit Halaqah Santri -->
<div class="modal-overlay" id="modalEditHalaqah">
    <div class="modal-container" style="max-width: 660px; max-height: 90vh; display: flex; flex-direction: column;">
        <form action="<?= $baseUrl ?>/tahfidz/halaqah-siswa/update" method="POST" style="display: flex; flex-direction: column; overflow: hidden; height: 100%;">
            <?= CSRF::field() ?>
            <input type="hidden" name="id" id="edit_hq_id" value="">

            <div class="modal-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px;">
                <h3 class="card-title" id="editHqModalTitle" style="font-size: 1.05rem; display: flex; align-items: center; gap: 8px; margin: 0;">
                    <i class="fa-solid fa-pen-to-square" style="color: #0d9488;"></i> Edit Kelompok Halaqah
                </h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeEditHalaqahModal()">&times;</button>
            </div>

            <div class="modal-body" style="padding: 20px; overflow-y: auto; flex: 1;">
                <!-- 1. Ganti Nama Halaqah & Pengampu -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Nama Halaqah <span style="color: #ef4444;">*</span></label>
                        <input type="text" name="name" id="edit_hq_name" class="form-control" required placeholder="Contoh: Halaqah Imam Nafi">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Guru Pengampu / Tutor <span style="color: #ef4444;">*</span></label>
                        <select name="tutor_employee_id" id="edit_hq_tutor_id" class="form-control" required>
                            <option value="">-- Pilih Guru Tahfidz --</option>
                            <?php foreach ($tutors as $tutor): ?>
                                <option value="<?= $tutor['id'] ?>"><?= SecurityHelper::escape($tutor['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- 2. Santri Saat Ini (Kurangi Siswa) -->
                <div style="margin-bottom: 18px; border-top: 1px dashed #e2e8f0; padding-top: 14px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <label class="form-label" style="font-weight: 700; font-size: 0.875rem; color: var(--text-main); margin: 0;">
                            <i class="fa-solid fa-users" style="color: #0d9488;"></i> Santri dalam Halaqah ini (<span id="editCurrentMemberCount">0</span> santri)
                        </label>
                        <small style="color: #64748b; font-size: 0.75rem;">Centang untuk mengeluarkan santri</small>
                    </div>
                    <div id="editCurrentMembersContainer" style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; max-height: 160px; overflow-y: auto; background: #f8fafc;">
                        <!-- Rendered by JS -->
                    </div>
                </div>

                <!-- 3. Tambah Santri Baru (Belum Berkelompok) -->
                <div style="border-top: 1px dashed #e2e8f0; padding-top: 14px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <label class="form-label" style="font-weight: 700; font-size: 0.875rem; color: var(--text-main); margin: 0;">
                            <i class="fa-solid fa-user-plus" style="color: #10b981;"></i> Tambah Santri ke Halaqah ini
                        </label>
                        <small style="color: #64748b; font-size: 0.75rem;">Pilih dari santri belum berkelompok</small>
                    </div>

                    <div style="margin-bottom: 8px;">
                        <input type="text" id="searchUnassignedEdit" class="form-control" placeholder="Cari nama santri / kelas..." style="font-size: 0.8125rem; height: 34px;">
                    </div>

                    <div id="editUnassignedListContainer" style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; max-height: 160px; overflow-y: auto; background: #fafafa;">
                        <!-- Rendered by JS -->
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="padding: 12px 20px; border-top: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: flex-end; gap: 8px; background: #f8fafc;">
                <button type="button" class="btn btn-outline" onclick="closeEditHalaqahModal()">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan Halaqah
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const halaqahsData = <?= json_encode($halaqahs) ?>;
const halaqahMembersData = <?= json_encode($halaqahMembers ?? []) ?>;
const unassignedStudentsData = <?= json_encode($unassignedStudents ?? []) ?>;

function openEditHalaqahModal(hqId) {
    const hq = halaqahsData.find(h => parseInt(h.id) === parseInt(hqId));
    if (!hq) return;

    document.getElementById('edit_hq_id').value = hq.id;
    document.getElementById('edit_hq_name').value = hq.name;
    document.getElementById('edit_hq_tutor_id').value = hq.tutor_employee_id;
    document.getElementById('editHqModalTitle').innerHTML = '<i class="fa-solid fa-pen-to-square" style="color: #0d9488;"></i> Edit: ' + hq.name;

    // Render Current Members
    const members = halaqahMembersData[hqId] || [];
    document.getElementById('editCurrentMemberCount').innerText = members.length;
    const currentContainer = document.getElementById('editCurrentMembersContainer');

    if (members.length === 0) {
        currentContainer.innerHTML = '<div style="text-align: center; padding: 12px; color: #94a3b8; font-size: 0.85rem;"><i class="fa-solid fa-info-circle"></i> Belum ada santri di halaqah ini.</div>';
    } else {
        let html = '';
        members.forEach(st => {
            html += `
                <div class="current-member-row" id="curr_row_${st.id}" style="display: flex; align-items: center; justify-content: space-between; padding: 6px 10px; background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 6px; transition: all 0.2s ease;">
                    <div>
                        <strong style="font-size: 0.875rem; color: #1e293b;">${st.nama}</strong>
                        <span style="font-size: 0.75rem; color: #64748b; margin-left: 4px;">(NIS: ${st.nis} - ${st.class_name})</span>
                    </div>
                    <label style="display: flex; align-items: center; gap: 4px; font-size: 0.8rem; color: #ef4444; font-weight: 600; cursor: pointer; margin: 0; padding: 2px 8px; border-radius: 4px; background: #fef2f2; border: 1px solid #fecaca;">
                        <input type="checkbox" name="remove_student_ids[]" value="${st.id}" onchange="toggleRemoveRow(this, 'curr_row_${st.id}')" style="accent-color: #ef4444;">
                        Keluarkan
                    </label>
                </div>
            `;
        });
        currentContainer.innerHTML = html;
    }

    // Render Unassigned Students
    renderUnassignedForEdit('');
    const editSearchInput = document.getElementById('searchUnassignedEdit');
    if (editSearchInput) editSearchInput.value = '';

    document.getElementById('modalEditHalaqah').classList.add('active');
}

function toggleRemoveRow(cb, rowId) {
    const row = document.getElementById(rowId);
    if (!row) return;
    if (cb.checked) {
        row.style.background = '#fee2e2';
        row.style.borderColor = '#fca5a5';
        row.style.opacity = '0.7';
    } else {
        row.style.background = '#fff';
        row.style.borderColor = '#e2e8f0';
        row.style.opacity = '1';
    }
}

function renderUnassignedForEdit(filterText) {
    const container = document.getElementById('editUnassignedListContainer');
    const query = (filterText || '').toLowerCase().trim();

    if (unassignedStudentsData.length === 0) {
        container.innerHTML = '<div style="text-align: center; padding: 12px; color: #10b981; font-size: 0.85rem;"><i class="fa-solid fa-circle-check"></i> Seluruh santri sudah memiliki kelompok halaqah.</div>';
        return;
    }

    let filtered = unassignedStudentsData;
    if (query) {
        filtered = unassignedStudentsData.filter(st => {
            const txt = ((st.nama || '') + ' ' + (st.class_name || '') + ' ' + (st.nis || '')).toLowerCase();
            return txt.includes(query);
        });
    }

    if (filtered.length === 0) {
        container.innerHTML = '<div style="text-align: center; padding: 12px; color: #94a3b8; font-size: 0.85rem;">Tidak ada santri yang sesuai pencarian.</div>';
        return;
    }

    let html = '';
    filtered.forEach(st => {
        html += `
            <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; padding: 5px 8px; border-radius: 4px; cursor: pointer; margin-bottom: 4px; background: #fff; border: 1px solid #e2e8f0;">
                <input type="checkbox" name="add_student_ids[]" value="${st.id}" style="accent-color: #10b981;">
                <span>
                    <strong style="color: #1e293b;">${st.nama}</strong>
                    <small style="color: #64748b;">(NIS: ${st.nis || '-'} - ${st.class_name || 'Non-Kelas'})</small>
                </span>
            </label>
        `;
    });
    container.innerHTML = html;
}

function closeEditHalaqahModal() {
    document.getElementById('modalEditHalaqah').classList.remove('active');
}

document.addEventListener('DOMContentLoaded', function() {
    const editSearch = document.getElementById('searchUnassignedEdit');
    if (editSearch) {
        editSearch.addEventListener('input', function() {
            renderUnassignedForEdit(this.value);
        });
    }

    const searchInput = document.getElementById('searchUnassigned');
    const items = document.querySelectorAll('.unassigned-item');
    const noMatch = document.getElementById('noMatchMessage');
    const btnSelectAll = document.getElementById('btnSelectAll');
    const btnUnselectAll = document.getElementById('btnUnselectAll');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            let visibleCount = 0;

            items.forEach(function(item) {
                const text = item.getAttribute('data-search') || '';
                if (text.includes(query)) {
                    item.style.display = 'flex';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            if (noMatch) {
                noMatch.style.display = (visibleCount === 0 && items.length > 0) ? 'block' : 'none';
            }
        });
    }

    if (btnSelectAll) {
        btnSelectAll.addEventListener('click', function() {
            items.forEach(function(item) {
                if (item.style.display !== 'none') {
                    const cb = item.querySelector('.student-checkbox');
                    if (cb) cb.checked = true;
                }
            });
        });
    }

    if (btnUnselectAll) {
        btnUnselectAll.addEventListener('click', function() {
            items.forEach(function(item) {
                const cb = item.querySelector('.student-checkbox');
                if (cb) cb.checked = false;
            });
        });
    }
});
</script>
