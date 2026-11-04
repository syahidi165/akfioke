<?php
// views/deputi/rekap_supervisi.php
use Core\SecurityHelper;

$predicateBadge = function($pred) {
    return match($pred) {
        'SANGAT BAIK' => 'badge-success',
        'BAIK'        => 'badge-info',
        'CUKUP'       => 'badge-warning',
        default       => 'badge-danger'
    };
};
?>

<div class="page-header" style="margin-bottom: 20px;">
    <div>
        <h1 class="page-title" style="display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-chart-simple" style="color: var(--primary);"></i>
            <?= $isDirektur ? 'Rekap Supervisi Guru (Seluruh Unit Yayasan)' : 'Rekap Supervisi Guru (Unit Binaan)' ?>
        </h1>
        <div class="page-subtitle">
            <?= $isDirektur ? 'Monitoring evaluasi supervisi KBM guru skala Yayasan Islam Al Kahfi' : 'Pengawasan mutu mengajar dan hasil supervisi guru satuan unit binaan' ?> &bull; 
            Kompilasi nilai, rata-rata capaian, dan pemeringkatan guru dengan nilai tertinggi.
        </div>
    </div>
    <div style="display: flex; gap: 8px;">
        <button onclick="window.print()" class="btn btn-outline btn-sm" style="padding: 7px 14px; font-weight: 600;">
            <i class="fa-solid fa-print"></i> Cetak Rekap Supervisi
        </button>
    </div>
</div>

