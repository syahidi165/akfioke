<?php
// views/tagihan/approval.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span class="badge" style="background: #0f766e; color: #fff; padding: 4px 10px; border-radius: 6px; font-weight: 600;">Kepala Sekolah</span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Tindak Lanjut Penagihan &amp; Pemanggilan Orang Tua</span>
            </div>
            <h1 class="page-title" style="margin: 0 0 6px 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px; font-weight: 700; color: #0f172a;">
                <i class="fa-solid fa-envelope-open-text" style="color: #0f766e;"></i> Penagihan &amp; Pemanggilan Orang Tua (FR 8001C / D)
            </h1>
            <p class="page-subtitle" style="margin: 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Menerima pengajuan penagihan dari Tata Usaha, menetapkan jadwal pemanggilan resmi (FR 8001C), mencetak formulir kesepakatan (FR 8001D), atau eskalasi ke Yayasan (Deputi Direktur Pendidikan).
            </p>
        </div>
    </div>
</div>

<!-- Versi Data Impor Tagihan TU -->
<div style="background: #f0fdfa; border: 1px solid #99f6e4; border-left: 4px solid #0d9488; border-radius: 8px; padding: 10px 16px; margin-bottom: 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; font-size: 0.8125rem;">
    <div style="display: flex; align-items: center; gap: 8px; color: #115e59;">
        <i class="fa-solid fa-clock-rotate-left"></i>
        <?php if (!empty($lastImportMeta['imported_at'])):
            $impTs = strtotime($lastImportMeta['imported_at']);
            $fmtLabel = !empty($lastImportMeta['format']) ? strtoupper((string)$lastImportMeta['format']) : 'EXCEL';
        ?>
            <span>
                <strong>Versi Data Tagihan TU Terakhir:</strong>
                <?= SecurityHelper::formatTanggalIndo(date('Y-m-d', $impTs)) ?>, pukul <?= date('H:i', $impTs) ?> WIB
                &bull; Format: <span class="badge" style="background: #ccfbf1; color: #0f766e; font-size: 11px;"><?= SecurityHelper::escape($fmtLabel) ?></span>
                <?php if (!empty($lastImportMeta['filename'])): ?>
                    &bull; File: <code><?= SecurityHelper::escape($lastImportMeta['filename']) ?></code>
                <?php endif; ?>
                <?php if (!empty($lastImportMeta['by_name'])): ?>
                    &bull; Oleh: <strong><?= SecurityHelper::escape($lastImportMeta['by_name']) ?></strong>
                <?php endif; ?>
            </span>
        <?php else: ?>
            <span><strong>Versi Data Tagihan TU:</strong> Belum ada riwayat impor berkas tagihan Excel dari Tata Usaha.</span>
        <?php endif; ?>
    </div>
</div>

<!-- Stats Ringkasan KS -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 16px 20px; border-left: 4px solid #f59e0b; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 600;">Menunggu Panggilan / Review KS</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #b45309; margin: 6px 0 2px 0;">
            <?= $pendingCount ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Tagihan</span>
        </div>
        <div style="font-size: 0.8125rem; color: #b45309; font-weight: 600;">
            Total: <?= SecurityHelper::formatRupiah($totalNominalPending) ?>
        </div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0f766e; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 600;">Format Surat Pemanggilan</div>
        <div style="font-size: 1.35rem; font-weight: 700; color: #0f766e; margin: 6px 0 2px 0;">
            FR 8001C (2 Kolom)
        </div>
        <div style="font-size: 0.8125rem; color: #64748b;">
            Tunggakan SPP &amp; Tunggakan Non-SPP + Schedule KS
        </div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0284c7; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 600;">Surat Kesepakatan Pelunasan</div>
        <div style="font-size: 1.35rem; font-weight: 700; color: #0284c7; margin: 6px 0 2px 0;">
            Formulir FR 8001D
        </div>
        <div style="font-size: 0.8125rem; color: #64748b;">
            Skema cicilan diteruskan ke Buku Cicilan TU
        </div>
    </div>
</div>

