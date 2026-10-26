<?php
// views/sarpras/categories.php
use Core\SecurityHelper;
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
    <div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <h1 class="page-title" style="margin: 0; font-size: 24px; font-weight: 800; color: #1e293b;">
                <i class="fa-solid fa-tags" style="color: #0f766e; margin-right: 8px;"></i> Master Kategori Aset
            </h1>
            <span class="badge" style="background: #ccfbf1; color: #0f766e; border: 1px solid #99f6e4; font-size: 13px; font-weight: 700; padding: 4px 10px;">
                Sarpras Inventaris
            </span>
        </div>
        <div class="page-subtitle" style="color: #64748b; font-size: 13px; margin-top: 4px;">
            Kelola kategori inventaris sekolah agar muncul otomatis pada menu dropdown input aset dan formulir permintaan barang.
        </div>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="<?= $baseUrl ?>/sarpras/assets" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-boxes-stacked"></i> Kembali ke Daftar Aset
        </a>
        <button type="button" class="btn btn-primary" onclick="openAddCatModal()" style="display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-plus"></i> Tambah Kategori Baru
        </button>
    </div>
</div>

<div class="card" style="box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-radius: 12px;">
    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title" style="margin: 0; font-size: 15px; font-weight: 700; color: #0f172a;">
            Daftar Kategori Terdaftar (<?= count($categories) ?> Kategori)
        </h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table" style="width: 100%; margin: 0;">
                <thead>
                    <tr style="background: #f1f5f9; color: #475569; font-size: 12px; text-transform: uppercase;">
                        <th style="padding: 12px 18px; width: 60px;">No</th>
                        <th style="padding: 12px 18px;">Nama Kategori</th>
                        <th style="padding: 12px 18px;">Deskripsi / Cakupan</th>
                        <th style="padding: 12px 18px; text-align: center; width: 140px;">Jumlah Aset Aktif</th>
                        <th style="padding: 12px 18px; text-align: right; width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px; color: #64748b;">
                                Belum ada kategori aset. Silakan tambahkan kategori baru terlebih dahulu.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($categories as $cat): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px 18px; font-weight: 600; color: #64748b;"><?= $no++ ?></td>
                                <td style="padding: 12px 18px; font-weight: 700; color: #0f172a;">
                                    <i class="fa-solid fa-folder text-teal me-2" style="color: #0d9488;"></i>
                                    <?= SecurityHelper::escape($cat['name']) ?>
                                </td>
                                <td style="padding: 12px 18px; color: #64748b; font-size: 13px;">
                                    <?= SecurityHelper::escape($cat['description'] ?: '-') ?>
                                </td>
                                <td style="padding: 12px 18px; text-align: center;">
                                    <span class="badge" style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-size: 12px; font-weight: 700; padding: 4px 10px;">
                                        <?= (int)$cat['total_assets'] ?> Aset
                                    </span>
                                </td>
                                <td style="padding: 12px 18px; text-align: right;">
                                    <div style="display: inline-flex; gap: 6px;">
                                        <button type="button" class="btn btn-sm btn-outline" 
                                                onclick='openEditCatModal(<?= json_encode($cat) ?>)'
                                                title="Edit Kategori">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <?php if ((int)$cat['total_assets'] === 0): ?>
                                            <form method="POST" action="<?= $baseUrl ?>/sarpras/categories/delete" onsubmit="return confirm('Hapus kategori <?= SecurityHelper::escape($cat['name']) ?>?');" style="margin: 0;">
                                                <?= $csrfField ?>
                                                <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus Kategori">
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

<!-- MODAL TAMBAH / EDIT KATEGORI -->
<div id="catModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; margin: 16px;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <h3 id="catModalTitle" style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">
                Tambah Kategori Aset
            </h3>
            <button type="button" onclick="document.getElementById('catModal').style.display='none'" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>
        <form method="POST" action="<?= $baseUrl ?>/sarpras/categories/save">
            <?= $csrfField ?>
            <input type="hidden" name="id" id="catId" value="0">
            <div style="padding: 20px;">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px; display: block;">
                        Nama Kategori <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="name" id="catName" class="form-control" placeholder="Contoh: Elektronik & IT, Mebel/Furniture" required>
                    <small style="color: #64748b; font-size: 11px;">Nama kategori akan ditampilkan pada dropdown pilihan saat mendata aset.</small>
                </div>
                <div class="form-group" style="margin-bottom: 8px;">
                    <label class="form-label" style="font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px; display: block;">
                        Deskripsi / Cakupan Barang
                    </label>
                    <textarea name="description" id="catDesc" class="form-control" rows="3" placeholder="Contoh: Laptop, printer, meja kerja, perlengkapan KBM..."></textarea>
                </div>
            </div>
            <div style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('catModal').style.display='none'">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Kategori</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddCatModal() {
    document.getElementById('catModalTitle').innerText = 'Tambah Kategori Aset Baru';
    document.getElementById('catId').value = '0';
    document.getElementById('catName').value = '';
    document.getElementById('catDesc').value = '';
    document.getElementById('catModal').style.display = 'flex';
}

function openEditCatModal(cat) {
    document.getElementById('catModalTitle').innerText = 'Edit Kategori Aset';
    document.getElementById('catId').value = cat.id;
    document.getElementById('catName').value = cat.name;
    document.getElementById('catDesc').value = cat.description || '';
    document.getElementById('catModal').style.display = 'flex';
}
</script>
