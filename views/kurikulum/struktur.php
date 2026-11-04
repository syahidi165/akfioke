<?php
// views/kurikulum/struktur.php
use Core\SecurityHelper;

$unitName = $unit['name'] ?? 'AL KAHFI ISLAMIC SCHOOL';
$unitCode = $unit['code'] ?? 'AIS';
$level = strtoupper($unit['level'] ?? '');
$isKbtk = (in_array($level, ['KB', 'TK'], true) || $unit['id'] == 1 || $unit['id'] == 2);
$isSd   = ($level === 'SD' || $unit['id'] == 6);
$isSmp  = ($level === 'SMP' || $unit['id'] == 7);
$isSma  = ($level === 'SMA' || $unit['id'] == 8);

$docNo = $meta['doc_number'] ?? 'FR – 9023a';
$rev   = $meta['revision'] ?? '-';
$notes = $meta['notes'] ?? '';
$ksNama = $meta['kepsek_nama'] ?: ($ksEmp['name'] ?? 'Kepala Sekolah');
$ksTelp = $meta['kepsek_telp'] ?: ($ksEmp['phone'] ?? '');
$deputiNama = $meta['deputi_nama'] ?: ($deputiUser['full_name'] ?? 'Yona Melinda, B.Eng., M.Ed');
$signDateKs = $meta['sign_date_kepsek'] ?? date('Y-m-d');
$signDateDeputi = $meta['sign_date_deputi'] ?? date('Y-m-d');
?>

