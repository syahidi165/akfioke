<?php
// views/administrasi/review_guru.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$activeAcademicYearName = Auth::academicYearName() ?? 'Tahun Ajaran Aktif';
$unitName = $unit['name'] ?? 'Unit Sekolah';
?>
<div class="page-header" style="margin-bottom: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title" style="display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-clipboard-check" style="color: var(--primary);"></i>
                Approval Administrasi Guru, Walas & Staf TU
            </h1>
            <div class="page-subtitle">
                Panel Kepala Sekolah untuk memverifikasi dokumen perangkat pembelajaran guru, administrasi wali kelas, dan arsip TU per individu pegawai.
            </div>
        </div>
        <div style="display: flex; gap: 8px; align-items: center;">
            <span class="badge badge-info" style="font-size: 12px; padding: 6px 12px;">
                <i class="fa-solid fa-school" style="margin-right: 5px;"></i> <?= SecurityHelper::escape($unitName) ?>
            </span>
            <span class="badge badge-secondary" style="font-size: 12px; padding: 6px 12px;">
                <i class="fa-solid fa-calendar-alt" style="margin-right: 5px;"></i> <?= SecurityHelper::escape($activeAcademicYearName) ?>
            </span>
        </div>
    </div>
</div>

<!-- STATISTIK RINGKASAN ATAS -->
<div class="grid-cols-4" style="margin-bottom: 24px;">
    <div class="stat-card" style="box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
        <div class="stat-icon blue">
            <i class="fa-solid fa-chalkboard-user"></i>
        </div>
        <div>
            <div class="stat-value"><?= (int)($summary['total_teachers'] ?? 0) ?></div>
            <div class="stat-label">Total Guru & Staf</div>
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
            <i class="fa-solid fa-graduation-cap"></i>
        </div>
        <div>
            <div class="stat-value" style="color: #7e22ce;"><?= (int)($summary['total_karya'] ?? 0) ?></div>
            <div class="stat-label">Karya Tulis Ilmiah (PTK/BP)</div>
        </div>
    </div>
</div>

<!-- TOOLBAR: FILTER & SEARCH -->
<div class="card" style="margin-bottom: 24px; padding: 16px;">
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 14px;">
        <!-- Filter Tabs / Kategori -->
        <div class="filter-pills-wrapper" style="display: flex; flex-wrap: wrap; gap: 8px;">
            <button type="button" class="filter-btn active" data-filter="all" onclick="filterCards('all', this)">
                Semua (<?= count($teacherCards ?? []) ?>)
            </button>
            <?php if (($summary['teachers_with_pending'] ?? 0) > 0): ?>
                <button type="button" class="filter-btn filter-btn-pending" data-filter="pending" onclick="filterCards('pending', this)">
                    <i class="fa-solid fa-bell"></i> Perlu Review (<?= $summary['teachers_with_pending'] ?>)
                </button>
            <?php endif; ?>
            <button type="button" class="filter-btn" data-filter="mapel" onclick="filterCards('mapel', this)">
                Guru Mapel / Diniyah
            </button>
            <button type="button" class="filter-btn" data-filter="tahfidz" onclick="filterCards('tahfidz', this)">
                Guru Tahfidz
            </button>
            <button type="button" class="filter-btn" data-filter="walas" onclick="filterCards('walas', this)">
                Wali Kelas
            </button>
            <button type="button" class="filter-btn" data-filter="tu" onclick="filterCards('tu', this)">
                Tata Usaha
            </button>
            <button type="button" class="filter-btn" data-filter="incomplete" onclick="filterCards('incomplete', this)">
                Belum Lengkap (<?= $summary['teachers_incomplete'] ?? 0 ?>)
            </button>
        </div>

        <!-- Search Bar & Sort Dropdown -->
        <div style="display: flex; gap: 10px; align-items: center; flex: 1; max-width: 480px; justify-content: flex-end;">
            <div style="position: relative; width: 100%;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 13px;"></i>
                <input type="text" id="teacherSearchInput" class="form-control" placeholder="Cari nama guru atau NIPY..." oninput="handleSearch()" style="padding-left: 36px; height: 38px;">
            </div>
            <select id="teacherSortSelect" class="form-control" style="width: 170px; height: 38px;" onchange="handleSort()">
                <option value="pending_first">Prioritas: Perlu Review</option>
                <option value="name_asc">Nama (A - Z)</option>
                <option value="percent_desc">Paling Lengkap (%)</option>
                <option value="missing_desc">Paling Banyak Kurang</option>
            </select>
        </div>
    </div>
</div>

