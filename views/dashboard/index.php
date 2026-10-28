<?php
// views/dashboard/index.php
use Core\SecurityHelper;
?>
<div class="page-header">
    <div>
        <h1 class="page-title">Assalamu'alaikum, <?= SecurityHelper::escape($currentUser['name'] ?? 'User') ?>!</h1>
        <div class="page-subtitle">
            Selamat datang di Dashboard <strong><?= SecurityHelper::escape($activeRole['display_name']) ?></strong> | Tahun Ajaran: <strong><?= SecurityHelper::escape($_SESSION['academic_year_name'] ?? '2024/2025 Genap') ?></strong>
        </div>
    </div>
    <div>
        <span class="badge badge-success page-header-badge">
            <i class="fa-solid fa-signal" style="margin-right: 4px;"></i> Sistem Aktif & Terhubung
        </span>
    </div>
</div>

<?php if ($role === 'operator_yayasan'): ?>
    <!-- STAT CARDS OPERATOR YAYASAN -->
    <div class="grid-cols-4" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fa-solid fa-school"></i></div>
            <div>
                <div class="stat-value"><?= $totalUnits ?? 5 ?></div>
                <div class="stat-label">Total Unit Sekolah</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon emerald"><i class="fa-solid fa-users-gear"></i></div>
            <div>
                <div class="stat-value"><?= $totalEmployees ?? 0 ?></div>
                <div class="stat-label">Guru & Karyawan</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon amber"><i class="fa-solid fa-user-graduate"></i></div>
            <div>
                <div class="stat-value"><?= $totalStudents ?? 0 ?></div>
                <div class="stat-label">Siswa Aktif Yayasan</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon rose"><i class="fa-solid fa-receipt"></i></div>
            <div>
                <div class="stat-value"><?= SecurityHelper::formatRupiah($totalTunggakan ?? 0) ?></div>
                <div class="stat-label">Tunggakan SPP Berjalan</div>
            </div>
        </div>
    </div>

    <div class="grid-cols-2">
        <!-- Grafik Distribusi Siswa -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fa-solid fa-chart-pie" style="color: var(--primary);"></i> Distribusi Siswa per Unit Sekolah</h3>
            </div>
            <div class="card-body">
                <canvas id="chartUnitStudents" height="200"></canvas>
            </div>
        </div>

        <!-- Status Persetujuan Pindah Tahun Ajaran -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fa-solid fa-calendar-check" style="color: var(--accent);"></i> Status Persetujuan Tahun Ajaran</h3>
                <a href="<?= $baseUrl ?>/master/academic-years" class="btn btn-outline btn-sm">Kelola</a>
            </div>
            <div class="card-body table-responsive" style="padding: 0;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Unit Sekolah</th>
                            <th>Status Persetujuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($taApprovals as $app): ?>
                            <tr>
                                <td><strong><?= SecurityHelper::escape($app['unit_name']) ?></strong></td>
                                <td>
                                    <?php if ($app['status'] === 'Setuju'): ?>
                                        <span class="badge badge-success"><i class="fa-solid fa-check"></i> Setuju</span>
                                    <?php elseif ($app['status'] === 'Tidak Setuju'): ?>
                                        <span class="badge badge-danger"><i class="fa-solid fa-xmark"></i> Ditolak</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning"><i class="fa-solid fa-hourglass-half"></i> Menunggu</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('chartUnitStudents');
        if (ctx) {
            const labels = <?= json_encode(array_column($unitStudents ?? [], 'name')) ?>;
            const data = <?= json_encode(array_map('intval', array_column($unitStudents ?? [], 'count'))) ?>;
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Siswa',
                        data: data,
                        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899'],
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }
    });
    </script>

<?php elseif ($role === 'direktur_pendidikan'): ?>
    <!-- DASHBOARD EKSEKUTIF DIREKTUR PENDIDIKAN YAYASAN -->
    <div class="grid-cols-4" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fa-solid fa-school"></i></div>
            <div>
                <div class="stat-value"><?= $totalUnits ?? 5 ?> Unit</div>
                <div class="stat-label">Lembaga Pendidikan Yayasan</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon emerald"><i class="fa-solid fa-users-gear"></i></div>
            <div>
                <div class="stat-value"><?= $totalEmployees ?? 0 ?> Pegawai</div>
                <div class="stat-label">Guru & Karyawan Yayasan</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon amber"><i class="fa-solid fa-signature"></i></div>
            <div>
                <div class="stat-value"><?= $pendingHrApproval ?? 0 ?> Berkas</div>
                <div class="stat-label">Menunggu Pengesahan HR</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon teal" style="background: #ccfbf1; color: #0f766e;"><i class="fa-solid fa-award"></i></div>
            <div>
                <div class="stat-value"><?= number_format((float)($avgAkfi ?? 0), 1) ?></div>
                <div class="stat-label">Rata-rata Skor AKFI Final</div>
            </div>
        </div>
    </div>

    <div class="grid-cols-2">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-list-check" style="color: var(--primary); margin-right: 6px;"></i>
                    Rata-rata Kinerja AKFI per Unit Lembaga
                </h3>
                <a href="<?= $baseUrl ?>/akfi/rekap-yayasan" class="btn btn-outline btn-sm">Lihat Rekap</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Unit Sekolah</th>
                            <th style="text-align: center;">Jumlah Pegawai</th>
                            <th style="text-align: center;">Rata-rata AKFI</th>
                            <th style="text-align: center;">Predikat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($unitAkfiStats)): ?>
                            <tr><td colspan="4" style="text-align: center; padding: 20px; color: var(--text-muted);">Belum ada data evaluasi AKFI berjalan.</td></tr>
                        <?php else: ?>
                            <?php foreach ($unitAkfiStats as $st): ?>
                                <?php 
                                $avg = (float)($st['avg_score'] ?? 0);
                                $pred = $avg >= 90 ? 'A' : ($avg >= 80 ? 'B' : ($avg >= 70 ? 'C' : 'D'));
                                ?>
                                <tr>
                                    <td><strong><?= SecurityHelper::escape($st['name']) ?></strong></td>
                                    <td style="text-align: center;"><?= (int)$st['total_emp'] ?> orang</td>
                                    <td style="text-align: center; font-weight: 700; color: var(--primary);"><?= number_format($avg, 1) ?></td>
                                    <td style="text-align: center;">
                                        <span class="badge badge-success"><?= $pred ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-bolt" style="color: var(--warning); margin-right: 6px;"></i>
                    Aksi Cepat Direktur Pendidikan
                </h3>
            </div>
            <div class="card-body" style="display: flex; flex-direction: column; gap: 12px;">
                <a href="<?= $baseUrl ?>/akfi/approval-hr" class="btn btn-outline" style="justify-content: space-between; padding: 12px 16px;">
                    <span><i class="fa-solid fa-signature" style="color: var(--primary); margin-right: 8px;"></i> Pengesahan Resmi Rapor AKFI (Tanda Tangan HR)</span>
                    <span class="badge badge-warning"><?= $pendingHrApproval ?? 0 ?> menunggu</span>
                </a>
                <a href="<?= $baseUrl ?>/akfi/rekap-yayasan" class="btn btn-outline" style="justify-content: space-between; padding: 12px 16px;">
                    <span><i class="fa-solid fa-file-excel" style="color: var(--accent); margin-right: 8px;"></i> Unduh Rekapitulasi AKFI Seluruh Lembaga (Excel)</span>
                    <i class="fa-solid fa-angle-right"></i>
                </a>
                <a href="<?= $baseUrl ?>/spmb/target" class="btn btn-outline" style="justify-content: space-between; padding: 12px 16px;">
                    <span><i class="fa-solid fa-bullseye" style="color: #8b5cf6; margin-right: 8px;"></i> Pantau Target & Realisasi Pendaftaran SPMB</span>
                    <i class="fa-solid fa-angle-right"></i>
                </a>
                <a href="<?= $baseUrl ?>/akfi/kanit-preview" class="btn btn-outline" style="justify-content: space-between; padding: 12px 16px;">
                    <span><i class="fa-solid fa-chart-line" style="color: #0284c7; margin-right: 8px;"></i> Pantau Pratinjau Kinerja Kepala Sekolah (KS)</span>
                    <i class="fa-solid fa-angle-right"></i>
                </a>
            </div>
        </div>
    </div>

