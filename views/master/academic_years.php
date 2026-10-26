<?php
// views/master/academic_years.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$isOperatorYayasan = Auth::hasRole('operator_yayasan');
$isOperatorSekolah = Auth::hasRole('operator_sekolah');
$canManageStorage = $isOperatorYayasan; // Khusus Operator Yayasan yang dapat mengelola storage
$userUnitId = $userUnitId ?? Auth::unitId();

// Data Tahun Ajaran Aktif
$activeTa = null;
foreach ($taList as $t) {
    if (!empty($t['is_active'])) {
        $activeTa = $t;
        break;
    }
}

// Nama Satuan Unit Sekolah Pengguna
$myUnitName = '';
if (!empty($units) && !empty($userUnitId)) {
    foreach ($units as $u) {
        if ((int)$u['id'] === (int)$userUnitId) {
            $myUnitName = $u['name'];
            break;
        }
    }
}
if (empty($myUnitName)) {
    $myUnitName = Auth::user()['unit_name'] ?? ('Unit #' . ($userUnitId ?: ''));
}

// Filter Persetujuan: Operator Sekolah hanya fokus melihat pengajuan unitnya sendiri
$filteredApprovals = [];
foreach ($approvals as $ap) {
    if ($isOperatorSekolah) {
        if ((int)$ap['unit_id'] === (int)$userUnitId) {
            $filteredApprovals[] = $ap;
        }
    } else {
        $filteredApprovals[] = $ap;
    }
}
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span class="badge badge-primary"><?= $isOperatorYayasan ? 'Operator Yayasan' : 'Operator Unit: ' . SecurityHelper::escape($myUnitName) ?></span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Tahun Ajaran Aktif: <?= SecurityHelper::escape(Auth::academicYearName()) ?></span>
            </div>
            <h1 class="page-title" style="margin: 0 0 6px 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-calendar-check" style="color: #0d9488;"></i> <?= $isOperatorYayasan ? 'Tahun Ajaran &amp; Persetujuan Transisi Unit' : 'Tahun Ajaran &amp; Persetujuan Satuan Unit' ?>
            </h1>
            <p class="page-subtitle" style="margin: 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                <?= $isOperatorYayasan 
                    ? 'Pengajuan pembukaan tahun ajaran baru oleh Yayasan serta verifikasi kesiapan &amp; persetujuan 100% satuan unit sekolah.' 
                    : 'Monitoring tahun ajaran aktif dan verifikasi persetujuan transisi periode akademik untuk satuan unit sekolah Anda.' ?>
            </p>
        </div>
        <?php if ($isOperatorYayasan): ?>
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <button type="button" class="btn btn-outline" onclick="openNasModal()" style="display: inline-flex; align-items: center; gap: 8px; border-color: #0284c7; color: #0284c7; background: #f0f9ff;">
                    <i class="fa-solid fa-server"></i> Kelola Cold Storage NAS
                </button>
                <button type="button" class="btn btn-primary" onclick="openTaModal()" style="display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-plus"></i> Ajukan Tahun Ajaran Baru
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($isOperatorSekolah && !empty($myPendingApproval)): ?>
    <!-- Banner Aksi Persetujuan Operator Sekolah -->
    <div class="card" style="margin-bottom: 24px; border-left: 4px solid #f59e0b; background: #fffbeb;">
        <div class="card-body" style="padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
                <div style="display: flex; gap: 14px; align-items: flex-start; max-width: 720px;">
                    <div style="width: 44px; height: 44px; border-radius: 50%; background: #fef3c7; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #d97706; font-size: 1.25rem;">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                            <span class="badge badge-warning" style="font-weight: 600;">Menunggu Respon Unit</span>
                            <span style="font-size: 0.8125rem; font-weight: 600; color: #92400e;">Satuan: <?= SecurityHelper::escape($myPendingApproval['unit_name']) ?></span>
                        </div>
                        <h3 style="margin: 0 0 6px 0; font-size: 1.1rem; color: #78350f;">
                            Pengajuan Tahun Ajaran Baru: <strong><?= SecurityHelper::escape($myPendingApproval['ta_name']) ?> (Semester <?= SecurityHelper::escape($myPendingApproval['semester']) ?>)</strong>
                        </h3>
                        <p style="margin: 0; font-size: 0.875rem; color: #92400e; line-height: 1.5;">
                            Operator Yayasan telah mengajukan transisi periode akademik baru. Mohon periksa kelengkapan rapor, nilai akhir santri, dan administrasi unit sekolah Anda sebelum memberikan keputusan persetujuan.
                        </p>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <!-- Tombol Setujui Langsung -->
                    <form method="POST" action="<?= $baseUrl ?>/master/academic-years/approve" onsubmit="return confirm('Apakah Anda yakin satuan unit Anda telah siap untuk menyetujui transisi ke Tahun Ajaran Baru <?= SecurityHelper::escape($myPendingApproval['ta_name']) ?>?')">
                        <?= CSRF::inputField() ?>
                        <input type="hidden" name="approval_id" value="<?= (int)$myPendingApproval['id'] ?>">
                        <input type="hidden" name="status" value="Setuju">
                        <button type="submit" class="btn btn-primary" style="background: #10b981; border-color: #10b981; display: inline-flex; align-items: center; gap: 6px; padding: 10px 18px; font-weight: 600;">
                            <i class="fa-solid fa-circle-check"></i> Setujui Tahun Ajaran
                        </button>
                    </form>

                    <!-- Tombol Tolak dengan Alasan -->
                    <button type="button" class="btn btn-outline" onclick="openRejectModal(<?= (int)$myPendingApproval['id'] ?>, '<?= SecurityHelper::escape($myPendingApproval['ta_name']) ?> (Semester <?= SecurityHelper::escape($myPendingApproval['semester']) ?>)')" style="border-color: #ef4444; color: #ef4444; display: inline-flex; align-items: center; gap: 6px; padding: 10px 18px; font-weight: 600;">
                        <i class="fa-solid fa-circle-xmark"></i> Tolak (Dengan Alasan)
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($isOperatorYayasan && !empty($storageOverview)): 
    $hotInfo = $storageOverview['hot'] ?? [];
    $coldInfo = $storageOverview['cold'] ?? [];
    $archSummary = $storageOverview['archived_summary'] ?? [];
