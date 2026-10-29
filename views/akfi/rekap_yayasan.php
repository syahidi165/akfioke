<?php
// views/akfi/rekap_yayasan.php
use Core\SecurityHelper;
use Core\Auth;

$baseUrl = Auth::baseUrl();

// Hitung metrik keseluruhan
$totalAllEmployees = 0;
$totalAllAssessed = 0;
$totalAllFinal = 0;
$totalAllDisetujui = 0;
$totalAllDraft = 0;
$sumScores = 0;
$countScoreUnits = 0;
$totalGradeA = 0;
$totalGradeB = 0;
$totalGradeC = 0;
$totalGradeDE = 0;

foreach ($unitsSummary as $u) {
    $totalAllEmployees += (int)$u['total_pegawai'];
    $totalAllAssessed  += (int)$u['total_dinilai'];
    $totalAllFinal     += (int)$u['total_final'];
    $totalAllDisetujui += (int)$u['total_disetujui'];
    $totalAllDraft     += (int)$u['total_draft'];
    $totalGradeA       += (int)$u['grade_a'];
    $totalGradeB       += (int)$u['grade_b'];
    $totalGradeC       += (int)$u['grade_c'];
    $totalGradeDE      += (int)$u['grade_de'];

    if ($u['avg_score'] !== null && (float)$u['avg_score'] > 0) {
        $sumScores += ((float)$u['avg_score'] * (int)$u['total_dinilai']);
        $countScoreUnits += (int)$u['total_dinilai'];
    }
}

$overallAvg = $countScoreUnits > 0 ? round($sumScores / $countScoreUnits, 1) : 0;
$totalAllUnassessed = max(0, $totalAllEmployees - $totalAllAssessed);
$overallProgress = $totalAllEmployees > 0 ? round(($totalAllAssessed / $totalAllEmployees) * 100, 1) : 0;
?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-chart-pie" style="color: var(--primary); margin-right: 8px;"></i> Rekapitulasi Rapor Kinerja AKFI Yayasan</h1>
        <div class="page-subtitle">Monitoring komprehensif evaluasi kinerja, progres penilaian, dan sebaran predikat seluruh pegawai & satuan pendidikan Al-Kahfi.</div>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="<?= $baseUrl ?>/akfi/export-xlsx" class="btn btn-outline" style="border-color: #10b981; color: #047857;">
            <i class="fa-solid fa-file-excel" style="color: #10b981;"></i> Unduh Excel (XLSX)
        </a>
        <a href="<?= $baseUrl ?>/akfi/kanit-preview" class="btn btn-primary">
            <i class="fa-solid fa-user-tie"></i> Pratinjau Kanit (KS)
        </a>
    </div>
</div>

<!-- KPI Summary Cards -->
<div class="row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(15, 118, 110, 0.1); color: var(--primary);">
            <i class="fa-solid fa-users"></i>
        </div>
        <div class="stat-value"><?= number_format($totalAllEmployees) ?></div>
        <div class="stat-label">Total Seluruh Pegawai</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #059669;">
            <i class="fa-solid fa-file-signature"></i>
        </div>
        <div class="stat-value"><?= number_format($totalAllAssessed) ?></div>
        <div class="stat-label">Sudah Dinilai (<?= $overallProgress ?>%)</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #d97706;">
            <i class="fa-solid fa-user-clock"></i>
        </div>
        <div class="stat-value"><?= number_format($totalAllUnassessed) ?></div>
        <div class="stat-label">Belum Dinilai</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(2, 132, 199, 0.1); color: #0284c7;">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="stat-value"><?= number_format($totalAllFinal) ?></div>
        <div class="stat-label">Disahkan Final</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(124, 58, 237, 0.1); color: #7c3aed;">
            <i class="fa-solid fa-award"></i>
        </div>
        <div class="stat-value"><?= $overallAvg > 0 ? $overallAvg : '-' ?></div>
        <div class="stat-label">Rata-rata Skor Yayasan</div>
    </div>
</div>