<!-- GRID KARTU GURU -->
<div id="teacherCardsContainer" class="teacher-grid">
    <?php if (empty($teacherCards)): ?>
        <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 48px;">
            <i class="fa-solid fa-users-slash" style="font-size: 48px; color: #cbd5e1; margin-bottom: 12px;"></i>
            <h3 style="color: var(--text-main); font-weight: 600;">Belum Ada Data Guru / Staf</h3>
            <p style="color: var(--text-muted); font-size: 13px;">Belum ada pegawai aktif yang terdaftar pada unit ini.</p>
        </div>
    <?php else: ?>
        <?php foreach ($teacherCards as $index => $t): ?>
            <div class="teacher-card" 
                 id="card-<?= $index ?>"
                 data-index="<?= $index ?>"
                 data-name="<?= strtolower(SecurityHelper::escape($t['name'])) ?>"
                 data-nipy="<?= strtolower(SecurityHelper::escape($t['nipy'])) ?>"
                 data-category="<?= $t['category'] ?>"
                 data-pending="<?= (int)$t['pending_count'] ?>"
                 data-percent="<?= (int)$t['percent'] ?>"
                 data-missing="<?= (int)$t['missing_count'] ?>">
                
                <!-- Card Header -->
                <div class="teacher-card-header">
                    <div style="display: flex; gap: 12px; align-items: center; min-width: 0;">
                        <!-- Avatar Inisial -->
                        <div class="teacher-avatar category-<?= $t['category'] ?>">
                            <?php
                            $words = explode(' ', trim($t['name']));
                            $initials = '';
                            foreach (array_slice($words, 0, 2) as $w) {
                                $initials .= strtoupper(mb_substr($w, 0, 1));
                            }
                            echo $initials ?: 'GR';
                            ?>
                        </div>
                        <div style="min-width: 0; flex: 1;">
                            <h3 class="teacher-card-name" title="<?= SecurityHelper::escape($t['name']) ?>">
                                <?= SecurityHelper::escape($t['name']) ?>
                            </h3>
                            <div class="teacher-card-meta">
                                <span class="nipy-pill"><i class="fa-solid fa-id-badge"></i> <?= SecurityHelper::escape($t['nipy']) ?></span>
                                <span class="role-pill category-badge-<?= $t['category'] ?>">
                                    <?= SecurityHelper::escape($t['role_display']) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Top Status Badge -->
                    <div style="margin-top: 10px; display: flex; justify-content: space-between; align-items: center;">
                        <?php if ($t['pending_count'] > 0): ?>
                            <span class="badge badge-warning" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-weight: 700;">
                                <i class="fa-solid fa-bell" style="animation: bellShake 2s infinite;"></i> <?= $t['pending_count'] ?> Menunggu Review
                            </span>
                        <?php elseif ($t['percent'] >= 100): ?>
                            <span class="badge badge-success" style="background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;">
                                <i class="fa-solid fa-circle-check"></i> Lengkap 100%
                            </span>
                        <?php elseif ($t['uploaded_count'] == 0): ?>
                            <span class="badge badge-danger" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;">
                                <i class="fa-solid fa-circle-xmark"></i> Belum Upload
                            </span>
                        <?php else: ?>
                            <span class="badge badge-info" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">
                                <i class="fa-solid fa-clock"></i> <?= $t['percent'] ?>% Terkumpul
                            </span>
                        <?php endif; ?>

                        <?php if ($t['karya_count'] > 0): ?>
                            <span class="badge" style="background: #f3e8ff; color: #7e22ce; border: 1px solid #e9d5ff; font-weight: 600;" title="Karya Tulis Ilmiah Diajukan">
                                <i class="fa-solid fa-graduation-cap"></i> <?= $t['karya_count'] ?> Karya Ilmiah
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Card Body: Dua Box Utama (Sudah Upload vs Belum Upload) -->
                <div class="teacher-card-body">
                    <div class="metrics-dual-box">
                        <!-- BOX SUDAH DIUNGGAH -->
                        <div class="metric-box box-uploaded">
                            <div class="metric-num text-success">
                                <?= $t['uploaded_count'] ?> <span style="font-size: 13px; font-weight: 500; color: #16a34a;">/ <?= $t['expected_count'] ?></span>
                            </div>
                            <div class="metric-title">
                                <i class="fa-solid fa-file-circle-check" style="color: #16a34a;"></i> Sudah Diunggah
                            </div>
                            <div class="metric-sub"><?= $t['total_files'] ?> berkas masuk</div>
                        </div>

                        <!-- BOX BELUM DIUNGGAH -->
                        <div class="metric-box box-missing">
                            <div class="metric-num text-danger">
                                <?= $t['missing_count'] ?>
                            </div>
                            <div class="metric-title">
                                <i class="fa-solid fa-file-circle-xmark" style="color: #ef4444;"></i> Belum Diunggah
                            </div>
                            <div class="metric-sub"><?= $t['missing_count'] == 0 ? 'Semua beres' : 'Item wajib kurang' ?></div>
                        </div>
                    </div>

                    <!-- Progress Bar Kelengkapan -->
                    <div style="margin-top: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 5px;">
                            <span style="color: var(--text-muted); font-weight: 500;">Kelengkapan Dokumen:</span>
                            <strong style="color: var(--text-main);"><?= $t['percent'] ?>%</strong>
                        </div>
                        <div class="progress-track">
                            <div class="progress-bar-fill progress-<?= $t['percent'] >= 100 ? 'full' : ($t['percent'] >= 50 ? 'half' : ($t['percent'] > 0 ? 'low' : 'zero')) ?>"
                                 style="width: <?= $t['percent'] ?>%;"></div>
                        </div>
                    </div>

                    <!-- Rincian Status Verifikasi -->
                    <div class="status-chips-row">
                        <span class="chip chip-success" title="Berkas Disetujui">
                            <i class="fa-solid fa-check"></i> Disetujui: <strong><?= $t['approved_count'] ?></strong>
                        </span>
                        <span class="chip chip-warning" title="Berkas Menunggu Review">
                            <i class="fa-solid fa-hourglass-half"></i> Menunggu: <strong><?= $t['pending_count'] ?></strong>
                        </span>
                        <?php if ($t['revision_count'] > 0): ?>
                            <span class="chip chip-danger" title="Berkas Perlu Revisi">
                                <i class="fa-solid fa-triangle-exclamation"></i> Revisi: <strong><?= $t['revision_count'] ?></strong>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Klasifikasi Periode Pengumpulan Dokumen -->
                    <div class="period-breakdown-row" style="margin-top: 10px; display: flex; flex-wrap: wrap; gap: 5px; font-size: 11px;">
                        <?php 
                        $pStats = $t['period_stats'] ?? [];
                        if (!empty($pStats)):
                        ?>
                            <?php if (($pStats['bulanan']['expected'] ?? 0) > 0 || ($pStats['bulanan']['uploaded'] ?? 0) > 0): ?>
                                <span class="badge" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 10.5px; padding: 2px 7px;" title="Dokumen Periode Bulanan">
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
                                <span class="badge" style="background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; font-size: 10.5px; padding: 2px 7px;" title="Dokumen Periode Lainnya">
                                    <i class="fa-solid fa-clock"></i> Lainnya: <strong><?= (int)$pStats['lainnya']['uploaded'] ?></strong>/<?= (int)$pStats['lainnya']['expected'] ?>
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Riwayat Pengumpulan Dokumen Bulanan (12 Bulan) -->
                    <?php if (($t['monthly_items_count'] ?? 0) > 0): ?>
                        <div class="monthly-strip-wrapper" style="margin-top: 12px; padding-top: 10px; border-top: 1px dashed #e2e8f0;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <span style="font-size: 11px; font-weight: 700; color: #475569; display: flex; align-items: center; gap: 4px;">
                                    <i class="fa-solid fa-calendar-days" style="color: #3b82f6;"></i> Riwayat Dokumen Bulanan:
                                </span>
                                <?php if (($t['monthly_past_missing'] ?? 0) > 0): ?>
                                    <span class="badge" style="background: #fef2f2; color: #dc2626; border: 1px solid #fca5a5; font-size: 10px; padding: 1px 6px;">
                                        <i class="fa-solid fa-triangle-exclamation"></i> <?= $t['monthly_past_missing'] ?> belum kumpul
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
                                $mSummary = $t['months_summary'] ?? [];
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

                <!-- Card Footer: Aksi -->
                <div class="teacher-card-footer">
                    <button type="button" class="btn btn-primary btn-block" onclick="openTeacherDetail(<?= $index ?>)">
                        <i class="fa-solid fa-folder-open"></i> Periksa Dokumen
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ============================================================== -->
<!-- MODAL DETAIL GURU (INSPEKSI BERKAS DIUNGGAH & BELUM DIUNGGAH)  -->
<!-- ============================================================== -->
<div class="modal-overlay" id="teacherDetailModal">
    <div class="modal-container" style="max-width: 940px; width: 95%; max-height: 90vh; display: flex; flex-direction: column;">
        <!-- Modal Top Bar -->
        <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
            <div style="display: flex; gap: 14px; align-items: center; min-width: 0;">
                <div id="modalAvatar" class="teacher-avatar" style="width: 44px; height: 44px; font-size: 16px; flex-shrink: 0;">
                    GR
                </div>
                <div style="min-width: 0;">
                    <h2 id="modalTeacherName" style="font-size: 18px; font-weight: 700; color: var(--text-main); margin-bottom: 2px;"></h2>
                    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                        <span id="modalTeacherNipy" class="badge badge-secondary" style="font-size: 11px;"></span>
                        <span id="modalTeacherRole" class="badge badge-info" style="font-size: 11px;"></span>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 10px; align-items: center;">
                <!-- Tombol Batch Approve (Tampil jika ada berkas menunggu) -->
                <form id="modalBatchApproveForm" method="POST" action="<?= $baseUrl ?>/administrasi/batch-approve" style="display: none;" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui semua berkas yang berstatus menunggu review untuk guru ini?');">
                    <?= CSRF::inputField() ?>
                    <input type="hidden" name="user_id" id="modalBatchUserId">
                    <button type="submit" class="btn btn-success btn-sm" id="modalBatchBtn">
                        <i class="fa-solid fa-check-double"></i> Setujui Semua yang Menunggu
                    </button>
                </form>

                <button type="button" class="btn btn-outline btn-sm" onclick="closeTeacherDetailModal()" style="font-size: 16px; width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;">
                    &times;
                </button>
            </div>
        </div>

        <!-- Sub-Banner: Status Summary -->
        <div style="padding: 12px 20px; background: #ffffff; border-bottom: 1px solid var(--border-color); display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px;">
            <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <span class="badge badge-success" style="font-size: 12px; padding: 4px 10px;">
                    <i class="fa-solid fa-circle-check" style="margin-right: 4px;"></i> Sudah Diunggah: <strong id="modalStatUploaded">0</strong>
                </span>
                <span class="badge badge-danger" style="font-size: 12px; padding: 4px 10px;">
                    <i class="fa-solid fa-circle-xmark" style="margin-right: 4px;"></i> Belum Diunggah: <strong id="modalStatMissing">0</strong>
                </span>
                <span class="badge badge-warning" style="font-size: 12px; padding: 4px 10px;">
                    <i class="fa-solid fa-hourglass-half" style="margin-right: 4px;"></i> Menunggu Review: <strong id="modalStatPending">0</strong>
                </span>
                <span class="badge badge-secondary" style="font-size: 12px; padding: 4px 10px;">
                    Disetujui: <strong id="modalStatApproved">0</strong>
                </span>
            </div>
            <div style="font-size: 13px; font-weight: 600; color: var(--text-main);">
                Kelengkapan: <span id="modalStatPercent" style="color: var(--primary);">0%</span>
            </div>
        </div>

        <!-- Modal Tabs Navigation -->
        <div class="modal-tabs-header">
            <button type="button" class="modal-tab-btn active" id="tabBtnUploaded" onclick="switchDetailTab('uploaded')">
                <i class="fa-solid fa-file-circle-check"></i> Berkas Diunggah (<span id="modalCountUploaded">0</span>)
            </button>
            <button type="button" class="modal-tab-btn" id="tabBtnMissing" onclick="switchDetailTab('missing')">
                <i class="fa-solid fa-file-circle-xmark"></i> Berkas Belum Diunggah (<span id="modalCountMissing">0</span>)
            </button>
            <button type="button" class="modal-tab-btn" id="tabBtnMonthly" onclick="switchDetailTab('monthly')">
                <i class="fa-solid fa-calendar-days"></i> Riwayat Bulanan (12 Bulan) <span id="modalMonthlyBadge" class="badge" style="font-size: 10px; margin-left: 4px; display: none;"></span>
            </button>
            <button type="button" class="modal-tab-btn" id="tabBtnKarya" onclick="switchDetailTab('karya')">
                <i class="fa-solid fa-graduation-cap"></i> Karya Ilmiah PTK / BP (<span id="modalCountKarya">0</span>)
            </button>
        </div>

        <!-- Modal Body Content (Scrollable) -->
        <div class="modal-body" style="padding: 20px; overflow-y: auto; flex: 1;">
            <!-- TAB 1: BERKAS DIUNGGAH -->
            <div id="detailTabUploaded" class="modal-tab-content active">
                <!-- Filter Periode Bar Tab 1 -->
                <div style="margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                    <div style="display: flex; gap: 6px; flex-wrap: wrap;" id="uploadedPeriodFilters">
                        <button type="button" class="btn btn-xs filter-pill active" onclick="filterModalTable('uploaded', 'all', this)">Semua Periode</button>
                        <button type="button" class="btn btn-xs filter-pill" onclick="filterModalTable('uploaded', 'bulanan', this)"><i class="fa-solid fa-calendar-days"></i> Bulanan</button>
                        <button type="button" class="btn btn-xs filter-pill" onclick="filterModalTable('uploaded', 'tahunan', this)"><i class="fa-solid fa-calendar"></i> 1x Setahun (Tahunan)</button>
                        <button type="button" class="btn btn-xs filter-pill" onclick="filterModalTable('uploaded', 'semester', this)"><i class="fa-solid fa-calendar-week"></i> 2x Setahun (Semester)</button>
                        <button type="button" class="btn btn-xs filter-pill" onclick="filterModalTable('uploaded', 'lainnya', this)"><i class="fa-solid fa-clock"></i> Lainnya</button>
                    </div>
                    <span id="uploadedFilterCount" style="font-size: 12px; color: var(--text-muted);"></span>
                </div>

                <div class="table-responsive">
                    <table class="table" style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">No</th>
                                <th>Jenis Administrasi</th>
                                <th style="width: 130px;">Periode</th>
                                <th>Berkas File</th>
                                <th style="width: 120px;">Tgl Unggah</th>
                                <th style="width: 100px;">Status</th>
                                <th style="width: 80px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="modalUploadedTableBody">
                            <!-- Injected by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: BERKAS BELUM DIUNGGAH -->
            <div id="detailTabMissing" class="modal-tab-content">
                <!-- Filter Periode Bar Tab 2 -->
                <div style="margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                    <div style="display: flex; gap: 6px; flex-wrap: wrap;" id="missingPeriodFilters">
                        <button type="button" class="btn btn-xs filter-pill active" onclick="filterModalTable('missing', 'all', this)">Semua Periode</button>
                        <button type="button" class="btn btn-xs filter-pill" onclick="filterModalTable('missing', 'bulanan', this)"><i class="fa-solid fa-calendar-days"></i> Bulanan</button>
                        <button type="button" class="btn btn-xs filter-pill" onclick="filterModalTable('missing', 'tahunan', this)"><i class="fa-solid fa-calendar"></i> 1x Setahun (Tahunan)</button>
                        <button type="button" class="btn btn-xs filter-pill" onclick="filterModalTable('missing', 'semester', this)"><i class="fa-solid fa-calendar-week"></i> 2x Setahun (Semester)</button>
                        <button type="button" class="btn btn-xs filter-pill" onclick="filterModalTable('missing', 'lainnya', this)"><i class="fa-solid fa-clock"></i> Lainnya</button>
                    </div>
                    <span id="missingFilterCount" style="font-size: 12px; color: var(--text-muted);"></span>
                </div>

                <div id="missingEmptyNotice" class="alert alert-success" style="display: none;">
                    <i class="fa-solid fa-circle-check" style="font-size: 18px;"></i>
                    <div>
                        <strong>Luar Biasa!</strong> Seluruh dokumen administrasi wajib telah diunggah lengkap oleh pegawai ini.
                    </div>
                </div>

                <div class="table-responsive" id="missingTableWrapper">
                    <table class="table" style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">No</th>
                                <th>Jenis Administrasi Wajib</th>
                                <th style="width: 140px;">Periode / Frekuensi</th>
                                <th style="width: 130px;">Kategori Peran</th>
                                <th style="width: 120px; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="modalMissingTableBody">
                            <!-- Injected by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: RIWAYAT BULANAN (12 BULAN: JAN - DES) -->
            <div id="detailTabMonthly" class="modal-tab-content">
                <!-- Status Banner Kepatuhan Bulanan -->
                <div id="monthlyAlertBanner" style="margin-bottom: 16px;"></div>

                <!-- Legend Keterangan Status Sel -->
                <div style="margin-bottom: 14px; padding: 10px 14px; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; display: flex; flex-wrap: wrap; gap: 12px; align-items: center; font-size: 11px;">
                    <span style="font-weight: 700; color: #475569;">Keterangan Sel:</span>
                    <span style="display: inline-flex; align-items: center; gap: 4px;">
                        <span style="display: inline-block; width: 12px; height: 12px; border-radius: 3px; background: #dcfce7; border: 1px solid #86efac;"></span>
                        Disetujui
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 4px;">
                        <span style="display: inline-block; width: 12px; height: 12px; border-radius: 3px; background: #fef3c7; border: 1px solid #fcd34d;"></span>
                        Menunggu Review
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 4px;">
                        <span style="display: inline-block; width: 12px; height: 12px; border-radius: 3px; background: #fee2e2; border: 1px solid #fca5a5;"></span>
                        Perlu Revisi
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 4px;">
                        <span style="display: inline-block; width: 12px; height: 12px; border-radius: 3px; background: #fef2f2; border: 1px dashed #ef4444;"></span>
                        <strong style="color: #dc2626;">Belum Kumpul (Bulan Lampau)</strong>
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 4px;">
                        <span style="display: inline-block; width: 12px; height: 12px; border-radius: 3px; background: #fffbeb; border: 2px solid #f59e0b;"></span>
                        Bulan Berjalan
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 4px;">
                        <span style="display: inline-block; width: 12px; height: 12px; border-radius: 3px; background: #f1f5f9; border: 1px solid #e2e8f0;"></span>
                        Bulan Mendatang
                    </span>
                </div>

                <!-- Matriks Tabel Riwayat Bulanan -->
                <div class="table-responsive" id="monthlyTableWrapper">
                    <table class="table" style="margin-bottom: 0; font-size: 11.5px;">
                        <thead>
                            <tr style="background: #f1f5f9;">
                                <th style="width: 30px; text-align: center;">No</th>
                                <th style="min-width: 170px;">Jenis Dokumen Bulanan</th>
                                <th style="text-align: center; width: 48px;">Jan</th>
                                <th style="text-align: center; width: 48px;">Feb</th>
                                <th style="text-align: center; width: 48px;">Mar</th>
                                <th style="text-align: center; width: 48px;">Apr</th>
                                <th style="text-align: center; width: 48px;">Mei</th>
                                <th style="text-align: center; width: 48px;">Jun</th>
                                <th style="text-align: center; width: 48px;">Jul</th>
                                <th style="text-align: center; width: 48px;">Agu</th>
                                <th style="text-align: center; width: 48px;">Sep</th>
                                <th style="text-align: center; width: 48px;">Okt</th>
                                <th style="text-align: center; width: 48px;">Nov</th>
                                <th style="text-align: center; width: 48px;">Des</th>
                            </tr>
                        </thead>
                        <tbody id="modalMonthlyTableBody">
                            <!-- Injected by JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Daftar Rincian Dokumen Bulan-Bulan Sebelumnya yang Belum Dikumpulkan -->
                <div id="monthlyPastMissingContainer" style="margin-top: 20px;"></div>
            </div>

            <!-- TAB 4: KARYA ILMIAH (PTK & BEST PRACTICE) -->
            <div id="detailTabKarya" class="modal-tab-content">
                <div class="table-responsive">
                    <table class="table" style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">No</th>
                                <th style="width: 100px;">Jenis</th>
                                <th>Judul Karya Tulis</th>
                                <th style="width: 110px; text-align: center;">Berkas</th>
                                <th style="width: 120px;">Status</th>
                                <th style="width: 90px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="modalKaryaTableBody">
                            <!-- Injected by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal-footer" style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end;">
            <button type="button" class="btn btn-secondary" onclick="closeTeacherDetailModal()">Tutup</button>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL REVIEW DOKUMEN SATUAN (APPROVE / REVISI)                 -->