?>
<!-- Panel Dashboard Arsitektur Hot vs Cold Storage (Khusus Operator Yayasan) -->
<div class="card" style="margin-bottom: 24px; border: 1px solid #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 34px; height: 34px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 1.05rem;">
                <i class="fa-solid fa-hard-drive"></i>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: #0f172a;">
                    Sistem Penyimpanan Data Bertingkat: Hot Storage (Hosting) &amp; Cold Storage (NAS Lokal)
                </h3>
                <p style="margin: 0; font-size: 0.75rem; color: #64748b;">
                    Tahun ajaran aktif berjalan di media hosting cepat. Tahun ajaran lampau diarsipkan ke NAS lokal sekolah.
                </p>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <button type="button" class="btn btn-outline btn-sm" onclick="checkNasStatusAjax()" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.75rem; padding: 5px 10px;">
                <i class="fa-solid fa-arrows-rotate" id="nasRefreshIcon"></i> Cek Status NAS
            </button>
            <button type="button" class="btn btn-outline btn-sm" onclick="openNasModal()" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.75rem; padding: 5px 10px; border-color: #0284c7; color: #0284c7;">
                <i class="fa-solid fa-gear"></i> Konfigurasi NAS
            </button>
        </div>
    </div>
    <div class="card-body" style="padding: 16px 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
            <!-- Tier 1: Hot Storage (Hosting) -->
            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 14px 16px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 1.1rem; color: #ea580c;"><i class="fa-solid fa-fire"></i></span>
                        <strong style="color: #92400e; font-size: 0.875rem;">Hot Storage (Hosting Web)</strong>
                    </div>
                    <span class="badge" style="background: #dcfce7; color: #15803d; font-size: 0.6875rem; border: 1px solid #bbf7d0;">Online / Aktif</span>
                </div>
                <div style="font-size: 0.8125rem; color: #78350f; margin-bottom: 6px;">
                    Peruntukan: <strong>Tahun Ajaran Aktif Berjalan</strong>
                </div>
                <div style="font-size: 0.75rem; color: #a16207; line-height: 1.4;">
                    Path: <code style="background: rgba(255,255,255,0.7); padding: 1px 4px; border-radius: 3px;"><?= SecurityHelper::escape(basename($hotInfo['path'] ?? 'storage/uploads')) ?></code>
                    <?php if (!empty($hotInfo['free_space'])): ?>
                        <br>Sisa Kuota Hosting: <strong><?= round($hotInfo['free_space'] / (1024*1024*1024), 2) ?> GB</strong>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tier 2: Cold Storage (NAS Lokal) -->
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 14px 16px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 1.1rem; color: #0284c7;"><i class="fa-solid fa-snowflake"></i></span>
                        <strong style="color: #166534; font-size: 0.875rem;">Cold Storage (NAS Lokal)</strong>
                    </div>
                    <span id="nasStatusBadge" class="badge" style="background: <?= $coldInfo['is_online'] ? '#dcfce7' : '#fee2e2' ?>; color: <?= $coldInfo['is_online'] ? '#15803d' : '#b91c1c' ?>; font-size: 0.6875rem; border: 1px solid <?= $coldInfo['is_online'] ? '#bbf7d0' : '#fecaca' ?>;">
                        <?= $coldInfo['is_online'] ? 'NAS Terhubung' : 'NAS Terputus' ?>
                    </span>
                </div>
                <div style="font-size: 0.8125rem; color: #14532d; margin-bottom: 6px;">
                    Peruntukan: <strong>Arsip Dokumen Seluruh TA Lampau</strong>
                </div>
                <div id="nasStatusDesc" style="font-size: 0.75rem; color: #15803d; line-height: 1.4;">
                    Driver: <strong><?= strtoupper(SecurityHelper::escape($coldInfo['driver'] ?? 'local_path')) ?></strong>
                    <br><?= SecurityHelper::escape($coldInfo['status_msg'] ?? '') ?>
                </div>
            </div>

            <!-- Tier 3: Efisiensi & Penghematan -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 1.1rem; color: #475569;"><i class="fa-solid fa-cloud-arrow-down"></i></span>
                        <strong style="color: #334155; font-size: 0.875rem;">Efisiensi Kuota Hosting</strong>
                    </div>
                    <span class="badge" style="background: #e2e8f0; color: #475569; font-size: 0.6875rem;">Terarsip</span>
                </div>
                <div style="font-size: 1.35rem; font-weight: 700; color: #0f172a; line-height: 1.2;">
                    <?= (int)$archSummary['total_files'] ?> <span style="font-size: 0.8125rem; font-weight: 500; color: #64748b;">Berkas di NAS</span>
                </div>
                <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">
                    Kapasitas Hosting Dihemat: <strong style="color: #0d9488; font-size: 0.875rem;"><?= $archSummary['total_mb'] ?> MB</strong>
                </div>
            </div>
        </div>
    </div>
