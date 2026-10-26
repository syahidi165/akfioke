<?php
// views/sarpras/po_create.php
use Core\SecurityHelper;

$isYayasan = $access['isYayasan'];
$initialUnitId = (int)($unit['id'] ?? 2);
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <h1 class="page-title" style="margin: 0; font-size: 24px; font-weight: 800; color: #1e293b;">
                    <i class="fa-solid fa-file-pen" style="color: #0f766e; margin-right: 8px;"></i> Formulir Permintaan Barang (Purchase Order)
                </h1>
                <span class="badge" style="background: #ccfbf1; color: #0f766e; border: 1px solid #99f6e4; font-size: 13px; font-weight: 700; padding: 4px 10px;">
                    Pengajuan Baru
                </span>
            </div>
            <div class="page-subtitle" style="color: #64748b; font-size: 13px; margin-top: 4px;">
                Isi rincian pengadaan barang atau pengerjaan sarpras. Formulir akan otomatis masuk ke antrean persetujuan Kepala Sekolah.
            </div>
        </div>
        <div>
            <a href="<?= $baseUrl ?>/sarpras/po" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar PO
            </a>
        </div>
    </div>
</div>

<form method="POST" action="<?= $baseUrl ?>/sarpras/po/save" enctype="multipart/form-data" id="poForm">
    <?= $csrfField ?>

    <!-- HEADER KOP & INFORMASI DASAR PO -->
    <div class="card" style="margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-radius: 12px;">
        <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 20px;">
            <h3 class="card-title" style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-building-columns" style="color: #0f766e;"></i> Informasi Pengadaan & Satuan Unit
            </h3>
        </div>
        <div class="card-body" style="padding: 20px;">
            <div class="grid-cols-3" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 16px;">
                <div>
                    <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                        No. Referensi PO <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="po_number" id="poNumberInput" class="form-control" value="<?= SecurityHelper::escape($suggestedRef) ?>" required>
                    <small style="color: #64748b; font-size: 11px;">Format: REQ/[UNIT]/[BULAN TAHUN]</small>
                </div>

                <div>
                    <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                        Tanggal Pengajuan <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="request_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div>
                    <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                        Satuan Unit Pemohon <span class="text-danger">*</span>
                    </label>
                    <?php if ($isYayasan): ?>
                        <select name="unit_id" id="poUnitSelect" class="form-control" onchange="onUnitChanged(this.value)" required style="font-weight: 700; color: #0f766e; border-color: #0d9488;">
                            <?php foreach ($units as $u): ?>
                                <option value="<?= $u['id'] ?>" data-code="<?= SecurityHelper::escape($u['code']) ?>" <?= ($initialUnitId === (int)$u['id']) ? 'selected' : '' ?>>
                                    <?= SecurityHelper::escape($u['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small style="color: #0f766e; font-size: 11px;"><i class="fa-solid fa-circle-info me-1"></i>Pilihan lokasi & aset otomatis mengikuti unit terpilih.</small>
                    <?php else: ?>
                        <input type="text" class="form-control" value="<?= SecurityHelper::escape($unit['name'] ?? 'Unit Sekolah') ?>" readonly style="background: #f8fafc; font-weight: 600;">
                        <input type="hidden" name="unit_id" id="poUnitSelect" value="<?= $initialUnitId ?>" data-code="<?= SecurityHelper::escape($unit['code'] ?? 'AIS') ?>">
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid-cols-2" style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                        Alasan Permintaan <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="reason" class="form-control" placeholder="Contoh: Memenuhi kebutuhan siswa/i dan guru" required>
                </div>
                <div>
                    <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                        Pengadaan Melalui
                    </label>
                    <input type="text" name="procurement_division" class="form-control" value="Divisi Pengadaan Barang" readonly style="background: #f8fafc;">
                </div>
            </div>

            <div class="grid-cols-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                        Tanggal Delivery Yang Diharapkan
                    </label>
                    <input type="date" name="delivery_date" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                </div>
                <div>
                    <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                        Lokasi Delivery <span class="text-danger">*</span>
                    </label>
                    <select name="delivery_location" id="deliveryLocationSelect" class="form-control" onchange="onDeliveryLocationChange(this)" required style="font-weight: 600;">
                        <!-- Opsi terisi otomatis sesuai unit sarpras -->
                    </select>
                    <input type="text" name="custom_delivery_location" id="customDeliveryLocationInput" class="form-control" placeholder="Ketik lokasi delivery lainnya..." style="display: none; margin-top: 6px; font-size: 13px;">
                    <small style="color: #64748b; font-size: 11px;">Pilihan lokasi delivery hanya menampilkan area / ruangan pada unit sarpras terkait.</small>
                </div>
            </div>

            <!-- TOGGLE KONTRAKTOR -->
            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 14px; margin-top: 8px;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 13px; font-weight: 700; color: #92400e; margin: 0;">
                    <input type="checkbox" name="needs_contractor" id="needsContractor" value="1" style="width: 18px; height: 18px;">
                    <span>Butuh Pengerjaan Lebih Lanjut yang Membutuhkan Kontraktor?</span>
                </label>
                <div style="font-size: 12px; color: #b45309; margin-top: 4px; padding-left: 28px;">
                    <i class="fa-solid fa-circle-info me-1"></i> Jika dicentang, setelah disetujui Direktur Pengadaan, PO akan otomatis diteruskan ke <strong>Direktur Sarpras (Yayasan)</strong> untuk persetujuan pengerjaan kontraktor. Jika tidak, cukup sampai Direktur Pengadaan.
                </div>
            </div>
        </div>
    </div>

    <!-- DAFTAR ITEM BARANG DINAMIS -->
    <div class="card" style="margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-radius: 12px;">
        <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
                <h3 class="card-title" style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-list-check" style="color: #0f766e;"></i> Rincian Barang yang Diminta
                </h3>
                <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                    Pilih barang dari daftar aset sekolah untuk pengisian posisi stok otomatis, atau input barang baru.
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-primary" onclick="addItemRow()" style="display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-plus"></i> Tambah Baris Barang
            </button>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table" id="itemsTable" style="width: 100%; margin: 0;">
                    <thead>
                        <tr style="background: #0f766e; color: #ffffff; font-size: 12px; text-transform: uppercase;">
                            <th style="padding: 12px 10px; width: 40px; text-align: center;">No</th>
                            <th style="padding: 12px 10px; min-width: 220px;">Pilih Aset / Deskripsi Item *</th>
                            <th style="padding: 12px 10px; min-width: 120px;">Size / Spesifikasi</th>
                            <th style="padding: 12px 10px; width: 85px;">RAPBS</th>
                            <th style="padding: 12px 10px; width: 90px;">Satuan</th>
                            <th style="padding: 12px 10px; width: 100px; text-align: center;">Posisi Stok</th>
                            <th style="padding: 12px 10px; width: 80px; text-align: center;">ORDER *</th>
                            <th style="padding: 12px 10px; min-width: 130px;">Waktu Pengadaan</th>
                            <th style="padding: 12px 10px; min-width: 150px;">Beban/Sub Unit</th>
                            <th style="padding: 12px 10px; min-width: 150px;">Kategori / Tipe</th>
                            <th style="padding: 12px 10px; min-width: 160px;">Foto Referensi</th>
                            <th style="padding: 12px 10px; width: 40px; text-align: center;">Hapus</th>
                        </tr>
                    </thead>
                    <tbody id="itemsTbody">
                        <!-- Default Row 1 -->
                        <tr class="item-row" data-row-index="0" style="border-bottom: 1px solid #e2e8f0;">
                            <td class="row-number" style="text-align: center; font-weight: 700; color: #64748b; padding: 10px 4px;">1</td>
                            <td style="padding: 10px 6px;">
                                <div style="margin-bottom: 5px;">
                                    <select class="form-control asset-select" onchange="onAssetChosen(this)" style="font-size: 11px; padding: 4px 6px; background: #f0fdfa; border: 1px solid #99f6e4; color: #0f766e; font-weight: 600; border-radius: 6px;">
                                        <!-- Opsi aset unit sekolah terisi otomatis -->
                                    </select>
                                </div>
                                <input type="text" name="description[0]" class="form-control item-desc-input" placeholder="Nama item / merk *" required style="font-size: 13px;">
                            </td>
                            <td style="padding: 10px 6px;">
                                <input type="text" name="specs[0]" class="form-control" placeholder="Size / Spek" style="font-size: 13px;">
                            </td>
                            <td style="padding: 10px 6px;">
                                <select name="rapbs[0]" class="form-control" style="font-size: 12px; padding: 6px 4px;">
                                    <option value="YA" selected>YA</option>
                                    <option value="TIDAK">TIDAK</option>
                                </select>
                            </td>
                            <td style="padding: 10px 6px;">
                                <input type="text" name="unit_of_measure[0]" class="form-control" value="unit" placeholder="unit/pcs" style="font-size: 12px;">
                            </td>
                            <td style="padding: 10px 6px;">
                                <input type="number" name="stock_position[0]" class="form-control stock-pos-input" value="0" min="0" style="font-size: 12px; text-align: center; font-weight: 700;">
                                <div class="stock-info-label" style="font-size: 10px; color: #0d9488; text-align: center; margin-top: 3px; font-weight: 600;"></div>
                            </td>
                            <td style="padding: 10px 6px;">
                                <input type="number" name="quantity_ordered[0]" class="form-control" value="1" min="1" required style="font-size: 13px; font-weight: 700; text-align: center;">
                            </td>
                            <td style="padding: 10px 6px;">
                                <input type="text" name="procurement_time[0]" class="form-control" value="<?= date('d F Y', strtotime('+14 days')) ?>" placeholder="21 August 2026" style="font-size: 12px;">
                            </td>
                            <td style="padding: 10px 6px;">
                                <select name="cost_burden_unit[0]" class="form-control cost-burden-select" onchange="onCostBurdenChange(this)" style="font-size: 12px; padding: 6px 4px;">
                                    <!-- Opsi beban/sub unit terisi otomatis -->
                                </select>
                                <input type="text" name="custom_cost_burden[0]" class="form-control custom-cost-burden-input" placeholder="Ketik sub unit..." style="display: none; font-size: 12px; margin-top: 4px;">
                            </td>
                            <td style="padding: 10px 6px;">
                                <select name="item_category_id[0]" class="form-control" style="font-size: 12px; margin-bottom: 4px;">
                                    <option value="">-- Kategori --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= SecurityHelper::escape($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label style="font-size: 11px; display: flex; align-items: center; gap: 4px; color: #475569; cursor: pointer;">
                                    <input type="checkbox" name="is_consumable[0]" value="1">
                                    ATK / Pecah Belah
                                </label>
                            </td>
                            <td style="padding: 10px 6px;">
                                <input type="file" name="item_photo[0]" class="form-control" accept="image/*" style="font-size: 11px; padding: 4px 6px;">
                            </td>
                            <td style="text-align: center; padding: 10px 4px;">
                                <button type="button" class="btn btn-sm btn-outline text-danger" onclick="removeRow(this)" title="Hapus baris">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- CATATAN KHUSUS -->
    <div class="card" style="margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-radius: 12px;">
        <div class="card-body" style="padding: 20px;">
            <label class="form-label" style="font-weight: 700; font-size: 13px; color: #0f172a; margin-bottom: 6px; display: block;">
                Catatan khusus jika ada :
            </label>
            <textarea name="special_notes" class="form-control" rows="3" placeholder="Tuliskan catatan urgensi, merk tertentu, atau ketentuan instalasi khusus..."></textarea>
        </div>
        <div class="card-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div style="font-size: 12px; color: #64748b;">
                <i class="fa-solid fa-clock-rotate-left me-1"></i> Setelah diajukan, timestamp pengajuan PO akan tercatat otomatis pada sistem.
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="<?= $baseUrl ?>/sarpras/po" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 700; font-size: 14px;">
                    <i class="fa-solid fa-paper-plane me-1"></i> Kirim Formulir PO ke Kepala Sekolah
                </button>
            </div>
        </div>
    </div>
</form>

<script>
let rowIndex = 1;
const assetsByUnit = <?= json_encode($assetsByUnit, JSON_UNESCAPED_UNICODE) ?>;
const locationsByUnit = <?= json_encode($locationsByUnit, JSON_UNESCAPED_UNICODE) ?>;
const subUnitsByUnit = <?= json_encode($subUnitsByUnit, JSON_UNESCAPED_UNICODE) ?>;
const categories = <?= json_encode($categories, JSON_UNESCAPED_UNICODE) ?>;

const categoryOptionsHtml = `
    <option value="">-- Kategori --</option>
    <?php foreach ($categories as $cat): ?>
        <option value="<?= $cat['id'] ?>"><?= SecurityHelper::escape($cat['name']) ?></option>
    <?php endforeach; ?>
`;

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function getCurrentUnitId() {
    const el = document.getElementById('poUnitSelect');
    return parseInt(el ? el.value : <?= $initialUnitId ?>) || <?= $initialUnitId ?>;
}

function getAssetOptionsHtml(unitId) {
    const assets = assetsByUnit[unitId] || [];
    let html = `<option value="">-- Pilih dari Aset Sekolah Ini (Stok Otomatis) --</option>`;
    if (assets.length > 0) {
        assets.forEach(a => {
            const stock = parseInt(a.total_stock) || 0;
            const catId = a.category_id || '';
            const isCons = a.is_consumable ? '1' : '0';
            const loc = a.location ? ` [${a.location}]` : '';
            html += `<option value="${escapeHtml(a.name)}" data-name="${escapeHtml(a.name)}" data-stock="${stock}" data-cat="${catId}" data-consumable="${isCons}">${escapeHtml(a.name)} (Stok: ${stock})${escapeHtml(loc)}</option>`;
        });
    } else {
        html += `<option value="" disabled>(Belum ada data aset di unit ini)</option>`;
    }
    html += `<option value="__NEW__">+ Input Barang Baru (Stok 0)...</option>`;
    return html;
}

function getCostBurdenOptionsHtml(unitId) {
    const subUnits = subUnitsByUnit[unitId] || [];
    let html = '';
    subUnits.forEach(s => {
        html += `<option value="${escapeHtml(s)}">${escapeHtml(s)}</option>`;
    });
    html += `<option value="__CUSTOM__">+ Sub Unit Lainnya (Ketik)...</option>`;
    return html;
}

function updateDeliveryLocations(unitId) {
    const locSelect = document.getElementById('deliveryLocationSelect');
    if (!locSelect) return;
    const locs = locationsByUnit[unitId] || [];
    let html = '';
    locs.forEach(l => {
        html += `<option value="${escapeHtml(l)}">${escapeHtml(l)}</option>`;
    });
    html += `<option value="__CUSTOM__">+ Ketik Lokasi Lainnya...</option>`;
    locSelect.innerHTML = html;

    const customInp = document.getElementById('customDeliveryLocationInput');
    if (customInp) {
        customInp.style.display = 'none';
        customInp.value = '';
    }
}

function updateAllRowsForUnit(unitId) {
    const assetOptions = getAssetOptionsHtml(unitId);
    const costBurdenOptions = getCostBurdenOptionsHtml(unitId);

    document.querySelectorAll('#itemsTbody .item-row').forEach(row => {
        const assetSelect = row.querySelector('.asset-select');
        if (assetSelect) {
            assetSelect.innerHTML = assetOptions;
        }

        const costSelect = row.querySelector('.cost-burden-select');
        if (costSelect) {
            costSelect.innerHTML = costBurdenOptions;
        }

        const customCost = row.querySelector('.custom-cost-burden-input');
        if (customCost) {
            customCost.style.display = 'none';
            customCost.value = '';
        }
    });
}

function onUnitChanged(unitId) {
    const uid = parseInt(unitId);
    updateDeliveryLocations(uid);
    updateAllRowsForUnit(uid);

    // Update PO reference code if prefix is REQ/
    const poNumInput = document.getElementById('poNumberInput');
    const unitSelect = document.getElementById('poUnitSelect');
    const selectedOpt = unitSelect?.options[unitSelect.selectedIndex];
    const unitCode = selectedOpt?.dataset.code || 'AIS';
    if (poNumInput && poNumInput.value.startsWith('REQ/')) {
        const parts = poNumInput.value.split('/');
        if (parts.length >= 3) {
            parts[1] = unitCode;
            poNumInput.value = parts.join('/');
        }
    }
}

function onAssetChosen(selectEl) {
    const row = selectEl.closest('.item-row');
    if (!row) return;

    const opt = selectEl.options[selectEl.selectedIndex];
    const descInput = row.querySelector('.item-desc-input');
    const stockInput = row.querySelector('.stock-pos-input');
    const stockLabel = row.querySelector('.stock-info-label');
    const catSelect = row.querySelector('select[name^="item_category_id"]');
    const consCheckbox = row.querySelector('input[type="checkbox"][name^="is_consumable"]');

    if (opt.value === '__NEW__') {
        if (stockInput) stockInput.value = 0;
        if (stockLabel) stockLabel.innerHTML = '<span class="badge" style="background: #f1f5f9; color: #475569; font-size: 10px; border: 1px solid #cbd5e1;">Item Baru (Stok 0)</span>';
        if (descInput) {
            descInput.value = '';
            descInput.focus();
        }
        if (consCheckbox) consCheckbox.checked = false;
    } else if (opt.dataset && opt.dataset.name) {
        if (descInput) descInput.value = opt.dataset.name;
        const stock = parseInt(opt.dataset.stock) || 0;
        if (stockInput) stockInput.value = stock;
        if (stockLabel) {
            stockLabel.innerHTML = `<span class="badge" style="background: #ecfdf5; color: #047857; font-size: 10px; border: 1px solid #a7f3d0;"><i class="fa-solid fa-boxes-stacked"></i> Stok: ${stock} unit</span>`;
        }
        if (catSelect && opt.dataset.cat) {
            catSelect.value = opt.dataset.cat;
        }
        if (consCheckbox) {
            consCheckbox.checked = (opt.dataset.consumable === '1');
        }
    } else {
        if (stockLabel) stockLabel.innerHTML = '';
    }
}

function onDeliveryLocationChange(selectEl) {
    const customInp = document.getElementById('customDeliveryLocationInput');
    if (!customInp) return;
    if (selectEl.value === '__CUSTOM__') {
        customInp.style.display = 'block';
        customInp.required = true;
        customInp.focus();
    } else {
        customInp.style.display = 'none';
        customInp.required = false;
        customInp.value = '';
    }
}

function onCostBurdenChange(selectEl) {
    const row = selectEl.closest('.item-row');
    if (!row) return;
    const customInp = row.querySelector('.custom-cost-burden-input');
    if (!customInp) return;
    if (selectEl.value === '__CUSTOM__') {
        customInp.style.display = 'block';
        customInp.required = true;
        customInp.focus();
    } else {
        customInp.style.display = 'none';
        customInp.required = false;
        customInp.value = '';
    }
}

function addItemRow() {
    const tbody = document.getElementById('itemsTbody');
    const tr = document.createElement('tr');
    tr.className = 'item-row';
    tr.dataset.rowIndex = rowIndex;
    tr.style.borderBottom = '1px solid #e2e8f0';

    const unitId = getCurrentUnitId();
    const assetOptions = getAssetOptionsHtml(unitId);
    const costBurdenOptions = getCostBurdenOptionsHtml(unitId);

    tr.innerHTML = `
        <td class="row-number" style="text-align: center; font-weight: 700; color: #64748b; padding: 10px 4px;">${tbody.children.length + 1}</td>
        <td style="padding: 10px 6px;">
            <div style="margin-bottom: 5px;">
                <select class="form-control asset-select" onchange="onAssetChosen(this)" style="font-size: 11px; padding: 4px 6px; background: #f0fdfa; border: 1px solid #99f6e4; color: #0f766e; font-weight: 600; border-radius: 6px;">
                    ${assetOptions}
                </select>
            </div>
            <input type="text" name="description[${rowIndex}]" class="form-control item-desc-input" placeholder="Nama item / merk *" required style="font-size: 13px;">
        </td>
        <td style="padding: 10px 6px;">
            <input type="text" name="specs[${rowIndex}]" class="form-control" placeholder="Size / Spek" style="font-size: 13px;">
        </td>
        <td style="padding: 10px 6px;">
            <select name="rapbs[${rowIndex}]" class="form-control" style="font-size: 12px; padding: 6px 4px;">
                <option value="YA" selected>YA</option>
                <option value="TIDAK">TIDAK</option>
            </select>
        </td>
        <td style="padding: 10px 6px;">
            <input type="text" name="unit_of_measure[${rowIndex}]" class="form-control" value="unit" placeholder="unit/pcs" style="font-size: 12px;">
        </td>
        <td style="padding: 10px 6px;">
            <input type="number" name="stock_position[${rowIndex}]" class="form-control stock-pos-input" value="0" min="0" style="font-size: 12px; text-align: center; font-weight: 700;">
            <div class="stock-info-label" style="font-size: 10px; color: #0d9488; text-align: center; margin-top: 3px; font-weight: 600;"></div>
        </td>
        <td style="padding: 10px 6px;">
            <input type="number" name="quantity_ordered[${rowIndex}]" class="form-control" value="1" min="1" required style="font-size: 13px; font-weight: 700; text-align: center;">
        </td>
        <td style="padding: 10px 6px;">
            <input type="text" name="procurement_time[${rowIndex}]" class="form-control" value="<?= date('d F Y', strtotime('+14 days')) ?>" style="font-size: 12px;">
        </td>
        <td style="padding: 10px 6px;">
            <select name="cost_burden_unit[${rowIndex}]" class="form-control cost-burden-select" onchange="onCostBurdenChange(this)" style="font-size: 12px; padding: 6px 4px;">
                ${costBurdenOptions}
            </select>
            <input type="text" name="custom_cost_burden[${rowIndex}]" class="form-control custom-cost-burden-input" placeholder="Ketik sub unit..." style="display: none; font-size: 12px; margin-top: 4px;">
        </td>
        <td style="padding: 10px 6px;">
            <select name="item_category_id[${rowIndex}]" class="form-control" style="font-size: 12px; margin-bottom: 4px;">
                ${categoryOptionsHtml}
            </select>
            <label style="font-size: 11px; display: flex; align-items: center; gap: 4px; color: #475569; cursor: pointer;">
                <input type="checkbox" name="is_consumable[${rowIndex}]" value="1">
                ATK / Pecah Belah
            </label>
        </td>
        <td style="padding: 10px 6px;">
            <input type="file" name="item_photo[${rowIndex}]" class="form-control" accept="image/*" style="font-size: 11px; padding: 4px 6px;">
        </td>
        <td style="text-align: center; padding: 10px 4px;">
            <button type="button" class="btn btn-sm btn-outline text-danger" onclick="removeRow(this)" title="Hapus baris">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        </td>
    `;

    tbody.appendChild(tr);
    rowIndex++;
    renumberRows();
}

function removeRow(btn) {
    const tbody = document.getElementById('itemsTbody');
    if (tbody.children.length <= 1) {
        alert('Minimal harus ada 1 item barang yang diminta.');
        return;
    }
    const tr = btn.closest('tr');
    tr.remove();
    renumberRows();
}

function renumberRows() {
    const rows = document.querySelectorAll('#itemsTbody .item-row');
    rows.forEach((r, idx) => {
        r.querySelector('.row-number').innerText = idx + 1;
    });
}

// Inisialisasi dropdown lokasi, beban, dan aset saat pertama kali form dimuat
document.addEventListener('DOMContentLoaded', function() {
    const uid = getCurrentUnitId();
    updateDeliveryLocations(uid);
    updateAllRowsForUnit(uid);
});
</script>
