<?php
// views/audit/rekap_deputi.php
use Core\SecurityHelper;
use Core\Auth;

$baseUrl = Auth::baseUrl();
?>

<!-- Executive Banner -->
<div class="card" style="background: linear-gradient(135deg, #0f766e 0%, #1e3a8a 100%); color: #ffffff; border: none; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(15, 118, 110, 0.15);">
    <div class="card-body" style="padding: 24px 28px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: rgba(255, 255, 255, 0.15); border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">
                    <i class="fa-solid fa-chart-line" style="color: #6ee7b7;"></i> Dashboard Eksekutif Deputi Pendidikan
                </div>
                <h1 style="font-size: 22px; font-weight: 700; margin: 0 0 6px 0; color: #ffffff; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-table-list"></i>
                    Rekapitulasi Indikator Mutu Satuan Pendidikan
                </h1>
                <p style="font-size: 13px; color: #e2e8f0; margin: 0; max-width: 680px; line-height: 1.5;">
                    Pemantauan komprehensif performa manajerial, ketercapaian target SPMB, mutu akademik, tahfidz, dan efisiensi keuangan di seluruh 5 unit sekolah Yayasan Islam Al Kahfi Batam.
                </p>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="<?= $baseUrl ?>/akfi/rekap-yayasan" class="btn" style="background: rgba(255, 255, 255, 0.15); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.3);">
                    <i class="fa-solid fa-award"></i> Rekap AKFI Yayasan
                </a>
                <button type="button" onclick="window.print()" class="btn" style="background: #ffffff; color: #0f766e; font-weight: 600;">
                    <i class="fa-solid fa-print"></i> Cetak Laporan Eksekutif
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Stats Grid: Indikator Konsolidasi Yayasan -->
<div class="grid-cols-4" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fa-solid fa-user-graduate"></i>
        </div>
        <div>
            <div class="stat-value"><?= number_format($totSiswaYayasan, 0, ',', '.') ?> <span style="font-size: 13px; font-weight: 500; color: var(--text-muted);">Santri</span></div>
            <div class="stat-label">Total Santri Aktif Yayasan</div>
            <div style="font-size: 11px; color: #2563eb; font-weight: 600; margin-top: 2px;">
                5 Satuan Pendidikan
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon emerald">
            <i class="fa-solid fa-chalkboard-user"></i>
        </div>
        <div>
            <div class="stat-value"><?= number_format($totPegawaiYayasan, 0, ',', '.') ?> <span style="font-size: 13px; font-weight: 500; color: var(--text-muted);">Pegawai</span></div>
            <div class="stat-label">Dewan Guru & Tenaga Kependidikan</div>
            <div style="font-size: 11px; color: #0d9488; font-weight: 600; margin-top: 2px;">
                Status Aktif Dinas
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon amber">
            <i class="fa-solid fa-award"></i>
        </div>
        <div>
            <div class="stat-value"><?= $avgAkfiYayasan > 0 ? $avgAkfiYayasan : '-' ?> <span style="font-size: 13px; font-weight: 500; color: var(--text-muted);">/ 100</span></div>
            <div class="stat-label">Rata-rata Skor AKFI Yayasan</div>
            <div style="font-size: 11px; color: #d97706; font-weight: 600; margin-top: 2px;">
                <?= $avgAkfiYayasan >= 90 ? 'Predikat Amat Baik (A)' : ($avgAkfiYayasan >= 80 ? 'Predikat Baik (B)' : 'Dalam Penilaian') ?>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon rose">
            <i class="fa-solid fa-bullseye"></i>
        </div>
        <div>
            <div class="stat-value"><?= $spmbPctYayasan ?>%</div>
            <div class="stat-label">Capaian SPMB Yayasan</div>
            <div style="font-size: 11px; color: #e11d48; font-weight: 600; margin-top: 2px;">
                <?= $totSpmbActual ?> / <?= $totSpmbTarget ?> Target Santri
            </div>
        </div>
    </div>
</div>

