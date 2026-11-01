<?php
// views/akfi/penilaian_bawahan.php
use Core\SecurityHelper;
use Core\Auth;

$totalStaff = count($assessments);
$countDiajukan = 0;
$countSelesai = 0;
$countDraft = 0;

foreach ($assessments as $a) {
    if ($a['status'] === 'Diajukan') $countDiajukan++;
    elseif ($a['status'] === 'Disetujui_Atasan' || $a['status'] === 'Final') $countSelesai++;
    else $countDraft++;
}

// Helper untuk URL sorting header kolom tabel
$buildSortUrl = function(string $col) use ($sortBy, $sortOrder, $search, $filterStatus, $filterGrade, $filterTemplate) {
    $nextOrder = ($sortBy === $col && $sortOrder === 'desc') ? 'asc' : 'desc';
    if ($col === 'nama') {
        $nextOrder = ($sortBy === $col && $sortOrder === 'asc') ? 'desc' : 'asc';
    }
    $params = [
        'sort_by'    => $col,
        'sort_order' => $nextOrder
    ];
    if (!empty($search)) $params['q'] = $search;
    if (!empty($filterStatus)) $params['status'] = $filterStatus;
    if (!empty($filterGrade)) $params['grade'] = $filterGrade;
    if (!empty($filterTemplate)) $params['template_id'] = $filterTemplate;
    if (!empty($_GET['unit_id'])) $params['unit_id'] = $_GET['unit_id'];
    return Auth::baseUrl() . '/akfi/penilaian-bawahan?' . http_build_query($params);
};

$getSortIcon = function(string $col) use ($sortBy, $sortOrder) {
    if ($sortBy !== $col) return '<i class="fa-solid fa-sort" style="font-size: 10px; opacity: 0.35; margin-left: 4px;"></i>';
    return $sortOrder === 'asc' 
        ? '<i class="fa-solid fa-arrow-up-wide-short" style="font-size: 11px; color: #0284c7; margin-left: 4px;"></i>' 
        : '<i class="fa-solid fa-arrow-down-wide-short" style="font-size: 11px; color: #0284c7; margin-left: 4px;"></i>';
};
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><?= SecurityHelper::escape($pageTitle) ?></h1>
        <div class="page-subtitle">
            Evaluasi capaian sasaran kerja, instrumen indikator, dan rubrik perilaku Islami bawahan untuk tahun ajaran aktif.
        </div>
    </div>
</div>

<!-- 4 KARTU STATISTIK PENILAIAN BAWAHAN -->
<div class="grid-cols-4" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <div class="stat-value"><?= number_format($totalStaff) ?> <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">Pegawai</span></div>
            <div class="stat-label">Total Bawahan Ditampilkan</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber">
            <i class="fa-solid fa-clock"></i>
        </div>
        <div>
            <div class="stat-value" style="color: var(--warning);"><?= number_format($countDiajukan) ?> <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">Pegawai</span></div>
            <div class="stat-label">Siap / Menunggu Dinilai</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon emerald">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div>
            <div class="stat-value" style="color: var(--accent);"><?= number_format($countSelesai) ?> <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">Pegawai</span></div>
            <div class="stat-label">Selesai Dinilai</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon rose">
            <i class="fa-solid fa-file-pen"></i>
        </div>
        <div>
            <div class="stat-value"><?= number_format($countDraft) ?> <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">Pegawai</span></div>
            <div class="stat-label">Draft / Belum Diajukan</div>
        </div>
    </div>
</div>