<?php elseif ($role === 'deputi_pendidikan'): ?>
    <!-- DASHBOARD DEPUTI DIREKTUR PENDIDIKAN YAYASAN -->
    <div class="grid-cols-4" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon emerald"><i class="fa-solid fa-user-check"></i></div>
            <div>
                <div class="stat-value"><?= number_format((float)($avgTeacherAttOverall ?? 0), 1) ?>%</div>
                <div class="stat-label">Kehadiran Guru Yayasan (<?= $totalGuruYayasan ?? 0 ?> Guru)</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fa-solid fa-users"></i></div>
            <div>
                <div class="stat-value"><?= number_format((float)($avgStudentAttOverall ?? 0), 1) ?>%</div>
                <div class="stat-label">Kehadiran Siswa Yayasan (<?= $totalSiswaYayasan ?? 0 ?> Siswa)</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon amber"><i class="fa-solid fa-folder-check"></i></div>
            <div>
                <div class="stat-value"><?= $pendingAdminKs ?? 0 ?> Berkas</div>
                <div class="stat-label">Admin KS Perlu Review</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon rose"><i class="fa-solid fa-trophy"></i></div>
            <div>
                <div class="stat-value"><?= $totalJuaraOsn ?? 0 ?> Juara</div>
                <div class="stat-label">Prestasi Siswa (OSN/O2SN/FLS2N)</div>
            </div>
        </div>
    </div>

    <!-- 1. MONITORING & PERSENTASE KEHADIRAN SETIAP SEKOLAH -->
    <div style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <div>
                <h2 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-clipboard-user" style="color: var(--primary);"></i>
                    Monitoring & Persentase Kehadiran Guru dan Siswa Setiap Sekolah
                </h2>
                <div style="font-size: 13px; color: var(--text-muted);">
                    Tahun Ajaran: <strong><?= SecurityHelper::escape($_SESSION['academic_year_name'] ?? '2024/2025 Genap') ?></strong> | Perbandingan real-time seluruh satuan lembaga pendidikan Yayasan
                </div>
            </div>
            <div>
                <span class="badge badge-info" style="font-size: 12px; padding: 6px 12px;">
                    <i class="fa-solid fa-check-double"></i> Rekap Seluruh Unit (<?= count($teacherAttendanceByUnit ?? []) ?> Lembaga)
                </span>
            </div>
        </div>

        <div class="grid-cols-2" style="margin-bottom: 20px;">
            <!-- Grafik Persentase Kehadiran Guru Setiap Sekolah -->
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%); border-bottom: 1px solid #bbf7d0;">
                    <div>
                        <h3 class="card-title" style="color: #166534;">
                            <i class="fa-solid fa-chalkboard-user" style="color: #16a34a; margin-right: 6px;"></i>
                            Persentase Kehadiran Guru Setiap Sekolah
                        </h3>
                        <div style="font-size: 12px; color: #15803d;">Tingkat presensi tenaga pendidik per unit satuan pendidikan</div>
                    </div>
                    <span class="badge badge-success" style="font-size: 11px;">Rata-rata: <?= number_format((float)($avgTeacherAttOverall ?? 0), 1) ?>%</span>
                </div>
                <div class="card-body">
                    <canvas id="chartDeputiTeacherAtt" height="200"></canvas>
                </div>
            </div>

            <!-- Grafik Persentase Kehadiran Siswa Setiap Sekolah -->
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%); border-bottom: 1px solid #bfdbfe;">
                    <div>
                        <h3 class="card-title" style="color: #1e40af;">
                            <i class="fa-solid fa-user-graduate" style="color: #2563eb; margin-right: 6px;"></i>
                            Persentase Kehadiran Siswa Setiap Sekolah
                        </h3>
                        <div style="font-size: 12px; color: #1d4ed8;">Tingkat presensi dan keaktifan peserta didik per unit satuan pendidikan</div>
                    </div>
                    <span class="badge badge-info" style="font-size: 11px;">Rata-rata: <?= number_format((float)($avgStudentAttOverall ?? 0), 1) ?>%</span>
                </div>
                <div class="card-body">
                    <canvas id="chartDeputiStudentAtt" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Tabel Rincian Kehadiran Guru & Siswa per Unit -->
        <div class="card" style="box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-table-list" style="color: var(--primary); margin-right: 6px;"></i>
                    Tabel Rekapitulasi Rincian Presensi Guru & Siswa per Unit Sekolah
                </h3>
                <span style="font-size: 12px; color: var(--text-muted);">Diperbarui secara live count</span>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr style="background: #f8fafc;">
                                <th width="24%">Satuan Unit Lembaga</th>
                                <th width="12%" style="text-align: center;">Total Guru</th>
                                <th width="20%">Presensi Guru (Hadir/S/I/A)</th>
                                <th width="12%" style="text-align: center;">% Kehadiran Guru</th>
                                <th width="12%" style="text-align: center;">Total Siswa</th>
                                <th width="12%" style="text-align: center;">% Kehadiran Siswa</th>
                                <th width="8%" style="text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($teacherAttendanceByUnit as $idx => $tg): ?>
                                <?php 
                                    $stdU = $studentAttendanceByUnit[$idx] ?? null;
                                    $gPct = (float)$tg['pct'];
                                    $sPct = (float)($stdU['pct'] ?? 0);
                                ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: #1e293b;"><?= SecurityHelper::escape($tg['unit_name']) ?></div>
                                        <div style="font-size: 11px; color: var(--text-muted);">Kode Unit: <code><?= SecurityHelper::escape($tg['unit_code']) ?></code></div>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge badge-secondary" style="font-size: 12px; font-weight: 600;"><?= $tg['total_guru'] ?> Guru</span>
                                    </td>
                                    <td>
                                        <div style="font-size: 12px; font-weight: 600; color: #166534;">
                                            <i class="fa-solid fa-check" style="color: #16a34a;"></i> Hadir: <?= $tg['hadir'] ?> hari
                                        </div>
                                        <div style="font-size: 11px; color: var(--text-muted); display: flex; gap: 6px; margin-top: 2px;">
                                            <span>S: <?= $tg['sakit'] ?></span> | 
                                            <span>I: <?= $tg['izin'] ?></span> | 
                                            <span>A: <?= $tg['alpha'] ?></span> | 
                                            <span style="color: #ea580c;">Telat: <?= $tg['terlambat'] ?>m</span>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="font-size: 14px; font-weight: 700; color: <?= $gPct >= 95 ? '#16a34a' : ($gPct >= 90 ? '#2563eb' : '#d97706') ?>;">
                                            <?= number_format($gPct, 1) ?>%
                                        </div>
                                        <div style="background: #e2e8f0; height: 6px; border-radius: 3px; overflow: hidden; margin-top: 4px;">
                                            <div style="width: <?= min(100, $gPct) ?>%; background: <?= $gPct >= 95 ? '#16a34a' : ($gPct >= 90 ? '#2563eb' : '#d97706') ?>; height: 100%;"></div>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge badge-info" style="font-size: 12px; font-weight: 600;"><?= $stdU['total_siswa'] ?? 0 ?> Siswa</span>
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="font-size: 14px; font-weight: 700; color: <?= $sPct >= 95 ? '#16a34a' : ($sPct >= 90 ? '#2563eb' : '#d97706') ?>;">
                                            <?= number_format($sPct, 1) ?>%
                                        </div>
                                        <div style="background: #e2e8f0; height: 6px; border-radius: 3px; overflow: hidden; margin-top: 4px;">
                                            <div style="width: <?= min(100, $sPct) ?>%; background: <?= $sPct >= 95 ? '#16a34a' : ($sPct >= 90 ? '#2563eb' : '#d97706') ?>; height: 100%;"></div>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($gPct >= 95 && $sPct >= 95): ?>
                                            <span class="badge badge-success" style="font-size: 10px;"><i class="fa-solid fa-star"></i> Prima</span>
                                        <?php elseif ($gPct >= 90 && $sPct >= 90): ?>
                                            <span class="badge badge-info" style="font-size: 10px;"><i class="fa-solid fa-check"></i> Baik</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning" style="font-size: 10px;"><i class="fa-solid fa-triangle-exclamation"></i> Pantau</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="grid-cols-2">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-user-graduate" style="color: var(--primary); margin-right: 6px;"></i>
                    Ringkasan Kinerja AKFI Kepala Sekolah
                </h3>
                <a href="<?= $baseUrl ?>/akfi/kanit-preview" class="btn btn-outline btn-sm">Lihat Detail</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kepala Sekolah</th>
                            <th>Unit Lembaga</th>
                            <th style="text-align: center;">Skor AKFI</th>
                            <th style="text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ksAssessments)): ?>
                            <tr><td colspan="4" style="text-align: center; padding: 20px; color: var(--text-muted);">Belum ada data evaluasi Kepala Sekolah.</td></tr>
                        <?php else: ?>
                            <?php foreach ($ksAssessments as $ks): ?>
                                <tr>
                                    <td><strong><?= SecurityHelper::escape($ks['nama']) ?></strong></td>
                                    <td><span class="badge badge-info"><?= SecurityHelper::escape($ks['unit_name']) ?></span></td>
                                    <td style="text-align: center; font-weight: 700; color: var(--primary);"><?= number_format((float)$ks['total_score'], 1) ?></td>
                                    <td style="text-align: center;">
                                        <span class="badge badge-secondary"><?= SecurityHelper::escape($ks['status']) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-bolt" style="color: var(--warning); margin-right: 6px;"></i>
                    Menu Operasional Deputi Pendidikan
                </h3>
            </div>
            <div class="card-body" style="display: flex; flex-direction: column; gap: 12px;">
                <a href="<?= $baseUrl ?>/administrasi/review-ks" class="btn btn-outline" style="justify-content: space-between; padding: 12px 16px;">
                    <span><i class="fa-solid fa-folder-check" style="color: var(--primary); margin-right: 8px;"></i> Verifikasi 18 Dokumen Administrasi Kepala Sekolah</span>
                    <span class="badge badge-warning"><?= $pendingAdminKs ?? 0 ?> berkas</span>
                </a>
                <a href="<?= $baseUrl ?>/akfi/penilaian-sikap-ks" class="btn btn-outline" style="justify-content: space-between; padding: 12px 16px;">
                    <span><i class="fa-solid fa-user-pen" style="color: var(--accent); margin-right: 8px;"></i> Penilaian Sikap Kepemimpinan Islami KS</span>
                    <i class="fa-solid fa-angle-right"></i>
                </a>
                <a href="<?= $baseUrl ?>/akfi/kanit-preview" class="btn btn-outline" style="justify-content: space-between; padding: 12px 16px;">
                    <span><i class="fa-solid fa-chart-line" style="color: #8b5cf6; margin-right: 8px;"></i> Pratinjau Rapor AKFI Kepala Sekolah</span>
                    <i class="fa-solid fa-angle-right"></i>
                </a>
                <a href="<?= $baseUrl ?>/spmb/target" class="btn btn-outline" style="justify-content: space-between; padding: 12px 16px;">
                    <span><i class="fa-solid fa-bullseye" style="color: #0284c7; margin-right: 8px;"></i> Evaluasi Target & Kuota SPMB Unit</span>
                    <i class="fa-solid fa-angle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Chart.js Script untuk Deputi Pendidikan -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const teacherLabels = <?= json_encode(array_column($teacherAttendanceByUnit ?? [], 'unit_name')) ?>;
        const teacherPcts = <?= json_encode(array_map('floatval', array_column($teacherAttendanceByUnit ?? [], 'pct'))) ?>;

        const studentLabels = <?= json_encode(array_column($studentAttendanceByUnit ?? [], 'unit_name')) ?>;
        const studentPcts = <?= json_encode(array_map('floatval', array_column($studentAttendanceByUnit ?? [], 'pct'))) ?>;

        // Chart Kehadiran Guru
        const ctxTeacher = document.getElementById('chartDeputiTeacherAtt');
        if (ctxTeacher) {
            new Chart(ctxTeacher, {
                type: 'bar',
                data: {
                    labels: teacherLabels,
                    datasets: [{
                        label: 'Persentase Kehadiran Guru (%)',
                        data: teacherPcts,
                        backgroundColor: teacherPcts.map(val => val >= 95 ? '#10b981' : (val >= 90 ? '#3b82f6' : '#f59e0b')),
                        borderRadius: 6,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ' Kehadiran Guru: ' + ctx.raw + '%'
                            }
                        }
                    },
                    scales: {
                        y: {
                            min: 50,
                            max: 100,
                            ticks: { callback: v => v + '%' }
                        }
                    }
                }
            });
        }

        // Chart Kehadiran Siswa
        const ctxStudent = document.getElementById('chartDeputiStudentAtt');
        if (ctxStudent) {
            new Chart(ctxStudent, {
                type: 'bar',
                data: {
                    labels: studentLabels,
                    datasets: [{
                        label: 'Persentase Kehadiran Siswa (%)',
                        data: studentPcts,
                        backgroundColor: studentPcts.map(val => val >= 95 ? '#06b6d4' : (val >= 90 ? '#6366f1' : '#f59e0b')),
                        borderRadius: 6,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ' Kehadiran Siswa: ' + ctx.raw + '%'
                            }
                        }
                    },
                    scales: {
                        y: {
                            min: 50,
                            max: 100,
                            ticks: { callback: v => v + '%' }
                        }
                    }
                }
            });
        }
    });
    </script>

