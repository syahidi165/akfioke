<?php
// views/master/students.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$canEdit = Auth::hasAnyRole(['operator_sekolah', 'operator_yayasan']);
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

<!-- ============================================================== -->
<!-- KARTU VERIFIKASI PRA-IMPOR DATA SISWA (PRATINJAU & VALIDASI)   -->
<!-- ============================================================== -->
<div id="verifyImportCard" class="card" style="display: none; margin-bottom: 24px; border: 2px solid #0284c7; box-shadow: 0 10px 30px rgba(2, 132, 199, 0.15); border-radius: 12px; overflow: hidden;">
    <div class="card-header" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                <i class="fa-solid fa-clipboard-check"></i>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 1.15rem; color: #ffffff; font-weight: 700;">
                    Kartu Verifikasi &amp; Pratinjau Data Santri (Pra-Impor)
                </h3>
                <p style="margin: 2px 0 0 0; font-size: 0.8125rem; color: #e0f2fe;">
                    Periksa kecocokan data sebelum dimasukkan ke database. Hapus baris yang tidak sesuai agar hanya data valid yang tersimpan di sistem.
                </p>
            </div>
        </div>
        <button type="button" class="btn btn-outline btn-sm" onclick="cancelVerifyImport()" style="color: #ffffff; border-color: rgba(255,255,255,0.4); background: rgba(255,255,255,0.1); font-size: 18px; line-height: 1;" title="Tutup / Batalkan">&times;</button>
    </div>

    <!-- Ringkasan Status Validasi -->
    <div style="padding: 16px 24px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 6px 14px; font-size: 12.5px;">
                Total Data: <strong id="vTotalCount" style="color: #0f172a;">0</strong> Santri
            </div>
            <div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 6px 14px; font-size: 12.5px; color: #166534;">
                <i class="fa-solid fa-circle-check" style="color: #16a34a;"></i> Siap Masuk: <strong id="vValidCount">0</strong>
            </div>
            <div id="vInvalidBadgeBox" style="background: #fef2f2; border: 1px solid #fca5a5; border-radius: 8px; padding: 6px 14px; font-size: 12.5px; color: #991b1b;">
                <i class="fa-solid fa-triangle-exclamation" style="color: #dc2626;"></i> Bermasalah / Tidak Sesuai: <strong id="vInvalidCount">0</strong>
            </div>
        </div>

        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <button type="button" id="btnDropInvalid" class="btn btn-sm btn-outline-danger" onclick="dropAllInvalidRows()" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600; padding: 6px 12px; font-size: 12px;">
                <i class="fa-solid fa-trash-can"></i> Hapus Semua Data Bermasalah
            </button>
            <div class="btn-group" style="display: inline-flex; border: 1px solid #cbd5e1; border-radius: 6px; overflow: hidden;">
                <button type="button" class="btn btn-sm" id="btnFilterAll" onclick="filterVerifyRows('all')" style="padding: 4px 10px; font-size: 11.5px; background: #0284c7; color: #fff;">Semua</button>
                <button type="button" class="btn btn-sm" id="btnFilterValid" onclick="filterVerifyRows('valid')" style="padding: 4px 10px; font-size: 11.5px; background: #fff; color: #475569;">Hanya Valid</button>
                <button type="button" class="btn btn-sm" id="btnFilterInvalid" onclick="filterVerifyRows('invalid')" style="padding: 4px 10px; font-size: 11.5px; background: #fff; color: #475569;">Bermasalah</button>
            </div>
        </div>
    </div>

    <!-- Tabel Baris Data Terverifikasi -->
    <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
        <table class="table" style="margin-bottom: 0; font-size: 12px;">
            <thead style="position: sticky; top: 0; background: #f1f5f9; z-index: 2;">
                <tr>
                    <th style="width: 40px; text-align: center;">No</th>
                    <th>Nama Santri &amp; NIS/NISN</th>
                    <th style="width: 70px; text-align: center;">JK</th>
                    <th>Tgl Lahir</th>
                    <th>Orang Tua &amp; Kontak</th>
                    <th>Kelas Penempatan</th>
                    <th style="width: 220px; text-align: center;">Status Validasi</th>
                    <th style="width: 90px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody id="verifyTableBody">
                <!-- Diinjeksi oleh JavaScript -->
            </tbody>
        </table>
    </div>

    <!-- Card Footer: Aksi Simpan ke Sistem -->
    <div class="card-footer" style="padding: 16px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <div style="font-size: 12px; color: #64748b; max-width: 580px; line-height: 1.4;">
            <i class="fa-solid fa-circle-info" style="color: #0284c7; margin-right: 4px;"></i>
            Hanya data santri berstatus <strong>Valid (Siap Masuk)</strong> yang akan disimpan ke database. Akun wali santri (username = No. KK) dan penempatan kelas otomatis dibuat.
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <button type="button" class="btn btn-outline" onclick="cancelVerifyImport()" style="padding: 8px 18px;">
                Batalkan
            </button>
            <button type="button" class="btn btn-success" id="btnCommitImport" onclick="commitVerifiedImport()" style="padding: 9px 22px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; background: #10b981; border-color: #10b981; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2);">
                <i class="fa-solid fa-cloud-arrow-down"></i> Masukkan Data ke Sistem (<span id="btnCommitCount">0</span> Santri Valid)
            </button>
        </div>
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
<div class="card" style="margin-top: 24px; border: 1px solid var(--border-color, #e2e8f0); border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; background: #f8fafc;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                <i class="fa-solid fa-file-import"></i>
            </div>
            <div>
                <h3 class="card-title" style="margin: 0; font-size: 1.05rem; color: var(--text-main, #0f172a); font-weight: 700;">
                    Impor Massal Data Santri &amp; Akun Orang Tua
                </h3>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">
                    Unggah data santri baru secara kolektif dengan validasi format kolom Excel otomatis
                </span>
            </div>
        </div>
        <div>
            <a href="<?= $baseUrl ?>/students/template?unit_id=<?= $unitId ?>" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.8125rem; padding: 8px 16px; border-color: #0284c7; color: #0284c7; background: #ffffff;">
                <i class="fa-solid fa-download"></i> Unduh Format Template Excel (.xlsx)
            </a>
        </div>
    </div>

    <div class="card-body" style="padding: 24px;">
        <!-- 3 Step Info Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                    <span style="display: flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 50%; background: #0284c7; color: #fff; font-size: 0.8rem; font-weight: 700;">1</span>
                    <strong style="font-size: 0.875rem; color: var(--text-main, #0f172a);">Gunakan Template Resmi</strong>
                </div>
                <p style="font-size: 0.8125rem; color: var(--text-muted, #64748b); margin: 0; line-height: 1.5;">
                    Format kolom sudah terintegrasi dropdown kelas dan kolom email orang tua untuk tagihan digital.
                </p>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                    <span style="display: flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 50%; background: #10b981; color: #fff; font-size: 0.8rem; font-weight: 700;">2</span>
                    <strong style="font-size: 0.875rem; color: var(--text-main, #0f172a);">Otomatisasi Akun Ortu</strong>
                </div>
                <p style="font-size: 0.8125rem; color: var(--text-muted, #64748b); margin: 0; line-height: 1.5;">
                    Kolom <strong>Nomor KK</strong> otomatis menjadi username login Orang Tua (Password default: <code>password123</code>).
                </p>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                    <span style="display: flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 50%; background: #f59e0b; color: #fff; font-size: 0.8rem; font-weight: 700;">3</span>
                    <strong style="font-size: 0.875rem; color: var(--text-main, #0f172a);">Validasi &amp; Penempatan</strong>
                </div>
                <p style="font-size: 0.8125rem; color: var(--text-muted, #64748b); margin: 0; line-height: 1.5;">
                    Sistem otomatis mencegah duplikasi NIS dan langsung menempatkan santri ke rombel yang sesuai.
                </p>
            </div>
        </div>

        <!-- Form Upload Berkas -->
        <form method="POST" action="<?= $baseUrl ?>/students/verify-import" enctype="multipart/form-data" onsubmit="return handleVerifyImport(event, this);" style="background: #f8fafc; border: 2px dashed #93c5fd; border-radius: 12px; padding: 32px 24px; text-align: center;">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="unit_id" value="<?= $unitId ?>">

            <div style="margin-bottom: 16px;">
                <i class="fa-solid fa-cloud-arrow-up" style="font-size: 3rem; color: #0284c7; opacity: 0.9; margin-bottom: 10px; display: inline-block;"></i>
                <h4 style="margin: 0 0 6px 0; font-size: 1.1rem; color: var(--text-main, #0f172a); font-weight: 700;">Pilih Berkas Excel Santri (.xlsx / .xls)</h4>
                <p style="margin: 0; font-size: 0.8125rem; color: var(--text-muted, #64748b);">Pastikan berkas telah diisi sesuai template resmi yayasan sebelum diunggah.</p>
            </div>

            <div style="max-width: 420px; margin: 0 auto 20px auto;">
                <input type="file" name="file" class="form-control" accept=".xlsx, .xls" required style="padding: 10px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px;">
            </div>

            <div style="display: flex; gap: 12px; justify-content: center; align-items: center; flex-wrap: wrap;">
                <a href="<?= $baseUrl ?>/students/template?unit_id=<?= $unitId ?>" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: #ffffff;">
                    <i class="fa-solid fa-download"></i> Unduh Template Excel
                </a>
                <button type="submit" class="btn btn-primary" style="padding: 10px 26px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; background: #0284c7; border-color: #0284c7;">
                    <i class="fa-solid fa-magnifying-glass-chart"></i> Unggah &amp; Verifikasi Data Santri
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Import Siswa Excel (Sesuai app.css modal-overlay & modal-container) -->
<div class="modal-overlay" id="modalImportStudents">
    <div class="modal-container" style="max-width: 580px; border-radius: 12px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
        <form method="POST" action="<?= $baseUrl ?>/students/verify-import" enctype="multipart/form-data" onsubmit="return handleVerifyImport(event, this);">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="unit_id" value="<?= $unitId ?>">
            
            <div class="modal-header" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff; padding: 18px 24px;">
                <h3 class="card-title" style="display: flex; align-items: center; gap: 10px; margin: 0; font-size: 1.1rem; color: #ffffff;">
                    <i class="fa-solid fa-file-excel" style="font-size: 1.25rem;"></i> Impor Massal Data Santri dari Excel
                </h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeImportModal()" style="color: #ffffff; border-color: rgba(255,255,255,0.4); background: rgba(255,255,255,0.1); font-size: 16px; line-height: 1;">&times;</button>
            </div>
            
            <div class="modal-body" style="padding: 24px; background: #ffffff;">
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 14px 16px; margin-bottom: 20px;">
                    <div style="font-weight: 700; color: #166534; font-size: 0.875rem; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-shield-halved" style="color: #15803d;"></i> Ketentuan Format &amp; Validasi:
                    </div>
                    <ul style="margin: 0; padding-left: 18px; font-size: 0.8125rem; color: #15803d; line-height: 1.5;">
                        <li>Unduh berkas template resmi yang sudah dilengkapi dropdown kelas aktif.</li>
                        <li>Kolom <strong>Nomor KK</strong> otomatis menjadi akun login wali santri.</li>
                        <li>Kolom <strong>Email Orang Tua</strong> otomatis tersimpan untuk pengiriman surat tagihan SPP digital.</li>
                    </ul>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" style="font-weight: 700; font-size: 0.875rem; color: #1e293b; margin-bottom: 8px; display: block;">
                        Pilih Berkas Excel (.xlsx / .xls) <span style="color:#ef4444;">*</span>
                    </label>
                    <div style="border: 2px dashed #93c5fd; border-radius: 10px; padding: 20px; text-align: center; background: #f8fafc; transition: all 0.2s ease;">
                        <i class="fa-solid fa-cloud-arrow-up" style="font-size: 2rem; color: #0284c7; margin-bottom: 8px; display: block;"></i>
                        <input type="file" name="file" class="form-control" accept=".xlsx, .xls" required style="padding: 8px; background: #ffffff; border: 1px solid #cbd5e1; max-width: 360px; margin: 0 auto;">
                        <span style="font-size: 0.75rem; color: #64748b; margin-top: 6px; display: block;">Maksimal ukuran berkas 10 MB</span>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; background: #f1f5f9; padding: 10px 14px; border-radius: 8px;">
                    <span style="font-size: 0.8125rem; color: #475569;">Belum memiliki format terbaru?</span>
                    <a href="<?= $baseUrl ?>/students/template?unit_id=<?= $unitId ?>" class="btn btn-outline btn-sm" style="font-size: 0.8125rem; color: #0284c7; border-color: #0284c7; background: #ffffff;">
                        <i class="fa-solid fa-download"></i> Unduh Template
                    </a>
                </div>
            </div>

            <div class="modal-footer" style="padding: 16px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeImportModal()" style="padding: 8px 18px;">Batal</button>
                <button type="submit" class="btn btn-primary" style="padding: 8px 20px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; background: #0284c7; border-color: #0284c7;">
                    <i class="fa-solid fa-magnifying-glass-chart"></i> Unggah &amp; Verifikasi Berkas
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

