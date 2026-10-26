<?php
// views/master/students_inactive.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$canEdit = Auth::hasRole('operator_sekolah') || Auth::hasRole('operator_yayasan');
$totalLulus = (int)($stats['lulus'] ?? 0);
$totalPindah = (int)($stats['pindah'] ?? 0);
$totalAll = (int)($stats['total'] ?? count($students ?? []));
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div>
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
            <span class="badge" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">
                <i class="fa-solid fa-box-archive"></i> Arsip Peserta Didik
            </span>
            <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Unit Terpilih</span>
        </div>
        <h1 class="page-title" style="margin: 0 0 6px 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-user-xmark" style="color: #64748b;"></i> Data Siswa Lulus &amp; Pindah
        </h1>
        <div class="page-subtitle" style="color: var(--text-muted, #64748b); font-size: 0.875rem;">
            Pusat arsip siswa yang telah lulus tingkat akhir (alumni) atau pindah sekolah. Terpisah dari rombel &amp; siswa aktif.
        </div>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
        <a href="<?= $baseUrl ?>/students" class="btn btn-secondary">
            <i class="fa-solid fa-users"></i> Ke Data Siswa Aktif
        </a>
        <a href="<?= $baseUrl ?>/alumni" class="btn btn-outline">
            <i class="fa-solid fa-user-tie" style="color: #0284c7;"></i> Kelola Pelacakan Alumni
        </a>
        <a href="<?= $baseUrl ?>/students/export-inactive<?= isset($_GET['unit_id']) ? ('?unit_id=' . (int)$_GET['unit_id']) : '' ?>" class="btn btn-outline">
            <i class="fa-solid fa-file-excel" style="color: #10b981;"></i> Ekspor Excel (.xlsx)
        </a>
    </div>
</div>

<!-- STATS GRID -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 16px 20px; border-left: 4px solid #64748b;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Total Siswa Diarsipkan</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-main, #0f172a); margin: 6px 0 2px 0;">
            <?= $totalAll ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Santri</span>
        </div>
        <div style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Lulus &amp; Pindah di unit ini</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0284c7;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Siswa Lulus (Alumni)</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #0284c7; margin: 6px 0 2px 0;">
            <?= $totalLulus ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Santri</span>
        </div>
        <div style="font-size: 0.8125rem; color: #0284c7; font-weight: 500;">Tingkat akhir / tamat sekolah</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #f59e0b;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Siswa Pindah Sekolah</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #f59e0b; margin: 6px 0 2px 0;">
            <?= $totalPindah ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Santri</span>
        </div>
        <div style="font-size: 0.8125rem; color: #f59e0b; font-weight: 500;">Mutasi keluar dari unit</div>
    </div>
</div>