</div>
<?php elseif ($isOperatorSekolah): ?>
<!-- Kartu Ringkasan Tahun Ajaran (T.A) Aktif (Khusus Operator Sekolah) -->
<div class="card" style="margin-bottom: 24px; border: 1px solid #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.05); background: #ffffff;">
    <div class="card-header" style="background: linear-gradient(135deg, #f0fdf4 0%, #e0f2fe 100%); border-bottom: 1px solid #e2e8f0; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: #0d9488; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; box-shadow: 0 2px 4px rgba(13,148,136,0.25);">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    Tahun Ajaran Aktif: <?= SecurityHelper::escape($activeTa['name'] ?? Auth::academicYearName() ?? '-') ?>
                    <span class="badge badge-success" style="font-size: 0.7rem; font-weight: 600;"><i class="fa-solid fa-circle-check"></i> Sedang Berjalan</span>
                </h3>
                <p style="margin: 2px 0 0 0; font-size: 0.78rem; color: #475569;">
                    Informasi periode kalender pendidikan dan status operasional aktif untuk satuan unit sekolah Anda.
                </p>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <span class="badge badge-info" style="font-size: 0.8125rem; padding: 6px 12px; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-school"></i> <?= SecurityHelper::escape($myUnitName) ?>
            </span>
        </div>
    </div>
    <div class="card-body" style="padding: 18px 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
            <!-- Kolom 1: Periode & Semester -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.5px;">Periode Akademik</span>
                    <span style="font-size: 1.1rem; color: #0d9488;"><i class="fa-solid fa-calendar-check"></i></span>
                </div>
                <div style="font-size: 1.35rem; font-weight: 800; color: #0f172a; line-height: 1.2;">
                    <?= SecurityHelper::escape($activeTa['name'] ?? Auth::academicYearName() ?? '-') ?>
                </div>
                <div style="font-size: 0.8125rem; color: #0d9488; font-weight: 600; margin-top: 4px;">
                    Semester <?= SecurityHelper::escape($activeTa['semester'] ?? 'Ganjil') ?>
                </div>
            </div>

            <!-- Kolom 2: Satuan Unit Sekolah -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.5px;">Unit Sekolah Anda</span>
                    <span style="font-size: 1.1rem; color: #0284c7;"><i class="fa-solid fa-school"></i></span>
                </div>
                <div style="font-size: 1.15rem; font-weight: 700; color: #0f172a; line-height: 1.3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= SecurityHelper::escape($myUnitName) ?>">
                    <?= SecurityHelper::escape($myUnitName) ?>
                </div>
                <div style="font-size: 0.8125rem; color: #64748b; margin-top: 4px;">
                    Operator Unit Sekolah
                </div>
            </div>

            <!-- Kolom 3: Target Kalender Pendidikan -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.5px;">Target Kalender</span>
                    <span style="font-size: 1.1rem; color: #d97706;"><i class="fa-solid fa-clock"></i></span>
                </div>
                <div style="font-size: 1.25rem; font-weight: 700; color: #0f172a; line-height: 1.2;">
                    <?= (int)($activeTa['target_workdays'] ?? 264) ?> <span style="font-size: 0.8125rem; font-weight: 500; color: #64748b;">Hari Kerja</span>
                </div>
                <div style="font-size: 0.8125rem; color: #64748b; margin-top: 4px;">
                    Target Kajian: <strong><?= (int)($activeTa['target_kajian'] ?? 48) ?> Pertemuan</strong>
                </div>
            </div>

            <!-- Kolom 4: Status Operasional Data -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.5px;">Status Operasional</span>
                    <span style="font-size: 1.1rem; color: #16a34a;"><i class="fa-solid fa-shield-halved"></i></span>
                </div>
                <div style="font-size: 1.15rem; font-weight: 700; color: #15803d; line-height: 1.3; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-circle-check"></i> Siap Input Data
                </div>
                <div style="font-size: 0.8125rem; color: #64748b; margin-top: 4px;">
                    Siswa, Presensi, Nilai &amp; Rapor
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="grid-cols-2" style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 24px; align-items: start;">
    <!-- Daftar Tahun Ajaran -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title" style="margin: 0; font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-calendar-days" style="color: #0d9488;"></i> <?= $isOperatorYayasan ? 'Daftar Periode Tahun Ajaran &amp; Storage' : 'Daftar Periode Tahun Ajaran' ?>
            </h3>
            <span class="badge badge-secondary"><?= count($taList ?? []) ?> Periode</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tahun Ajaran</th>
                            <th style="width: 90px; text-align: center;">Semester</th>
                            <th style="width: <?= $isOperatorYayasan ? '170px' : '130px' ?>;">Status<?= $isOperatorYayasan ? ' &amp; Storage' : '' ?></th>
                            <th style="width: <?= $isOperatorYayasan ? '130px' : '90px' ?>; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($taList as $ta): 
                            $isCold = ($ta['storage_tier'] ?? 'hot') === 'cold';
                        ?>
                            <tr>
                                <td>
                                    <strong style="color: var(--text-main, #0f172a); font-size: 0.95rem;"><?= SecurityHelper::escape($ta['name']) ?></strong>
                                    <div style="font-size: 0.75rem; color: var(--text-muted, #64748b);">
                                        Target: <?= (int)($ta['target_workdays'] ?? 264) ?> Hari Kerja | <?= (int)($ta['target_kajian'] ?? 48) ?> Kajian
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge badge-secondary"><?= SecurityHelper::escape($ta['semester']) ?></span>
                                </td>
                                <td>
                                    <?php if ($ta['is_active']): ?>
                                        <div style="margin-bottom: <?= $isOperatorYayasan ? '4px' : '0' ?>;">
                                            <span class="badge badge-success" style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.75rem;">
                                                <i class="fa-solid fa-circle-check"></i> Aktif Berjalan
                                            </span>
                                        </div>
                                        <?php if ($isOperatorYayasan): ?>
                                            <div>
                                                <span class="badge" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; display: inline-flex; align-items: center; gap: 4px; font-size: 0.6875rem;">
                                                    <i class="fa-solid fa-fire" style="color: #ea580c;"></i> Hot Storage (Hosting)
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    <?php elseif ($isCold): ?>
                                        <div style="margin-bottom: <?= $isOperatorYayasan ? '4px' : '0' ?>;">
                                            <span class="badge badge-secondary" style="font-size: 0.75rem;">Arsip Selesai</span>
                                        </div>
                                        <?php if ($isOperatorYayasan): ?>
                                            <div>
                                                <span class="badge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; display: inline-flex; align-items: center; gap: 4px; font-size: 0.6875rem;">
                                                    <i class="fa-solid fa-snowflake" style="color: #0284c7;"></i> Cold Storage (NAS)
                                                </span>
                                                <div style="font-size: 0.6875rem; color: #64748b; margin-top: 2px;">
                                                    <?= (int)($ta['archive_file_count'] ?? 0) ?> berkas (<?= round(($ta['archive_total_size'] ?? 0) / (1024*1024), 2) ?> MB)
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div style="margin-bottom: <?= $isOperatorYayasan ? '4px' : '0' ?>;">
                                            <span class="badge badge-secondary" style="font-size: 0.75rem;">Non-Aktif / Draft</span>
                                        </div>
                                        <?php if ($isOperatorYayasan): ?>
                                            <div>
                                                <span class="badge" style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; display: inline-flex; align-items: center; gap: 4px; font-size: 0.6875rem;" title="Data lampau masih membebani penyimpanan hosting">
                                                    <i class="fa-solid fa-triangle-exclamation"></i> Di Hot (Belum Diarsip)
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: flex; flex-direction: column; gap: 6px; align-items: center;">
                                        <?php if ($isOperatorYayasan && !$ta['is_active']): ?>
                                            <!-- Tombol Aktifkan TA (Khusus Operator Yayasan) -->
                                            <form method="POST" action="<?= $baseUrl ?>/master/academic-years/switch-active" onsubmit="return confirm('PENTING: Pergantian tahun ajaran memerlukan persetujuan dari 100% unit sekolah. Lanjutkan pengaktifan?')" style="width: 100%;">
                                                <?= CSRF::inputField() ?>
                                                <input type="hidden" name="target_ta_id" value="<?= $ta['id'] ?>">
                                                <button type="submit" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; justify-content: center; gap: 4px; font-size: 0.75rem; padding: 4px 8px; width: 100%;">
                                                    <i class="fa-solid fa-play"></i> Aktifkan
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($isOperatorYayasan && !$ta['is_active']): ?>
                                            <?php if (!$isCold): ?>
                                                <!-- Tombol Pindahkan ke Cold Storage NAS (Khusus Operator Yayasan) -->
                                                <button type="button" class="btn btn-outline btn-sm" onclick="openStorageTransferModal(<?= $ta['id'] ?>, '<?= SecurityHelper::escape($ta['name']) ?> (<?= SecurityHelper::escape($ta['semester']) ?>)', 'to_cold')" style="display: inline-flex; align-items: center; justify-content: center; gap: 4px; font-size: 0.75rem; padding: 4px 8px; color: #0284c7; border-color: #0284c7; background: #f0f9ff; width: 100%;" title="Arsipkan ke Cold Storage NAS Lokal (Copy-Verify Aman &amp; Progress Bar)">
                                                    <i class="fa-solid fa-snowflake"></i> Arsip ke NAS
                                                </button>
                                            <?php else: ?>
                                                <!-- Tombol Unduh Snapshot & Rehydrate (Khusus Operator Yayasan) -->
                                                <div style="display: flex; gap: 4px; width: 100%;">
                                                    <a href="<?= $baseUrl ?>/master/academic-years/download-db-archive?id=<?= $ta['id'] ?>" class="btn btn-outline btn-sm" style="display: inline-flex; align-items: center; justify-content: center; gap: 3px; font-size: 0.7rem; padding: 4px 6px; flex: 1; border-color: #64748b; color: #475569;" title="Unduh Snapshot Database SQL dari NAS">
                                                        <i class="fa-solid fa-database"></i> SQL
                                                    </a>
                                                    <button type="button" class="btn btn-outline btn-sm" onclick="openStorageTransferModal(<?= $ta['id'] ?>, '<?= SecurityHelper::escape($ta['name']) ?> (<?= SecurityHelper::escape($ta['semester']) ?>)', 'to_hot')" style="display: inline-flex; align-items: center; justify-content: center; gap: 3px; font-size: 0.7rem; padding: 4px 6px; flex: 1; color: #d97706; border-color: #f59e0b; background: #fffbeb;" title="Kembalikan ke Hosting (Hot Storage)">
                                                        <i class="fa-solid fa-fire"></i> Balik Hot
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        <?php elseif ($ta['is_active']): ?>
                                            <span class="badge badge-success" style="font-size: 0.6875rem; padding: 3px 6px;"><i class="fa-solid fa-lock"></i> Aktif Berjalan</span>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted, #64748b); font-size: 0.85rem;">-</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Status Persetujuan Antar Unit (Fokus Satuan Unit Sendiri untuk Operator Sekolah) -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title" style="margin: 0; font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-signature" style="color: #0284c7;"></i> <?= $isOperatorSekolah ? 'Status Persetujuan Unit Anda' : 'Status Persetujuan Unit Sekolah' ?>
            </h3>
            <span class="badge badge-secondary"><?= $isOperatorSekolah ? count($filteredApprovals) . ' Pengajuan Unit Anda' : count($approvals ?? []) . ' Keputusan' ?></span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tahun Ajaran</th>
                            <?php if ($isOperatorYayasan): ?>
                                <th>Unit Sekolah</th>
                            <?php endif; ?>
                            <th style="width: 120px; text-align: center;">Keputusan</th>
                            <th style="width: 140px; text-align: center;">Aksi Unit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($filteredApprovals)): ?>
                            <tr>
                                <td colspan="<?= $isOperatorYayasan ? '4' : '3' ?>" style="text-align: center; padding: 32px 20px; color: var(--text-muted, #64748b);">
                                    <i class="fa-solid fa-clipboard-check" style="font-size: 1.75rem; color: #cbd5e1; display: block; margin-bottom: 8px;"></i>
                                    Belum ada pengajuan transisi tahun ajaran untuk unit Anda.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($filteredApprovals as $ap): 
                                $isMyUnit = $isOperatorSekolah || ((int)$ap['unit_id'] === (int)$userUnitId);
                            ?>
                                <tr style="<?= $isMyUnit && $isOperatorYayasan ? 'background: #f0fdf4;' : '' ?>">
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-main, #0f172a); font-size: 0.875rem;">
                                            <?= SecurityHelper::escape($ap['ta_name']) ?>
                                        </div>
                                        <small style="color: var(--text-muted, #64748b);">(Semester <?= SecurityHelper::escape($ap['semester']) ?>)</small>
                                    </td>
                                    <?php if ($isOperatorYayasan): ?>
                                        <td>
                                            <strong><?= SecurityHelper::escape($ap['unit_name']) ?></strong>
                                            <?php if ((int)$ap['unit_id'] === (int)$userUnitId): ?>
                                                <span class="badge badge-info" style="font-size: 0.6875rem; margin-left: 4px;">Unit Saya</span>
                                            <?php endif; ?>
                                            <?php if (!empty($ap['rejection_reason'])): ?>
                                                <div style="font-size: 0.75rem; color: #ef4444; margin-top: 4px; line-height: 1.3;">
                                                    <i class="fa-solid fa-circle-exclamation"></i> <?= SecurityHelper::escape($ap['rejection_reason']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                    <td style="text-align: center;">
                                        <?php if ($ap['status'] === 'Setuju'): ?>
                                            <span class="badge badge-success" style="display: inline-flex; align-items: center; gap: 4px;">
                                                <i class="fa-solid fa-check"></i> Setuju
                                            </span>
                                        <?php elseif ($ap['status'] === 'Tidak Setuju'): ?>
                                            <span class="badge badge-danger" style="display: inline-flex; align-items: center; gap: 4px; background: #fee2e2; color: #ef4444;">
                                                <i class="fa-solid fa-xmark"></i> Ditolak
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-warning" style="display: inline-flex; align-items: center; gap: 4px;">
                                                <i class="fa-solid fa-hourglass-half"></i> Menunggu
                                            </span>
                                        <?php endif; ?>

                                        <?php if ($isOperatorSekolah && !empty($ap['rejection_reason'])): ?>
                                            <div style="font-size: 0.72rem; color: #ef4444; margin-top: 4px; line-height: 1.3; text-align: left;">
                                                <i class="fa-solid fa-circle-exclamation"></i> <?= SecurityHelper::escape($ap['rejection_reason']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($isMyUnit): ?>
                                            <?php if ($ap['status'] === 'Menunggu'): ?>
                                                <!-- Tombol Cepat: Setujui & Tolak -->
                                                <div style="display: flex; gap: 4px; justify-content: center;">
                                                    <form method="POST" action="<?= $baseUrl ?>/master/academic-years/approve" onsubmit="return confirm('Setujui Tahun Ajaran Baru?')">
                                                        <?= CSRF::inputField() ?>
                                                        <input type="hidden" name="approval_id" value="<?= (int)$ap['id'] ?>">
                                                        <input type="hidden" name="status" value="Setuju">
                                                        <button type="submit" class="btn btn-sm btn-primary" style="background: #10b981; border-color: #10b981; padding: 4px 8px; font-size: 0.75rem;" title="Setujui">
                                                            <i class="fa-solid fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-outline" onclick="openRejectModal(<?= (int)$ap['id'] ?>, '<?= SecurityHelper::escape($ap['ta_name']) ?>')" style="border-color: #ef4444; color: #ef4444; padding: 4px 8px; font-size: 0.75rem;" title="Tolak dengan Alasan">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-outline btn-sm" onclick='openApprovalModal(<?= htmlspecialchars(json_encode($ap), ENT_QUOTES, 'UTF-8') ?>)' style="padding: 4px 8px; font-size: 0.75rem;">
                                                    <i class="fa-solid fa-pen-to-square"></i> Ubah Respon
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted, #64748b); font-size: 0.85rem;">-</span>
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
</div>

<?php if ($isOperatorYayasan): ?>
<!-- Modal Ajukan TA Baru (Operator Yayasan) -->
<div class="modal-overlay" id="taModal">
    <div class="modal-container" style="max-width: 440px;">
        <form method="POST" action="<?= $baseUrl ?>/master/academic-years/save">
            <?= CSRF::inputField() ?>
            <div class="modal-header">
                <h3 class="card-title" style="margin: 0; font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-calendar-plus" style="color: #0d9488;"></i> Ajukan Tahun Ajaran Baru
                </h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeTaModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Tahun Ajaran <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="name" class="form-control" required placeholder="Contoh: 2025/2026">
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Semester <span style="color: #ef4444;">*</span></label>
                    <select name="semester" class="form-control" required>
                        <option value="Ganjil">Semester Ganjil</option>
                        <option value="Genap">Semester Genap</option>
                    </select>
                </div>
                <div class="alert alert-info" style="font-size: 0.8125rem; line-height: 1.4; background: #f0f9ff; border: 1px solid #bae6fd; padding: 12px; border-radius: 6px; color: #0369a1;">
                    <?php 
                    $uCount = count($units ?? []);
                    $uNames = !empty($units) ? implode(', ', array_map(function($u) {
                        return preg_replace('/ Islam Al Kahfi Batam| ALKAHFI ISLAMIC SCHOOL/i', '', $u['name']);
                    }, $units)) : 'seluruh unit';
                    ?>
                    <i class="fa-solid fa-circle-info"></i> Pengajuan ini otomatis mengirim permintaan persetujuan ke seluruh <strong><?= $uCount ?> satuan unit sekolah</strong> (<?= SecurityHelper::escape($uNames) ?>).
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeTaModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-paper-plane"></i> Ajukan ke Seluruh Unit
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Modal Tolak dengan Alasan (Operator Sekolah) -->
<div class="modal-overlay" id="rejectModal">
    <div class="modal-container" style="max-width: 480px;">
        <form method="POST" action="<?= $baseUrl ?>/master/academic-years/approve">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="approval_id" id="reject_approval_id">
            <input type="hidden" name="status" value="Tidak Setuju">
            
            <div class="modal-header">
                <h3 class="card-title" style="margin: 0; font-size: 1.05rem; display: flex; align-items: center; gap: 8px; color: #ef4444;">
                    <i class="fa-solid fa-circle-xmark"></i> Tolak Transisi Tahun Ajaran
                </h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeRejectModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p id="reject_ta_label" style="font-size: 0.875rem; color: var(--text-main, #0f172a); margin: 0 0 14px 0; font-weight: 500;">
                    Anda akan menolak pengajuan transisi tahun ajaran baru.
                </p>
                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">
                        Alasan Penolakan / Kendala Unit <span style="color: #ef4444;">*</span>
                    </label>
                    <textarea name="rejection_reason" id="reject_reason_text" class="form-control" rows="4" placeholder="Jelaskan secara spesifik alasan unit Anda belum siap (misal: Rapor santri belum tuntas, input ledger masih berjalan, kelulusan belum final)..." required></textarea>
                </div>
                <div style="font-size: 0.75rem; color: #ef4444;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Alasan ini akan langsung terbaca oleh Operator Yayasan &amp; Direktur Pendidikan.
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeRejectModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="background: #ef4444; border-color: #ef4444; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-paper-plane"></i> Kirim Penolakan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Ubah Respon (Operator Sekolah) -->
<div class="modal-overlay" id="approvalModal">
    <div class="modal-container" style="max-width: 480px;">
        <form method="POST" action="<?= $baseUrl ?>/master/academic-years/approve">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="approval_id" id="modal_approval_id">
            
            <div class="modal-header">
                <h3 class="card-title" style="margin: 0; font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-pen-to-square" style="color: #0d9488;"></i> Ubah Keputusan Persetujuan TA
                </h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeApprovalModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Keputusan Unit <span style="color: #ef4444;">*</span></label>
                    <select name="status" id="approval_status" class="form-control" onchange="toggleRejectionReason(this.value)">
                        <option value="Setuju">Setuju (Unit telah siap untuk transisi)</option>
                        <option value="Tidak Setuju">Tidak Setuju (Ada kendala / belum siap)</option>
                    </select>
                </div>
                <div class="form-group" id="rejectionReasonBox" style="display: none; margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Alasan Penolakan (Wajib Diisi)</label>
                    <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="3" placeholder="Jelaskan alasan mengapa belum siap pindah tahun ajaran..."></textarea>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeApprovalModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($isOperatorYayasan): 
    $coldSettings = \Core\Storage\StorageManager::getConfig()['cold'] ?? [];
    $isColdOn = !empty($coldSettings['enabled']);
    $curDriver = $coldSettings['driver'] ?? 'local_path';
    $curLocalPath = $coldSettings['local_path'] ?? '';
    $curWebdavUrl = $coldSettings['webdav']['base_url'] ?? 'http://192.168.1.100:5005';
    $curWebdavPath = $coldSettings['webdav']['path'] ?? '/akfi_cold_storage';
    $curWebdavUser = $coldSettings['webdav']['username'] ?? 'admin_nas';
?>
<!-- Modal Konfigurasi Cold Storage NAS Lokal -->
<div class="modal-overlay" id="nasModal">
    <div class="modal-container" style="max-width: 600px;">
        <form method="POST" action="<?= $baseUrl ?>/master/storage/save-nas-config" id="nasConfigForm">
            <?= CSRF::inputField() ?>
            <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 20px;">
                <h3 class="card-title" style="margin: 0; font-size: 1.1rem; display: flex; align-items: center; gap: 8px; color: #0284c7;">
                    <i class="fa-solid fa-server"></i> Konfigurasi Cold Storage NAS Lokal
                </h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeNasModal()">&times;</button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <div class="alert alert-info" style="font-size: 0.8125rem; line-height: 1.4; background: #f0f9ff; border: 1px solid #bae6fd; padding: 12px; border-radius: 6px; color: #0369a1; margin-bottom: 16px;">
                    <i class="fa-solid fa-circle-info"></i> Cold Storage digunakan untuk mengarsipkan seluruh dokumen/berkas dan snapshot database dari tahun ajaran yang telah ditutup/berlalu ke NAS lokal yayasan agar menghemat kapasitas hosting. Berkas tetap dapat diunduh kapan saja secara otomatis dari NAS.
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; cursor: pointer;">
                        <input type="checkbox" name="storage_cold_enabled" value="1" <?= $isColdOn ? 'checked' : '' ?> style="width: 16px; height: 16px;">
                        <span>Aktifkan Integrasi Cold Storage (NAS Lokal Sekolah)</span>
                    </label>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Metode / Driver Koneksi NAS <span style="color: #ef4444;">*</span></label>
                    <select name="storage_cold_driver" id="storage_cold_driver" class="form-control" onchange="toggleNasDriverFields(this.value)">
                        <option value="local_path" <?= $curDriver === 'local_path' ? 'selected' : '' ?>>Direktori Lokal / Mount Drive / Samba UNC Share (\\IP\share)</option>
                        <option value="webdav" <?= $curDriver === 'webdav' ? 'selected' : '' ?>>WebDAV Remote (Synology DSM / TrueNAS / QNAP / NextCloud)</option>
                    </select>
                </div>

                <!-- Driver: Local Path / UNC Share -->
                <div id="nasLocalPathBox" style="display: <?= $curDriver === 'local_path' ? 'block' : 'none' ?>; margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">
                        Path Folder NAS Lokal / Network Share <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" name="storage_cold_local_path" id="storage_cold_local_path" class="form-control" value="<?= SecurityHelper::escape($curLocalPath) ?>" placeholder="Contoh: \\192.168.1.100\akfi_cold atau Z:\akfi_cold atau /mnt/nas/akfi_cold">
                    <small style="color: #64748b; font-size: 0.75rem; display: block; margin-top: 4px;">
                        Dapat berupa folder lokal server, mapped drive Windows (<code>Z:\...</code>), path UNC Windows (<code>\\192.168.1.50\arsip_nas</code>), atau mount point Linux (<code>/mnt/nas/...</code>).
                    </small>
                </div>

                <!-- Driver: WebDAV -->
                <div id="nasWebdavBox" style="display: <?= $curDriver === 'webdav' ? 'block' : 'none' ?>; margin-bottom: 16px;">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">URL WebDAV NAS</label>
                        <input type="text" name="storage_cold_webdav_url" class="form-control" value="<?= SecurityHelper::escape($curWebdavUrl) ?>" placeholder="Contoh: http://192.168.1.100:5005 atau https://nas.sekolah.id:5006">
                    </div>
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Path Folder di NAS</label>
                        <input type="text" name="storage_cold_webdav_path" class="form-control" value="<?= SecurityHelper::escape($curWebdavPath) ?>" placeholder="/akfi_cold_storage">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Username NAS</label>
                            <input type="text" name="storage_cold_webdav_user" class="form-control" value="<?= SecurityHelper::escape($curWebdavUser) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Password NAS</label>
                            <input type="password" name="storage_cold_webdav_pass" class="form-control" placeholder="Kosongkan jika tidak diubah">
                        </div>
                    </div>
                </div>

                <!-- Hasil Uji Koneksi -->
                <div id="nasTestResultBox" style="display: none; padding: 10px 14px; border-radius: 6px; font-size: 0.8125rem; margin-top: 10px;"></div>
            </div>
            <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center;">
                <button type="button" class="btn btn-outline" id="btnTestNasConnection" onclick="testNasInModal()" style="display: inline-flex; align-items: center; gap: 6px; border-color: #0284c7; color: #0284c7;">
                    <i class="fa-solid fa-plug"></i> Uji Koneksi Sekarang
                </button>
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="btn btn-outline" onclick="closeNasModal()">Batal</button>
                    <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Konfigurasi
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if ($isOperatorYayasan): ?>
<!-- Modal Transfer Storage Aman dengan Real-time Progress Bar (Copy -> Verifikasi -> Hapus) -->
<div class="modal-overlay" id="storageTransferModal">
    <div class="modal-container" style="max-width: 600px; width: 95%;">
        <div class="modal-header" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 16px 20px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div id="transferModalIcon" style="width: 38px; height: 38px; border-radius: 10px; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; font-size: 1.15rem; color: #38bdf8;">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </div>
                <div>
                    <h3 class="card-title" id="transferModalTitle" style="margin: 0; color: #ffffff; font-size: 1.05rem; font-weight: 700;">
                        Perpindahan Data Storage Aman
                    </h3>
                    <div id="transferModalSub" style="font-size: 0.75rem; color: #94a3b8; margin-top: 2px;">
                        Tahun Ajaran: <span id="transferTaName" style="color: #f8fafc; font-weight: 600;">-</span>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-outline btn-sm" onclick="closeStorageTransferModal()" id="btnTransferClose" style="color: #cbd5e1; border-color: rgba(255,255,255,0.2); font-size: 16px;">&times;</button>
        </div>

        <div class="modal-body" style="padding: 20px;">
            <!-- Jaminan Keamanan: Copy-Verify-Delete -->
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px 14px; margin-bottom: 16px; display: flex; align-items: flex-start; gap: 10px;">
                <i class="fa-solid fa-shield-halved" style="color: #16a34a; font-size: 1.3rem; margin-top: 2px;"></i>
                <div style="font-size: 0.8125rem; color: #166534; line-height: 1.45;">
                    <strong>Jaminan Keamanan Berkas (Copy &amp; Verifikasi Integritas):</strong>
                    <br>Proses menggunakan metode <em>Copy Data</em> terlebih dahulu. Berkas sumber <strong>hanya dihapus jika salinan di tujuan telah 100% terverifikasi utuh</strong>. Jika terjadi kegagalan atau koneksi putus, berkas sumber tetap utuh dan selamat.
                </div>
            </div>

            <!-- Direction Card -->
            <div style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border: 1px solid var(--border-color, #e2e8f0); border-radius: 8px; padding: 12px 16px; margin-bottom: 16px;">
                <div style="text-align: left;">
                    <div style="font-size: 0.7rem; color: var(--text-muted, #64748b); text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Asal (Sumber)</div>
                    <div id="transferSourceLabel" style="font-size: 0.875rem; font-weight: 700; color: #b45309; display: flex; align-items: center; gap: 6px; margin-top: 2px;">
                        <i class="fa-solid fa-fire"></i> Hot Storage (Hosting)
                    </div>
                </div>
                <div style="color: #0284c7; font-size: 1.25rem; padding: 0 10px;">
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 0.7rem; color: var(--text-muted, #64748b); text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Tujuan</div>
                    <div id="transferDestLabel" style="font-size: 0.875rem; font-weight: 700; color: #0369a1; display: flex; align-items: center; gap: 6px; margin-top: 2px; justify-content: flex-end;">
                        <i class="fa-solid fa-snowflake"></i> Cold Storage (NAS)
                    </div>
                </div>
            </div>

            <!-- Progress Bar Section -->
            <div id="transferProgressSection" style="margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 6px;">
                    <div>
                        <span id="transferStatusBadge" class="badge badge-info" style="font-size: 0.75rem;">Siap Memulai</span>
                        <span id="transferFileCounter" style="font-size: 0.8125rem; color: var(--text-muted, #64748b); margin-left: 8px;">0 / 0 Berkas</span>
                    </div>
                    <div style="font-size: 1.35rem; font-weight: 800; color: #0284c7;" id="transferPercentText">0%</div>
                </div>

                <div style="height: 16px; background: #e2e8f0; border-radius: 9999px; overflow: hidden; position: relative; box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);">
                    <div id="transferProgressBar" style="height: 100%; width: 0%; background: linear-gradient(90deg, #0284c7 0%, #0d9488 100%); border-radius: 9999px; transition: width 0.25s ease;"></div>
                </div>

                <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--text-muted, #64748b); margin-top: 6px;">
                    <span id="transferByteProgress">0 B / 0 B</span>
                    <span id="transferCurrentAction" style="font-weight: 500;">Menunggu konfirmasi...</span>
                </div>
            </div>

            <!-- Terminal Log Box -->
            <div style="margin-bottom: 8px;">
                <label style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted, #64748b); margin-bottom: 4px; display: block;">
                    <i class="fa-solid fa-terminal"></i> Log Aktivitas Perpindahan:
                </label>
                <div id="transferLogConsole" style="background: #0f172a; color: #f8fafc; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.75rem; padding: 12px; border-radius: 8px; height: 130px; overflow-y: auto; line-height: 1.5; border: 1px solid #334155;">
                    <div style="color: #94a3b8;">[Info] Klik tombol "Mulai Pindahkan Data" di bawah untuk memulai.</div>
                </div>
            </div>

            <!-- Alert info box -->
            <div id="transferAlertBox" style="display: none; padding: 12px; border-radius: 8px; font-size: 0.8125rem; margin-top: 10px;"></div>
        </div>

        <div class="modal-footer" style="padding: 14px 20px; background: #f8fafc; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border-color, #e2e8f0);">
            <button type="button" class="btn btn-outline" id="btnTransferCancel" onclick="closeStorageTransferModal()">Batal</button>
            <button type="button" class="btn btn-primary" id="btnTransferStart" onclick="executeStorageTransfer()" style="display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-play"></i> Mulai Pindahkan Data
            </button>
            <button type="button" class="btn btn-success" id="btnTransferReload" onclick="location.reload()" style="display: none; align-items: center; gap: 6px;">
                <i class="fa-solid fa-check"></i> Selesai &amp; Muat Ulang
            </button>
        </div>
    </div>
</div>

<script>
let currentTransfer = {
    taId: 0,
    taName: '',
    direction: 'to_cold',
    files: [],
    currentIndex: 0,
    totalFiles: 0,
    totalBytes: 0,
    transferredBytes: 0,
    errorsCount: 0,
    isRunning: false
};

function openStorageTransferModal(taId, taName, direction) {
    currentTransfer.taId = taId;
    currentTransfer.taName = taName;
    currentTransfer.direction = direction;
    currentTransfer.files = [];
    currentTransfer.currentIndex = 0;
    currentTransfer.totalFiles = 0;
    currentTransfer.totalBytes = 0;
    currentTransfer.transferredBytes = 0;
    currentTransfer.errorsCount = 0;
    currentTransfer.isRunning = false;

    const isToCold = (direction === 'to_cold');
    document.getElementById('transferTaName').innerText = taName;
    document.getElementById('transferModalTitle').innerText = isToCold 
        ? 'Arsipkan ke Cold Storage NAS (Metode Aman)' 
        : 'Kembalikan (Rehydrate) ke Hot Storage (Metode Aman)';
    
    document.getElementById('transferSourceLabel').innerHTML = isToCold 
        ? '<i class="fa-solid fa-fire"></i> Hot Storage (Hosting)' 
        : '<i class="fa-solid fa-snowflake"></i> Cold Storage (NAS)';
    document.getElementById('transferSourceLabel').style.color = isToCold ? '#b45309' : '#0369a1';

    document.getElementById('transferDestLabel').innerHTML = isToCold 
        ? '<i class="fa-solid fa-snowflake"></i> Cold Storage (NAS)' 
        : '<i class="fa-solid fa-fire"></i> Hot Storage (Hosting)';
    document.getElementById('transferDestLabel').style.color = isToCold ? '#0369a1' : '#b45309';

    document.getElementById('transferPercentText').innerText = '0%';
    document.getElementById('transferProgressBar').style.width = '0%';
    document.getElementById('transferProgressBar').style.background = isToCold 
        ? 'linear-gradient(90deg, #0284c7 0%, #0d9488 100%)' 
        : 'linear-gradient(90deg, #d97706 0%, #ea580c 100%)';

    document.getElementById('transferStatusBadge').className = 'badge badge-info';
    document.getElementById('transferStatusBadge').innerText = 'Siap Memulai';
    document.getElementById('transferFileCounter').innerText = '0 / 0 Berkas';
    document.getElementById('transferByteProgress').innerText = '0 B / 0 B';
    document.getElementById('transferCurrentAction').innerText = 'Menunggu instruksi...';

    const logBox = document.getElementById('transferLogConsole');
    logBox.innerHTML = '<div style="color: #94a3b8;">[Info] Menyiapkan ' + (isToCold ? 'pengarsipan ke Cold NAS' : 'pemulihan ke Hosting') + ' untuk ' + taName + '...</div>';

    document.getElementById('transferAlertBox').style.display = 'none';
    document.getElementById('btnTransferStart').style.display = 'inline-flex';
    document.getElementById('btnTransferStart').disabled = false;
    document.getElementById('btnTransferCancel').style.display = 'inline-flex';
    document.getElementById('btnTransferClose').style.display = 'inline-block';
    document.getElementById('btnTransferReload').style.display = 'none';

    document.getElementById('storageTransferModal').classList.add('active');
}

function closeStorageTransferModal() {
    if (currentTransfer.isRunning) {
        if (!confirm('Proses transfer data sedang berjalan. Berkas yang sedang disalin akan diselesaikan secara aman. Yakin ingin menutup jendela?')) {
            return;
        }
    }
    document.getElementById('storageTransferModal').classList.remove('active');
}

function appendTransferLog(msg, color = '#f8fafc') {
    const logBox = document.getElementById('transferLogConsole');
    if (!logBox) return;
    const time = new Date().toLocaleTimeString('id-ID', { hour12: false });
    const line = document.createElement('div');
    line.style.color = color;
    line.innerHTML = `<span style="color: #64748b;">[${time}]</span> ${msg}`;
    logBox.appendChild(line);
    logBox.scrollTop = logBox.scrollHeight;
}

function updateTransferProgress(percent, label) {
    percent = Math.min(100, Math.max(0, Math.round(percent)));
    document.getElementById('transferPercentText').innerText = percent + '%';
    document.getElementById('transferProgressBar').style.width = percent + '%';
    if (label) {
        document.getElementById('transferCurrentAction').innerText = label;
    }
}

function executeStorageTransfer() {
    currentTransfer.isRunning = true;
    document.getElementById('btnTransferStart').disabled = true;
    document.getElementById('btnTransferCancel').style.display = 'none';
    document.getElementById('btnTransferClose').style.display = 'none';

    document.getElementById('transferStatusBadge').className = 'badge badge-warning';
    document.getElementById('transferStatusBadge').innerText = 'Memeriksa Berkas...';
    appendTransferLog('Menghubungi server dan memeriksa kesiapan NAS...', '#38bdf8');

    fetch('<?= $baseUrl ?>/master/storage/transfer-init', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': '<?= CSRF::getToken() ?>',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            ta_id: currentTransfer.taId,
            direction: currentTransfer.direction
        })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || 'Gagal memulai proses transfer.');
        }

        currentTransfer.files = data.files || [];
        currentTransfer.totalFiles = data.total_files || 0;
        currentTransfer.totalBytes = data.total_bytes || 0;
        currentTransfer.currentIndex = 0;

        document.getElementById('transferFileCounter').innerText = `0 / ${currentTransfer.totalFiles} Berkas`;
        document.getElementById('transferByteProgress').innerText = `0 B / ${data.total_mb} MB`;
        
        appendTransferLog(`Ditemukan ${currentTransfer.totalFiles} berkas fisik (${data.total_mb} MB) untuk diproses.`, '#4ade80');

        if (currentTransfer.totalFiles === 0) {
            appendTransferLog('Tidak ada berkas fisik yang perlu dipindahkan. Menyelesaikan proses...', '#94a3b8');
            finalizeStorageTransfer();
        } else {
            document.getElementById('transferStatusBadge').className = 'badge badge-primary';
            document.getElementById('transferStatusBadge').innerText = 'Menyalin & Verifikasi...';
            processNextTransferFile();
        }
    })
    .catch(err => {
        currentTransfer.isRunning = false;
        document.getElementById('btnTransferCancel').style.display = 'inline-flex';
        document.getElementById('btnTransferClose').style.display = 'inline-block';
        document.getElementById('transferStatusBadge').className = 'badge badge-danger';
        document.getElementById('transferStatusBadge').innerText = 'Gagal';
        appendTransferLog('ERROR: ' + err.message, '#f87171');
        
        const alertBox = document.getElementById('transferAlertBox');
        alertBox.style.display = 'block';
        alertBox.style.background = '#fee2e2';
        alertBox.style.color = '#991b1b';
        alertBox.style.border = '1px solid #fecaca';
        alertBox.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> <strong>Kendala:</strong> ' + err.message + '<br><small>Catatan: Berkas asal tetap 100% aman tersimpan dan tidak mengalami perubahan.</small>';
    });
}