<!-- ============================================================== -->
<div class="modal-overlay" id="reviewModal" style="z-index: 2100;">
    <div class="modal-container" style="max-width: 500px;">
        <form method="POST" action="<?= $baseUrl ?>/administrasi/approve">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="id" id="review_upload_id">
            
            <div class="modal-header">
                <h3 class="card-title"><i class="fa-solid fa-stamp" style="color: var(--primary); margin-right: 6px;"></i> Review Berkas Administrasi</h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeReviewModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div style="margin-bottom: 16px; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Dokumen Pegawai:</div>
                    <strong id="review_doc_title" style="color: var(--primary); display: block; margin-top: 4px; font-size: 14px;"></strong>
                </div>

                <div class="form-group">
                    <label class="form-label">Keputusan Review Kepala Sekolah</label>
                    <select name="status" id="review_status_select" class="form-control" required>
                        <option value="Disetujui">Disetujui (Memenuhi Standar)</option>
                        <option value="Revisi">Perlu Revisi (Perbaikan)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan / Arahan Kepala Sekolah</label>
                    <textarea name="review_notes" id="review_notes" class="form-control" rows="3" placeholder="Tuliskan arahan revisi atau apresiasi kepada guru..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeReviewModal()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Simpan Keputusan</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL REVIEW KARYA ILMIAH (PTK & BEST PRACTICE)               -->