<!-- DAFTAR PEGAWAI BAWAHAN & MULTI-FILTER TOOLBAR -->
<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <h3 class="card-title" style="margin: 0;">
                <i class="fa-solid fa-user-check" style="color: var(--primary-light); margin-right: 8px;"></i>
                Daftar Penilaian Kinerja AKFI Pegawai
            </h3>
            <span class="badge badge-secondary"><?= $totalStaff ?> Pegawai</span>
        </div>

        <!-- Filter & Sorting Komprehensif Controls -->
        <form method="GET" action="<?= Auth::baseUrl() ?>/akfi/penilaian-bawahan" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap; margin: 0;">
            <?php if (!empty($_GET['unit_id'])): ?>
                <input type="hidden" name="unit_id" value="<?= (int)$_GET['unit_id'] ?>">
            <?php endif; ?>

            <!-- Input Pencarian Nama / NIPY -->
            <div style="position: relative;">
                <input type="text" name="q" value="<?= SecurityHelper::escape($search ?? '') ?>" placeholder="Cari nama / NIPY..." class="form-control" style="font-size: 12px; padding: 6px 10px; width: 150px; border-radius: 6px;">
            </div>

            <!-- Filter Status Penilaian -->
            <select name="status" class="form-select" style="font-size: 12px; padding: 6px 10px; border-radius: 6px; width: 130px;" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="Diajukan" <?= ($filterStatus ?? '') === 'Diajukan' ? 'selected' : '' ?>>Siap Dinilai</option>
                <option value="Disetujui_Atasan" <?= ($filterStatus ?? '') === 'Disetujui_Atasan' ? 'selected' : '' ?>>Disetujui KS</option>
                <option value="Final" <?= ($filterStatus ?? '') === 'Final' ? 'selected' : '' ?>>Final HR</option>
                <option value="Draft" <?= ($filterStatus ?? '') === 'Draft' ? 'selected' : '' ?>>Draft Mandiri</option>
            </select>

            <!-- Filter Predikat (Grade) -->
            <select name="grade" class="form-select" style="font-size: 12px; padding: 6px 10px; border-radius: 6px; width: 125px;" onchange="this.form.submit()">
                <option value="">Semua Predikat</option>
                <option value="A" <?= ($filterGrade ?? '') === 'A' ? 'selected' : '' ?>>Predikat A (Amat Baik)</option>
                <option value="B" <?= ($filterGrade ?? '') === 'B' ? 'selected' : '' ?>>Predikat B (Baik)</option>
                <option value="C" <?= ($filterGrade ?? '') === 'C' ? 'selected' : '' ?>>Predikat C (Cukup)</option>
                <option value="D" <?= ($filterGrade ?? '') === 'D' ? 'selected' : '' ?>>Predikat D (Sedang)</option>
                <option value="E" <?= ($filterGrade ?? '') === 'E' ? 'selected' : '' ?>>Predikat E (Kurang)</option>
            </select>

            <!-- Filter Posisi / Rumpun Template -->
            <?php if (!empty($templates)): ?>
                <select name="template_id" class="form-select" style="font-size: 12px; padding: 6px 10px; border-radius: 6px; max-width: 140px;" onchange="this.form.submit()">
                    <option value="">Semua Posisi</option>
                    <?php foreach ($templates as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= ((int)($filterTemplate ?? 0) === (int)$t['id']) ? 'selected' : '' ?>>
                            <?= SecurityHelper::escape($t['position_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <!-- Dropdown Filter & Urutan Lengkap (Semua Komponen Nilai) -->
            <select name="sort_by" class="form-select" style="font-size: 12px; padding: 6px 10px; border-radius: 6px; min-width: 205px; background-color: #f8fafc; border-color: #cbd5e1; color: #0f172a; font-weight: 600;" onchange="this.form.submit()">
                <option value="">Urutan Default (Status &amp; Total)</option>
                <optgroup label="⭐ Skor Akhir (Total AKFI)">
                    <option value="total" <?= (($sortBy ?? '') === 'total' && ($sortOrder ?? '') === 'desc') ? 'selected' : '' ?>>Total AKFI: Tertinggi (Terbaik)</option>
                    <option value="total_asc" <?= (($sortBy ?? '') === 'total' && ($sortOrder ?? '') === 'asc') ? 'selected' : '' ?>>Total AKFI: Terendah</option>
                </optgroup>
                <optgroup label="🌱 Nilai Sikap &amp; Perilaku">
                    <option value="sikap" <?= (($sortBy ?? '') === 'sikap' && ($sortOrder ?? '') === 'desc') ? 'selected' : '' ?>>Nilai Sikap: Tertinggi (Paling Baik)</option>
                    <option value="sikap_asc" <?= (($sortBy ?? '') === 'sikap' && ($sortOrder ?? '') === 'asc') ? 'selected' : '' ?>>Nilai Sikap: Terendah</option>
                </optgroup>
                <optgroup label="💼 Nilai Kinerja (Tupoksi)">
                    <option value="kinerja" <?= (($sortBy ?? '') === 'kinerja' && ($sortOrder ?? '') === 'desc') ? 'selected' : '' ?>>Nilai Kinerja: Tertinggi</option>
                    <option value="kinerja_asc" <?= (($sortBy ?? '') === 'kinerja' && ($sortOrder ?? '') === 'asc') ? 'selected' : '' ?>>Nilai Kinerja: Terendah</option>
                </optgroup>
                <optgroup label="⏰ Nilai Absensi (Kehadiran)">
                    <option value="absensi" <?= (($sortBy ?? '') === 'absensi' && ($sortOrder ?? '') === 'desc') ? 'selected' : '' ?>>Nilai Absensi: Tertinggi</option>
                    <option value="absensi_asc" <?= (($sortBy ?? '') === 'absensi' && ($sortOrder ?? '') === 'asc') ? 'selected' : '' ?>>Nilai Absensi: Terendah</option>
                </optgroup>
                <optgroup label="👤 Nama Pegawai">
                    <option value="nama" <?= (($sortBy ?? '') === 'nama' && ($sortOrder ?? '') === 'asc') ? 'selected' : '' ?>>Nama: A ke Z</option>
                    <option value="nama_desc" <?= (($sortBy ?? '') === 'nama' && ($sortOrder ?? '') === 'desc') ? 'selected' : '' ?>>Nama: Z ke A</option>
                </optgroup>
            </select>

            <button type="submit" class="btn btn-sm btn-primary" style="padding: 6px 12px;">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
            <?php if (!empty($search) || !empty($filterStatus) || !empty($filterGrade) || !empty($filterTemplate) || !empty($sortBy)): ?>
                <a href="<?= Auth::baseUrl() ?>/akfi/penilaian-bawahan<?= !empty($_GET['unit_id']) ? ('?unit_id=' . (int)$_GET['unit_id']) : '' ?>" class="btn btn-sm btn-outline-danger" style="padding: 6px 10px; border-color: #cbd5e1;" title="Reset Semua Filter">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Active Filter & Sorting Indicator Banner -->
    <?php if (!empty($sortBy) || !empty($filterGrade) || !empty($filterStatus) || !empty($filterTemplate) || !empty($search)): ?>
        <div style="background: #f0fdf4; border-bottom: 1px solid #bbf7d0; padding: 10px 18px; font-size: 12px; color: #166534; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <i class="fa-solid fa-filter-circle-check" style="color: #16a34a; font-size: 13px;"></i>
                <strong>Filter Aktif:</strong>
                <?php if (!empty($sortBy)): ?>
                    <span class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac;">
                        Urutan: <?= match($sortBy) {
                            'total'   => 'Skor Total AKFI (' . ($sortOrder === 'desc' ? 'Tertinggi/Terbaik' : 'Terendah') . ')',
                            'sikap'   => 'Nilai Sikap (' . ($sortOrder === 'desc' ? 'Tertinggi/Paling Baik' : 'Terendah') . ')',
                            'kinerja' => 'Nilai Kinerja (' . ($sortOrder === 'desc' ? 'Tertinggi' : 'Terendah') . ')',
                            'absensi' => 'Nilai Absensi (' . ($sortOrder === 'desc' ? 'Tertinggi' : 'Terendah') . ')',
                            'nama'    => 'Nama Pegawai (' . ($sortOrder === 'asc' ? 'A-Z' : 'Z-A') . ')',
                            default   => $sortBy
                        } ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($filterGrade)): ?>
                    <span class="badge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">
                        Predikat: <?= SecurityHelper::escape($filterGrade) ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($filterStatus)): ?>
                    <span class="badge" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a;">
                        Status: <?= SecurityHelper::escape($filterStatus) ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($search)): ?>
                    <span class="badge" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">
                        Cari: "<?= SecurityHelper::escape($search) ?>"
                    </span>
                <?php endif; ?>
            </div>
            <a href="<?= Auth::baseUrl() ?>/akfi/penilaian-bawahan<?= !empty($_GET['unit_id']) ? ('?unit_id=' . (int)$_GET['unit_id']) : '' ?>" style="color: #dc2626; font-weight: 600; text-decoration: underline; font-size: 11.5px;">
                <i class="fa-solid fa-xmark"></i> Hapus Filter
            </a>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">No</th>
                    <th style="cursor: pointer; <?= $sortBy === 'nama' ? 'background: #eff6ff;' : '' ?>">
                        <a href="<?= $buildSortUrl('nama') ?>" style="color: inherit; text-decoration: none; display: inline-flex; align-items: center;" title="Klik untuk mengurutkan berdasarkan nama">
                            Pegawai <?= $getSortIcon('nama') ?>
                        </a>
                    </th>
                    <th>Unit &amp; Posisi</th>
                    <th style="text-align: center;">Status Pengajuan</th>
                    <th style="text-align: center; cursor: pointer; <?= $sortBy === 'kinerja' ? 'background: #eff6ff;' : '' ?>">
                        <a href="<?= $buildSortUrl('kinerja') ?>" style="color: inherit; text-decoration: none; display: inline-flex; align-items: center;" title="Klik untuk mengurutkan berdasarkan nilai kinerja">
                            Kinerja <?= $getSortIcon('kinerja') ?>
                        </a>
                    </th>
                    <th style="text-align: center; cursor: pointer; <?= $sortBy === 'sikap' ? 'background: #f0fdf4;' : '' ?>">
                        <a href="<?= $buildSortUrl('sikap') ?>" style="color: #166534; text-decoration: none; display: inline-flex; align-items: center;" title="Klik untuk mengurutkan berdasarkan nilai sikap">
                            Sikap <?= $getSortIcon('sikap') ?>
                        </a>
                    </th>
                    <th style="text-align: center; cursor: pointer; <?= $sortBy === 'absensi' ? 'background: #eff6ff;' : '' ?>">
                        <a href="<?= $buildSortUrl('absensi') ?>" style="color: inherit; text-decoration: none; display: inline-flex; align-items: center;" title="Klik untuk mengurutkan berdasarkan nilai absensi">
                            Absensi <?= $getSortIcon('absensi') ?>
                        </a>
                    </th>
                    <th style="text-align: center; cursor: pointer; <?= $sortBy === 'total' ? 'background: #eff6ff;' : '' ?>">
                        <a href="<?= $buildSortUrl('total') ?>" style="color: inherit; text-decoration: none; display: inline-flex; align-items: center;" title="Klik untuk mengurutkan berdasarkan skor total akhir">
                            Total &amp; Predikat <?= $getSortIcon('total') ?>
                        </a>
                    </th>
                    <th style="width: 140px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($assessments)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px 20px; color: var(--text-muted);">
                            <i class="fa-solid fa-users-slash" style="font-size: 32px; margin-bottom: 10px; display: block; opacity: 0.4;"></i>
                            Tidak ada data penilaian pegawai yang sesuai dengan kriteria filter saat ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($assessments as $idx => $a): ?>
                        <tr>
                            <td style="text-align: center; color: var(--text-muted);"><?= $idx + 1 ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 36px; height: 36px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0;">
                                        <?= strtoupper(mb_substr($a['nama'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; color: var(--text-main);">
                                            <?= SecurityHelper::escape($a['nama']) ?>
                                        </div>
                                        <div style="font-size: 11px; color: var(--text-muted);">
                                            NIPY: <?= SecurityHelper::escape($a['nipy'] ?? '-') ?> &bull; <?= SecurityHelper::escape($a['position'] ?? 'Guru/Staf') ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 500;"><?= SecurityHelper::escape($a['unit_name']) ?></div>
                                <span class="badge badge-secondary" style="font-size: 10px; margin-top: 2px;">
                                    <?= SecurityHelper::escape($a['position_name']) ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($a['status'] === 'Final'): ?>
                                    <span class="badge badge-success">
                                        <i class="fa-solid fa-check-double" style="margin-right: 4px;"></i> Final HR
                                    </span>
                                <?php elseif ($a['status'] === 'Disetujui_Atasan'): ?>
                                    <span class="badge badge-info">
                                        <i class="fa-solid fa-check" style="margin-right: 4px;"></i> Disetujui
                                    </span>
                                <?php elseif ($a['status'] === 'Diajukan'): ?>
                                    <span class="badge badge-warning">
                                        <i class="fa-solid fa-bell" style="margin-right: 4px;"></i> Siap Dinilai
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Draft Mandiri</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center; font-family: monospace; font-size: 13px; font-weight: 600; <?= $sortBy === 'kinerja' ? 'background: #f8fafc;' : '' ?>">
                                <?= number_format((float)($a['kinerja_score'] ?? 0), 1) ?>
                            </td>
                            <td style="text-align: center; font-family: monospace; font-size: 13px; font-weight: 600; <?= $sortBy === 'sikap' ? 'background: #f0fdf4;' : '' ?>">
                                <?= number_format((float)($a['sikap_score'] ?? 0), 1) ?>
                            </td>
                            <td style="text-align: center; font-family: monospace; font-size: 13px; font-weight: 600; <?= $sortBy === 'absensi' ? 'background: #f8fafc;' : '' ?>">
                                <?= number_format((float)($a['absensi_score'] ?? 0), 1) ?>
                            </td>
                            <td style="text-align: center; <?= $sortBy === 'total' ? 'background: #eff6ff;' : '' ?>">
                                <div style="font-weight: 700; font-size: 14px; color: var(--primary);">
                                    <?= number_format((float)($a['total_score'] ?? 0), 2) ?>
                                </div>
                                <?php if (!empty($a['letter_grade'])): ?>
                                    <?php 
                                    $grade = $a['letter_grade'];
                                    $badgeClass = ($grade === 'A') ? 'badge-success' : (($grade === 'B') ? 'badge-info' : (($grade === 'C') ? 'badge-warning' : 'badge-danger'));
                                    ?>
                                    <span class="badge <?= $badgeClass ?>" style="font-size: 10px; margin-top: 2px;">
                                        Predikat <?= $grade ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                                    <a href="<?= Auth::baseUrl() ?>/akfi/assess/<?= $a['id'] ?>" class="btn btn-sm btn-primary" title="Buka Formulir Penilaian">
                                        <i class="fa-solid fa-pen-to-square"></i> Nilai
                                    </a>
                                    <?php if ($a['status'] === 'Disetujui_Atasan' || $a['status'] === 'Final'): ?>
                                        <a href="<?= Auth::baseUrl() ?>/akfi/print-rapor/<?= $a['id'] ?>" target="_blank" class="btn btn-sm btn-outline" title="Cetak Rapor AKFI">
                                            <i class="fa-solid fa-print"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