<!-- TOOLBAR FILTER & PENCARIAN -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body" style="padding: 16px 20px;">
        <form method="GET" action="<?= $baseUrl ?>/students/alumni-mutasi" id="inactiveFilterForm">
            <?php if (isset($_GET['unit_id'])): ?>
                <input type="hidden" name="unit_id" value="<?= (int)$_GET['unit_id'] ?>">
            <?php endif; ?>
            <div style="display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;">
                <!-- Filter Keyword Nama / NIS -->
                <div style="flex: 1; min-width: 220px;">
                    <label class="form-label" style="font-size: 12px; font-weight: 600; margin-bottom: 4px;">Cari Nama / NIS / NISN:</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 12px; color: var(--text-muted);"></i>
                        <input type="text" name="q" id="filterKeyword" class="form-control" placeholder="Ketik nama atau NIS santri..." value="<?= SecurityHelper::escape($filterQuery ?? '') ?>" style="padding-left: 32px; font-size: 13px;" oninput="liveFilterInactive()">
                    </div>
                </div>

                <!-- Filter Status -->
                <div style="width: 200px;">
                    <label class="form-label" style="font-size: 12px; font-weight: 600; margin-bottom: 4px;">Filter Status:</label>
                    <select name="status" id="filterStatusSelect" class="form-control" style="font-size: 13px;" onchange="liveFilterInactive()">
                        <option value="">-- Semua (Lulus &amp; Pindah) --</option>
                        <option value="Lulus" <?= ($filterStatus ?? '') === 'Lulus' ? 'selected' : '' ?>>🎓 Lulus / Alumni (<?= $totalLulus ?>)</option>
                        <option value="Pindah" <?= ($filterStatus ?? '') === 'Pindah' ? 'selected' : '' ?>>➡️ Pindah Sekolah (<?= $totalPindah ?>)</option>
                    </select>
                </div>

                <!-- Filter Nomor KK -->
                <div style="width: 220px;">
                    <label class="form-label" style="font-size: 12px; font-weight: 600; margin-bottom: 4px;">Cari Nomor KK (Ortu):</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-address-card" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 12px; color: var(--text-muted);"></i>
                        <input type="text" name="no_kk" id="filterNoKk" class="form-control" placeholder="16 digit No. KK..." value="<?= SecurityHelper::escape($filterNoKk ?? '') ?>" style="padding-left: 32px; font-size: 13px;" oninput="liveFilterInactive()">
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn btn-primary" style="padding: 8px 16px;">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                    <?php if (!empty($filterQuery) || !empty($filterStatus) || !empty($filterNoKk)): ?>
                        <a href="<?= $baseUrl ?>/students/alumni-mutasi<?= isset($_GET['unit_id']) ? ('?unit_id=' . (int)$_GET['unit_id']) : '' ?>" class="btn btn-outline" style="padding: 8px 14px;" title="Reset Filter">
                            <i class="fa-solid fa-rotate-left"></i> Reset
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- TABEL DATA SISWA LULUS & PINDAH -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="font-size: 13px; font-weight: 600; color: var(--text-main);">
                <i class="fa-solid fa-box-archive" style="color: #64748b; margin-right: 6px;"></i> Daftar Arsip Siswa Non-Aktif
            </div>
            <span class="badge" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;">
                <?= $totalLulus ?> Lulus
            </span>
            <span class="badge" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a;">
                <?= $totalPindah ?> Pindah
            </span>
        </div>
        <span class="badge badge-secondary" id="totalVisibleBadge"><?= count($students) ?> Siswa Diarsipkan</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>NIS / NISN</th>
                        <th>Nama Siswa</th>
                        <th>Status</th>
                        <th>Kelas Terakhir</th>
                        <th>Kelanjutan Studi / Alumni</th>
                        <th>Nomor KK (Akun Ortu)</th>
                        <th>Kontak Ortu</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 32px; color: var(--text-muted);">
                                <i class="fa-solid fa-folder-open" style="font-size: 2rem; color: #cbd5e1; margin-bottom: 8px; display: block;"></i>
                                Belum ada arsip siswa berstatus Lulus atau Pindah di unit ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $s): ?>
                            <tr class="inactive-row"
                                data-name="<?= strtolower(SecurityHelper::escape($s['nama'])) ?>"
                                data-nis="<?= strtolower(SecurityHelper::escape($s['nis'] . ' ' . ($s['nisn'] ?? ''))) ?>"
                                data-status="<?= SecurityHelper::escape($s['status']) ?>"
                                data-nokk="<?= SecurityHelper::escape($s['no_kk'] ?? '') ?>">
                                <td>
                                    <strong><?= SecurityHelper::escape($s['nis']) ?></strong>
                                    <div style="font-size: 11px; color: var(--text-muted);">NISN: <?= SecurityHelper::escape($s['nisn'] ?: '-') ?></div>
                                </td>
                                <td>
                                    <strong><?= SecurityHelper::escape($s['nama']) ?></strong> (<?= $s['gender'] ?>)
                                    <div style="font-size: 11px; color: var(--text-muted);">NIK: <?= SecurityHelper::maskNik($s['nik'], $canEdit) ?></div>
                                </td>
                                <td>
                                    <?php if ($s['status'] === 'Lulus' || $s['status'] === 'Alumni'): ?>
                                        <span class="badge" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 12px;">
                                            <i class="fa-solid fa-graduation-cap"></i> Lulus
                                        </span>
                                    <?php elseif ($s['status'] === 'Pindah'): ?>
                                        <span class="badge" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-size: 12px;">
                                            <i class="fa-solid fa-arrow-right-from-bracket"></i> Pindah
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary"><?= SecurityHelper::escape($s['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span style="font-size: 12px; color: var(--text-main); font-weight: 500;">
                                        <?= SecurityHelper::escape($s['last_class_name'] ?? 'Tingkat Akhir') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($s['alumni_destination'])): ?>
                                        <div style="font-size: 12px; font-weight: 600; color: #0284c7;">
                                            <i class="fa-solid fa-building-columns"></i> <?= SecurityHelper::escape($s['alumni_destination']) ?>
                                        </div>
                                        <div style="font-size: 11px; color: var(--text-muted);">Tahun: <?= SecurityHelper::escape($s['alumni_grad_year'] ?? '-') ?></div>
                                    <?php else: ?>
                                        <a href="<?= $baseUrl ?>/alumni" style="font-size: 11px; color: var(--text-muted); text-decoration: underline;" title="Catat kampus/sekolah lanjutan">
                                            + Catat Jejak Alumni
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-secondary" style="font-family: monospace; font-size: 12px;">
                                        <?= SecurityHelper::maskKk($s['no_kk'], $canEdit) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= SecurityHelper::escape($s['parent_phone'] ?: '-') ?>
                                    <div style="font-size: 11px; color: var(--text-muted);"><?= SecurityHelper::escape($s['father_name'] ?: ($s['mother_name'] ?: '')) ?></div>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px;">
                                        <?php if ($canEdit): ?>
                                            <!-- Tombol Kembalikan ke Aktif -->
                                            <button type="button" class="btn btn-outline btn-sm" style="color: #047857; border-color: #a7f3d0; background: #ecfdf5;" onclick="confirmReactivate(<?= (int)$s['id'] ?>, '<?= SecurityHelper::escape(addslashes($s['nama'])) ?>', '<?= SecurityHelper::escape($s['nis']) ?>')" title="Aktifkan Kembali Siswa (Kembali ke Siswa Aktif)">
                                                <i class="fa-solid fa-rotate-left"></i> Aktifkan
                                            </button>
                                            <!-- Tombol Edit Data -->
                                            <button type="button" class="btn btn-outline btn-sm" onclick='editInactiveStudent(<?= json_encode($s) ?>)' title="Ubah Data Siswa">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
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

