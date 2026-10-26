<?php
// views/administrasi/review_ks.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$activeAcademicYearName = Auth::academicYearName() ?? 'Tahun Ajaran Aktif';
?>
<div class="page-header" style="margin-bottom: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title" style="display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-folder-check" style="color: var(--primary);"></i>
                Approval Administrasi Kepala Sekolah
            </h1>
            <div class="page-subtitle">
                Panel Deputi Direktur Pendidikan untuk memeriksa 18 dokumen administrasi Kepala Sekolah seluruh satuan unit.
            </div>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <!-- Filter Unit Header Dropdown -->
            <form method="GET" action="<?= $baseUrl ?>/administrasi/review-ks" style="display: flex; align-items: center; gap: 8px;">
                <select name="unit_id" class="form-control" onchange="this.form.submit()" style="height: 36px; font-size: 12px; font-weight: 600; padding: 4px 10px; min-width: 190px;">
                    <option value="0">-- Seluruh Unit Sekolah --</option>
                    <?php foreach ($units as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= $unitFilter === (int)$u['id'] ? 'selected' : '' ?>>
                            <?= SecurityHelper::escape($u['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <span class="badge badge-secondary" style="font-size: 12px; padding: 8px 12px;">
                <i class="fa-solid fa-calendar-alt" style="margin-right: 5px;"></i> <?= SecurityHelper::escape($activeAcademicYearName) ?>
            </span>
        </div>
    </div>
</div>

<!-- STATISTIK RINGKASAN ATAS -->
<div class="grid-cols-4" style="margin-bottom: 24px;">
    <div class="stat-card" style="box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
        <div class="stat-icon blue">
            <i class="fa-solid fa-school"></i>
        </div>
        <div>
            <div class="stat-value"><?= (int)($summary['total_units'] ?? 0) ?></div>
            <div class="stat-label">Unit / Kepala Sekolah</div>
        </div>
    </div>

    <div class="stat-card" style="box-shadow: 0 2px 6px rgba(0,0,0,0.04); <?= ($summary['total_pending'] ?? 0) > 0 ? 'border: 1px solid #f59e0b; background: #fffbeb;' : '' ?>">
        <div class="stat-icon amber" style="<?= ($summary['total_pending'] ?? 0) > 0 ? 'background: #fde68a; color: #b45309;' : '' ?>">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
        <div>
            <div class="stat-value" style="<?= ($summary['total_pending'] ?? 0) > 0 ? 'color: #b45309;' : '' ?>">
                <?= (int)($summary['total_pending'] ?? 0) ?>
            </div>
            <div class="stat-label">Menunggu Review</div>
        </div>
    </div>

    <div class="stat-card" style="box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
        <div class="stat-icon emerald">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div>
            <div class="stat-value" style="color: #15803d;"><?= (int)($summary['total_approved'] ?? 0) ?></div>
            <div class="stat-label">Berkas Disetujui</div>
        </div>
    </div>

    <div class="stat-card" style="box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
        <div class="stat-icon" style="background: #f3e8ff; color: #7e22ce;">
            <i class="fa-solid fa-folder-tree"></i>
        </div>
        <div>
            <div class="stat-value" style="color: #7e22ce;"><?= (int)($summary['total_files'] ?? 0) ?></div>
            <div class="stat-label">Total Dokumen Masuk</div>
        </div>
    </div>
</div>

<!-- TOOLBAR: FILTER & SEARCH -->
<div class="card" style="margin-bottom: 24px; padding: 16px;">
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 14px;">
        <!-- Filter Tabs / Kategori -->
        <div class="filter-pills-wrapper" style="display: flex; flex-wrap: wrap; gap: 8px;">
            <button type="button" class="filter-btn active" data-filter="all" onclick="filterCards('all', this)">
                Semua Unit (<?= count($ksCards ?? []) ?>)
            </button>
            <?php if (($summary['units_with_pending'] ?? 0) > 0): ?>
                <button type="button" class="filter-btn filter-btn-pending" data-filter="pending" onclick="filterCards('pending', this)">
                    <i class="fa-solid fa-bell"></i> Perlu Review (<?= $summary['units_with_pending'] ?>)
                </button>
            <?php endif; ?>
            <button type="button" class="filter-btn" data-filter="complete" onclick="filterCards('complete', this)">
                Lengkap 100% (<?= $summary['units_complete'] ?? 0 ?>)
            </button>
            <button type="button" class="filter-btn" data-filter="incomplete" onclick="filterCards('incomplete', this)">
                Belum Lengkap (<?= $summary['units_incomplete'] ?? 0 ?>)
            </button>
        </div>

        <!-- Search Bar & Sort Dropdown -->
        <div style="display: flex; gap: 10px; align-items: center; flex: 1; max-width: 480px; justify-content: flex-end;">
            <div style="position: relative; width: 100%;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 13px;"></i>
                <input type="text" id="ksSearchInput" class="form-control" placeholder="Cari nama Kepala Sekolah atau unit..." oninput="handleSearch()" style="padding-left: 36px; height: 38px;">
            </div>
            <select id="ksSortSelect" class="form-control" style="width: 180px; height: 38px;" onchange="handleSort()">
                <option value="pending_first">Prioritas: Perlu Review</option>
                <option value="name_asc">Nama Unit (A - Z)</option>
                <option value="percent_desc">Paling Lengkap (%)</option>
                <option value="missing_desc">Paling Banyak Kurang</option>
            </select>
        </div>
    </div>
</div>

<!-- GRID KARTU KEPALA SEKOLAH -->
<div id="ksCardsContainer" class="teacher-grid">
    <?php if (empty($ksCards)): ?>
        <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 48px;">
            <i class="fa-solid fa-school-circle-xmark" style="font-size: 48px; color: #cbd5e1; margin-bottom: 12px;"></i>
            <h3 style="color: var(--text-main); font-weight: 600;">Belum Ada Unit Terdaftar</h3>
            <p style="color: var(--text-muted); font-size: 13px;">Tidak ada unit sekolah yang sesuai dengan filter.</p>
        </div>
    <?php else: ?>
        <?php foreach ($ksCards as $index => $k): ?>
            <div class="teacher-card ks-card" 
                 id="card-<?= $index ?>"
                 data-index="<?= $index ?>"
                 data-name="<?= strtolower(SecurityHelper::escape($k['name'] . ' ' . $k['unit_name'])) ?>"
                 data-unit="<?= strtolower(SecurityHelper::escape($k['unit_name'])) ?>"
                 data-pending="<?= (int)$k['pending_count'] ?>"
                 data-percent="<?= (int)$k['percent'] ?>"
                 data-missing="<?= (int)$k['missing_count'] ?>"
                 data-complete="<?= $k['percent'] >= 100 ? '1' : '0' ?>">
                
                <!-- Card Header -->
                <div class="teacher-card-header">
                    <div style="display: flex; gap: 12px; align-items: center; min-width: 0;">
                        <!-- Avatar Inisial -->
                        <div class="teacher-avatar category-walas" style="background: linear-gradient(135deg, #1e3a8a, #0284c7);">
                            <?php
                            $words = explode(' ', trim($k['name']));
                            $initials = '';
                            foreach (array_slice($words, 0, 2) as $w) {
                                $initials .= strtoupper(mb_substr($w, 0, 1));
                            }
                            echo $initials ?: 'KS';
                            ?>
                        </div>
                        <div style="min-width: 0; flex: 1;">
                            <h3 class="teacher-card-name" title="<?= SecurityHelper::escape($k['name']) ?>">
                                <?= SecurityHelper::escape($k['name']) ?>
                            </h3>
                            <div class="teacher-card-meta">
                                <span class="badge badge-info" style="font-size: 11px;">
                                    <i class="fa-solid fa-school"></i> <?= SecurityHelper::escape($k['unit_name']) ?>
                                </span>
                                <?php if (!empty($k['nipy']) && $k['nipy'] !== '-'): ?>
                                    <span class="nipy-pill"><i class="fa-solid fa-id-badge"></i> <?= SecurityHelper::escape($k['nipy']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Top Status Badge -->
                    <div style="margin-top: 10px; display: flex; justify-content: space-between; align-items: center;">
                        <?php if ($k['pending_count'] > 0): ?>
                            <span class="badge badge-warning" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-weight: 700;">
                                <i class="fa-solid fa-bell" style="animation: bellShake 2s infinite;"></i> <?= $k['pending_count'] ?> Menunggu Review
                            </span>
                        <?php elseif ($k['percent'] >= 100): ?>
                            <span class="badge badge-success" style="background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;">
                                <i class="fa-solid fa-circle-check"></i> Lengkap 100% (18/18)
                            </span>
                        <?php elseif ($k['uploaded_count'] == 0): ?>
                            <span class="badge badge-danger" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;">
                                <i class="fa-solid fa-circle-xmark"></i> Belum Upload
                            </span>
                        <?php else: ?>
                            <span class="badge badge-info" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">
                                <i class="fa-solid fa-clock"></i> <?= $k['percent'] ?>% Terkumpul
                            </span>
                        <?php endif; ?>

                        <span class="badge" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; font-weight: 600;">
                            18 Arsip Wajib
                        </span>
                    </div>
                </div>

                <!-- Card Body -->
                <div class="teacher-card-body">
                    <div class="metrics-dual-box">
                        <!-- BOX SUDAH DIUNGGAH -->
                        <div class="metric-box box-uploaded">
                            <div class="metric-num text-success">
                                <?= $k['uploaded_count'] ?> <span style="font-size: 13px; font-weight: 500; color: #16a34a;">/ <?= $k['expected_count'] ?></span>
                            </div>
                            <div class="metric-title">
                                <i class="fa-solid fa-file-circle-check" style="color: #16a34a;"></i> Sudah Diunggah
                            </div>
                            <div class="metric-sub"><?= $k['total_files'] ?> berkas masuk</div>
                        </div>

                        <!-- BOX BELUM DIUNGGAH -->
                        <div class="metric-box box-missing">
                            <div class="metric-num text-danger">
                                <?= $k['missing_count'] ?>
                            </div>
                            <div class="metric-title">
                                <i class="fa-solid fa-file-circle-xmark" style="color: #ef4444;"></i> Belum Diunggah
                            </div>
                            <div class="metric-sub"><?= $k['missing_count'] == 0 ? 'Semua beres' : 'Item wajib kurang' ?></div>
                        </div>
                    </div>

                    <!-- Progress Bar Kelengkapan -->
                    <div style="margin-top: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 5px;">
                            <span style="color: var(--text-muted); font-weight: 500;">Kelengkapan Dokumen KS:</span>
                            <strong style="color: var(--text-main);"><?= $k['percent'] ?>%</strong>
                        </div>
                        <div class="progress-track">
                            <div class="progress-bar-fill progress-<?= $k['percent'] >= 100 ? 'full' : ($k['percent'] >= 50 ? 'half' : ($k['percent'] > 0 ? 'low' : 'zero')) ?>"
                                 style="width: <?= $k['percent'] ?>%;"></div>
                        </div>
                    </div>

                    <!-- Rincian Status Verifikasi -->
                    <div class="status-chips-row">
                        <span class="chip chip-success" title="Berkas Disetujui">
                            <i class="fa-solid fa-check"></i> Disetujui: <strong><?= $k['approved_count'] ?></strong>
                        </span>
                        <span class="chip chip-warning" title="Berkas Menunggu Review">
                            <i class="fa-solid fa-hourglass-half"></i> Menunggu: <strong><?= $k['pending_count'] ?></strong>
                        </span>
                        <?php if ($k['revision_count'] > 0): ?>
                            <span class="chip chip-danger" title="Berkas Perlu Revisi">
                                <i class="fa-solid fa-triangle-exclamation"></i> Revisi: <strong><?= $k['revision_count'] ?></strong>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Klasifikasi Periode Pengumpulan Dokumen -->
                    <div class="period-breakdown-row" style="margin-top: 10px; display: flex; flex-wrap: wrap; gap: 5px; font-size: 11px;">
                        <?php 
                        $pStats = $k['period_stats'] ?? [];
                        if (!empty($pStats)):
                        ?>
                            <?php if (($pStats['bulanan']['expected'] ?? 0) > 0 || ($pStats['bulanan']['uploaded'] ?? 0) > 0): ?>
                                <span class="badge" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 10.5px; padding: 2px 7px;" title="Laporan Bulanan & Closing Keuangan (CA)">
                                    <i class="fa-solid fa-calendar-days"></i> Bulanan: <strong><?= (int)$pStats['bulanan']['uploaded'] ?></strong>/<?= (int)$pStats['bulanan']['expected'] ?>
                                </span>
                            <?php endif; ?>
                            <?php if (($pStats['tahunan']['expected'] ?? 0) > 0 || ($pStats['tahunan']['uploaded'] ?? 0) > 0): ?>
                                <span class="badge" style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-size: 10.5px; padding: 2px 7px;" title="Dokumen Periode 1x Setahun (Tahunan)">
                                    <i class="fa-solid fa-calendar"></i> 1x Setahun: <strong><?= (int)$pStats['tahunan']['uploaded'] ?></strong>/<?= (int)$pStats['tahunan']['expected'] ?>
                                </span>
                            <?php endif; ?>
                            <?php if (($pStats['semester']['expected'] ?? 0) > 0 || ($pStats['semester']['uploaded'] ?? 0) > 0): ?>
                                <span class="badge" style="background: #fefce8; color: #854d0e; border: 1px solid #fef08a; font-size: 10.5px; padding: 2px 7px;" title="Dokumen Periode 2x Setahun (Semester)">
                                    <i class="fa-solid fa-calendar-week"></i> 2x Setahun: <strong><?= (int)$pStats['semester']['uploaded'] ?></strong>/<?= (int)$pStats['semester']['expected'] ?>
                                </span>
                            <?php endif; ?>
                            <?php if (($pStats['lainnya']['expected'] ?? 0) > 0 || ($pStats['lainnya']['uploaded'] ?? 0) > 0): ?>
                                <span class="badge" style="background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; font-size: 10.5px; padding: 2px 7px;" title="Dokumen Periode 3x Setahun (Baseline Tahfidz)">
                                    <i class="fa-solid fa-clock"></i> 3x Setahun: <strong><?= (int)$pStats['lainnya']['uploaded'] ?></strong>/<?= (int)$pStats['lainnya']['expected'] ?>
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Riwayat Pengumpulan Dokumen Bulanan (12 Bulan) -->
                    <?php if (($k['monthly_items_count'] ?? 0) > 0): ?>
                        <div class="monthly-strip-wrapper" style="margin-top: 12px; padding-top: 10px; border-top: 1px dashed #e2e8f0;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <span style="font-size: 11px; font-weight: 700; color: #475569; display: flex; align-items: center; gap: 4px;">
                                    <i class="fa-solid fa-calendar-days" style="color: #3b82f6;"></i> Dokumen Bulanan KS (12 Bulan):
                                </span>
                                <?php if (($k['monthly_past_missing'] ?? 0) > 0): ?>
                                    <span class="badge" style="background: #fef2f2; color: #dc2626; border: 1px solid #fca5a5; font-size: 10px; padding: 1px 6px;">
                                        <i class="fa-solid fa-triangle-exclamation"></i> <?= $k['monthly_past_missing'] ?> belum kumpul
                                    </span>
                                <?php else: ?>
                                    <span class="badge" style="background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; font-size: 10px; padding: 1px 6px;">
                                        <i class="fa-solid fa-check"></i> Tertib Bulanan
                                    </span>
                                <?php endif; ?>
                            </div>
                            <!-- Mini Grid 12 Bulan -->
                            <div style="display: grid; grid-template-columns: repeat(12, 1fr); gap: 2px; text-align: center;">
                                <?php 
                                $mSummary = $k['months_summary'] ?? [];
                                for ($m = 1; $m <= 12; $m++):
                                    $st = $mSummary[$m]['status'] ?? 'upcoming';
                                    $mName = $mSummary[$m]['month_name'] ?? $m;
                                    $mTitle = "Bulan {$mName}: " . ($mSummary[$m]['uploaded_count'] ?? 0) . "/" . ($mSummary[$m]['expected_count'] ?? 0) . " berkas";
                                    
                                    $bg = '#f8fafc';
                                    $color = '#94a3b8';
                                    $border = '1px solid #e2e8f0';
                                    
                                    if ($st === 'complete') {
                                        $bg = '#dcfce7'; $color = '#15803d'; $border = '1px solid #86efac';
                                    } elseif ($st === 'pending') {
                                        $bg = '#fef3c7'; $color = '#b45309'; $border = '1px solid #fcd34d';
                                    } elseif ($st === 'partial') {
                                        $bg = '#e0f2fe'; $color = '#0369a1'; $border = '1px solid #7dd3fc';
                                    } elseif ($st === 'missing_past') {
                                        $bg = '#fee2e2'; $color = '#b91c1c'; $border = '1px solid #fca5a5';
                                    } elseif ($st === 'missing_current') {
                                        $bg = '#fffbeb'; $color = '#d97706'; $border = '2px solid #f59e0b';
                                    }
                                ?>
                                    <div style="font-size: 9px; font-weight: 700; padding: 3px 0; border-radius: 4px; background: <?= $bg ?>; color: <?= $color ?>; border: <?= $border ?>;" title="<?= SecurityHelper::escape($mTitle) ?>">
                                        <?= $mName ?>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Card Footer -->
                <div class="teacher-card-footer">
                    <button type="button" class="btn btn-primary btn-block" onclick="openKsDetail(<?= $index ?>)" style="font-weight: 600;">
                        <i class="fa-solid fa-folder-open"></i> Periksa Dokumen KS (18 Item)
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ============================================================== -->
<!-- MODAL DETAIL KEPALA SEKOLAH (INSPEKSI BERKAS DIUNGGAH & KURANG) -->
<!-- ============================================================== -->
<div class="modal-overlay" id="ksDetailModal">
    <div class="modal-container" style="max-width: 960px; width: 95%; max-height: 90vh; display: flex; flex-direction: column;">
        <!-- Modal Top Bar -->
        <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
            <div style="display: flex; gap: 14px; align-items: center; min-width: 0;">
                <div id="modalAvatar" class="teacher-avatar" style="width: 44px; height: 44px; font-size: 16px; flex-shrink: 0; background: linear-gradient(135deg, #1e3a8a, #0284c7);">
                    KS
                </div>
                <div style="min-width: 0;">
                    <h2 id="modalKsName" style="font-size: 18px; font-weight: 700; color: var(--text-main); margin-bottom: 2px;"></h2>
                    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                        <span id="modalKsUnit" class="badge badge-info" style="font-size: 11px;"></span>
                        <span id="modalKsNipy" class="badge badge-secondary" style="font-size: 11px;"></span>
                        <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 11px;">18 Arsip Kepala Sekolah</span>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-outline btn-sm" onclick="closeKsDetailModal()" style="font-size: 18px; width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;">&times;</button>
        </div>

        <!-- Summary Strip Modal -->
        <div style="background: #ffffff; padding: 12px 20px; border-bottom: 1px solid var(--border-color); display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px;">
            <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                <span class="chip chip-success" id="modalStatApproved"><i class="fa-solid fa-check"></i> Disetujui: 0</span>
                <span class="chip chip-warning" id="modalStatPending"><i class="fa-solid fa-hourglass-half"></i> Menunggu: 0</span>
                <span class="chip chip-danger" id="modalStatMissing"><i class="fa-solid fa-xmark"></i> Kurang: 0</span>
                <span class="badge badge-secondary" id="modalStatPercent" style="padding: 4px 8px; font-weight: 700;">0% Lengkap</span>
            </div>
            
            <!-- Quick Batch Approve All Pending -->
            <form method="POST" action="<?= $baseUrl ?>/administrasi/batch-approve" id="batchApproveForm" style="margin: 0; display: inline;">
                <?= CSRF::inputField() ?>
                <input type="hidden" name="user_id" id="batch_user_id" value="">
                <input type="hidden" name="unit_id" id="batch_unit_id" value="">
                <input type="hidden" name="batch_notes" value="Disetujui menyeluruh oleh Deputi Direktur Pendidikan.">
                <button type="submit" id="btnBatchApprove" class="btn btn-primary btn-sm" onclick="return confirm('Apakah Anda yakin menyetujui seluruh dokumen Kepala Sekolah yang sedang menunggu review?')" style="display: none; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-check-double"></i> Setujui Semua Menunggu Review (<span id="batchCountNum">0</span>)
                </button>
            </form>
        </div>

        <!-- Navigation Tabs Modal -->
        <div style="background: #f8fafc; border-bottom: 1px solid var(--border-color); padding: 0 20px; display: flex; gap: 12px;">
            <button type="button" class="tab-btn active" id="tabBtnUploaded" onclick="switchModalTab('uploaded')">
                <i class="fa-solid fa-file-circle-check"></i> Berkas Diunggah (<span id="tabCountUploaded">0</span>)
            </button>
            <button type="button" class="tab-btn" id="tabBtnMissing" onclick="switchModalTab('missing')">
                <i class="fa-solid fa-file-circle-xmark"></i> Item Belum Diunggah (<span id="tabCountMissing">0</span>)
            </button>
            <button type="button" class="tab-btn" id="tabBtnMonthly" onclick="switchModalTab('monthly')">
                <i class="fa-solid fa-calendar-days"></i> Matriks 12 Bulan
            </button>
        </div>

        <!-- Modal Scrollable Content -->
        <div class="modal-body" style="padding: 20px; overflow-y: auto; flex: 1;">
            
            <!-- TAB 1: BERKAS DIUNGGAH -->
            <div id="tabContentUploaded">
                <div id="uploadedEmptyNotice" style="display: none; text-align: center; padding: 40px; color: var(--text-muted);">
                    <i class="fa-solid fa-folder-open" style="font-size: 36px; margin-bottom: 8px; opacity: 0.5;"></i>
                    <p style="margin: 0;">Belum ada dokumen administrasi yang diunggah oleh Kepala Sekolah ini.</p>
                </div>
                <div id="uploadedListContainer" style="display: flex; flex-direction: column; gap: 10px;">
                    <!-- Items injected by JS -->
                </div>
            </div>

            <!-- TAB 2: ITEM BELUM DIUNGGAH -->
            <div id="tabContentMissing" style="display: none;">
                <div id="missingEmptyNotice" style="display: none; text-align: center; padding: 40px; color: #16a34a;">
                    <i class="fa-solid fa-circle-check" style="font-size: 40px; margin-bottom: 8px;"></i>
                    <h4 style="margin: 0 0 4px 0; font-weight: 700;">Luar Biasa, Seluruh Dokumen Lengkap!</h4>
                    <p style="margin: 0; font-size: 13px; color: var(--text-muted);">Seluruh 18 item administrasi Kepala Sekolah telah berhasil diunggah.</p>
                </div>
                <div id="missingListContainer" style="display: flex; flex-direction: column; gap: 10px;">
                    <!-- Items injected by JS -->
                </div>
            </div>

            <!-- TAB 3: MATRIKS RIWAYAT 12 BULAN -->
            <div id="tabContentMonthly" style="display: none;">
                <div class="card" style="margin-bottom: 16px; border: 1px solid var(--border-color); padding: 12px 16px; background: #f8fafc;">
                    <div style="font-size: 12px; color: var(--text-muted); line-height: 1.5;">
                        <strong style="color: var(--text-main);">Matriks Verifikasi Laporan Bulanan Kepala Sekolah:</strong><br>
                        Memeriksa kedisiplinan pelaporan berkas periode bulanan (Laporan Bulanan Unit &amp; Closing Laporan Keuangan CA) untuk masing-masing bulan (Januari - Desember).
                    </div>
                </div>

                <div class="table-responsive" style="border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden;">
                    <table class="table" style="margin-bottom: 0; font-size: 12px;">
                        <thead style="background: #f1f5f9;">
                            <tr>
                                <th style="width: 250px; text-align: left;">Nama Dokumen Administrasi</th>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <th style="width: 48px; text-align: center; padding: 6px 2px;">
                                        <?= ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'][$m-1] ?>
                                    </th>
                                <?php endfor; ?>
                            </tr>
                        </thead>
                        <tbody id="modalMonthlyMatrixBody">
                            <!-- Injected by JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Box Peringatan Tunggakan Bulan Lampau -->
                <div id="modalPastMissingDetailBox" style="margin-top: 16px;"></div>
            </div>

        </div>

        <!-- Modal Footer -->
        <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid var(--border-color); padding: 12px 20px; display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 12px; color: var(--text-muted);">
                <i class="fa-solid fa-shield-halved" style="color: #10b981; margin-right: 4px;"></i> Terverifikasi sebagai Deputi Direktur Pendidikan
            </div>
            <button type="button" class="btn btn-outline" onclick="closeKsDetailModal()">Tutup</button>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL REVIEW SINGLE BERKAS KS                                  -->
<!-- ============================================================== -->
<div class="modal-overlay" id="reviewModal">
    <div class="modal-container" style="max-width: 500px;">
        <form method="POST" action="<?= $baseUrl ?>/administrasi/approve">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="id" id="review_upload_id">
            
            <div class="modal-header">
                <h3 class="card-title">Review Dokumen Kepala Sekolah</h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeReviewModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div style="margin-bottom: 16px; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Dokumen:</div>
                    <strong id="review_doc_title" style="color: var(--primary); display: block; margin-top: 4px; font-size: 14px;"></strong>
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Keputusan Review</label>
                    <select name="status" id="review_status_select" class="form-control" required style="height: 40px;">
                        <option value="Disetujui">Disetujui (Dokumen Lengkap &amp; Sesuai)</option>
                        <option value="Revisi">Perlu Revisi (Kembalikan ke Kepala Sekolah)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Catatan / Umpan Balik Deputi</label>
                    <textarea name="review_notes" id="review_notes" class="form-control" rows="3" placeholder="Tuliskan catatan verifikasi atau arahan perbaikan jika perlu revisi..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeReviewModal()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check-double"></i> Simpan Review</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- CSS STYLES KHUSUS CARD UI & MODAL                              -->
<!-- ============================================================== -->
<style>
/* Grid Kartu */
.teacher-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
    gap: 20px;
}
@media (max-width: 640px) {
    .teacher-grid {
        grid-template-columns: 1fr;
    }
}

/* Card Styling */
.teacher-card {
    background: #ffffff;
    border: 1px solid var(--border-color);
    border-radius: 12px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.04);
    display: flex;
    flex-direction: column;
    transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
    overflow: hidden;
}
.teacher-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
    border-color: #cbd5e1;
}

.teacher-card-header {
    padding: 16px;
    border-bottom: 1px solid #f1f5f9;
    background: #ffffff;
}

.teacher-card-name {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-main);
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.teacher-card-meta {
    display: flex;
    gap: 6px;
    align-items: center;
    margin-top: 4px;
    flex-wrap: wrap;
}

.nipy-pill {
    font-size: 11px;
    color: var(--text-muted);
    font-family: monospace;
    background: #f1f5f9;
    padding: 2px 6px;
    border-radius: 4px;
}

/* Avatar Inisial */
.teacher-avatar {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-weight: 700;
    font-size: 14px;
    flex-shrink: 0;
    text-shadow: 0 1px 2px rgba(0,0,0,0.15);
}

/* Card Body */
.teacher-card-body {
    padding: 16px;
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #ffffff;
}

/* Dual Metric Box: Sudah Upload vs Belum Upload */
.metrics-dual-box {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
.metric-box {
    padding: 12px;
    border-radius: 8px;
    text-align: center;
    border: 1px solid transparent;
}
.box-uploaded {
    background: #f0fdf4;
    border-color: #dcfce7;
}
.box-missing {
    background: #fef2f2;
    border-color: #fee2e2;
}

.metric-num {
    font-size: 22px;
    font-weight: 800;
    line-height: 1.1;
}
.text-success { color: #16a34a; }
.text-danger { color: #dc2626; }

.metric-title {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-main);
    margin-top: 4px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.metric-sub {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 2px;
}

/* Progress Track */
.progress-track {
    width: 100%;
    height: 7px;
    background: #e2e8f0;
    border-radius: 9999px;
    overflow: hidden;
}
.progress-bar-fill {
    height: 100%;
    border-radius: 9999px;
    transition: width 0.3s ease;
}
.progress-full { background: #16a34a; }
.progress-half { background: #3b82f6; }
.progress-low { background: #f59e0b; }
.progress-zero { background: #cbd5e1; }

/* Status Chips */
.status-chips-row {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-top: 14px;
}
.chip {
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.chip-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.chip-warning { background: #fefce8; color: #854d0e; border: 1px solid #fef08a; }
.chip-danger { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

/* Card Footer */
.teacher-card-footer {
    padding: 12px 16px;
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;
}

/* Filter Pills Toolbar */
.filter-btn {
    border: 1px solid var(--border-color);
    background: #ffffff;
    color: var(--text-muted);
    padding: 6px 14px;
    border-radius: 9999px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.filter-btn:hover {
    background: #f8fafc;
    color: var(--text-main);
    border-color: #cbd5e1;
}
.filter-btn.active {
    background: var(--primary);
    color: #ffffff;
    border-color: var(--primary);
}
.filter-btn-pending {
    background: #fffbeb;
    color: #b45309;
    border-color: #fde68a;
}
.filter-btn-pending.active {
    background: #b45309;
    color: #ffffff;
    border-color: #b45309;
}

/* Navigation Tabs Modal */
.tab-btn {
    background: transparent;
    border: none;
    border-bottom: 2px solid transparent;
    padding: 12px 16px;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-muted);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}
.tab-btn:hover {
    color: var(--text-main);
}
.tab-btn.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
    background: #ffffff;
}

/* Bell Shake Animation */
@keyframes bellShake {
    0%, 100% { transform: rotate(0); }
    10%, 30% { transform: rotate(14deg); }
    20%, 40% { transform: rotate(-14deg); }
    50% { transform: rotate(0); }
}
</style>

<!-- ============================================================== -->
<!-- JAVASCRIPT: FILTER, SEARCH, SORT & MODAL INTERACTIONS          -->
<!-- ============================================================== -->
<script>
const ksCards = <?= json_encode($ksCards ?? []) ?>;
const baseUrl = '<?= $baseUrl ?>';
let currentFilter = 'all';
let activeKs = null;

// Filter Pills Handler
function filterCards(filter, btn) {
    currentFilter = filter;
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    applyFilters();
}

// Search Input Handler
function handleSearch() {
    applyFilters();
}

// Sort Dropdown Handler
function handleSort() {
    const container = document.getElementById('ksCardsContainer');
    const cards = Array.from(container.querySelectorAll('.ks-card'));
    const sortVal = document.getElementById('ksSortSelect').value;

    cards.sort((a, b) => {
        const idxA = parseInt(a.getAttribute('data-index'), 10);
        const idxB = parseInt(b.getAttribute('data-index'), 10);
        const dataA = ksCards[idxA];
        const dataB = ksCards[idxB];

        if (sortVal === 'pending_first') {
            if (dataB.pending_count !== dataA.pending_count) {
                return dataB.pending_count - dataA.pending_count;
            }
            return (dataA.unit_name || '').localeCompare(dataB.unit_name || '');
        } else if (sortVal === 'name_asc') {
            return (dataA.unit_name || '').localeCompare(dataB.unit_name || '');
        } else if (sortVal === 'percent_desc') {
            return dataB.percent - dataA.percent;
        } else if (sortVal === 'missing_desc') {
            return dataB.missing_count - dataA.missing_count;
        }
        return 0;
    });

    cards.forEach(card => container.appendChild(card));
}

// Combined Filter Application
function applyFilters() {
    const query = (document.getElementById('ksSearchInput').value || '').toLowerCase().trim();
    const cards = document.querySelectorAll('.ks-card');

    cards.forEach(card => {
        const name = card.getAttribute('data-name') || '';
        const pending = parseInt(card.getAttribute('data-pending'), 10);
        const isComplete = card.getAttribute('data-complete') === '1';

        // 1. Check Search Query
        let matchSearch = (!query || name.includes(query));

        // 2. Check Pill Filter
        let matchPill = true;
        if (currentFilter === 'pending') {
            matchPill = (pending > 0);
        } else if (currentFilter === 'complete') {
            matchPill = isComplete;
        } else if (currentFilter === 'incomplete') {
            matchPill = !isComplete;
        }

        if (matchSearch && matchPill) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

// Buka Modal Detail Kepala Sekolah
function openKsDetail(index) {
    const k = ksCards[index];
    if (!k) return;
    activeKs = k;

    // 1. Header Information
    document.getElementById('modalKsName').innerText = k.name || 'Kepala Sekolah';
    document.getElementById('modalKsUnit').innerText = k.unit_name || '-';
    document.getElementById('modalKsNipy').innerText = 'NIPY: ' + (k.nipy || '-');

    // Avatar Inisial
    const words = (k.name || 'KS').trim().split(' ');
    let initials = '';
    words.slice(0, 2).forEach(w => { if (w.length) initials += w[0].toUpperCase(); });
    document.getElementById('modalAvatar').innerText = initials || 'KS';

    // 2. Summary Bar
    document.getElementById('modalStatApproved').innerHTML = '<i class="fa-solid fa-check"></i> Disetujui: <strong>' + k.approved_count + '</strong>';
    document.getElementById('modalStatPending').innerHTML = '<i class="fa-solid fa-hourglass-half"></i> Menunggu: <strong>' + k.pending_count + '</strong>';
    document.getElementById('modalStatMissing').innerHTML = '<i class="fa-solid fa-xmark"></i> Kurang: <strong>' + k.missing_count + '</strong>';
    document.getElementById('modalStatPercent').innerText = k.percent + '% Lengkap';

    // Batch Approve Button
    const btnBatch = document.getElementById('btnBatchApprove');
    if (k.pending_count > 0) {
        document.getElementById('batch_user_id').value = k.user_id || '';
        document.getElementById('batch_unit_id').value = k.unit_id || '';
        document.getElementById('batchCountNum').innerText = k.pending_count;
        btnBatch.style.display = 'inline-flex';
    } else {
        btnBatch.style.display = 'none';
    }

    // 3. Tab Badges Count
    document.getElementById('tabCountUploaded').innerText = (k.uploads || []).length;
    document.getElementById('tabCountMissing').innerText = (k.missing_items || []).length;

    // 4. Render Tab 1: Berkas Diunggah
    renderUploadedTab(k.uploads || []);

    // 5. Render Tab 2: Item Belum Diunggah
    renderMissingTab(k.missing_items || []);

    // 6. Render Tab 3: Matriks 12 Bulan
    renderMonthlyMatrix(k.monthly_matrix || [], k.current_month_num || <?= (int)date('n') ?>);

    // Default ke Tab 1 (Uploaded)
    switchModalTab('uploaded');

    // Buka Modal
    document.getElementById('ksDetailModal').classList.add('active');
}

function closeKsDetailModal() {
    document.getElementById('ksDetailModal').classList.remove('active');
}

// Switch Tabs Modal
function switchModalTab(tabKey) {
    document.getElementById('tabBtnUploaded').classList.remove('active');
    document.getElementById('tabBtnMissing').classList.remove('active');
    document.getElementById('tabBtnMonthly').classList.remove('active');

    document.getElementById('tabContentUploaded').style.display = 'none';
    document.getElementById('tabContentMissing').style.display = 'none';
    document.getElementById('tabContentMonthly').style.display = 'none';

    if (tabKey === 'uploaded') {
        document.getElementById('tabBtnUploaded').classList.add('active');
        document.getElementById('tabContentUploaded').style.display = 'block';
    } else if (tabKey === 'missing') {
        document.getElementById('tabBtnMissing').classList.add('active');
        document.getElementById('tabContentMissing').style.display = 'block';
    } else if (tabKey === 'monthly') {
        document.getElementById('tabBtnMonthly').classList.add('active');
        document.getElementById('tabContentMonthly').style.display = 'block';
    }
}

// Render Tab 1: Berkas Diunggah
function renderUploadedTab(uploads) {
    const container = document.getElementById('uploadedListContainer');
    const emptyNotice = document.getElementById('uploadedEmptyNotice');
    container.innerHTML = '';

    if (uploads.length === 0) {
        emptyNotice.style.display = 'block';
        return;
    }
    emptyNotice.style.display = 'none';

    uploads.forEach(up => {
        let statusBadge = '';
        if (up.status === 'Disetujui') {
            statusBadge = '<span class="badge badge-success"><i class="fa-solid fa-check"></i> Disetujui</span>';
        } else if (up.status === 'Revisi') {
            statusBadge = '<span class="badge badge-danger"><i class="fa-solid fa-triangle-exclamation"></i> Perlu Revisi</span>';
        } else {
            statusBadge = '<span class="badge badge-warning"><i class="fa-solid fa-hourglass-half"></i> Menunggu Review</span>';
        }

        let onTimeBadge = up.is_on_time == 1 
            ? '<span class="badge badge-success" style="font-size: 10px;"><i class="fa-solid fa-clock-check"></i> Tepat Waktu</span>'
            : '<span class="badge badge-danger" style="font-size: 10px;"><i class="fa-solid fa-clock-rotate-left"></i> Terlambat</span>';

        const fileSizeKb = up.file_size ? Math.round(up.file_size / 1024) + ' KB' : '';

        const itemDiv = document.createElement('div');
        itemDiv.className = 'card';
        itemDiv.style.cssText = 'border: 1px solid var(--border-color); padding: 14px 16px; margin: 0; display: flex; flex-direction: column; gap: 8px;';
        
        itemDiv.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap;">
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 2px;">
                        <span class="badge badge-secondary" style="font-family: monospace; font-size: 11px;">${escapeHtml(up.jenis_code || 'KS')}</span>
                        <strong style="color: var(--text-main); font-size: 13.5px;">${escapeHtml(up.jenis_title || 'Dokumen')}</strong>
                        ${up.sub_title ? '<span class="badge" style="background: #f1f5f9; color: #475569; font-size: 11px;">' + escapeHtml(up.sub_title) + '</span>' : ''}
                    </div>
                    <div style="font-size: 11.5px; color: var(--text-muted); display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-top: 4px;">
                        <span><i class="fa-solid fa-calendar-check" style="margin-right: 4px;"></i> Diunggah: <strong>${up.uploaded_at || '-'}</strong></span>
                        <span><i class="fa-solid fa-repeat" style="margin-right: 4px;"></i> Periode: <strong>${escapeHtml(up.frequency || '1x setahun')}</strong></span>
                        ${up.month_num ? '<span><i class="fa-solid fa-calendar-days" style="margin-right: 4px;"></i> Bulan ke: <strong>' + up.month_num + '</strong></span>' : ''}
                        <span>${onTimeBadge}</span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    ${statusBadge}
                    <button type="button" class="btn btn-primary btn-sm" onclick='openReviewModalFromDetail(${JSON.stringify(up).replace(/'/g, "&apos;")})' style="display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-stamp"></i> Review
                    </button>
                </div>
            </div>

            <!-- Download Link & Catatan -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px; display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 8px; min-width: 0;">
                    <i class="fa-solid fa-file-pdf" style="color: #ef4444; font-size: 18px;"></i>
                    <div style="min-width: 0;">
                        <a href="${baseUrl}/administrasi/download/${up.id}" target="_blank" style="font-weight: 600; color: #0284c7; text-decoration: underline; font-size: 12.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;" title="Unduh Berkas">
                            <i class="fa-solid fa-download"></i> ${escapeHtml(up.original_filename || 'Berkas Dokumen')}
                        </a>
                        <small style="color: var(--text-muted); font-size: 11px;">Versi ke-${up.version_num || 1} &bull; ${fileSizeKb}</small>
                    </div>
                </div>
                ${up.review_notes ? '<div style="font-size: 12px; color: #dc2626; background: #fff1f2; padding: 4px 8px; border-radius: 4px; border: 1px solid #fecdd3;"><i class="fa-solid fa-comment-dots"></i> <strong>Catatan:</strong> ' + escapeHtml(up.review_notes) + '</div>' : ''}
            </div>
        `;

        container.appendChild(itemDiv);
    });
}

// Render Tab 2: Item Belum Diunggah
function renderMissingTab(missingItems) {
    const container = document.getElementById('missingListContainer');
    const emptyNotice = document.getElementById('missingEmptyNotice');
    container.innerHTML = '';

    if (missingItems.length === 0) {
        emptyNotice.style.display = 'block';
        return;
    }
    emptyNotice.style.display = 'none';

    missingItems.forEach((mItem, idx) => {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'card';
        itemDiv.style.cssText = 'border: 1px dashed #fca5a5; background: #fff5f5; padding: 12px 16px; margin: 0; display: flex; justify-content: space-between; align-items: center; gap: 10px;';

        itemDiv.innerHTML = `
            <div style="display: flex; gap: 12px; align-items: center;">
                <span style="display: flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; background: #fee2e2; color: #dc2626; font-weight: 700; font-size: 12px; flex-shrink: 0;">
                    ${idx + 1}
                </span>
                <div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="badge badge-secondary" style="font-family: monospace; font-size: 10px;">${escapeHtml(mItem.code || 'KS')}</span>
                        <strong style="color: #991b1b; font-size: 13px;">${escapeHtml(mItem.title)}</strong>
                    </div>
                    <div style="font-size: 11px; color: #7f1d1d; margin-top: 2px;">
                        Frekuensi Pengumpulan: <strong>${escapeHtml(mItem.frequency || '1x setahun')}</strong>
                    </div>
                </div>
            </div>
            <span class="badge badge-danger" style="font-size: 11px; padding: 4px 8px;">
                <i class="fa-solid fa-clock-rotate-left"></i> Belum Masuk
            </span>
        `;

        container.appendChild(itemDiv);
    });
}

// Render Tab 3: Matriks 12 Bulan
function renderMonthlyMatrix(matrixRows, currentMonthNum) {
    const tbody = document.getElementById('modalMonthlyMatrixBody');
    const pastMissingBox = document.getElementById('modalPastMissingDetailBox');
    tbody.innerHTML = '';
    pastMissingBox.innerHTML = '';

    if (!matrixRows || matrixRows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="13" style="text-align: center; padding: 24px; color: var(--text-muted);">Tidak ada dokumen khusus periode bulanan untuk Kepala Sekolah.</td></tr>';
        return;
    }

    let uncollectedPast = [];

    matrixRows.forEach(row => {
        const tr = document.createElement('tr');
        let cellsHtml = `
            <td style="text-align: left; vertical-align: middle; padding: 8px 12px;">
                <div style="font-weight: 600; color: var(--text-main); font-size: 12px;">${escapeHtml(row.title)}</div>
                <div style="font-size: 10px; color: var(--text-muted); margin-top: 2px;">
                    ${row.code ? '<span class="badge badge-secondary" style="font-size: 9px; margin-right: 4px;">' + escapeHtml(row.code) + '</span>' : ''}
                    <span>${escapeHtml(row.due_info || 'Setiap Bulan')}</span>
                </div>
            </td>
        `;

        for (let m = 1; m <= 12; m++) {
            const mData = row.months[m] || {};
            const state = mData.state || 'upcoming';
            const up = mData.upload;
            let cellContent = '-';

            if (state === 'approved' && up) {
                cellContent = `
                    <a href="${baseUrl}/administrasi/download/${up.id}" target="_blank" class="btn btn-xs" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; padding: 2px 4px; font-size: 9.5px; font-weight: 700; border-radius: 4px; display: inline-flex; align-items: center; gap: 2px; text-decoration: none;" title="Disetujui: ${escapeHtml(up.original_filename)} (v${up.version_num})">
                        <i class="fa-solid fa-check"></i> v${up.version_num}
                    </a>
                `;
            } else if (state === 'pending' && up) {
                cellContent = `
                    <button type="button" class="btn btn-xs" style="background: #fef3c7; color: #b45309; border: 1px solid #fcd34d; padding: 2px 4px; font-size: 9.5px; font-weight: 700; border-radius: 4px; display: inline-flex; align-items: center; gap: 2px;" onclick='openReviewModalFromDetail(${JSON.stringify(up).replace(/'/g, "&apos;")})' title="Menunggu Review: ${escapeHtml(up.original_filename)}">
                        <i class="fa-solid fa-hourglass-half"></i> Rev
                    </button>
                `;
            } else if (state === 'revision' && up) {
                cellContent = `
                    <button type="button" class="btn btn-xs" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; padding: 2px 4px; font-size: 9.5px; font-weight: 700; border-radius: 4px; display: inline-flex; align-items: center; gap: 2px;" onclick='openReviewModalFromDetail(${JSON.stringify(up).replace(/'/g, "&apos;")})' title="Perlu Revisi">
                        <i class="fa-solid fa-rotate-left"></i> Fix
                    </button>
                `;
            } else if (state === 'missing_past') {
                uncollectedPast.push({
                    title: row.title,
                    month_name: mData.month_name,
                    month_num: m
                });
                cellContent = `
                    <span class="badge" style="background: #fef2f2; color: #dc2626; border: 1px dashed #ef4444; font-size: 9px; padding: 2px 4px;" title="Belum Kumpul (Bulan Lampau: ${mData.month_name})">
                        <i class="fa-solid fa-xmark"></i> Ksg
                    </span>
                `;
            } else if (state === 'missing_current') {
                cellContent = `
                    <span class="badge" style="background: #fffbeb; color: #d97706; border: 1px solid #f59e0b; font-size: 9px; padding: 2px 4px;" title="Belum Diunggah (Bulan Berjalan)">
                        Ini
                    </span>
                `;
            } else {
                cellContent = `<span style="color: #cbd5e1; font-size: 11px;">-</span>`;
            }

            const isCurrCol = (m === currentMonthNum);
            const colBg = isCurrCol ? 'background: #f8fafc;' : '';
            cellsHtml += `<td style="text-align: center; vertical-align: middle; padding: 6px 3px; ${colBg}">${cellContent}</td>`;
        }

        tr.innerHTML = cellsHtml;
        tbody.appendChild(tr);
    });

    if (uncollectedPast.length > 0) {
        let itemsHtml = uncollectedPast.map(u => `
            <div style="background: #ffffff; border: 1px solid #fecaca; border-radius: 6px; padding: 8px 12px; display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 12px; font-weight: 600; color: #991b1b;">
                    <i class="fa-solid fa-file-circle-xmark" style="color: #dc2626; margin-right: 6px;"></i> ${escapeHtml(u.title)}
                </div>
                <span class="badge badge-danger" style="font-size: 11px;">
                    <i class="fa-solid fa-calendar-xmark" style="margin-right: 4px;"></i> Bulan ${escapeHtml(u.month_name)}
                </span>
            </div>
        `).join('');

        pastMissingBox.innerHTML = `
            <div class="card" style="border: 1px solid #fca5a5; background: #fff5f5; border-radius: 8px; padding: 14px;">
                <h4 style="margin: 0 0 10px 0; font-size: 13px; font-weight: 700; color: #b91c1c; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-clipboard-list"></i> Rincian Laporan Bulanan Sebelumnya yang Belum Dikumpulkan (${uncollectedPast.length} item):
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 8px;">
                    ${itemsHtml}
                </div>
            </div>
        `;
    }
}

// Single Review Modal
function openReviewModalFromDetail(up) {
    document.getElementById('review_upload_id').value = up.id;
    document.getElementById('review_doc_title').innerText = (up.kepsek_name || (activeKs ? activeKs.name : '')) + ' - ' + (up.jenis_title || '');
    document.getElementById('review_status_select').value = up.status === 'Revisi' ? 'Revisi' : 'Disetujui';
    document.getElementById('review_notes').value = up.review_notes || '';
    document.getElementById('reviewModal').classList.add('active');
}
function closeReviewModal() {
    document.getElementById('reviewModal').classList.remove('active');
}

// Utility: Escape HTML to avoid XSS
function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// Keyboard ESC to close modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (document.getElementById('reviewModal').classList.contains('active')) {
            closeReviewModal();
        } else if (document.getElementById('ksDetailModal').classList.contains('active')) {
            closeKsDetailModal();
        }
    }
});
</script>
