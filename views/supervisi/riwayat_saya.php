<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa fa-history text-emerald-600 me-2"></i> Riwayat Hasil Supervisi Mengajar Saya</span>
        <span class="badge bg-secondary"><?= count($sessions) ?> Catatan</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40">No</th>
                        <th>Mata Pelajaran</th>
                        <th>Kelas</th>
                        <th>Supervisor / Asessor</th>
                        <th>Tanggal Pelaksanaan</th>
                        <th>Skor Akhir</th>
                        <th>Predikat</th>
                        <th width="140">Cetak Instrumen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sessions)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada riwayat supervisi mengajar untuk Anda pada tahun ajaran ini.</td></tr>
                    <?php else: ?>
                        <?php foreach ($sessions as $idx => $s): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td><strong><?= htmlspecialchars($s['subject_name']) ?></strong></td>
                                <td><?= htmlspecialchars($s['class_name']) ?></td>
                                <td><?= htmlspecialchars($s['supervisor_name']) ?></td>
                                <td><?= \Core\SecurityHelper::formatTanggalIndo($s['session_date']) ?></td>
                                <td>
                                    <strong class="fs-6 text-primary"><?= $s['total_score'] > 0 ? $s['total_score'] : '-' ?></strong>
                                </td>
                                <td>
                                    <?php if ($s['total_score'] > 0): ?>
                                        <span class="badge <?= $s['achievement_predicate'] === 'SANGAT BAIK' ? 'bg-success' : ($s['achievement_predicate'] === 'BAIK' ? 'bg-primary' : ($s['achievement_predicate'] === 'CUKUP' ? 'bg-warning text-dark' : 'bg-danger')) ?>">
                                            <?= $s['achievement_predicate'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Dalam Proses</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($s['total_score'] > 0): ?>
                                        <a href="<?= \Core\Auth::baseUrl() ?>/supervisi/print/<?= $s['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                            <i class="fa fa-print me-1"></i> Rapor PDF
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
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
