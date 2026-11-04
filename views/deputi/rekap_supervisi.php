<?php
// views/deputi/rekap_supervisi.php
use Core\SecurityHelper;

$predicateBadge = function($pred) {
    return match($pred) {
        'SANGAT BAIK' => 'bg-success text-white',
        'BAIK'        => 'bg-primary text-white',
        'CUKUP'       => 'bg-warning text-dark',
        default       => 'bg-danger text-white'
    };
};
?>

<div class="page-header d-print-none mb-4">
    <div class="row align-items-center">
        <div class="col">
            <div class="page-pretitle text-muted">
                <?= $isDirektur ? 'Monitoring Evaluasi Tingkat Yayasan' : 'Pengawasan Mutu Pembelajaran AIS' ?>
            </div>
            <h2 class="page-title text-primary d-flex align-items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-chart-bar" width="28" height="28" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="3" y1="12" x2="6" y2="12" /><line x1="12" y1="5" x2="15" y2="5" /><line x1="18" y1="9" x2="21" y2="9" /><path d="M3 12v6a2 2 0 0 0 2 2h14a2 2 0 0 0 2 -2v-9" /><circle cx="4.5" cy="12" r="1.5" /><circle cx="13.5" cy="5" r="1.5" /><circle cx="19.5" cy="9" r="1.5" /></svg>
                <?= $isDirektur ? 'Rekap Data Supervisi Guru (Seluruh Unit Yayasan)' : 'Rekap Data Supervisi Guru (Unit Binaan)' ?>
            </h2>
            <div class="text-muted small mt-1">
                Kompilasi hasil supervisi mengajar guru, perbandingan rata-rata capaian mutu antar unit, dan pemeringkatan guru dengan nilai tertinggi.
            </div>
        </div>
        <div class="col-auto ms-auto d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><rect x="7" y="13" width="10" height="8" rx="2" /></svg>
                Cetak Rekap Supervisi
            </button>
        </div>
    </div>
</div>

