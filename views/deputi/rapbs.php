<?php
// views/deputi/rapbs.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                <span class="badge badge-primary"><?= $isKepsek ? 'Kepala Sekolah' : 'Deputi Pendidikan' ?></span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Format Resmi SOP Pos 501 s.d 509</span>
            </div>
            <h1 class="page-title" style="font-size: 1.5rem; font-weight: 700; color: var(--text-heading, #1e293b); margin: 0;">
                <i class="fa-solid fa-file-invoice-dollar" style="color: #059669; margin-right: 8px;"></i>
                Penyusunan &amp; Monitoring RAPBS Unit Sekolah
            </h1>
            <p style="margin: 4px 0 0 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Tabel Rencana Anggaran Pendapatan dan Belanja Sekolah (RAPBS) serta pengawasan realisasi serapan anggaran.
            </p>
        </div>

        <div style="display: flex; gap: 8px; align-items: center;">
            <button class="btn btn-primary" onclick="openAddItemModal()" style="font-weight: 600;">
                <i class="fa-solid fa-plus"></i> Tambah Pos Anggaran
            </button>
        </div>
    </div>
</div>

<!-- Unit Selector Tabs (untuk Deputi/Direktur) -->
<?php if (!$isKepsek): ?>
    <div style="display: flex; gap: 8px; margin-bottom: 20px; overflow-x: auto; padding-bottom: 4px;">
        <?php foreach ($units as $u): ?>
            <?php $isActive = (int)$u['id'] === $selectedUnitId; ?>
            <a href="<?= $baseUrl ?>/deputi/rapbs?unit_id=<?= $u['id'] ?>" 
               class="btn <?= $isActive ? 'btn-primary' : 'btn-outline' ?>" 
               style="font-size: 0.875rem; font-weight: 600; white-space: nowrap; border-radius: 8px;">
                <i class="fa-solid fa-school"></i> <?= SecurityHelper::escape($u['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Status Banner & Workflow Approval -->
<div class="card" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 18px 24px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
        <div style="font-size: 0.8125rem; color: #64748b; font-weight: 600;">STATUS RAPBS TAHUN AJARAN INI:</div>
        <div style="display: flex; align-items: center; gap: 10px; margin-top: 4px;">
            <?php
                $st = $header['status'] ?? 'Draft';
                $stBadge = match($st) {
                    'Disetujui Yayasan' => ['bg' => '#dcfce7', 'text' => '#15803d', 'icon' => 'fa-check-double'],
                    'Review Deputi'     => ['bg' => '#e0e7ff', 'text' => '#4338ca', 'icon' => 'fa-clipboard-check'],
                    'Diajukan'          => ['bg' => '#fef3c7', 'text' => '#b45309', 'icon' => 'fa-paper-plane'],
                    'Revisi'            => ['bg' => '#fee2e2', 'text' => '#b91c1c', 'icon' => 'fa-triangle-exclamation'],
                    default             => ['bg' => '#f1f5f9', 'text' => '#475569', 'icon' => 'fa-file-lines']
                };
            ?>
            <span class="badge" style="font-size: 0.9375rem; padding: 6px 14px; background: <?= $stBadge['bg'] ?>; color: <?= $stBadge['text'] ?>; font-weight: 700;">
                <i class="fa-solid <?= $stBadge['icon'] ?>"></i> <?= SecurityHelper::escape($st) ?>
            </span>
            <?php if (!empty($header['reviewed_by_deputi_id'])): ?>
                <span style="font-size: 0.8125rem; color: #64748b;">
                    Direview Deputi: <strong><?= SecurityHelper::escape($header['deputi_name'] ?? 'Deputi') ?></strong>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Workflow Action Buttons -->
    <div style="display: flex; gap: 8px;">
        <?php if ($header): ?>
            <?php if ($isKepsek && in_array($st, ['Draft', 'Revisi'])): ?>
                <form action="<?= $baseUrl ?>/deputi/rapbs/update-status" method="POST" onsubmit="return confirm('Ajukan draf RAPBS ke Deputi Pendidikan?')">
                    <?= CSRF::inputField() ?>
                    <input type="hidden" name="header_id" value="<?= $header['id'] ?>">
                    <input type="hidden" name="unit_id" value="<?= $selectedUnitId ?>">
                    <input type="hidden" name="status" value="Diajukan">
                    <button type="submit" class="btn btn-primary" style="font-weight: 600;">
                        <i class="fa-solid fa-paper-plane"></i> Ajukan ke Deputi
                    </button>
                </form>
            <?php elseif (Auth::hasRole('deputi_pendidikan') && in_array($st, ['Diajukan', 'Draft'])): ?>
                <form action="<?= $baseUrl ?>/deputi/rapbs/update-status" method="POST" onsubmit="return confirm('Verifikasi & teruskan RAPBS ke Yayasan?')">
                    <?= CSRF::inputField() ?>
                    <input type="hidden" name="header_id" value="<?= $header['id'] ?>">
                    <input type="hidden" name="unit_id" value="<?= $selectedUnitId ?>">
                    <input type="hidden" name="status" value="Review Deputi">
                    <button type="submit" class="btn btn-primary" style="font-weight: 600; background: #4338ca; border-color: #4338ca;">
                        <i class="fa-solid fa-clipboard-check"></i> Verifikasi &amp; Teruskan ke Yayasan
                    </button>
                </form>
            <?php elseif (Auth::hasAnyRole(['direktur_pendidikan', 'operator_yayasan', 'pembina_yayasan']) && $st !== 'Disetujui Yayasan'): ?>
                <form action="<?= $baseUrl ?>/deputi/rapbs/update-status" method="POST" onsubmit="return confirm('Sahkan RAPBS untuk unit ini?')">
                    <?= CSRF::inputField() ?>
                    <input type="hidden" name="header_id" value="<?= $header['id'] ?>">
                    <input type="hidden" name="unit_id" value="<?= $selectedUnitId ?>">
                    <input type="hidden" name="status" value="Disetujui Yayasan">
                    <button type="submit" class="btn btn-primary" style="font-weight: 600; background: #059669; border-color: #059669;">
                        <i class="fa-solid fa-check-double"></i> Sahkan RAPBS (Yayasan)
                    </button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ========================================================================= -->
<!-- SUMMARY PAGE TABLE (PERSIS GAMBAR FORMAT RAPBS.JPEG)                      -->
<!-- ========================================================================= -->
<div class="card" style="background: var(--bg-card, #ffffff); border-radius: 12px; border: 1px solid var(--border-color, #e2e8f0); box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 24px;">
    <div style="background: #fed7aa; padding: 12px 20px; border-bottom: 1px solid #fdba74; font-weight: 800; font-size: 0.9375rem; color: #9a3412; text-transform: uppercase;">
        SUMMARY PAGE - RENCANA ANGGARAN PENDAPATAN DAN BELANJA SEKOLAH (RAPBS)
    </div>

    <div class="table-responsive">
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8fafc; text-align: left; font-size: 0.8125rem; font-weight: 700; color: #1e293b; border-bottom: 2px solid #cbd5e1;">
                    <th style="padding: 10px 16px; width: 60px; text-align: center;">No</th>
                    <th style="padding: 10px 16px;">KETERANGAN</th>
                    <th style="padding: 10px 16px; width: 180px; text-align: right;">Real Pengeluaran</th>
                    <th style="padding: 10px 16px; width: 200px; text-align: right; background: #f0fdf4;">Plan Pengeluaran di RAPBS</th>
                    <th style="padding: 10px 16px; width: 180px; text-align: center; background: #f0fdf4;">% Penggunaan Anggaran</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $grandPlan = 0.0;
                $grandReal = 0.0;
                $rowIdx = 1;

                foreach ($summaryPos as $pCode => $p):
                    $grandPlan += $p['plan_total'];
                    $grandReal += $p['real_total'];
                    $pct = $p['pct'];

                    // Color indicator logic persis gambar format rapbs:
                    // Merah jika overbudget > 100%
                    // Kuning/Oranye jika rendah < 50%
                    // Hijau jika on-track 50% - 100%
                    if ($pct > 100.0) {
                        $pctBg = '#ef4444'; $pctColor = '#ffffff';
                    } elseif ($pct < 50.0 && $pct > 0.0) {
                        $pctBg = '#f59e0b'; $pctColor = '#ffffff';
                    } elseif ($pct >= 50.0 && $pct <= 100.0) {
                        $pctBg = '#22c55e'; $pctColor = '#ffffff';
                    } else {
                        $pctBg = '#f1f5f9'; $pctColor = '#64748b';
                    }
                ?>
                    <!-- Pos Header Row -->
                    <tr style="background: #e2e8f0; font-weight: 800; border-top: 1px solid #cbd5e1;">
                        <td style="padding: 8px 16px; text-align: center;"><?= $pCode ?></td>
                        <td style="padding: 8px 16px;" colspan="4">
                            <?= SecurityHelper::escape($p['pos_name']) ?>
                        </td>
                    </tr>

                    <!-- Item Rows -->
                    <?php if (!empty($p['items'])): ?>
                        <?php foreach ($p['items'] as $itIdx => $it): ?>
                            <?php
                                $itPlan = (float)$it['plan_amount'];
                                $itReal = (float)$it['real_amount'];
                                $itPct = $itPlan > 0 ? round(($itReal / $itPlan) * 100, 2) : 0.0;
                                if ($itPct > 100.0) {
                                    $itPctBg = '#ef4444'; $itPctColor = '#ffffff';
                                } elseif ($itPct < 50.0 && $itPct > 0.0) {
                                    $itPctBg = '#f59e0b'; $itPctColor = '#ffffff';
                                } elseif ($itPct >= 50.0 && $itPct <= 100.0) {
                                    $itPctBg = '#22c55e'; $itPctColor = '#ffffff';
                                } else {
                                    $itPctBg = '#f8fafc'; $itPctColor = '#64748b';
                                }
                            ?>
                            <tr style="border-bottom: 1px solid #f1f5f9; font-size: 0.8125rem;">
                                <td style="padding: 6px 16px; text-align: center; color: #64748b;"><?= $itIdx + 1 ?></td>
                                <td style="padding: 6px 16px; color: #334155;">
                                    <?= SecurityHelper::escape($it['keterangan']) ?>
                                </td>
                                <td style="padding: 6px 16px; text-align: right; font-weight: 600;">
                                    Rp <?= number_format($itReal, 0, ',', '.') ?>
                                </td>
                                <td style="padding: 6px 16px; text-align: right; font-weight: 600; background: #fafafa;">
                                    Rp <?= number_format($itPlan, 0, ',', '.') ?>
                                </td>
                                <td style="padding: 6px 16px; text-align: center; background: <?= $itPctBg ?>; color: <?= $itPctColor ?>; font-weight: 700;">
                                    <?= number_format($itPct, 2, ',', '.') ?>%
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Pos Subtotal Row -->
                    <tr style="background: #f8fafc; font-weight: 700; border-bottom: 2px solid #e2e8f0; font-size: 0.8125rem;">
                        <td style="padding: 8px 16px;"></td>
                        <td style="padding: 8px 16px; color: #475569;">
                            <em>Subtotal <?= SecurityHelper::escape($p['pos_name']) ?></em>
                        </td>
                        <td style="padding: 8px 16px; text-align: right; color: #1e293b;">
                            Rp <?= number_format($p['real_total'], 0, ',', '.') ?>
                        </td>
                        <td style="padding: 8px 16px; text-align: right; color: #1e293b; background: #f0fdf4;">
                            Rp <?= number_format($p['plan_total'], 0, ',', '.') ?>
                        </td>
                        <td style="padding: 8px 16px; text-align: center; background: <?= $pctBg ?>; color: <?= $pctColor ?>; font-weight: 800;">
                            <?= number_format($pct, 2, ',', '.') ?>%
                        </td>
                    </tr>
                <?php endforeach; ?>

                <!-- Grand Total Row -->
                <?php
                    $grandPct = $grandPlan > 0 ? round(($grandReal / $grandPlan) * 100, 2) : 0.0;
                ?>
                <tr style="background: #0f172a; color: #ffffff; font-weight: 800; font-size: 0.9375rem;">
                    <td style="padding: 12px 16px; text-align: center;">TOTAL</td>
                    <td style="padding: 12px 16px;">TOTAL KESELURUHAN PENGELUARAN</td>
                    <td style="padding: 12px 16px; text-align: right; color: #f8fafc;">
                        Rp <?= number_format($grandReal, 0, ',', '.') ?>
                    </td>
                    <td style="padding: 12px 16px; text-align: right; color: #4ade80;">
                        Rp <?= number_format($grandPlan, 0, ',', '.') ?>
                    </td>
                    <td style="padding: 12px 16px; text-align: center; background: <?= $grandPct > 100 ? '#ef4444' : '#22c55e' ?>; color: #ffffff; font-size: 1rem;">
                        <?= number_format($grandPct, 2, ',', '.') ?>%
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL TAMBAH ITEM ANGGARAN                                                 -->
<!-- ========================================================================= -->
<div class="modal-overlay" id="addItemModal">
    <div class="modal-container" style="max-width: 550px;">
        <form action="<?= $baseUrl ?>/deputi/rapbs/save-item" method="POST">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="unit_id" value="<?= $selectedUnitId ?>">
            <input type="hidden" name="header_id" value="<?= $header['id'] ?? 0 ?>">

            <div class="modal-header">
                <h3 class="card-title" style="margin: 0; font-size: 1.125rem;">
                    <i class="fa-solid fa-plus-circle" style="color: #059669;"></i> Tambah Rincian Item Anggaran
                </h3>
            </div>
            <div class="modal-body" style="display: flex; flex-direction: column; gap: 14px;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Pos Anggaran Standar (501 - 509) *</label>
                    <select name="pos_code" class="form-control" required>
                        <?php foreach ($posDefinitions as $pCode => $pName): ?>
                            <option value="<?= $pCode ?>"><?= SecurityHelper::escape($pName) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Keterangan / Uraian Mata Anggaran *</label>
                    <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Belajar Keluar (Outbond - TK 1) atau Listrik Air" required>
                </div>
                <div class="row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Plan Anggaran (Rp) *</label>
                        <input type="number" step="1000" min="0" name="plan_amount" class="form-control" placeholder="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Realisasi Saat Ini (Rp)</label>
                        <input type="number" step="1000" min="0" name="real_amount" class="form-control" value="0">
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="btn btn-outline" onclick="closeAddItemModal()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan Pos Anggaran</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddItemModal() {
    document.getElementById('addItemModal').classList.add('active');
}
function closeAddItemModal() {
    document.getElementById('addItemModal').classList.remove('active');
}
</script>