<?php elseif ($role === 'kepala_sekolah'): ?>
    <!-- DASHBOARD KEPALA SEKOLAH - ANALISIS LENGKAP & COMPARATIVE VIEW -->

    <!-- BANNER PERBANDINGAN TAHUN AJARAN BERJALAN VS DIARSIP -->
    <div class="card" style="margin-bottom: 24px; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #ffffff; border: 1px solid #334155; padding: 18px 24px; border-radius: 10px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="badge" style="background: #10b981; color: white; font-size: 11px; padding: 4px 8px;">
                        <i class="fa-solid fa-circle-check"></i> TA Aktif: <?= SecurityHelper::escape($_SESSION['academic_year_name'] ?? '2024/2025 Genap') ?>
                    </span>
                    <span class="badge" style="background: #3b82f6; color: white; font-size: 11px; padding: 4px 8px;">
                        <i class="fa-solid fa-school"></i> <?= SecurityHelper::escape($currentUnit['name'] ?? 'Unit Sekolah') ?>
                    </span>
                    <span class="badge" style="background: #64748b; color: white; font-size: 11px; padding: 4px 8px;">
                        <i class="fa-solid fa-box-archive"></i> Tahun Ajaran Arsip Tersedia
                    </span>
                </div>
                <h2 style="font-size: 19px; font-weight: 700; margin: 8px 0 4px 0; color: #f8fafc;">
                    Dashboard Kepala Sekolah: Live Monitoring & Evaluasi Mutu Sekolah
                </h2>
                <div style="font-size: 13px; color: #94a3b8;">
                    Data di bawah ini dihitung <strong>secara khusus dan eksklusif hanya untuk sekolah Anda</strong>. Anda dapat melihat perbandingan data antara Tahun Ajaran berjalan dengan Tahun Ajaran yang diarsipkan.
                </div>
            </div>

            <!-- Tombol Switcher Cepat ke TA Arsip untuk Kemudahan Simulasi -->
            <div style="display: flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.08); padding: 8px 14px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.15);">
                <div style="font-size: 12px; color: #cbd5e1; font-weight: 600;">
                    <i class="fa-solid fa-clock-rotate-left"></i> Ganti Tahun Ajaran:
                </div>
                <form method="POST" action="<?= $baseUrl ?>/switch-ta" style="display: inline-flex; align-items: center; gap: 6px; margin: 0;">
                    <?= $csrfField ?>
                    <select name="academic_year_id" class="header-select" style="font-size: 12px; font-weight: 700; background: #ffffff; color: #0f172a; padding: 4px 8px; border-radius: 6px;" onchange="this.form.submit()">
                        <?php foreach ($globalAcademicYears as $taItem): ?>
                            <option value="<?= $taItem['id'] ?>" <?= ((int)$taItem['id'] === (int)($currentTaId ?? 1)) ? 'selected' : '' ?>>
                                <?= SecurityHelper::escape($taItem['name']) ?> <?= SecurityHelper::escape($taItem['semester']) ?> <?= $taItem['is_active'] ? '(★ Berjalan)' : '(🔒 Arsip)' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>
    </div>

    <!-- WIDGET NOTIFIKASI COUNTDOWN AKREDITASI & IZIN OPERASIONAL -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <!-- KARTU AKREDITASI -->
        <div class="card" style="margin-bottom: 0; position: relative; overflow: hidden; border-radius: 12px; border: 1px solid <?= $akreditasiStatus === 'danger' ? '#fecaca' : ($akreditasiStatus === 'warning' ? '#fde68a' : 'var(--border-color)') ?>; background: <?= $akreditasiStatus === 'danger' ? '#fef2f2' : ($akreditasiStatus === 'warning' ? '#fffbeb' : 'var(--bg-card)') ?>; padding: 20px 20px 16px 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background: <?= $akreditasiStatus === 'expired' || $akreditasiStatus === 'danger' ? '#ef4444' : ($akreditasiStatus === 'warning' ? '#f59e0b' : '#10b981') ?>;"></div>
            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                <div style="display: flex; gap: 12px; align-items: center;">
                    <div style="width: 44px; height: 44px; border-radius: 10px; background: <?= $akreditasiStatus === 'danger' ? '#fee2e2' : ($akreditasiStatus === 'warning' ? '#fef3c7' : '#ecfdf5') ?>; color: <?= $akreditasiStatus === 'danger' ? '#dc2626' : ($akreditasiStatus === 'warning' ? '#d97706' : '#059669') ?>; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fa-solid fa-award"></i>
                    </div>
                    <div>
                        <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--text-muted); letter-spacing: 0.5px;">Status Akreditasi Sekolah</div>
                        <div style="font-size: 16px; font-weight: 700; color: var(--text-main); margin-top: 2px;">
                            Peringkat <?= !empty($currentUnit['akreditasi']) ? SecurityHelper::escape($currentUnit['akreditasi']) : '-' ?>
                            <?php if (!empty($currentUnit['nomor_akreditasi'])): ?>
                                <span style="font-size: 12px; font-weight: 500; color: var(--text-muted);">(No: <?= SecurityHelper::escape($currentUnit['nomor_akreditasi']) ?>)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div>
                    <?php if ($akreditasiStatus === 'expired'): ?>
                        <span class="badge badge-danger"><i class="fa-solid fa-triangle-exclamation"></i> KEDALUWARSA</span>
                    <?php elseif ($akreditasiStatus === 'danger'): ?>
                        <span class="badge badge-danger"><i class="fa-solid fa-clock"></i> SEGERA HABIS</span>
                    <?php elseif ($akreditasiStatus === 'warning'): ?>
                        <span class="badge badge-warning"><i class="fa-solid fa-hourglass-half"></i> PERSIAPKAN</span>
                    <?php elseif ($akreditasiStatus === 'valid'): ?>
                        <span class="badge badge-success"><i class="fa-solid fa-check"></i> AKTIF</span>
                    <?php else: ?>
                        <span class="badge badge-secondary">BELUM DIATUR</span>
                    <?php endif; ?>
                </div>
            </div>
            <div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 12px; color: var(--text-muted);">
                    <i class="fa-regular fa-calendar" style="margin-right: 4px;"></i> Berlaku s.d: <strong><?= !empty($currentUnit['akreditasi_expired_at']) ? date('d F Y', strtotime($currentUnit['akreditasi_expired_at'])) : 'Belum diatur' ?></strong>
                </div>
                <?php if ($akreditasiDaysLeft !== null): ?>
                    <div style="font-weight: 700; font-size: 13px; color: <?= $akreditasiDaysLeft <= 90 ? '#dc2626' : ($akreditasiDaysLeft <= 180 ? '#d97706' : '#059669') ?>;">
                        <i class="fa-solid fa-stopwatch" style="margin-right: 4px;"></i>
                        <?= $akreditasiDaysLeft < 0 ? abs($akreditasiDaysLeft) . ' hari lewat' : $akreditasiDaysLeft . ' hari lagi' ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- KARTU IZIN OPERASIONAL -->
        <div class="card" style="margin-bottom: 0; position: relative; overflow: hidden; border-radius: 12px; border: 1px solid <?= $izinStatus === 'danger' ? '#fecaca' : ($izinStatus === 'warning' ? '#fde68a' : 'var(--border-color)') ?>; background: <?= $izinStatus === 'danger' ? '#fef2f2' : ($izinStatus === 'warning' ? '#fffbeb' : 'var(--bg-card)') ?>; padding: 20px 20px 16px 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background: <?= $izinStatus === 'expired' || $izinStatus === 'danger' ? '#ef4444' : ($izinStatus === 'warning' ? '#f59e0b' : '#3b82f6') ?>;"></div>
            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                <div style="display: flex; gap: 12px; align-items: center;">
                    <div style="width: 44px; height: 44px; border-radius: 10px; background: <?= $izinStatus === 'danger' ? '#fee2e2' : ($izinStatus === 'warning' ? '#fef3c7' : '#eff6ff') ?>; color: <?= $izinStatus === 'danger' ? '#dc2626' : ($izinStatus === 'warning' ? '#d97706' : '#2563eb') ?>; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fa-solid fa-file-shield"></i>
                    </div>
                    <div>
                        <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--text-muted); letter-spacing: 0.5px;">Izin Operasional & Legalitas</div>
                        <div style="font-size: 15px; font-weight: 700; color: var(--text-main); margin-top: 2px;">
                            <?= !empty($currentUnit['nomor_izin_operasional']) ? SecurityHelper::escape($currentUnit['nomor_izin_operasional']) : 'Belum diisi' ?>
                            <?php if (!empty($currentUnit['npsn'])): ?>
                                <span style="font-size: 12px; font-weight: 500; color: var(--text-muted);">| NPSN: <?= SecurityHelper::escape($currentUnit['npsn']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div>
                    <?php if ($izinStatus === 'expired'): ?>
                        <span class="badge badge-danger"><i class="fa-solid fa-triangle-exclamation"></i> KEDALUWARSA</span>
                    <?php elseif ($izinStatus === 'danger'): ?>
                        <span class="badge badge-danger"><i class="fa-solid fa-clock"></i> SEGERA HABIS</span>
                    <?php elseif ($izinStatus === 'warning'): ?>
                        <span class="badge badge-warning"><i class="fa-solid fa-hourglass-half"></i> PERSIAPKAN</span>
                    <?php elseif ($izinStatus === 'valid'): ?>
                        <span class="badge badge-info"><i class="fa-solid fa-check"></i> AKTIF</span>
                    <?php else: ?>
                        <span class="badge badge-secondary">BELUM DIATUR</span>
                    <?php endif; ?>
                </div>
            </div>
            <div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 12px; color: var(--text-muted);">
                    <i class="fa-regular fa-calendar" style="margin-right: 4px;"></i> Berlaku s.d: <strong><?= !empty($currentUnit['izin_operasional_expired_at']) ? date('d F Y', strtotime($currentUnit['izin_operasional_expired_at'])) : 'Belum diatur' ?></strong>
                </div>
                <?php if ($izinDaysLeft !== null): ?>
                    <div style="font-weight: 700; font-size: 13px; color: <?= $izinDaysLeft <= 90 ? '#dc2626' : ($izinDaysLeft <= 180 ? '#d97706' : '#2563eb') ?>;">
                        <i class="fa-solid fa-stopwatch" style="margin-right: 4px;"></i>
                        <?= $izinDaysLeft < 0 ? abs($izinDaysLeft) . ' hari lewat' : $izinDaysLeft . ' hari lagi' ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- STAT CARDS KEPALA SEKOLAH -->
    <div class="grid-cols-4" style="margin-bottom: 24px;">
        <!-- Live Count Siswa Aktif -->
        <div class="stat-card" style="border-left: 4px solid #3b82f6;">
            <div class="stat-icon blue"><i class="fa-solid fa-users"></i></div>
            <div>
                <div style="display: flex; align-items: baseline; gap: 6px;">
                    <div class="stat-value" style="color: #1e40af;"><?= $liveStudentCount ?? 0 ?></div>
                    <span class="badge badge-success" style="font-size: 10px; padding: 2px 6px;">Live Count</span>
                </div>
                <div class="stat-label">Siswa Aktif di Sekolah Anda</div>
            </div>
        </div>

        <!-- Total Guru & Karyawan -->
        <div class="stat-card" style="border-left: 4px solid #10b981;">
            <div class="stat-icon emerald"><i class="fa-solid fa-chalkboard-user"></i></div>
            <div>
                <div class="stat-value" style="color: #065f46;"><?= $totalTeachers ?? 0 ?></div>
                <div class="stat-label">Total Guru &amp; Tenaga Pendidik</div>
            </div>
        </div>

        <!-- Laporan Tunggakan SPP -->
        <div class="stat-card" style="border-left: 4px solid #ef4444;">
            <div class="stat-icon rose"><i class="fa-solid fa-money-bill-wave"></i></div>
            <div>
                <div class="stat-value" style="color: #991b1b;"><?= SecurityHelper::formatRupiah($billsStats['total_unpaid'] ?? ($unpaidBills['s'] ?? 0)) ?></div>
                <div class="stat-label"><?= $billsStats['count_unpaid'] ?? ($unpaidBills['c'] ?? 0) ?> Siswa Tertunggak</div>
            </div>
        </div>

        <!-- Nilai Rata-rata Supervisi -->
        <div class="stat-card" style="border-left: 4px solid #8b5cf6;">
            <div class="stat-icon" style="background: #ede9fe; color: #6d28d9;"><i class="fa-solid fa-clipboard-check"></i></div>
            <div>
                <div class="stat-value" style="color: #5b21b6;"><?= $supStats['avg_final_score'] ?? 0 ?> / 100</div>
                <div class="stat-label">Rata-rata Supervisi (<?= $supStats['total_sessions'] ?? 0 ?> Sesi)</div>
            </div>
        </div>
    </div>

    <!-- 1. GRAFIK PERGERAKAN JUMLAH SISWA LIVE COUNT & REKAP BULANAN -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
                <h3 class="card-title">
                    <i class="fa-solid fa-arrow-trend-up" style="color: #2563eb; margin-right: 6px;"></i>
                    Grafik Pergerakan Jumlah Siswa (Live Count &amp; Rekapitulasi Bulanan)
                </h3>
                <div style="font-size: 12px; color: var(--text-muted);">
                    Hanya menghitung siswa yang terdaftar di sekolah ini | Live count saat ini: <strong><?= $liveStudentCount ?? 0 ?> Siswa</strong>
                </div>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <span class="badge badge-success" style="font-size: 11px; padding: 4px 8px;">
                    <i class="fa-solid fa-user-plus"></i> Masuk: +<?= $totalMasuk ?? 0 ?>
                </span>
                <span class="badge badge-danger" style="font-size: 11px; padding: 4px 8px;">
                    <i class="fa-solid fa-user-minus"></i> Keluar/Pindah: -<?= $totalKeluar ?? 0 ?>
                </span>
                <span class="badge badge-primary" style="font-size: 11px; padding: 4px 8px;">
                    <i class="fa-solid fa-signal"></i> Total Terkini: <?= $liveStudentCount ?? 0 ?>
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="grid-cols-3" style="gap: 20px;">
                <div style="grid-column: span 2;">
                    <canvas id="chartStudentMovement" height="150"></canvas>
                </div>
                <!-- Tabel Rekap Mini Pergerakan Bulanan -->
                <div>
                    <div style="font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 8px;">
                        <i class="fa-solid fa-calendar-days"></i> Rekap Bulanan Siswa
                    </div>
                    <div style="max-height: 250px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 6px;">
                        <table class="table" style="font-size: 12px;">
                            <thead>
                                <tr style="background: #f8fafc;">
                                    <th>Bulan</th>
                                    <th style="text-align: center; color: #16a34a;">Masuk</th>
                                    <th style="text-align: center; color: #dc2626;">Keluar</th>
                                    <th style="text-align: center; font-weight: 700;">Saat Ini</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($studentMovements as $mov): ?>
                                    <tr>
                                        <td><strong><?= SecurityHelper::escape($mov['month_name']) ?></strong></td>
                                        <td style="text-align: center; color: #16a34a; font-weight: 600;">
                                            <?= $mov['masuk'] > 0 ? '+'.$mov['masuk'] : '0' ?>
                                        </td>
                                        <td style="text-align: center; color: #dc2626; font-weight: 600;">
                                            <?= $mov['keluar'] > 0 ? '-'.$mov['keluar'] : '0' ?>
                                        </td>
                                        <td style="text-align: center; font-weight: 700; color: #1e40af;">
                                            <?= $mov['current_count'] ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. KONDISI TENAGA PENDIDIK (GRAFIK GENDER & TINGKAT PENDIDIKAN) -->
    <div class="grid-cols-2" style="margin-bottom: 24px;">
        <!-- Grafik Berdasarkan Gender -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-venus-mars" style="color: #ec4899; margin-right: 6px;"></i>
                    Kondisi Tenaga Pendidik Berdasarkan Gender
                </h3>
                <span class="badge badge-secondary"><?= $totalTeachers ?? 0 ?> Pegawai</span>
            </div>
            <div class="card-body">
                <div style="display: flex; align-items: center; justify-content: space-around; flex-wrap: wrap;">
                    <div style="max-width: 220px; width: 100%;">
                        <canvas id="chartTeacherGender" height="180"></canvas>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <div style="padding: 10px 16px; background: #eff6ff; border-radius: 8px; border-left: 4px solid #3b82f6;">
                            <div style="font-size: 11px; color: #1e40af; font-weight: 600;"><i class="fa-solid fa-mars"></i> Laki-laki</div>
                            <div style="font-size: 18px; font-weight: 700; color: #1e3a8a;"><?= $empGenderCounts['L'] ?? 0 ?> Orang</div>
                            <div style="font-size: 11px; color: #3b82f6;">
                                <?= ($totalTeachers > 0) ? round((($empGenderCounts['L'] ?? 0) / $totalTeachers) * 100, 1) : 0 ?>% dari total
                            </div>
                        </div>
                        <div style="padding: 10px 16px; background: #fdf2f8; border-radius: 8px; border-left: 4px solid #ec4899;">
                            <div style="font-size: 11px; color: #9d174d; font-weight: 600;"><i class="fa-solid fa-venus"></i> Perempuan</div>
                            <div style="font-size: 18px; font-weight: 700; color: #831843;"><?= $empGenderCounts['P'] ?? 0 ?> Orang</div>
                            <div style="font-size: 11px; color: #ec4899;">
                                <?= ($totalTeachers > 0) ? round((($empGenderCounts['P'] ?? 0) / $totalTeachers) * 100, 1) : 0 ?>% dari total
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafik Berdasarkan Tingkat Pendidikan -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-graduation-cap" style="color: #6366f1; margin-right: 6px;"></i>
                    Kondisi Tenaga Pendidik Berdasarkan Tingkat Pendidikan
                </h3>
                <span class="badge badge-info">Master Data Pegawai</span>
            </div>
            <div class="card-body">
                <canvas id="chartTeacherEducation" height="180"></canvas>
            </div>
        </div>
    </div>

    <!-- 3. GRAFIK CAPAIAN NILAI SELURUH MAPEL (FILTER PER MAPEL & FILTER PERIODIK) -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <h3 class="card-title">
                    <i class="fa-solid fa-chart-line" style="color: #059669; margin-right: 6px;"></i>
                    Grafik Capaian Nilai Siswa (Filter Mapel &amp; Periodik)
                </h3>
                <div style="font-size: 12px; color: var(--text-muted);">
                    Pantau daya serap kurikulum dan ketuntasan KKM (Standar KKM: <strong>75.00</strong>)
                </div>
            </div>

            <!-- Kontrol Filter Interaktif -->
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <!-- Filter Mapel -->
                <div style="display: flex; align-items: center; gap: 6px;">
                    <label style="font-size: 12px; font-weight: 600; color: #475569;"><i class="fa-solid fa-book"></i> Mapel:</label>
                    <select id="filterGradeSubject" class="header-select" style="font-size: 12px; font-weight: 600; padding: 5px 10px;" onchange="updateGradesChart()">
                        <option value="ALL">-- Seluruh Mata Pelajaran --</option>
                        <?php foreach ($subjects as $sb): ?>
                            <option value="<?= $sb['id'] ?>"><?= SecurityHelper::escape($sb['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Periodik: Per Bulan / Per 3 Bulan / Per Semester / Per Tahun -->
                <div style="display: flex; align-items: center; gap: 6px;">
                    <label style="font-size: 12px; font-weight: 600; color: #475569;"><i class="fa-solid fa-clock"></i> Periode:</label>
                    <select id="filterGradePeriod" class="header-select" style="font-size: 12px; font-weight: 600; padding: 5px 10px; color: #0284c7; border-color: #bae6fd; background: #f0f9ff;" onchange="updateGradesChart()">
                        <option value="monthly" selected>Per Bulan (Bulan 1 s/d 12)</option>
                        <option value="quarterly">Per 3 Bulan (Triwulan 1 s/d 4)</option>
                        <option value="semester">Per Semester (Ganjil vs Genap)</option>
                        <option value="yearly">Per Tahun (TA Berjalan vs Arsip)</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            <canvas id="chartGradesPerformance" height="120"></canvas>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px; padding-top: 10px; border-top: 1px dashed #e2e8f0; font-size: 12px; color: var(--text-muted);">
                <div>
                    <span style="display: inline-block; width: 12px; height: 12px; background: #059669; border-radius: 2px; margin-right: 4px;"></span> Nilai Capaian Siswa &nbsp;&nbsp;
                    <span style="display: inline-block; width: 12px; height: 12px; background: #dc2626; border-radius: 2px; margin-right: 4px;"></span> Garis Ambang KKM (75.00)
                </div>
                <div id="gradeStatsIndicator" style="font-weight: 600; color: #059669;">
                    Rata-rata Keseluruhan: Memenuhi Standar Ketuntasan
                </div>
            </div>
        </div>
    </div>

    <!-- 4. LAPORAN TUNGGAKAN & LAPORAN SUPERVISI DENGAN GRAFIK -->
    <div class="grid-cols-2" style="margin-bottom: 24px;">
        <!-- Laporan Tunggakan SPP dengan Grafik -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 class="card-title">
                        <i class="fa-solid fa-receipt" style="color: #dc2626; margin-right: 6px;"></i>
                        Laporan Tunggakan SPP &amp; Keuangan Sekolah
                    </h3>
                    <div style="font-size: 12px; color: var(--text-muted);">Monitoring ketertiban pembayaran wali santri</div>
                </div>
                <a href="<?= $baseUrl ?>/tagihan/approval" class="btn btn-outline btn-sm">Approval Tagihan</a>
            </div>
            <div class="card-body">
                <!-- Stat Ringkas -->
                <div class="grid-cols-3" style="gap: 10px; margin-bottom: 14px;">
                    <div style="background: #f8fafc; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <div style="font-size: 11px; color: var(--text-muted);">Total Tagihan</div>
                        <div style="font-size: 13px; font-weight: 700; color: #1e293b;"><?= SecurityHelper::formatRupiah($billsStats['total_amount'] ?? 0) ?></div>
                    </div>
                    <div style="background: #f0fdf4; padding: 8px 12px; border-radius: 6px; border: 1px solid #bbf7d0;">
                        <div style="font-size: 11px; color: #166534;">Terbayar (Lunas)</div>
                        <div style="font-size: 13px; font-weight: 700; color: #15803d;"><?= SecurityHelper::formatRupiah($billsStats['total_paid'] ?? 0) ?></div>
                    </div>
                    <div style="background: #fef2f2; padding: 8px 12px; border-radius: 6px; border: 1px solid #fecaca;">
                        <div style="font-size: 11px; color: #991b1b;">Tertunggak</div>
                        <div style="font-size: 13px; font-weight: 700; color: #b91c1c;"><?= SecurityHelper::formatRupiah($billsStats['total_unpaid'] ?? 0) ?></div>
                    </div>
                </div>

                <div style="display: flex; gap: 16px; align-items: center;">
                    <div style="width: 40%; max-width: 160px;">
                        <canvas id="chartBillsDonut" height="150"></canvas>
                    </div>
                    <div style="width: 60%;">
                        <canvas id="chartBillsMonthly" height="150"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Laporan Supervisi Akademik dengan Grafik -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 class="card-title">
                        <i class="fa-solid fa-clipboard-check" style="color: #7c3aed; margin-right: 6px;"></i>
                        Laporan Supervisi Pembelajaran Kelas
                    </h3>
                    <div style="font-size: 12px; color: var(--text-muted);">Evaluasi kompetensi pedagogik &amp; KBM guru</div>
                </div>
                <a href="<?= $baseUrl ?>/supervisi" class="btn btn-outline btn-sm">Buka Supervisi</a>
            </div>
            <div class="card-body">
                <div class="grid-cols-3" style="gap: 10px; margin-bottom: 14px;">
                    <div style="background: #f5f3ff; padding: 8px 12px; border-radius: 6px; border: 1px solid #ddd6fe;">
                        <div style="font-size: 11px; color: #6d28d9;">Rata-rata Final</div>
                        <div style="font-size: 15px; font-weight: 700; color: #5b21b6;"><?= $supStats['avg_final_score'] ?? 0 ?> / 100</div>
                    </div>
                    <div style="background: #f8fafc; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <div style="font-size: 11px; color: var(--text-muted);">Bag. B (Pendahuluan)</div>
                        <div style="font-size: 13px; font-weight: 700; color: #1e293b;"><?= $supStats['avg_score_b'] ?? 0 ?> / 20</div>
                    </div>
                    <div style="background: #f8fafc; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <div style="font-size: 11px; color: var(--text-muted);">Bag. C (Kegiatan Inti)</div>
                        <div style="font-size: 13px; font-weight: 700; color: #1e293b;"><?= $supStats['avg_score_c'] ?? 0 ?> / 70</div>
                    </div>
                </div>

                <div style="display: flex; gap: 16px; align-items: center;">
                    <div style="width: 45%; max-width: 180px;">
                        <canvas id="chartSupervisiPredicate" height="150"></canvas>
                    </div>
                    <div style="width: 55%;">
                        <div style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px;">Top Guru Hasil Supervisi:</div>
                        <div style="max-height: 140px; overflow-y: auto;">
                            <table class="table" style="font-size: 11px; margin-bottom: 0;">
                                <tbody>
                                    <?php foreach (array_slice($supList ?? [], 0, 4) as $sItem): ?>
                                        <tr>
                                            <td style="padding: 4px 6px;"><strong><?= SecurityHelper::escape($sItem['teacher_name']) ?></strong></td>
                                            <td style="padding: 4px 6px;"><?= SecurityHelper::escape($sItem['subject_name']) ?></td>
                                            <td style="padding: 4px 6px; text-align: right; font-weight: 700; color: #7c3aed;"><?= $sItem['final_score'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. PERBANDINGAN TAHUN AJARAN AKTIF VS TAHUN AJARAN DIARSIP -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header" style="background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);">
            <div>
                <h3 class="card-title">
                    <i class="fa-solid fa-code-compare" style="color: #0284c7; margin-right: 6px;"></i>
                    Tabel Perbandingan Mutu: Tahun Ajaran Berjalan vs Tahun Ajaran Diarsip
                </h3>
                <div style="font-size: 12px; color: var(--text-muted);">
                    Evaluasi komparatif multi-tahun untuk perencanaan kurikulum &amp; RKAS satuan pendidikan
                </div>
            </div>
            <span class="badge badge-secondary"><i class="fa-solid fa-box-archive"></i> Arsip Multi-Semester</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr style="background: #f1f5f9;">
                            <th>Tahun Ajaran &amp; Semester</th>
                            <th style="text-align: center;">Status Sistem</th>
                            <th style="text-align: center;">Pergerakan Siswa Tercatat</th>
                            <th style="text-align: center;">Rata-rata Supervisi Guru</th>
                            <th style="text-align: center;">Tunggakan SPP (Rp)</th>
                            <th style="text-align: center;">Rata-rata Kehadiran Siswa</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($taComparison as $cmp): ?>
                            <tr style="<?= $cmp['is_active'] ? 'background: #f0fdf4; font-weight: 500;' : '' ?>">
                                <td>
                                    <strong><?= SecurityHelper::escape($cmp['ta_name']) ?> - <?= SecurityHelper::escape($cmp['semester']) ?></strong>
                                    <?php if ($cmp['is_active']): ?>
                                        <span class="badge badge-success" style="font-size: 10px; margin-left: 6px;">Sedang Berjalan</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary" style="font-size: 10px; margin-left: 6px;">Diarsipkan (Terkunci)</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($cmp['is_active']): ?>
                                        <span class="badge badge-success"><i class="fa-solid fa-unlock"></i> Aktif</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning"><i class="fa-solid fa-lock"></i> Diarsipkan</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <strong><?= (int)$cmp['movement_count'] ?></strong> Bulan rekap
                                </td>
                                <td style="text-align: center; font-weight: 700; color: #7c3aed;">
                                    <?= $cmp['avg_supervisi'] ? number_format((float)$cmp['avg_supervisi'], 1) : '-' ?>
                                </td>
                                <td style="text-align: center; font-weight: 700; color: #dc2626;">
                                    <?= $cmp['total_tunggakan'] ? SecurityHelper::formatRupiah($cmp['total_tunggakan']) : 'Rp 0' ?>
                                </td>
                                <td style="text-align: center; font-weight: 700; color: #16a34a;">
                                    <?= $cmp['avg_att'] ? number_format((float)$cmp['avg_att'], 1).'%' : '-' ?>
                                </td>
                                <td style="text-align: center;">
                                    <form method="POST" action="<?= $baseUrl ?>/switch-ta" style="display: inline;">
                                        <?= $csrfField ?>
                                        <input type="hidden" name="academic_year_id" value="<?= $cmp['ta_id'] ?>">
                                        <button type="submit" class="btn <?= $cmp['is_active'] ? 'btn-outline' : 'btn-primary' ?> btn-sm" style="font-size: 11px; padding: 3px 8px;">
                                            <i class="fa-solid fa-eye"></i> <?= $cmp['is_active'] ? 'Lihat Berjalan' : 'Buka Arsip' ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Chart.js Scripts untuk Kepala Sekolah -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- 1. Chart Pergerakan Jumlah Siswa (Combo Bar & Line) ---
        const movLabels = <?= json_encode(array_column($studentMovements ?? [], 'month_name')) ?>;
        const movMasuk = <?= json_encode(array_map('intval', array_column($studentMovements ?? [], 'masuk'))) ?>;
        const movKeluar = <?= json_encode(array_map('intval', array_column($studentMovements ?? [], 'keluar'))) ?>;
        const movCurrent = <?= json_encode(array_map('intval', array_column($studentMovements ?? [], 'current_count'))) ?>;

        const ctxMov = document.getElementById('chartStudentMovement');
        if (ctxMov) {
            new Chart(ctxMov, {
                data: {
                    labels: movLabels,
                    datasets: [
                        {
                            type: 'line',
                            label: 'Jumlah Siswa Saat Ini (Live Count)',
                            data: movCurrent,
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.3,
                            yAxisID: 'y'
                        },
                        {
                            type: 'bar',
                            label: 'Siswa Masuk (+)',
                            data: movMasuk,
                            backgroundColor: '#10b981',
                            borderRadius: 4,
                            yAxisID: 'y1'
                        },
                        {
                            type: 'bar',
                            label: 'Siswa Keluar (-)',
                            data: movKeluar,
                            backgroundColor: '#ef4444',
                            borderRadius: 4,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: { display: true, text: 'Total Siswa Saat Ini' }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            title: { display: true, text: 'Siswa Masuk / Keluar' },
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        }

        // --- 2. Chart Gender Guru & Staf (Donut) ---
        const ctxGender = document.getElementById('chartTeacherGender');
        if (ctxGender) {
            new Chart(ctxGender, {
                type: 'doughnut',
                data: {
                    labels: ['Laki-laki', 'Perempuan'],
                    datasets: [{
                        data: [<?= (int)($empGenderCounts['L'] ?? 0) ?>, <?= (int)($empGenderCounts['P'] ?? 0) ?>],
                        backgroundColor: ['#3b82f6', '#ec4899'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        // --- 2. Chart Tingkat Pendidikan (Bar Chart) ---
        const eduLabels = <?= json_encode(array_column($empEduCounts ?? [], 'tingkat')) ?>;
        const eduData = <?= json_encode(array_map('intval', array_column($empEduCounts ?? [], 'count'))) ?>;
        const ctxEdu = document.getElementById('chartTeacherEducation');
        if (ctxEdu) {
            new Chart(ctxEdu, {
                type: 'bar',
                data: {
                    labels: eduLabels,
                    datasets: [{
                        label: 'Jumlah Guru / Staf',
                        data: eduData,
                        backgroundColor: ['#6366f1', '#8b5cf6', '#3b82f6', '#06b6d4', '#10b981', '#f59e0b'],
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        }

        // --- 4. Chart Tunggakan SPP (Donut Status & Stacked Bar Bulanan) ---
        const ctxBillsDonut = document.getElementById('chartBillsDonut');
        if (ctxBillsDonut) {
            new Chart(ctxBillsDonut, {
                type: 'doughnut',
                data: {
                    labels: ['Lunas', 'Tertunggak'],
                    datasets: [{
                        data: [<?= (float)($billsStats['total_paid'] ?? 0) ?>, <?= (float)($billsStats['total_unpaid'] ?? 0) ?>],
                        backgroundColor: ['#10b981', '#ef4444'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        const billsMonthlyLabels = ['Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];
        const billsMonthlyDataRaw = <?= json_encode($billsMonthly ?? []) ?>;
        
        // Map bills data to standard 12 months
        const monthNumOrder = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
        const paidByMonth = monthNumOrder.map(m => {
            const found = billsMonthlyDataRaw.find(b => parseInt(b.bill_month) === m);
            return found ? parseFloat(found.paid_amount) / 1000000 : 0;
        });
        const unpaidByMonth = monthNumOrder.map(m => {
            const found = billsMonthlyDataRaw.find(b => parseInt(b.bill_month) === m);
            return found ? parseFloat(found.unpaid_amount) / 1000000 : 0;
        });

        const ctxBillsMonthly = document.getElementById('chartBillsMonthly');
        if (ctxBillsMonthly) {
            new Chart(ctxBillsMonthly, {
                type: 'bar',
                data: {
                    labels: billsMonthlyLabels,
                    datasets: [
                        {
                            label: 'Lunas (Juta Rp)',
                            data: paidByMonth,
                            backgroundColor: '#10b981',
                            borderRadius: 4
                        },
                        {
                            label: 'Tertunggak (Juta Rp)',
                            data: unpaidByMonth,
                            backgroundColor: '#ef4444',
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: { stacked: true },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: { callback: v => 'Rp ' + v + 'Jt' }
                        }
                    }
                }
            });
        }

        // --- 5. Chart Supervisi Predikat (Donut) ---
        const supPredLabels = <?= json_encode(array_column($supPredicates ?? [], 'achievement_predicate')) ?>;
        const supPredCounts = <?= json_encode(array_map('intval', array_column($supPredicates ?? [], 'count'))) ?>;
        const ctxSupPred = document.getElementById('chartSupervisiPredicate');
        if (ctxSupPred) {
            new Chart(ctxSupPred, {
                type: 'doughnut',
                data: {
                    labels: supPredLabels,
                    datasets: [{
                        data: supPredCounts,
                        backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        // Initialize Grades Performance Chart
        initGradesChart();
    });

    // --- 3. DYNAMIC GRADES CHART (FILTER MAPEL & FILTER PERIODIK) ---
    let gradesChartInstance = null;
    const rawGradesMonthly = <?= json_encode($gradesMonthly ?? []) ?>;
    const rawAllSubjectsMonthly = <?= json_encode($allSubjectsMonthly ?? []) ?>;
    const rawGradesYearly = <?= json_encode($gradesYearly ?? []) ?>;

    function initGradesChart() {
        updateGradesChart();
    }

    function updateGradesChart() {
        const subjectFilter = document.getElementById('filterGradeSubject').value;
        const periodFilter = document.getElementById('filterGradePeriod').value;
        const ctx = document.getElementById('chartGradesPerformance');
        if (!ctx) return;

        let labels = [];
        let scoreData = [];
        let kkmData = [];
        const monthNumOrder = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
        const monthNames = ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'];

        if (periodFilter === 'monthly') {
            labels = monthNames;
            if (subjectFilter === 'ALL') {
                scoreData = monthNumOrder.map(m => {
                    const row = rawAllSubjectsMonthly.find(r => parseInt(r.month_num) === m);
                    return row ? parseFloat(row.avg_score) : 80.0;
                });
            } else {
                scoreData = monthNumOrder.map(m => {
                    const row = rawGradesMonthly.find(r => parseInt(r.month_num) === m && parseInt(r.subject_id) === parseInt(subjectFilter));
                    return row ? parseFloat(row.avg_score) : 78.0;
                });
            }
            kkmData = Array(12).fill(75.0);

        } else if (periodFilter === 'quarterly') {
            labels = ['Triwulan 1 (Jul-Sep)', 'Triwulan 2 (Okt-Des)', 'Triwulan 3 (Jan-Mar)', 'Triwulan 4 (Apr-Jun)'];
            const quarters = [
                [7, 8, 9],
                [10, 11, 12],
                [1, 2, 3],
                [4, 5, 6]
            ];

            scoreData = quarters.map(qMonths => {
                let filtered = [];
                if (subjectFilter === 'ALL') {
                    filtered = rawAllSubjectsMonthly.filter(r => qMonths.includes(parseInt(r.month_num)));
                } else {
                    filtered = rawGradesMonthly.filter(r => qMonths.includes(parseInt(r.month_num)) && parseInt(r.subject_id) === parseInt(subjectFilter));
                }
                if (filtered.length === 0) return 80.0;
                const sum = filtered.reduce((acc, cur) => acc + parseFloat(cur.avg_score), 0);
                return +(sum / filtered.length).toFixed(1);
            });
            kkmData = Array(4).fill(75.0);

        } else if (periodFilter === 'semester') {
            labels = ['Semester 1 (Ganjil: Jul-Des)', 'Semester 2 (Genap: Jan-Jun)'];
            const sems = [
                [7, 8, 9, 10, 11, 12],
                [1, 2, 3, 4, 5, 6]
            ];

            scoreData = sems.map(sMonths => {
                let filtered = [];
                if (subjectFilter === 'ALL') {
                    filtered = rawAllSubjectsMonthly.filter(r => sMonths.includes(parseInt(r.month_num)));
                } else {
                    filtered = rawGradesMonthly.filter(r => sMonths.includes(parseInt(r.month_num)) && parseInt(r.subject_id) === parseInt(subjectFilter));
                }
                if (filtered.length === 0) return 82.0;
                const sum = filtered.reduce((acc, cur) => acc + parseFloat(cur.avg_score), 0);
                return +(sum / filtered.length).toFixed(1);
            });
            kkmData = Array(2).fill(75.0);

        } else if (periodFilter === 'yearly') {
            // Perbandingan Multi-Tahun (TA Berjalan vs TA Arsip)
            labels = rawGradesYearly.map(y => y.ta_name + ' ' + y.semester + (y.is_active == 1 ? ' (Berjalan)' : ' (Arsip)'));
            scoreData = rawGradesYearly.map(y => parseFloat(y.avg_score));
            kkmData = rawGradesYearly.map(() => 75.0);
        }

        // Hitung rata-rata capaian untuk indikator
        if (scoreData.length > 0) {
            const avg = (scoreData.reduce((a, b) => a + b, 0) / scoreData.length).toFixed(1);
            const indicatorEl = document.getElementById('gradeStatsIndicator');
            if (indicatorEl) {
                indicatorEl.innerHTML = 'Rata-rata Skor: <strong>' + avg + '</strong> | ' + 
                    (avg >= 75.0 ? '<span style="color:#059669;"><i class="fa-solid fa-check"></i> Memenuhi KKM</span>' : '<span style="color:#dc2626;"><i class="fa-solid fa-triangle-exclamation"></i> Di Bawah KKM</span>');
            }
        }

        if (gradesChartInstance) {
            gradesChartInstance.destroy();
        }

        gradesChartInstance = new Chart(ctx, {
            type: periodFilter === 'yearly' || periodFilter === 'semester' ? 'bar' : 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Nilai Capaian Siswa',
                        data: scoreData,
                        borderColor: '#059669',
                        backgroundColor: periodFilter === 'yearly' || periodFilter === 'semester' ? 'rgba(5, 150, 105, 0.7)' : 'rgba(5, 150, 105, 0.1)',
                        borderWidth: 3,
                        fill: periodFilter !== 'yearly' && periodFilter !== 'semester',
                        tension: 0.35,
                        borderRadius: 6
                    },
                    {
                        type: 'line',
                        label: 'Garis Batas KKM (75.0)',
                        data: kkmData,
                        borderColor: '#dc2626',
                        borderWidth: 2,
                        borderDash: [6, 6],
                        pointRadius: 0,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        min: 50,
                        max: 100,
                        title: { display: true, text: 'Rentang Nilai (0 - 100)' }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(c) {
                                return ' ' + c.dataset.label + ': ' + c.raw;
                            }
                        }
                    }
                }
            }
        });
    }
    </script>


<?php elseif ($role === 'operator_sekolah'): ?>
    <!-- DASHBOARD OPERATOR SEKOLAH -->
    <div class="grid-cols-4" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fa-solid fa-user-graduate"></i></div>
            <div>
                <div class="stat-value"><?= (int)($totalStudents ?? 0) ?></div>
                <div class="stat-label">Siswa Aktif Unit</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon emerald"><i class="fa-solid fa-chalkboard-user"></i></div>
            <div>
                <div class="stat-value"><?= (int)($totalTeachers ?? 0) ?></div>
                <div class="stat-label">Guru &amp; Karyawan Unit</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon amber"><i class="fa-solid fa-shapes"></i></div>
            <div>
                <div class="stat-value"><?= (int)($totalClasses ?? 0) ?></div>
                <div class="stat-label">Rombel (Kelas) Aktif</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon rose"><i class="fa-solid fa-receipt"></i></div>
            <div>
                <div class="stat-value"><?= SecurityHelper::formatRupiah($unpaidBills['s'] ?? 0) ?></div>
                <div class="stat-label">Tunggakan SPP Berjalan</div>
            </div>
        </div>
    </div>

    <!-- MENU OPERASIONAL CEPAT OPERATOR SEKOLAH -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fa-solid fa-bolt" style="color: var(--primary);"></i> Menu Aksi Operasional Operator Sekolah
            </h3>
        </div>
        <div class="card-body">
            <p style="color: var(--text-muted); margin-bottom: 16px;">
                Pusat pengelolaan data induk santri, wali santri, struktur rombel kelas &amp; wali kelas, serta penempatan siswa.
            </p>
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <a href="<?= $baseUrl ?>/students" class="btn btn-primary">
                    <i class="fa-solid fa-user-graduate"></i> Kelola Data Siswa &amp; Ortu
                </a>
                <a href="<?= $baseUrl ?>/classes" class="btn btn-secondary" style="background: #0284c7; color: #fff;">
                    <i class="fa-solid fa-shapes"></i> Data Kelas &amp; Walas
                </a>
                <a href="<?= $baseUrl ?>/classes/placement" class="btn btn-outline">
                    <i class="fa-solid fa-users-rectangle"></i> Penempatan Siswa Kelas
                </a>
                <a href="<?= $baseUrl ?>/employees" class="btn btn-outline">
                    <i class="fa-solid fa-chalkboard-user"></i> Data Guru &amp; Staf Unit
                </a>
                <a href="<?= $baseUrl ?>/attendance/students" class="btn btn-outline">
                    <i class="fa-solid fa-clipboard-user"></i> Rekap Presensi Siswa
                </a>
            </div>
        </div>
    </div>

    <!-- DAFTAR SANTRI TERBARU / AKTIF UNIT -->
    <?php if (!empty($recentStudents)): ?>
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3 class="card-title">
                    <i class="fa-solid fa-user-check" style="color: #059669;"></i> Santri Aktif Unit Terkini
                </h3>
                <a href="<?= $baseUrl ?>/students" class="btn btn-outline btn-sm">Lihat Semua Siswa</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th>NIS / NISN</th>
                            <th>Nama Santri</th>
                            <th>Kelas Saat Ini</th>
                            <th>Nama Orang Tua</th>
                            <th style="text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentStudents as $rs): ?>
                            <tr>
                                <td><strong><?= SecurityHelper::escape($rs['nis'] ?: ($rs['nisn'] ?: '-')) ?></strong></td>
                                <td><strong><?= SecurityHelper::escape($rs['nama']) ?></strong></td>
                                <td><?= SecurityHelper::escape($rs['class_name'] ?? '(Belum Ditempatkan)') ?></td>
                                <td><?= SecurityHelper::escape(($rs['father_name'] ?? '') ?: (($rs['mother_name'] ?? '') ?: '-')) ?></td>
                                <td style="text-align: center;">
                                    <span class="badge badge-success">Aktif</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

<?php elseif ($role === 'orang_tua'): ?>
    <!-- PORTAL ORANG TUA -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h3 class="card-title"><i class="fa-solid fa-children" style="color: var(--primary);"></i> Data Santri Anda di Yayasan Islam Al Kahfi</h3>
        </div>
        <div class="card-body">
            <div class="grid-cols-2">
                <?php foreach ($children as $child): ?>
                    <div style="border: 1px solid var(--border-color); border-radius: 10px; padding: 16px; background: #f8fafc;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <h4 style="font-size: 16px; font-weight: 700; color: var(--primary);"><?= SecurityHelper::escape($child['nama']) ?></h4>
                                <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                                    NIS: <?= SecurityHelper::escape($child['nis']) ?> | NISN: <?= SecurityHelper::escape($child['nisn'] ?? '-') ?>
                                </div>
                                <div style="margin-top: 6px;">
                                    <span class="badge badge-info"><?= SecurityHelper::escape($child['unit_name']) ?></span>
                                    <span class="badge badge-secondary"><?= SecurityHelper::escape($child['class_name'] ?? 'Belum ada kelas') ?></span>
                                </div>
                            </div>
                            <div style="width: 44px; height: 44px; border-radius: 50%; background: #dbeafe; color: #1e40af; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                <i class="fa-solid fa-child"></i>
                            </div>
                        </div>

                        <div style="margin-top: 16px; display: flex; gap: 8px; flex-wrap: wrap;">
                            <a href="<?= $baseUrl ?>/parent/rapor-bulanan/<?= $child['id'] ?>" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-download"></i> Rapor Bulanan
                            </a>
                            <a href="<?= $baseUrl ?>/parent/rapor-tahfidz/<?= $child['id'] ?>" class="btn btn-accent btn-sm">
                                <i class="fa-solid fa-quran"></i> Rapor Tahfidz
                            </a>
                            <a href="<?= $baseUrl ?>/parent/rapor-gabungan/<?= $child['id'] ?>" class="btn btn-outline btn-sm">
                                <i class="fa-solid fa-file-pdf"></i> Rapor Gabungan
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Status Tagihan SPP Anak -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fa-solid fa-receipt" style="color: var(--danger);"></i> Kewajiban Pembayaran / Tagihan SPP</h3>
            <a href="<?= $baseUrl ?>/parent/bills" class="btn btn-outline btn-sm">Lihat Semua</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($unpaidBills)): ?>
                <div style="padding: 24px; text-align: center; color: var(--success); font-weight: 600;">
                    <i class="fa-solid fa-circle-check" style="font-size: 28px; margin-bottom: 8px; display: block;"></i>
                    Alhamdulillah, seluruh tagihan SPP anak Anda telah lunas.
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Santri</th>
                            <th>Unit Sekolah</th>
                            <th>Uraian Tagihan</th>
                            <th>Jatuh Tempo</th>
                            <th>Nominal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($unpaidBills as $bill): ?>
                            <tr>
                                <td><strong><?= SecurityHelper::escape($bill['student_name']) ?></strong></td>
                                <td><?= SecurityHelper::escape($bill['unit_name']) ?></td>
                                <td><?= SecurityHelper::escape($bill['title']) ?> (Bulan <?= $bill['bill_month'] ?>)</td>
                                <td><?= SecurityHelper::formatTanggalIndo($bill['due_date']) ?></td>
                                <td style="font-weight: 700; color: var(--danger);"><?= SecurityHelper::formatRupiah($bill['amount']) ?></td>
                                <td>
                                    <a href="<?= $baseUrl ?>/parent/surat-tagihan/<?= $bill['id'] ?>" class="btn btn-danger btn-sm" target="_blank">
                                        <i class="fa-solid fa-file-pdf"></i> Surat Tagihan
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

<?php elseif ($role === 'sarpras'): ?>
    <!-- DASHBOARD ROLE SARPRAS -->
    <div class="grid-cols-4" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon emerald"><i class="fa-solid fa-boxes-stacked"></i></div>
            <div>
                <div class="stat-value"><?= (int)($totalAssets ?? 0) ?></div>
                <div class="stat-label">Total Aset Unit Aktif</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon amber"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div>
                <div class="stat-value"><?= (int)($totalDamaged ?? 0) ?></div>
                <div class="stat-label">Aset Rusak / Perlu Perbaikan</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fa-solid fa-file-invoice"></i></div>
            <div>
                <div class="stat-value"><?= (int)($pendingPo ?? 0) ?></div>
                <div class="stat-label">PO Berjalan / Menunggu</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon rose"><i class="fa-solid fa-trash-can"></i></div>
            <div>
                <div class="stat-value"><?= (int)($totalScrapped ?? 0) ?></div>
                <div class="stat-label">Barang Di-Scrap (F30)</div>
            </div>
        </div>
    </div>

    <!-- QUICK ACTIONS -->
    <div class="card" style="margin-bottom: 24px; border-left: 4px solid #0f766e;">
        <div class="card-body" style="padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <div style="font-weight: 700; font-size: 15px; color: #0f172a;">Menu Cepat Operasional Sarpras</div>
                <div style="font-size: 12px; color: #64748b;">Pengajuan barang, pendataan stok inventaris, dan penerbitan berita acara pemusnahan barang afkir.</div>
            </div>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <a href="<?= $baseUrl ?>/sarpras/po/create" class="btn btn-primary" style="background: #0f766e; border-color: #0f766e;">
                    <i class="fa-solid fa-plus me-1"></i> Buat PO Barang Baru
                </a>
                <a href="<?= $baseUrl ?>/sarpras/assets" class="btn btn-outline">
                    <i class="fa-solid fa-boxes-stacked me-1"></i> Pendataan Aset Sekolah
                </a>
                <a href="<?= $baseUrl ?>/sarpras/scrap/create" class="btn btn-outline" style="border-color: #dc2626; color: #dc2626;">
                    <i class="fa-solid fa-file-circle-xmark me-1"></i> Buat Berita Acara Scrap
                </a>
                <a href="<?= $baseUrl ?>/profile" class="btn btn-outline">
                    <i class="fa-solid fa-id-card me-1"></i> Profil Saya
                </a>
            </div>
        </div>
    </div>

    <div class="grid-cols-2" style="margin-bottom: 24px;">
        <!-- Recent POs -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3 class="card-title"><i class="fa-solid fa-file-invoice" style="color: #0f766e;"></i> Permintaan Barang (PO) Terakhir</h3>
                <a href="<?= $baseUrl ?>/sarpras/po" class="btn btn-sm btn-outline">Semua PO</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="table" style="margin: 0;">
                    <thead>
                        <tr>
                            <th>No PO</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentPos)): ?>
                            <tr><td colspan="4" style="text-align: center; color: #64748b; padding: 20px;">Belum ada PO</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentPos as $po): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; font-family: monospace; color: #0f766e;"><?= SecurityHelper::escape($po['po_number']) ?></div>
                                        <div style="font-size: 11px; color: #64748b;"><?= SecurityHelper::escape($po['reason']) ?></div>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($po['request_date'])) ?></td>
                                    <td>
                                        <span class="badge badge-info" style="font-size: 11px;"><?= SecurityHelper::escape($po['status']) ?></span>
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="<?= $baseUrl ?>/sarpras/po/detail/<?= $po['id'] ?>" class="btn btn-sm btn-outline">Detail</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Assets -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3 class="card-title"><i class="fa-solid fa-box-open" style="color: #0d9488;"></i> Inventaris Aset Terbaru</h3>
                <a href="<?= $baseUrl ?>/sarpras/assets" class="btn btn-sm btn-outline">Semua Aset</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="table" style="margin: 0;">
                    <thead>
                        <tr>
                            <th>No Aset</th>
                            <th>Nama / Merk</th>
                            <th>Kondisi</th>
                            <th>Pemegang</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentAssets)): ?>
                            <tr><td colspan="4" style="text-align: center; color: #64748b; padding: 20px;">Belum ada aset</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentAssets as $ast): ?>
                                <tr>
                                    <td>
                                        <span class="badge" style="background: #f1f5f9; color: #1e293b; font-family: monospace; font-size: 11px;">
                                            <?= SecurityHelper::escape($ast['asset_code']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 700;"><?= SecurityHelper::escape($ast['name']) ?></div>
                                        <div style="font-size: 11px; color: #64748b;"><?= SecurityHelper::escape($ast['category_name'] ?? '-') ?></div>
                                    </td>
                                    <td>
                                        <span class="badge <?= ($ast['condition_status'] === 'Baik') ? 'badge-success' : 'badge-warning' ?>" style="font-size: 11px;">
                                            <?= SecurityHelper::escape($ast['condition_status']) ?>
                                        </span>
                                    </td>
                                    <td style="font-size: 12px; color: #475569;">
                                        <?= SecurityHelper::escape($ast['user_holder_name'] ?: 'Sarpras') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php elseif ($role === 'direktur_pengadaan'): ?>
    <!-- DASHBOARD DIREKTUR PENGADAAN YAYASAN -->
    <div class="grid-cols-4" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon amber"><i class="fa-solid fa-hourglass-start"></i></div>
            <div>
                <div class="stat-value"><?= (int)($pendingApprovalPo ?? 0) ?></div>
                <div class="stat-label">PO Butuh Approval Anda</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon emerald"><i class="fa-solid fa-check-double"></i></div>
            <div>
                <div class="stat-value"><?= (int)($approvedPo ?? 0) ?></div>
                <div class="stat-label">PO Disetujui / Selesai</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fa-solid fa-helmet-safety"></i></div>
            <div>
                <div class="stat-value"><?= (int)($contractorPo ?? 0) ?></div>
                <div class="stat-label">PO Jalur Kontraktor</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fa-solid fa-boxes-stacked"></i></div>
            <div>
                <div class="stat-value"><?= (int)($totalAllAssets ?? 0) ?></div>
                <div class="stat-label">Total Aset Aktif Yayasan</div>
            </div>
        </div>
    </div>

    <!-- ANTREAN PERSETUJUAN PO -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header" style="background: #e0e7ff; display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title" style="color: #3730a3; margin: 0;">
                <i class="fa-solid fa-inbox me-1"></i> Antrean Permohonan Barang (Menunggu Persetujuan Direktur Pengadaan)
            </h3>
            <span class="badge" style="background: #4338ca; color: #fff; font-weight: 700;">
                <?= count($actionPos ?? []) ?> Berkas Masuk
            </span>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="table" style="margin: 0;">
                <thead>
                    <tr>
                        <th>No PO</th>
                        <th>Unit Pemohon</th>
                        <th>Alasan Permintaan</th>
                        <th>Tanggal Disetujui KS</th>
                        <th>Tipe Pengadaan</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($actionPos)): ?>
                        <tr><td colspan="6" style="text-align: center; color: #64748b; padding: 24px;">Tidak ada permohonan PO yang menunggu persetujuan Anda saat ini.</td></tr>
                    <?php else: ?>
                        <?php foreach ($actionPos as $apo): ?>
                            <tr>
                                <td style="font-weight: 700; font-family: monospace; color: #1e3a8a;"><?= SecurityHelper::escape($apo['po_number']) ?></td>
                                <td><span class="badge badge-info"><?= SecurityHelper::escape($apo['unit_name']) ?></span></td>
                                <td><?= SecurityHelper::escape($apo['reason']) ?></td>
                                <td><?= !empty($apo['approved_kepsek_at']) ? date('d/m/Y H:i', strtotime($apo['approved_kepsek_at'])) : '-' ?></td>
                                <td>
                                    <?php if ($apo['needs_contractor']): ?>
                                        <span class="badge badge-warning"><i class="fa-solid fa-helmet-safety me-1"></i> Butuh Kontraktor</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Standar Toko</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <a href="<?= $baseUrl ?>/sarpras/po/detail/<?= $apo['id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="fa-solid fa-pen-to-square me-1"></i> Review & Setujui
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($role === 'direktur_sarpras'): ?>
    <!-- DASHBOARD DIREKTUR SARPRAS YAYASAN -->
    <div class="grid-cols-4" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon amber"><i class="fa-solid fa-person-digging"></i></div>
            <div>
                <div class="stat-value"><?= (int)($pendingContractorPo ?? 0) ?></div>
                <div class="stat-label">PO Kontraktor Butuh Approval</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon emerald"><i class="fa-solid fa-building-columns"></i></div>
            <div>
                <div class="stat-value"><?= (int)($totalYayasanAssets ?? 0) ?></div>
                <div class="stat-label">Total Aset Seluruh Yayasan</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon rose"><i class="fa-solid fa-wrench"></i></div>
            <div>
                <div class="stat-value"><?= (int)($totalBrokenYayasan ?? 0) ?></div>
                <div class="stat-label">Aset Rusak Perlu Monitoring</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fa-solid fa-file-excel"></i></div>
            <div>
                <div class="stat-value"><?= (int)($totalScrapReports ?? 0) ?></div>
                <div class="stat-label">Berita Acara Scrap Terbit</div>
            </div>
        </div>
    </div>

    <!-- ANTREAN KONTRAKTOR -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header" style="background: #fef3c7; display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title" style="color: #92400e; margin: 0;">
                <i class="fa-solid fa-helmet-safety me-1"></i> Permohonan Pengadaan dengan Pengerjaan Kontraktor
            </h3>
            <span class="badge" style="background: #b45309; color: #fff; font-weight: 700;">
                <?= count($actionContractorPos ?? []) ?> Menunggu Approval
            </span>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="table" style="margin: 0;">
                <thead>
                    <tr>
                        <th>No PO</th>
                        <th>Unit</th>
                        <th>Alasan / Proyek</th>
                        <th>Disetujui Pengadaan</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($actionContractorPos)): ?>
                        <tr><td colspan="5" style="text-align: center; color: #64748b; padding: 24px;">Tidak ada permohonan kontraktor yang menunggu persetujuan Anda saat ini.</td></tr>
                    <?php else: ?>
                        <?php foreach ($actionContractorPos as $cpo): ?>
                            <tr>
                                <td style="font-weight: 700; font-family: monospace; color: #b45309;"><?= SecurityHelper::escape($cpo['po_number']) ?></td>
                                <td><span class="badge badge-info"><?= SecurityHelper::escape($cpo['unit_name']) ?></span></td>
                                <td><?= SecurityHelper::escape($cpo['reason']) ?></td>
                                <td><?= !empty($cpo['approved_pengadaan_at']) ? date('d/m/Y H:i', strtotime($cpo['approved_pengadaan_at'])) : '-' ?></td>
                                <td style="text-align: right;">
                                    <a href="<?= $baseUrl ?>/sarpras/po/detail/<?= $cpo['id'] ?>" class="btn btn-sm btn-primary" style="background: #d97706; border-color: #d97706;">
                                        <i class="fa-solid fa-check me-1"></i> Approval Kontraktor
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php else: ?>
    <!-- DASHBOARD GURU & STAF UMUM -->
    <div class="grid-cols-3" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon emerald"><i class="fa-solid fa-award"></i></div>
            <div>
                <div class="stat-value"><?= $personalAkfi['letter_grade'] ?? 'B' ?></div>
                <div class="stat-label">Predikat AKFI Berjalan</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fa-solid fa-book-quran"></i></div>
            <div>
                <div class="stat-value">1 Juz</div>
                <div class="stat-label">Target Hafalan Tahunan</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon amber"><i class="fa-solid fa-user-clock"></i></div>
            <div>
                <div class="stat-value"><?= $myAttendance['t'] ?? 0 ?>x Terlambat</div>
                <div class="stat-label">Catatan Pelanggaran Absensi</div>
            </div>
        </div>
    </div>

    <?php if (!empty($myPrestasi)): ?>
        <div class="card" style="margin-bottom: 24px; border-left: 4px solid #f59e0b;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3 class="card-title" style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-trophy" style="color: #f59e0b;"></i> Prestasi &amp; Apresiasi Penghargaan Guru
                </h3>
                <span class="badge badge-warning"><?= count($myPrestasi) ?> Terpilih</span>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table" style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th>Kategori Penghargaan</th>
                                <th style="text-align: center; width: 140px;">Tingkat</th>
                                <th style="text-align: center; width: 90px;">Tahun</th>
                                <th style="text-align: center; width: 120px;">Status</th>
                                <th style="text-align: center; width: 140px;">Bukti Sertifikat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($myPrestasi as $mp): 
                                $stBadge = match($mp['status'] ?? 'Terverifikasi') {
                                    'Terverifikasi' => 'badge-success',
                                    'Dalam Proses'  => 'badge-warning',
                                    'Selesai'       => 'badge-primary',
                                    default         => 'badge-secondary'
                                };
                            ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-main, #0f172a);">
                                            <?= SecurityHelper::escape($mp['title']) ?>
                                        </div>
                                        <?php if (!empty($mp['notes'])): ?>
                                            <div style="font-size: 0.75rem; color: var(--text-muted, #64748b); margin-top: 2px;">
                                                <?= SecurityHelper::escape($mp['notes']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge badge-info"><?= SecurityHelper::escape($mp['level']) ?></span>
                                    </td>
                                    <td style="text-align: center; font-weight: 600;">
                                        <?= SecurityHelper::escape($mp['year']) ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge <?= $stBadge ?>"><?= SecurityHelper::escape($mp['status']) ?></span>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if (!empty($mp['proof_document'])): ?>
                                            <a href="<?= $baseUrl ?>/<?= SecurityHelper::escape($mp['proof_document']) ?>" target="_blank" class="btn btn-outline btn-sm" style="color: #0d9488; padding: 4px 10px; font-size: 0.75rem;">
                                                <i class="fa-solid fa-file-arrow-down"></i> Lihat Berkas
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #94a3b8; font-size: 0.75rem;">(Belum diunggah)</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fa-solid fa-list-check" style="color: var(--primary);"></i> Menu Operasional <?= SecurityHelper::escape($activeRole['display_name']) ?></h3>
        </div>
        <div class="card-body">
            <?php if ($role === 'tutor_sebaya'): ?>
                <p style="color: var(--text-muted); margin-bottom: 16px;">
                    Akses cepat tugas pendampingan hafalan guru sebaya (halaqah, penentuan target, dan penilaian berkala).
                </p>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <a href="<?= $baseUrl ?>/tahfidz/halaqah-guru" class="btn btn-primary">
                        <i class="fa-solid fa-users"></i> Kelola Halaqah Guru
                    </a>
                    <a href="<?= $baseUrl ?>/tahfidz/guru-target" class="btn btn-secondary" style="background: #0284c7; color: #fff;">
                        <i class="fa-solid fa-bullseye"></i> Target Hafalan Guru
                    </a>
                    <a href="<?= $baseUrl ?>/tahfidz/guru-nilai" class="btn btn-outline">
                        <i class="fa-solid fa-calculator"></i> Input Nilai Hafalan Guru
                    </a>
                </div>

            <?php elseif ($role === 'tutor_siswa'): ?>
                <p style="color: var(--text-muted); margin-bottom: 16px;">
                    Akses cepat pengujian hafalan Al-Qur'an santri dan penginputan nilai setoran.
                </p>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <a href="<?= $baseUrl ?>/tahfidz/ujian-antrean" class="btn btn-primary">
                        <i class="fa-solid fa-clipboard-check"></i> Ujian Tahfidz Siswa
                    </a>
                    <a href="<?= $baseUrl ?>/tahfidz/guru-nilai" class="btn btn-outline">
                        <i class="fa-solid fa-calculator"></i> Nilai Hafalan Santri
                    </a>
                </div>

            <?php elseif ($role === 'guru_supervisor'): ?>
                <p style="color: var(--text-muted); margin-bottom: 16px;">
                    Akses cepat instrumen supervisi klinis dan observasi proses pembelajaran di kelas untuk guru yang Anda bimbing.
                </p>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <a href="<?= $baseUrl ?>/supervisi/penilaian" class="btn btn-primary">
                        <i class="fa-solid fa-clipboard-check"></i> Instrumen Supervisi Kelas
                    </a>
                </div>

            <?php elseif ($role === 'koor_tahfidz'): ?>
                <p style="color: var(--text-muted); margin-bottom: 16px;">
                    Panel koordinasi capaian hafalan santri, pembagian halaqah, target tahfidz, dan baseline pengukuran mutu.
                </p>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <a href="<?= $baseUrl ?>/tahfidz/halaqah-siswa" class="btn btn-primary">
                        <i class="fa-solid fa-users"></i> Halaqah Siswa
                    </a>
                    <a href="<?= $baseUrl ?>/tahfidz/target-siswa" class="btn btn-secondary" style="background: #0284c7; color: #fff;">
                        <i class="fa-solid fa-bullseye"></i> Target Capaian Tahfidz
                    </a>
                    <a href="<?= $baseUrl ?>/tahfidz/rekap-siswa" class="btn btn-outline">
                        <i class="fa-solid fa-chart-line"></i> Capaian & Rapor Tahfidz
                    </a>
                    <a href="<?= $baseUrl ?>/tahfidz/baseline" class="btn btn-outline">
                        <i class="fa-solid fa-bars-progress"></i> Baseline Tahfidz (3x)
                    </a>
                </div>

            <?php elseif ($role === 'waka_kurikulum'): ?>
                <p style="color: var(--text-muted); margin-bottom: 16px;">
                    Panel operasional manajemen kurikulum, penjadwalan supervisi mengajar, presensi guru, dan deadline administrasi.
                </p>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <a href="<?= $baseUrl ?>/supervisi/jadwal" class="btn btn-primary">
                        <i class="fa-solid fa-calendar-check"></i> Jadwal Supervisi Guru
                    </a>
                    <a href="<?= $baseUrl ?>/attendance/teachers" class="btn btn-secondary" style="background: #0284c7; color: #fff;">
                        <i class="fa-solid fa-clipboard-user"></i> Kehadiran & Kajian Guru
                    </a>
                    <a href="<?= $baseUrl ?>/administrasi/deadlines" class="btn btn-outline">
                        <i class="fa-solid fa-clock"></i> Batas Waktu Administrasi
                    </a>
                </div>

            <?php else: ?>
                <p style="color: var(--text-muted); margin-bottom: 16px;">
                    Silakan gunakan menu di sidebar sebelah kiri untuk melakukan penginputan nilai, absensi, unggah berkas administrasi, dan asesmen kinerja AKFI mandiri.
                </p>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <a href="<?= $baseUrl ?>/akfi/pribadi" class="btn btn-primary">
                        <i class="fa-solid fa-award"></i> Buka Rapor AKFI Saya
                    </a>
                    <a href="<?= $baseUrl ?>/survey/guru-kepsek" class="btn btn-outline">
                        <i class="fa-solid fa-comments"></i> Isi Survei Kepala Sekolah
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