<!-- Kartu Ringkasan Per Unit -->
<div class="card mb-4" style="border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid var(--border-color); padding: 14px 20px; font-weight: 600; display: flex; justify-content: space-between; align-items: center;">
        <span><i class="fa-solid fa-school text-primary" style="margin-right: 8px;"></i> Perbandingan Capaian AKFI Antar Satuan Pendidikan</span>
        <span class="badge" style="background: #e2e8f0; color: #334155; font-size: 11px; padding: 4px 8px; border-radius: 4px;">Tahun Akademik Aktif</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center" style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f1f5f9; color: #475569; font-size: 12px; font-weight: 700; text-transform: uppercase;">
                    <tr>
                        <th style="padding: 12px 8px; width: 45px;">No</th>
                        <th class="text-start" style="padding: 12px 16px;">Satuan Pendidikan</th>
                        <th style="padding: 12px 8px;">Total Pegawai</th>
                        <th style="padding: 12px 8px;">Sudah Dinilai</th>
                        <th style="padding: 12px 8px;">Belum Dinilai</th>
                        <th style="padding: 12px 8px;">Disahkan Final</th>
                        <th style="padding: 12px 12px; min-width: 130px;">Progres (%)</th>
                        <th style="padding: 12px 8px;">Rata-rata Skor</th>
                        <th style="padding: 12px 8px;">Predikat A</th>
                        <th style="padding: 12px 8px;">Predikat B</th>
                        <th style="padding: 12px 8px;">Predikat C</th>
                        <th style="padding: 12px 8px;">Predikat D/E</th>
                    </tr>
                </thead>
                <tbody style="font-size: 13px;">
                    <?php if (empty($unitsSummary)): ?>
                        <tr><td colspan="12" style="padding: 24px; color: var(--text-muted);">Belum ada data unit satuan pendidikan.</td></tr>
                    <?php else: ?>
                        <?php foreach ($unitsSummary as $idx => $u): 
                            $tot = (int)$u['total_pegawai'];
                            $dinilai = (int)$u['total_dinilai'];
                            $belum = max(0, $tot - $dinilai);
                            $pct = $tot > 0 ? round(($dinilai / $tot) * 100, 1) : 0;
                            $avg = $u['avg_score'] !== null && (float)$u['avg_score'] > 0 ? round((float)$u['avg_score'], 1) : '-';
                            $isPusat = ((int)$u['id'] === 0);
                        ?>
                            <tr style="<?= $isPusat ? 'background: #f8fafc;' : '' ?>">
                                <td style="padding: 12px 8px; color: var(--text-muted);"><?= $idx + 1 ?></td>
                                <td class="text-start" style="padding: 12px 16px; font-weight: 600;">
                                    <?php if ($isPusat): ?>
                                        <i class="fa-solid fa-building text-secondary" style="margin-right: 6px;"></i>
                                    <?php else: ?>
                                        <i class="fa-solid fa-graduation-cap text-primary" style="margin-right: 6px;"></i>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($u['name']) ?>
                                </td>
                                <td style="padding: 12px 8px; font-weight: 700; color: #1e293b;"><?= number_format($tot) ?></td>
                                <td style="padding: 12px 8px; color: #059669; font-weight: 600;"><?= number_format($dinilai) ?></td>
                                <td style="padding: 12px 8px; color: <?= $belum > 0 ? '#d97706' : '#94a3b8' ?>; font-weight: 600;"><?= number_format($belum) ?></td>
                                <td style="padding: 12px 8px;"><span class="badge" style="background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 4px; font-weight: 600;"><?= number_format((int)$u['total_final']) ?></span></td>
                                <td style="padding: 12px 12px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="flex: 1; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                                            <div style="height: 100%; width: <?= min(100, $pct) ?>%; background: <?= $pct >= 80 ? '#10b981' : ($pct >= 50 ? '#0284c7' : '#f59e0b') ?>; border-radius: 4px;"></div>
                                        </div>
                                        <span style="font-size: 11px; font-weight: 600; color: #475569; min-width: 38px; text-align: right;"><?= $pct ?>%</span>
                                    </div>
                                </td>
                                <td style="padding: 12px 8px;"><strong style="color: var(--primary); font-size: 14px;"><?= $avg ?></strong></td>
                                <td style="padding: 12px 8px;"><span class="badge" style="background: #dcfce7; color: #15803d; border-radius: 12px; padding: 2px 8px;"><?= (int)$u['grade_a'] ?></span></td>
                                <td style="padding: 12px 8px;"><span class="badge" style="background: #e0f2fe; color: #0369a1; border-radius: 12px; padding: 2px 8px;"><?= (int)$u['grade_b'] ?></span></td>
                                <td style="padding: 12px 8px;"><span class="badge" style="background: #fef3c7; color: #b45309; border-radius: 12px; padding: 2px 8px;"><?= (int)$u['grade_c'] ?></span></td>
                                <td style="padding: 12px 8px;"><span class="badge" style="background: #fee2e2; color: #b91c1c; border-radius: 12px; padding: 2px 8px;"><?= (int)$u['grade_de'] ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot style="background: #f8fafc; font-weight: 700; border-top: 2px solid var(--border-color); font-size: 13px;">
                    <tr>
                        <td colspan="2" class="text-start" style="padding: 12px 16px; text-transform: uppercase;">Total Seluruh Yayasan</td>
                        <td style="padding: 12px 8px; color: #1e293b;"><?= number_format($totalAllEmployees) ?></td>
                        <td style="padding: 12px 8px; color: #059669;"><?= number_format($totalAllAssessed) ?></td>
                        <td style="padding: 12px 8px; color: #d97706;"><?= number_format($totalAllUnassessed) ?></td>
                        <td style="padding: 12px 8px; color: #0369a1;"><?= number_format($totalAllFinal) ?></td>
                        <td style="padding: 12px 12px;">
                            <span class="badge" style="background: #dcfce7; color: #15803d; padding: 4px 10px; border-radius: 4px;"><?= $overallProgress ?>% Dinilai</span>
                        </td>
                        <td style="padding: 12px 8px; color: var(--primary); font-size: 14px;"><?= $overallAvg > 0 ? $overallAvg : '-' ?></td>
                        <td style="padding: 12px 8px; color: #15803d;"><?= number_format($totalGradeA) ?></td>
                        <td style="padding: 12px 8px; color: #0369a1;"><?= number_format($totalGradeB) ?></td>
                        <td style="padding: 12px 8px; color: #b45309;"><?= number_format($totalGradeC) ?></td>
                        <td style="padding: 12px 8px; color: #b91c1c;"><?= number_format($totalGradeDE) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Daftar Rinci Seluruh Pegawai -->