function processNextTransferFile() {
    if (currentTransfer.currentIndex >= currentTransfer.totalFiles) {
        finalizeStorageTransfer();
        return;
    }

    const file = currentTransfer.files[currentTransfer.currentIndex];
    const fileNum = currentTransfer.currentIndex + 1;

    appendTransferLog(`[${fileNum}/${currentTransfer.totalFiles}] Salin (Copy) & Verifikasi: ${file.name} (${file.size_fmt})...`, '#e2e8f0');
    updateTransferProgress(
        ((currentTransfer.currentIndex) / currentTransfer.totalFiles) * 90, 
        `Menyalin berkas ${fileNum} dari ${currentTransfer.totalFiles}...`
    );

    fetch('<?= $baseUrl ?>/master/storage/transfer-step', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': '<?= CSRF::getToken() ?>',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            ta_id: currentTransfer.taId,
            direction: currentTransfer.direction,
            file: file
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            currentTransfer.transferredBytes += (res.size || file.size || 0);
            appendTransferLog(`  ✓ Berhasil: Berkas disalin & diverifikasi. Sumber dibersihkan.`, '#4ade80');
        } else {
            currentTransfer.errorsCount++;
            appendTransferLog(`  ✗ Peringatan: ${res.message}`, '#facc15');
        }

        currentTransfer.currentIndex++;
        const pct = Math.round((currentTransfer.currentIndex / currentTransfer.totalFiles) * 90);
        document.getElementById('transferFileCounter').innerText = `${currentTransfer.currentIndex} / ${currentTransfer.totalFiles} Berkas`;
        document.getElementById('transferByteProgress').innerText = `${(currentTransfer.transferredBytes / (1024*1024)).toFixed(2)} MB / ${(currentTransfer.totalBytes / (1024*1024)).toFixed(2)} MB`;
        updateTransferProgress(pct, `Selesai memproses ${currentTransfer.currentIndex} berkas`);

        // Lanjut berkas berikutnya
        setTimeout(processNextTransferFile, 80);
    })
    .catch(err => {
        currentTransfer.errorsCount++;
        appendTransferLog(`  ✗ Error jaringan pada ${file.name}: ${err.message}. Sumber tetap aman!`, '#f87171');
        currentTransfer.currentIndex++;
        setTimeout(processNextTransferFile, 100);
    });
}

