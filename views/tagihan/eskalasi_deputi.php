<?php
// views/tagihan/eskalasi_deputi.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span class="badge" style="background: linear-gradient(135deg, #be185d, #9d174d); color: #fff; padding: 4px 10px; border-radius: 6px; font-weight: 600;">Deputi Direktur Pendidikan</span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Tingkat Yayasan Islam Al Kahfi Batam</span>
            </div>
            <h1 class="page-title" style="margin: 0 0 6px 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px; font-weight: 700; color: #0f172a;">
                <i class="fa-solid fa-crown" style="color: #be185d;"></i> Eskalasi Penagihan Yayasan &amp; Pemanggilan Deputi (FR 8001C Kop Yayasan)
            </h1>
            <p class="page-subtitle" style="margin: 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Penanganan kasus tunggakan yang dieskalasikan oleh Kepala Sekolah karena orang tua tidak kunjung hadir. Deputi menerbitkan surat pemanggilan resmi ber-Kop Yayasan (FR 8001C) dengan tanda tangan KS &amp; Deputi, serta persetujuan form kesepakatan (FR 8001D).
            </p>
        </div>
    </div>
</div>

<!-- Stats Deputi -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 16px 20px; border-left: 4px solid #be185d; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 600;">Tagihan Eskalasi ke Yayasan</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #be185d; margin: 6px 0 2px 0;">
            <?= $pendingCount ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Kasus</span>
        </div>
        <div style="font-size: 0.8125rem; color: #be185d; font-weight: 600;">
            Total Nominal: <?= SecurityHelper::formatRupiah($totalNominal) ?>
        </div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0f766e; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 600;">Format Surat Pemanggilan</div>
        <div style="font-size: 1.35rem; font-weight: 700; color: #0f766e; margin: 6px 0 2px 0;">
            FR 8001C (Kop Yayasan)
        </div>
        <div style="font-size: 0.8125rem; color: #64748b;">
            2 Kolom (SPP vs Non-SPP) &bull; TTD KS &amp; Deputi
        </div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0284c7; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 600;">Integrasi Buku Cicilan TU</div>
        <div style="font-size: 1.35rem; font-weight: 700; color: #0284c7; margin: 6px 0 2px 0;">
            FR 8001D Disetujui
        </div>
        <div style="font-size: 0.8125rem; color: #64748b;">
            Otomatis diteruskan ke Akun TU untuk penarikan angsuran
        </div>
    </div>
</div>

