<?php
// views/master/units.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
$activeUnits = $activeUnits ?? $units ?? [];
$archivedUnits = $archivedUnits ?? [];
?>
<div class="page-header">
    <div>
        <h1 class="page-title">Master Sekolah &amp; Unit Yayasan</h1>
        <div class="page-subtitle">Kelola informasi unit (KB, TK, SD, SMP, SMA), logo resmi, kop surat, rekening penampung SPP, dan konfigurasi operasional.</div>
    </div>
    <div style="display: flex; gap: 8px;">
        <button type="button" class="btn btn-primary" onclick="openUnitModal()">
            <i class="fa-solid fa-plus"></i> Tambah Unit Sekolah
        </button>
    </div>
</div>

<!-- Tabs Unit Aktif vs Terarsip -->
<div style="display: flex; gap: 10px; margin-bottom: 16px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">
    <button type="button" id="tabActiveBtn" class="btn btn-primary btn-sm" onclick="switchUnitTab('active')" style="font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
        <i class="fa-solid fa-school"></i> Unit Sekolah Aktif
        <span class="badge" style="background: rgba(255,255,255,0.25); color: #ffffff;"><?= count($activeUnits) ?></span>
    </button>
    <button type="button" id="tabArchivedBtn" class="btn btn-outline btn-sm" onclick="switchUnitTab('archived')" style="font-weight: 600; display: inline-flex; align-items: center; gap: 6px; color: #64748b; border-color: #cbd5e1;">
        <i class="fa-solid fa-box-archive"></i> Unit Terarsip / Nonaktif
        <span class="badge" style="background: #e2e8f0; color: #475569;"><?= count($archivedUnits) ?></span>
    </button>
</div>

