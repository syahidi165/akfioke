<?php
// views/sarpras/scrap_detail.php
use Core\SecurityHelper;
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <h1 class="page-title" style="margin: 0; font-size: 24px; font-weight: 800; color: #1e293b;">
                    <i class="fa-solid fa-file-excel" style="color: #dc2626; margin-right: 8px;"></i>
                    Berita Acara Scrap: <?= SecurityHelper::escape($report['report_number']) ?>
                </h1>
                <span class="badge" style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; font-size: 13px; font-weight: 700; padding: 4px 10px;">
                    Form <?= SecurityHelper::escape($report['form_code']) ?> (Disetujui & Dikeluarkan)
                </span>
            </div>
            <div class="page-subtitle" style="color: #64748b; font-size: 13px; margin-top: 4px;">
                Unit: <strong><?= SecurityHelper::escape($report['unit_name']) ?></strong> | Tanggal: <strong><?= SecurityHelper::escape($report['day_name']) ?>, <?= date('d F Y', strtotime($report['report_date'])) ?></strong> | Diterbitkan sah oleh Sarpras
            </div>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="<?= $baseUrl ?>/sarpras/scrap" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
            <a href="<?= $baseUrl ?>/sarpras/scrap/print/<?= $report['id'] ?>" target="_blank" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px; background: #dc2626; border-color: #dc2626;">
                <i class="fa-solid fa-print"></i> Cetak Berita Acara Resmi (F30)
            </a>
        </div>
    </div>
</div>

<!-- STATUS BOX -->
<div class="card" style="border: 1px solid #fecaca; background: #fef2f2; border-radius: 12px; margin-bottom: 24px;">
    <div class="card-body" style="padding: 18px 20px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 44px; height: 44px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                <i class="fa-solid fa-trash-can"></i>
            </div>
            <div>
                <div style="font-weight: 700; font-size: 15px; color: #991b1b;">
                    Barang Telah Dikeluarkan dari Sistem & Dihapus dari Stok Aktif
                </div>
                <div style="font-size: 13px; color: #b91c1c; margin-top: 2px;">
                    Sesuai Berita Acara ini, sistem telah mengubah status aset terkait menjadi <strong>Scrapped</strong> (Afkir/Scrap).
                </div>
            </div>
        </div>
    </div>
</div>

<!-- INFORMASI RINCIAN SCRAP -->
<div class="card" style="margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-radius: 12px;">
    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 20px;">
        <h3 class="card-title" style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-list-check" style="color: #dc2626;"></i> Daftar Barang yang Dinyatakan Scrap (<?= count($items) ?> Item)
        </h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table" style="width: 100%; margin: 0;">
                <thead>
                    <tr style="background: #dc2626; color: #ffffff; font-size: 12px; text-transform: uppercase;">
                        <th style="padding: 12px 16px; text-align: center; width: 50px;">No</th>
                        <th style="padding: 12px 16px;">Nama Item / Deskripsi</th>
                        <th style="padding: 12px 16px; text-align: center; width: 100px;">Jumlah</th>
                        <th style="padding: 12px 16px;">Penanggung Jawab</th>
                        <th style="padding: 12px 16px; text-align: center; width: 160px;">Foto Fisik Scrap</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($items as $item): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 14px 16px; text-align: center; font-weight: 700; color: #64748b;"><?= $no++ ?></td>
                            <td style="padding: 14px 16px;">
                                <div style="font-weight: 800; font-size: 14px; color: #0f172a;">
                                    <?= SecurityHelper::escape($item['item_name']) ?>
                                </div>
                                <?php if (!empty($item['asset_code'])): ?>
                                    <div style="font-size: 11px; color: #64748b; font-family: monospace; margin-top: 2px;">
                                        No Aset Sistem: <?= SecurityHelper::escape($item['asset_code']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 14px 16px; text-align: center; font-weight: 800; font-size: 15px; color: #dc2626;">
                                <?= (int)$item['quantity'] ?>
                            </td>
                            <td style="padding: 14px 16px; font-weight: 600; color: #334155; font-size: 13px;">
                                <?= SecurityHelper::escape($item['responsible_person']) ?>
                            </td>
                            <td style="padding: 14px 16px; text-align: center;">
                                <?php if (!empty($item['photo'])): ?>
                                    <a href="<?= $baseUrl ?>/sarpras/file/view?file=<?= urlencode($item['photo']) ?>" target="_blank" style="display: inline-block;">
                                        <img src="<?= $baseUrl ?>/sarpras/file/view?file=<?= urlencode($item['photo']) ?>" alt="Foto Scrap" style="width: 70px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size: 12px;">Tanpa Foto</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- SEBAB DI SCRAP & PENANDATANGAN -->
<div class="card" style="box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 24px;">
    <div class="card-body" style="padding: 20px;">
        <div style="margin-bottom: 20px;">
            <div style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">
                Sebab di scrap :
            </div>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; font-size: 13px; color: #334155; line-height: 1.5;">
                <?= nl2br(SecurityHelper::escape($report['reason'])) ?>
            </div>
        </div>

        <div class="grid-cols-3" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
            <div style="background: #f8fafc; padding: 14px; border: 1px solid #e2e8f0; border-radius: 8px;">
                <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b;">Diambil Oleh</div>
                <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-top: 4px;">
                    <?= SecurityHelper::escape($report['taken_by_name'] ?: 'Pihak Kebersihan / Pengepul') ?>
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                    Tgl: <?= !empty($report['taken_by_date']) ? date('d/m/Y', strtotime($report['taken_by_date'])) : '-' ?>
                </div>
            </div>

            <div style="background: #f8fafc; padding: 14px; border: 1px solid #e2e8f0; border-radius: 8px;">
                <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b;">Disiapkan Oleh</div>
                <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-top: 4px;">
                    <?= SecurityHelper::escape($report['prepared_by_name']) ?>
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                    Jabatan: <?= SecurityHelper::escape($report['prepared_by_title']) ?>
                </div>
            </div>

            <div style="background: #f8fafc; padding: 14px; border: 1px solid #e2e8f0; border-radius: 8px;">
                <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #047857;">Disetujui Oleh</div>
                <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-top: 4px;">
                    <?= SecurityHelper::escape($report['approved_by_name']) ?>
                </div>
                <div style="font-size: 11px; color: #047857; margin-top: 2px;">
                    Jabatan: <?= SecurityHelper::escape($report['approved_by_title']) ?>
                </div>
            </div>
        </div>
    </div>
</div>
