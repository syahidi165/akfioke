<?php
// views/sarpras/po_detail.php
use Core\SecurityHelper;

$role = $access['activeRole'];
$status = $po['status'];
$needsContractor = (int)$po['needs_contractor'];
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <h1 class="page-title" style="margin: 0; font-size: 24px; font-weight: 800; color: #1e293b;">
                    <i class="fa-solid fa-file-invoice" style="color: #0f766e; margin-right: 8px;"></i>
                    PO: <?= SecurityHelper::escape($po['po_number']) ?>
                </h1>
                <?php if ($status === 'Menunggu_Kepsek'): ?>
                    <span class="badge badge-warning" style="font-size: 13px; font-weight: 700; padding: 4px 10px;">
                        <i class="fa-solid fa-hourglass-start me-1"></i> Menunggu Kepala Sekolah
                    </span>
                <?php elseif ($status === 'Menunggu_Direktur_Pengadaan'): ?>
                    <span class="badge" style="background: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; font-size: 13px; font-weight: 700; padding: 4px 10px;">
                        <i class="fa-solid fa-user-clock me-1"></i> Menunggu Direktur Pengadaan
                    </span>
                <?php elseif ($status === 'Menunggu_Direktur_Sarpras'): ?>
                    <span class="badge" style="background: #fef08a; color: #854d0e; border: 1px solid #fde047; font-size: 13px; font-weight: 700; padding: 4px 10px;">
                        <i class="fa-solid fa-helmet-safety me-1"></i> Menunggu Direktur Sarpras (Kontraktor)
                    </span>
                <?php elseif ($status === 'Disetujui'): ?>
                    <span class="badge badge-success" style="font-size: 13px; font-weight: 700; padding: 4px 10px;">
                        <i class="fa-solid fa-circle-check me-1"></i> Disetujui (Siap Realisasi)
                    </span>
                <?php elseif ($status === 'Selesai'): ?>
                    <span class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-size: 13px; font-weight: 700; padding: 4px 10px;">
                        <i class="fa-solid fa-boxes-packing me-1"></i> Selesai & Auto-Stocked
                    </span>
                <?php elseif ($status === 'Ditolak'): ?>
                    <span class="badge badge-danger" style="font-size: 13px; font-weight: 700; padding: 4px 10px;">
                        <i class="fa-solid fa-ban me-1"></i> Ditolak
                    </span>
                <?php endif; ?>
            </div>
            <div class="page-subtitle" style="color: #64748b; font-size: 13px; margin-top: 4px;">
                Unit: <strong><?= SecurityHelper::escape($po['unit_name']) ?></strong> | Tanggal Pengajuan: <strong><?= date('d F Y', strtotime($po['request_date'])) ?></strong> | Oleh: <strong><?= SecurityHelper::escape($po['requested_by_name']) ?></strong>
            </div>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="<?= $baseUrl ?>/sarpras/po" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
            <a href="<?= $baseUrl ?>/sarpras/po/print/<?= $po['id'] ?>" target="_blank" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-print"></i> Cetak Formulir Resmi PO
            </a>
        </div>
    </div>
</div>