<!-- PANEL 1: UNIT SEKOLAH AKTIF -->
<div id="panelActiveUnits" class="card">
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 70px; text-align: center;">Logo</th>
                        <th style="width: 80px;">Kode</th>
                        <th>Nama Unit</th>
                        <th>Jenjang</th>
                        <th>Rekening SPP</th>
                        <th>Kontak &amp; Alamat</th>
                        <th style="width: 220px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($activeUnits)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 24px; color: var(--text-muted); font-style: italic;">
                                Tidak ada data unit sekolah aktif.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($activeUnits as $u): 
                            $hasLogo = !empty($u['logo_path']) && file_exists(dirname(__DIR__, 2) . '/public/' . ltrim($u['logo_path'], '/'));
                        ?>
                            <tr>
                                <td style="text-align: center; vertical-align: middle;">
                                    <?php if ($hasLogo): ?>
                                        <img src="<?= $baseUrl ?>/public/<?= htmlspecialchars(ltrim($u['logo_path'], '/')) ?>" 
                                             alt="Logo <?= SecurityHelper::escape($u['code']) ?>" 
                                             onerror="this.onerror=null; this.src='<?= $baseUrl ?>/<?= htmlspecialchars(ltrim($u['logo_path'], '/')) ?>';"
                                             style="height: 44px; width: 44px; object-fit: contain; border-radius: 8px; border: 1px solid var(--border-color); background: #ffffff; padding: 2px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                                    <?php else: ?>
                                        <div style="height: 44px; width: 44px; border-radius: 8px; background: #f8fafc; border: 1px dashed #cbd5e1; color: #94a3b8; display: flex; flex-direction: column; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; margin: 0 auto;" title="Belum ada logo">
                                            <i class="fa-solid fa-image" style="font-size: 14px; margin-bottom: 2px;"></i>
                                            <?= SecurityHelper::escape($u['code']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge badge-info"><?= SecurityHelper::escape($u['code']) ?></span></td>
                                <td>
                                    <strong><?= SecurityHelper::escape($u['name']) ?></strong>
                                    <div style="font-size: 11px; color: var(--text-muted);"><?= SecurityHelper::escape($u['kop_subtitle']) ?></div>
                                    <div style="margin-top: 5px; display: flex; gap: 4px; flex-wrap: wrap;">
                                        <?php if (!empty($u['npsn'])): ?>
                                            <span class="badge badge-info" style="font-size: 10px;">NPSN: <?= SecurityHelper::escape($u['npsn']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($u['akreditasi'])): ?>
                                            <span class="badge badge-success" style="font-size: 10px;">Akreditasi: <?= SecurityHelper::escape($u['akreditasi']) ?><?= !empty($u['akreditasi_expired_at']) ? ' (s.d ' . date('d/m/Y', strtotime($u['akreditasi_expired_at'])) . ')' : '' ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($u['nomor_izin_operasional'])): ?>
                                            <span class="badge badge-secondary" style="font-size: 10px;">Izin Op: <?= !empty($u['izin_operasional_expired_at']) ? 's.d ' . date('d/m/Y', strtotime($u['izin_operasional_expired_at'])) : 'Ada' ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><span class="badge badge-secondary"><?= SecurityHelper::escape($u['level']) ?></span></td>
                                <td>
                                    <div><strong><?= SecurityHelper::escape($u['bank_name']) ?></strong></div>
                                    <div style="font-size: 12px; color: #0284c7;"><?= SecurityHelper::escape($u['bank_account']) ?> (a.n. <?= SecurityHelper::escape($u['bank_holder']) ?>)</div>
                                </td>
                                <td>
                                    <div><?= SecurityHelper::escape($u['phone']) ?> | <?= SecurityHelper::escape($u['email']) ?></div>
                                    <div style="font-size: 11px; color: var(--text-muted);"><?= SecurityHelper::escape($u['address']) ?></div>
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: flex; gap: 6px; justify-content: center; flex-wrap: wrap;">
                                        <button type="button" class="btn btn-outline btn-sm" onclick='openUploadLogoModal(<?= json_encode($u) ?>)' title="Upload / Ganti Logo Resmi" style="color: #0284c7; border-color: #bae6fd; background: #f0f9ff;">
                                            <i class="fa-solid fa-image"></i> Logo
                                        </button>
                                        <button type="button" class="btn btn-outline btn-sm" onclick='editUnit(<?= json_encode($u) ?>)' title="Edit Data Unit">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </button>
                                        <button type="button" class="btn btn-outline btn-sm" onclick='confirmDeleteUnit(<?= json_encode($u) ?>)' title="Hapus / Arsipkan Unit Sekolah" style="color: #dc2626; border-color: #fca5a5; background: #fff5f5;">
                                            <i class="fa-solid fa-trash-can"></i> Hapus
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

