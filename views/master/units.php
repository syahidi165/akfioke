<?php
// views/master/units.php
use Core\SecurityHelper;
use Core\CSRF;
?>
<div class="page-header">
    <div>
        <h1 class="page-title">Master Sekolah & Unit Yayasan</h1>
        <div class="page-subtitle">Kelola informasi unit (KB, TK, SD, SMP, SMA), logo resmi, kop surat, rekening penampung SPP, dan konfigurasi.</div>
    </div>
    <div>
        <button type="button" class="btn btn-primary" onclick="openUnitModal()">
            <i class="fa-solid fa-plus"></i> Tambah Unit Sekolah
        </button>
    </div>
</div>

<div class="card">
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
                        <th>Kontak & Alamat</th>
                        <th style="width: 160px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($units as $u): 
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
                                <div style="display: flex; gap: 6px; justify-content: center;">
                                    <button type="button" class="btn btn-outline btn-sm" onclick='openUploadLogoModal(<?= json_encode($u) ?>)' title="Upload / Ganti Logo Resmi" style="color: #0284c7; border-color: #bae6fd; background: #f0f9ff;">
                                        <i class="fa-solid fa-image"></i> Logo
                                    </button>
                                    <button type="button" class="btn btn-outline btn-sm" onclick='editUnit(<?= json_encode($u) ?>)' title="Edit Data Unit">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form Unit -->