<!-- ============================================================== -->
<div class="modal-overlay" id="karyaModal" style="z-index: 2100;">
    <div class="modal-container" style="max-width: 500px;">
        <form method="POST" action="<?= $baseUrl ?>/karya-ilmiah/approve">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="id" id="karya_id">
            
            <div class="modal-header">
                <h3 class="card-title"><i class="fa-solid fa-graduation-cap" style="color: #7e22ce; margin-right: 6px;"></i> Review Karya Tulis Ilmiah</h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeKaryaModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div style="margin-bottom: 16px; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Judul Karya Ilmiah:</div>
                    <strong id="karya_title_display" style="color: #7e22ce; display: block; margin-top: 4px; font-size: 14px;"></strong>
                </div>

                <div class="form-group">
                    <label class="form-label">Keputusan Review</label>
                    <select name="status" id="karya_status_select" class="form-control" required>
                        <option value="Disetujui">Disetujui (Otomatis Masuk AKFI 100%)</option>
                        <option value="Revisi">Perlu Revisi</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan Evaluasi / Rekomendasi</label>
                    <textarea name="review_notes" id="karya_review_notes" class="form-control" rows="3" placeholder="Tuliskan apresiasi inovasi atau arahan perbaikan..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeKaryaModal()">Batal</button>
                <button type="submit" class="btn btn-accent"><i class="fa-solid fa-check"></i> Simpan & Perbarui AKFI</button>
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