<!-- PANEL 2: UNIT SEKOLAH TERARSIP / NONAKTIF -->
<div id="panelArchivedUnits" class="card" style="display: none;">
    <div class="card-header" style="background: #f8fafc; padding: 12px 18px; border-bottom: 1px solid #e2e8f0;">
        <strong style="color: #475569;"><i class="fa-solid fa-box-archive" style="color: #64748b; margin-right: 6px;"></i> Arsip Unit Sekolah yang Dinonaktifkan</strong>
        <p style="margin: 2px 0 0 0; font-size: 0.8125rem; color: #64748b;">
            Unit di bawah ini telah dinonaktifkan untuk pendaftaran baru dan operasional masa depan. <strong>Seluruh data arsip, rapor, nilai santri, dan laporan masa lalu tetap tersimpan utuh dan tidak hilang.</strong>
        </p>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 80px;">Kode</th>
                        <th>Nama Unit</th>
                        <th>Jenjang</th>
                        <th>Tanggal Dinonaktifkan</th>
                        <th>Alasan Penonaktifan</th>
                        <th style="width: 140px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($archivedUnits)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px; color: var(--text-muted); font-style: italic;">
                                <i class="fa-solid fa-circle-check" style="color: #10b981; font-size: 1.5rem; display: block; margin-bottom: 6px;"></i>
                                Tidak ada unit sekolah yang terarsip/nonaktif. Semua unit beroperasi aktif.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($archivedUnits as $au): ?>
                            <tr style="background: #fffafa;">
                                <td><span class="badge" style="background: #fee2e2; color: #991b1b;"><?= SecurityHelper::escape($au['code']) ?></span></td>
                                <td>
                                    <strong style="color: #334155;"><?= SecurityHelper::escape($au['name']) ?></strong>
                                    <div style="font-size: 11px; color: #dc2626;"><i class="fa-solid fa-circle-xmark"></i> Status: Nonaktif (Arsip Terlindungi)</div>
                                </td>
                                <td><span class="badge badge-secondary"><?= SecurityHelper::escape($au['level']) ?></span></td>
                                <td style="font-size: 0.8125rem; color: #475569;">
                                    <?= !empty($au['deleted_at']) ? date('d M Y H:i', strtotime($au['deleted_at'])) : '-' ?>
                                </td>
                                <td style="font-size: 0.8125rem; color: #475569; font-style: italic;">
                                    <?= SecurityHelper::escape($au['deletion_reason'] ?: 'Unit dinonaktifkan oleh Operator Yayasan') ?>
                                </td>
                                <td style="text-align: center;">
                                    <form method="POST" action="<?= $baseUrl ?>/master/units/restore" onsubmit="return confirm('Pulihkan unit <?= SecurityHelper::escape($au['name']) ?> kembali menjadi unit aktif?')">
                                        <?= CSRF::inputField() ?>
                                        <input type="hidden" name="id" value="<?= $au['id'] ?>">
                                        <button type="submit" class="btn btn-outline btn-sm" style="color: #15803d; border-color: #86efac; background: #f0fdf4;" title="Pulihkan / Aktifkan Kembali Unit">
                                            <i class="fa-solid fa-rotate-left"></i> Pulihkan
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form Tambah/Edit Unit -->
<div class="modal-overlay" id="unitModal">
    <div class="modal-container">
        <form method="POST" action="<?= $baseUrl ?>/master/units/save" enctype="multipart/form-data">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="id" id="unit_id" value="0">
            
            <div class="modal-header">
                <h3 class="card-title" id="modalUnitTitle" style="margin: 0; font-size: 1.125rem;">Tambah Unit Sekolah Baru</h3>
            </div>
            
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Kode Singkat Unit *</label>
                        <input type="text" name="code" id="unit_code" class="form-control" placeholder="Contoh: SD AIS" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Jenjang Pendidikan *</label>
                        <select name="level" id="unit_level" class="form-control" required>
                            <option value="KB">KB (Kelompok Bermain)</option>
                            <option value="TK">TK (Taman Kanak-Kanak)</option>
                            <option value="SD">SD (Sekolah Dasar)</option>
                            <option value="SMP">SMP (Sekolah Menengah Pertama)</option>
                            <option value="SMA">SMA (Sekolah Menengah Atas)</option>
                            <option value="Yayasan">Kantor Yayasan</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Nama Resmi Satuan Pendidikan *</label>
                    <input type="text" name="name" id="unit_name" class="form-control" placeholder="Contoh: SD ALKAHFI ISLAMIC SCHOOL BATAM" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Sub-Judul / Keterangan Kop Surat</label>
                    <input type="text" name="kop_subtitle" id="unit_kop_sub" class="form-control" placeholder="Contoh: Terakreditasi A - NPSN: 69987654">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label">NPSN</label>
                        <input type="text" name="npsn" id="unit_npsn" class="form-control" placeholder="Contoh: 69987654">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status Akreditasi</label>
                        <select name="akreditasi" id="unit_akreditasi" class="form-control">
                            <option value="">-- Pilih --</option>
                            <option value="A">A (Unggul)</option>
                            <option value="B">B (Baik)</option>
                            <option value="C">C (Cukup)</option>
                            <option value="Belum">Belum Terakreditasi</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Masa Berlaku Akreditasi</label>
                        <input type="date" name="akreditasi_expired_at" id="unit_akreditasi_expired_at" class="form-control">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label">Nomor SK Akreditasi</label>
                        <input type="text" name="nomor_akreditasi" id="unit_nomor_akreditasi" class="form-control" placeholder="No. SK BAN-S/M">
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. Izin Operasional</label>
                        <input type="text" name="nomor_izin_operasional" id="unit_nomor_izin_operasional" class="form-control" placeholder="No. Izin Disdik">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Masa Berlaku Izin Operasional</label>
                    <input type="date" name="izin_operasional_expired_at" id="unit_izin_operasional_expired_at" class="form-control">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="phone" id="unit_phone" class="form-control" placeholder="Contoh: 08117000001">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Resmi Unit</label>
                        <input type="email" name="email" id="unit_email" class="form-control" placeholder="Contoh: sd@alkahfi.sch.id">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Alamat Lengkap Unit</label>
                    <textarea name="address" id="unit_address" class="form-control" rows="2" placeholder="Jalan, kelurahan, kecamatan, Batam"></textarea>
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-top: 10px;">
                    <strong style="font-size: 0.875rem; color: #1e293b; display: block; margin-bottom: 8px;">Rekening Penampung SPP &amp; Tagihan</strong>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" style="font-size: 0.75rem;">Nama Bank</label>
                            <input type="text" name="bank_name" id="unit_bank_name" class="form-control" placeholder="BSI / Muamalat / Mandiri">
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" style="font-size: 0.75rem;">Nomor Rekening</label>
                            <input type="text" name="bank_account" id="unit_bank_account" class="form-control" placeholder="Contoh: 7123456789">
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" style="font-size: 0.75rem;">Atas Nama (Holder)</label>
                            <input type="text" name="bank_holder" id="unit_bank_holder" class="form-control" placeholder="Yayasan Islam Al-Kahfi">
                        </div>
                    </div>
                </div>

                <!-- Logo Section -->
                <div style="margin-top: 14px;">
                    <label class="form-label" style="font-weight: 600;">Logo Resmi Unit (Kop &amp; Rapor)</label>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <div id="unit_logo_preview_wrap" style="width: 50px; height: 50px; border-radius: 8px; border: 1px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; background: #ffffff; overflow: hidden;">
                            <img id="unit_logo_img" src="" alt="Preview Logo" style="max-width: 100%; max-height: 100%; object-fit: contain; display: none;">
                            <span id="unit_logo_placeholder" style="font-size: 10px; color: #94a3b8;"><i class="fa-solid fa-image"></i></span>
                        </div>
                        <div style="flex: 1;">
                            <input type="file" name="logo" id="unit_logo_file" class="form-control" accept="image/png, image/jpeg, image/webp, image/svg+xml">
                            <span style="font-size: 11px; color: var(--text-muted);">Maksimal 2MB. Format: PNG transparan, JPG, WEBP, atau SVG.</span>
                        </div>
                    </div>
                    <div id="delete_logo_wrapper" style="margin-top: 6px; display: none;">
                        <label style="font-size: 0.8125rem; color: #dc2626; cursor: pointer;">
                            <input type="checkbox" name="delete_logo" id="delete_logo_chk" value="1"> Hapus logo saat ini dan gunakan logo standar yayasan
                        </label>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="btn btn-outline" onclick="closeUnitModal()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan Unit Sekolah</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Upload Logo Khusus -->
