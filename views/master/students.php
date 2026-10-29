<?php
// views/master/students.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$canEdit = Auth::hasRole('operator_sekolah');
?>
<div class="page-header">
    <div>
        <h1 class="page-title">Data Siswa Aktif & Akun Orang Tua Berbasis No. KK</h1>
        <div class="page-subtitle">Pusat data siswa aktif yang mengikuti KBM dan penempatan rombel kelas. Akun orang tua terhubung berbasis Nomor KK.</div>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="<?= $baseUrl ?>/students/alumni-mutasi" class="btn btn-outline" style="border-color: #cbd5e1;">
            <i class="fa-solid fa-graduation-cap" style="color: #64748b;"></i> Siswa Lulus &amp; Pindah <?= !empty($inactiveCount) ? "({$inactiveCount})" : '' ?>
        </a>
        <a href="<?= $baseUrl ?>/students/export" class="btn btn-outline">
            <i class="fa-solid fa-file-excel" style="color: #10b981;"></i> Ekspor Excel (.xlsx)
        </a>
        <a href="<?= $baseUrl ?>/students/template?unit_id=<?= $unitId ?>" class="btn btn-outline">
            <i class="fa-solid fa-download" style="color: var(--primary);"></i> Unduh Template Excel
        </a>
        <?php if ($canEdit): ?>
            <button type="button" class="btn btn-outline" onclick="openImportModal()">
                <i class="fa-solid fa-file-import" style="color: #0284c7;"></i> Impor Siswa Excel
            </button>
            <button type="button" class="btn btn-primary" onclick="openStudentModal()">
                <i class="fa-solid fa-user-plus"></i> Tambah Siswa Baru
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- TOOLBAR FILTER & PENCARIAN (KELAS & NOMOR KK) -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body" style="padding: 16px 20px;">
        <form method="GET" action="<?= $baseUrl ?>/students" id="studentFilterForm">
            <?php if (isset($_GET['unit_id'])): ?>
                <input type="hidden" name="unit_id" value="<?= (int)$_GET['unit_id'] ?>">
            <?php endif; ?>
            <div style="display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;">
                <!-- Filter Keyword Nama / NIS -->
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label" style="font-size: 12px; font-weight: 600; margin-bottom: 4px;">Cari Nama / NIS / NISN:</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 12px; color: var(--text-muted);"></i>
                        <input type="text" name="q" id="filterKeyword" class="form-control" placeholder="Ketik nama atau NIS santri..." value="<?= SecurityHelper::escape($filterQuery ?? '') ?>" style="padding-left: 32px; font-size: 13px;" oninput="liveFilterStudents()">
                    </div>
                </div>

                <!-- Filter Kelas -->
                <div style="width: 240px;">
                    <label class="form-label" style="font-size: 12px; font-weight: 600; margin-bottom: 4px;">Filter Kelas / Rombel:</label>
                    <select name="class_id" id="filterClassSelect" class="form-control" style="font-size: 13px;" onchange="liveFilterStudents()">
                        <option value="">-- Semua Kelas Aktif --</option>
                        <option value="0" <?= isset($filterClassId) && $filterClassId === 0 ? 'selected' : '' ?>>⚠️ Belum Masuk Kelas (Perlu Penempatan)</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= isset($filterClassId) && (int)$filterClassId === (int)$c['id'] ? 'selected' : '' ?>>
                                <?= SecurityHelper::escape($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Nomor KK -->
                <div style="width: 220px;">
                    <label class="form-label" style="font-size: 12px; font-weight: 600; margin-bottom: 4px;">Cari Nomor KK (Ortu):</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-address-card" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 12px; color: var(--text-muted);"></i>
                        <input type="text" name="no_kk" id="filterNoKk" class="form-control" placeholder="16 digit No. KK..." value="<?= SecurityHelper::escape($filterNoKk ?? '') ?>" style="padding-left: 32px; font-size: 13px;" oninput="liveFilterStudents()">
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn btn-primary" style="padding: 8px 16px;">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                    <?php if (!empty($filterQuery) || isset($filterClassId) || !empty($filterNoKk)): ?>
                        <a href="<?= $baseUrl ?>/students<?= isset($_GET['unit_id']) ? ('?unit_id=' . (int)$_GET['unit_id']) : '' ?>" class="btn btn-outline" style="padding: 8px 14px;" title="Reset Filter">
                            <i class="fa-solid fa-rotate-left"></i> Reset
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <div style="font-size: 13px; font-weight: 600; color: var(--text-main);">
                <i class="fa-solid fa-users" style="color: var(--primary); margin-right: 6px;"></i> Daftar Siswa Aktif
            </div>
            <span class="badge" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
                <i class="fa-solid fa-circle-check"></i> Khusus Siswa Aktif
            </span>
            <?php if (!empty($inactiveCount)): ?>
                <a href="<?= $baseUrl ?>/students/alumni-mutasi" style="font-size: 12px; color: var(--text-muted); text-decoration: underline; margin-left: 4px;">
                    <i class="fa-solid fa-box-archive"></i> <?= $inactiveCount ?> siswa telah Lulus / Pindah diarsip terpisah
                </a>
            <?php endif; ?>
        </div>
        <span class="badge badge-secondary" id="totalVisibleBadge"><?= count($students) ?> Siswa Aktif Ditemukan</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>NIS / NISN</th>
                        <th>Nama Siswa</th>
                        <th>Kelas Aktif</th>
                        <th>Nomor KK (Akun Ortu)</th>
                        <th>Nama Orang Tua / Wali</th>
                        <th>Kontak Ortu</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr><td colspan="7" style="text-align: center; padding: 24px; color: var(--text-muted);">Belum ada data siswa aktif di unit ini.</td></tr>
                    <?php else: ?>
                        <?php foreach ($students as $s): ?>
                            <tr class="student-row"
                                data-name="<?= strtolower(SecurityHelper::escape($s['nama'])) ?>"
                                data-nis="<?= strtolower(SecurityHelper::escape($s['nis'] . ' ' . ($s['nisn'] ?? ''))) ?>"
                                data-class-id="<?= (int)($s['class_id'] ?? 0) ?>"
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
                                    <?php if (!empty($s['class_name'])): ?>
                                        <span class="badge badge-info"><i class="fa-solid fa-chalkboard-user"></i> <?= SecurityHelper::escape($s['class_name']) ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-warning" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a;">
                                            <i class="fa-solid fa-triangle-exclamation"></i> Belum Masuk Kelas
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-secondary" style="font-family: monospace; font-size: 12px;">
                                        <?= SecurityHelper::maskKk($s['no_kk'], $canEdit) ?>
                                    </span>
                                </td>
                                <td><?= SecurityHelper::escape($s['father_name'] ?: ($s['mother_name'] ?: '-')) ?></td>
                                <td><?= SecurityHelper::escape($s['parent_phone'] ?: '-') ?></td>
                                <td>
                                    <div style="display: flex; gap: 4px;">
                                        <a href="<?= $baseUrl ?>/students/print-parent-card/<?= $s['id'] ?>" class="btn btn-outline btn-sm" target="_blank" title="Cetak Kartu Akun Ortu">
                                            <i class="fa-solid fa-id-card"></i>
                                        </a>
                                        <?php if ($canEdit): ?>
                                            <button type="button" class="btn btn-outline btn-sm" data-student="<?= htmlspecialchars(json_encode($s, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>" onclick="editStudent(JSON.parse(this.getAttribute('data-student')))" title="Edit Data Siswa">
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

<!-- Modal Form Siswa -->
<?php if ($canEdit): ?>
<div class="modal-overlay" id="studentModal">
    <div class="modal-container" style="max-width: 640px;">
        <form method="POST" action="<?= $baseUrl ?>/students/save">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="id" id="std_id" value="0">
            
            <div class="modal-header">
                <h3 class="card-title" id="modalStdTitle">Tambah Siswa Baru</h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeStudentModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="grid-cols-2">
                    <div class="form-group">
                        <label class="form-label">NIS (Nomor Induk Siswa)</label>
                        <input type="text" name="nis" id="std_nis" class="form-control" required placeholder="Contoh: 20240105">
                    </div>
                    <div class="form-group">
                        <label class="form-label">NISN (Nasional)</label>
                        <input type="text" name="nisn" id="std_nisn" class="form-control" placeholder="10 digit NISN">
                    </div>
                </div>

                <div class="grid-cols-2">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap Siswa</label>
                        <input type="text" name="nama" id="std_nama" class="form-control" required placeholder="Nama lengkap santri">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="gender" id="std_gender" class="form-control">
                            <option value="L">Laki-laki (Ikhwan)</option>
                            <option value="P">Perempuan (Akhwat)</option>
                        </select>
                    </div>
                </div>

                <div class="grid-cols-2">
                    <div class="form-group">
                        <label class="form-label">NIK Siswa</label>
                        <input type="text" name="nik" id="std_nik" class="form-control" placeholder="16 digit NIK">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nomor Kartu Keluarga (KK) *</label>
                        <input type="text" name="no_kk" id="std_no_kk" class="form-control" required placeholder="16 digit No. KK untuk Akun Ortu">
                        <small style="font-size: 11px; color: var(--accent); display: block; margin-top: 2px;">
                            * Menjadi username login Orang Tua. Jika No. KK sudah terdaftar, siswa akan otomatis digabungkan.
                        </small>
                    </div>
                </div>

                <div class="grid-cols-2">
                    <div class="form-group">
                        <label class="form-label">Kelas Penempatan</label>
                        <select name="class_id" id="std_class_id" class="form-control">
                            <option value="0">-- Belum Ditentukan --</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= SecurityHelper::escape($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status Siswa</label>
                        <select name="status" id="std_status" class="form-control">
                            <option value="Aktif">Aktif (Masuk Rombel / KBM)</option>
                            <option value="Lulus">Lulus (Alumni Tingkat Akhir)</option>
                            <option value="Pindah">Pindah Sekolah (Non-Aktif)</option>
                            <option value="Alumni">Alumni</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Ayah / Wali</label>
                    <input type="text" name="father_name" id="std_father" class="form-control" placeholder="Nama ayah">
                </div>

                <div class="grid-cols-2">
                    <div class="form-group">
                        <label class="form-label">No. HP / WhatsApp Orang Tua</label>
                        <input type="text" name="parent_phone" id="std_phone" class="form-control" placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Orang Tua (Untuk Notif SPP)</label>
                        <input type="email" name="parent_email" id="std_email" class="form-control" placeholder="ortu@gmail.com">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeStudentModal()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan Data Siswa</button>
            </div>
        </form>
    </div>
</div>

<?php if ($canEdit): ?>
<!-- Bagian Bawah: Card Impor Data Santri dari Excel -->
<div class="card" style="margin-top: 24px; border: 1px solid var(--border-color, #e2e8f0);">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; background: #f8fafc;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-file-import" style="color: #0284c7; font-size: 1.1rem;"></i>
            <div>
                <h3 class="card-title" style="margin: 0; font-size: 1rem; color: var(--text-main, #0f172a);">
                    Impor Massal Data Santri &amp; Akun Orang Tua dari Excel
                </h3>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">
                    Unggah data santri baru secara kolektif dengan validasi format kolom otomatis
                </span>
            </div>
        </div>
        <div>
            <a href="<?= $baseUrl ?>/students/template?unit_id=<?= $unitId ?>" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.8125rem; padding: 7px 14px;">
                <i class="fa-solid fa-download" style="color: #0284c7;"></i> Unduh Template Excel Resmi
            </a>
        </div>
    </div>

    <div class="card-body" style="padding: 24px;">
        <!-- 3 Step Info Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                    <span style="display: flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 50%; background: #0284c7; color: #fff; font-size: 0.75rem; font-weight: 700;">1</span>
                    <strong style="font-size: 0.875rem; color: var(--text-main, #0f172a);">Gunakan Template Resmi</strong>
                </div>
                <p style="font-size: 0.8125rem; color: var(--text-muted, #64748b); margin: 0; line-height: 1.4;">
                    Format kolom Excel sudah terintegrasi dengan dropdown kelas aktif satuan unit Anda.
                </p>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                    <span style="display: flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 50%; background: #10b981; color: #fff; font-size: 0.75rem; font-weight: 700;">2</span>
                    <strong style="font-size: 0.875rem; color: var(--text-main, #0f172a);">Otomatisasi Akun Ortu</strong>
                </div>
                <p style="font-size: 0.8125rem; color: var(--text-muted, #64748b); margin: 0; line-height: 1.4;">
                    Kolom <strong>Nomor KK</strong> otomatis menjadi username login Orang Tua (Password default: <code>password123</code>).
                </p>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                    <span style="display: flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 50%; background: #f59e0b; color: #fff; font-size: 0.75rem; font-weight: 700;">3</span>
                    <strong style="font-size: 0.875rem; color: var(--text-main, #0f172a);">Validasi &amp; Penempatan</strong>
                </div>
                <p style="font-size: 0.8125rem; color: var(--text-muted, #64748b); margin: 0; line-height: 1.4;">
                    Sistem mendeteksi duplikasi NIS/NIK dan otomatis menempatkan santri ke kelas yang valid.
                </p>
            </div>
        </div>

        <!-- Form Upload Berkas -->
        <form method="POST" action="<?= $baseUrl ?>/students/import" enctype="multipart/form-data" style="background: #ffffff; border: 2px dashed #cbd5e1; border-radius: 10px; padding: 24px; text-align: center;">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="unit_id" value="<?= $unitId ?>">

            <div style="margin-bottom: 16px;">
                <i class="fa-solid fa-cloud-arrow-up" style="font-size: 2.5rem; color: #0284c7; opacity: 0.8; margin-bottom: 8px; display: inline-block;"></i>
                <h4 style="margin: 0 0 4px 0; font-size: 1rem; color: var(--text-main, #0f172a);">Pilih Berkas Excel Santri (.xlsx / .xls)</h4>
                <p style="margin: 0; font-size: 0.8125rem; color: var(--text-muted, #64748b);">Pastikan data sesuai format template resmi sebelum diunggah.</p>
            </div>

            <div style="max-width: 440px; margin: 0 auto 16px auto;">
                <input type="file" name="file" class="form-control" accept=".xlsx, .xls" required style="padding: 10px; background: #f8fafc; border: 1px solid #cbd5e1;">
            </div>

            <div style="display: flex; gap: 10px; justify-content: center; align-items: center;">
                <a href="<?= $baseUrl ?>/students/template?unit_id=<?= $unitId ?>" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-download"></i> Unduh Template
                </a>
                <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-upload"></i> Unggah &amp; Impor Data Santri
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Import Siswa Excel (Sesuai app.css modal-overlay & modal-container) -->
<div class="modal-overlay" id="modalImportStudents">
    <div class="modal-container" style="max-width: 520px;">
        <form method="POST" action="<?= $baseUrl ?>/students/import" enctype="multipart/form-data">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="unit_id" value="<?= $unitId ?>">
            <div class="modal-header">
                <h3 class="card-title" style="display: flex; align-items: center; gap: 8px; margin: 0; font-size: 1.05rem;">
                    <i class="fa-solid fa-file-import" style="color: #0284c7;"></i> Impor Data Santri dari Excel
                </h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeImportModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" style="margin-bottom: 16px; font-size: 0.8125rem; line-height: 1.5; background: #f0f9ff; border: 1px solid #bae6fd; padding: 14px; border-radius: 8px; color: #0369a1;">
                    <i class="fa-solid fa-shield-halved" style="color: #0284c7; margin-right: 4px;"></i> <strong>Sistem Validasi Aman Terproteksi:</strong><br>
                    1. Unduh template resmi melalui tautan template Excel.<br>
                    2. Kolom <strong>Jenis Kelamin</strong> dan <strong>Kelas Penempatan</strong> dilengkapi <em>dropdown otomatis</em> yang disesuaikan dengan kelas aktif unit ini.<br>
                    3. Kolom <strong>Nomor KK</strong> otomatis dibuatkan akun Orang Tua santri (Password default: <code>password123</code>).
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Pilih Berkas Excel (.xlsx / .xls) <span style="color:#ef4444;">*</span></label>
                    <input type="file" name="file" class="form-control" accept=".xlsx, .xls" required style="padding: 10px;">
                </div>
                <div style="font-size: 0.75rem; color: var(--text-muted, #64748b);">
                    Belum punya template? <a href="<?= $baseUrl ?>/students/template?unit_id=<?= $unitId ?>" style="color: #0284c7; font-weight: 600; text-decoration: underline;">Klik di sini untuk mengunduh template</a>.
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeImportModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-upload"></i> Unggah &amp; Impor Santri
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function openImportModal() {
    document.getElementById('modalImportStudents').classList.add('active');
}
function closeImportModal() {
    document.getElementById('modalImportStudents').classList.remove('active');
}
function openStudentModal() {
    document.getElementById('std_id').value = '0';
    document.getElementById('modalStdTitle').innerText = 'Tambah Siswa Baru';
    document.getElementById('std_nis').value = '';
    document.getElementById('std_nisn').value = '';
    document.getElementById('std_nama').value = '';
    document.getElementById('std_gender').value = 'L';
    document.getElementById('std_nik').value = '';
    document.getElementById('std_no_kk').value = '';
    document.getElementById('std_class_id').value = '0';
    document.getElementById('std_status').value = 'Aktif';
    document.getElementById('std_father').value = '';
    document.getElementById('std_phone').value = '';
    document.getElementById('std_email').value = '';
    document.getElementById('studentModal').classList.add('active');
}
function editStudent(s) {
    document.getElementById('std_id').value = s.id;
    document.getElementById('modalStdTitle').innerText = 'Edit Siswa: ' + s.nama;
    document.getElementById('std_nis').value = s.nis;
    document.getElementById('std_nisn').value = s.nisn || '';
    document.getElementById('std_nama').value = s.nama;
    document.getElementById('std_gender').value = s.gender;
    document.getElementById('std_nik').value = s.nik || '';
    document.getElementById('std_no_kk').value = s.no_kk;
    document.getElementById('std_class_id').value = s.class_id || '0';
    document.getElementById('std_status').value = s.status || 'Aktif';
    document.getElementById('std_father').value = s.father_name || '';
    document.getElementById('std_phone').value = s.parent_phone || '';
    document.getElementById('std_email').value = s.parent_email || '';
    document.getElementById('studentModal').classList.add('active');
}
function closeStudentModal() {
    document.getElementById('studentModal').classList.remove('active');
}

function liveFilterStudents() {
    const q = (document.getElementById('filterKeyword').value || '').toLowerCase().trim();
    const classId = document.getElementById('filterClassSelect').value;
    const noKk = (document.getElementById('filterNoKk').value || '').trim();

    const rows = document.querySelectorAll('.student-row');
    let visible = 0;

    rows.forEach(row => {
        const name = row.getAttribute('data-name') || '';
        const nis = row.getAttribute('data-nis') || '';
        const rowClassId = row.getAttribute('data-class-id') || '0';
        const rowNoKk = row.getAttribute('data-nokk') || '';

        let matchQ = (!q || name.includes(q) || nis.includes(q));
        let matchClass = true;
        if (classId !== '') {
            matchClass = (rowClassId === classId);
        }
        let matchNoKk = (!noKk || rowNoKk.includes(noKk));

        if (matchQ && matchClass && matchNoKk) {
            row.style.display = '';
            visible++;
        } else {
            row.style.display = 'none';
        }
    });

    const badge = document.getElementById('totalVisibleBadge');
    if (badge) badge.innerText = visible + ' Siswa Aktif Ditampilkan';
}
</script>
<?php endif; ?>