<!-- Unit Performance Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(310px, 1fr)); gap: 20px; margin-bottom: 28px;">
    <?php foreach ($unitsOverview as $u): 
        $spmbPct = (float)$u['spmb_pct'];
        $akfiScore = $u['avg_akfi'] !== null ? (float)$u['avg_akfi'] : 0.0;
        $tahfidzPct = (float)$u['tahfidz_pct'];
        $tunggakanPct = (float)$u['tunggakan_pct'];
    ?>
        <div class="card" style="border-top: 4px solid <?= $spmbPct >= 100 ? '#10b981' : ($spmbPct >= 75 ? '#2563eb' : '#0d9488') ?>; margin-bottom: 0;">
            <div class="card-header" style="background: #ffffff; padding: 14px 18px;">
                <div>
                    <div style="font-weight: 700; font-size: 15px; color: var(--text-main);">
                        <?= SecurityHelper::escape($u['name']) ?>
                    </div>
                    <div style="font-size: 11px; color: var(--text-muted);">
                        Jenjang: <strong><?= SecurityHelper::escape($u['level']) ?></strong> &bull; Kode: <?= SecurityHelper::escape($u['code']) ?>
                    </div>
                </div>
                <span class="badge badge-info" style="font-size: 11px;">
                    <?= $u['total_siswa'] ?> Santri
                </span>
            </div>
            <div class="card-body" style="padding: 16px 18px;">
                
                <!-- Quick Metric Row -->
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 16px; text-align: center;">
                    <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 6px; padding: 8px 4px;">
                        <div style="font-size: 10px; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Guru & Staf</div>
                        <div style="font-size: 15px; font-weight: 700; color: #1e3a8a; margin-top: 2px;"><?= $u['total_pegawai'] ?></div>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 6px; padding: 8px 4px;">
                        <div style="font-size: 10px; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Skor AKFI</div>
                        <div style="font-size: 15px; font-weight: 700; color: #0d9488; margin-top: 2px;">
                            <?= $u['avg_akfi'] !== null ? $u['avg_akfi'] : '-' ?>
                        </div>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 6px; padding: 8px 4px;">
                        <div style="font-size: 10px; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Prestasi</div>
                        <div style="font-size: 15px; font-weight: 700; color: #b45309; margin-top: 2px;"><?= $u['prestasi_count'] ?> Piala</div>
                    </div>
                </div>

                <!-- Progress Bar: Target SPMB -->
                <div style="margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500; margin-bottom: 4px;">
                        <span>Realisasi Target SPMB</span>
                        <strong style="color: <?= $spmbPct >= 100 ? '#10b981' : '#2563eb' ?>;">
                            <?= $u['spmb_actual'] ?> / <?= $u['spmb_target'] ?> (<?= $spmbPct ?>%)
                        </strong>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" style="width: <?= min(100, $spmbPct) ?>%; background: <?= $spmbPct >= 100 ? '#10b981' : '#2563eb' ?>;"></div>
                    </div>
                </div>

                <!-- Progress Bar: Capaian Tahfidz -->
                <div style="margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 500; margin-bottom: 4px;">
                        <span>Kelulusan Ujian Tahfidz</span>
                        <strong style="color: #6366f1;"><?= $tahfidzPct ?>%</strong>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" style="width: <?= min(100, $tahfidzPct) ?>%; background: #6366f1;"></div>
                    </div>
                </div>

                <!-- Keuangan & Mutasi -->
                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 10px; border-top: 1px dashed var(--border-color); font-size: 11px;">
                    <div>
                        <span style="color: var(--text-muted);">Tunggakan SPP:</span> 
                        <strong style="color: <?= $tunggakanPct <= 5 ? '#10b981' : '#ef4444' ?>;"><?= $tunggakanPct ?>%</strong>
                    </div>
                    <div>
                        <span style="color: var(--text-muted);">Mutasi / Turnover:</span>
                        <strong style="color: #f59e0b;"><?= $u['total_turnover'] ?> Pegawai</strong>
                    </div>
                </div>

            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Matriks Komparatif Seluruh Satuan Pendidikan -->