<div class="modal-overlay" id="uploadLogoModal">
    <div class="modal-container" style="max-width: 480px;">
        <form method="POST" action="<?= $baseUrl ?>/master/units/upload-logo" enctype="multipart/form-data">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="unit_id" id="logo_modal_unit_id" value="0">
            
            <div class="modal-header">
                <h3 class="card-title" id="logoModalTitle" style="margin: 0; font-size: 1.125rem;">Upload Logo Resmi Unit</h3>
            </div>
            
            <div class="modal-body">
                <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 14px;">
                    Logo ini akan dicantumkan pada kop surat dinas, rapor digital bulanan/semester santri, kartu SPP, kwitansi pembayaran, dan sertifikat prestasi.
                </p>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Pilih File Logo Baru</label>
                    <input type="file" name="logo" class="form-control" accept="image/png, image/jpeg, image/webp, image/svg+xml" required>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Rekomendasi format PNG transparan dengan resolusi min. 300x300 px (Maks. 2MB).</div>
                </div>
            </div>

            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="btn btn-outline" onclick="closeUploadLogoModal()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-cloud-arrow-up"></i> Upload Logo</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Unit Sekolah (Soft Delete dengan Proteksi Arsip) -->
<div class="modal-overlay" id="deleteUnitModal">
    <div class="modal-container" style="max-width: 520px;">
        <form method="POST" action="<?= $baseUrl ?>/master/units/delete">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="id" id="delete_modal_unit_id" value="0">

            <div class="modal-header" style="border-bottom: 1px solid #fee2e2; background: #fff5f5;">
                <h3 class="card-title" style="margin: 0; font-size: 1.125rem; color: #b91c1c;">
                    <i class="fa-solid fa-triangle-exclamation" style="margin-right: 6px;"></i> Hapus / Nonaktifkan Unit Sekolah
                </h3>
            </div>

            <div class="modal-body" style="padding: 18px;">
                <div style="background: #eff6ff; border-left: 4px solid #3b82f6; border-radius: 6px; padding: 12px; margin-bottom: 16px;">
                    <strong style="color: #1e40af; font-size: 0.875rem; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-shield-halved"></i> Perlindungan Keutuhan Data Arsip
                    </strong>
                    <div style="font-size: 0.8125rem; color: #1e3a8a; margin-top: 4px; line-height: 1.4;">
                        Penghapusan unit ini bersifat <strong>Soft Delete (Pengarsipan Aman)</strong>:
                        <ul style="margin: 4px 0 0 16px; padding: 0;">
                            <li><strong>Data masa lalu &amp; arsip TIDAK HILANG</strong>: Nilai rapor siswa, berkas administrasi guru, data alumni, catatan keuangan SPP tetap aman tersimpan.</li>
                            <li><strong>Untuk masa depan</strong>: Unit ini tidak lagi muncul pada SPMB baru, pendaftaran kelas, maupun operasional tahun ajaran mendatang.</li>
                        </ul>
                    </div>
                </div>

                <p style="font-size: 0.875rem; color: #334155; margin-bottom: 12px;">
                    Apakah Anda yakin ingin menonaktifkan unit sekolah: <strong id="delete_modal_unit_name" style="color: #b91c1c;"></strong> (<span id="delete_modal_unit_code"></span>)?
                </p>

                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600;">Alasan Penutupan / Pengarsipan Unit</label>
                    <textarea name="reason" id="delete_modal_reason" class="form-control" rows="2" placeholder="Contoh: Unit digabung ke kampus utama, atau penataan kembali struktur yayasan..."></textarea>
                </div>
            </div>

            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="btn btn-outline" onclick="closeDeleteUnitModal()">Batal</button>
                <button type="submit" class="btn btn-danger" style="background: #dc2626; border-color: #dc2626; color: #ffffff;">
                    <i class="fa-solid fa-trash-can"></i> Ya, Hapus &amp; Arsipkan Unit
                </button>
            </div>
        </form>
    </div>