<!-- Tabel Daftar Penagihan di Kepala Sekolah -->
<div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); overflow: hidden;">
    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-clipboard-user" style="color: #0f766e;"></i>
            <h3 class="card-title" style="margin: 0; font-size: 1rem; font-weight: 700; color: #0f172a;">Antrean Tagihan untuk Pemanggilan Resmi Orang Tua</h3>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <label style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.8125rem; font-weight: 600; color: #334155; cursor: pointer; user-select: none;" title="Secara bawaan siswa yang sudah dipanggil &amp; selesai pertemuan disembunyikan. Centang untuk menampilkannya.">
                <input type="checkbox" id="toggleShowProcessedKs" onchange="applyApprovalFilters()" style="width: 15px; height: 15px; accent-color: #0f766e; cursor: pointer;">
                <span>Tampilkan yang Sudah Pemanggilan &amp; Pertemuan (<span id="processedCountKs"><?= (int)($processedCount ?? 0) ?></span>)</span>
            </label>
            <input type="text" id="searchTable" class="form-control" placeholder="Cari santri / NIS..." style="font-size: 0.8125rem; height: 34px; width: 220px;">
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <div style="overflow-x: auto;">
            <table id="tableApprovalKs" class="table" style="margin-bottom: 0; width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f1f5f9; border-bottom: 2px solid #cbd5e1; font-size: 0.8125rem; text-transform: uppercase;">
                        <th style="width: 40px; text-align: center; padding: 10px;">No</th>
                        <th class="sortable-th" data-sort-key="student" data-sort-type="text" onclick="sortApprovalTable(this)" style="padding: 10px; cursor: pointer; user-select: none;" title="Klik untuk mengurutkan berdasarkan nama santri">
                            Santri &amp; Orang Tua <i class="fa-solid fa-sort sort-icon" style="color: #94a3b8; margin-left: 4px;"></i>
                        </th>
                        <th class="sortable-th" data-sort-key="spp" data-sort-type="number" onclick="sortApprovalTable(this)" style="padding: 10px; width: 170px; cursor: pointer; user-select: none;" title="Klik untuk mengurutkan berdasarkan Tunggakan SPP">
                            Tunggakan SPP (Rp) <i class="fa-solid fa-sort sort-icon" style="color: #94a3b8; margin-left: 4px;"></i>
                        </th>
                        <th class="sortable-th" data-sort-key="non-spp" data-sort-type="number" onclick="sortApprovalTable(this)" style="padding: 10px; width: 180px; cursor: pointer; user-select: none;" title="Klik untuk mengurutkan berdasarkan Tunggakan Non-SPP">
                            Tunggakan Non-SPP (Rp) <i class="fa-solid fa-sort sort-icon" style="color: #94a3b8; margin-left: 4px;"></i>
                        </th>
                        <th class="sortable-th" data-sort-key="total" data-sort-type="number" onclick="sortApprovalTable(this)" style="text-align: right; width: 150px; padding: 10px; cursor: pointer; user-select: none;" title="Klik untuk mengurutkan berdasarkan Jumlah Total Tunggakan">
                            JUMLAH (Total) <i class="fa-solid fa-sort sort-icon" style="color: #94a3b8; margin-left: 4px;"></i>
                        </th>
                        <th class="sortable-th" data-sort-key="schedule" data-sort-type="number" onclick="sortApprovalTable(this)" style="padding: 10px; width: 180px; text-align: center; cursor: pointer; user-select: none;" title="Klik untuk mengurutkan berdasarkan Jadwal Panggilan">
                            Jadwal Panggilan (FR 8001C) <i class="fa-solid fa-sort sort-icon" style="color: #94a3b8; margin-left: 4px;"></i>
                        </th>
                        <th style="text-align: center; width: 220px; padding: 10px;">Aksi Kepala Sekolah</th>
                    </tr>
                </thead>
                <tbody id="tbodyApprovalKs">
                    <?php if (empty($bills)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #64748b;">
                                <i class="fa-solid fa-circle-check" style="font-size: 2.5rem; margin-bottom: 10px; display: block; color: #10b981; opacity: 0.6;"></i>
                                Tidak ada antrean penagihan tertunggak yang perlu ditindaklanjuti Kepala Sekolah saat ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bills as $idx => $b): 
                            $stage = $b['pipeline_stage'];
                            $isScheduled = !empty($b['kepsek_schedule_date']);
                            $isProcessed = !empty($b['is_processed']);
                            $isEscalatedDeputi = !empty($b['is_escalated_deputi']);
                            $isCalled = !empty($b['is_called']);
                            $rowBg = $isProcessed ? ($isEscalatedDeputi ? '#fdf2f8' : '#f0fdf4') : '#ffffff';
                        ?>
                            <tr class="data-row"
                                data-search="<?= strtolower(SecurityHelper::escape($b['student_name'] . ' ' . $b['nis'] . ' ' . ($b['parent_name'] ?? ''))) ?>"
                                data-student="<?= strtolower(SecurityHelper::escape($b['student_name'])) ?>"
                                data-spp="<?= (float)$b['amount_spp'] ?>"
                                data-non-spp="<?= (float)$b['amount_non_spp'] ?>"
                                data-total="<?= (float)$b['amount'] ?>"
                                data-schedule="<?= $isScheduled ? (int)strtotime($b['kepsek_schedule_date']) : 0 ?>"
                                data-processed="<?= $isProcessed ? '1' : '0' ?>"
                                style="border-bottom: 1px solid #f1f5f9; background: <?= $rowBg ?>; <?= $isProcessed ? 'display: none;' : '' ?>">
                                <td class="row-no" style="text-align: center; color: #64748b; font-size: 0.8125rem; padding: 10px;"><?= $idx + 1 ?></td>
                                
                                <td style="padding: 10px;">
                                    <div style="font-weight: 700; color: #0f172a; font-size: 0.9375rem;">
                                        <?= SecurityHelper::escape($b['student_name']) ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                                        NIS: <?= SecurityHelper::escape($b['nis']) ?> &bull; Kelas <?= SecurityHelper::escape($b['class_name'] ?? 'Aktif') ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #334155; margin-top: 2px;">
                                        Wali: <strong><?= SecurityHelper::escape($b['parent_name'] ?: 'Orang Tua') ?></strong>
                                        <?php if (!empty($b['parent_phone'])): ?>
                                            &bull; <?= SecurityHelper::escape($b['parent_phone']) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div style="font-size: 0.72rem; margin-top: 3px; color: <?= !empty($b['parent_email']) ? '#0369a1' : '#94a3b8' ?>;">
                                        <i class="fa-solid fa-envelope"></i>
                                        <?= !empty($b['parent_email']) ? SecurityHelper::escape($b['parent_email']) : 'Email ortu belum diisi' ?>
                                    </div>
                                    <div style="margin-top: 5px; display: flex; gap: 4px; flex-wrap: wrap; align-items: center;">
                                        <?php if (($b['status'] ?? '') === 'Lunas'): ?>
                                            <span class="badge" style="font-size: 10.5px; background: #dcfce7; color: #15803d; border: 1px solid #86efac;">
                                                <i class="fa-solid fa-circle-check"></i> Sudah Pemanggilan &amp; Pertemuan (Lunas TU)
                                            </span>
                                        <?php elseif ($isEscalatedDeputi): ?>
                                            <span class="badge" style="font-size: 10.5px; background: #fce7f3; color: #9d174d; border: 1px solid #f9a8d4;">
                                                <i class="fa-solid fa-crown"></i> Sudah Pemanggilan &amp; Dieskalasi ke Yayasan
                                            </span>
                                        <?php elseif ($isProcessed): ?>
                                            <span class="badge" style="font-size: 10.5px; background: #dcfce7; color: #15803d; border: 1px solid #86efac;">
                                                <i class="fa-solid fa-circle-check"></i> Sudah Pemanggilan &amp; Pertemuan
                                            </span>
                                            <?php if ((int)($b['agreement_count'] ?? 0) > 0): ?>
                                                <span class="badge" style="font-size: 10px; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
                                                    Kesepakatan FR 8001D
                                                </span>
                                            <?php elseif (!empty($b['has_ks_signed_doc'])): ?>
                                                <span class="badge" style="font-size: 10px; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">
                                                    Berkas TTD
                                                </span>
                                            <?php endif; ?>
                                        <?php elseif ($isCalled): ?>
                                            <span class="badge" style="font-size: 10.5px; background: #fef3c7; color: #b45309; border: 1px solid #fde68a;">
                                                <i class="fa-solid fa-envelope-circle-check"></i> Sudah Dipanggil (Menunggu Pertemuan)
                                            </span>
                                        <?php else: ?>
                                            <span class="badge" style="font-size: 10.5px; background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca;">
                                                <i class="fa-solid fa-clock"></i> Belum Dipanggil
                                            </span>
                                        <?php endif; ?>

                                        <?php
                                            $emailSent = !empty($b['email_8001c_sent']);
                                            $emailSentAtFmt = !empty($b['email_8001c_sent_at']) ? date('d/m/Y H:i', strtotime($b['email_8001c_sent_at'])) . ' WIB' : '';
                                            $emailSentTo = trim((string)($b['email_8001c_sent_to'] ?? ''));
                                            $emailCount = (int)($b['email_8001c_count'] ?? 0);
                                        ?>
                                        <?php if ($emailSent): ?>
                                            <span class="badge" style="font-size: 10.5px; background: #ecfdf5; color: #047857; border: 1px solid #6ee7b7;" title="Email FR 8001C terkirim<?= $emailSentTo !== '' ? ' ke ' . SecurityHelper::escape($emailSentTo) : '' ?><?= $emailSentAtFmt !== '' ? ' pada ' . $emailSentAtFmt : '' ?>">
                                                <i class="fa-solid fa-envelope-circle-check"></i> Email 8001C Terkirim<?= $emailCount > 1 ? ' (' . $emailCount . 'x)' : '' ?><?= $emailSentAtFmt !== '' ? ' &bull; ' . $emailSentAtFmt : '' ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge" style="font-size: 10.5px; background: #f8fafc; color: #64748b; border: 1px solid #cbd5e1;" title="Wali murid belum pernah dikirimi email pemanggilan FR 8001C oleh Kepala Sekolah">
                                                <i class="fa-regular fa-envelope"></i> Email 8001C Belum Dikirim
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- 2 Kolom FR 8001C: Tunggakan SPP -->
                                <td style="padding: 10px; font-family: monospace; font-weight: 600; color: #0369a1; font-size: 0.875rem;">
                                    <?= SecurityHelper::formatRupiah((float)$b['amount_spp']) ?>
                                </td>

                                <!-- 2 Kolom FR 8001C: Tunggakan Non SPP -->
                                <td style="padding: 10px; font-family: monospace; font-weight: 600; color: #d97706; font-size: 0.875rem;">
                                    <?= SecurityHelper::formatRupiah((float)$b['amount_non_spp']) ?>
                                </td>

                                <!-- JUMLAH -->
                                <td style="text-align: right; padding: 10px;">
                                    <div style="font-weight: 800; font-family: monospace; font-size: 1rem; color: #b91c1c;">
                                        <?= SecurityHelper::formatRupiah((float)$b['amount']) ?>
                                    </div>
                                    <span class="badge" style="font-size: 10px; background: #fee2e2; color: #b91c1c;">Tertunggak</span>
                                </td>

                                <!-- Jadwal Panggilan (FR 8001C) -->
                                <td style="text-align: center; padding: 10px; font-size: 0.8125rem;">
                                    <?php if ($isScheduled): ?>
                                        <div style="font-weight: 700; color: #0f766e;">
                                            <i class="fa-solid fa-calendar-day"></i> <?= SecurityHelper::formatTanggalIndo(date('Y-m-d', strtotime($b['kepsek_schedule_date']))) ?>
                                        </div>
                                        <div style="font-size: 0.75rem; color: #475569;">
                                            Pukul <?= date('H:i', strtotime($b['kepsek_schedule_date'])) ?> WIB &bull; <?= SecurityHelper::escape($b['kepsek_schedule_place'] ?: 'Ruang KS') ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 11px;">Belum Terjadwal</span>
                                    <?php endif; ?>

                                    <?php if (!empty($b['signed_fr8001c_path']) || !empty($b['signed_fr8001d_path'])): ?>
                                        <div style="margin-top: 6px; display: flex; gap: 4px; justify-content: center; flex-wrap: wrap;">
                                            <?php if (!empty($b['signed_fr8001c_path'])): ?>
                                                <a href="<?= $baseUrl ?>/tagihan/signed-doc/<?= (int)$b['id'] ?>/c" target="_blank" class="badge" style="background: #e0f2fe; color: #0369a1; text-decoration: none; font-size: 10px; border: 1px solid #bae6fd;" title="Lihat Berkas TTD FR 8001C">
                                                    <i class="fa-solid fa-file-pdf"></i> TTD 8001C
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($b['signed_fr8001d_path'])): ?>
                                                <a href="<?= $baseUrl ?>/tagihan/signed-doc/<?= (int)$b['id'] ?>/d" target="_blank" class="badge" style="background: #ede9fe; color: #6d28d9; text-decoration: none; font-size: 10px; border: 1px solid #ddd6fe;" title="Lihat Berkas TTD FR 8001D">
                                                    <i class="fa-solid fa-file-signature"></i> TTD 8001D
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Aksi Kepala Sekolah -->
                                <td style="text-align: center; padding: 10px;">
                                    <div style="display: flex; gap: 4px; justify-content: center; flex-wrap: wrap;">
                                        
                                        <!-- Tombol Set / Edit Jadwal Panggilan -->
                                        <button type="button" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 0.75rem; color: #0f766e; border-color: #0f766e;" onclick="openScheduleModal(<?= $b['id'] ?>, '<?= SecurityHelper::escape($b['student_name']) ?>', '<?= !empty($b['kepsek_schedule_date']) ? date('Y-m-d', strtotime($b['kepsek_schedule_date'])) : date('Y-m-d', strtotime('+2 days')) ?>', '<?= !empty($b['kepsek_schedule_date']) ? date('H:i', strtotime($b['kepsek_schedule_date'])) : '09:00' ?>', '<?= SecurityHelper::escape($b['kepsek_schedule_place'] ?: 'Ruang Kepala Sekolah') ?>')" title="Atur Jadwal Pertemuan / Panggilan FR 8001C">
                                            <i class="fa-solid fa-calendar-plus"></i> Set Jadwal
                                        </button>

                                        <!-- Cetak Surat Pemanggilan FR 8001C -->
                                        <a href="<?= $baseUrl ?>/tagihan/cetak-fr8001c/<?= $b['id'] ?>" target="_blank" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 0.75rem; color: #0284c7; border-color: #0284c7;" title="Cetak Surat Pemanggilan FR 8001C (2 Kolom & Jadwal)">
                                            <i class="fa-solid fa-print"></i> FR 8001C
                                        </a>

                                        <!-- Cetak Form Kesepakatan FR 8001D -->
                                        <a href="<?= $baseUrl ?>/tagihan/cetak-fr8001d/<?= $b['id'] ?>" target="_blank" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 0.75rem; color: #7c3aed; border-color: #7c3aed;" title="Cetak Form Kesepakatan FR 8001D untuk Orang Tua">
                                            <i class="fa-solid fa-file-signature"></i> FR 8001D
                                        </a>

                                        <!-- Kirim Surat via Email (Lampiran PDF FR 8001C) -->
                                        <button type="button" class="btn btn-outline btn-sm"
                                                style="padding: 4px 8px; font-size: 0.75rem; <?= $emailSent ? 'background: #f0fdf4; color: #15803d; border-color: #86efac; font-weight: 600;' : 'color: #0369a1; border-color: #0369a1;' ?>"
                                                data-bill-id="<?= (int)$b['id'] ?>"
                                                data-student-name="<?= SecurityHelper::escape($b['student_name']) ?>"
                                                data-parent-email="<?= SecurityHelper::escape($b['parent_email'] ?? '') ?>"
                                                data-email-sent="<?= $emailSent ? '1' : '0' ?>"
                                                data-email-sent-at="<?= SecurityHelper::escape($emailSentAtFmt) ?>"
                                                data-email-sent-to="<?= SecurityHelper::escape($emailSentTo) ?>"
                                                data-email-count="<?= $emailCount ?>"
                                                onclick="openEmailKepsekModal(this)"
                                                title="<?= $emailSent ? 'PERHATIAN: Email FR 8001C sudah dikirim (' . SecurityHelper::escape($emailSentAtFmt) . '). Klik hanya jika perlu mengirim ulang.' : 'Kirim Surat Pemanggilan FR 8001C via Email dengan Lampiran PDF' ?>">
                                            <i class="fa-solid <?= $emailSent ? 'fa-envelope-circle-check' : 'fa-paper-plane' ?>"></i> <?= $emailSent ? 'Email Terkirim' : 'Kirim Email 8001C' ?>
                                        </button>

                                        <!-- Modal Kesepakatan Saat Ortu Datang -->
                                        <button type="button" class="btn btn-primary btn-sm" style="padding: 4px 8px; font-size: 0.75rem; background: #059669; border-color: #059669; font-weight: 600;" onclick="openKesepakatanModal(<?= $b['id'] ?>, <?= $b['student_id'] ?>, '<?= SecurityHelper::escape($b['student_name']) ?>', '<?= SecurityHelper::escape($b['class_name'] ?? '') ?>', '<?= SecurityHelper::escape($b['parent_name'] ?? '') ?>', '<?= SecurityHelper::escape($b['nik'] ?? '') ?>', '<?= SecurityHelper::escape($b['parent_phone'] ?? '') ?>', '<?= SecurityHelper::escape($b['alamat'] ?? '') ?>', <?= (float)$b['amount'] ?>)" title="Setujui Kesepakatan Orang Tua (FR 8001D) & Teruskan ke Buku Cicilan TU">
                                            <i class="fa-solid fa-handshake"></i> Kesepakatan
                                        </button>

                                        <!-- Mark Selesai KS dengan Opsi Upload Form TTD -->
                                        <button type="button" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 0.75rem; color: #16a34a; border-color: #16a34a;" onclick="openSelesaiKepsekModal(<?= $b['id'] ?>, '<?= SecurityHelper::escape($b['student_name']) ?>')" title="Tandai Selesai di KS & Upload Berkas TTD (Opsional)">
                                            <i class="fa-solid fa-check"></i> Selesai
                                        </button>

                                        <!-- Eskalasi ke Yayasan / Deputi -->
                                        <button type="button" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 0.75rem; color: #be185d; border-color: #be185d;" onclick="openEskalasiDeputiModal(<?= $b['id'] ?>, '<?= SecurityHelper::escape($b['student_name']) ?>', '<?= SecurityHelper::formatRupiah((float)$b['amount']) ?>')" title="Eskalasi ke Yayasan (Deputi Direktur Pendidikan)">
                                            <i class="fa-solid fa-triangle-exclamation"></i> Ke Yayasan
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr id="emptyFilteredRowKs" style="display: none;">
                            <td colspan="7" style="text-align: center; padding: 36px 20px; color: #64748b; font-size: 0.875rem;">
                                <i class="fa-solid fa-circle-check" style="font-size: 2rem; margin-bottom: 8px; display: block; color: #10b981; opacity: 0.75;"></i>
                                Tidak ada antrean tagihan aktif yang ditampilkan saat ini.
                                <?php if ((int)($processedCount ?? 0) > 0): ?>
                                    <div style="margin-top: 4px; font-size: 0.8125rem; color: #475569;">
                                        Terdapat <strong><?= (int)$processedCount ?></strong> siswa yang sudah dilakukan pemanggilan &amp; pertemuan (otomatis disembunyikan). Centang kotak <strong>Tampilkan yang Sudah Pemanggilan &amp; Pertemuan</strong> di kanan atas tabel bila ingin menampilkannya.
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal 1: Atur Jadwal Pemanggilan (FR 8001C) -->
<div id="modalSchedule" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #fff; width: 100%; max-width: 480px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); overflow: hidden; margin: 20px;">
        <div style="background: #f0fdfa; border-bottom: 1px solid #ccfbf1; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: #0f766e; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-calendar-check"></i> Atur Jadwal Pemanggilan Ortu (FR 8001C)
            </h4>
            <button type="button" onclick="closeScheduleModal()" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>
        <form action="<?= $baseUrl ?>/tagihan/kepsek/schedule" method="POST">
            <?= CSRF::field() ?>
            <input type="hidden" name="bill_id" id="schedBillId" value="">
            <div style="padding: 20px;">
                <div style="font-size: 0.875rem; margin-bottom: 14px; color: #334155;">
                    Santri: <strong id="schedStudentName">-</strong>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Hari &amp; Tanggal Pertemuan <span style="color: #ef4444;">*</span></label>
                        <input type="date" name="schedule_date" id="schedDate" class="form-control" required style="font-size: 0.8125rem;">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Pukul / Waktu <span style="color: #ef4444;">*</span></label>
                        <input type="time" name="schedule_time" id="schedTime" class="form-control" required style="font-size: 0.8125rem;">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Tempat / Ruangan Pertemuan <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="schedule_place" id="schedPlace" class="form-control" value="Ruang Kepala Sekolah" required style="font-size: 0.8125rem;">
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Nomor HP / Kontak Kepala Sekolah (Tertera di Surat)</label>
                    <input type="text" name="kepsek_phone" class="form-control" value="<?= SecurityHelper::escape($kepsekEmp['phone'] ?? '0812-XXXX-XXXX') ?>" style="font-size: 0.8125rem;">
                </div>
            </div>
            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeScheduleModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="background: #0f766e; border-color: #0f766e; font-weight: 600;">
                    <i class="fa-solid fa-save" style="margin-right: 6px;"></i> Simpan &amp; Terapkan ke FR 8001C
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Setujui Kesepakatan FR 8001D Saat Orang Tua Hadir -->
<div id="modalKesepakatan" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #fff; width: 100%; max-width: 580px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); overflow: hidden; margin: 20px; max-height: 90vh; display: flex; flex-direction: column;">
        <div style="background: #ecfdf5; border-bottom: 1px solid #a7f3d0; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: #047857; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-file-signature"></i> Input &amp; Setujui Kesepakatan Pembayaran (FR 8001D)
            </h4>
            <button type="button" onclick="closeKesepakatanModal()" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>
        <form action="<?= $baseUrl ?>/tagihan/approve-kesepakatan-fr8001d" method="POST" style="overflow-y: auto;">
            <?= CSRF::field() ?>
            <input type="hidden" name="bill_id" id="kesepakatanBillId" value="">
            <input type="hidden" name="student_id" id="kesepakatanStudentId" value="">

            <div style="padding: 20px;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px; margin-bottom: 14px;">
                    <div style="font-size: 0.8125rem; color: #475569;">Santri: <strong id="kesepakatanStudentName" style="color: #0f172a;">-</strong> (<span id="kesepakatanClass">-</span>)</div>
                    <div style="font-size: 0.875rem; color: #b91c1c; font-weight: 700; margin-top: 4px;">
                        Total Tunggakan: <span id="kesepakatanDisplayDebt">Rp 0</span>
                    </div>
                </div>

                <div style="font-weight: 700; font-size: 0.875rem; color: #0f172a; margin-bottom: 8px;">Data Orang Tua / Penandatangan:</div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.8125rem; font-weight: 600;">Nama Orang Tua <span style="color: #ef4444;">*</span></label>
                        <input type="text" name="parent_name" id="kesepakatanParentName" class="form-control" required style="font-size: 0.8125rem;">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.8125rem; font-weight: 600;">No. KTP / NIK</label>
                        <input type="text" name="parent_ktp" id="kesepakatanParentKtp" class="form-control" style="font-size: 0.8125rem;">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.8125rem; font-weight: 600;">No. HP / WA</label>
                        <input type="text" name="parent_phone" id="kesepakatanParentPhone" class="form-control" style="font-size: 0.8125rem;">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.8125rem; font-weight: 600;">Alamat Tempat Tinggal</label>
                        <input type="text" name="parent_address" id="kesepakatanParentAddress" class="form-control" style="font-size: 0.8125rem;">
                    </div>
                </div>

                <div style="font-weight: 700; font-size: 0.875rem; color: #0f172a; margin-bottom: 8px;">Pilihan Cara Penyelesaian (Sesuai Form FR 8001D):</div>
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 12px; margin-bottom: 14px;">
                    <div style="margin-bottom: 10px;">
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 0.8125rem; cursor: pointer;">
                            <input type="radio" name="scheme_type" value="lunas_tanggal" checked onclick="toggleSchemeInputs()">
                            <strong>A. Membayar lunas pada tanggal</strong>
                        </label>
                        <div id="boxSchemeA" style="margin-top: 6px; margin-left: 24px;">
                            <input type="date" name="scheme_lunas_date" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" style="font-size: 0.8125rem; width: 200px;">
                        </div>
                    </div>

                    <div style="margin-bottom: 10px;">
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 0.8125rem; cursor: pointer;">
                            <input type="radio" name="scheme_type" value="cicil_bulan" onclick="toggleSchemeInputs()">
                            <strong>B. Dicicil selama ... bulan terhitung mulai tanggal</strong>
                        </label>
                        <div id="boxSchemeB" style="display: none; margin-top: 6px; margin-left: 24px; gap: 8px; flex-wrap: wrap;">
                            <input type="number" name="scheme_months" placeholder="Jml Bulan (misal 3)" min="1" max="24" class="form-control" style="font-size: 0.8125rem; width: 140px;">
                            <input type="date" name="scheme_start_date" class="form-control" value="<?= date('Y-m-d') ?>" style="font-size: 0.8125rem; width: 180px;">
                        </div>
                    </div>

                    <div>
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 0.8125rem; cursor: pointer;">
                            <input type="radio" name="scheme_type" value="cicil_nominal" onclick="toggleSchemeInputs()">
                            <strong>C. Dicicil dengan minimal Rp ... /bulan sampai tanggal</strong>
                        </label>
                        <div id="boxSchemeC" style="display: none; margin-top: 6px; margin-left: 24px; gap: 8px; flex-wrap: wrap;">
                            <input type="text" name="scheme_min_monthly" placeholder="Minimal Rp / Bulan" class="form-control" style="font-size: 0.8125rem; width: 170px;">
                            <input type="date" name="scheme_end_date" class="form-control" value="<?= date('Y-m-d', strtotime('+3 months')) ?>" style="font-size: 0.8125rem; width: 180px;">
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 10px;">
                    <label class="form-label" style="font-size: 0.8125rem; font-weight: 600;">Nominal Total Tunggakan Disepakati (Rp) <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="total_debt" id="kesepakatanTotalDebt" class="form-control" required style="font-size: 0.875rem; font-weight: 700;">
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-size: 0.8125rem; font-weight: 600;">Catatan Tambahan Kesepakatan</label>
                    <textarea name="agreement_notes" class="form-control" rows="2" placeholder="Catatan hasil pertemuan tatap muka..." style="font-size: 0.8125rem;">Orang tua telah hadir dan menandatangani formulir FR 8001D di hadapan Kepala Sekolah.</textarea>
                </div>
            </div>

            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeKesepakatanModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="background: #059669; border-color: #059669; font-weight: 700;">
                    <i class="fa-solid fa-check-double" style="margin-right: 6px;"></i> Setujui &amp; Buat Buku Cicilan TU
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Tandai Selesai di KS (Opsi Upload Dokumen TTD FR 8001C & D) -->
<div id="modalSelesaiKepsek" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #fff; width: 100%; max-width: 500px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); overflow: hidden; margin: 20px;">
        <div style="background: #f0fdf4; border-bottom: 1px solid #bbf7d0; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: #15803d; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-circle-check"></i> Konfirmasi Selesai Penagihan (Kepala Sekolah)
            </h4>
            <button type="button" onclick="closeSelesaiKepsekModal()" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>
        <form action="<?= $baseUrl ?>/tagihan/mark-selesai-kepsek" method="POST" enctype="multipart/form-data">
            <?= CSRF::field() ?>
            <input type="hidden" name="bill_id" id="selesaiKepsekBillId" value="">
            <div style="padding: 20px;">
                <p style="font-size: 0.875rem; color: #334155; margin-top: 0; line-height: 1.5;">
                    Tandai penagihan santri <strong id="selesaiKepsekStudentName">-</strong> telah selesai disepakati atau diselesaikan di tingkat Kepala Sekolah.
                </p>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; margin-bottom: 14px;">
                    <div style="font-size: 0.8125rem; font-weight: 700; color: #0f172a; margin-bottom: 6px;">
                        <i class="fa-solid fa-cloud-arrow-up" style="color: #0284c7;"></i> Unggah Dokumen Bertanda Tangan (Opsional)
                    </div>
                    <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 12px;">
                        File yang diunggah dapat dilihat dan diunduh kembali sewaktu-waktu oleh pihak sekolah maupun yayasan (Format: PDF/JPG/PNG, max 5MB).
                    </div>

                    <div class="form-group" style="margin-bottom: 10px;">
                        <label class="form-label" style="font-size: 0.8125rem; font-weight: 600;">Form FR 8001C (TTD Orang Tua):</label>
                        <input type="file" name="signed_fr8001c" class="form-control" accept=".pdf,.jpg,.jpeg,.png" style="font-size: 0.8125rem;">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 0.8125rem; font-weight: 600;">Form FR 8001D (Kesepakatan TTD Orang Tua):</label>
                        <input type="file" name="signed_fr8001d" class="form-control" accept=".pdf,.jpg,.jpeg,.png" style="font-size: 0.8125rem;">
                    </div>
                </div>
            </div>
            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeSelesaiKepsekModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="background: #16a34a; border-color: #16a34a; font-weight: 600;">
                    <i class="fa-solid fa-check" style="margin-right: 6px;"></i> Tandai Selesai
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 4: Eskalasi ke Yayasan (Deputi Direktur Pendidikan) -->
<div id="modalEskalasiDeputi" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #fff; width: 100%; max-width: 500px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); overflow: hidden; margin: 20px;">
        <div style="background: #fdf2f8; border-bottom: 1px solid #fce7f3; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: #be185d; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-triangle-exclamation"></i> Eskalasi ke Yayasan (Deputi Pendidikan)
            </h4>
            <button type="button" onclick="closeEskalasiDeputiModal()" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>
        <form action="<?= $baseUrl ?>/tagihan/eskalasi-deputi" method="POST" enctype="multipart/form-data">
            <?= CSRF::field() ?>
            <input type="hidden" name="bill_id" id="deputiBillId" value="">
            <div style="padding: 20px;">
                <p style="font-size: 0.875rem; color: #334155; margin-top: 0; line-height: 1.5;">
                    Jika orang tua murid tidak juga datang memenuhi pemanggilan Kepala Sekolah, masalah penagihan akan dialihkan ke Yayasan (Deputi Direktur Pendidikan) untuk diterbitkan surat pemanggilan resmi berkop Yayasan.
                </p>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px; margin-bottom: 14px; font-size: 0.875rem;">
                    <div>Santri: <strong id="deputiStudentName">-</strong></div>
                    <div>Total Tagihan: <strong id="deputiAmount" style="color: #be185d;">-</strong></div>
                </div>
                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Alasan Eskalasi ke Yayasan</label>
                    <textarea name="escalation_notes" class="form-control" rows="2" style="font-size: 0.8125rem;">Orang tua santri tidak juga datang memenuhi panggilan Kepala Sekolah. Mohon tindak lanjut pemanggilan tingkat Yayasan oleh Deputi Direktur Pendidikan.</textarea>
                </div>

                <div style="background: #fdf4ff; border: 1px solid #f5d0fe; border-radius: 6px; padding: 12px;">
                    <div style="font-size: 0.8125rem; font-weight: 700; color: #86198f; margin-bottom: 4px;">
                        <i class="fa-solid fa-paperclip"></i> Lampirkan Dokumen TTD Pemanggilan (Opsional)
                    </div>
                    <div style="font-size: 0.75rem; color: #701a75; margin-bottom: 10px;">
                        Bisa melampirkan berkas FR 8001C/D yang telah ditandatangani untuk arsip penagihan yayasan.
                    </div>
                    <div class="form-group" style="margin-bottom: 8px;">
                        <label class="form-label" style="font-size: 0.8125rem; font-weight: 600;">Form FR 8001C (TTD Orang Tua):</label>
                        <input type="file" name="signed_fr8001c" class="form-control" accept=".pdf,.jpg,.jpeg,.png" style="font-size: 0.8125rem;">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 0.8125rem; font-weight: 600;">Form FR 8001D (Kesepakatan TTD Orang Tua):</label>
                        <input type="file" name="signed_fr8001d" class="form-control" accept=".pdf,.jpg,.jpeg,.png" style="font-size: 0.8125rem;">
                    </div>
                </div>
            </div>
            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeEskalasiDeputiModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="background: #be185d; border-color: #be185d; font-weight: 600;">
                    <i class="fa-solid fa-paper-plane" style="margin-right: 6px;"></i> Eskalasi ke Yayasan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 5: Kirim Email FR 8001C oleh Kepala Sekolah (Lampiran PDF) -->