<!-- Tabel Eskalasi di Deputi -->
<div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); overflow: hidden;">
    <div class="card-header" style="background: #fdf2f8; border-bottom: 1px solid #fce7f3; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-triangle-exclamation" style="color: #be185d;"></i>
            <h3 class="card-title" style="margin: 0; font-size: 1rem; font-weight: 700; color: #831843;">Daftar Tagihan Dieskalasikan oleh Kepala Sekolah Seluruh Unit</h3>
        </div>
        <div>
            <input type="text" id="searchTable" class="form-control" placeholder="Cari santri / unit..." style="font-size: 0.8125rem; height: 34px; width: 220px;">
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <div style="overflow-x: auto;">
            <table class="table" style="margin-bottom: 0; width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; font-size: 0.8125rem; text-transform: uppercase;">
                        <th style="width: 40px; text-align: center; padding: 10px;">No</th>
                        <th style="padding: 10px;">Santri, Unit &amp; Orang Tua</th>
                        <th style="padding: 10px; width: 160px;">Tunggakan SPP</th>
                        <th style="padding: 10px; width: 160px;">Tunggakan Non-SPP</th>
                        <th style="text-align: right; width: 130px; padding: 10px;">Total Tagihan</th>
                        <th style="padding: 10px; width: 180px; text-align: center;">Jadwal Yayasan</th>
                        <th style="text-align: center; width: 240px; padding: 10px;">Aksi Deputi Direktur</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bills)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #64748b;">
                                <i class="fa-solid fa-shield-halved" style="font-size: 2.5rem; margin-bottom: 10px; display: block; color: #10b981; opacity: 0.6;"></i>
                                Tidak ada kasus tunggakan yang dieskalasi ke tingkat Yayasan saat ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bills as $idx => $b): 
                            $stage = $b['pipeline_stage'];
                            $isScheduled = !empty($b['deputi_schedule_date']);
                        ?>
                            <tr class="data-row" data-search="<?= strtolower(SecurityHelper::escape($b['student_name'] . ' ' . $b['nis'] . ' ' . $b['unit_name'] . ' ' . ($b['parent_name'] ?? ''))) ?>" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="text-align: center; color: #64748b; font-size: 0.8125rem; padding: 10px;"><?= $idx + 1 ?></td>
                                
                                <td style="padding: 10px;">
                                    <div style="font-weight: 700; color: #0f172a; font-size: 0.9375rem;">
                                        <?= SecurityHelper::escape($b['student_name']) ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                                        <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 11px;"><?= SecurityHelper::escape($b['unit_name']) ?></span> &bull; 
                                        NIS: <?= SecurityHelper::escape($b['nis']) ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #334155; margin-top: 2px;">
                                        Orang Tua: <strong><?= SecurityHelper::escape($b['parent_name'] ?: 'Wali') ?></strong> (<?= SecurityHelper::escape($b['parent_phone'] ?: '-') ?>)
                                    </div>
                                </td>

                                <td style="padding: 10px; font-family: monospace; font-weight: 600; color: #0369a1; font-size: 0.875rem;">
                                    <?= SecurityHelper::formatRupiah((float)$b['amount_spp']) ?>
                                </td>

                                <td style="padding: 10px; font-family: monospace; font-weight: 600; color: #d97706; font-size: 0.875rem;">
                                    <?= SecurityHelper::formatRupiah((float)$b['amount_non_spp']) ?>
                                </td>

                                <td style="text-align: right; padding: 10px;">
                                    <div style="font-weight: 800; font-family: monospace; font-size: 1rem; color: #be185d;">
                                        <?= SecurityHelper::formatRupiah((float)$b['amount']) ?>
                                    </div>
                                </td>

                                <!-- Jadwal Panggilan Yayasan -->
                                <td style="text-align: center; padding: 10px; font-size: 0.8125rem;">
                                    <?php if ($isScheduled): ?>
                                        <div style="font-weight: 700; color: #be185d;">
                                            <i class="fa-solid fa-calendar-day"></i> <?= SecurityHelper::formatTanggalIndo(date('Y-m-d', strtotime($b['deputi_schedule_date']))) ?>
                                        </div>
                                        <div style="font-size: 0.75rem; color: #475569;">
                                            Pukul <?= date('H:i', strtotime($b['deputi_schedule_date'])) ?> WIB &bull; <?= SecurityHelper::escape($b['deputi_schedule_place'] ?: 'Kantor Yayasan') ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 11px;">Belum Terjadwal</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Aksi Deputi -->
                                <td style="text-align: center; padding: 10px;">
                                    <div style="display: flex; gap: 4px; justify-content: center; flex-wrap: wrap;">
                                        
                                        <!-- Atur Jadwal Panggilan Yayasan -->
                                        <button type="button" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 0.75rem; color: #be185d; border-color: #be185d;" onclick="openDeputiScheduleModal(<?= $b['id'] ?>, '<?= SecurityHelper::escape($b['student_name']) ?>', '<?= !empty($b['deputi_schedule_date']) ? date('Y-m-d', strtotime($b['deputi_schedule_date'])) : date('Y-m-d', strtotime('+3 days')) ?>', '<?= !empty($b['deputi_schedule_date']) ? date('H:i', strtotime($b['deputi_schedule_date'])) : '10:00' ?>', '<?= SecurityHelper::escape($b['deputi_schedule_place'] ?: 'Ruang Rapat Direktorat Pendidikan Yayasan Al-Kahfi') ?>')" title="Atur Jadwal Pemanggilan Yayasan">
                                            <i class="fa-solid fa-calendar-plus"></i> Set Jadwal
                                        </button>

                                        <!-- Cetak Surat FR 8001C Kop Yayasan -->
                                        <a href="<?= $baseUrl ?>/tagihan/cetak-fr8001c-deputi/<?= $b['id'] ?>" target="_blank" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 0.75rem; color: #0f766e; border-color: #0f766e;" title="Cetak Surat Pemanggilan FR 8001C (Kop Yayasan & TTD KS-Deputi)">
                                            <i class="fa-solid fa-print"></i> FR 8001C (Yayasan)
                                        </a>

                                        <!-- Cetak Form FR 8001D Kop Yayasan -->
                                        <a href="<?= $baseUrl ?>/tagihan/cetak-fr8001d-deputi/<?= $b['id'] ?>" target="_blank" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 0.75rem; color: #7c3aed; border-color: #7c3aed;" title="Cetak Form Kesepakatan FR 8001D Kop Yayasan">
                                            <i class="fa-solid fa-file-signature"></i> FR 8001D
                                        </a>

                                        <!-- Setujui Kesepakatan FR 8001D Saat Ortu Hadir -->
                                        <button type="button" class="btn btn-primary btn-sm" style="padding: 4px 8px; font-size: 0.75rem; background: #059669; border-color: #059669; font-weight: 600;" onclick="openDeputiKesepakatanModal(<?= $b['id'] ?>, <?= $b['student_id'] ?>, <?= $b['unit_id'] ?>, '<?= SecurityHelper::escape($b['student_name']) ?>', '<?= SecurityHelper::escape($b['unit_name']) ?>', '<?= SecurityHelper::escape($b['parent_name'] ?? '') ?>', '<?= SecurityHelper::escape($b['nik'] ?? '') ?>', '<?= SecurityHelper::escape($b['parent_phone'] ?? '') ?>', '<?= SecurityHelper::escape($b['alamat'] ?? '') ?>', <?= (float)$b['amount'] ?>)" title="Setujui Kesepakatan FR 8001D & Masukkan ke Buku Cicilan TU">
                                            <i class="fa-solid fa-handshake"></i> Kesepakatan
                                        </button>

                                        <!-- Mark Selesai Deputi -->
                                        <form action="<?= $baseUrl ?>/tagihan/mark-selesai-deputi" method="POST" style="display: inline;" onsubmit="return confirm('Tandai kasus penagihan ini SELESAI di tingkat Deputi Yayasan?');">
                                            <?= CSRF::field() ?>
                                            <input type="hidden" name="bill_id" value="<?= $b['id'] ?>">
                                            <button type="submit" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 0.75rem; color: #16a34a; border-color: #16a34a;" title="Tandai Selesai di Deputi">
                                                <i class="fa-solid fa-check"></i> Selesai
                                            </button>
                                        </form>

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