<!-- Modal Konfirmasi Reaktivasi Siswa -->
<?php if ($canEdit): ?>
<div class="modal-overlay" id="reactivateModal">
    <div class="modal-container" style="max-width: 480px;">
        <form method="POST" action="<?= $baseUrl ?>/students/reactivate">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="student_id" id="reactivate_std_id" value="0">
            <input type="hidden" name="unit_id" value="<?= (int)$unitId ?>">

            <div class="modal-header">
                <h3 class="card-title" style="color: #047857; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-circle-check"></i> Aktifkan Kembali Siswa
                </h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeReactivateModal()">&times;</button>
            </div>
            <div class="modal-body" style="font-size: 13px; line-height: 1.6;">
                <p style="margin-top: 0;">Apakah Anda yakin ingin mengaktifkan kembali santri berikut?</p>
                <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                    <div><strong>Nama:</strong> <span id="reactivate_std_name">-</span></div>
                    <div><strong>NIS:</strong> <span id="reactivate_std_nis">-</span></div>
                </div>
                <div style="color: var(--text-muted); font-size: 12px;">
                    <i class="fa-solid fa-circle-info" style="color: var(--primary);"></i> Santri akan berstatus <strong>Aktif</strong> dan akan kembali muncul di menu <strong>Data Siswa Aktif</strong> serta dapat ditempatkan di <strong>Penempatan Siswa Kelas / Rombel</strong>.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeReactivateModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="background: #059669; border-color: #059669;">
                    <i class="fa-solid fa-check"></i> Ya, Aktifkan Kembali
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Form Edit Siswa Non-Aktif -->
<div class="modal-overlay" id="editInactiveModal">
    <div class="modal-container" style="max-width: 640px;">
        <form method="POST" action="<?= $baseUrl ?>/students/save">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="id" id="edit_std_id" value="0">
            <input type="hidden" name="class_id" value="0">

            <div class="modal-header">
                <h3 class="card-title" id="editModalTitle">Edit Siswa Non-Aktif</h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeEditInactiveModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="grid-cols-2">
                    <div class="form-group">
                        <label class="form-label">NIS (Nomor Induk Siswa)</label>
                        <input type="text" name="nis" id="edit_std_nis" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">NISN (Nasional)</label>
                        <input type="text" name="nisn" id="edit_std_nisn" class="form-control">
                    </div>
                </div>

                <div class="grid-cols-2">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap Siswa</label>
                        <input type="text" name="nama" id="edit_std_nama" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="gender" id="edit_std_gender" class="form-control">
                            <option value="L">Laki-laki (Ikhwan)</option>
                            <option value="P">Perempuan (Akhwat)</option>
                        </select>
                    </div>
                </div>

                <div class="grid-cols-2">
                    <div class="form-group">
                        <label class="form-label">Status Siswa</label>
                        <select name="status" id="edit_std_status" class="form-control" style="font-weight: 600;">
                            <option value="Lulus">🎓 Lulus (Alumni Tingkat Akhir)</option>
                            <option value="Pindah">➡️ Pindah Sekolah (Mutasi Keluar)</option>
                            <option value="Aktif">✅ Aktifkan Kembali (Pindah ke Siswa Aktif)</option>
                        </select>
                        <small style="font-size: 11px; color: var(--text-muted); display: block; margin-top: 4px;">
                            * Jika diubah ke 'Aktif', siswa akan dipindahkan ke Data Siswa Aktif &amp; Penempatan Rombel.
                        </small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nomor Kartu Keluarga (KK) *</label>
                        <input type="text" name="no_kk" id="edit_std_no_kk" class="form-control" required>
                    </div>
                </div>

                <div class="grid-cols-2">
                    <div class="form-group">
                        <label class="form-label">NIK Siswa</label>
                        <input type="text" name="nik" id="edit_std_nik" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nama Ayah / Wali</label>
                        <input type="text" name="father_name" id="edit_std_father" class="form-control">
                    </div>
                </div>

                <div class="grid-cols-2">
                    <div class="form-group">
                        <label class="form-label">No. HP / WhatsApp Orang Tua</label>
                        <input type="text" name="parent_phone" id="edit_std_phone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Orang Tua</label>
                        <input type="email" name="parent_email" id="edit_std_email" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeEditInactiveModal()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmReactivate(id, name, nis) {
    document.getElementById('reactivate_std_id').value = id;
    document.getElementById('reactivate_std_name').innerText = name;
    document.getElementById('reactivate_std_nis').innerText = nis;
    document.getElementById('reactivateModal').classList.add('active');
}
function closeReactivateModal() {
    document.getElementById('reactivateModal').classList.remove('active');
}

