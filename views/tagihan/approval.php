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
        <div>
            <input type="text" id="searchTable" class="form-control" placeholder="Cari santri / NIS..." style="font-size: 0.8125rem; height: 34px; width: 220px;">
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <div style="overflow-x: auto;">
            <table class="table" style="margin-bottom: 0; width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f1f5f9; border-bottom: 2px solid #cbd5e1; font-size: 0.8125rem; text-transform: uppercase;">
                        <th style="width: 40px; text-align: center; padding: 10px;">No</th>
                        <th style="padding: 10px;">Santri &amp; Orang Tua</th>
                        <th style="padding: 10px; width: 170px;">Tunggakan SPP (Rp)</th>
                        <th style="padding: 10px; width: 180px;">Tunggakan Non-SPP (Rp)</th>
                        <th style="text-align: right; width: 140px; padding: 10px;">JUMLAH (Total)</th>
                        <th style="padding: 10px; width: 180px; text-align: center;">Jadwal Panggilan (FR 8001C)</th>
                        <th style="text-align: center; width: 220px; padding: 10px;">Aksi Kepala Sekolah</th>
                    </tr>
                </thead>
                <tbody>
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
                        ?>
                            <tr class="data-row" data-search="<?= strtolower(SecurityHelper::escape($b['student_name'] . ' ' . $b['nis'] . ' ' . ($b['parent_name'] ?? ''))) ?>" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="text-align: center; color: #64748b; font-size: 0.8125rem; padding: 10px;"><?= $idx + 1 ?></td>
                                
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

                                        <!-- Modal Kesepakatan Saat Ortu Datang -->
                                        <button type="button" class="btn btn-primary btn-sm" style="padding: 4px 8px; font-size: 0.75rem; background: #059669; border-color: #059669; font-weight: 600;" onclick="openKesepakatanModal(<?= $b['id'] ?>, <?= $b['student_id'] ?>, '<?= SecurityHelper::escape($b['student_name']) ?>', '<?= SecurityHelper::escape($b['class_name'] ?? '') ?>', '<?= SecurityHelper::escape($b['parent_name'] ?? '') ?>', '<?= SecurityHelper::escape($b['nik'] ?? '') ?>', '<?= SecurityHelper::escape($b['parent_phone'] ?? '') ?>', '<?= SecurityHelper::escape($b['alamat'] ?? '') ?>', <?= (float)$b['amount'] ?>)" title="Setujui Kesepakatan Orang Tua (FR 8001D) & Teruskan ke Buku Cicilan TU">
                                            <i class="fa-solid fa-handshake"></i> Kesepakatan
                                        </button>

                                        <!-- Mark Selesai KS -->
                                        <form action="<?= $baseUrl ?>/tagihan/mark-selesai-kepsek" method="POST" style="display: inline;" onsubmit="return confirm('Tandai penagihan ini SELESAI di Kepala Sekolah?');">
                                            <?= CSRF::field() ?>
                                            <input type="hidden" name="bill_id" value="<?= $b['id'] ?>">
                                            <button type="submit" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 0.75rem; color: #16a34a; border-color: #16a34a;" title="Tandai Selesai di KS">
                                                <i class="fa-solid fa-check"></i> Selesai
                                            </button>
                                        </form>

                                        <!-- Eskalasi ke Yayasan / Deputi -->
                                        <button type="button" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 0.75rem; color: #be185d; border-color: #be185d;" onclick="openEskalasiDeputiModal(<?= $b['id'] ?>, '<?= SecurityHelper::escape($b['student_name']) ?>', '<?= SecurityHelper::formatRupiah((float)$b['amount']) ?>')" title="Eskalasi ke Yayasan (Deputi Direktur Pendidikan)">
                                            <i class="fa-solid fa-triangle-exclamation"></i> Ke Yayasan
                                        </button>
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

<!-- Modal 3: Eskalasi ke Yayasan (Deputi Direktur Pendidikan) -->
<div id="modalEskalasiDeputi" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #fff; width: 100%; max-width: 480px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); overflow: hidden; margin: 20px;">
        <div style="background: #fdf2f8; border-bottom: 1px solid #fce7f3; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: #be185d; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-triangle-exclamation"></i> Eskalasi ke Yayasan (Deputi Pendidikan)
            </h4>
            <button type="button" onclick="closeEskalasiDeputiModal()" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>
        <form action="<?= $baseUrl ?>/tagihan/eskalasi-deputi" method="POST">
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
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Alasan Eskalasi ke Yayasan</label>
                    <textarea name="escalation_notes" class="form-control" rows="3" style="font-size: 0.8125rem;">Orang tua santri tidak juga datang memenuhi panggilan Kepala Sekolah. Mohon tindak lanjut pemanggilan tingkat Yayasan oleh Deputi Direktur Pendidikan.</textarea>
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

<script>
// Filter tabel pencarian
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchTable');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = (this.value || '').toLowerCase();
            document.querySelectorAll('.data-row').forEach(function(row) {
                const search = row.getAttribute('data-search') || '';
                row.style.display = search.includes(query) ? '' : 'none';
            });
        });
    }
});

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
</script>