<!-- TIMELINE TRACKER TIMESTAMP ALUR -->
<div class="card" style="margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-radius: 12px;">
    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 14px 20px;">
        <h3 class="card-title" style="margin: 0; font-size: 15px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-timeline" style="color: #0f766e;"></i> Timeline & Timestamp Alur Pengadaan
        </h3>
    </div>
    <div class="card-body" style="padding: 24px 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
            <!-- Step 1: Terbit PO -->
            <div style="padding: 14px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px;">
                <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #166534; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-circle-check"></i> 1. Terbit PO (Sarpras)
                </div>
                <div style="font-size: 13px; font-weight: 700; color: #0f172a; margin-top: 6px;">
                    <?= SecurityHelper::escape($po['requested_by_name']) ?>
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                    <i class="fa-solid fa-clock me-1"></i> <?= date('d/m/Y H:i', strtotime($po['requested_at'])) ?> WIB
                </div>
            </div>

            <!-- Step 2: Kepala Sekolah -->
            <?php $isKsApproved = !empty($po['approved_kepsek_at']); ?>
            <div style="padding: 14px; background: <?= $isKsApproved ? '#f0fdf4' : '#f8fafc' ?>; border: 1px solid <?= $isKsApproved ? '#bbf7d0' : '#e2e8f0' ?>; border-radius: 10px;">
                <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: <?= $isKsApproved ? '#166534' : '#64748b' ?>; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid <?= $isKsApproved ? 'fa-circle-check' : 'fa-hourglass-half' ?>"></i> 2. Kepala Sekolah
                </div>
                <div style="font-size: 13px; font-weight: 700; color: #0f172a; margin-top: 6px;">
                    <?= $isKsApproved ? SecurityHelper::escape($po['approved_kepsek_name']) : 'Menunggu Persetujuan' ?>
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                    <?php if ($isKsApproved): ?>
                        <i class="fa-solid fa-clock me-1"></i> <?= date('d/m/Y H:i', strtotime($po['approved_kepsek_at'])) ?> WIB
                    <?php else: ?>
                        Belum disetujui
                    <?php endif; ?>
                </div>
            </div>

            <!-- Step 3: Direktur Pengadaan -->
            <?php $isPengadaanApproved = !empty($po['approved_pengadaan_at']); ?>
            <div style="padding: 14px; background: <?= $isPengadaanApproved ? '#f0fdf4' : '#f8fafc' ?>; border: 1px solid <?= $isPengadaanApproved ? '#bbf7d0' : '#e2e8f0' ?>; border-radius: 10px;">
                <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: <?= $isPengadaanApproved ? '#166534' : '#64748b' ?>; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid <?= $isPengadaanApproved ? 'fa-circle-check' : 'fa-hourglass-half' ?>"></i> 3. Direktur Pengadaan
                </div>
                <div style="font-size: 13px; font-weight: 700; color: #0f172a; margin-top: 6px;">
                    <?= $isPengadaanApproved ? SecurityHelper::escape($po['approved_pengadaan_name']) : 'Menunggu Pengadaan' ?>
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                    <?php if ($isPengadaanApproved): ?>
                        <i class="fa-solid fa-clock me-1"></i> <?= date('d/m/Y H:i', strtotime($po['approved_pengadaan_at'])) ?> WIB
                    <?php else: ?>
                        Menunggu tahap 2
                    <?php endif; ?>
                </div>
            </div>

            <!-- Step 4: Direktur Sarpras (Jika Kontraktor) -->
            <?php if ($needsContractor): ?>
                <?php $isSarprasDirApproved = !empty($po['approved_sarpras_dir_at']); ?>
                <div style="padding: 14px; background: <?= $isSarprasDirApproved ? '#f0fdf4' : '#fffbeb' ?>; border: 1px solid <?= $isSarprasDirApproved ? '#bbf7d0' : '#fde68a' ?>; border-radius: 10px;">
                    <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: <?= $isSarprasDirApproved ? '#166534' : '#92400e' ?>; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid <?= $isSarprasDirApproved ? 'fa-circle-check' : 'fa-helmet-safety' ?>"></i> 4. Direktur Sarpras (Kontraktor)
                    </div>
                    <div style="font-size: 13px; font-weight: 700; color: #0f172a; margin-top: 6px;">
                        <?= $isSarprasDirApproved ? SecurityHelper::escape($po['approved_sarpras_dir_name']) : 'Menunggu Approval' ?>
                    </div>
                    <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                        <?php if ($isSarprasDirApproved): ?>
                            <i class="fa-solid fa-clock me-1"></i> <?= date('d/m/Y H:i', strtotime($po['approved_sarpras_dir_at'])) ?> WIB
                        <?php else: ?>
                            Pengerjaan Khusus
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Step 5: Realisasi & Auto-Stock -->
            <?php $isCompleted = ($status === 'Selesai'); ?>
            <div style="padding: 14px; background: <?= $isCompleted ? '#dcfce7' : '#f8fafc' ?>; border: 1px solid <?= $isCompleted ? '#86efac' : '#e2e8f0' ?>; border-radius: 10px;">
                <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: <?= $isCompleted ? '#15803d' : '#64748b' ?>; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid <?= $isCompleted ? 'fa-boxes-packing' : 'fa-truck-ramp-box' ?>"></i> <?= $needsContractor ? '5' : '4' ?>. Realisasi & Auto-Stock
                </div>
                <div style="font-size: 13px; font-weight: 700; color: #0f172a; margin-top: 6px;">
                    <?= $isCompleted ? 'Barang Diterima & Masuk Stok' : 'Menunggu Realisasi' ?>
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                    <?php if (!empty($po['completed_at'])): ?>
                        <i class="fa-solid fa-clock me-1"></i> Selesai: <?= date('d/m/Y H:i', strtotime($po['completed_at'])) ?>
                    <?php else: ?>
                        Surat jalan / pengerjaan
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- APPROVAL ACTION BOXES (BERDASARKAN ROLE & STATUS) -->
<?php if ($status === 'Menunggu_Kepsek' && in_array($role, ['kepala_sekolah', 'operator_yayasan'], true)): ?>
    <div class="card" style="border: 2px solid #f59e0b; border-radius: 12px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.15);">
        <div class="card-header" style="background: #fef3c7; padding: 16px 20px; border-bottom: 1px solid #fde68a;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: #92400e; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-user-check"></i> Tindakan Persetujuan: Kepala Sekolah
            </h3>
        </div>
        <div class="card-body" style="padding: 20px;">
            <p style="margin: 0 0 16px 0; font-size: 13px; color: #78350f;">
                Sebagai Kepala Sekolah, periksa apakah permohonan sarpras ini telah sesuai dengan RAPBS dan kebutuhan riil unit sekolah Anda.
            </p>
            <form method="POST" action="<?= $baseUrl ?>/sarpras/po/approve-kepsek">
                <?= $csrfField ?>
                <input type="hidden" name="po_id" value="<?= $po['id'] ?>">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 13px; color: #334155;">Catatan / Rekomendasi Kepala Sekolah (Opsional):</label>
                    <textarea name="kepsek_notes" class="form-control" rows="2" placeholder="Catatan persetujuan..."></textarea>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-success" style="font-weight: 700; padding: 8px 20px;">
                        <i class="fa-solid fa-check me-1"></i> Setujui & Teruskan ke Direktur Pengadaan
                    </button>
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('rejectModal').style.display='flex'">
                        <i class="fa-solid fa-ban me-1"></i> Tolak Permohonan
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php if ($status === 'Menunggu_Direktur_Pengadaan' && in_array($role, ['direktur_pengadaan', 'operator_yayasan'], true)): ?>
    <div class="card" style="border: 2px solid #4338ca; border-radius: 12px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(67, 56, 202, 0.15);">
        <div class="card-header" style="background: #e0e7ff; padding: 16px 20px; border-bottom: 1px solid #c7d2fe;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: #3730a3; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-cart-flatbed"></i> Tindakan Persetujuan: Direktur Pengadaan (Yayasan)
            </h3>
        </div>
        <div class="card-body" style="padding: 20px;">
            <p style="margin: 0 0 16px 0; font-size: 13px; color: #312e81;">
                Kepala Sekolah telah menyetujui PO ini. Sebagai Direktur Pengadaan, Anda berwenang memvalidasi anggaran dan jalur pengadaan barang.
                <?php if ($needsContractor): ?>
                    <br><strong class="text-warning"><i class="fa-solid fa-triangle-exclamation"></i> PO ini memerlukan kontraktor/pengerjaan lanjutan, sehingga setelah disetujui akan diteruskan ke Direktur Sarpras.</strong>
                <?php endif; ?>
            </p>
            <form method="POST" action="<?= $baseUrl ?>/sarpras/po/approve-pengadaan">
                <?= $csrfField ?>
                <input type="hidden" name="po_id" value="<?= $po['id'] ?>">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 13px; color: #334155;">Catatan Direktur Pengadaan (Opsional):</label>
                    <textarea name="pengadaan_notes" class="form-control" rows="2" placeholder="Catatan supplier, vendor, estimasi anggaran..."></textarea>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-success" style="font-weight: 700; padding: 8px 20px;">
                        <i class="fa-solid fa-check me-1"></i> <?= $needsContractor ? 'Setujui & Teruskan ke Direktur Sarpras' : 'Setujui Pengadaan (Siap Realisasi)' ?>
                    </button>
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('rejectModal').style.display='flex'">
                        <i class="fa-solid fa-ban me-1"></i> Tolak Permohonan
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php if ($status === 'Menunggu_Direktur_Sarpras' && in_array($role, ['direktur_sarpras', 'operator_yayasan'], true)): ?>
    <div class="card" style="border: 2px solid #d97706; border-radius: 12px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.15);">
        <div class="card-header" style="background: #fef3c7; padding: 16px 20px; border-bottom: 1px solid #fde68a;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: #92400e; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-helmet-safety"></i> Tindakan Persetujuan: Direktur Sarpras (Yayasan)
            </h3>
        </div>
        <div class="card-body" style="padding: 20px;">
            <p style="margin: 0 0 16px 0; font-size: 13px; color: #78350f;">
                Pengadaan ini membutuhkan pengerjaan fisik atau kontraktor. Mohon verifikasi kelayakan teknis dan spesifikasi konstruksi/pekerjaan.
            </p>
            <form method="POST" action="<?= $baseUrl ?>/sarpras/po/approve-sarpras-dir">
                <?= $csrfField ?>
                <input type="hidden" name="po_id" value="<?= $po['id'] ?>">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 13px; color: #334155;">Catatan Direktur Sarpras (Vendor / Kontraktor):</label>
                    <textarea name="sarpras_dir_notes" class="form-control" rows="2" placeholder="Instruksi pengerjaan kontraktor, jadwal instalasi..."></textarea>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-success" style="font-weight: 700; padding: 8px 20px;">
                        <i class="fa-solid fa-check me-1"></i> Setujui Pengerjaan Kontraktor (Siap Realisasi)
                    </button>
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('rejectModal').style.display='flex'">
                        <i class="fa-solid fa-ban me-1"></i> Tolak Permohonan
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<!-- TAHAP REALISASI & SURAT JALAN & AUTO-STOCK -->
<?php 
$canRealize = ($status === 'Disetujui') && (
    ($role === 'sarpras' && (int)($access['unitId'] ?? 0) === (int)$po['unit_id']) ||
    ($role === 'operator_yayasan')
);
?>
<?php if ($canRealize): ?>
    <div class="card" style="border: 2px solid #059669; border-radius: 12px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.15);">
        <div class="card-header" style="background: #ecfdf5; padding: 16px 20px; border-bottom: 1px solid #a7f3d0;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: #047857; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-truck-ramp-box"></i> Serah Terima Fisik Barang & Unggah Surat Jalan (Sarpras <?= SecurityHelper::escape($po['unit_name']) ?>)
            </h3>
        </div>
        <div class="card-body" style="padding: 20px;">
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px; margin-bottom: 16px; font-size: 13px; color: #1e40af;">
                <i class="fa-solid fa-circle-info me-1"></i> 
                <strong>PENTING:</strong> Pengadaan ini telah disetujui oleh Direktur. Petugas <strong>Sarpras Sekolah</strong> wajib memeriksa kesesuaian fisik barang yang diterima, mengunggah Surat Jalan/Bukti Terima dan foto serah terima. Ketika diselesaikan, barang akan <strong>otomatis masuk ke sistem stok/aset <?= SecurityHelper::escape($po['unit_name']) ?></strong>.
            </div>

            <form method="POST" action="<?= $baseUrl ?>/sarpras/po/realize" enctype="multipart/form-data">
                <?= $csrfField ?>
                <input type="hidden" name="po_id" value="<?= $po['id'] ?>">

                <div class="grid-cols-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                            Waktu Mulai Pengerjaan / Pengiriman <span class="text-danger">*</span>
                        </label>
                        <input type="datetime-local" name="work_started_at" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
                    </div>
                    <div>
                        <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                            Waktu Selesai / Barang Diterima Lengkap <span class="text-danger">*</span>
                        </label>
                        <input type="datetime-local" name="completed_at" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
                    </div>
                </div>

                <div class="grid-cols-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                            Unggah Surat Jalan / Bukti Terima (PDF / Foto) <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="delivery_letter_file" class="form-control" accept="image/*,.pdf" required>
                    </div>
                    <div>
                        <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                            Dokumentasi / Foto Serah Terima Fisik (Foto Pengerjaan)
                        </label>
                        <input type="file" name="work_documentation_file" class="form-control" accept="image/*">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 4px; display: block;">
                        Catatan Realisasi & Hasil Penerimaan / Pengerjaan Fisik:
                    </label>
                    <textarea name="realization_notes" class="form-control" rows="2" placeholder="Barang diterima lengkap dan berfungsi normal sesuai spesifikasi..."></textarea>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 700;">
                        <i class="fa-solid fa-box-archive me-1"></i> Selesaikan PO & Otomatis Masukkan ke Stok Aset Sekolah
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php elseif ($status === 'Disetujui'): ?>
    <div class="card" style="border: 1px solid #10b981; border-radius: 12px; margin-bottom: 24px; background: #f0fdf4;">
        <div class="card-body" style="padding: 20px; display: flex; align-items: flex-start; gap: 16px;">
            <div style="width: 44px; height: 44px; border-radius: 50%; background: #d1fae5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                <i class="fa-solid fa-truck-ramp-box"></i>
            </div>
            <div>
                <h4 style="margin: 0 0 6px 0; font-size: 15px; font-weight: 700; color: #065f46;">
                    Status: PO Disetujui — Menunggu Serah Terima Fisik & Surat Jalan oleh Sarpras Sekolah
                </h4>
                <p style="margin: 0; font-size: 13px; color: #047857; line-height: 1.5;">
                    Pengadaan ini telah selesai disetujui. Penerimaan fisik barang, pemeriksaan pengerjaan, dan pengunggahan Surat Jalan serta Dokumentasi Serah Terima dilakukan oleh <strong>Sarpras <?= SecurityHelper::escape($po['unit_name']) ?></strong> saat barang tiba di sekolah.
                </p>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- INFORMASI DOKUMEN REALISASI JIKA SELESAI -->
<?php if ($status === 'Selesai'): ?>
    <div class="card" style="border: 1px solid #86efac; background: #f0fdf4; border-radius: 12px; margin-bottom: 24px;">
        <div class="card-body" style="padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div>
                    <div style="font-size: 16px; font-weight: 800; color: #166534; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-circle-check"></i> Pengadaan Telah Terealisasi & Selesai
                    </div>
                    <div style="font-size: 13px; color: #15803d; margin-top: 4px;">
                        Timestamp Selesai: <strong><?= date('d/m/Y H:i', strtotime($po['completed_at'])) ?> WIB</strong> | 
                        Otomatis masuk stok: <strong><?= date('d/m/Y H:i', strtotime($po['auto_stocked_at'])) ?> WIB</strong>
                    </div>
                    <?php if (!empty($po['realization_notes'])): ?>
                        <div style="font-size: 13px; color: #334155; margin-top: 6px; font-style: italic;">
                            "<?= SecurityHelper::escape($po['realization_notes']) ?>"
                        </div>
                    <?php endif; ?>
                </div>
                <div style="display: flex; gap: 8px;">
                    <?php if (!empty($po['delivery_letter_file'])): ?>
                        <a href="<?= $baseUrl ?>/sarpras/file/view?file=<?= urlencode($po['delivery_letter_file']) ?>" target="_blank" class="btn btn-sm btn-outline" style="border-color: #166534; color: #166534; font-weight: 700;">
                            <i class="fa-solid fa-file-lines me-1"></i> Lihat Surat Jalan
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($po['work_documentation_file'])): ?>
                        <a href="<?= $baseUrl ?>/sarpras/file/view?file=<?= urlencode($po['work_documentation_file']) ?>" target="_blank" class="btn btn-sm btn-outline" style="border-color: #166534; color: #166534; font-weight: 700;">
                            <i class="fa-solid fa-image me-1"></i> Foto Dokumentasi Pengerjaan
                        </a>
                    <?php endif; ?>
                    <a href="<?= $baseUrl ?>/sarpras/assets" class="btn btn-sm btn-primary" style="background: #0f766e; border: none;">
                        <i class="fa-solid fa-boxes-stacked me-1"></i> Cek Stok Masuk
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- TABEL RINCIAN ITEM BARANG DENGAN FOTO -->
<div class="card" style="box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 24px;">
    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title" style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-list-ol" style="color: #0f766e;"></i> Rincian Barang Diminta (<?= count($items) ?> Item)
        </h3>
        <span class="badge" style="background: #ccfbf1; color: #0f766e; border: 1px solid #99f6e4; font-weight: 700;">
            Unit: <?= SecurityHelper::escape($po['delivery_location']) ?>
        </span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table" style="width: 100%; margin: 0;">
                <thead>
                    <tr style="background: #0f766e; color: #ffffff; font-size: 12px; text-transform: uppercase;">
                        <th style="padding: 12px 14px; text-align: center; width: 40px;">Item</th>
                        <th style="padding: 12px 14px;">Deskripsi / Merk / Etc</th>
                        <th style="padding: 12px 14px;">Size / Spesifikasi</th>
                        <th style="padding: 12px 14px; text-align: center; width: 70px;">RAPBS</th>
                        <th style="padding: 12px 14px; text-align: center; width: 80px;">Satuan</th>
                        <th style="padding: 12px 14px; text-align: center; width: 85px;">Posisi Stok</th>
                        <th style="padding: 12px 14px; text-align: center; width: 85px;">ORDER</th>
                        <th style="padding: 12px 14px;">Waktu Pengadaan</th>
                        <th style="padding: 12px 14px;">Beban / Sub Unit</th>
                        <th style="padding: 12px 14px;">Foto Referensi Barang</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 24px; color: #64748b;">Tidak ada rincian item.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px 14px; text-align: center; font-weight: 700; color: #64748b;">
                                    <?= (int)$item['item_no'] ?>
                                </td>
                                <td style="padding: 12px 14px;">
                                    <div style="font-weight: 800; color: #0f172a; font-size: 13px;">
                                        <?= SecurityHelper::escape($item['description']) ?>
                                    </div>
                                    <?php if ($item['is_consumable']): ?>
                                        <span class="badge" style="background: #e0e7ff; color: #3730a3; font-size: 10px;">ATK (Habis Pakai)</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 14px; color: #334155; font-size: 13px;">
                                    <?= SecurityHelper::escape($item['specs'] ?: '-') ?>
                                </td>
                                <td style="padding: 12px 14px; text-align: center;">
                                    <span class="badge <?= ($item['rapbs'] === 'YA') ? 'badge-success' : 'badge-secondary' ?>" style="font-size: 11px;">
                                        <?= SecurityHelper::escape($item['rapbs']) ?>
                                    </span>
                                </td>
                                <td style="padding: 12px 14px; text-align: center; color: #64748b; font-size: 12px;">
                                    <?= SecurityHelper::escape($item['unit_of_measure']) ?>
                                </td>
                                <td style="padding: 12px 14px; text-align: center; font-weight: 600; color: #64748b;">
                                    <?= (int)$item['stock_position'] ?>
                                </td>
                                <td style="padding: 12px 14px; text-align: center; font-weight: 800; color: #0f766e; font-size: 14px;">
                                    <?= (int)$item['quantity_ordered'] ?>
                                </td>
                                <td style="padding: 12px 14px; color: #475569; font-size: 12px;">
                                    <?= SecurityHelper::escape($item['procurement_time'] ?: '-') ?>
                                </td>
                                <td style="padding: 12px 14px; color: #475569; font-size: 12px;">
                                    <?= SecurityHelper::escape($item['cost_burden_unit'] ?: '-') ?>
                                </td>
                                <td style="padding: 12px 14px;">
                                    <?php if (!empty($item['photo'])): ?>
                                        <a href="<?= $baseUrl ?>/sarpras/file/view?file=<?= urlencode($item['photo']) ?>" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; text-decoration: none; color: #0f766e; font-size: 12px; font-weight: 600;">
                                            <img src="<?= $baseUrl ?>/sarpras/file/view?file=<?= urlencode($item['photo']) ?>" alt="Foto" style="width: 38px; height: 38px; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5e1;">
                                            <span>Lihat Foto</span>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size: 12px;">Tanpa Foto</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TOLAK PO -->
<div id="rejectModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; margin: 16px;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #fef2f2;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #991b1b;">
                <i class="fa-solid fa-triangle-exclamation me-1"></i> Tolak Permohonan PO
            </h3>
            <button type="button" onclick="document.getElementById('rejectModal').style.display='none'" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>
        <form method="POST" action="<?= $baseUrl ?>/sarpras/po/reject">
            <?= $csrfField ?>
            <input type="hidden" name="po_id" value="<?= $po['id'] ?>">
            <div style="padding: 20px;">
                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label" style="font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px; display: block;">
                        Alasan Penolakan <span class="text-danger">*</span>
                    </label>
                    <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Tuliskan alasan penolakan secara jelas agar dapat diperbaiki pemohon..." required></textarea>
                </div>
            </div>
            <div style="padding: 14px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('rejectModal').style.display='none'">Batal</button>
                <button type="submit" class="btn btn-danger">Tolak PO Sekarang</button>
            </div>
        </form>
    </div>
</div>