function finalizeStorageTransfer() {
    updateTransferProgress(95, 'Membuat snapshot database & finalisasi status...');
    appendTransferLog('Langkah Final: Menyimpan metadata dan memperbarui status tahun ajaran...', '#38bdf8');

    fetch('<?= $baseUrl ?>/master/storage/transfer-finalize', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': '<?= CSRF::getToken() ?>',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            ta_id: currentTransfer.taId,
            direction: currentTransfer.direction,
            stats: {
                files_transferred: currentTransfer.totalFiles - currentTransfer.errorsCount,
                bytes_transferred: currentTransfer.transferredBytes,
                errors_count: currentTransfer.errorsCount
            }
        })
    })
    .then(r => r.json())
    .then(data => {
        currentTransfer.isRunning = false;
        updateTransferProgress(100, 'Selesai 100%!');
        document.getElementById('transferProgressBar').style.background = '#16a34a';
        document.getElementById('transferStatusBadge').className = 'badge badge-success';
        document.getElementById('transferStatusBadge').innerText = 'Selesai 100%';
        appendTransferLog('✓ ' + data.message, '#4ade80');

        const alertBox = document.getElementById('transferAlertBox');
        alertBox.style.display = 'block';
        alertBox.style.background = '#dcfce7';
        alertBox.style.color = '#15803d';
        alertBox.style.border = '1px solid #bbf7d0';
        alertBox.innerHTML = '<i class="fa-solid fa-circle-check"></i> <strong>Sukses:</strong> ' + data.message;

        document.getElementById('btnTransferStart').style.display = 'none';
        document.getElementById('btnTransferReload').style.display = 'inline-flex';
        document.getElementById('btnTransferClose').style.display = 'inline-block';
    })
    .catch(err => {
        currentTransfer.isRunning = false;
        appendTransferLog('Peringatan saat finalisasi: ' + err.message, '#facc15');
        document.getElementById('btnTransferReload').style.display = 'inline-flex';
        document.getElementById('btnTransferClose').style.display = 'inline-block';
    });
}

