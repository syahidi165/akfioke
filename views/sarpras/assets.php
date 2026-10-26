<?php
// views/sarpras/assets.php
use Core\SecurityHelper;

$isYayasan = $access['isYayasan'];
$canManage = in_array($access['activeRole'], ['sarpras', 'operator_yayasan', 'direktur_sarpras'], true);
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
    <div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <h1 class="page-title" style="margin: 0; font-size: 24px; font-weight: 800; color: #1e293b;">
                <i class="fa-solid fa-boxes-stacked" style="color: #0f766e; margin-right: 8px;"></i> Inventaris & Aset Sekolah
            </h1>
            <span class="badge" style="background: #ccfbf1; color: #0f766e; border: 1px solid #99f6e4; font-size: 13px; font-weight: 700; padding: 4px 10px;">
                Total: <?= count($assets) ?> Aset
            </span>
        </div>
        <div class="page-subtitle" style="color: #64748b; font-size: 13px; margin-top: 4px;">
            Pendataan, pengelolaan kondisi, pemegang aset guru/karyawan, serta riwayat stok sarana prasarana unit sekolah.
        </div>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="<?= $baseUrl ?>/sarpras/categories" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-tags"></i> Kelola Kategori
        </a>
        <a href="<?= $baseUrl ?>/sarpras/assets/export?unit_id=<?= $selectedUnit ?? '' ?>" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-file-excel" style="color: #16a34a;"></i> Ekspor Excel
        </a>
        <?php if ($canManage): ?>
            <button type="button" class="btn btn-outline" onclick="document.getElementById('importModal').style.display='flex'" style="display: inline-flex; align-items: center; gap: 6px; border-color: #0f766e; color: #0f766e;">
                <i class="fa-solid fa-file-import"></i> Import by Excel
            </button>
            <button type="button" class="btn btn-primary" onclick="openAddAssetModal()" style="display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-plus"></i> Tambah Aset Manual
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- FILTER CARD -->
<div class="card" style="margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-radius: 10px;">
    <div class="card-body" style="padding: 16px 20px;">
        <form method="GET" action="<?= $baseUrl ?>/sarpras/assets" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end;">
            <?php if ($isYayasan): ?>
                <div style="flex: 1; min-width: 180px;">
                    <label class="form-label" style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; display: block;">Unit Sekolah</label>
                    <select name="unit_id" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua Unit Sekolah</option>
                        <?php foreach ($units as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= ((string)$selectedUnit === (string)$u['id']) ? 'selected' : '' ?>>
                                <?= SecurityHelper::escape($u['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div style="flex: 1; min-width: 180px;">
                <label class="form-label" style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; display: block;">Kategori Aset</label>
                <select name="category_id" class="form-control" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ((string)$selectedCat === (string)$cat['id']) ? 'selected' : '' ?>>
                            <?= SecurityHelper::escape($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="flex: 1; min-width: 150px;">
                <label class="form-label" style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; display: block;">Kondisi Barang</label>
                <select name="condition" class="form-control" onchange="this.form.submit()">
                    <option value="">Semua Kondisi</option>
                    <option value="Baik" <?= ($selectedCond === 'Baik') ? 'selected' : '' ?>>Baik</option>
                    <option value="Rusak Ringan" <?= ($selectedCond === 'Rusak Ringan') ? 'selected' : '' ?>>Rusak Ringan</option>
                    <option value="Rusak Berat" <?= ($selectedCond === 'Rusak Berat') ? 'selected' : '' ?>>Rusak Berat</option>
                    <option value="Afkir/Scrap" <?= ($selectedCond === 'Afkir/Scrap') ? 'selected' : '' ?>>Afkir/Scrap</option>
                </select>
            </div>

            <div style="flex: 1; min-width: 140px;">
                <label class="form-label" style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; display: block;">Status Aset</label>
                <select name="status" class="form-control" onchange="this.form.submit()">
                    <option value="Aktif" <?= ($selectedStatus === 'Aktif') ? 'selected' : '' ?>>Aset Aktif</option>
                    <option value="Scrapped" <?= ($selectedStatus === 'Scrapped') ? 'selected' : '' ?>>Telah Di-Scrap</option>
                    <option value="" <?= ($selectedStatus === '') ? 'selected' : '' ?>>Semua Status</option>
                </select>
            </div>

            <div>
                <a href="<?= $baseUrl ?>/sarpras/assets" class="btn btn-outline" style="padding: 8px 14px;">
                    <i class="fa-solid fa-arrows-rotate"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- ASSETS TABLE CARD -->
<div class="card" style="box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-radius: 12px;">
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table" style="width: 100%; margin: 0;">
                <thead>
                    <tr style="background: #f1f5f9; color: #475569; font-size: 12px; text-transform: uppercase;">
                        <th style="padding: 12px 16px;">No</th>
                        <th style="padding: 12px 16px;">No Aset</th>
                        <th style="padding: 12px 16px;">Nama / Merk / Brand</th>
                        <th style="padding: 12px 16px;">Kategori</th>
                        <th style="padding: 12px 16px;">Tgl Beli</th>
                        <th style="padding: 12px 16px;">Unit</th>
                        <th style="padding: 12px 16px;">Kondisi</th>
                        <th style="padding: 12px 16px;">User / Pemegang</th>
                        <th style="padding: 12px 16px;">Lokasi / Penempatan</th>
                        <th style="padding: 12px 16px;">Foto</th>
                        <?php if ($canManage): ?>
                            <th style="padding: 12px 16px; text-align: right;">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($assets)): ?>
                        <tr>
                            <td colspan="11" style="text-align: center; padding: 36px; color: #64748b;">
                                <div style="font-size: 16px; font-weight: 700; color: #334155;">Tidak ada data aset inventaris</div>
                                <div style="font-size: 13px; margin-top: 4px;">Gunakan tombol <strong>Tambah Aset Manual</strong> atau <strong>Import by Excel</strong> untuk mendata aset.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($assets as $asset): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px 16px; font-weight: 600; color: #64748b;"><?= $no++ ?></td>
                                <td style="padding: 12px 16px;">
                                    <span class="badge" style="background: #f1f5f9; color: #1e293b; border: 1px solid #cbd5e1; font-family: monospace; font-size: 12px;">
                                        <?= SecurityHelper::escape($asset['asset_code']) ?>
                                    </span>
                                    <?php if ($asset['is_consumable']): ?>
                                        <div style="margin-top: 2px;">
                                            <span class="badge" style="background: #e0e7ff; color: #3730a3; font-size: 10px; padding: 2px 6px;">
                                                ATK (Habis Pakai)
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 16px; font-weight: 700; color: #0f172a;">
                                    <?= SecurityHelper::escape($asset['name']) ?>
                                    <?php if ($asset['quantity'] > 1): ?>
                                        <span class="badge badge-secondary" style="font-size: 11px; margin-left: 4px;">
                                            Qty: <?= (int)$asset['quantity'] ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 16px; color: #64748b; font-size: 13px;">
                                    <?= SecurityHelper::escape($asset['category_name'] ?? 'Umum') ?>
                                </td>
                                <td style="padding: 12px 16px; color: #64748b; font-size: 13px;">
                                    <?= !empty($asset['purchase_date']) ? date('d/m/Y', strtotime($asset['purchase_date'])) : '-' ?>
                                </td>
                                <td style="padding: 12px 16px;">
                                    <span class="badge badge-info" style="font-size: 11px;">
                                        <?= SecurityHelper::escape($asset['unit_code'] ?? $asset['unit_name']) ?>
                                    </span>
                                </td>
                                <td style="padding: 12px 16px;">
                                    <?php if ($asset['condition_status'] === 'Baik'): ?>
                                        <span class="badge badge-success"><i class="fa-solid fa-check"></i> Baik</span>
                                    <?php elseif ($asset['condition_status'] === 'Rusak Ringan'): ?>
                                        <span class="badge badge-warning"><i class="fa-solid fa-triangle-exclamation"></i> Rusak Ringan</span>
                                    <?php elseif ($asset['condition_status'] === 'Rusak Berat'): ?>
                                        <span class="badge badge-danger"><i class="fa-solid fa-xmark"></i> Rusak Berat</span>
                                    <?php else: ?>
                                        <span class="badge" style="background: #475569; color: #ffffff;"><i class="fa-solid fa-trash-can"></i> <?= SecurityHelper::escape($asset['condition_status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 16px;">
                                    <div style="font-weight: 600; color: #0f172a; font-size: 13px;">
                                        <?= SecurityHelper::escape($asset['employee_holder_name'] ?: ($asset['user_holder_name'] ?: '-')) ?>
                                    </div>
                                    <?php if (!empty($asset['employee_holder_nipy'])): ?>
                                        <div style="font-size: 11px; color: #64748b;">NIPY: <?= SecurityHelper::escape($asset['employee_holder_nipy']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 16px; color: #475569; font-size: 13px;">
                                    <i class="fa-solid fa-location-dot text-muted me-1"></i>
                                    <?= SecurityHelper::escape($asset['location'] ?: 'Sarpras / Gudang') ?>
                                </td>
                                <td style="padding: 12px 16px;">
                                    <?php if (!empty($asset['photo'])): ?>
                                        <a href="<?= $baseUrl ?>/sarpras/file/view?file=<?= urlencode($asset['photo']) ?>" target="_blank" class="btn btn-sm btn-outline" style="padding: 2px 8px; font-size: 11px;" title="Lihat Foto Aset">
                                            <i class="fa-regular fa-image"></i> Foto
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size: 12px;">-</span>
                                    <?php endif; ?>
                                </td>
                                <?php if ($canManage): ?>
                                    <td style="padding: 12px 16px; text-align: right;">
                                        <div style="display: inline-flex; gap: 4px;">
                                            <button type="button" class="btn btn-sm btn-outline" 
                                                    onclick='openEditAssetModal(<?= json_encode($asset) ?>)'
                                                    title="Edit Aset">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <form method="POST" action="<?= $baseUrl ?>/sarpras/assets/delete" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aset <?= SecurityHelper::escape($asset['name']) ?> (<?= SecurityHelper::escape($asset['asset_code']) ?>) dari sistem?');" style="margin: 0;">
                                                <?= $csrfField ?>
                                                <input type="hidden" name="id" value="<?= $asset['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus Aset">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH & EDIT ASET -->
<div id="assetModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #ffffff; border-radius: 14px; width: 100%; max-width: 680px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; margin: 16px;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <h3 id="assetModalTitle" style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">
                Tambah Aset Sekolah Baru
            </h3>
            <button type="button" onclick="document.getElementById('assetModal').style.display='none'" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>
        <form method="POST" action="<?= $baseUrl ?>/sarpras/assets/save" enctype="multipart/form-data" style="overflow-y: auto; flex: 1;">
            <?= $csrfField ?>
            <input type="hidden" name="id" id="assetId" value="0">
            <div style="padding: 20px;">
                <div class="grid-cols-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                            No. Aset (Kosongkan untuk Otomatis)
                        </label>
                        <input type="text" name="asset_code" id="assetCode" class="form-control" placeholder="Otomatis: AST/TK/2026/001">
                    </div>
                    <div>
                        <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                            Nama / Merk / Brand <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" id="assetName" class="form-control" placeholder="Contoh: Printer Epson L1210, Meja Guru Olimpic" required>
                    </div>
                </div>

                <div class="grid-cols-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                            Kategori Aset <span class="text-danger">*</span>
                        </label>
                        <select name="category_id" id="assetCategory" class="form-control" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= SecurityHelper::escape($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                            Tanggal Pembelian <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="purchase_date" id="assetDate" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div class="grid-cols-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <?php if ($isYayasan): ?>
                        <div>
                            <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                                Unit Satuan Pendidikan <span class="text-danger">*</span>
                            </label>
                            <select name="unit_id" id="assetUnit" class="form-control" required>
                                <?php foreach ($units as $u): ?>
                                    <option value="<?= $u['id'] ?>" <?= ((string)$selectedUnit === (string)$u['id']) ? 'selected' : '' ?>>
                                        <?= SecurityHelper::escape($u['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div>
                        <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                            Kondisi Barang <span class="text-danger">*</span>
                        </label>
                        <select name="condition_status" id="assetCondition" class="form-control" required>
                            <option value="Baik">Baik</option>
                            <option value="Rusak Ringan">Rusak Ringan</option>
                            <option value="Rusak Berat">Rusak Berat</option>
                            <option value="Afkir/Scrap">Afkir/Scrap</option>
                        </select>
                    </div>
                </div>

                <div class="grid-cols-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                            User / Pemegang Aset (Pegawai)
                        </label>
                        <select name="user_holder_id" id="assetHolderId" class="form-control">
                            <option value="">-- Tanpa Pemegang Khusus (Sarpras/Umum) --</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?= $emp['id'] ?>">
                                    <?= SecurityHelper::escape($emp['nama']) ?> (<?= SecurityHelper::escape($emp['nipy']) ?> - <?= SecurityHelper::escape($emp['level_posisi']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                            Atau Tuliskan Nama Pemegang Lain
                        </label>
                        <input type="text" name="user_holder_name" id="assetHolderName" class="form-control" placeholder="Contoh: Kantor KS / Guru Piket">
                    </div>
                </div>

                <div class="grid-cols-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                            Lokasi / Penempatan Fisik
                        </label>
                        <input type="text" name="location" id="assetLocation" class="form-control" placeholder="Contoh: Ruang Guru TK, Lab Komputer, Kantor KS">
                    </div>
                    <div>
                        <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                            Harga / Nilai Perolehan (Rp)
                        </label>
                        <input type="number" name="price" id="assetPrice" class="form-control" placeholder="0" min="0">
                    </div>
                </div>

                <div class="grid-cols-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                            Jumlah (Quantity / Stok)
                        </label>
                        <input type="number" name="quantity" id="assetQuantity" class="form-control" value="1" min="1">
                    </div>
                    <div>
                        <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                            Foto Aset (Opsional)
                        </label>
                        <input type="file" name="photo" class="form-control" accept="image/*">
                    </div>
                </div>

                <div style="background: #f8fafc; border-radius: 8px; padding: 12px; border: 1px solid #e2e8f0; margin-bottom: 14px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px; font-weight: 600; color: #334155;">
                        <input type="checkbox" name="is_consumable" id="assetConsumable" value="1" onchange="toggleConsumable(this.checked)">
                        Barang Pecah Belah / ATK (Habis Pakai)
                    </label>
                    <div id="consumableBox" style="display: none; margin-top: 8px; padding-top: 8px; border-top: 1px solid #e2e8f0;">
                        <label class="form-label" style="font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 4px; display: block;">Status Stok ATK</label>
                        <select name="stock_status" id="assetStockStatus" class="form-control" style="font-size: 13px;">
                            <option value="Ready">Ready (Tersedia)</option>
                            <option value="Habis Stock">Habis Stock</option>
                        </select>
                        <small style="color: #64748b; font-size: 11px;">Barang pecah belah seperti ATK tidak perlu diberi nomor seri individual, cukup status ketersediaan stok.</small>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                        Catatan Khusus / Spesifikasi Tambahan
                    </label>
                    <textarea name="notes" id="assetNotes" class="form-control" rows="2" placeholder="Catatan serial number, garansi, kondisi fisik..."></textarea>
                </div>
            </div>
            <div style="padding: 14px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('assetModal').style.display='none'">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Aset</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL IMPORT EXCEL -->
<div id="importModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #ffffff; border-radius: 14px; width: 100%; max-width: 520px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; margin: 16px;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">
                <i class="fa-solid fa-file-import" style="color: #0f766e; margin-right: 6px;"></i> Import Data Aset by Excel
            </h3>
            <button type="button" onclick="document.getElementById('importModal').style.display='none'" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>
        <form method="POST" action="<?= $baseUrl ?>/sarpras/assets/import" enctype="multipart/form-data">
            <?= $csrfField ?>
            <div style="padding: 20px;">
                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px; margin-bottom: 16px;">
                    <div style="font-weight: 700; color: #1e40af; font-size: 13px; margin-bottom: 4px;">
                        <i class="fa-solid fa-circle-info me-1"></i> Gunakan Template Resmi
                    </div>
                    <div style="font-size: 12px; color: #1e3a8a; line-height: 1.4;">
                        Untuk menghindari salah format kolom, unduh template resmi Excel terlebih dahulu lalu isi data aset Anda.
                    </div>
                    <div style="margin-top: 10px;">
                        <a href="<?= $baseUrl ?>/sarpras/assets/template" class="btn btn-sm btn-outline" style="background: #ffffff; border-color: #3b82f6; color: #1d4ed8; font-weight: 700;">
                            <i class="fa-solid fa-download me-1"></i> Download Template Excel Aset (.XLSX)
                        </a>
                    </div>
                </div>

                <?php if ($isYayasan): ?>
                    <div class="form-group" style="margin-bottom: 14px;">
                        <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                            Target Unit Sekolah <span class="text-danger">*</span>
                        </label>
                        <select name="unit_id" class="form-control" required>
                            <?php foreach ($units as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= ((string)$selectedUnit === (string)$u['id']) ? 'selected' : '' ?>>
                                    <?= SecurityHelper::escape($u['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="form-group" style="margin-bottom: 8px;">
                    <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 6px; display: block;">
                        Unggah Berkas Excel (.xlsx / .xls) <span class="text-danger">*</span>
                    </label>
                    <input type="file" name="excel_file" class="form-control" accept=".xlsx, .xls" required>
                </div>
            </div>
            <div style="padding: 14px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('importModal').style.display='none'">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-cloud-arrow-up me-1"></i> Mulai Import Data
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddAssetModal() {
    document.getElementById('assetModalTitle').innerText = 'Tambah Aset Sekolah Baru';
    document.getElementById('assetId').value = '0';
    document.getElementById('assetCode').value = '';
    document.getElementById('assetName').value = '';
    document.getElementById('assetCategory').value = '';
    document.getElementById('assetDate').value = '<?= date('Y-m-d') ?>';
    document.getElementById('assetCondition').value = 'Baik';
    document.getElementById('assetHolderId').value = '';
    document.getElementById('assetHolderName').value = '';
    document.getElementById('assetLocation').value = '';
    document.getElementById('assetPrice').value = '';
    document.getElementById('assetQuantity').value = '1';
    document.getElementById('assetConsumable').checked = false;
    document.getElementById('assetNotes').value = '';
    toggleConsumable(false);
    document.getElementById('assetModal').style.display = 'flex';
}

function openEditAssetModal(asset) {
    document.getElementById('assetModalTitle').innerText = 'Edit Data Aset';
    document.getElementById('assetId').value = asset.id;
    document.getElementById('assetCode').value = asset.asset_code;
    document.getElementById('assetName').value = asset.name;
    document.getElementById('assetCategory').value = asset.category_id;
    document.getElementById('assetDate').value = asset.purchase_date;
    document.getElementById('assetCondition').value = asset.condition_status;
    document.getElementById('assetHolderId').value = asset.user_holder_id || '';
    document.getElementById('assetHolderName').value = asset.user_holder_name || '';
    document.getElementById('assetLocation').value = asset.location || '';
    document.getElementById('assetPrice').value = asset.price || 0;
    document.getElementById('assetQuantity').value = asset.quantity || 1;
    
    const isCons = parseInt(asset.is_consumable) === 1;
    document.getElementById('assetConsumable').checked = isCons;
    toggleConsumable(isCons);
    if (isCons && asset.stock_status) {
        document.getElementById('assetStockStatus').value = asset.stock_status;
    }
    
    document.getElementById('assetNotes').value = asset.notes || '';
    document.getElementById('assetModal').style.display = 'flex';
}

function toggleConsumable(checked) {
    document.getElementById('consumableBox').style.display = checked ? 'block' : 'none';
}
</script>