</div>

<script>
var baseAppUrl = '<?= $baseUrl ?>';

function switchUnitTab(tab) {
    var pActive = document.getElementById('panelActiveUnits');
    var pArchived = document.getElementById('panelArchivedUnits');
    var bActive = document.getElementById('tabActiveBtn');
    var bArchived = document.getElementById('tabArchivedBtn');

    if (tab === 'active') {
        pActive.style.display = 'block';
        pArchived.style.display = 'none';
        bActive.className = 'btn btn-primary btn-sm';
        bActive.style.color = '#ffffff';
        bArchived.className = 'btn btn-outline btn-sm';
        bArchived.style.color = '#64748b';
    } else {
        pActive.style.display = 'none';
        pArchived.style.display = 'block';
        bArchived.className = 'btn btn-primary btn-sm';
        bArchived.style.color = '#ffffff';
        bActive.className = 'btn btn-outline btn-sm';
        bActive.style.color = '#64748b';
    }
}

function openUnitModal() {
    document.getElementById('unit_id').value = '0';
    document.getElementById('modalUnitTitle').innerText = 'Tambah Unit Sekolah Baru';
    document.getElementById('unit_code').value = '';
    document.getElementById('unit_name').value = '';
    document.getElementById('unit_kop_sub').value = '';
    document.getElementById('unit_phone').value = '';
    document.getElementById('unit_email').value = '';
    document.getElementById('unit_address').value = '';
    document.getElementById('unit_bank_name').value = '';
    document.getElementById('unit_bank_account').value = '';
    document.getElementById('unit_bank_holder').value = '';
    document.getElementById('unit_npsn').value = '';
    document.getElementById('unit_akreditasi').value = '';
    document.getElementById('unit_nomor_akreditasi').value = '';
    document.getElementById('unit_akreditasi_expired_at').value = '';
    document.getElementById('unit_nomor_izin_operasional').value = '';
    document.getElementById('unit_izin_operasional_expired_at').value = '';
    document.getElementById('unit_logo_file').value = '';
    document.getElementById('unit_logo_img').style.display = 'none';
    document.getElementById('unit_logo_img').src = '';
    document.getElementById('unit_logo_placeholder').style.display = 'block';
    document.getElementById('delete_logo_wrapper').style.display = 'none';
    document.getElementById('delete_logo_chk').checked = false;
    document.getElementById('unitModal').classList.add('active');
}