<!-- STAT CARDS METRIK & TOP PERFORMER -->
<div class="grid-cols-4" style="margin-bottom: 20px;">
    <!-- Rata-rata Gabungan -->
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fa-solid fa-gauge-high"></i>
        </div>
        <div>
            <div class="stat-value" style="color: #2563eb;"><?= number_format($overallAvg, 1) ?></div>
            <div class="stat-label">
                Rata-rata Supervisi (<?= $overallAvg >= 90 ? 'Sangat Baik' : ($overallAvg >= 80 ? 'Baik' : 'Cukup') ?>)
            </div>
        </div>
    </div>

    <!-- Total Sesi -->
    <div class="stat-card">
        <div class="stat-icon emerald">
            <i class="fa-solid fa-chalkboard-user"></i>
        </div>
        <div>
            <div class="stat-value" style="color: #10b981;"><?= $totCount ?> Sesi</div>
            <div class="stat-label">Total Guru Disupervisi</div>
        </div>
    </div>

    <!-- Card Guru Nilai Tertinggi (Rank #1) -->
    <div class="stat-card" style="grid-column: span 2; background: linear-gradient(135deg, #fffbeb 0%, #ffffff 100%); border-color: #fde68a;">
        <div class="stat-icon amber" style="background: #fef3c7; color: #d97706;">
            <i class="fa-solid fa-trophy"></i>
        </div>
        <div style="flex: 1; min-width: 0;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2px;">
                <span class="badge badge-warning" style="font-size: 10.5px; font-weight: 700;">
                    <i class="fa-solid fa-award"></i> JUARA #1 SUPERVISI TERTINGGI
                </span>
                <?php if ($topTeacher): ?>
                    <strong style="font-size: 17px; color: #047857;">Skor <?= number_format((float)$topTeacher['final_score'], 1) ?></strong>
                <?php endif; ?>
            </div>
            <?php if ($topTeacher): ?>
                <div style="font-size: 14px; font-weight: 700; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <?= SecurityHelper::escape($topTeacher['teacher_name']) ?>
                </div>
                <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 1px;">
                    Unit: <strong><?= SecurityHelper::escape($topTeacher['unit_code'] ?? $topTeacher['unit_name']) ?></strong> &bull; 
                    Mapel: <span><?= SecurityHelper::escape($topTeacher['subject_name']) ?></span>
                </div>
            <?php else: ?>
                <div style="color: var(--text-muted); font-size: 12px;">Belum ada data sesi supervisi.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- BREAKDOWN RATA-RATA PER UNIT -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-header" style="background: #ffffff; padding: 14px 20px;">
        <h3 class="card-title" style="margin: 0; font-size: 14px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-chart-pie" style="color: var(--primary);"></i>
            Perbandingan Rata-rata Nilai per Unit Sekolah
        </h3>
    </div>
    <div class="card-body" style="padding: 16px 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px;">
            <?php foreach ($unitAverages as $ua): 
                $avgU = (float)($ua['avg_score'] ?? 0);
            ?>
                <div style="border: 1px solid var(--border-color); border-radius: 8px; padding: 12px 14px; background: #ffffff;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <span class="badge badge-secondary" style="font-weight: 700; font-size: 11px;">
                            <?= SecurityHelper::escape($ua['unit_code']) ?>
                        </span>
                        <span class="badge <?= $avgU >= 90 ? 'badge-success' : ($avgU >= 80 ? 'badge-info' : ($avgU > 0 ? 'badge-warning' : 'badge-secondary')) ?>" style="font-size: 11.5px; font-weight: 700;">
                            <?= $avgU > 0 ? number_format($avgU, 1) : '-' ?>
                        </span>
                    </div>
                    <div style="font-size: 12.5px; font-weight: 600; color: var(--text-main); margin-bottom: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <?= SecurityHelper::escape($ua['unit_name']) ?>
                    </div>
                    <div style="background: #f1f5f9; height: 6px; border-radius: 3px; overflow: hidden; margin-bottom: 8px;">
                        <div style="width: <?= min(100, $avgU) ?>%; background: <?= $avgU >= 90 ? '#10b981' : ($avgU >= 80 ? '#0284c7' : '#f59e0b') ?>; height: 100%;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 11px; color: var(--text-muted);">
                        <span>Total: <strong><?= (int)$ua['total_sessions'] ?> Guru</strong></span>
                        <span>Tertinggi: <strong><?= $ua['max_score'] ? number_format((float)$ua['max_score'], 1) : '-' ?></strong></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- FILTER & PENCARIAN -->
<div class="card" style="margin-bottom: 20px; background: #ffffff;">
    <div class="card-body" style="padding: 14px 20px;">
        <form method="GET" action="" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 12px;">
            <div style="flex: 1; min-width: 180px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Pilih Unit Sekolah:</label>
                <select name="unit_id" style="width: 100%; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 12.5px; color: var(--text-main);">
                    <option value="0">-- Semua Unit <?= $isDirektur ? 'Yayasan' : 'Binaan' ?> --</option>
                    <?php foreach ($units as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= $filterUnit == $u['id'] ? 'selected' : '' ?>>
                            <?= SecurityHelper::escape($u['code'] . ' - ' . $u['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="width: 160px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Predikat:</label>
                <select name="predicate" style="width: 100%; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 12.5px; color: var(--text-main);">
                    <option value="">-- Semua --</option>
                    <option value="SANGAT BAIK" <?= $filterPredicate === 'SANGAT BAIK' ? 'selected' : '' ?>>SANGAT BAIK</option>
                    <option value="BAIK" <?= $filterPredicate === 'BAIK' ? 'selected' : '' ?>>BAIK</option>
                    <option value="CUKUP" <?= $filterPredicate === 'CUKUP' ? 'selected' : '' ?>>CUKUP</option>
                    <option value="PERLU PENINGKATAN" <?= $filterPredicate === 'PERLU PENINGKATAN' ? 'selected' : '' ?>>PERLU PENINGKATAN</option>
                </select>
            </div>

            <div style="width: 220px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Urutan / Ranking:</label>
                <select name="sort" style="width: 100%; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 12.5px; color: var(--text-main); font-weight: 600;">
                    <option value="highest" <?= $sortOption === 'highest' ? 'selected' : '' ?>>Nilai Tertinggi (Rank #1 Default)</option>
                    <option value="lowest" <?= $sortOption === 'lowest' ? 'selected' : '' ?>>Nilai Terendah</option>
                    <option value="date_desc" <?= $sortOption === 'date_desc' ? 'selected' : '' ?>>Tanggal Supervisi Terbaru</option>
                    <option value="name_asc" <?= $sortOption === 'name_asc' ? 'selected' : '' ?>>Nama Guru (A - Z)</option>
                </select>
            </div>

            <div style="flex: 1; min-width: 180px;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Cari Guru / Mapel:</label>
                <input type="text" name="q" style="width: 100%; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 12.5px;" placeholder="Ketik nama atau mapel..." value="<?= SecurityHelper::escape($searchQuery) ?>">
            </div>

            <div>
                <button type="submit" class="btn btn-primary" style="padding: 7px 16px; font-size: 12.5px; font-weight: 600;">
                    <i class="fa-solid fa-filter"></i> Terapkan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- TABEL REKAPITULASI DETAIL SUPERVISI -->
<div class="card">
    <div class="card-header" style="background: #ffffff; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title" style="margin: 0; font-size: 14px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-ranking-star" style="color: #f59e0b;"></i>
            Peringkat &amp; Hasil Supervisi Mengajar (<?= count($sessions) ?> Sesi Terdata)
        </h3>
        <span class="badge badge-info" style="font-size: 11px;">
            <?= $sortOption === 'highest' ? 'Ranking Nilai Tertinggi' : ($sortOption === 'lowest' ? 'Urutan Nilai Terendah' : ($sortOption === 'date_desc' ? 'Urutan Tanggal Terbaru' : 'Urutan Abjad')) ?>
        </span>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 55px; text-align: center;">Rank</th>
                    <th>Nama Guru &amp; NIPY</th>
                    <th style="width: 120px; text-align: center;">Satuan Unit</th>
                    <th>Mata Pelajaran &amp; Kelas</th>
                    <th style="width: 110px; text-align: center;">Tanggal</th>
                    <th style="width: 160px;">Supervisor</th>
                    <th style="width: 65px; text-align: center;">Skor A</th>
                    <th style="width: 65px; text-align: center;">Skor B</th>
                    <th style="width: 65px; text-align: center;">Skor C</th>
                    <th style="width: 105px; text-align: center;">Nilai Akhir</th>
                    <th style="width: 140px; text-align: center;">Predikat</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sessions)): ?>
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 28px; color: var(--text-muted);">
                            <i class="fa-solid fa-magnifying-glass" style="font-size: 24px; margin-bottom: 8px; display: block; color: #cbd5e1;"></i>
                            Tidak ada data sesi supervisi guru yang sesuai dengan kriteria filter.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($sessions as $idx => $s): 
                        $rank = $idx + 1;
                        $finalScore = (float)$s['final_score'];
                    ?>
                        <tr style="transition: background-color 0.2s;">
                            <td style="text-align: center; font-weight: 700;">
                                <?php if ($rank === 1 && $sortOption === 'highest'): ?>
                                    <span class="badge badge-warning" style="font-size: 12px; padding: 4px 8px; font-weight: 700; background: #fef3c7; color: #92400e; border: 1px solid #fde68a;">
                                        <i class="fa-solid fa-trophy" style="color: #d97706;"></i> #1
                                    </span>
                                <?php elseif ($rank === 2 && $sortOption === 'highest'): ?>
                                    <span class="badge badge-secondary" style="font-size: 11.5px; padding: 4px 8px; font-weight: 700; background: #e2e8f0; color: #334155;">
                                        #2
                                    </span>
                                <?php elseif ($rank === 3 && $sortOption === 'highest'): ?>
                                    <span class="badge badge-secondary" style="font-size: 11.5px; padding: 4px 8px; font-weight: 700; background: #ffedd5; color: #9a3412;">
                                        #3
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 12px;">#<?= $rank ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="color: var(--text-main); font-size: 13.5px;"><?= SecurityHelper::escape($s['teacher_name']) ?></strong>
                                <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                                    NIPY: <?= SecurityHelper::escape($s['teacher_nipy'] ?? '-') ?>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-secondary" style="font-size: 11px; font-weight: 700;">
                                    <?= SecurityHelper::escape($s['unit_code'] ?? $s['unit_name']) ?>
                                </span>
                            </td>
                            <td>
                                <strong style="color: #334155; font-size: 12.5px;"><?= SecurityHelper::escape($s['subject_name']) ?></strong>
                                <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                                    Kelas: <span><?= SecurityHelper::escape($s['class_name'] ?? 'Semua') ?></span>
                                </div>
                            </td>
                            <td style="text-align: center; font-size: 12px; color: var(--text-main);">
                                <?= date('d/m/Y', strtotime($s['session_date'])) ?>
                            </td>
                            <td style="font-size: 12px; color: var(--text-main);">
                                <?= SecurityHelper::escape($s['supervisor_name'] ?? '-') ?>
                            </td>
                            <td style="text-align: center; font-size: 12px; color: var(--text-muted);"><?= number_format((float)$s['score_a'], 1) ?></td>
                            <td style="text-align: center; font-size: 12px; color: var(--text-muted);"><?= number_format((float)$s['score_b'], 1) ?></td>
                            <td style="text-align: center; font-size: 12px; color: var(--text-muted);"><?= number_format((float)$s['score_c'], 1) ?></td>
                            <td style="text-align: center;">
                                <span style="font-size: 15px; font-weight: 800; color: var(--primary);">
                                    <?= number_format($finalScore, 1) ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge <?= $predicateBadge($s['achievement_predicate']) ?>" style="font-size: 11px; font-weight: 700; padding: 4px 8px;">
                                    <?= SecurityHelper::escape($s['achievement_predicate']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php if (!empty($s['notes_good']) || !empty($s['notes_improve'])): ?>
                            <tr style="background: #fafafa; font-size: 11.5px;">
                                <td></td>
                                <td colspan="10" style="padding: 6px 16px;">
                                    <?php if (!empty($s['notes_good'])): ?>
                                        <span style="color: #15803d; margin-right: 14px;">
                                            <i class="fa-solid fa-thumbs-up" style="margin-right: 3px;"></i> <strong>Kelebihan:</strong> <?= SecurityHelper::escape($s['notes_good']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($s['notes_improve'])): ?>
                                        <span style="color: #64748b;">
                                            <i class="fa-solid fa-lightbulb" style="color: #f59e0b; margin-right: 3px;"></i> <strong>Rekomendasi:</strong> <?= SecurityHelper::escape($s['notes_improve']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: var(--text-muted);">
        <span>Data supervisi ini tersinkronisasi otomatis dengan Rapor AKFI Guru dan Indikator PKG Satuan.</span>
        <span>Yayasan Pendidikan Islam Al Kahfi Batam</span>
    </div>
</div>
