<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa fa-stamp text-emerald-600 me-2"></i> Pengesahan Akhir Rapor Kinerja AKFI Pegawai (Direktur Pendidikan)</span>
        <span class="badge bg-secondary"><?= count($assessments) ?> Data Terverifikasi</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40">No</th>
                        <th>Pegawai</th>
                        <th>Unit</th>
                        <th>Posisi</th>
                        <th>Diverifikasi Oleh</th>
                        <th>Skor Akhir</th>
                        <th>Predikat</th>
                        <th>Status</th>
                        <th width="200">Aksi Pengesahan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($assessments)): ?>
                        <tr><td colspan="9" class="text-center py-4 text-muted">Belum ada penilaian yang diajukan untuk pengesahan HR.</td></tr>
                    <?php else: ?>
                        <?php foreach ($assessments as $idx => $a): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($a['nama']) ?></strong>
                                    <div class="small text-muted">NIPY: <?= htmlspecialchars($a['nipy']) ?></div>
                                </td>
                                <td><?= htmlspecialchars($a['unit_name']) ?></td>
                                <td><?= htmlspecialchars($a['position_name']) ?></td>
                                <td><?= htmlspecialchars($a['superior_name'] ?: 'Atasan Langsung') ?></td>
                                <td><strong class="text-emerald-700"><?= $a['total_score'] ?></strong></td>
                                <td>
                                    <span class="badge bg-emerald-100 text-emerald-800 fs-7"><?= $a['letter_grade'] ?></span>
                                </td>
                                <td>
                                    <?php if ($a['status'] === 'Final'): ?>
                                        <span class="badge bg-success"><i class="fa fa-check-double me-1"></i> Telah Disahkan</span>
                                    <?php elseif ($a['status'] === 'Disetujui_Atasan'): ?>
                                        <span class="badge bg-warning text-dark"><i class="fa fa-hourglass-half me-1"></i> Menunggu Sah HR</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= $a['status'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($a['status'] === 'Disetujui_Atasan'): ?>
                                        <form action="<?= \Core\Auth::baseUrl() ?>/akfi/hr-sign" method="POST" class="d-inline">
                                            <?= \Core\CSRF::field() ?>
                                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-emerald" onclick="return confirm('Sahkan penilaian kinerja pegawai ini secara resmi?')">
                                                <i class="fa fa-stamp me-1"></i> Sahkan HR
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <a href="<?= \Core\Auth::baseUrl() ?>/akfi/print-rapor/<?= $a['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Pratinjau PDF">
                                        <i class="fa fa-file-pdf me-1"></i> PDF
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