<!-- Modal Jadwal Pemanggilan Yayasan -->
<div id="modalDeputiSchedule" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #fff; width: 100%; max-width: 480px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); overflow: hidden; margin: 20px;">
        <div style="background: #fdf2f8; border-bottom: 1px solid #fce7f3; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: #be185d; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-calendar-check"></i> Jadwal Pemanggilan Yayasan (FR 8001C)
            </h4>
            <button type="button" onclick="closeDeputiScheduleModal()" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>
        <form action="<?= $baseUrl ?>/tagihan/deputi/schedule" method="POST">
            <?= CSRF::field() ?>
            <input type="hidden" name="bill_id" id="deputiSchedBillId" value="">
            <div style="padding: 20px;">
                <div style="font-size: 0.875rem; margin-bottom: 14px; color: #334155;">
                    Santri: <strong id="deputiSchedStudentName">-</strong>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Hari &amp; Tanggal Pertemuan <span style="color: #ef4444;">*</span></label>
                        <input type="date" name="schedule_date" id="deputiSchedDate" class="form-control" required style="font-size: 0.8125rem;">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Pukul / Waktu <span style="color: #ef4444;">*</span></label>
                        <input type="time" name="schedule_time" id="deputiSchedTime" class="form-control" required style="font-size: 0.8125rem;">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Tempat / Ruangan di Yayasan <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="schedule_place" id="deputiSchedPlace" class="form-control" value="Ruang Rapat Direktorat Pendidikan Yayasan Al-Kahfi" required style="font-size: 0.8125rem;">
                </div>
            </div>
            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeDeputiScheduleModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="background: #be185d; border-color: #be185d; font-weight: 600;">
                    <i class="fa-solid fa-save" style="margin-right: 6px;"></i> Simpan Jadwal Yayasan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Kesepakatan Tingkat Yayasan (FR 8001D) -->
