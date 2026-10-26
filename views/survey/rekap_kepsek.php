<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card p-3 border-left-emerald">
            <div class="text-muted small">Indeks Kepuasan Orang Tua</div>
            <div class="fs-4 font-bold text-emerald-700">
                <?= $ortuStats['avg_pct'] !== null ? round((float)$ortuStats['avg_pct'], 1) : 0 ?>%
            </div>
            <div class="small text-muted mt-1">Total Responden: <?= (int)($ortuStats['total_respondents'] ?? 0) ?> Wali Santri</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 border-left-emerald">
            <div class="text-muted small">Indeks Kepemimpinan Kepala Sekolah</div>
            <div class="fs-4 font-bold text-primary">
                <?= $guruStats['avg_pct'] !== null ? round((float)$guruStats['avg_pct'], 1) : 0 ?>%
            </div>
            <div class="small text-muted mt-1">Total Responden: <?= (int)($guruStats['total_respondents'] ?? 0) ?> Guru</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 border-left-emerald">
            <div class="text-muted small">Status Evaluasi Mutu</div>
            <div class="fs-4 font-bold text-success">
                <i class="fa fa-award me-1"></i> Memuaskan
            </div>
            <div class="small text-muted mt-1">Standar Akreditasi & AKFI Kepsek</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa fa-list text-emerald-600 me-2"></i> Daftar Respon Kuesioner Masuk</span>
        <span class="badge bg-secondary"><?= count($responses) ?> Respon</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40">No</th>
                        <th>Kategori Survei</th>
                        <th>Satuan Unit</th>
                        <th>Skor Capaian</th>
                        <th>Indeks Kepuasan</th>
                        <th>Saran / Masukan</th>
                        <th>Tanggal Pengisian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($responses)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada respon kuesioner yang masuk.</td></tr>
                    <?php else: ?>
                        <?php foreach ($responses as $idx => $r): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td><strong><?= htmlspecialchars($r['template_title']) ?></strong></td>
                                <td><?= htmlspecialchars($r['unit_name']) ?></td>
                                <td><?= $r['total_score'] ?> / <?= $r['max_score'] ?></td>
                                <td>
                                    <span class="badge <?= $r['score_pct'] >= 80 ? 'bg-success' : ($r['score_pct'] >= 65 ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                        <?= $r['score_pct'] ?>%
                                    </span>
                                </td>
                                <td class="small">
                                    <?= htmlspecialchars($r['suggestions'] ?: '-') ?>
                                </td>
                                <td class="small text-muted"><?= \Core\SecurityHelper::formatTanggalIndo(date('Y-m-d', strtotime($r['created_at']))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