function editInactiveStudent(s) {
    document.getElementById('edit_std_id').value = s.id;
    document.getElementById('editModalTitle').innerText = 'Edit Data: ' + s.nama;
    document.getElementById('edit_std_nis').value = s.nis;
    document.getElementById('edit_std_nisn').value = s.nisn || '';
    document.getElementById('edit_std_nama').value = s.nama;
    document.getElementById('edit_std_gender').value = s.gender;
    document.getElementById('edit_std_status').value = s.status || 'Lulus';
    document.getElementById('edit_std_nik').value = s.nik || '';
    document.getElementById('edit_std_no_kk').value = s.no_kk;
    document.getElementById('edit_std_father').value = s.father_name || '';
    document.getElementById('edit_std_phone').value = s.parent_phone || '';
    document.getElementById('edit_std_email').value = s.parent_email || '';
    document.getElementById('editInactiveModal').classList.add('active');
}
function closeEditInactiveModal() {
    document.getElementById('editInactiveModal').classList.remove('active');
}

function liveFilterInactive() {
    const q = (document.getElementById('filterKeyword').value || '').toLowerCase().trim();
    const statusVal = document.getElementById('filterStatusSelect').value;
    const noKk = (document.getElementById('filterNoKk').value || '').trim();

    const rows = document.querySelectorAll('.inactive-row');
    let visible = 0;

    rows.forEach(row => {
        const name = row.getAttribute('data-name') || '';
        const nis = row.getAttribute('data-nis') || '';
        const rowStatus = row.getAttribute('data-status') || '';
        const rowNoKk = row.getAttribute('data-nokk') || '';

        let matchQ = (!q || name.includes(q) || nis.includes(q));
        let matchStatus = true;
        if (statusVal !== '') {
            matchStatus = (rowStatus === statusVal);
        }
        let matchNoKk = (!noKk || rowNoKk.includes(noKk));

        if (matchQ && matchStatus && matchNoKk) {
            row.style.display = '';
            visible++;
        } else {
            row.style.display = 'none';
        }
    });

    const badge = document.getElementById('totalVisibleBadge');
    if (badge) badge.innerText = visible + ' Siswa Ditampilkan';
}
</script>
<?php endif; ?>