<div id="modalKirimEmailKepsek" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #fff; width: 100%; max-width: 500px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); overflow: hidden; margin: 20px;">
        <div style="background: #f0f9ff; border-bottom: 1px solid #bae6fd; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: #0369a1; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-paper-plane"></i> Kirim Surat Pemanggilan FR 8001C via Email
            </h4>
            <button type="button" onclick="closeEmailKepsekModal()" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>
        <form action="<?= $baseUrl ?>/tagihan/kepsek/kirim-email" method="POST">
            <?= CSRF::field() ?>
            <input type="hidden" name="bill_id" id="emailKepsekBillId" value="">
            <div style="padding: 20px;">
                <div style="font-size: 0.875rem; margin-bottom: 14px; color: #334155;">
                    Santri: <strong id="emailKepsekStudentName">-</strong>
                </div>
                <div id="emailKepsekAlreadySentWarn" style="display: none; background: #fffbeb; border: 1px solid #fcd34d; border-left: 4px solid #d97706; border-radius: 6px; padding: 10px 12px; margin-bottom: 12px; font-size: 0.8125rem; color: #92400e; line-height: 1.45;">
                    <div style="font-weight: 700; margin-bottom: 2px;">
                        <i class="fa-solid fa-triangle-exclamation"></i> Email Pemanggilan FR 8001C Sudah Pernah Dikirim!
                    </div>
                    <div id="emailKepsekAlreadySentDetail"></div>
                    <div style="margin-top: 4px; font-size: 0.75rem; color: #b45309;">
                        Hindari pengiriman ulang jika tidak diperlukan agar wali murid tidak menerima penagihan bertubi-tubi.
                    </div>
                </div>
                <div style="background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 6px; padding: 8px 12px; margin-bottom: 12px; font-size: 0.8125rem; color: #0f766e;">
                    <i class="fa-solid fa-file-pdf" style="margin-right: 6px;"></i>
                    Lampiran otomatis: <strong>Surat Pemanggilan Kepala Sekolah (FR 8001C - PDF)</strong>
                </div>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Email Orang Tua / Wali <span style="color: #ef4444;">*</span></label>
                    <input type="email" name="target_email" id="emailKepsekTargetEmail" class="form-control" required placeholder="contoh@email.com" style="font-size: 0.8125rem;">
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Catatan Tambahan (Opsional)</label>
                    <textarea name="extra_note" id="emailKepsekNote" rows="2" class="form-control" placeholder="Pesan pengantar tambahan untuk orang tua..." style="font-size: 0.8125rem;"></textarea>
                </div>
            </div>
            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeEmailKepsekModal()">Batal</button>
                <button type="submit" id="btnSubmitEmailKepsek" class="btn btn-primary" style="background: #0369a1; border-color: #0369a1; font-weight: 600;">
                    <i class="fa-solid fa-paper-plane" style="margin-right: 6px;"></i> Kirim Email FR 8001C (PDF)
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Filter pencarian + otomatis sembunyikan siswa yang sudah pemanggilan & pertemuan (kecuali dicentang)
function applyApprovalFilters() {
    const searchInput = document.getElementById('searchTable');
    const toggleShow = document.getElementById('toggleShowProcessedKs');
    const emptyRow = document.getElementById('emptyFilteredRowKs');
    const query = searchInput ? (searchInput.value || '').toLowerCase() : '';
    const showProcessed = toggleShow ? toggleShow.checked : false;

    let visibleNo = 1;
    document.querySelectorAll('#tbodyApprovalKs tr.data-row').forEach(function(row) {
        const search = row.getAttribute('data-search') || '';
        const isProcessed = row.getAttribute('data-processed') === '1';
        const visible = search.includes(query) && (showProcessed || !isProcessed);
        row.style.display = visible ? '' : 'none';
        if (visible) {
            const noCell = row.querySelector('.row-no');
            if (noCell) noCell.innerText = visibleNo++;
        }
    });

    if (emptyRow) {
        emptyRow.style.display = (visibleNo === 1) ? '' : 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchTable');
    if (searchInput) {
        searchInput.addEventListener('input', applyApprovalFilters);
    }
    applyApprovalFilters();
});