<div class="modal-overlay" id="unitModal">
    <div class="modal-container">
        <form method="POST" action="<?= $baseUrl ?>/master/units/save" enctype="multipart/form-data">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="id" id="unit_id" value="0">
            
            <div class="modal-header">
                <h3 class="card-title" id="modalUnitTitle">Tambah Unit Sekolah</h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeUnitModal()">&times;</button>
            </div>
            <div class="modal-body">
                <!-- Logo Unit Sekolah -->
                <div class="form-group" style="padding: 12px; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px;">
                    <label class="form-label" style="font-weight: 600; display: flex; justify-content: space-between; align-items: center;">
                        <span><i class="fa-solid fa-image" style="color: var(--primary); margin-right: 6px;"></i> Logo Unit Sekolah</span>
                        <span style="font-size: 11px; font-weight: normal; color: var(--text-muted);">Maks. 2MB (PNG, JPG, WEBP, SVG)</span>
                    </label>
                    <div style="display: flex; align-items: center; gap: 14px; margin-top: 8px;">
                        <div id="unit_logo_preview_box" style="width: 56px; height: 56px; border-radius: 8px; border: 1px solid var(--border-color); background: #ffffff; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                            <span id="unit_logo_placeholder" style="color: #94a3b8; font-size: 22px;"><i class="fa-solid fa-image"></i></span>
                            <img id="unit_logo_img" src="" alt="Preview" style="display: none; width: 100%; height: 100%; object-fit: contain;">
                        </div>
                        <div style="flex: 1;">
                            <input type="file" name="logo" id="unit_logo_file" class="form-control form-control-sm" accept="image/png,image/jpeg,image/webp,image/svg+xml" onchange="previewLogoFile(this, 'unit_logo_img', 'unit_logo_placeholder')">
                            <div id="delete_logo_wrapper" style="margin-top: 6px; display: none;">
                                <label style="font-size: 12px; color: #ef4444; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                                    <input type="checkbox" name="delete_logo" value="1" id="delete_logo_chk"> Hapus logo saat ini
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid-cols-2">
                    <div class="form-group">
                        <label class="form-label">Kode Unit</label>
                        <input type="text" name="code" id="unit_code" class="form-control" required placeholder="Contoh: SD">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jenjang</label>
                        <select name="level" id="unit_level" class="form-control" required>
                            <option value="KB">KB (PAUD)</option>
                            <option value="TK">TK</option>
                            <option value="SD">SD</option>
                            <option value="SMP">SMP</option>
                            <option value="SMA">SMA</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Lengkap Unit</label>
                    <input type="text" name="name" id="unit_name" class="form-control" required placeholder="Contoh: SD Islam Al Kahfi Batam">
                </div>

                <div class="form-group">
                    <label class="form-label">Subjudul Kop Surat (Resmi)</label>
                    <input type="text" name="kop_subtitle" id="unit_kop_sub" class="form-control" required placeholder="Contoh: SEKOLAH DASAR ISLAM AL KAHFI">
                </div>

                <div class="grid-cols-2">
                    <div class="form-group">
                        <label class="form-label">No. Telepon</label>
                        <input type="text" name="phone" id="unit_phone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Unit</label>
                        <input type="email" name="email" id="unit_email" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Alamat Lengkap</label>
                    <textarea name="address" id="unit_address" class="form-control" rows="2"></textarea>
                </div>

                <h4 style="font-size: 13px; font-weight: 700; margin: 16px 0 10px; color: var(--primary); border-top: 1px dashed var(--border-color); padding-top: 14px;">
                    <i class="fa-solid fa-certificate" style="margin-right: 6px; color: var(--accent);"></i> Legalitas, Akreditasi & Izin Operasional
                </h4>
                <div class="grid-cols-2">
                    <div class="form-group">
                        <label class="form-label">NPSN</label>
                        <input type="text" name="npsn" id="unit_npsn" class="form-control" placeholder="Contoh: 69968633">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Peringkat Akreditasi</label>
                        <select name="akreditasi" id="unit_akreditasi" class="form-control">
                            <option value="">-- Pilih Peringkat --</option>
                            <option value="A">A (Unggul)</option>
                            <option value="B">B (Baik)</option>
                            <option value="C">C (Cukup)</option>
                            <option value="Belum Terakreditasi">Belum Terakreditasi</option>
                        </select>
                    </div>
                </div>
                <div class="grid-cols-2">
                    <div class="form-group">
                        <label class="form-label">Nomor Sertifikat / SK Akreditasi</label>
                        <input type="text" name="nomor_akreditasi" id="unit_nomor_akreditasi" class="form-control" placeholder="Contoh: 123/BAN-SM/SK/2022">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Masa Berlaku Akreditasi Hingga</label>
                        <input type="date" name="akreditasi_expired_at" id="unit_akreditasi_expired_at" class="form-control">
                    </div>
                </div>
                <div class="grid-cols-2">
                    <div class="form-group">
                        <label class="form-label">Nomor Izin Operasional</label>
                        <input type="text" name="nomor_izin_operasional" id="unit_nomor_izin_operasional" class="form-control" placeholder="Contoh: 1302/KPTS-4/VII/2017">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Masa Berlaku Izin Operasional Hingga</label>
                        <input type="date" name="izin_operasional_expired_at" id="unit_izin_operasional_expired_at" class="form-control">
                    </div>
                </div>

                <h4 style="font-size: 13px; font-weight: 700; margin: 16px 0 10px; color: var(--primary); border-top: 1px dashed var(--border-color); padding-top: 14px;">Rekening Bank Penampung SPP</h4>
                <div class="grid-cols-3">
                    <div class="form-group">
                        <label class="form-label">Nama Bank</label>
                        <input type="text" name="bank_name" id="unit_bank_name" class="form-control" placeholder="Contoh: BSI">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nomor Rekening</label>
                        <input type="text" name="bank_account" id="unit_bank_account" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Atas Nama</label>
                        <input type="text" name="bank_holder" id="unit_bank_holder" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeUnitModal()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Khusus Upload Logo Unit -->
