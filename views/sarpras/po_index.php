<?php
// views/sarpras/po_index.php
use Core\SecurityHelper;

$isYayasan = $access['isYayasan'];
$role = $access['activeRole'];
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
    <div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <h1 class="page-title" style="margin: 0; font-size: 24px; font-weight: 800; color: #1e293b;">
                <i class="fa-solid fa-file-invoice" style="color: #0f766e; margin-right: 8px;"></i> Formulir Permintaan Barang (PO)
            </h1>
            <span class="badge" style="background: #ccfbf1; color: #0f766e; border: 1px solid #99f6e4; font-size: 13px; font-weight: 700; padding: 4px 10px;">
                Pengadaan & Belanja Sarpras
            </span>
        </div>
        <div class="page-subtitle" style="color: #64748b; font-size: 13px; margin-top: 4px;">
            Alur persetujuan bertingkat: Sarpras &rarr; Kepala Sekolah &rarr; Direktur Pengadaan (&rarr; Direktur Sarpras jika butuh kontraktor) &rarr; Realisasi & Surat Jalan.
        </div>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="<?= $baseUrl ?>/sarpras/assets" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-boxes-stacked"></i> Stok Aset
        </a>
        <?php if (in_array($role, ['sarpras', 'operator_yayasan'], true)): ?>
            <a href="<?= $baseUrl ?>/sarpras/po/create" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-plus"></i> Buat Formulir PO Baru
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- FILTER CARD -->
<div class="card" style="margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-radius: 10px;">
    <div class="card-body" style="padding: 16px 20px;">
        <form method="GET" action="<?= $baseUrl ?>/sarpras/po" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end;">
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
                <label class="form-label" style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; display: block;">Status Persetujuan</label>
                <select name="status" class="form-control" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="Menunggu_Kepsek" <?= ($statusFilter === 'Menunggu_Kepsek') ? 'selected' : '' ?>>Menunggu Kepala Sekolah</option>
                    <option value="Menunggu_Direktur_Pengadaan" <?= ($statusFilter === 'Menunggu_Direktur_Pengadaan') ? 'selected' : '' ?>>Menunggu Direktur Pengadaan</option>
                    <option value="Menunggu_Direktur_Sarpras" <?= ($statusFilter === 'Menunggu_Direktur_Sarpras') ? 'selected' : '' ?>>Menunggu Direktur Sarpras (Kontraktor)</option>
                    <option value="Disetujui" <?= ($statusFilter === 'Disetujui') ? 'selected' : '' ?>>Disetujui (Siap Realisasi)</option>
                    <option value="Selesai" <?= ($statusFilter === 'Selesai') ? 'selected' : '' ?>>Selesai & Auto-Stock</option>
                    <option value="Ditolak" <?= ($statusFilter === 'Ditolak') ? 'selected' : '' ?>>Ditolak</option>
                </select>
            </div>

            <div>
                <a href="<?= $baseUrl ?>/sarpras/po" class="btn btn-outline" style="padding: 8px 14px;">
                    <i class="fa-solid fa-arrows-rotate"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- ORDERS LIST -->