// Modal Selesai Kepsek
function openSelesaiKepsekModal(billId, studentName) {
    document.getElementById('selesaiKepsekBillId').value = billId;
    document.getElementById('selesaiKepsekStudentName').innerText = studentName;
    document.getElementById('modalSelesaiKepsek').style.display = 'flex';
}
function closeSelesaiKepsekModal() {
    document.getElementById('modalSelesaiKepsek').style.display = 'none';
}

// Modal Schedule Pemanggilan
function openScheduleModal(billId, studentName, defDate, defTime, defPlace) {
    document.getElementById('schedBillId').value = billId;
    document.getElementById('schedStudentName').innerText = studentName;
    document.getElementById('schedDate').value = defDate;
    document.getElementById('schedTime').value = defTime;
    document.getElementById('schedPlace').value = defPlace;
    document.getElementById('modalSchedule').style.display = 'flex';
}
function closeScheduleModal() {
    document.getElementById('modalSchedule').style.display = 'none';
}

// Modal Kesepakatan FR 8001D
function openKesepakatanModal(billId, studentId, studentName, className, parentName, parentKtp, parentPhone, parentAddress, amount) {
    document.getElementById('kesepakatanBillId').value = billId;
    document.getElementById('kesepakatanStudentId').value = studentId;
    document.getElementById('kesepakatanStudentName').innerText = studentName;
    document.getElementById('kesepakatanClass').innerText = className || 'Aktif';
    document.getElementById('kesepakatanParentName').value = parentName || '';
    document.getElementById('kesepakatanParentKtp').value = parentKtp || '';
    document.getElementById('kesepakatanParentPhone').value = parentPhone || '';
    document.getElementById('kesepakatanParentAddress').value = parentAddress || '';
    document.getElementById('kesepakatanTotalDebt').value = amount;
    document.getElementById('kesepakatanDisplayDebt').innerText = 'Rp ' + amount.toLocaleString('id-ID');
    document.getElementById('modalKesepakatan').style.display = 'flex';
}
function closeKesepakatanModal() {
    document.getElementById('modalKesepakatan').style.display = 'none';
}

