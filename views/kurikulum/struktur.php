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
$ksNama = $meta['kepsek_nama'] ?: ($ksEmp['nama'] ?? 'Kepala Sekolah');
$ksTelp = $meta['kepsek_telp'] ?: ($ksEmp['contact_phone'] ?? '');
$deputiNama = $meta['deputi_nama'] ?: ($deputiUser['full_name'] ?? 'Yona Melinda, B.Eng., M.Ed');
$signDateKs = $meta['sign_date_kepsek'] ?? date('Y-m-d');
$signDateDeputi = $meta['sign_date_deputi'] ?? date('Y-m-d');
?>

<div class="page-header" style="margin-bottom: 20px;">
    <div>
        <h1 class="page-title" style="display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-file-contract" style="color: var(--primary);"></i>
            Pengajuan Struktur Kurikulum <?= SecurityHelper::escape($unitName) ?>
        </h1>
        <div class="page-subtitle">
            Format Dokumen Resmi Approved <strong>FR – 9023a</strong> Yayasan Pendidikan Islam Al Kahfi Batam.
        </div>
    </div>
    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
        <?php if ($isPimpinan && count($availableUnits) > 1): ?>
            <form method="GET" action="" style="display: flex; align-items: center; gap: 6px; margin: 0;">
                <label class="form-label mb-0" style="font-size: 13px; font-weight: 600; white-space: nowrap; margin-bottom: 0;">Unit:</label>
                <select name="unit_id" onchange="this.form.submit()" style="padding: 7px 12px; border-radius: 8px; border: 1px solid var(--border-color); background: #ffffff; font-weight: 600; font-size: 13px; color: var(--text-main); cursor: pointer;">
                    <?php foreach ($availableUnits as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= $u['id'] == $unitId ? 'selected' : '' ?>>
                            <?= SecurityHelper::escape($u['code'] . ' - ' . $u['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        <?php endif; ?>

        <a href="<?= $baseUrl ?>/kurikulum/struktur/pdf?unit_id=<?= $unitId ?>" target="_blank" class="btn btn-danger btn-sm" style="padding: 7px 14px; font-weight: 600;">
            <i class="fa-solid fa-file-pdf"></i> Download PDF (FR – 9023a)
        </a>
        <a href="<?= $baseUrl ?>/kurikulum/struktur/print?unit_id=<?= $unitId ?>" target="_blank" class="btn btn-outline btn-sm" style="padding: 7px 14px; font-weight: 600;">
            <i class="fa-solid fa-print"></i> Cetak Web
        </a>
    </div>
</div>

<!-- TABS NAVIGATION & ACTIONS -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-header" style="background: #ffffff; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; gap: 8px;">
            <button type="button" id="tabBtnForm" class="btn btn-primary btn-sm" onclick="switchTab('form')" style="padding: 7px 16px; font-weight: 600;">
                <i class="fa-solid fa-pen-to-square"></i> Pengaturan &amp; Alokasi JP
            </button>
            <button type="button" id="tabBtnPreview" class="btn btn-outline btn-sm" onclick="switchTab('preview')" style="padding: 7px 16px; font-weight: 600;">
                <i class="fa-solid fa-eye"></i> Pratinjau Dokumen Resmi (FR – 9023a)
            </button>
        </div>
        <?php if ($canEdit): ?>
            <form method="POST" action="<?= $baseUrl ?>/kurikulum/struktur/reset-default" onsubmit="return confirm('Kembalikan struktur kurikulum ke format standar approved lampiran? Seluruh perubahan baris akan diatur ulang.')" style="margin: 0;">
                <input type="hidden" name="unit_id" value="<?= $unitId ?>">
                <button type="submit" class="btn btn-outline btn-sm" style="color: #d97706; border-color: #fde68a; background: #fffbeb;">
                    <i class="fa-solid fa-rotate-left"></i> Reset ke Standar Approved
                </button>
            </form>
        <?php endif; ?>
    </div>

    <!-- TAB 1: PENGATURAN & INPUT JP -->
    <div id="tabContentForm" style="display: block; padding: 20px;">
        <?php if (!$canEdit): ?>
            <div class="alert alert-info" style="margin-bottom: 20px;">
                <i class="fa-solid fa-lock" style="font-size: 16px;"></i>
                <div>Mode Pratinjau Pimpinan: Pengeditan alokasi JP kurikulum hanya dapat dilakukan oleh Kepala Sekolah atau Waka Kurikulum unit terkait.</div>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= $baseUrl ?>/kurikulum/struktur/save" id="formStruktur">
            <input type="hidden" name="unit_id" value="<?= $unitId ?>">

            <!-- META HEADER GRID -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                <div>
                    <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">No. Dokumen:</label>
                    <input type="text" name="doc_number" style="width: 100%; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 12.5px; font-weight: 600;" value="<?= SecurityHelper::escape($docNo) ?>" <?= $canEdit ? '' : 'readonly' ?>>
                </div>
                <div>
                    <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Revisi:</label>
                    <input type="text" name="revision" style="width: 100%; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 12.5px;" value="<?= SecurityHelper::escape($rev) ?>" <?= $canEdit ? '' : 'readonly' ?>>
                </div>
                <div>
                    <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Nama Kepala Sekolah:</label>
                    <input type="text" name="kepsek_nama" style="width: 100%; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 12.5px; font-weight: 600;" value="<?= SecurityHelper::escape($ksNama) ?>" <?= $canEdit ? '' : 'readonly' ?>>
                </div>
                <div>
                    <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">No. Telp / WA Kepala Sekolah:</label>
                    <input type="text" name="kepsek_telp" style="width: 100%; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 12.5px;" value="<?= SecurityHelper::escape($ksTelp) ?>" placeholder="Contoh: 0812-xxxx-xxxx" <?= $canEdit ? '' : 'readonly' ?>>
                </div>
                <div>
                    <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Tanggal Diajukan KS:</label>
                    <input type="date" name="sign_date_kepsek" style="width: 100%; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 12.5px;" value="<?= SecurityHelper::escape($signDateKs) ?>" <?= $canEdit ? '' : 'readonly' ?>>
                </div>
                <div>
                    <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Nama Deputi Direktur:</label>
                    <input type="text" name="deputi_nama" style="width: 100%; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 12.5px; font-weight: 600;" value="<?= SecurityHelper::escape($deputiNama) ?>" <?= $canEdit ? '' : 'readonly' ?>>
                </div>
                <div>
                    <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Tanggal Disetujui Deputi:</label>
                    <input type="date" name="sign_date_deputi" style="width: 100%; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 12.5px;" value="<?= SecurityHelper::escape($signDateDeputi) ?>" <?= $canEdit ? '' : 'readonly' ?>>
                </div>
                <div>
                    <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Status Integrasi Master Mapel:</label>
                    <div class="badge badge-success" style="font-size: 11.5px; padding: 7px 12px; width: 100%; display: flex; align-items: center; justify-content: center; gap: 6px;">
                        <i class="fa-solid fa-database"></i> <?= count($masterSubjects) ?> Mapel Master Terhubung
                    </div>
                </div>
            </div>

            <?php if ($canEdit): ?>
                <!-- QUICK ADD BAR FROM MASTER MAPEL -->
                <div style="background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 14px 18px; margin-bottom: 20px;">
                    <div style="font-size: 13px; font-weight: 700; color: var(--text-main); margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-plus-circle" style="color: var(--primary);"></i>
                        Tambah Mata Pelajaran ke Struktur Kurikulum
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end;">
                        <div style="flex: 2; min-width: 220px;">
                            <label class="form-label" style="font-size: 11.5px; margin-bottom: 3px;">Pilih dari Master Mapel (Input Operator):</label>
                            <select id="quickSelectSubject" style="width: 100%; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 12.5px;">
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                <?php foreach ($masterSubjects as $s): ?>
                                    <option value="<?= SecurityHelper::escape($s['name']) ?>" data-kelompok="<?= SecurityHelper::escape($s['type'] ?? 'Kelompok A') ?>">
                                        <?= SecurityHelper::escape($s['name']) ?> (<?= SecurityHelper::escape($s['type'] ?? 'Umum') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="flex: 1.5; min-width: 180px;">
                            <label class="form-label" style="font-size: 11.5px; margin-bottom: 3px;">Kelompok Kurikulum:</label>
                            <select id="quickKelompok" style="width: 100%; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 12.5px;">
                                <?php if ($isKbtk): ?>
                                    <option value="Kelompok A (Umum)">Kelompok A (Umum)</option>
                                    <option value="Kelompok B (Ciri Khusus)">Kelompok B (Ciri Khusus)</option>
                                    <option value="Program Khusus">Program Khusus</option>
                                <?php elseif ($isSd): ?>
                                    <option value="Kelompok A">Kelompok A</option>
                                    <option value="Kelompok B">Kelompok B</option>
                                    <option value="Kelompok C">Kelompok C (Kokurikuler P5)</option>
                                    <option value="Kelompok D">Kelompok D (Pengembangan Diri)</option>
                                <?php else: ?>
                                    <option value="Kelompok A (Umum)">Kelompok A (Umum)</option>
                                    <option value="Kelompok B (Umum)">Kelompok B (Umum)</option>
                                    <option value="Kurikulum Tingkat Satuan Pendidikan">Kurikulum Khas / KTSP</option>
                                    <option value="Pilihan / P5">Pilihan / P5</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div style="flex: 1.5; min-width: 180px;">
                            <label class="form-label" style="font-size: 11.5px; margin-bottom: 3px;">Atau Nama Mapel / Program Baru:</label>
                            <input type="text" id="quickCustomName" style="width: 100%; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 12.5px;" placeholder="Ketik nama mapel baru...">
                        </div>
                        <div>
                            <button type="button" class="btn btn-primary" id="btnAddRow" style="padding: 7px 16px; font-size: 12.5px; font-weight: 600;">
                                <i class="fa-solid fa-plus"></i> Tambah Baris
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- TABEL INSTRUMEN STRUKTUR KURIKULUM -->
            <div class="table-responsive" style="border: 1px solid var(--border-color); border-radius: 8px; margin-bottom: 20px;">
                <table class="table" id="itemsTable">
                    <thead>
                        <tr style="background: #f8fafc;">
                            <th style="width: 40px; text-align: center;">No</th>
                            <th style="width: 170px;">Kelompok</th>
                            <th style="width: 65px; text-align: center;">Kode</th>
                            <th>Mata Pelajaran / Program</th>
                            <?php if ($isKbtk): ?>
                                <th style="width: 85px; text-align: center;">JP KB</th>
                                <th style="width: 85px; text-align: center;">JP TK</th>
                            <?php elseif ($isSd): ?>
                                <th style="width: 55px; text-align: center;">I</th>
                                <th style="width: 55px; text-align: center;">II</th>
                                <th style="width: 55px; text-align: center;">III</th>
                                <th style="width: 55px; text-align: center;">IV</th>
                                <th style="width: 55px; text-align: center;">V</th>
                                <th style="width: 55px; text-align: center;">VI</th>
                            <?php elseif ($isSmp): ?>
                                <th style="width: 70px; text-align: center;">VII</th>
                                <th style="width: 70px; text-align: center;">VIII</th>
                                <th style="width: 70px; text-align: center;">IX</th>
                            <?php elseif ($isSma): ?>
                                <th style="width: 70px; text-align: center;">X</th>
                                <th style="width: 70px; text-align: center;">XI</th>
                                <th style="width: 70px; text-align: center;">XII</th>
                            <?php endif; ?>
                            <th style="width: 170px;">Keterangan</th>
                            <?php if ($canEdit): ?>
                                <th style="width: 50px; text-align: center;">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="itemsTbody">
                        <?php if (empty($items)): ?>
                            <tr id="emptyRow">
                                <td colspan="12" style="text-align: center; padding: 28px; color: var(--text-muted);">
                                    Belum ada baris struktur kurikulum. Silakan klik <strong>Reset Standar Approved</strong> atau tambahkan baris di atas.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $idx => $it): ?>
                                <tr class="item-row" style="transition: background-color 0.2s;">
                                    <td style="text-align: center; color: var(--text-muted); font-weight: 600;" class="row-num"><?= $idx + 1 ?></td>
                                    <td>
                                        <input type="hidden" name="item_id[]" value="<?= $it['id'] ?>">
                                        <input type="hidden" name="order_num[]" value="<?= $idx + 1 ?>">
                                        <input type="text" name="kelompok[]" style="width: 100%; padding: 5px 8px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px;" value="<?= SecurityHelper::escape($it['kelompok']) ?>" <?= $canEdit ? '' : 'readonly' ?>>
                                    </td>
                                    <td>
                                        <input type="text" name="item_number[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="<?= SecurityHelper::escape($it['item_number']) ?>" <?= $canEdit ? '' : 'readonly' ?>>
                                    </td>
                                    <td>
                                        <input type="text" name="nama_mapel[]" style="width: 100%; padding: 5px 8px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12.5px; font-weight: 700; color: var(--text-main);" value="<?= SecurityHelper::escape($it['nama_mapel']) ?>" required <?= $canEdit ? '' : 'readonly' ?>>
                                    </td>
                                    <?php if ($isKbtk): ?>
                                        <td><input type="text" name="jp_1[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="<?= SecurityHelper::escape($it['jp_1'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>></td>
                                        <td><input type="text" name="jp_2[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="<?= SecurityHelper::escape($it['jp_2'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>></td>
                                    <?php elseif ($isSd): ?>
                                        <td><input type="text" name="jp_1[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="<?= SecurityHelper::escape($it['jp_1'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>></td>
                                        <td><input type="text" name="jp_2[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="<?= SecurityHelper::escape($it['jp_2'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>></td>
                                        <td><input type="text" name="jp_3[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="<?= SecurityHelper::escape($it['jp_3'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>></td>
                                        <td><input type="text" name="jp_4[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="<?= SecurityHelper::escape($it['jp_4'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>></td>
                                        <td><input type="text" name="jp_5[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="<?= SecurityHelper::escape($it['jp_5'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>></td>
                                        <td><input type="text" name="jp_6[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="<?= SecurityHelper::escape($it['jp_6'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>></td>
                                    <?php elseif ($isSmp): ?>
                                        <td><input type="text" name="jp_7[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="<?= SecurityHelper::escape($it['jp_7'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>></td>
                                        <td><input type="text" name="jp_8[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="<?= SecurityHelper::escape($it['jp_8'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>></td>
                                        <td><input type="text" name="jp_9[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="<?= SecurityHelper::escape($it['jp_9'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>></td>
                                    <?php elseif ($isSma): ?>
                                        <td><input type="text" name="jp_10[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="<?= SecurityHelper::escape($it['jp_10'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>></td>
                                        <td><input type="text" name="jp_11[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="<?= SecurityHelper::escape($it['jp_11'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>></td>
                                        <td><input type="text" name="jp_12[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="<?= SecurityHelper::escape($it['jp_12'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>></td>
                                    <?php endif; ?>
                                    <td>
                                        <input type="text" name="keterangan[]" style="width: 100%; padding: 5px 8px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px;" value="<?= SecurityHelper::escape($it['keterangan'] ?? '') ?>" <?= $canEdit ? '' : 'readonly' ?>>
                                    </td>
                                    <?php if ($canEdit): ?>
                                        <td style="text-align: center;">
                                            <button type="button" class="btn btn-outline btn-sm btn-del-row" style="padding: 4px 8px; color: #ef4444; border-color: #fca5a5;" title="Hapus Baris">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- CATATAN ALOKASI WAKTU -->
            <div style="margin-bottom: 20px;">
                <label class="form-label" style="font-size: 13px; font-weight: 700;">Catatan Alokasi Waktu / Keterangan Pembelajaran:</label>
                <textarea name="notes" rows="3" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 13px;" placeholder="Contoh: Alokasi waktu tatap muka per JP = 35 menit..." <?= $canEdit ? '' : 'readonly' ?>><?= SecurityHelper::escape($notes) ?></textarea>
            </div>

            <?php if ($canEdit): ?>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="submit" class="btn btn-primary" style="padding: 10px 28px; font-weight: 700; font-size: 13.5px;">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan Struktur Kurikulum
                    </button>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- TAB 2: PRATINJAU DOKUMEN RESMI (FR – 9023a) -->
    <div id="tabContentPreview" style="display: none; padding: 24px; background: #f1f5f9;">
        <div style="max-width: 860px; margin: 0 auto; background: #ffffff; padding: 36px 40px; border-radius: 8px; box-shadow: 0 4px 14px rgba(0,0,0,0.08); border: 1px solid #cbd5e1; font-family: 'Segoe UI', Arial, sans-serif; color: #111;">
            
            <!-- KOP DOKUMEN -->
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #0f766e; padding-bottom: 10px; margin-bottom: 16px;">
                <div>
                    <h2 style="margin: 0; font-size: 16px; font-weight: 800; color: #047857; text-transform: uppercase;">
                        <?= SecurityHelper::escape($unitName) ?>
                    </h2>
                    <div style="font-size: 12px; color: #475569; font-weight: 600;">YAYASAN PENDIDIKAN ISLAM AL KAHFI BATAM</div>
                </div>
                <div>
                    <table style="border-collapse: collapse; font-size: 11px;">
                        <tr>
                            <td style="border: 1px solid #cbd5e1; padding: 3px 8px; background: #f8fafc; font-weight: 700;">No. Dokumen</td>
                            <td style="border: 1px solid #cbd5e1; padding: 3px 8px; font-weight: 700;"><?= SecurityHelper::escape($docNo) ?></td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #cbd5e1; padding: 3px 8px; background: #f8fafc; font-weight: 700;">Revisi</td>
                            <td style="border: 1px solid #cbd5e1; padding: 3px 8px;"><?= SecurityHelper::escape($rev) ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- JUDUL PENGAJUAN -->
            <div style="text-align: center; margin-bottom: 18px;">
                <h3 style="margin: 0; font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                    PENGAJUAN STRUKTUR KURIKULUM <?= SecurityHelper::escape($unitCode) ?>
                </h3>
                <div style="font-size: 12px; font-weight: 700; color: #475569; margin-top: 3px;">
                    TAHUN AJARAN <?= SecurityHelper::escape($_SESSION['academic_year_name'] ?? '2026-2027') ?>
                </div>
            </div>

            <!-- TABEL DOKUMEN RESMI PREVIEW -->
            <?php if ($isKbtk): ?>
                <table style="width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 16px;">
                    <thead>
                        <tr style="background: #f1f5f9;">
                            <th rowspan="2" style="border: 1px solid #334155; padding: 6px; width: 35px; text-align: center;">NO</th>
                            <th rowspan="2" style="border: 1px solid #334155; padding: 6px; text-align: left;">STRUKTUR PROGRAM</th>
                            <th colspan="2" style="border: 1px solid #334155; padding: 4px; text-align: center;">ALOKASI WAKTU</th>
                            <th rowspan="2" style="border: 1px solid #334155; padding: 6px; width: 140px; text-align: left;">KETERANGAN</th>
                        </tr>
                        <tr style="background: #f1f5f9;">
                            <th style="border: 1px solid #334155; padding: 4px; width: 60px; text-align: center;">KB</th>
                            <th style="border: 1px solid #334155; padding: 4px; width: 60px; text-align: center;">TK</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $currKel = '';
                        foreach ($items as $it): 
                            if ($it['kelompok'] !== $currKel):
                                $currKel = $it['kelompok'];
                        ?>
                            <tr style="background: #e2e8f0; font-weight: 700;">
                                <td style="border: 1px solid #334155; padding: 5px; text-align: center;"><?= SecurityHelper::escape(substr($currKel, -1)) ?></td>
                                <td colspan="4" style="border: 1px solid #334155; padding: 5px;"><?= SecurityHelper::escape($currKel) ?></td>
                            </tr>
                        <?php endif; ?>
                            <tr>
                                <td style="border: 1px solid #334155; padding: 5px; text-align: center;"><?= SecurityHelper::escape($it['item_number']) ?></td>
                                <td style="border: 1px solid #334155; padding: 5px;"><?= SecurityHelper::escape($it['nama_mapel']) ?></td>
                                <td style="border: 1px solid #334155; padding: 5px; text-align: center;"><?= SecurityHelper::escape($it['jp_1'] ?: '-') ?></td>
                                <td style="border: 1px solid #334155; padding: 5px; text-align: center;"><?= SecurityHelper::escape($it['jp_2'] ?: '-') ?></td>
                                <td style="border: 1px solid #334155; padding: 5px;"><?= SecurityHelper::escape($it['keterangan'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php elseif ($isSd): ?>
                <table style="width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 16px;">
                    <thead>
                        <tr style="background: #f1f5f9;">
                            <th rowspan="2" style="border: 1px solid #334155; padding: 5px; width: 30px; text-align: center;">NO</th>
                            <th rowspan="2" style="border: 1px solid #334155; padding: 5px; text-align: left;">MATA PELAJARAN</th>
                            <th colspan="6" style="border: 1px solid #334155; padding: 4px; text-align: center;">ALOKASI WAKTU PER MINGGU</th>
                            <th rowspan="2" style="border: 1px solid #334155; padding: 5px; width: 140px; text-align: left;">KETERANGAN</th>
                        </tr>
                        <tr style="background: #f1f5f9;">
                            <th style="border: 1px solid #334155; padding: 4px; width: 32px; text-align: center;">I</th>
                            <th style="border: 1px solid #334155; padding: 4px; width: 32px; text-align: center;">II</th>
                            <th style="border: 1px solid #334155; padding: 4px; width: 32px; text-align: center;">III</th>
                            <th style="border: 1px solid #334155; padding: 4px; width: 32px; text-align: center;">IV</th>
                            <th style="border: 1px solid #334155; padding: 4px; width: 32px; text-align: center;">V</th>
                            <th style="border: 1px solid #334155; padding: 4px; width: 32px; text-align: center;">VI</th>
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
                            <tr style="background: #e2e8f0; font-weight: 700;">
                                <td colspan="9" style="border: 1px solid #334155; padding: 5px;"><?= SecurityHelper::escape($currKel) ?></td>
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
                                <td style="border: 1px solid #334155; padding: 5px; text-align: center;"><?= SecurityHelper::escape($it['item_number']) ?></td>
                                <td style="border: 1px solid #334155; padding: 5px;"><?= SecurityHelper::escape($it['nama_mapel']) ?></td>
                                <td style="border: 1px solid #334155; padding: 5px; text-align: center;"><?= SecurityHelper::escape($it['jp_1'] ?: '-') ?></td>
                                <td style="border: 1px solid #334155; padding: 5px; text-align: center;"><?= SecurityHelper::escape($it['jp_2'] ?: '-') ?></td>
                                <td style="border: 1px solid #334155; padding: 5px; text-align: center;"><?= SecurityHelper::escape($it['jp_3'] ?: '-') ?></td>
                                <td style="border: 1px solid #334155; padding: 5px; text-align: center;"><?= SecurityHelper::escape($it['jp_4'] ?: '-') ?></td>
                                <td style="border: 1px solid #334155; padding: 5px; text-align: center;"><?= SecurityHelper::escape($it['jp_5'] ?: '-') ?></td>
                                <td style="border: 1px solid #334155; padding: 5px; text-align: center;"><?= SecurityHelper::escape($it['jp_6'] ?: '-') ?></td>
                                <td style="border: 1px solid #334155; padding: 5px;"><?= SecurityHelper::escape($it['keterangan'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr style="background: #f8fafc; font-weight: 700;">
                            <td colspan="2" style="border: 1px solid #334155; padding: 6px; text-align: center;">TOTAL (tanpa kelompok C dan D)</td>
                            <td style="border: 1px solid #334155; padding: 6px; text-align: center;"><?= $tot1 ?></td>
                            <td style="border: 1px solid #334155; padding: 6px; text-align: center;"><?= $tot2 ?></td>
                            <td style="border: 1px solid #334155; padding: 6px; text-align: center;"><?= $tot3 ?></td>
                            <td style="border: 1px solid #334155; padding: 6px; text-align: center;"><?= $tot4 ?></td>
                            <td style="border: 1px solid #334155; padding: 6px; text-align: center;"><?= $tot5 ?></td>
                            <td style="border: 1px solid #334155; padding: 6px; text-align: center;"><?= $tot6 ?></td>
                            <td style="border: 1px solid #334155; padding: 6px;"></td>
                        </tr>
                    </tbody>
                </table>

            <?php elseif ($isSmp): ?>
                <table style="width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 16px;">
                    <thead>
                        <tr style="background: #f1f5f9;">
                            <th rowspan="2" style="border: 1px solid #334155; padding: 5px; width: 30px; text-align: center;">NO</th>
                            <th rowspan="2" style="border: 1px solid #334155; padding: 5px; text-align: left;">MATA PELAJARAN</th>
                            <th colspan="3" style="border: 1px solid #334155; padding: 4px; text-align: center;">ALOKASI WAKTU PER MINGGU</th>
                            <th rowspan="2" style="border: 1px solid #334155; padding: 5px; width: 140px; text-align: left;">KETERANGAN</th>
                        </tr>
                        <tr style="background: #f1f5f9;">
                            <th style="border: 1px solid #334155; padding: 4px; width: 50px; text-align: center;">VII</th>
                            <th style="border: 1px solid #334155; padding: 4px; width: 50px; text-align: center;">VIII</th>
                            <th style="border: 1px solid #334155; padding: 4px; width: 50px; text-align: center;">IX</th>
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
                            <tr style="background: #e2e8f0; font-weight: 700;">
                                <td colspan="6" style="border: 1px solid #334155; padding: 5px;"><?= SecurityHelper::escape($currKel) ?></td>
                            </tr>
                        <?php endif; 
                            if (is_numeric($it['jp_7'])) $tot7 += (int)$it['jp_7'];
                            if (is_numeric($it['jp_8'])) $tot8 += (int)$it['jp_8'];
                            if (is_numeric($it['jp_9'])) $tot9 += (int)$it['jp_9'];
                        ?>
                            <tr>
                                <td style="border: 1px solid #334155; padding: 5px; text-align: center;"><?= SecurityHelper::escape($it['item_number']) ?></td>
                                <td style="border: 1px solid #334155; padding: 5px;"><?= SecurityHelper::escape($it['nama_mapel']) ?></td>
                                <td style="border: 1px solid #334155; padding: 5px; text-align: center;"><?= SecurityHelper::escape($it['jp_7'] ?: '-') ?></td>
                                <td style="border: 1px solid #334155; padding: 5px; text-align: center;"><?= SecurityHelper::escape($it['jp_8'] ?: '-') ?></td>
                                <td style="border: 1px solid #334155; padding: 5px; text-align: center;"><?= SecurityHelper::escape($it['jp_9'] ?: '-') ?></td>
                                <td style="border: 1px solid #334155; padding: 5px;"><?= SecurityHelper::escape($it['keterangan'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr style="background: #f8fafc; font-weight: 700;">
                            <td colspan="2" style="border: 1px solid #334155; padding: 6px; text-align: center;">TOTAL ALOKASI JP PER MINGGU</td>
                            <td style="border: 1px solid #334155; padding: 6px; text-align: center;"><?= $tot7 ?></td>
                            <td style="border: 1px solid #334155; padding: 6px; text-align: center;"><?= $tot8 ?></td>
                            <td style="border: 1px solid #334155; padding: 6px; text-align: center;"><?= $tot9 ?></td>
                            <td style="border: 1px solid #334155; padding: 6px;"></td>
                        </tr>
                    </tbody>
                </table>

            <?php elseif ($isSma): ?>
                <table style="width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 16px;">
                    <thead>
                        <tr style="background: #f1f5f9;">
                            <th rowspan="2" style="border: 1px solid #334155; padding: 5px; width: 30px; text-align: center;">NO</th>
                            <th rowspan="2" style="border: 1px solid #334155; padding: 5px; text-align: left;">MATA PELAJARAN</th>
                            <th colspan="3" style="border: 1px solid #334155; padding: 4px; text-align: center;">ALOKASI WAKTU PER MINGGU</th>
                            <th rowspan="2" style="border: 1px solid #334155; padding: 5px; width: 140px; text-align: left;">KETERANGAN</th>
                        </tr>
                        <tr style="background: #f1f5f9;">
                            <th style="border: 1px solid #334155; padding: 4px; width: 50px; text-align: center;">X</th>
                            <th style="border: 1px solid #334155; padding: 4px; width: 50px; text-align: center;">XI</th>
                            <th style="border: 1px solid #334155; padding: 4px; width: 50px; text-align: center;">XII</th>
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
                            <tr style="background: #e2e8f0; font-weight: 700;">
                                <td colspan="6" style="border: 1px solid #334155; padding: 5px;"><?= SecurityHelper::escape($currKel) ?></td>
                            </tr>
                        <?php endif; 
                            if (is_numeric($it['jp_10'])) $tot10 += (int)$it['jp_10'];
                            if (is_numeric($it['jp_11'])) $tot11 += (int)$it['jp_11'];
                            if (is_numeric($it['jp_12'])) $tot12 += (int)$it['jp_12'];
                        ?>
                            <tr>
                                <td style="border: 1px solid #334155; padding: 5px; text-align: center;"><?= SecurityHelper::escape($it['item_number']) ?></td>
                                <td style="border: 1px solid #334155; padding: 5px;"><?= SecurityHelper::escape($it['nama_mapel']) ?></td>
                                <td style="border: 1px solid #334155; padding: 5px; text-align: center;"><?= SecurityHelper::escape($it['jp_10'] ?: '-') ?></td>
                                <td style="border: 1px solid #334155; padding: 5px; text-align: center;"><?= SecurityHelper::escape($it['jp_11'] ?: '-') ?></td>
                                <td style="border: 1px solid #334155; padding: 5px; text-align: center;"><?= SecurityHelper::escape($it['jp_12'] ?: '-') ?></td>
                                <td style="border: 1px solid #334155; padding: 5px;"><?= SecurityHelper::escape($it['keterangan'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr style="background: #f8fafc; font-weight: 700;">
                            <td colspan="2" style="border: 1px solid #334155; padding: 6px; text-align: center;">TOTAL ALOKASI JP PER MINGGU</td>
                            <td style="border: 1px solid #334155; padding: 6px; text-align: center;"><?= $tot10 ?></td>
                            <td style="border: 1px solid #334155; padding: 6px; text-align: center;"><?= $tot11 ?></td>
                            <td style="border: 1px solid #334155; padding: 6px; text-align: center;"><?= $tot12 ?></td>
                            <td style="border: 1px solid #334155; padding: 6px;"></td>
                        </tr>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php if (!empty($notes)): ?>
                <div style="font-size: 11px; margin-bottom: 20px; padding: 8px 12px; background: #fafafa; border: 1px dashed #cbd5e1; border-radius: 4px;">
                    <strong>Catatan / Keterangan Pembelajaran:</strong><br>
                    <?= nl2br(SecurityHelper::escape($notes)) ?>
                </div>
            <?php endif; ?>

            <!-- DUAL TANDA TANGAN PREVIEW -->
            <div style="display: flex; justify-content: space-between; margin-top: 24px; text-align: center;">
                <div style="flex: 1; padding: 0 10px;">
                    <div style="font-size: 11px; color: #475569;">Diajukan oleh:</div>
                    <div style="font-size: 12px; font-weight: 700;">Kepala Sekolah <?= SecurityHelper::escape($unitCode) ?></div>
                    <div style="font-size: 11px; color: #64748b;">Tanggal: <?= date('d F Y', strtotime($signDateKs)) ?></div>
                    <div style="height: 60px;"></div>
                    <div style="font-size: 12px; font-weight: 700; text-decoration: underline;"><?= SecurityHelper::escape($ksNama) ?></div>
                    <div style="font-size: 11px; color: #64748b;">No. Telp / WA: <?= SecurityHelper::escape($ksTelp ?: '-') ?></div>
                </div>
                <div style="flex: 1; padding: 0 10px;">
                    <div style="font-size: 11px; color: #475569;">Disetujui oleh:</div>
                    <div style="font-size: 12px; font-weight: 700;">Deputi Direktur Pendidikan AIS</div>
                    <div style="font-size: 11px; color: #64748b;">Tanggal: <?= date('d F Y', strtotime($signDateDeputi)) ?></div>
                    <div style="height: 60px;"></div>
                    <div style="font-size: 12px; font-weight: 700; text-decoration: underline;"><?= SecurityHelper::escape($deputiNama) ?></div>
                    <div style="font-size: 11px; color: #64748b;">Deputi Direktur Pendidikan Al Kahfi</div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function switchTab(tab) {
    const tabContentForm = document.getElementById('tabContentForm');
    const tabContentPreview = document.getElementById('tabContentPreview');
    const tabBtnForm = document.getElementById('tabBtnForm');
    const tabBtnPreview = document.getElementById('tabBtnPreview');

    if (tab === 'form') {
        tabContentForm.style.display = 'block';
        tabContentPreview.style.display = 'none';
        tabBtnForm.className = 'btn btn-primary btn-sm';
        tabBtnPreview.className = 'btn btn-outline btn-sm';
    } else {
        tabContentForm.style.display = 'none';
        tabContentPreview.style.display = 'block';
        tabBtnForm.className = 'btn btn-outline btn-sm';
        tabBtnPreview.className = 'btn btn-primary btn-sm';
    }
}

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

            const kelompokVal = quickKelompok ? quickKelompok.value : 'Kelompok A';
            const emptyRow = document.getElementById('emptyRow');
            if (emptyRow) emptyRow.remove();

            const currentRows = tbody.querySelectorAll('.item-row');
            const rowCount = currentRows.length + 1;

            let jpColsHtml = '';
            if (isKbtk) {
                jpColsHtml = `
                    <td><input type="text" name="jp_1[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="2"></td>
                    <td><input type="text" name="jp_2[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="2"></td>
                `;
            } else if (isSd) {
                jpColsHtml = `
                    <td><input type="text" name="jp_1[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="2"></td>
                    <td><input type="text" name="jp_2[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="2"></td>
                    <td><input type="text" name="jp_3[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="2"></td>
                    <td><input type="text" name="jp_4[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="2"></td>
                    <td><input type="text" name="jp_5[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="2"></td>
                    <td><input type="text" name="jp_6[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="2"></td>
                `;
            } else if (isSmp) {
                jpColsHtml = `
                    <td><input type="text" name="jp_7[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="2"></td>
                    <td><input type="text" name="jp_8[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="2"></td>
                    <td><input type="text" name="jp_9[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="2"></td>
                `;
            } else if (isSma) {
                jpColsHtml = `
                    <td><input type="text" name="jp_10[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="2"></td>
                    <td><input type="text" name="jp_11[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="2"></td>
                    <td><input type="text" name="jp_12[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="2"></td>
                `;
            }

            const tr = document.createElement('tr');
            tr.className = 'item-row';
            tr.innerHTML = `
                <td style="text-align: center; color: var(--text-muted); font-weight: 600;" class="row-num">${rowCount}</td>
                <td>
                    <input type="hidden" name="item_id[]" value="0">
                    <input type="hidden" name="order_num[]" value="${rowCount}">
                    <input type="text" name="kelompok[]" style="width: 100%; padding: 5px 8px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px;" value="${escapeHtml(kelompokVal)}">
                </td>
                <td>
                    <input type="text" name="item_number[]" style="width: 100%; padding: 5px 4px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px; text-align: center;" value="${rowCount}">
                </td>
                <td>
                    <input type="text" name="nama_mapel[]" style="width: 100%; padding: 5px 8px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12.5px; font-weight: 700; color: var(--text-main);" value="${escapeHtml(mapelName)}" required>
                </td>
                ${jpColsHtml}
                <td>
                    <input type="text" name="keterangan[]" style="width: 100%; padding: 5px 8px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 12px;" value="">
                </td>
                <td style="text-align: center;">
                    <button type="button" class="btn btn-outline btn-sm btn-del-row" style="padding: 4px 8px; color: #ef4444; border-color: #fca5a5;" title="Hapus Baris">
                        <i class="fa-solid fa-trash-can"></i>
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