<div class="page-header d-print-none mb-4">
    <div class="row align-items-center">
        <div class="col">
            <div class="page-pretitle text-muted">Standar Kurikulum AIS Batam</div>
            <h2 class="page-title text-primary d-flex align-items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-certificate" width="28" height="28" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M5 8v-3a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2h-5" /><circle cx="6" cy="14" r="3" /><path d="M4.5 17l-1.5 5l3 -1.5l3 1.5l-1.5 -5" /></svg>
                Pengajuan Struktur Kurikulum <?= SecurityHelper::escape($unitName) ?>
            </h2>
            <div class="text-muted small mt-1">
                Format Standar Resmi Dokumen <strong>FR – 9023a</strong> Terintegrasi Master Mata Pelajaran Unit.
            </div>
        </div>
        <div class="col-auto ms-auto d-flex align-items-center gap-2">
            <?php if ($isPimpinan && count($availableUnits) > 1): ?>
                <form method="GET" action="" class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 small text-muted">Unit Binaan:</label>
                    <select name="unit_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php foreach ($availableUnits as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= $u['id'] == $unitId ? 'selected' : '' ?>>
                                <?= SecurityHelper::escape($u['code'] . ' - ' . $u['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            <?php endif; ?>

            <a href="<?= SecurityHelper::baseUrl() ?>/kurikulum/struktur/pdf?unit_id=<?= $unitId ?>" target="_blank" class="btn btn-danger btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 15h6" /><path d="M12 12v6" /></svg>
                Download PDF (FR – 9023a)
            </a>
            <a href="<?= SecurityHelper::baseUrl() ?>/kurikulum/struktur/print?unit_id=<?= $unitId ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><rect x="7" y="13" width="10" height="8" rx="2" /></svg>
                Cetak Web
            </a>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs">
            <li class="nav-item">
                <a href="#tab-form" class="nav-link active" data-bs-toggle="tab">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1 text-primary" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a1.5 1.5 0 0 0 -5 -5l-10.5 10.5v4" /><line x1="13.5" y1="6.5" x2="17.5" y2="10.5" /></svg>
                    Pengaturan & Alokasi JP
                </a>
            </li>
            <li class="nav-item">
                <a href="#tab-preview" class="nav-link" data-bs-toggle="tab">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1 text-success" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                    Pratinjau Dokumen Resmi
                </a>
            </li>
        </ul>
        <?php if ($canEdit): ?>
            <div>
                <form method="POST" action="<?= SecurityHelper::baseUrl() ?>/kurikulum/struktur/reset-default" onsubmit="return confirm('Apakah Anda yakin ingin mengembalikan struktur kurikulum ke format standar approved lampiran? Semua perubahan baris manual akan diganti.')" class="d-inline">
                    <input type="hidden" name="unit_id" value="<?= $unitId ?>">
                    <button type="submit" class="btn btn-outline-warning btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
                        Reset ke Standar Approved
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <div class="card-body p-0">
        <div class="tab-content">
            <!-- TAB 1: FORM PENGATURAN -->
            <div class="tab-pane active show p-4" id="tab-form">
                <?php if (!$canEdit): ?>
                    <div class="alert alert-info py-2">
                        Anda berada dalam mode <strong>Pratinjau / Pimpinan</strong>. Formulir dikunci untuk pengeditan hanya dapat dilakukan oleh Kepala Sekolah atau Waka Kurikulum unit terkait.
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= SecurityHelper::baseUrl() ?>/kurikulum/struktur/save" id="formStruktur">
                    <input type="hidden" name="unit_id" value="<?= $unitId ?>">

                    <div class="row g-3 mb-4 p-3 bg-light rounded border">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">No. Dokumen</label>
                            <input type="text" name="doc_number" class="form-control form-control-sm" value="<?= SecurityHelper::escape($docNo) ?>" <?= $canEdit ? '' : 'readonly' ?>>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Revisi</label>
                            <input type="text" name="revision" class="form-control form-control-sm" value="<?= SecurityHelper::escape($rev) ?>" <?= $canEdit ? '' : 'readonly' ?>>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Nama Kepala Sekolah</label>
                            <input type="text" name="kepsek_nama" class="form-control form-control-sm" value="<?= SecurityHelper::escape($ksNama) ?>" <?= $canEdit ? '' : 'readonly' ?>>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">No. Telp / WA Kepala Sekolah</label>
                            <input type="text" name="kepsek_telp" class="form-control form-control-sm" value="<?= SecurityHelper::escape($ksTelp) ?>" placeholder="Contoh: 0812-xxxx-xxxx" <?= $canEdit ? '' : 'readonly' ?>>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Tanggal Diajukan KS</label>
                            <input type="date" name="sign_date_kepsek" class="form-control form-control-sm" value="<?= SecurityHelper::escape($signDateKs) ?>" <?= $canEdit ? '' : 'readonly' ?>>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Nama Deputi Direktur</label>
                            <input type="text" name="deputi_nama" class="form-control form-control-sm" value="<?= SecurityHelper::escape($deputiNama) ?>" <?= $canEdit ? '' : 'readonly' ?>>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Tanggal Disetujui Deputi</label>
                            <input type="date" name="sign_date_deputi" class="form-control form-control-sm" value="<?= SecurityHelper::escape($signDateDeputi) ?>" <?= $canEdit ? '' : 'readonly' ?>>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">Status Integrasi Mapel</label>
                            <div class="badge bg-success-lt p-2 w-100 text-start">
                                <?= count($masterSubjects) ?> Mapel Master Terdaftar
                            </div>
                        </div>
                    </div>

                    <?php if ($canEdit): ?>
                        <!-- Quick Add Bar from Master Subjects -->
                        <div class="card mb-4 border-dashed bg-white shadow-sm">
                            <div class="card-body p-3">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted">Pilih dari Master Mata Pelajaran (Input Operator):</label>
                                        <select id="quickSelectSubject" class="form-select form-select-sm">
                                            <option value="">-- Pilih Mata Pelajaran --</option>
                                            <?php foreach ($masterSubjects as $s): ?>
                                                <option value="<?= SecurityHelper::escape($s['name']) ?>" data-kelompok="<?= SecurityHelper::escape($s['type'] ?? 'Kelompok A') ?>">
                                                    <?= SecurityHelper::escape($s['name']) ?> (<?= SecurityHelper::escape($s['type'] ?? 'Umum') ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-muted">Kelompok Kurikulum:</label>
                                        <select id="quickKelompok" class="form-select form-select-sm">
                                            <?php if ($isKbtk): ?>
                                                <option value="Kelompok A (Umum)">Kelompok A (Umum)</option>
                                                <option value="Kelompok B (Ciri Khusus)">Kelompok B (Ciri Khusus)</option>
                                                <option value="Program Khusus">Program Khusus</option>
                                            <?php else: ?>
                                                <option value="Kelompok A (Umum)">Kelompok A (Umum)</option>
                                                <option value="Kelompok B">Kelompok B</option>
                                                <option value="Kelompok B (Khas)">Kelompok B (Khas)</option>
                                                <option value="Kelompok C (Pilihan)">Kelompok C (Pilihan)</option>
                                                <option value="Kelompok D (Muatan Lokal)">Kelompok D (Muatan Lokal)</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-muted">Atau Ketik Nama Program/Mapel Baru:</label>
                                        <input type="text" id="quickCustomName" class="form-control form-control-sm" placeholder="Nama program / mapel baru...">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-primary btn-sm w-100" id="btnAddRow">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                                            Tambah Baris
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- TABLE OF STRUKTUR ITEMS -->
                    <div class="table-responsive border rounded mb-3">
                        <table class="table table-vcenter table-bordered table-hover mb-0" id="itemsTable">
                            <thead class="bg-light text-center small">
                                <tr>
                                    <th style="width: 35px;">No</th>
                                    <th style="width: 160px;">Kelompok</th>
                                    <th style="width: 50px;">Kode</th>
                                    <th>Mata Pelajaran / Program</th>
                                    <?php if ($isKbtk): ?>
                                        <th style="width: 80px;">JP KB</th>
                                        <th style="width: 80px;">JP TK</th>
                                    <?php elseif ($isSd): ?>
                                        <th style="width: 50px;">I</th>
                                        <th style="width: 50px;">II</th>
                                        <th style="width: 50px;">III</th>
                                        <th style="width: 50px;">IV</th>
                                        <th style="width: 50px;">V</th>
                                        <th style="width: 50px;">VI</th>
                                    <?php elseif ($isSmp): ?>
                                        <th style="width: 60px;">VII</th>
                                        <th style="width: 60px;">VIII</th>
                                        <th style="width: 60px;">IX</th>
                                    <?php elseif ($isSma): ?>
                                        <th style="width: 60px;">X</th>
                                        <th style="width: 60px;">XI</th>
                                        <th style="width: 60px;">XII</th>
                                    <?php endif; ?>
                                    <th style="width: 140px;">Keterangan</th>
                                    <?php if ($canEdit): ?>
                                        <th style="width: 45px;">Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody id="itemsTbody">
                                <?php if (empty($items)): ?>
                                    <tr id="emptyRow">
                                        <td colspan="12" class="text-center text-muted py-4">Belum ada item struktur kurikulum. Silakan tambahkan baris di atas atau klik Reset Standar Approved.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($items as $idx => $it): ?>
                                        <tr class="item-row">
                                            <td class="text-center row-num text-muted small"><?= $idx + 1 ?></td>
                                            <td>
                                                <input type="hidden" name="item_id[]" value="<?= $it['id'] ?>">
                                                <input type="hidden" name="order_num[]" value="<?= $idx + 1 ?>">
                                                <input type="text" name="kelompok[]" class="form-control form-control-sm" value="<?= SecurityHelper::escape($it['kelompok']) ?>" <?= $canEdit ? '' : 'readonly' ?>>
                                            </td>
                                            <td>
                                                <input type="text" name="item_number[]" class="form-control form-control-sm text-center" value="<?= SecurityHelper::escape($it['item_number']) ?>" <?= $canEdit ? '' : 'readonly' ?>>
                                            </td>
                                            <td>
                                                <input type="text" name="nama_mapel[]" class="form-control form-control-sm fw-bold" value="<?= SecurityHelper::escape($it['nama_mapel']) ?>" required <?= $canEdit ? '' : 'readonly' ?>>
                                            </td>
                                            <?php if ($isKbtk): ?>
                                                <td><input type="text" name="jp_kb[]" class="form-control form-control-sm text-center" value="<?= SecurityHelper::escape($it['jp_kb'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>></td>
                                                <td><input type="text" name="jp_tk[]" class="form-control form-control-sm text-center" value="<?= SecurityHelper::escape($it['jp_tk'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>></td>
                                            <?php elseif ($isSd): ?>
                                                <td><input type="text" name="jp_1[]" class="form-control form-control-sm text-center calc-jp" value="<?= SecurityHelper::escape($it['jp_1'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>></td>
                                                <td><input type="text" name="jp_2[]" class="form-control form-control-sm text-center calc-jp" value="<?= SecurityHelper::escape($it['jp_2'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>></td>
                                                <td><input type="text" name="jp_3[]" class="form-control form-control-sm text-center calc-jp" value="<?= SecurityHelper::escape($it['jp_3'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>></td>
                                                <td><input type="text" name="jp_4[]" class="form-control form-control-sm text-center calc-jp" value="<?= SecurityHelper::escape($it['jp_4'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>></td>
                                                <td><input type="text" name="jp_5[]" class="form-control form-control-sm text-center calc-jp" value="<?= SecurityHelper::escape($it['jp_5'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>></td>
                                                <td><input type="text" name="jp_6[]" class="form-control form-control-sm text-center calc-jp" value="<?= SecurityHelper::escape($it['jp_6'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>></td>
                                            <?php elseif ($isSmp): ?>
                                                <td><input type="text" name="jp_7[]" class="form-control form-control-sm text-center calc-jp" value="<?= SecurityHelper::escape($it['jp_7'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>></td>
                                                <td><input type="text" name="jp_8[]" class="form-control form-control-sm text-center calc-jp" value="<?= SecurityHelper::escape($it['jp_8'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>></td>
                                                <td><input type="text" name="jp_9[]" class="form-control form-control-sm text-center calc-jp" value="<?= SecurityHelper::escape($it['jp_9'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>></td>
                                            <?php elseif ($isSma): ?>
                                                <td><input type="text" name="jp_10[]" class="form-control form-control-sm text-center calc-jp" value="<?= SecurityHelper::escape($it['jp_10'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>></td>
                                                <td><input type="text" name="jp_11[]" class="form-control form-control-sm text-center calc-jp" value="<?= SecurityHelper::escape($it['jp_11'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>></td>
                                                <td><input type="text" name="jp_12[]" class="form-control form-control-sm text-center calc-jp" value="<?= SecurityHelper::escape($it['jp_12'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>></td>
                                            <?php endif; ?>
                                            <td>
                                                <input type="text" name="keterangan[]" class="form-control form-control-sm" value="<?= SecurityHelper::escape($it['keterangan'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>>
                                            </td>
                                            <?php if ($canEdit): ?>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-ghost-danger btn-sm p-1 btn-del-row" title="Hapus baris">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                                    </button>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Catatan Alokasi Waktu / Keterangan Tambahan:</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Contoh: 1 JP = 35 menit. Pembagian kelompok A, B, C disesuaikan dengan kurikulum operasional satuan..." <?= $canEdit ? '' : 'readonly' ?>><?= SecurityHelper::escape($notes) ?></textarea>
                    </div>

                    <?php if ($canEdit): ?>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><circle cx="12" cy="14" r="2" /><polyline points="14 4 14 8 8 8 8 4" /></svg>
                                Simpan Perubahan Struktur Kurikulum
                            </button>
                        </div>
                    <?php endif; ?>
                </form>
            </div>

            <!-- TAB 2: PRATINJAU DOKUMEN RESMI (FR – 9023a) -->
            <div class="tab-pane p-4" id="tab-preview">
                <div class="border rounded p-4 bg-white shadow-sm mx-auto" style="max-width: 900px;">
                    <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom border-2 border-dark">
                        <div>
                            <h4 class="mb-0 fw-bold text-success text-uppercase"><?= SecurityHelper::escape($unitName) ?></h4>
                            <div class="small text-muted">YAYASAN PENDIDIKAN ISLAM AL KAHFI BATAM</div>
                        </div>
                        <table class="table table-bordered table-sm mb-0 text-center" style="width: 220px; font-size: 11px;">
                            <tr>
                                <th class="bg-light py-1">No. Dokumen</th>
                                <td class="py-1 fw-bold"><?= SecurityHelper::escape($docNo) ?></td>
                            </tr>
                            <tr>
                                <th class="bg-light py-1">Revisi</th>
                                <td class="py-1"><?= SecurityHelper::escape($rev) ?></td>
                            </tr>
                        </table>
                    </div>

                    <div class="text-center my-3">
                        <h5 class="fw-bold mb-1 text-uppercase">PENGAJUAN STRUKTUR KURIKULUM <?= SecurityHelper::escape($unitCode) ?></h5>
                        <div class="text-muted fw-bold">TAHUN AJARAN <?= SecurityHelper::escape($meta['academic_year_name'] ?? '2026-2027') ?></div>
                    </div>

                    <?php if ($isKbtk): ?>
                        <table class="table table-bordered table-sm mb-3" style="font-size: 11px;">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th rowspan="2" style="width: 35px;">NO</th>
                                    <th rowspan="2">STRUKTUR PROGRAM</th>
                                    <th colspan="2">ALOKASI WAKTU</th>
                                    <th rowspan="2" style="width: 140px;">KETERANGAN</th>
                                </tr>
                                <tr>
                                    <th style="width: 70px;">KB</th>
                                    <th style="width: 70px;">TK</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $currKel = '';
                                foreach ($items as $it): 
                                    if ($it['kelompok'] !== $currKel):
                                        $currKel = $it['kelompok'];
                                ?>
                                    <tr class="table-secondary fw-bold">
                                        <td class="text-center"><?= SecurityHelper::escape(substr($currKel, -1)) ?></td>
                                        <td colspan="4"><?= SecurityHelper::escape($currKel) ?></td>
                                    </tr>
                                <?php endif; ?>
                                    <tr>
                                        <td class="text-center"><?= SecurityHelper::escape($it['item_number']) ?></td>
                                        <td><?= SecurityHelper::escape($it['nama_mapel']) ?></td>
                                        <td class="text-center"><?= SecurityHelper::escape($it['jp_kb'] ?: '-') ?></td>
                                        <td class="text-center"><?= SecurityHelper::escape($it['jp_tk'] ?: '-') ?></td>
                                        <td><?= SecurityHelper::escape($it['keterangan'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                    <?php elseif ($isSd): ?>
                        <table class="table table-bordered table-sm mb-3" style="font-size: 11px;">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th rowspan="2" style="width: 30px;">NO</th>
                                    <th rowspan="2">MATA PELAJARAN</th>
                                    <th colspan="6">ALOKASI WAKTU PER MINGGU</th>
                                    <th rowspan="2" style="width: 120px;">KETERANGAN</th>
                                </tr>
                                <tr>
                                    <th style="width: 32px;">I</th>
                                    <th style="width: 32px;">II</th>
                                    <th style="width: 32px;">III</th>
                                    <th style="width: 32px;">IV</th>
                                    <th style="width: 32px;">V</th>
                                    <th style="width: 32px;">VI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $currKel = '';
                                $tot1 = 0; $tot2 = 0; $tot3 = 0; $tot4 = 0; $tot5 = 0; $tot6 = 0;
                                foreach ($items as $it): 
                                    if ($it['kelompok'] !== $currKel):
                                        $currKel = $it['kelompok'];
                                ?>
                                    <tr class="table-secondary fw-bold">
                                        <td colspan="9"><?= SecurityHelper::escape($currKel) ?></td>
                                    </tr>
                                <?php endif; 
                                    if (!in_array($it['kelompok'], ['Kelompok C', 'Kelompok D'], true)) {
                                        if (is_numeric($it['jp_1'])) $tot1 += (int)$it['jp_1'];
                                        if (is_numeric($it['jp_2'])) $tot2 += (int)$it['jp_2'];
                                        if (is_numeric($it['jp_3'])) $tot3 += (int)$it['jp_3'];
                                        if (is_numeric($it['jp_4'])) $tot4 += (int)$it['jp_4'];
                                        if (is_numeric($it['jp_5'])) $tot5 += (int)$it['jp_5'];
                                        if (is_numeric($it['jp_6'])) $tot6 += (int)$it['jp_6'];
                                    }
                                ?>
                                    <tr>
                                        <td class="text-center"><?= SecurityHelper::escape($it['item_number']) ?></td>
                                        <td><?= SecurityHelper::escape($it['nama_mapel']) ?></td>
                                        <td class="text-center"><?= SecurityHelper::escape($it['jp_1'] ?: '-') ?></td>
                                        <td class="text-center"><?= SecurityHelper::escape($it['jp_2'] ?: '-') ?></td>
                                        <td class="text-center"><?= SecurityHelper::escape($it['jp_3'] ?: '-') ?></td>
                                        <td class="text-center"><?= SecurityHelper::escape($it['jp_4'] ?: '-') ?></td>
                                        <td class="text-center"><?= SecurityHelper::escape($it['jp_5'] ?: '-') ?></td>
                                        <td class="text-center"><?= SecurityHelper::escape($it['jp_6'] ?: '-') ?></td>
                                        <td><?= SecurityHelper::escape($it['keterangan'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="table-light fw-bold text-center">
                                    <td colspan="2">TOTAL (tanpa kelompok C dan D)</td>
                                    <td><?= $tot1 ?></td>
                                    <td><?= $tot2 ?></td>
                                    <td><?= $tot3 ?></td>
                                    <td><?= $tot4 ?></td>
                                    <td><?= $tot5 ?></td>
                                    <td><?= $tot6 ?></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>

                    <?php elseif ($isSmp): ?>
                        <table class="table table-bordered table-sm mb-3" style="font-size: 11px;">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th rowspan="2" style="width: 30px;">NO</th>
                                    <th rowspan="2">MATA PELAJARAN</th>
                                    <th colspan="3">ALOKASI WAKTU PER MINGGU</th>
                                    <th rowspan="2" style="width: 130px;">KETERANGAN</th>
                                </tr>
                                <tr>
                                    <th style="width: 45px;">VII</th>
                                    <th style="width: 45px;">VIII</th>
                                    <th style="width: 45px;">IX</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $currKel = '';
                                $tot7 = 0; $tot8 = 0; $tot9 = 0;
                                foreach ($items as $it): 
                                    if ($it['kelompok'] !== $currKel):
                                        $currKel = $it['kelompok'];
                                ?>
                                    <tr class="table-secondary fw-bold">
                                        <td colspan="6"><?= SecurityHelper::escape($currKel) ?></td>
                                    </tr>
                                <?php endif; 
                                    if (is_numeric($it['jp_7'])) $tot7 += (int)$it['jp_7'];
                                    if (is_numeric($it['jp_8'])) $tot8 += (int)$it['jp_8'];
                                    if (is_numeric($it['jp_9'])) $tot9 += (int)$it['jp_9'];
                                ?>
                                    <tr>
                                        <td class="text-center"><?= SecurityHelper::escape($it['item_number']) ?></td>
                                        <td><?= SecurityHelper::escape($it['nama_mapel']) ?></td>
                                        <td class="text-center"><?= SecurityHelper::escape($it['jp_7'] ?: '-') ?></td>
                                        <td class="text-center"><?= SecurityHelper::escape($it['jp_8'] ?: '-') ?></td>
                                        <td class="text-center"><?= SecurityHelper::escape($it['jp_9'] ?: '-') ?></td>
                                        <td><?= SecurityHelper::escape($it['keterangan'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="table-light fw-bold text-center">
                                    <td colspan="2">TOTAL ALOKASI JP PER MINGGU</td>
                                    <td><?= $tot7 ?></td>
                                    <td><?= $tot8 ?></td>
                                    <td><?= $tot9 ?></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>

                    <?php elseif ($isSma): ?>
                        <table class="table table-bordered table-sm mb-3" style="font-size: 11px;">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th rowspan="2" style="width: 30px;">NO</th>
                                    <th rowspan="2">MATA PELAJARAN</th>
                                    <th colspan="3">ALOKASI WAKTU PER MINGGU</th>
                                    <th rowspan="2" style="width: 130px;">KETERANGAN</th>
                                </tr>
                                <tr>
                                    <th style="width: 45px;">X</th>
                                    <th style="width: 45px;">XI</th>
                                    <th style="width: 45px;">XII</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $currKel = '';
                                $tot10 = 0; $tot11 = 0; $tot12 = 0;
                                foreach ($items as $it): 
                                    if ($it['kelompok'] !== $currKel):
                                        $currKel = $it['kelompok'];
                                ?>
                                    <tr class="table-secondary fw-bold">
                                        <td colspan="6"><?= SecurityHelper::escape($currKel) ?></td>
                                    </tr>
                                <?php endif; 
                                    if (is_numeric($it['jp_10'])) $tot10 += (int)$it['jp_10'];
                                    if (is_numeric($it['jp_11'])) $tot11 += (int)$it['jp_11'];
                                    if (is_numeric($it['jp_12'])) $tot12 += (int)$it['jp_12'];
                                ?>
                                    <tr>
                                        <td class="text-center"><?= SecurityHelper::escape($it['item_number']) ?></td>
                                        <td><?= SecurityHelper::escape($it['nama_mapel']) ?></td>
                                        <td class="text-center"><?= SecurityHelper::escape($it['jp_10'] ?: '-') ?></td>
                                        <td class="text-center"><?= SecurityHelper::escape($it['jp_11'] ?: '-') ?></td>
                                        <td class="text-center"><?= SecurityHelper::escape($it['jp_12'] ?: '-') ?></td>
                                        <td><?= SecurityHelper::escape($it['keterangan'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="table-light fw-bold text-center">
                                    <td colspan="2">TOTAL ALOKASI JP PER MINGGU</td>
                                    <td><?= $tot10 ?></td>
                                    <td><?= $tot11 ?></td>
                                    <td><?= $tot12 ?></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    <?php endif; ?>

                    <?php if (!empty($notes)): ?>
                        <div class="small p-2 bg-light border rounded mb-4">
                            <strong>Catatan / Keterangan:</strong><br>
                            <?= nl2br(SecurityHelper::escape($notes)) ?>
                        </div>
                    <?php endif; ?>

                    <div class="row text-center mt-4">
                        <div class="col-6">
                            <div class="small text-muted">Diajukan oleh:</div>
                            <div class="fw-bold small">Kepala Sekolah <?= SecurityHelper::escape($unitCode) ?></div>
                            <div class="text-muted small">Tanggal: <?= date('d F Y', strtotime($signDateKs)) ?></div>
                            <div style="height: 60px;"></div>
                            <div class="fw-bold text-decoration-underline"><?= SecurityHelper::escape($ksNama) ?></div>
                            <div class="small text-muted">No. Telp / WA: <?= SecurityHelper::escape($ksTelp ?: '-') ?></div>
                        </div>
                        <div class="col-6">
                            <div class="small text-muted">Disetujui oleh:</div>
                            <div class="fw-bold small">Deputi Direktur Pendidikan AIS</div>
                            <div class="text-muted small">Tanggal: <?= date('d F Y', strtotime($signDateDeputi)) ?></div>
                            <div style="height: 60px;"></div>
                            <div class="fw-bold text-decoration-underline"><?= SecurityHelper::escape($deputiNama) ?></div>
                            <div class="small text-muted">Deputi Direktur Pendidikan Al Kahfi</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const isKbtk = <?= $isKbtk ? 'true' : 'false' ?>;
    const isSd   = <?= $isSd ? 'true' : 'false' ?>;
    const isSmp  = <?= $isSmp ? 'true' : 'false' ?>;
    const isSma  = <?= $isSma ? 'true' : 'false' ?>;
    const canEdit = <?= $canEdit ? 'true' : 'false' ?>;

    const btnAddRow = document.getElementById('btnAddRow');
    const quickSelectSubject = document.getElementById('quickSelectSubject');
    const quickKelompok = document.getElementById('quickKelompok');
    const quickCustomName = document.getElementById('quickCustomName');
    const tbody = document.getElementById('itemsTbody');

    if (quickSelectSubject) {
        quickSelectSubject.addEventListener('change', function() {
            if (this.value) {
                quickCustomName.value = '';
                const opt = this.options[this.selectedIndex];
                const optKel = opt.getAttribute('data-kelompok');
                if (optKel && quickKelompok) {
                    for (let i = 0; i < quickKelompok.options.length; i++) {
                        if (quickKelompok.options[i].value.toLowerCase().includes(optKel.toLowerCase())) {
                            quickKelompok.selectedIndex = i;
                            break;
                        }
                    }
                }
            }
        });
    }

    if (btnAddRow && canEdit) {
        btnAddRow.addEventListener('click', function() {
            const mapelName = quickCustomName.value.trim() || (quickSelectSubject ? quickSelectSubject.value : '');
            if (!mapelName) {
                alert('Silakan pilih mata pelajaran dari master atau ketik nama mapel/program baru.');
                return;
            }

            const kelompokVal = quickKelompok ? quickKelompok.value : 'Kelompok A (Umum)';
            const emptyRow = document.getElementById('emptyRow');
            if (emptyRow) emptyRow.remove();

            const currentRows = tbody.querySelectorAll('.item-row');
            const rowCount = currentRows.length + 1;

            let jpColsHtml = '';
            if (isKbtk) {
                jpColsHtml = `
                    <td><input type="text" name="jp_kb[]" class="form-control form-control-sm text-center" value="2"></td>
                    <td><input type="text" name="jp_tk[]" class="form-control form-control-sm text-center" value="2"></td>
                `;
            } else if (isSd) {
                jpColsHtml = `
                    <td><input type="text" name="jp_1[]" class="form-control form-control-sm text-center calc-jp" value="2"></td>
                    <td><input type="text" name="jp_2[]" class="form-control form-control-sm text-center calc-jp" value="2"></td>
                    <td><input type="text" name="jp_3[]" class="form-control form-control-sm text-center calc-jp" value="2"></td>
                    <td><input type="text" name="jp_4[]" class="form-control form-control-sm text-center calc-jp" value="2"></td>
                    <td><input type="text" name="jp_5[]" class="form-control form-control-sm text-center calc-jp" value="2"></td>
                    <td><input type="text" name="jp_6[]" class="form-control form-control-sm text-center calc-jp" value="2"></td>
                `;
            } else if (isSmp) {
                jpColsHtml = `
                    <td><input type="text" name="jp_7[]" class="form-control form-control-sm text-center calc-jp" value="2"></td>
                    <td><input type="text" name="jp_8[]" class="form-control form-control-sm text-center calc-jp" value="2"></td>
                    <td><input type="text" name="jp_9[]" class="form-control form-control-sm text-center calc-jp" value="2"></td>
                `;
            } else if (isSma) {
                jpColsHtml = `
                    <td><input type="text" name="jp_10[]" class="form-control form-control-sm text-center calc-jp" value="2"></td>
                    <td><input type="text" name="jp_11[]" class="form-control form-control-sm text-center calc-jp" value="2"></td>
                    <td><input type="text" name="jp_12[]" class="form-control form-control-sm text-center calc-jp" value="2"></td>
                `;
            }

            const tr = document.createElement('tr');
            tr.className = 'item-row';
            tr.innerHTML = `
                <td class="text-center row-num text-muted small">${rowCount}</td>
                <td>
                    <input type="hidden" name="item_id[]" value="0">
                    <input type="hidden" name="order_num[]" value="${rowCount}">
                    <input type="text" name="kelompok[]" class="form-control form-control-sm" value="${escapeHtml(kelompokVal)}">
                </td>
                <td>
                    <input type="text" name="item_number[]" class="form-control form-control-sm text-center" value="${rowCount}">
                </td>
                <td>
                    <input type="text" name="nama_mapel[]" class="form-control form-control-sm fw-bold" value="${escapeHtml(mapelName)}" required>
                </td>
                ${jpColsHtml}
                <td>
                    <input type="text" name="keterangan[]" class="form-control form-control-sm" value="">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-ghost-danger btn-sm p-1 btn-del-row" title="Hapus baris">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                    </button>
                </td>
            `;

            tbody.appendChild(tr);
            quickCustomName.value = '';
            if (quickSelectSubject) quickSelectSubject.value = '';
            attachDeleteEvents();
        });
    }

    function attachDeleteEvents() {
        document.querySelectorAll('.btn-del-row').forEach(btn => {
            btn.onclick = function() {
                const tr = this.closest('tr');
                if (tr) {
                    tr.remove();
                    reindexRows();
                }
            };
        });
    }

    function reindexRows() {
        const rows = tbody.querySelectorAll('.item-row');
        rows.forEach((r, idx) => {
            const numCell = r.querySelector('.row-num');
            if (numCell) numCell.textContent = idx + 1;
            const orderInput = r.querySelector('input[name="order_num[]"]');
            if (orderInput) orderInput.value = idx + 1;
        });
    }

    function escapeHtml(text) {
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    attachDeleteEvents();
});
</script>