<div class="card" style="box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-radius: 12px;">
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table" style="width: 100%; margin: 0;">
                <thead>
                    <tr style="background: #f1f5f9; color: #475569; font-size: 12px; text-transform: uppercase;">
                        <th style="padding: 12px 18px;">No</th>
                        <th style="padding: 12px 18px;">No. Referensi PO</th>
                        <th style="padding: 12px 18px;">Tgl Pengajuan</th>
                        <th style="padding: 12px 18px;">Unit Sekolah</th>
                        <th style="padding: 12px 18px;">Alasan Permintaan</th>
                        <th style="padding: 12px 18px; text-align: center;">Item / Qty</th>
                        <th style="padding: 12px 18px;">Jenis Pengerjaan</th>
                        <th style="padding: 12px 18px;">Status Alur</th>
                        <th style="padding: 12px 18px; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 36px; color: #64748b;">
                                <div style="font-size: 15px; font-weight: 700; color: #334155;">Belum ada Formulir Permintaan Barang (PO)</div>
                                <div style="font-size: 13px; margin-top: 4px;">Sarpras dapat membuat pengajuan barang baru melalui tombol <strong>Buat Formulir PO Baru</strong>.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($orders as $o): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px 18px; font-weight: 600; color: #64748b;"><?= $no++ ?></td>
                                <td style="padding: 12px 18px;">
                                    <div style="font-weight: 700; color: #0f766e; font-family: monospace; font-size: 13px;">
                                        <?= SecurityHelper::escape($o['po_number']) ?>
                                    </div>
                                    <div style="font-size: 11px; color: #64748b;">Oleh: <?= SecurityHelper::escape($o['requested_by_name']) ?></div>
                                </td>
                                <td style="padding: 12px 18px; color: #475569; font-size: 13px;">
                                    <?= date('d/m/Y', strtotime($o['request_date'])) ?>
                                </td>
                                <td style="padding: 12px 18px;">
                                    <span class="badge badge-info" style="font-size: 11px;">
                                        <?= SecurityHelper::escape($o['unit_code'] ?? $o['unit_name']) ?>
                                    </span>
                                </td>
                                <td style="padding: 12px 18px; color: #334155; font-size: 13px; max-width: 250px;">
                                    <?= SecurityHelper::escape($o['reason']) ?>
                                </td>
                                <td style="padding: 12px 18px; text-align: center;">
                                    <span class="badge" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 12px; font-weight: 700; padding: 4px 8px;">
                                        <?= (int)$o['total_items'] ?> item (<?= (int)$o['total_ordered_qty'] ?> unit)
                                    </span>
                                </td>
                                <td style="padding: 12px 18px;">
                                    <?php if ($o['needs_contractor']): ?>
                                        <span class="badge" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-size: 11px; font-weight: 700;">
                                            <i class="fa-solid fa-person-digging me-1"></i> Butuh Kontraktor
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 11px;">
                                            Standar Toko
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 18px;">
                                    <?php if ($o['status'] === 'Menunggu_Kepsek'): ?>
                                        <span class="badge badge-warning" style="font-size: 11px;"><i class="fa-solid fa-hourglass-start me-1"></i> Menunggu Kepala Sekolah</span>
                                    <?php elseif ($o['status'] === 'Menunggu_Direktur_Pengadaan'): ?>
                                        <span class="badge" style="background: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; font-size: 11px;"><i class="fa-solid fa-user-clock me-1"></i> Menunggu Dir. Pengadaan</span>
                                    <?php elseif ($o['status'] === 'Menunggu_Direktur_Sarpras'): ?>
                                        <span class="badge" style="background: #fef08a; color: #854d0e; border: 1px solid #fde047; font-size: 11px;"><i class="fa-solid fa-helmet-safety me-1"></i> Menunggu Dir. Sarpras</span>
                                    <?php elseif ($o['status'] === 'Disetujui'): ?>
                                        <span class="badge badge-success" style="font-size: 11px;"><i class="fa-solid fa-circle-check me-1"></i> Disetujui (Siap Realisasi)</span>
                                    <?php elseif ($o['status'] === 'Selesai'): ?>
                                        <span class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-size: 11px;"><i class="fa-solid fa-boxes-packing me-1"></i> Selesai & Auto-Stock</span>
                                    <?php elseif ($o['status'] === 'Ditolak'): ?>
                                        <span class="badge badge-danger" style="font-size: 11px;"><i class="fa-solid fa-ban me-1"></i> Ditolak</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 18px; text-align: right;">
                                    <div style="display: inline-flex; gap: 6px;">
                                        <a href="<?= $baseUrl ?>/sarpras/po/detail/<?= $o['id'] ?>" class="btn btn-sm btn-outline" title="Lihat Detail & Persetujuan">
                                            <i class="fa-solid fa-eye"></i> Detail
                                        </a>
                                        <a href="<?= $baseUrl ?>/sarpras/po/print/<?= $o['id'] ?>" target="_blank" class="btn btn-sm btn-outline" style="border-color: #0f766e; color: #0f766e;" title="Cetak Formulir PO">
                                            <i class="fa-solid fa-print"></i> Cetak
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