function toggleSchemeInputs() {
    const type = document.querySelector('input[name="scheme_type"]:checked').value;
    document.getElementById('boxSchemeA').style.display = (type === 'lunas_tanggal') ? 'block' : 'none';
    document.getElementById('boxSchemeB').style.display = (type === 'cicil_bulan') ? 'flex' : 'none';
    document.getElementById('boxSchemeC').style.display = (type === 'cicil_nominal') ? 'flex' : 'none';
}

// Modal Eskalasi ke Deputi
function openEskalasiDeputiModal(billId, studentName, amount) {
    document.getElementById('deputiBillId').value = billId;
    document.getElementById('deputiStudentName').innerText = studentName;
    document.getElementById('deputiAmount').innerText = amount;
    document.getElementById('modalEskalasiDeputi').style.display = 'flex';
}
function closeEskalasiDeputiModal() {
    document.getElementById('modalEskalasiDeputi').style.display = 'none';
}
function openEmailKepsekModal(btn) {
    document.getElementById('emailKepsekBillId').value = btn.getAttribute('data-bill-id') || '';
    document.getElementById('emailKepsekStudentName').innerText = btn.getAttribute('data-student-name') || '-';
    document.getElementById('emailKepsekTargetEmail').value = btn.getAttribute('data-parent-email') || '';
    document.getElementById('emailKepsekNote').value = '';

    const isSent = btn.getAttribute('data-email-sent') === '1';
    const sentAt = btn.getAttribute('data-email-sent-at') || '';
    const sentTo = btn.getAttribute('data-email-sent-to') || '';
    const sentCount = parseInt(btn.getAttribute('data-email-count') || '0', 10);
    const warnBox = document.getElementById('emailKepsekAlreadySentWarn');
    const warnDetail = document.getElementById('emailKepsekAlreadySentDetail');
    const submitBtn = document.getElementById('btnSubmitEmailKepsek');

    if (warnBox && warnDetail) {
        if (isSent) {
            let parts = [];
            if (sentTo) parts.push('Tujuan: ' + sentTo);
            if (sentAt) parts.push('Waktu: ' + sentAt);
            if (sentCount > 0) parts.push('Total terkirim: ' + sentCount + 'x');
            warnDetail.innerText = parts.join(' • ');
            warnBox.style.display = 'block';
        } else {
            warnDetail.innerText = '';
            warnBox.style.display = 'none';
        }
    }
    if (submitBtn) {
        if (isSent) {
            submitBtn.style.background = '#d97706';
            submitBtn.style.borderColor = '#d97706';
            submitBtn.innerHTML = '<i class="fa-solid fa-rotate-right" style="margin-right: 6px;"></i> Kirim Ulang Email FR 8001C (PDF)';
        } else {
            submitBtn.style.background = '#0369a1';
            submitBtn.style.borderColor = '#0369a1';
            submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane" style="margin-right: 6px;"></i> Kirim Email FR 8001C (PDF)';
        }
    }

    document.getElementById('modalKirimEmailKepsek').style.display = 'flex';
}
function closeEmailKepsekModal() {
    document.getElementById('modalKirimEmailKepsek').style.display = 'none';
}