function openTaModal() {
    document.getElementById('taModal').classList.add('active');
}
function closeTaModal() {
    document.getElementById('taModal').classList.remove('active');
}

function openNasModal() {
    const el = document.getElementById('nasModal');
    if (el) el.classList.add('active');
}
function closeNasModal() {
    const el = document.getElementById('nasModal');
    if (el) el.classList.remove('active');
}

function toggleNasDriverFields(val) {
    const localBox = document.getElementById('nasLocalPathBox');
    const webdavBox = document.getElementById('nasWebdavBox');
    if (localBox) localBox.style.display = (val === 'local_path') ? 'block' : 'none';
    if (webdavBox) webdavBox.style.display = (val === 'webdav') ? 'block' : 'none';
}

function checkNasStatusAjax() {
    const icon = document.getElementById('nasRefreshIcon');
    if (icon) icon.classList.add('fa-spin');
    
    fetch('<?= $baseUrl ?>/master/storage/test-nas', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': '<?= CSRF::getToken() ?>',
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (icon) icon.classList.remove('fa-spin');
        const badge = document.getElementById('nasStatusBadge');
        const desc = document.getElementById('nasStatusDesc');
        if (badge) {
            badge.innerText = data.success ? 'NAS Terhubung' : 'NAS Terputus';
            badge.style.background = data.success ? '#dcfce7' : '#fee2e2';
            badge.style.color = data.success ? '#15803d' : '#b91c1c';
            badge.style.borderColor = data.success ? '#bbf7d0' : '#fecaca';
        }
        if (desc) {
            desc.innerHTML = data.message;
        }
        alert(data.message);
    })
    .catch(err => {
        if (icon) icon.classList.remove('fa-spin');
        alert('Gagal menghubungi server: ' + err);
    });
}