<div class="card" style="border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid var(--border-color); padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-list-check text-primary"></i>
            <span style="font-weight: 600;">Daftar Rinci Kinerja Seluruh Pegawai & Rapor AKFI</span>
            <span class="badge" style="background: #e2e8f0; color: #334155; font-size: 11px; padding: 4px 8px; border-radius: 12px; margin-left: 6px;"><?= count($allEmployees) ?> Total Pegawai</span>
        </div>
        
        <!-- Filter Bar -->
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <div style="position: relative; min-width: 220px;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 10px; top: 10px; color: #94a3b8; font-size: 12px;"></i>
                <input type="text" id="searchEmpInput" placeholder="Cari Nama Pegawai / NIPY..." class="form-control" style="padding-left: 30px; font-size: 12px; height: 34px; border-radius: 6px;" onkeyup="filterEmployeeRows()">
            </div>
            
            <select id="filterUnitSelect" class="form-control" style="font-size: 12px; height: 34px; width: auto; border-radius: 6px;" onchange="filterEmployeeRows()">
                <option value="">Semua Satuan / Unit</option>
                <?php foreach ($unitsSummary as $u): ?>
                    <option value="<?= htmlspecialchars($u['name']) ?>"><?= htmlspecialchars($u['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <select id="filterStatusSelect" class="form-control" style="font-size: 12px; height: 34px; width: auto; border-radius: 6px;" onchange="filterEmployeeRows()">
                <option value="">Semua Status AKFI</option>
                <option value="Final">Disahkan (Final)</option>
                <option value="Disetujui">Disetujui Atasan</option>
                <option value="Draft">Draft Penilaian</option>
                <option value="Belum Dinilai">Belum Memiliki Rapor</option>
            </select>
        </div>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="empTable" style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f1f5f9; color: #475569; font-size: 12px; font-weight: 700; text-transform: uppercase;">
                    <tr>
                        <th style="padding: 12px 10px; width: 45px; text-align: center;">No</th>
                        <th style="padding: 12px 16px;">Pegawai</th>
                        <th style="padding: 12px 12px;">Unit Tugas</th>
                        <th style="padding: 12px 12px;">Jabatan / Peran</th>
                        <th style="padding: 12px 8px; text-align: center;">Kinerja</th>
                        <th style="padding: 12px 8px; text-align: center;">Sikap</th>
                        <th style="padding: 12px 8px; text-align: center;">Absensi</th>
                        <th style="padding: 12px 8px; text-align: center;">Total Skor</th>
                        <th style="padding: 12px 8px; text-align: center;">Predikat</th>
                        <th style="padding: 12px 10px; text-align: center;">Status AKFI</th>
                        <th style="padding: 12px 14px; text-align: center; width: 110px;">Aksi</th>
                    </tr>
                </thead>
                <tbody style="font-size: 13px;">
                    <?php if (empty($allEmployees)): ?>
                        <tr><td colspan="11" class="text-center py-4 text-muted">Belum ada data pegawai di sistem.</td></tr>
                    <?php else: ?>
                        <?php foreach ($allEmployees as $idx => $e): 
                            $hasAssessment = !empty($e['assessment_id']);
                            $status = $e['assessment_status'] ?? 'Belum Dinilai';
                            $grade = $e['letter_grade'] ?? '-';
                        ?>
                            <tr class="emp-row" data-unit="<?= htmlspecialchars($e['unit_name']) ?>" data-status="<?= htmlspecialchars($status) ?>">
                                <td style="padding: 12px 10px; text-align: center; color: var(--text-muted);"><?= $idx + 1 ?></td>
                                <td style="padding: 12px 16px;">
                                    <div class="emp-name" style="font-weight: 600; color: #1e293b;"><?= htmlspecialchars($e['nama']) ?></div>
                                    <div class="emp-nipy" style="font-size: 11px; color: #64748b;">NIPY: <?= htmlspecialchars($e['nipy'] ?: '-') ?></div>
                                </td>
                                <td style="padding: 12px 12px;">
                                    <span style="font-size: 12px; font-weight: 500; color: #334155;"><?= htmlspecialchars($e['unit_name']) ?></span>
                                </td>
                                <td style="padding: 12px 12px;">
                                    <span style="font-size: 12px; color: var(--primary); font-weight: 600;"><?= htmlspecialchars($e['position_name']) ?></span>
                                </td>
                                <td style="padding: 12px 8px; text-align: center; font-family: monospace; font-size: 13px;">
                                    <?= $hasAssessment ? htmlspecialchars($e['kinerja_score']) : '-' ?>
                                </td>
                                <td style="padding: 12px 8px; text-align: center; font-family: monospace; font-size: 13px;">
                                    <?= $hasAssessment ? htmlspecialchars($e['sikap_score']) : '-' ?>
                                </td>
                                <td style="padding: 12px 8px; text-align: center; font-family: monospace; font-size: 13px;">
                                    <?= $hasAssessment ? htmlspecialchars($e['absensi_score']) : '-' ?>
                                </td>
                                <td style="padding: 12px 8px; text-align: center;">
                                    <?php if ($hasAssessment): ?>
                                        <strong style="color: var(--primary); font-size: 14px;"><?= $e['total_score'] ?></strong>
                                    <?php else: ?>
                                        <span style="color: #94a3b8;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 8px; text-align: center;">
                                    <?php if ($grade === 'A'): ?>
                                        <span class="badge" style="background: #dcfce7; color: #15803d; font-weight: 700; padding: 3px 8px; border-radius: 4px;">A</span>
                                    <?php elseif ($grade === 'B'): ?>
                                        <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 700; padding: 3px 8px; border-radius: 4px;">B</span>
                                    <?php elseif ($grade === 'C'): ?>
                                        <span class="badge" style="background: #fef3c7; color: #b45309; font-weight: 700; padding: 3px 8px; border-radius: 4px;">C</span>
                                    <?php elseif (in_array($grade, ['D', 'E'])): ?>
                                        <span class="badge" style="background: #fee2e2; color: #b91c1c; font-weight: 700; padding: 3px 8px; border-radius: 4px;"><?= $grade ?></span>
                                    <?php else: ?>
                                        <span style="color: #94a3b8;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 10px; text-align: center;">
                                    <?php if ($status === 'Final'): ?>
                                        <span class="badge" style="background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                            <i class="fa-solid fa-circle-check"></i> Final
                                        </span>
                                    <?php elseif ($status === 'Disetujui_Atasan'): ?>
                                        <span class="badge" style="background: #e0f2fe; color: #075985; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                            <i class="fa-solid fa-check"></i> Disetujui
                                        </span>
                                    <?php elseif ($status === 'Draft'): ?>
                                        <span class="badge" style="background: #fef3c7; color: #92400e; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                            <i class="fa-solid fa-pen"></i> Draft
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; padding: 4px 8px; border-radius: 4px; font-size: 11px;">
                                            Belum Dinilai
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 14px; text-align: center;">
                                    <?php if ($hasAssessment): ?>
                                        <a href="<?= $baseUrl ?>/akfi/print-rapor/<?= $e['assessment_id'] ?>" target="_blank" class="btn btn-outline btn-sm" style="font-size: 11px; padding: 4px 10px; border-radius: 4px;" title="Cetak Rapor Resmi">
                                            <i class="fa-solid fa-print"></i> Rapor
                                        </a>
                                    <?php else: ?>
                                        <span style="font-size: 11px; color: #94a3b8; font-style: italic;">Menunggu KS</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div id="noDataAlert" style="display: none; padding: 30px; text-align: center; color: var(--text-muted);">
            <i class="fa-solid fa-filter-circle-xmark" style="font-size: 24px; margin-bottom: 8px; display: block; color: #cbd5e1;"></i>
            Tidak ada pegawai yang cocok dengan filter pencarian yang dipilih.
        </div>
    </div>
</div>

<script>
function filterEmployeeRows() {
    var search = document.getElementById('searchEmpInput').value.toLowerCase().trim();
    var selectedUnit = document.getElementById('filterUnitSelect').value.toLowerCase().trim();
    var selectedStatus = document.getElementById('filterStatusSelect').value.toLowerCase().trim();
    
    var rows = document.querySelectorAll('.emp-row');
    var visibleCount = 0;
    
    rows.forEach(function(row) {
        var name = (row.querySelector('.emp-name')?.textContent || '').toLowerCase();
        var nipy = (row.querySelector('.emp-nipy')?.textContent || '').toLowerCase();
        var unit = (row.getAttribute('data-unit') || '').toLowerCase();
        var status = (row.getAttribute('data-status') || '').toLowerCase();
        
        var matchesSearch = (search === '' || name.includes(search) || nipy.includes(search));
        var matchesUnit = (selectedUnit === '' || unit.includes(selectedUnit));
        
        var matchesStatus = true;
        if (selectedStatus === 'final') {
            matchesStatus = status === 'final';
        } else if (selectedStatus === 'disetujui') {
            matchesStatus = status.includes('disetujui');
        } else if (selectedStatus === 'draft') {
            matchesStatus = status === 'draft';
        } else if (selectedStatus === 'belum dinilai') {
            matchesStatus = status.includes('belum dinilai');
        }
        
        if (matchesSearch && matchesUnit && matchesStatus) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    var noDataAlert = document.getElementById('noDataAlert');
    if (noDataAlert) {
        noDataAlert.style.display = (visibleCount === 0 && rows.length > 0) ? 'block' : 'none';
    }
}
</script>
