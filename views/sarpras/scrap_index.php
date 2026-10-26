<?php
// views/sarpras/scrap_index.php
use Core\SecurityHelper;

$role = $access['activeRole'];
$isYayasan = $access['isYayasan'];
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
    <div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <h1 class="page-title" style="margin: 0; font-size: 24px; font-weight: 800; color: #1e293b;">
                <i class="fa-solid fa-file-excel" style="color: #dc2626; margin-right: 8px;"></i> Berita Acara Pernyataan Barang Scrap (F30)
            </h1>
            <span class="badge" style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; font-size: 13px; font-weight: 700; padding: 4px 10px;">
                Form F30 Resmi
            </span>
        </div>
        <div class="page-subtitle" style="color: #64748b; font-size: 13px; margin-top: 4px;">
            Penghapusan aset rusak berat/afkir dari sistem. Barang yang tercantum pada berita acara otomatis dikeluarkan dan dihapus dari stok aktif.
        </div>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="<?= $baseUrl ?>/sarpras/assets?status=Scrapped" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-trash-can"></i> Aset yang Telah Di-Scrap
        </a>
        <?php if (in_array($role, ['sarpras', 'operator_yayasan', 'direktur_sarpras'], true)): ?>
            <a href="<?= $baseUrl ?>/sarpras/scrap/create" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px; background: #dc2626; border-color: #dc2626;">
                <i class="fa-solid fa-plus"></i> Buat Berita Acara Scrap Baru
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- FILTER CARD -->
<?php if ($isYayasan): ?>
    <div class="card" style="margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-radius: 10px;">
        <div class="card-body" style="padding: 16px 20px;">
            <form method="GET" action="<?= $baseUrl ?>/sarpras/scrap" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end;">
                <div style="flex: 1; min-width: 200px;">
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
                <div>
                    <a href="<?= $baseUrl ?>/sarpras/scrap" class="btn btn-outline" style="padding: 8px 14px;">
                        <i class="fa-solid fa-arrows-rotate"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<!-- SCRAP REPORTS TABLE -->
<div class="card" style="box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-radius: 12px;">
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table" style="width: 100%; margin: 0;">
                <thead>
                    <tr style="background: #f1f5f9; color: #475569; font-size: 12px; text-transform: uppercase;">
                        <th style="padding: 12px 18px;">No</th>
                        <th style="padding: 12px 18px;">Nomor Berita Acara</th>
                        <th style="padding: 12px 18px;">Hari & Tanggal</th>
                        <th style="padding: 12px 18px;">Unit Sekolah</th>
                        <th style="padding: 12px 18px; text-align: center;">Item Dikeluarkan</th>
                        <th style="padding: 12px 18px;">Sebab di Scrap</th>
                        <th style="padding: 12px 18px;">Disiapkan Oleh</th>
                        <th style="padding: 12px 18px;">Disetujui Oleh</th>
                        <th style="padding: 12px 18px; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reports)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 36px; color: #64748b;">
                                <div style="font-size: 15px; font-weight: 700; color: #334155;">Belum ada Berita Acara Scrap yang Diterbitkan</div>
                                <div style="font-size: 13px; margin-top: 4px;">Gunakan menu <strong>Buat Berita Acara Scrap Baru</strong> untuk menghapus barang rusak/afkir dari stok.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($reports as $r): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px 18px; font-weight: 600; color: #64748b;"><?= $no++ ?></td>
                                <td style="padding: 12px 18px;">
                                    <div style="font-weight: 700; color: #dc2626; font-family: monospace; font-size: 13px;">
                                        <?= SecurityHelper::escape($r['report_number']) ?>
                                    </div>
                                    <span class="badge" style="background: #fef2f2; color: #991b1b; font-size: 10px; padding: 2px 6px;">
                                        Form <?= SecurityHelper::escape($r['form_code']) ?>
                                    </span>
                                </td>
                                <td style="padding: 12px 18px; color: #475569; font-size: 13px;">
                                    <strong><?= SecurityHelper::escape($r['day_name']) ?></strong>, <?= date('d/m/Y', strtotime($r['report_date'])) ?>
                                </td>
                                <td style="padding: 12px 18px;">
                                    <span class="badge badge-info" style="font-size: 11px;">
                                        <?= SecurityHelper::escape($r['unit_code'] ?? $r['unit_name']) ?>
                                    </span>
                                </td>
                                <td style="padding: 12px 18px; text-align: center;">
                                    <span class="badge badge-danger" style="font-size: 12px; font-weight: 700; padding: 4px 10px;">
                                        <?= (int)$r['total_items'] ?> Jenis (<?= (int)$r['total_scrapped_qty'] ?> Unit)
                                    </span>
                                </td>
                                <td style="padding: 12px 18px; color: #334155; font-size: 13px; max-width: 220px;">
                                    <?= SecurityHelper::escape($r['reason']) ?>
                                </td>
                                <td style="padding: 12px 18px; font-size: 13px;">
                                    <?= SecurityHelper::escape($r['prepared_by_name']) ?>
                                </td>
                                <td style="padding: 12px 18px; font-size: 13px; font-weight: 600; color: #047857;">
                                    <?= SecurityHelper::escape($r['approved_by_name']) ?>
                                </td>
                                <td style="padding: 12px 18px; text-align: right;">
                                    <div style="display: inline-flex; gap: 6px;">
                                        <a href="<?= $baseUrl ?>/sarpras/scrap/detail/<?= $r['id'] ?>" class="btn btn-sm btn-outline" title="Lihat Detail">
                                            <i class="fa-solid fa-eye"></i> Detail
                                        </a>
                                        <a href="<?= $baseUrl ?>/sarpras/scrap/print/<?= $r['id'] ?>" target="_blank" class="btn btn-sm btn-outline" style="border-color: #dc2626; color: #dc2626;" title="Cetak Berita Acara F30">
                                            <i class="fa-solid fa-print"></i> Cetak F30
                                        </a>
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