<div id="modalDeputiKesepakatan" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #fff; width: 100%; max-width: 580px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); overflow: hidden; margin: 20px; max-height: 90vh; display: flex; flex-direction: column;">
        <div style="background: #ecfdf5; border-bottom: 1px solid #a7f3d0; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: #047857; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-file-signature"></i> Kesepakatan Pembayaran Yayasan (FR 8001D)
            </h4>
            <button type="button" onclick="closeDeputiKesepakatanModal()" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>
        <form action="<?= $baseUrl ?>/tagihan/approve-kesepakatan-fr8001d" method="POST" style="overflow-y: auto;">
            <?= CSRF::field() ?>
            <input type="hidden" name="bill_id" id="deputiKesepakatanBillId" value="">
            <input type="hidden" name="student_id" id="deputiKesepakatanStudentId" value="">
            <input type="hidden" name="unit_id" id="deputiKesepakatanUnitId" value="">

            <div style="padding: 20px;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px; margin-bottom: 14px;">
                    <div style="font-size: 0.8125rem; color: #475569;">Santri: <strong id="deputiKesepakatanStudentName" style="color: #0f172a;">-</strong> (<span id="deputiKesepakatanUnitName">-</span>)</div>
                    <div style="font-size: 0.875rem; color: #b91c1c; font-weight: 700; margin-top: 4px;">
                        Total Tunggakan: <span id="deputiKesepakatanDisplayDebt">Rp 0</span>
                    </div>
                </div>

                <div style="font-weight: 700; font-size: 0.875rem; color: #0f172a; margin-bottom: 8px;">Data Orang Tua / Wali:</div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.8125rem; font-weight: 600;">Nama Orang Tua <span style="color: #ef4444;">*</span></label>
                        <input type="text" name="parent_name" id="deputiKesepakatanParentName" class="form-control" required style="font-size: 0.8125rem;">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.8125rem; font-weight: 600;">No. KTP / NIK</label>
                        <input type="text" name="parent_ktp" id="deputiKesepakatanParentKtp" class="form-control" style="font-size: 0.8125rem;">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.8125rem; font-weight: 600;">No. HP / WA</label>
                        <input type="text" name="parent_phone" id="deputiKesepakatanParentPhone" class="form-control" style="font-size: 0.8125rem;">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.8125rem; font-weight: 600;">Alamat Lengkap</label>
                        <input type="text" name="parent_address" id="deputiKesepakatanParentAddress" class="form-control" style="font-size: 0.8125rem;">
                    </div>
                </div>

                <div style="font-weight: 700; font-size: 0.875rem; color: #0f172a; margin-bottom: 8px;">Skema Kesepakatan FR 8001D:</div>
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 12px; margin-bottom: 14px;">
                    <div style="margin-bottom: 10px;">
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 0.8125rem; cursor: pointer;">
                            <input type="radio" name="scheme_type" value="lunas_tanggal" checked onclick="toggleDeputiScheme()">
                            <strong>A. Membayar lunas pada tanggal</strong>
                        </label>
                        <div id="deputiBoxSchemeA" style="margin-top: 6px; margin-left: 24px;">
                            <input type="date" name="scheme_lunas_date" class="form-control" value="<?= date('Y-m-d', strtotime('+10 days')) ?>" style="font-size: 0.8125rem; width: 200px;">
                        </div>
                    </div>

                    <div style="margin-bottom: 10px;">
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 0.8125rem; cursor: pointer;">
                            <input type="radio" name="scheme_type" value="cicil_bulan" onclick="toggleDeputiScheme()">
                            <strong>B. Dicicil selama ... bulan terhitung mulai tanggal</strong>
                        </label>
                        <div id="deputiBoxSchemeB" style="display: none; margin-top: 6px; margin-left: 24px; gap: 8px; flex-wrap: wrap;">
                            <input type="number" name="scheme_months" placeholder="Jml Bulan (misal 4)" min="1" max="36" class="form-control" style="font-size: 0.8125rem; width: 140px;">
                            <input type="date" name="scheme_start_date" class="form-control" value="<?= date('Y-m-d') ?>" style="font-size: 0.8125rem; width: 180px;">
                        </div>
                    </div>

                    <div>
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 0.8125rem; cursor: pointer;">
                            <input type="radio" name="scheme_type" value="cicil_nominal" onclick="toggleDeputiScheme()">
                            <strong>C. Dicicil dengan minimal Rp ... /bulan sampai tanggal</strong>
                        </label>
                        <div id="deputiBoxSchemeC" style="display: none; margin-top: 6px; margin-left: 24px; gap: 8px; flex-wrap: wrap;">
                            <input type="text" name="scheme_min_monthly" placeholder="Minimal Rp / Bulan" class="form-control" style="font-size: 0.8125rem; width: 170px;">
                            <input type="date" name="scheme_end_date" class="form-control" value="<?= date('Y-m-d', strtotime('+4 months')) ?>" style="font-size: 0.8125rem; width: 180px;">
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 10px;">
                    <label class="form-label" style="font-size: 0.8125rem; font-weight: 600;">Nominal Total Disepakati (Rp) <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="total_debt" id="deputiKesepakatanTotalDebt" class="form-control" required style="font-size: 0.875rem; font-weight: 700;">
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-size: 0.8125rem; font-weight: 600;">Catatan Kesepakatan di Yayasan</label>
                    <textarea name="agreement_notes" class="form-control" rows="2" style="font-size: 0.8125rem;">Orang tua telah hadir memenuhi panggilan di Kantor Yayasan dan menyepakati skema penyelesaian FR 8001D di hadapan Deputi Direktur Pendidikan.</textarea>
                </div>
            </div>

            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeDeputiKesepakatanModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="background: #059669; border-color: #059669; font-weight: 700;">
                    <i class="fa-solid fa-check-double" style="margin-right: 6px;"></i> Setujui Kesepakatan (Masuk ke TU)
                </button>
            </div>
        </form>
    </div>