function testNasInModal() {
    const resBox = document.getElementById('nasTestResultBox');
    const btn = document.getElementById('btnTestNasConnection');
    if (btn) btn.disabled = true;
    if (resBox) {
        resBox.style.display = 'block';
        resBox.style.background = '#f0f9ff';
        resBox.style.color = '#0369a1';
        resBox.style.border = '1px solid #bae6fd';
        resBox.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menguji koneksi ke NAS...';
    }

    fetch('<?= $baseUrl ?>/master/storage/test-nas', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': '<?= CSRF::getToken() ?>',
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (btn) btn.disabled = false;
        if (resBox) {
            resBox.style.background = data.success ? '#dcfce7' : '#fee2e2';
            resBox.style.color = data.success ? '#15803d' : '#b91c1c';
            resBox.style.border = data.success ? '1px solid #bbf7d0' : '1px solid #fecaca';
            resBox.innerHTML = (data.success ? '<i class="fa-solid fa-circle-check"></i> ' : '<i class="fa-solid fa-circle-xmark"></i> ') + data.message;
        }
    })
    .catch(err => {
        if (btn) btn.disabled = false;
        if (resBox) {
            resBox.style.background = '#fee2e2';
            resBox.style.color = '#b91c1c';
            resBox.style.border = '1px solid #fecaca';
            resBox.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Terjadi kesalahan koneksi: ' + err;
        }
    });
}
</script>
<?php endif; ?>