// ==============================================================
// VERIFIKASI PRA-IMPOR DATA SISWA (PREVIEW & ROW FILTER/DELETE)
// ==============================================================
let verifiedRows = [];
let currentVerifyFilter = 'all';
let verifyUnitId = <?= (int)$unitId ?>;

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function handleVerifyImport(event, formElement) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    const submitBtn = formElement.querySelector('button[type="submit"]');
    const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memvalidasi Format...';
    }

    const formData = new FormData(formElement);

    fetch('<?= $baseUrl ?>/students/verify-import', {
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

        verifiedRows = data.rows || [];
        if (data.unit_id) {
            verifyUnitId = data.unit_id;
        }

        closeImportModal();
        formElement.reset();

        const card = document.getElementById('verifyImportCard');
        if (card) {
            card.style.display = 'block';
            currentVerifyFilter = 'all';
            renderVerifyTable();
            card.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
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

function filterVerifyRows(type) {
    currentVerifyFilter = type;
    renderVerifyTable();
}

function renderVerifyTable() {
    const total = verifiedRows.length;
    const validRows = verifiedRows.filter(r => r.is_valid);
    const validCount = validRows.length;
    const invalidCount = total - validCount;

    // Update Counter Badges
    const elTotal = document.getElementById('vTotalCount');
    const elValid = document.getElementById('vValidCount');
    const elInvalid = document.getElementById('vInvalidCount');
    const elCommit = document.getElementById('btnCommitCount');
    const btnCommit = document.getElementById('btnCommitImport');
    const btnDropInvalid = document.getElementById('btnDropInvalid');
    const badgeInvalidBox = document.getElementById('vInvalidBadgeBox');

    if (elTotal) elTotal.innerText = total;
    if (elValid) elValid.innerText = validCount;
    if (elInvalid) elInvalid.innerText = invalidCount;
    if (elCommit) elCommit.innerText = validCount;

    if (badgeInvalidBox) {
        badgeInvalidBox.style.display = invalidCount > 0 ? 'block' : 'none';
    }

    if (btnDropInvalid) {
        btnDropInvalid.style.display = invalidCount > 0 ? 'inline-flex' : 'none';
    }

    if (btnCommit) {
        btnCommit.disabled = (validCount === 0);
        if (validCount === 0) {
            btnCommit.style.opacity = '0.5';
            btnCommit.style.cursor = 'not-allowed';
        } else {
            btnCommit.style.opacity = '1';
            btnCommit.style.cursor = 'pointer';
        }
    }

    // Toggle filter buttons UI
    const btnAll = document.getElementById('btnFilterAll');
    const btnV = document.getElementById('btnFilterValid');
    const btnInv = document.getElementById('btnFilterInvalid');
    if (btnAll && btnV && btnInv) {
        btnAll.style.background = currentVerifyFilter === 'all' ? '#0284c7' : '#fff';
        btnAll.style.color = currentVerifyFilter === 'all' ? '#fff' : '#475569';
        btnV.style.background = currentVerifyFilter === 'valid' ? '#10b981' : '#fff';
        btnV.style.color = currentVerifyFilter === 'valid' ? '#fff' : '#475569';
        btnInv.style.background = currentVerifyFilter === 'invalid' ? '#ef4444' : '#fff';
        btnInv.style.color = currentVerifyFilter === 'invalid' ? '#fff' : '#475569';
    }

    const tbody = document.getElementById('verifyTableBody');
    if (!tbody) return;

    // Filter list for display
    let displayRows = verifiedRows;
    if (currentVerifyFilter === 'valid') {
        displayRows = verifiedRows.filter(r => r.is_valid);
    } else if (currentVerifyFilter === 'invalid') {
        displayRows = verifiedRows.filter(r => !r.is_valid);
    }

    if (displayRows.length === 0) {
        if (total === 0) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 32px 20px; color: #64748b;">
                <i class="fa-solid fa-folder-open" style="font-size: 2rem; color: #cbd5e1; margin-bottom: 8px; display: block;"></i>
                Semua baris pratinjau telah dihapus. Silakan unggah berkas baru jika diperlukan.
            </td></tr>`;
        } else {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 24px 20px; color: #64748b;">
                Tidak ada data santri untuk filter status ini.
            </td></tr>`;
        }
        return;
    }

    let html = '';
    displayRows.forEach((row, index) => {
        const bgRow = row.is_valid ? (index % 2 === 0 ? '#ffffff' : '#fcfdfd') : '#fff5f5';
        const borderRow = row.is_valid ? 'border-bottom: 1px solid #f1f5f9;' : 'border-bottom: 1px solid #fed7d7;';

        const genderBadge = row.gender === 'P'
            ? '<span class="badge" style="background: #fdf2f8; color: #db2777; border: 1px solid #fbcfe8;">Perempuan</span>'
            : '<span class="badge" style="background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe;">Laki-laki</span>';

        const classBadge = row.class_name
            ? `<span class="badge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-weight: 600;"><i class="fa-solid fa-school" style="margin-right: 4px;"></i>${escapeHtml(row.class_name)}</span>`
            : '<span class="badge" style="background: #f1f5f9; color: #64748b;">Belum Masuk Kelas</span>';

        let statusCell = '';
        if (row.is_valid) {
            statusCell = `<span class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; padding: 4px 10px; font-weight: 600;">
                <i class="fa-solid fa-circle-check"></i> Siap Masuk
            </span>`;
        } else {
            const errList = (row.errors || []).map(err => `<li>${escapeHtml(err)}</li>`).join('');
            statusCell = `<div>
                <span class="badge" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; padding: 3px 8px; margin-bottom: 4px; display: inline-block; font-weight: 700;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Tidak Sesuai
                </span>
                <ul style="margin: 0; padding-left: 14px; text-align: left; font-size: 11px; color: #dc2626; line-height: 1.35;">
                    ${errList}
                </ul>
            </div>`;
        }

        html += `<tr style="${bgRow} ${borderRow}">
            <td style="text-align: center; color: #64748b; font-weight: 600;">${index + 1}</td>
            <td>
                <div style="font-weight: 700; color: #0f172a; font-size: 12.5px;">${escapeHtml(row.nama)}</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                    NIS: <code style="background: #f1f5f9; padding: 1px 4px; border-radius: 4px; color: #0284c7;">${escapeHtml(row.nis)}</code>
                    ${row.nisn ? ` | NISN: <code>${escapeHtml(row.nisn)}</code>` : ''}
                </div>
                ${row.nik ? `<div style="font-size: 10.5px; color: #94a3b8;">NIK: ${escapeHtml(row.nik)}</div>` : ''}
            </td>
            <td style="text-align: center;">${genderBadge}</td>
            <td>
                <div>${escapeHtml(row.birthplace || '-')}</div>
                ${row.birthdate ? `<div style="font-size: 11px; color: #64748b;">${escapeHtml(row.birthdate)}</div>` : ''}
            </td>
            <td>
                <div style="font-weight: 600; color: #334155;">${escapeHtml(row.father_name || 'Orang Tua / Wali')}</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                    <i class="fa-solid fa-address-card" title="No. KK (Username Ortu)" style="color: #64748b;"></i> KK: <code>${escapeHtml(row.no_kk)}</code>
                </div>
                ${row.parent_phone ? `<div style="font-size: 11px; color: #166534;"><i class="fa-brands fa-whatsapp" style="color: #16a34a;"></i> ${escapeHtml(row.parent_phone)}</div>` : ''}
                ${row.parent_email ? `<div style="font-size: 11px; color: #0284c7;"><i class="fa-solid fa-envelope"></i> ${escapeHtml(row.parent_email)}</div>` : ''}
            </td>
            <td>${classBadge}</td>
            <td style="text-align: center; vertical-align: middle;">${statusCell}</td>
            <td style="text-align: center; vertical-align: middle;">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deletePreviewRow(${row.temp_id})" title="Hapus baris ini dari pratinjau" style="padding: 4px 10px; font-size: 11px; border-radius: 6px;">
                    <i class="fa-solid fa-trash-can"></i> Hapus
                </button>
            </td>
        </tr>`;
    });

    tbody.innerHTML = html;
}

function deletePreviewRow(tempId) {
    const idx = verifiedRows.findIndex(r => r.temp_id === tempId);
    if (idx !== -1) {
        verifiedRows.splice(idx, 1);
        renderVerifyTable();
    }
}

function dropAllInvalidRows() {
    const invalidCount = verifiedRows.filter(r => !r.is_valid).length;
    if (invalidCount === 0) return;

    if (!confirm('Hapus seluruh ' + invalidCount + ' baris data santri yang bermasalah?\n\nHanya data yang valid dan memenuhi syarat yang akan tersisa untuk dimasukkan ke sistem.')) {
        return;
    }

    verifiedRows = verifiedRows.filter(r => r.is_valid);
    currentVerifyFilter = 'all';
    renderVerifyTable();
}

function cancelVerifyImport() {
    if (verifiedRows.length > 0) {
        if (!confirm('Batalkan verifikasi impor data santri? Semua data pratinjau akan ditutup dan belum disimpan ke sistem.')) {
            return;
        }
    }
    const card = document.getElementById('verifyImportCard');
    if (card) card.style.display = 'none';
    verifiedRows = [];
}

function commitVerifiedImport() {
    const validRows = verifiedRows.filter(r => r.is_valid);
    if (validRows.length === 0) {
        alert('Tidak ada data santri yang berstatus valid untuk dimasukkan ke dalam sistem.');
        return;
    }

    const invalidCount = verifiedRows.length - validRows.length;
    let confirmMsg = 'Apakah Anda yakin ingin memasukkan ' + validRows.length + ' data santri yang telah terverifikasi ke dalam sistem database?';
    if (invalidCount > 0) {
        confirmMsg += '\n\nPerhatian: ' + invalidCount + ' data yang bermasalah/tidak sesuai tidak akan masuk ke sistem.';
    }

    if (!confirm(confirmMsg)) {
        return;
    }

    const btnCommit = document.getElementById('btnCommitImport');
    const originalHtml = btnCommit ? btnCommit.innerHTML : '';
    if (btnCommit) {
        btnCommit.disabled = true;
        btnCommit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses & Menyimpan Data...';
    }

    const postData = new URLSearchParams();
    postData.append('unit_id', verifyUnitId);
    postData.append('students_json', JSON.stringify(validRows));
    postData.append('csrf_token', '<?= CSRF::token() ?>');

    fetch('<?= $baseUrl ?>/students/confirm-import', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: postData.toString()
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
            alert('Gagal menyimpan data santri: ' + (data.message || 'Terjadi kesalahan'));
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

// Inisialisasi jika ada data verifikasi hasil submit non-AJAX / sesi
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($pendingImport) && !empty($pendingImport['rows'])): ?>
        const initialImport = <?= json_encode($pendingImport) ?>;
        if (initialImport && initialImport.rows && initialImport.rows.length > 0) {
            verifiedRows = initialImport.rows;
            if (initialImport.unit_id) {
                verifyUnitId = initialImport.unit_id;
            }
            const card = document.getElementById('verifyImportCard');
            if (card) {
                card.style.display = 'block';
                currentVerifyFilter = 'all';
                renderVerifyTable();
                card.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    <?php endif; ?>
});
</script>
<?php endif; ?>