function editUnit(u) {
    document.getElementById('unit_id').value = u.id;
    document.getElementById('modalUnitTitle').innerText = 'Edit Unit: ' + u.name;
    document.getElementById('unit_code').value = u.code;
    document.getElementById('unit_level').value = u.level;
    document.getElementById('unit_name').value = u.name;
    document.getElementById('unit_kop_sub').value = u.kop_subtitle;
    document.getElementById('unit_phone').value = u.phone || '';
    document.getElementById('unit_email').value = u.email || '';
    document.getElementById('unit_address').value = u.address || '';
    document.getElementById('unit_bank_name').value = u.bank_name || '';
    document.getElementById('unit_bank_account').value = u.bank_account || '';
    document.getElementById('unit_bank_holder').value = u.bank_holder || '';
    document.getElementById('unit_npsn').value = u.npsn || '';
    document.getElementById('unit_akreditasi').value = u.akreditasi || '';
    document.getElementById('unit_nomor_akreditasi').value = u.nomor_akreditasi || '';
    document.getElementById('unit_akreditasi_expired_at').value = u.akreditasi_expired_at || '';
    document.getElementById('unit_nomor_izin_operasional').value = u.nomor_izin_operasional || '';
    document.getElementById('unit_izin_operasional_expired_at').value = u.izin_operasional_expired_at || '';
    document.getElementById('unit_logo_file').value = '';
    document.getElementById('delete_logo_chk').checked = false;

    var img = document.getElementById('unit_logo_img');
    var placeholder = document.getElementById('unit_logo_placeholder');
    var delWrap = document.getElementById('delete_logo_wrapper');
    if (u.logo_path) {
        img.src = baseAppUrl + '/public/' + u.logo_path.replace(/^\//, '');
        img.style.display = 'block';
        placeholder.style.display = 'none';
        delWrap.style.display = 'block';
    } else {
        img.style.display = 'none';
        img.src = '';
        placeholder.style.display = 'block';
        delWrap.style.display = 'none';
    }

    document.getElementById('unitModal').classList.add('active');
}

function closeUnitModal() {
    document.getElementById('unitModal').classList.remove('active');
}

function openUploadLogoModal(u) {
    document.getElementById('logo_modal_unit_id').value = u.id;
    document.getElementById('logoModalTitle').innerText = 'Upload Logo: ' + u.name;
    document.getElementById('uploadLogoModal').classList.add('active');
}

function closeUploadLogoModal() {
    document.getElementById('uploadLogoModal').classList.remove('active');
}

function confirmDeleteUnit(u) {
    document.getElementById('delete_modal_unit_id').value = u.id;
    document.getElementById('delete_modal_unit_name').innerText = u.name;
    document.getElementById('delete_modal_unit_code').innerText = u.code;
    document.getElementById('delete_modal_reason').value = '';
    document.getElementById('deleteUnitModal').classList.add('active');
}

function closeDeleteUnitModal() {
    document.getElementById('deleteUnitModal').classList.remove('active');
}
</script>