.role-pill {
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 4px;
}
.category-badge-mapel { background: #eff6ff; color: #1d4ed8; }
.category-badge-tahfidz { background: #f0fdf4; color: #15803d; }
.category-badge-walas { background: #fefce8; color: #a16207; }
.category-badge-tu { background: #f5f3ff; color: #6d28d9; }
.category-badge-lainnya { background: #f1f5f9; color: #475569; }

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
.category-mapel { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
.category-tahfidz { background: linear-gradient(135deg, #10b981, #047857); }
.category-walas { background: linear-gradient(135deg, #f59e0b, #b45309); }
.category-tu { background: linear-gradient(135deg, #8b5cf6, #5b21b6); }
.category-lainnya { background: linear-gradient(135deg, #64748b, #334155); }

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

.btn-block {
    width: 100%;
    justify-content: center;
}

/* Filter Pills */
.filter-btn {
    background: #ffffff;
    border: 1px solid var(--border-color);
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    color: var(--text-main);
    cursor: pointer;
    transition: all 0.2s;
}
.filter-btn:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}
.filter-btn.active {
    background: var(--primary);
    color: #ffffff;
    border-color: var(--primary);
}
.filter-btn-pending {
    border-color: #f59e0b;
    color: #b45309;
    background: #fffbeb;
}
.filter-btn-pending.active {
    background: #b45309;
    color: #ffffff;
    border-color: #b45309;
}

.filter-pill {
    background: #ffffff;
    border: 1px solid var(--border-color);
    padding: 3px 10px;
    border-radius: 14px;
    font-size: 11px;
    font-weight: 500;
    color: var(--text-muted);
    cursor: pointer;
    transition: all 0.2s;
}
.filter-pill:hover {
    background: #f1f5f9;
    color: var(--text-main);
}
.filter-pill.active {
    background: var(--primary);
    color: #ffffff;
    border-color: var(--primary);
}

/* Modal Tabs */
.modal-tabs-header {
    display: flex;
    background: #f8fafc;
    border-bottom: 1px solid var(--border-color);
    padding: 0 16px;
}
.modal-tab-btn {
    padding: 12px 16px;
    background: transparent;
    border: none;
    border-bottom: 2px solid transparent;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-muted);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}
.modal-tab-btn:hover {
    color: var(--text-main);
}
.modal-tab-btn.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
    background: #ffffff;
}

.modal-tab-content {
    display: none;
}
.modal-tab-content.active {
    display: block;
}

@keyframes bellShake {
    0%, 100% { transform: rotate(0); }
    10%, 30%, 50% { transform: rotate(-12deg); }
    20%, 40% { transform: rotate(12deg); }
    60% { transform: rotate(0); }
}
</style>

<!-- ============================================================== -->
<!-- JAVASCRIPT: FILTER, SEARCH, MODAL POPULATION & REVIEW          -->
<!-- ============================================================== -->
<script>
// Data Master Kartu Guru dari Controller
const teacherCardsData = <?= json_encode($teacherCards, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const baseUrl = "<?= $baseUrl ?>";

let currentFilter = 'all';
let currentSearch = '';
let activeTeacher = null;

// Filter Tab Click Handler
function filterCards(filter, btn) {
    currentFilter = filter;
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    applyFilters();
}

// Search Input Handler
function handleSearch() {
    currentSearch = document.getElementById('teacherSearchInput').value.toLowerCase().trim();
    applyFilters();
}

// Apply Both Filter and Search
function applyFilters() {
    const cards = document.querySelectorAll('.teacher-card');
    let visibleCount = 0;

    cards.forEach(card => {
        const name = card.dataset.name || '';
        const nipy = card.dataset.nipy || '';
        const category = card.dataset.category || '';
        const pending = parseInt(card.dataset.pending || '0', 10);
        const missing = parseInt(card.dataset.missing || '0', 10);

        let matchesSearch = (!currentSearch || name.includes(currentSearch) || nipy.includes(currentSearch));
        let matchesFilter = false;

        if (currentFilter === 'all') {
            matchesFilter = true;
        } else if (currentFilter === 'pending') {
            matchesFilter = (pending > 0);
        } else if (currentFilter === 'incomplete') {
            matchesFilter = (missing > 0);
        } else if (currentFilter === category) {
            matchesFilter = true;
        }

        if (matchesSearch && matchesFilter) {
            card.style.display = 'flex';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
}

// Sort Handler
function handleSort() {
    const sortVal = document.getElementById('teacherSortSelect').value;
    const container = document.getElementById('teacherCardsContainer');
    const cards = Array.from(document.querySelectorAll('.teacher-card'));

    cards.sort((a, b) => {
        const idxA = parseInt(a.dataset.index, 10);
        const idxB = parseInt(b.dataset.index, 10);
        const dataA = teacherCardsData[idxA];
        const dataB = teacherCardsData[idxB];

        if (sortVal === 'pending_first') {
            if (dataB.pending_count !== dataA.pending_count) {
                return dataB.pending_count - dataA.pending_count;
            }
            return dataA.name.localeCompare(dataB.name);
        } else if (sortVal === 'name_asc') {
            return dataA.name.localeCompare(dataB.name);
        } else if (sortVal === 'percent_desc') {
            return dataB.percent - dataA.percent;
        } else if (sortVal === 'missing_desc') {
            return dataB.missing_count - dataA.missing_count;
        }
        return 0;
    });

    cards.forEach(card => container.appendChild(card));
}

// Open Teacher Detail Modal
function openTeacherDetail(index) {
    const t = teacherCardsData[index];
    if (!t) return;
    activeTeacher = t;

    // Header info
    document.getElementById('modalTeacherName').innerText = t.name;
    document.getElementById('modalTeacherNipy').innerText = 'NIPY: ' + t.nipy;
    document.getElementById('modalTeacherRole').innerText = t.role_display;

    // Avatar initials & background
    const avatarEl = document.getElementById('modalAvatar');
    avatarEl.className = 'teacher-avatar category-' + t.category;
    const parts = (t.name || '').trim().split(' ');
    let inits = '';
    for (let i = 0; i < Math.min(2, parts.length); i++) {
        if (parts[i]) inits += parts[i].substring(0, 1).toUpperCase();
    }
    avatarEl.innerText = inits || 'GR';

    // Summary stats
    document.getElementById('modalStatUploaded').innerText = t.uploaded_count + ' / ' + t.expected_count + ' Dokumen (' + t.total_files + ' berkas)';
    document.getElementById('modalStatMissing').innerText = t.missing_count + ' Dokumen';
    document.getElementById('modalStatPending').innerText = t.pending_count + ' Berkas';
    document.getElementById('modalStatApproved').innerText = t.approved_count + ' Berkas';
    document.getElementById('modalStatPercent').innerText = t.percent + '%';

    // Badge counts on tabs
    document.getElementById('modalCountUploaded').innerText = t.uploads.length;
    document.getElementById('modalCountMissing').innerText = t.missing_items.length;
    document.getElementById('modalCountKarya').innerText = t.karya.length;

    // Batch approve button visibility
    const batchForm = document.getElementById('modalBatchApproveForm');
    const batchBtn = document.getElementById('modalBatchBtn');
    if (t.pending_count > 0) {
        document.getElementById('modalBatchUserId').value = t.user_id;
        batchBtn.innerHTML = '<i class="fa-solid fa-check-double"></i> Setujui Semua yang Menunggu (' + t.pending_count + ')';
        batchForm.style.display = 'inline-block';
    } else {
        batchForm.style.display = 'none';
    }

    // Reset Period Filters
    resetModalPeriodFilters();

    // Populate Tab 1: Berkas Diunggah
    populateUploadedTable(t.uploads);

    // Populate Tab 2: Berkas Belum Diunggah
    populateMissingTable(t.missing_items);

    // Populate Tab 3: Riwayat Bulanan (12 Bulan)
    populateMonthlyTable(t.monthly_matrix || [], t.months_summary || {}, t.monthly_past_missing || 0, t.monthly_current_missing || 0, t.current_month_num || 1);

    // Update monthly tab badge
    const mBadge = document.getElementById('modalMonthlyBadge');
    if (mBadge) {
        if ((t.monthly_past_missing || 0) > 0) {
            mBadge.style.display = 'inline-block';
            mBadge.style.background = '#fee2e2';
            mBadge.style.color = '#dc2626';
            mBadge.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> ' + t.monthly_past_missing + ' kurang';
        } else {
            mBadge.style.display = 'inline-block';
            mBadge.style.background = '#dcfce7';
            mBadge.style.color = '#15803d';
            mBadge.innerHTML = '<i class="fa-solid fa-check"></i> Tertib';
        }
    }

    // Populate Tab 4: Karya Ilmiah
    populateKaryaTable(t.karya);

    // Default to Tab 1 (or Tab 2 if Tab 1 empty)
    if (t.uploads.length > 0) {
        switchDetailTab('uploaded');
    } else {
        switchDetailTab('missing');
    }

    document.getElementById('teacherDetailModal').classList.add('active');
}

function closeTeacherDetailModal() {
    document.getElementById('teacherDetailModal').classList.remove('active');
}

// Switch Detail Tabs
function switchDetailTab(tab) {
    document.querySelectorAll('.modal-tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.modal-tab-content').forEach(content => content.classList.remove('active'));

    if (tab === 'uploaded') {
        document.getElementById('tabBtnUploaded').classList.add('active');
        document.getElementById('detailTabUploaded').classList.add('active');
    } else if (tab === 'missing') {
        document.getElementById('tabBtnMissing').classList.add('active');
        document.getElementById('detailTabMissing').classList.add('active');
    } else if (tab === 'monthly') {
        document.getElementById('tabBtnMonthly').classList.add('active');
        document.getElementById('detailTabMonthly').classList.add('active');
    } else if (tab === 'karya') {
        document.getElementById('tabBtnKarya').classList.add('active');
        document.getElementById('detailTabKarya').classList.add('active');
    }
}

// Reset Modal Period Filters
function resetModalPeriodFilters() {
    document.querySelectorAll('#uploadedPeriodFilters .filter-pill').forEach((btn, idx) => {
        if (idx === 0) btn.classList.add('active');
        else btn.classList.remove('active');
    });
    document.querySelectorAll('#missingPeriodFilters .filter-pill').forEach((btn, idx) => {
        if (idx === 0) btn.classList.add('active');
        else btn.classList.remove('active');
    });
    const upCountEl = document.getElementById('uploadedFilterCount');
    if (upCountEl) upCountEl.innerText = '';
    const missCountEl = document.getElementById('missingFilterCount');
    if (missCountEl) missCountEl.innerText = '';
}

function getPeriodBadge(periodKey) {
    if (periodKey === 'bulanan') {
        return '<span class="badge" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 10.5px; padding: 2px 7px;"><i class="fa-solid fa-calendar-days"></i> Bulanan</span>';
    } else if (periodKey === 'tahunan') {
        return '<span class="badge" style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-size: 10.5px; padding: 2px 7px;"><i class="fa-solid fa-calendar"></i> 1x Setahun</span>';
    } else if (periodKey === 'semester') {
        return '<span class="badge" style="background: #fefce8; color: #854d0e; border: 1px solid #fef08a; font-size: 10.5px; padding: 2px 7px;"><i class="fa-solid fa-calendar-week"></i> 2x Setahun</span>';
    } else {
        return '<span class="badge" style="background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; font-size: 10.5px; padding: 2px 7px;"><i class="fa-solid fa-clock"></i> Lainnya</span>';
    }
}

// Filter Modal Table by Period
function filterModalTable(type, period, btn) {
    const parentContainer = type === 'uploaded' 
        ? document.getElementById('uploadedPeriodFilters') 
        : document.getElementById('missingPeriodFilters');
    
    if (parentContainer) {
        parentContainer.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
    }
    if (btn) btn.classList.add('active');

    const tbody = type === 'uploaded' 
        ? document.getElementById('modalUploadedTableBody') 
        : document.getElementById('modalMissingTableBody');
    
    const rows = tbody.querySelectorAll('tr[data-period]');
    let visibleCount = 0;

    rows.forEach(tr => {
        const itemPeriod = tr.getAttribute('data-period');
        if (period === 'all' || itemPeriod === period) {
            tr.style.display = '';
            visibleCount++;
            // Re-number
            const numCell = tr.querySelector('.row-num');
            if (numCell) numCell.innerText = visibleCount;
        } else {
            tr.style.display = 'none';
        }
    });

    const countLabel = type === 'uploaded' 
        ? document.getElementById('uploadedFilterCount') 
        : document.getElementById('missingFilterCount');
    
    if (countLabel) {
        if (period === 'all') {
            countLabel.innerText = 'Menampilkan seluruh (' + visibleCount + ') berkas';
        } else {
            countLabel.innerText = 'Menampilkan ' + visibleCount + ' berkas periode ' + period;
        }
    }
}

// Populate Tab 1: Berkas Diunggah
function populateUploadedTable(uploads) {
    const tbody = document.getElementById('modalUploadedTableBody');
    tbody.innerHTML = '';

    if (!uploads || uploads.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 30px; color: var(--text-muted);"><i class="fa-solid fa-folder-open" style="font-size: 28px; margin-bottom: 8px; color: #cbd5e1; display: block;"></i>Belum ada berkas administrasi yang diunggah oleh pegawai ini.</td></tr>';
        return;
    }

    uploads.forEach((up, i) => {
        let statusBadge = '';
        if (up.status === 'Disetujui') {
            statusBadge = '<span class="badge badge-success"><i class="fa-solid fa-check"></i> Disetujui</span>';
        } else if (up.status === 'Revisi') {
            statusBadge = '<span class="badge badge-danger"><i class="fa-solid fa-triangle-exclamation"></i> Revisi</span>';
        } else {
            statusBadge = '<span class="badge badge-warning"><i class="fa-solid fa-clock"></i> Menunggu</span>';
        }

        let timeBadge = up.is_on_time == 1 
            ? '<span class="badge badge-success" style="font-size: 10px; margin-top: 3px; display: inline-block;">Tepat Waktu</span>' 
            : '<span class="badge badge-danger" style="font-size: 10px; margin-top: 3px; display: inline-block;">Terlambat</span>';

        let uploadDate = up.uploaded_at ? up.uploaded_at.substring(0, 16) : '-';

        let subTitleText = up.sub_title ? '<div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">' + escapeHtml(up.sub_title) + '</div>' : '';
        let notesText = up.review_notes ? '<div style="font-size: 11px; color: #ef4444; margin-top: 4px; background: #fff1f2; padding: 3px 6px; border-radius: 4px;">Catatan: ' + escapeHtml(up.review_notes) + '</div>' : '';

        const pKey = up.period_key || 'lainnya';
        const pBadge = getPeriodBadge(pKey);

        const tr = document.createElement('tr');
        tr.setAttribute('data-period', pKey);
        tr.innerHTML = `
            <td class="row-num" style="text-align: center; font-weight: 600; color: var(--text-muted);">${i + 1}</td>
            <td>
                <strong>${escapeHtml(up.jenis_title)}</strong>
                ${subTitleText}
                <div style="font-size: 10px; color: var(--text-muted); margin-top: 2px;">
                    <span class="badge badge-secondary" style="font-size: 9px;">${escapeHtml(up.target_role || '')}</span>
                    ${up.frequency ? '<span style="margin-left: 4px;">(' + escapeHtml(up.frequency) + ')</span>' : ''}
                    ${up.version_num > 1 ? '<span class="badge badge-info" style="font-size: 9px; margin-left: 4px;">v' + up.version_num + '</span>' : ''}
                </div>
            </td>
            <td>${pBadge}</td>
            <td>
                <a href="${baseUrl}/administrasi/download/${up.id}" target="_blank" class="btn btn-outline btn-sm" title="Unduh Berkas">
                    <i class="fa-solid fa-file-arrow-down" style="color: var(--primary);"></i> ${escapeHtml(up.original_filename || 'Unduh Berkas')}
                </a>
            </td>
            <td>
                <div>${uploadDate}</div>
                ${timeBadge}
            </td>
            <td>
                ${statusBadge}
                ${notesText}
            </td>
            <td style="text-align: center;">
                <button type="button" class="btn btn-primary btn-sm" onclick='openReviewModalFromDetail(${JSON.stringify(up).replace(/'/g, "&apos;")})'>
                    <i class="fa-solid fa-stamp"></i> Review
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Populate Tab 2: Berkas Belum Diunggah
function populateMissingTable(missingItems) {
    const tbody = document.getElementById('modalMissingTableBody');
    const noticeEl = document.getElementById('missingEmptyNotice');
    const tableWrapper = document.getElementById('missingTableWrapper');
    tbody.innerHTML = '';

    if (!missingItems || missingItems.length === 0) {
        noticeEl.style.display = 'flex';
        tableWrapper.style.display = 'none';
        return;
    }

    noticeEl.style.display = 'none';
    tableWrapper.style.display = 'block';

    missingItems.forEach((m, i) => {
        let roleBadge = '<span class="badge badge-secondary">' + escapeHtml(m.target_role) + '</span>';
        if (m.target_role === 'WALAS') roleBadge = '<span class="badge badge-warning">WALI KELAS</span>';
        else if (m.target_role === 'TU') roleBadge = '<span class="badge" style="background: #f5f3ff; color: #6d28d9;">TATA USAHA</span>';
        else if (m.target_role === 'GURU_MAPEL') roleBadge = '<span class="badge badge-info">GURU MAPEL</span>';
        else if (m.target_role === 'GURU_TK') roleBadge = '<span class="badge badge-info">GURU TK</span>';

        const pKey = m.period_key || 'lainnya';
        const pBadge = getPeriodBadge(pKey);

        const tr = document.createElement('tr');
        tr.setAttribute('data-period', pKey);
        tr.innerHTML = `
            <td class="row-num" style="text-align: center; font-weight: 600; color: var(--text-muted);">${i + 1}</td>
            <td>
                <strong style="color: var(--text-main);">${escapeHtml(m.title)}</strong>
                ${m.description ? '<div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">' + escapeHtml(m.description) + '</div>' : ''}
            </td>
            <td>
                ${pBadge}
                <div style="font-size: 10px; color: var(--text-muted); margin-top: 2px;">${escapeHtml(m.frequency || '1x setahun')}</div>
            </td>
            <td>${roleBadge}</td>
            <td style="text-align: center;">
                <span class="badge badge-danger" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;">
                    <i class="fa-solid fa-circle-xmark" style="margin-right: 3px;"></i> Belum Diunggah
                </span>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Populate Tab 3: Karya Ilmiah
function populateKaryaTable(karyaList) {
    const tbody = document.getElementById('modalKaryaTableBody');
    tbody.innerHTML = '';

    if (!karyaList || karyaList.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 30px; color: var(--text-muted);"><i class="fa-solid fa-graduation-cap" style="font-size: 28px; margin-bottom: 8px; color: #cbd5e1; display: block;"></i>Belum ada karya tulis ilmiah (PTK atau Best Practice) yang diajukan oleh guru ini.</td></tr>';
        return;
    }

    karyaList.forEach((k, i) => {
        let statusBadge = '';
        if (k.status === 'Disetujui') {
            statusBadge = '<span class="badge badge-success"><i class="fa-solid fa-check"></i> Disetujui</span>';
        } else if (k.status === 'Revisi') {
            statusBadge = '<span class="badge badge-danger"><i class="fa-solid fa-triangle-exclamation"></i> Revisi</span>';
        } else {
            statusBadge = '<span class="badge badge-warning"><i class="fa-solid fa-clock"></i> Menunggu</span>';
        }

        let typeBadge = k.type === 'PTK' 
            ? '<span class="badge badge-info" style="font-weight: 700;">PTK</span>' 
            : '<span class="badge" style="background: #f3e8ff; color: #7e22ce; font-weight: 700;">Best Practice</span>';

        let notesText = k.review_notes ? '<div style="font-size: 11px; color: #ef4444; margin-top: 4px; background: #fff1f2; padding: 3px 6px; border-radius: 4px;">Catatan: ' + escapeHtml(k.review_notes) + '</div>' : '';

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="text-align: center; font-weight: 600; color: var(--text-muted);">${i + 1}</td>
            <td>${typeBadge}</td>
            <td>
                <strong>${escapeHtml(k.title)}</strong>
                <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                    Diajukan: ${k.created_at ? k.created_at.substring(0, 16) : '-'}
                </div>
            </td>
            <td style="text-align: center;">
                <a href="${baseUrl}/karya-ilmiah/download/${k.id}" target="_blank" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-file-pdf" style="color: #dc2626;"></i> PDF
                </a>
            </td>
            <td>
                ${statusBadge}
                ${notesText}
            </td>
            <td style="text-align: center;">
                <button type="button" class="btn btn-primary btn-sm" onclick='openKaryaModalFromDetail(${JSON.stringify(k).replace(/'/g, "&apos;")})'>
                    <i class="fa-solid fa-stamp"></i> Review
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Populate Tab 3: Riwayat Bulanan (12 Bulan)
function populateMonthlyTable(matrix, monthsSummary, pastMissing, currentMissing, currentMonthNum) {
    const tbody = document.getElementById('modalMonthlyTableBody');
    const alertBanner = document.getElementById('monthlyAlertBanner');
    const pastMissingBox = document.getElementById('monthlyPastMissingContainer');
    tbody.innerHTML = '';
    alertBanner.innerHTML = '';
    pastMissingBox.innerHTML = '';

    // Alert Banner
    if (pastMissing > 0) {
        alertBanner.innerHTML = `
            <div class="alert alert-danger" style="display: flex; align-items: center; gap: 12px; background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 12px 16px; border-radius: 8px;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 22px; color: #dc2626; flex-shrink: 0;"></i>
                <div>
                    <div style="font-weight: 700; font-size: 13px;">Perhatian: Ada ${pastMissing} Dokumen Bulanan di Bulan-Bulan Sebelumnya yang Belum Dikumpulkan!</div>
                    <div style="font-size: 12px; margin-top: 2px; color: #7f1d1d;">Pegawai ini terdeteksi memiliki tunggakan dokumen wajib bulanan pada bulan lampau. Silakan periksa daftar sel berwarna merah di bawah ini.</div>
                </div>
            </div>
        `;
    } else {
        alertBanner.innerHTML = `
            <div class="alert alert-success" style="display: flex; align-items: center; gap: 12px; background: #f0fdf4; border: 1px solid #86efac; color: #166534; padding: 12px 16px; border-radius: 8px;">
                <i class="fa-solid fa-circle-check" style="font-size: 22px; color: #16a34a; flex-shrink: 0;"></i>
                <div>
                    <div style="font-weight: 700; font-size: 13px;">Alhamdulillah, Tertib Pengumpulan Bulanan</div>
                    <div style="font-size: 12px; margin-top: 2px; color: #14532d;">Seluruh dokumen administrasi bulanan pada bulan-bulan lampau telah diunggah lengkap oleh pegawai ini.</div>
                </div>
            </div>
        `;
    }

    if (!matrix || matrix.length === 0) {
        tbody.innerHTML = '<tr><td colspan="14" style="text-align: center; padding: 24px; color: var(--text-muted);"><i class="fa-solid fa-calendar-check" style="font-size: 28px; margin-bottom: 8px; color: #cbd5e1; display: block;"></i>Tidak ada jenis administrasi berfrekuensi bulanan untuk peran pegawai ini.</td></tr>';
        return;
    }

    const uncollectedPast = [];

    matrix.forEach((row, i) => {
        const tr = document.createElement('tr');
        let cellsHtml = `
            <td style="text-align: center; font-weight: 600; color: var(--text-muted);">${i + 1}</td>
            <td>
                <strong>${escapeHtml(row.title)}</strong>
                <div style="font-size: 10px; color: var(--text-muted); margin-top: 2px;">
                    ${row.code ? '<span class="badge badge-secondary" style="font-size: 9px; margin-right: 4px;">' + escapeHtml(row.code) + '</span>' : ''}
                    <span>${escapeHtml(row.due_info || 'Bulanan')}</span>
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

    // Jika ada berkas lampau yang belum dikumpulkan, tampilkan rincian kotak khusus
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
                    <i class="fa-solid fa-clipboard-list"></i> Rincian Dokumen Bulan Sebelumnya yang Belum Dikumpulkan (${uncollectedPast.length} item):
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
    document.getElementById('review_doc_title').innerText = (up.teacher_name || (activeTeacher ? activeTeacher.name : '')) + ' - ' + (up.jenis_title || '');
    document.getElementById('review_status_select').value = up.status === 'Revisi' ? 'Revisi' : 'Disetujui';
    document.getElementById('review_notes').value = up.review_notes || '';
    document.getElementById('reviewModal').classList.add('active');
}
function closeReviewModal() {
    document.getElementById('reviewModal').classList.remove('active');
}

// Karya Modal
function openKaryaModalFromDetail(k) {
    document.getElementById('karya_id').value = k.id;
    document.getElementById('karya_title_display').innerText = '[' + k.type + '] ' + (k.teacher_name || (activeTeacher ? activeTeacher.name : '')) + ' - ' + k.title;
    document.getElementById('karya_status_select').value = k.status === 'Revisi' ? 'Revisi' : 'Disetujui';
    document.getElementById('karya_review_notes').value = k.review_notes || '';
    document.getElementById('karyaModal').classList.add('active');
}
function closeKaryaModal() {
    document.getElementById('karyaModal').classList.remove('active');
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
        } else if (document.getElementById('karyaModal').classList.contains('active')) {
            closeKaryaModal();
        } else if (document.getElementById('teacherDetailModal').classList.contains('active')) {
            closeTeacherDetailModal();
        }
    }
});
</script>