</div>

<script>
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

function openDeputiScheduleModal(billId, studentName, defDate, defTime, defPlace) {
    document.getElementById('deputiSchedBillId').value = billId;
    document.getElementById('deputiSchedStudentName').innerText = studentName;
    document.getElementById('deputiSchedDate').value = defDate;
    document.getElementById('deputiSchedTime').value = defTime;
    document.getElementById('deputiSchedPlace').value = defPlace;
    document.getElementById('modalDeputiSchedule').style.display = 'flex';
}
function closeDeputiScheduleModal() {
    document.getElementById('modalDeputiSchedule').style.display = 'none';
}

function openDeputiKesepakatanModal(billId, studentId, unitId, studentName, unitName, parentName, parentKtp, parentPhone, parentAddress, amount) {
    document.getElementById('deputiKesepakatanBillId').value = billId;
    document.getElementById('deputiKesepakatanStudentId').value = studentId;
    document.getElementById('deputiKesepakatanUnitId').value = unitId;
    document.getElementById('deputiKesepakatanStudentName').innerText = studentName;
    document.getElementById('deputiKesepakatanUnitName').innerText = unitName;
    document.getElementById('deputiKesepakatanParentName').value = parentName || '';
    document.getElementById('deputiKesepakatanParentKtp').value = parentKtp || '';
    document.getElementById('deputiKesepakatanParentPhone').value = parentPhone || '';
    document.getElementById('deputiKesepakatanParentAddress').value = parentAddress || '';
    document.getElementById('deputiKesepakatanTotalDebt').value = amount;
    document.getElementById('deputiKesepakatanDisplayDebt').innerText = 'Rp ' + amount.toLocaleString('id-ID');
    document.getElementById('modalDeputiKesepakatan').style.display = 'flex';
}
function closeDeputiKesepakatanModal() {
    document.getElementById('modalDeputiKesepakatan').style.display = 'none';
}

function toggleDeputiScheme() {
    const type = document.querySelector('input[name="scheme_type"]:checked').value;
    document.getElementById('deputiBoxSchemeA').style.display = (type === 'lunas_tanggal') ? 'block' : 'none';
    document.getElementById('deputiBoxSchemeB').style.display = (type === 'cicil_bulan') ? 'flex' : 'none';
    document.getElementById('deputiBoxSchemeC').style.display = (type === 'cicil_nominal') ? 'flex' : 'none';
}
</script>