<script>
function openRejectModal(approvalId, taName) {
    document.getElementById('reject_approval_id').value = approvalId;
    if (taName) {
        document.getElementById('reject_ta_label').innerText = 'Anda akan menolak pengajuan transisi untuk: ' + taName;
    }
    document.getElementById('reject_reason_text').value = '';
    document.getElementById('rejectModal').classList.add('active');
}
function closeRejectModal() {
    document.getElementById('rejectModal').classList.remove('active');
}

function openApprovalModal(ap) {
    document.getElementById('modal_approval_id').value = ap.id;
    document.getElementById('approval_status').value = ap.status === 'Tidak Setuju' ? 'Tidak Setuju' : 'Setuju';
    document.getElementById('rejection_reason').value = ap.rejection_reason || '';
    toggleRejectionReason(document.getElementById('approval_status').value);
    document.getElementById('approvalModal').classList.add('active');
}
function closeApprovalModal() {
    document.getElementById('approvalModal').classList.remove('active');
}

function toggleRejectionReason(val) {
    const box = document.getElementById('rejectionReasonBox');
    if (box) {
        box.style.display = val === 'Tidak Setuju' ? 'block' : 'none';
        const txt = document.getElementById('rejection_reason');
        if (txt) {
            txt.required = (val === 'Tidak Setuju');
        }
    }
}
</script>
