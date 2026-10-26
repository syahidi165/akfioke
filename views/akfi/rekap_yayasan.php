<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 font-bold text-slate-800"><i class="fa fa-chart-line text-emerald-600 me-2"></i> Rekapitulasi Rapor Kinerja AKFI Yayasan</h4>
        <div class="text-muted small">Monitoring evaluasi performa seluruh satuan pendidikan Al Kahfi Batam</div>
    </div>
    <div>
        <a href="<?= \Core\Auth::baseUrl() ?>/akfi/export-xlsx" class="btn btn-emerald">
            <i class="fa fa-file-excel me-1"></i> Export Rekap Excel (XLSX)
        </a>
    </div>
</div>

<!-- Kartu Ringkasan Per Unit -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fa fa-school text-primary me-2"></i> Perbandingan Capaian AKFI Antar Satuan Pendidikan
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle mb-0 text-center">
                <thead class="table-light">
                    <tr>
                        <th width="40">No</th>
                        <th class="text-start">Satuan Pendidikan (Unit)</th>
                        <th>Total Pegawai</th>
                        <th>Rata-rata Skor</th>
                        <th>Predikat A</th>
                        <th>Predikat B</th>
                        <th>Predikat C</th>
                        <th>Predikat D / E</th>
                        <th>Disahkan Final</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($unitsSummary as $idx => $u): ?>
                        <tr>
                            <td><?= $idx + 1 ?></td>
                            <td class="text-start font-semibold"><?= htmlspecialchars($u['name']) ?></td>
                            <td><?= (int)$u['total_pegawai'] ?></td>
                            <td><strong class="text-emerald-700"><?= $u['avg_score'] !== null ? round((float)$u['avg_score'], 1) : '-' ?></strong></td>
                            <td><span class="badge bg-success"><?= (int)$u['grade_a'] ?></span></td>
                            <td><span class="badge bg-primary"><?= (int)$u['grade_b'] ?></span></td>
                            <td><span class="badge bg-warning text-dark"><?= (int)$u['grade_c'] ?></span></td>
                            <td><span class="badge bg-danger"><?= (int)$u['grade_de'] ?></span></td>
                            <td><span class="badge bg-info text-dark"><?= (int)$u['total_final'] ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Daftar Rinci Seluruh Pegawai -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa fa-list text-muted me-2"></i> Rincian Seluruh Rapor Pegawai</span>
        <span class="badge bg-secondary"><?= count($allAssessments) ?> Pegawai</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40">No</th>
                        <th>Nama Pegawai</th>
                        <th>Unit</th>
                        <th>Posisi</th>
                        <th>Kinerja</th>
                        <th>Sikap</th>
                        <th>Absensi</th>
                        <th>Total</th>
                        <th>Predikat</th>
                        <th>Status</th>
                        <th width="100">Cetak</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($allAssessments)): ?>
                        <tr><td colspan="11" class="text-center py-4 text-muted">Belum ada data rapor pegawai.</td></tr>
                    <?php else: ?>
                        <?php foreach ($allAssessments as $idx => $a): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($a['nama']) ?></strong>
                                    <div class="small text-muted">NIPY: <?= htmlspecialchars($a['nipy']) ?></div>
                                </td>
                                <td><?= htmlspecialchars($a['unit_name']) ?></td>
                                <td><?= htmlspecialchars($a['position_name']) ?></td>
                                <td class="font-mono"><?= $a['kinerja_score'] ?></td>
                                <td class="font-mono"><?= $a['sikap_score'] ?></td>
                                <td class="font-mono"><?= $a['absensi_score'] ?></td>
                                <td><strong class="text-emerald-700"><?= $a['total_score'] ?></strong></td>
                                <td><span class="badge bg-emerald-100 text-emerald-800"><?= $a['letter_grade'] ?></span></td>
                                <td>
                                    <?php if ($a['status'] === 'Final'): ?>
                                        <span class="badge bg-success">Final</span>
                                    <?php elseif ($a['status'] === 'Disetujui_Atasan'): ?>
                                        <span class="badge bg-primary">Disetujui</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= $a['status'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= \Core\Auth::baseUrl() ?>/akfi/print-rapor/<?= $a['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                        <i class="fa fa-print me-1"></i> Rapor
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