<div class="card" style="border-top: 4px solid var(--primary);">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-table text-primary" style="margin-right: 8px;"></i>
            Matriks Komparasi Kinerja Seluruh Satuan Pendidikan
        </div>
        <span class="badge badge-secondary" style="font-size: 11px;">
            Tahun Ajaran <?= SecurityHelper::escape($activeTa['name'] ?? '-') ?> (<?= SecurityHelper::escape($activeTa['semester'] ?? '-') ?>)
        </span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table" style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">No</th>
                        <th>Satuan Pendidikan</th>
                        <th style="text-align: center;">Jenjang</th>
                        <th style="text-align: center;">Santri Aktif</th>
                        <th style="text-align: center;">Guru & Staf</th>
                        <th style="text-align: center;">Realisasi SPMB</th>
                        <th style="text-align: center;">Rata-rata AKFI</th>
                        <th style="text-align: center;">Kelulusan Tahfidz</th>
                        <th style="text-align: center;">Rasio Tunggakan</th>
                        <th style="text-align: center;">Prestasi</th>
                        <th style="text-align: center;">Status Satuan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($unitsOverview as $idx => $u): 
                        $spmbPct = (float)$u['spmb_pct'];
                        $tunggakanPct = (float)$u['tunggakan_pct'];
                        $tahfidzPct = (float)$u['tahfidz_pct'];
                    ?>
                        <tr>
                            <td style="text-align: center; color: var(--text-muted); font-size: 12px;"><?= $idx + 1 ?></td>
                            <td>
                                <div style="font-weight: 700; color: var(--text-main);">
                                    <?= SecurityHelper::escape($u['name']) ?>
                                </div>
                                <div style="font-size: 11px; color: var(--text-muted);">
                                    <?= SecurityHelper::escape($u['code']) ?> &bull; <?= SecurityHelper::escape($u['address'] ?? 'Kota Batam') ?>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-secondary" style="font-size: 11px; font-weight: 600;">
                                    <?= SecurityHelper::escape($u['level']) ?>
                                </span>
                            </td>
                            <td style="text-align: center; font-weight: 700; color: #1e3a8a;">
                                <?= $u['total_siswa'] ?>
                            </td>
                            <td style="text-align: center; font-weight: 600;">
                                <?= $u['total_pegawai'] ?>
                            </td>
                            <td style="text-align: center;">
                                <div style="font-weight: 700; color: <?= $spmbPct >= 100 ? '#10b981' : '#2563eb' ?>;">
                                    <?= $u['spmb_actual'] ?> / <?= $u['spmb_target'] ?>
                                </div>
                                <div style="font-size: 10px; color: var(--text-muted);">
                                    Capaian: <strong><?= $spmbPct ?>%</strong>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($u['avg_akfi'] !== null): ?>
                                    <strong style="color: #0d9488; font-size: 14px;"><?= $u['avg_akfi'] ?></strong>
                                    <div style="font-size: 10px; color: var(--text-muted);">
                                        <?= $u['avg_akfi'] >= 90 ? 'Grade A' : ($u['avg_akfi'] >= 80 ? 'Grade B' : 'Grade C') ?>
                                    </div>
                                <?php else: ?>
                                    <span class="badge badge-secondary" style="font-size: 10px;">Belum Ada Nilai</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <strong style="color: #6366f1;"><?= $tahfidzPct ?>%</strong>
                                <div style="font-size: 10px; color: var(--text-muted);"><?= $u['tahfidz_pass'] ?> Santri Lulus</div>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge <?= $tunggakanPct <= 5 ? 'badge-success' : 'badge-warning' ?>" style="font-size: 10px;">
                                    <?= $tunggakanPct ?>%
                                </span>
                            </td>
                            <td style="text-align: center; font-weight: 700; color: #b45309;">
                                <?= $u['prestasi_count'] ?> Kejuaraan
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-success" style="font-size: 10px;">
                                    <i class="fa-solid fa-circle-check"></i> Kondusif
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
