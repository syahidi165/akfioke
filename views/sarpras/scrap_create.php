<?php
// views/sarpras/scrap_create.php
use Core\SecurityHelper;

$isYayasan = $access['isYayasan'];
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <h1 class="page-title" style="margin: 0; font-size: 24px; font-weight: 800; color: #1e293b;">
                    <i class="fa-solid fa-file-circle-xmark" style="color: #dc2626; margin-right: 8px;"></i>
                    Buat Berita Acara Pernyataan Barang Scrap (F30)
                </h1>
                <span class="badge" style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; font-size: 13px; font-weight: 700; padding: 4px 10px;">
                    Formulir Resmi F30
                </span>
            </div>
            <div class="page-subtitle" style="color: #64748b; font-size: 13px; margin-top: 4px;">
                Pilih barang dari sistem yang akan dikeluarkan dan dihapus dari stok inventaris aktif unit.
            </div>
        </div>
        <div>
            <a href="<?= $baseUrl ?>/sarpras/scrap" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Berita Acara
            </a>
        </div>
    </div>
</div>

<form method="POST" action="<?= $baseUrl ?>/sarpras/scrap/save" enctype="multipart/form-data" id="scrapForm">
    <?= $csrfField ?>

    <div class="card" style="margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-radius: 12px;">
        <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 20px;">
            <h3 class="card-title" style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-file-lines" style="color: #dc2626;"></i> Data Pernyataan Berita Acara
            </h3>
        </div>
        <div class="card-body" style="padding: 20px;">
            <div class="grid-cols-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
                <div>
                    <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                        Nomor Form
                    </label>
                    <input type="text" name="form_code" class="form-control" value="F30" readonly style="background: #f8fafc; font-weight: 700; color: #dc2626;">
                </div>
                <div>
                    <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                        No. Berita Acara Scrap <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="report_number" class="form-control" value="<?= SecurityHelper::escape($suggestedNum) ?>" required>
                </div>
                <div>
                    <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                        Hari <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="day_name" class="form-control" value="<?= SecurityHelper::escape($dayName) ?>" required>
                </div>
                <div>
                    <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                        Tanggal Pernyataan <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="report_date" class="form-control" value="<?= $todayDate ?>" required>
                </div>
            </div>

            <?php if ($isYayasan): ?>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                        Unit Satuan Pendidikan <span class="text-danger">*</span>
                    </label>
                    <select name="unit_id" class="form-control" required>
                        <?php foreach ($units as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= ((int)$unit['id'] === (int)$u['id']) ? 'selected' : '' ?>>
                                <?= SecurityHelper::escape($u['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <input type="hidden" name="unit_id" value="<?= $unit['id'] ?? 2 ?>">
            <?php endif; ?>

            <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 12px; font-size: 12px; color: #991b1b; line-height: 1.4;">
                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                <strong>PERHATIAN:</strong> Sesuai SOP Inventaris Yayasan, barang yang dimasukkan ke dalam berita acara ini akan <strong>otomatis langsung dikeluarkan dari sistem dan dihapus dari status stok aktif</strong> (dialihkan menjadi status Afkir/Scrapped).
            </div>
        </div>
    </div>

    <!-- RINCIAN BARANG SCRAP DARI SISTEM -->
    <div class="card" style="margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-radius: 12px;">
        <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
                <h3 class="card-title" style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-boxes-stacked" style="color: #dc2626;"></i> Daftar Barang Target Scrap
                </h3>
                <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                    Pilih barang dari daftar aset yang sudah ada di sistem. Foto dan data pemegang akan otomatis ditarik.
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-primary" onclick="addScrapRow()" style="display: inline-flex; align-items: center; gap: 6px; background: #dc2626; border-color: #dc2626;">
                <i class="fa-solid fa-plus"></i> Tambah Item Scrap
            </button>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table" id="scrapTable" style="width: 100%; margin: 0;">
                    <thead>
                        <tr style="background: #dc2626; color: #ffffff; font-size: 12px; text-transform: uppercase;">
                            <th style="padding: 12px 10px; width: 45px; text-align: center;">No</th>
                            <th style="padding: 12px 10px; min-width: 250px;">Pilih Aset dari Sistem *</th>
                            <th style="padding: 12px 10px; min-width: 180px;">Nama Item / Rincian *</th>
                            <th style="padding: 12px 10px; width: 90px; text-align: center;">Jumlah *</th>
                            <th style="padding: 12px 10px; min-width: 180px;">Penanggung Jawab *</th>
                            <th style="padding: 12px 10px; min-width: 180px;">Foto Barang (Sistem / Fisik)</th>
                            <th style="padding: 12px 10px; width: 45px; text-align: center;">Hapus</th>
                        </tr>
                    </thead>
                    <tbody id="scrapTbody">
                        <!-- Default Row 1 -->
                        <tr class="scrap-row" data-row-index="0" style="border-bottom: 1px solid #e2e8f0;">
                            <td class="row-num" style="text-align: center; font-weight: 700; color: #64748b; padding: 12px 4px;">1</td>
                            <td style="padding: 10px 8px;">
                                <select name="asset_id[0]" class="form-control asset-select" onchange="onAssetSelected(this, 0)" style="font-size: 13px;">
                                    <option value="">-- Pilih dari Aset Sistem --</option>
                                    <?php foreach ($assets as $ast): ?>
                                        <option value="<?= $ast['id'] ?>" 
                                                data-name="<?= SecurityHelper::escape($ast['name']) ?>"
                                                data-holder="<?= SecurityHelper::escape($ast['holder_name'] ?? 'Sarpras') ?>"
                                                data-qty="<?= (int)$ast['quantity'] ?>"
                                                data-photo="<?= SecurityHelper::escape($ast['photo'] ?? '') ?>">
                                            <?= SecurityHelper::escape($ast['asset_code']) ?> - <?= SecurityHelper::escape($ast['name']) ?> (<?= SecurityHelper::escape($ast['condition_status']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small style="color: #64748b; font-size: 11px;">Otomatis mengisi nama, pemegang, & foto.</small>
                            </td>
                            <td style="padding: 10px 8px;">
                                <input type="text" name="item_name[0]" id="itemName_0" class="form-control" placeholder="Contoh: Kursi Guru 5, Lemari 1" required style="font-size: 13px;">
                            </td>
                            <td style="padding: 10px 8px;">
                                <input type="number" name="quantity[0]" id="itemQty_0" class="form-control" value="1" min="1" required style="font-size: 13px; font-weight: 700; text-align: center;">
                            </td>
                            <td style="padding: 10px 8px;">
                                <input type="text" name="responsible_person[0]" id="itemResp_0" class="form-control" value="Yona Melinda, B.Eng" required style="font-size: 13px;">
                            </td>
                            <td style="padding: 10px 8px;">
                                <div id="photoPreviewBox_0" style="margin-bottom: 4px; display: none;">
                                    <span class="badge badge-success" style="font-size: 11px;">
                                        <i class="fa-regular fa-image me-1"></i> Foto Tersedia di Sistem
                                    </span>
                                </div>
                                <input type="file" name="scrap_photo[0]" class="form-control" accept="image/*" style="font-size: 11px; padding: 4px 6px;">
                                <small style="color: #64748b; font-size: 10px;">Upload jika ingin memperbarui foto kondisi fisik terkini.</small>
                            </td>
                            <td style="text-align: center; padding: 10px 4px;">
                                <button type="button" class="btn btn-sm btn-outline text-danger" onclick="removeScrapRow(this)">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SEBAB DI SCRAP & PENANDATANGAN -->
    <div class="card" style="margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-radius: 12px;">
        <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 20px;">
            <h3 class="card-title" style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-signature" style="color: #0f766e;"></i> Alasan Scrap & Pihak Penandatangan Form F30
            </h3>
        </div>
        <div class="card-body" style="padding: 20px;">
            <!-- Sebab di scrap -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="font-weight: 700; font-size: 13px; color: #0f172a; margin-bottom: 6px; display: block;">
                    Sebab di scrap <span class="text-danger">*</span> :
                </label>
                <textarea name="reason" class="form-control" rows="3" placeholder="Jelaskan kondisi kerusakan atau pertimbangan afkir secara rinci (misal: Rangka patah, hidrolik mati, busa robek, tidak efisien bila diperbaiki)..." required></textarea>
            </div>

            <!-- Kolom Tanda Tangan Sesuai Form F30 -->
            <div class="grid-cols-3" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
                <div style="background: #f8fafc; padding: 14px; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <label class="form-label" style="font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 6px; display: block;">
                        Diambil Oleh (Pihak Pengambil/Pembuang)
                    </label>
                    <input type="text" name="taken_by_name" class="form-control" placeholder="Nama Petugas / Pengepul">
                    <input type="date" name="taken_by_date" class="form-control" style="margin-top: 8px;" value="<?= $todayDate ?>">
                </div>

                <div style="background: #f8fafc; padding: 14px; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <label class="form-label" style="font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 6px; display: block;">
                        Disiapkan Oleh (Sarpras) <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="prepared_by_name" class="form-control" value="Hasan Basri" required placeholder="Nama Petugas Sarpras">
                    <input type="text" name="prepared_by_title" class="form-control" value="Sarpras" style="margin-top: 8px;" placeholder="Jabatan">
                </div>

                <div style="background: #f8fafc; padding: 14px; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <label class="form-label" style="font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 6px; display: block;">
                        Disetujui Oleh (Deputi Direktur / KS) <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="approved_by_name" class="form-control" value="Yona Melinda, B.Eng" required placeholder="Nama Pejabat">
                    <input type="text" name="approved_by_title" class="form-control" value="Deputi Direktur AIS" style="margin-top: 8px;" placeholder="Jabatan">
                </div>
            </div>
        </div>
        <div class="card-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div style="font-size: 12px; color: #64748b;">
                <i class="fa-solid fa-trash-can me-1"></i> Aset yang disimpan akan langsung dikeluarkan dari stok sistem saat formulir ini disubmit.
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="<?= $baseUrl ?>/sarpras/scrap" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-danger" style="padding: 10px 24px; font-weight: 700; font-size: 14px;">
                    <i class="fa-solid fa-file-signature me-1"></i> Terbitkan Berita Acara Scrap (F30)
                </button>
            </div>
        </div>
    </div>
</form>

<script>
let scrapIndex = 1;
const assetOptionsJson = <?= json_encode($assets) ?>;

function onAssetSelected(selectEl, idx) {
    const selectedOpt = selectEl.options[selectEl.selectedIndex];
    if (!selectedOpt.value) return;

    const name = selectedOpt.getAttribute('data-name');
    const holder = selectedOpt.getAttribute('data-holder');
    const qty = selectedOpt.getAttribute('data-qty');
    const photo = selectedOpt.getAttribute('data-photo');

    const nameInput = document.getElementById('itemName_' + idx);
    const qtyInput = document.getElementById('itemQty_0') || document.querySelector(`[name="quantity[${idx}]"]`);
    const respInput = document.getElementById('itemResp_' + idx);
    const photoBox = document.getElementById('photoPreviewBox_' + idx);

    if (nameInput) nameInput.value = name;
    if (qtyInput) qtyInput.value = qty || 1;
    if (respInput && holder && holder !== '-') respInput.value = holder;
    if (photoBox) {
        photoBox.style.display = photo ? 'block' : 'none';
    }
}

function addScrapRow() {
    const tbody = document.getElementById('scrapTbody');
    const tr = document.createElement('tr');
    tr.className = 'scrap-row';
    tr.dataset.rowIndex = scrapIndex;
    tr.style.borderBottom = '1px solid #e2e8f0';

    let optHtml = '<option value="">-- Pilih dari Aset Sistem --</option>';
    assetOptionsJson.forEach(a => {
        optHtml += `<option value="${a.id}" data-name="${escapeHtml(a.name)}" data-holder="${escapeHtml(a.holder_name || 'Sarpras')}" data-qty="${a.quantity}" data-photo="${escapeHtml(a.photo || '')}">
            ${escapeHtml(a.asset_code)} - ${escapeHtml(a.name)} (${escapeHtml(a.condition_status)})
        </option>`;
    });

    tr.innerHTML = `
        <td class="row-num" style="text-align: center; font-weight: 700; color: #64748b; padding: 12px 4px;">${tbody.children.length + 1}</td>
        <td style="padding: 10px 8px;">
            <select name="asset_id[${scrapIndex}]" class="form-control" onchange="onAssetSelected(this, ${scrapIndex})" style="font-size: 13px;">
                ${optHtml}
            </select>
        </td>
        <td style="padding: 10px 8px;">
            <input type="text" name="item_name[${scrapIndex}]" id="itemName_${scrapIndex}" class="form-control" placeholder="Nama item" required style="font-size: 13px;">
        </td>
        <td style="padding: 10px 8px;">
            <input type="number" name="quantity[${scrapIndex}]" class="form-control" value="1" min="1" required style="font-size: 13px; font-weight: 700; text-align: center;">
        </td>
        <td style="padding: 10px 8px;">
            <input type="text" name="responsible_person[${scrapIndex}]" id="itemResp_${scrapIndex}" class="form-control" value="Yona Melinda, B.Eng" required style="font-size: 13px;">
        </td>
        <td style="padding: 10px 8px;">
            <div id="photoPreviewBox_${scrapIndex}" style="margin-bottom: 4px; display: none;">
                <span class="badge badge-success" style="font-size: 11px;">
                    <i class="fa-regular fa-image me-1"></i> Foto Tersedia di Sistem
                </span>
            </div>
            <input type="file" name="scrap_photo[${scrapIndex}]" class="form-control" accept="image/*" style="font-size: 11px; padding: 4px 6px;">
        </td>
        <td style="text-align: center; padding: 10px 4px;">
            <button type="button" class="btn btn-sm btn-outline text-danger" onclick="removeScrapRow(this)">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        </td>
    `;

    tbody.appendChild(tr);
    scrapIndex++;
    renumberScrapRows();
}

function removeScrapRow(btn) {
    const tbody = document.getElementById('scrapTbody');
    if (tbody.children.length <= 1) {
        alert('Minimal harus ada 1 item yang dimuat pada Berita Acara Scrap.');
        return;
    }
    btn.closest('tr').remove();
    renumberScrapRows();
}

function renumberScrapRows() {
    const rows = document.querySelectorAll('#scrapTbody .scrap-row');
    rows.forEach((r, idx) => {
        r.querySelector('.row-num').innerText = idx + 1;
    });
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>