<!-- RINGKASAN METRIK & TOP PERFORMER -->
<div class="row row-cards mb-4">
    <!-- Card Rata-rata Gabungan -->
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-primary text-white avatar">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9" /><line x1="12" y1="8" x2="12" y2="12" /><line x1="12" y1="16" x2="12.01" y2="16" /></svg>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">Rata-rata Supervisi</div>
                        <div class="h2 mb-0 fw-bold text-primary"><?= number_format($overallAvg, 2) ?></div>
                        <div class="text-muted small">
                            Predikat: <strong><?= $overallAvg >= 90 ? 'SANGAT BAIK' : ($overallAvg >= 80 ? 'BAIK' : 'CUKUP') ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Total Sesi -->
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-info text-white avatar">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">Total Sesi Supervisi</div>
                        <div class="h2 mb-0 fw-bold"><?= $totCount ?></div>
                        <div class="text-muted small">Tercatat di Sistem</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Guru Nilai Tertinggi (Rank #1) -->
    <div class="col-sm-12 col-lg-6">
        <div class="card card-sm border-warning bg-warning-lt">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-warning text-dark avatar">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 21l8 0" /><path d="M12 17l0 4" /><path d="M7 4l10 0" /><path d="M17 4v8a5 5 0 0 1 -10 0v-8" /><circle cx="5" cy="9" r="2" /><circle cx="19" cy="9" r="2" /></svg>
                        </span>
                    </div>
                    <div class="col">
                        <div class="badge bg-warning text-dark mb-1">
                            <i class="fa-solid fa-trophy me-1"></i> Nilai Tertinggi (Rank #1)
                        </div>
                        <?php if ($topTeacher): ?>
                            <div class="fw-bold text-dark fs-3">
                                <?= SecurityHelper::escape($topTeacher['teacher_name']) ?>
                            </div>
                            <div class="text-muted small">
                                Unit: <strong><?= SecurityHelper::escape($topTeacher['unit_name']) ?></strong> &bull;
                                Mapel: <strong><?= SecurityHelper::escape($topTeacher['subject_name']) ?></strong> &bull;
                                Skor: <span class="badge bg-success text-white fw-bold"><?= number_format((float)$topTeacher['final_score'], 2) ?></span>
                            </div>
                        <?php else: ?>
                            <div class="text-muted">Belum ada data supervisi.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- BREAKDOWN RATA-RATA PER UNIT -->
<div class="card mb-4 shadow-sm">
    <div class="card-header bg-light py-2">
        <h3 class="card-title text-dark fw-bold small text-uppercase">
            <i class="fa-solid fa-chart-pie me-2 text-primary"></i> Perbandingan Rata-rata Nilai per Unit Sekolah
        </h3>
    </div>
    <div class="card-body p-3">
        <div class="row g-3">
            <?php foreach ($unitAverages as $ua): 
                $avgU = (float)($ua['avg_score'] ?? 0);
            ?>
                <div class="col-md-4 col-sm-6">
                    <div class="border rounded p-3 bg-white h-100">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-secondary-lt fw-bold"><?= SecurityHelper::escape($ua['unit_code']) ?></span>
                            <span class="badge <?= $avgU >= 90 ? 'bg-success' : ($avgU >= 80 ? 'bg-primary' : ($avgU > 0 ? 'bg-warning' : 'bg-light text-muted')) ?>">
                                <?= $avgU > 0 ? number_format($avgU, 2) : 'Belum Ada' ?>
                            </span>
                        </div>
                        <div class="fw-bold text-dark small mb-2"><?= SecurityHelper::escape($ua['unit_name']) ?></div>
                        <div class="progress progress-sm mb-2">
                            <div class="progress-bar <?= $avgU >= 90 ? 'bg-success' : ($avgU >= 80 ? 'bg-primary' : 'bg-warning') ?>" style="width: <?= min(100, $avgU) ?>%"></div>
                        </div>
                        <div class="d-flex justify-content-between text-muted" style="font-size: 11px;">
                            <span>Sesi: <strong><?= (int)$ua['total_sessions'] ?> Guru</strong></span>
                            <span>Tertinggi: <strong><?= $ua['max_score'] ? number_format((float)$ua['max_score'], 1) : '-' ?></strong></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- FILTER & PENCARIAN -->
<div class="card mb-4 shadow-sm">
    <div class="card-body p-3 bg-light rounded">
        <form method="GET" action="" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Unit Sekolah:</label>
                <select name="unit_id" class="form-select form-select-sm">
                    <option value="0">-- Seluruh Unit Binaan --</option>
                    <?php foreach ($units as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= $filterUnit == $u['id'] ? 'selected' : '' ?>>
                            <?= SecurityHelper::escape($u['code'] . ' - ' . $u['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Predikat:</label>
                <select name="predicate" class="form-select form-select-sm">
                    <option value="">-- Semua Predikat --</option>
                    <option value="SANGAT BAIK" <?= $filterPredicate === 'SANGAT BAIK' ? 'selected' : '' ?>>SANGAT BAIK</option>
                    <option value="BAIK" <?= $filterPredicate === 'BAIK' ? 'selected' : '' ?>>BAIK</option>
                    <option value="CUKUP" <?= $filterPredicate === 'CUKUP' ? 'selected' : '' ?>>CUKUP</option>
                    <option value="PERLU PENINGKATAN" <?= $filterPredicate === 'PERLU PENINGKATAN' ? 'selected' : '' ?>>PERLU PENINGKATAN</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Urutan / Ranking:</label>
                <select name="sort" class="form-select form-select-sm">
                    <option value="highest" <?= $sortOption === 'highest' ? 'selected' : '' ?>>Nilai Tertinggi ke Terendah (Top Rank)</option>
                    <option value="lowest" <?= $sortOption === 'lowest' ? 'selected' : '' ?>>Nilai Terendah ke Tertinggi</option>
                    <option value="date_desc" <?= $sortOption === 'date_desc' ? 'selected' : '' ?>>Tanggal Supervisi Terbaru</option>
                    <option value="name_asc" <?= $sortOption === 'name_asc' ? 'selected' : '' ?>>Nama Guru (A - Z)</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Cari Guru / Mapel:</label>
                <input type="text" name="q" class="form-control form-control-sm" placeholder="Ketik nama guru atau mapel..." value="<?= SecurityHelper::escape($searchQuery) ?>">
            </div>

            <div class="col-md-1">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="10" cy="10" r="7" /><line x1="21" y1="21" x2="15" y2="15" /></svg>
                    Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- TABEL REKAPITULASI DETAIL SUPERVISI -->
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center py-3 bg-light">
        <h3 class="card-title mb-0 fw-bold text-dark">
            Daftar Peringkat Nilai Supervisi Guru (<?= count($sessions) ?> Data)
        </h3>
        <span class="badge bg-primary-lt">
            Sorted by: <?= $sortOption === 'highest' ? 'Nilai Tertinggi (Rank #1)' : ($sortOption === 'lowest' ? 'Nilai Terendah' : ($sortOption === 'date_desc' ? 'Tanggal Terbaru' : 'Nama A-Z')) ?>
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-vcenter table-bordered table-hover mb-0">
            <thead class="bg-light text-center small">
                <tr>
                    <th style="width: 50px;">Rank</th>
                    <th>Nama Guru &amp; NIPY</th>
                    <th style="width: 140px;">Unit Sekolah</th>
                    <th>Mata Pelajaran &amp; Kelas</th>
                    <th style="width: 110px;">Tanggal</th>
                    <th style="width: 150px;">Supervisor</th>
                    <th style="width: 60px;">Skor A</th>
                    <th style="width: 60px;">Skor B</th>
                    <th style="width: 60px;">Skor C</th>
                    <th style="width: 100px;">Nilai Akhir</th>
                    <th style="width: 130px;">Predikat</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sessions)): ?>
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">
                            Tidak ditemukan data supervisi guru yang sesuai dengan kriteria filter.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($sessions as $idx => $s): 
                        $rank = $idx + 1;
                        $finalScore = (float)$s['final_score'];
                    ?>
                        <tr>
                            <td class="text-center fw-bold">
                                <?php if ($rank === 1 && $sortOption === 'highest'): ?>
                                    <span class="badge bg-warning text-dark p-2" title="Juara 1 / Tertinggi">
                                        <i class="fa-solid fa-trophy"></i> #1
                                    </span>
                                <?php elseif ($rank === 2 && $sortOption === 'highest'): ?>
                                    <span class="badge bg-secondary text-white p-1" title="Peringkat 2">
                                        #2
                                    </span>
                                <?php elseif ($rank === 3 && $sortOption === 'highest'): ?>
                                    <span class="badge bg-danger-lt text-dark p-1" title="Peringkat 3">
                                        #3
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">#<?= $rank ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= SecurityHelper::escape($s['teacher_name']) ?></div>
                                <div class="text-muted small" style="font-size: 11px;">
                                    NIPY: <?= SecurityHelper::escape($s['teacher_nipy'] ?? '-') ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= SecurityHelper::escape($s['unit_code'] ?? $s['unit_name']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold small"><?= SecurityHelper::escape($s['subject_name']) ?></div>
                                <div class="text-muted small" style="font-size: 11px;">
                                    Kelas: <?= SecurityHelper::escape($s['class_name'] ?? 'Semua') ?>
                                </div>
                            </td>
                            <td class="text-center small">
                                <?= date('d/m/Y', strtotime($s['session_date'])) ?>
                            </td>
                            <td class="small">
                                <?= SecurityHelper::escape($s['supervisor_name'] ?? '-') ?>
                            </td>
                            <td class="text-center small"><?= number_format((float)$s['score_a'], 1) ?></td>
                            <td class="text-center small"><?= number_format((float)$s['score_b'], 1) ?></td>
                            <td class="text-center small"><?= number_format((float)$s['score_c'], 1) ?></td>
                            <td class="text-center">
                                <span class="h3 mb-0 fw-bold text-primary">
                                    <?= number_format($finalScore, 2) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= $predicateBadge($s['achievement_predicate']) ?> p-1 px-2" style="font-size: 11px;">
                                    <?= SecurityHelper::escape($s['achievement_predicate']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php if (!empty($s['notes_good']) || !empty($s['notes_improve'])): ?>
                            <tr class="bg-light" style="font-size: 11px;">
                                <td></td>
                                <td colspan="10" class="py-1 px-3">
                                    <?php if (!empty($s['notes_good'])): ?>
                                        <div class="text-success d-inline me-3">
                                            <strong>Kelebihan:</strong> <?= SecurityHelper::escape($s['notes_good']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($s['notes_improve'])): ?>
                                        <div class="text-muted d-inline">
                                            <strong>Area Peningkatan:</strong> <?= SecurityHelper::escape($s['notes_improve']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-light py-2 text-muted small d-flex justify-content-between align-items-center">
        <span>Data supervisi ini tersinkronisasi otomatis dengan Rapor AKFI Guru dan Indikator PKG Unit.</span>
        <span>Yayasan Pendidikan Islam Al Kahfi Batam</span>
    </div>
</div>