<div class="modal-overlay" id="uploadLogoModal">
    <div class="modal-container" style="max-width: 480px;">
        <form method="POST" action="<?= $baseUrl ?>/master/units/upload-logo" enctype="multipart/form-data">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="unit_id" id="quick_logo_unit_id" value="0">
            
            <div class="modal-header">
                <h3 class="card-title" id="quickLogoTitle">Upload Logo Unit</h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeUploadLogoModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div style="text-align: center; margin-bottom: 20px;">
                    <div id="quick_logo_box" style="width: 110px; height: 110px; border-radius: 14px; border: 2px dashed #93c5fd; background: #f8fafc; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.06);">
                        <span id="quick_logo_placeholder" style="color: #94a3b8; font-size: 38px;"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                        <img id="quick_logo_img" src="" alt="Preview" style="display: none; max-width: 100%; max-height: 100%; object-fit: contain;">
                    </div>
                    <div style="font-weight: 700; font-size: 15px; color: var(--text-main);" id="quick_unit_name">-</div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px; line-height: 1.4;">
                        Logo ini akan otomatis tampil pada <strong>pojok kanan atas</strong> sistem dan <strong>kop surat resmi</strong> PDF setiap akun unit ini.
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Pilih File Gambar Logo</label>
                    <input type="file" name="logo" id="quick_file_input" class="form-control" accept="image/png,image/jpeg,image/webp,image/svg+xml" onchange="previewLogoFile(this, 'quick_logo_img', 'quick_logo_placeholder')">
                    <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 4px;">
                        Format yang didukung: <strong>PNG, JPG, JPEG, WEBP, SVG</strong> (Maks. 2MB). Disarankan berlatar transparan.
                    </div>
                </div>

                <div id="quick_delete_option" style="display: none; padding: 10px 14px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; margin-top: 14px;">
                    <label style="font-size: 12.5px; color: #b91c1c; cursor: pointer; display: flex; align-items: center; gap: 8px; margin: 0;">
                        <input type="checkbox" name="delete_logo" value="1" id="quick_delete_chk" onchange="toggleQuickUpload(this)"> 
                        <span><strong>Hapus logo saat ini</strong> (kembalikan ke lambang bawaan)</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeUploadLogoModal()">Batal</button>
                <button type="submit" class="btn btn-primary" id="btnSubmitLogo"><i class="fa-solid fa-cloud-arrow-up"></i> Simpan Logo</button>
            </div>
        </form>
    </div>
</div>

<script>
var baseAppUrl = '<?= $baseUrl ?>/public';

function previewLogoFile(input, imgId, placeholderId) {
    var img = document.getElementById(imgId);
    var placeholder = document.getElementById(placeholderId);
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            img.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function openUploadLogoModal(u) {
    document.getElementById('quick_logo_unit_id').value = u.id;
    document.getElementById('quick_unit_name').innerText = u.name;
    document.getElementById('quickLogoTitle').innerText = 'Upload Logo: ' + u.name;
    document.getElementById('quick_file_input').value = '';
    
    var img = document.getElementById('quick_logo_img');
    var placeholder = document.getElementById('quick_logo_placeholder');
    var delOption = document.getElementById('quick_delete_option');
    var delChk = document.getElementById('quick_delete_chk');
    delChk.checked = false;
    document.getElementById('quick_file_input').disabled = false;
    document.getElementById('btnSubmitLogo').innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Simpan Logo';
    document.getElementById('btnSubmitLogo').className = 'btn btn-primary';

    if (u.logo_path) {
        img.src = baseAppUrl + '/' + u.logo_path;
        img.style.display = 'block';
        placeholder.style.display = 'none';
        delOption.style.display = 'block';
    } else {
        img.style.display = 'none';
        img.src = '';
        placeholder.style.display = 'block';
        delOption.style.display = 'none';
    }

    document.getElementById('uploadLogoModal').classList.add('active');
}

function closeUploadLogoModal() {
    document.getElementById('uploadLogoModal').classList.remove('active');
}

function toggleQuickUpload(chk) {
    var fileInput = document.getElementById('quick_file_input');
    var submitBtn = document.getElementById('btnSubmitLogo');
    if (chk.checked) {
        fileInput.disabled = true;
        fileInput.value = '';
        submitBtn.innerHTML = '<i class="fa-solid fa-trash-can"></i> Hapus Logo';
        submitBtn.className = 'btn btn-danger';
    } else {
        fileInput.disabled = false;
        submitBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Simpan Logo';
        submitBtn.className = 'btn btn-primary';
    }
}

function openUnitModal() {
    document.getElementById('unit_id').value = '0';
    document.getElementById('modalUnitTitle').innerText = 'Tambah Unit Sekolah';
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
        img.src = baseAppUrl + '/' + u.logo_path;
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
</script>