let currentSortKey = '';
let currentSortDir = 'desc';
function sortApprovalTable(th) {
    const tbody = document.getElementById('tbodyApprovalKs');
    if (!tbody) return;
    const key = th.getAttribute('data-sort-key');
    const type = th.getAttribute('data-sort-type') || 'text';
    if (!key) return;

    if (currentSortKey === key) {
        currentSortDir = (currentSortDir === 'desc') ? 'asc' : 'desc';
    } else {
        currentSortKey = key;
        currentSortDir = (type === 'number') ? 'desc' : 'asc';
    }

    const rows = Array.from(tbody.querySelectorAll('tr.data-row'));
    const attrName = 'data-' + key;
    rows.sort(function(a, b) {
        const valA = a.getAttribute(attrName) || '';
        const valB = b.getAttribute(attrName) || '';
        if (type === 'number') {
            const numA = parseFloat(valA) || 0;
            const numB = parseFloat(valB) || 0;
            return currentSortDir === 'asc' ? (numA - numB) : (numB - numA);
        }
        return currentSortDir === 'asc'
            ? valA.localeCompare(valB, 'id')
            : valB.localeCompare(valA, 'id');
    });

    rows.forEach(function(row) {
        tbody.appendChild(row);
    });
    const emptyRow = document.getElementById('emptyFilteredRowKs');
    if (emptyRow) {
        tbody.appendChild(emptyRow);
    }
    applyApprovalFilters();

    document.querySelectorAll('#tableApprovalKs thead th.sortable-th .sort-icon').forEach(function(icon) {
        icon.className = 'fa-solid fa-sort sort-icon';
        icon.style.color = '#94a3b8';
    });
    const activeIcon = th.querySelector('.sort-icon');
    if (activeIcon) {
        activeIcon.className = (currentSortDir === 'asc' ? 'fa-solid fa-sort-up' : 'fa-solid fa-sort-down') + ' sort-icon';
        activeIcon.style.color = '#0f766e';
    }
}
</script>
