<?php
// views/deputi/units.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div>
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
            <span class="badge badge-primary">Direktur &amp; Yayasan</span>
            <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Struktur Organisasi &amp; Wilayah Binaan</span>
        </div>
        <h1 class="page-title" style="font-size: 1.5rem; font-weight: 700; color: var(--text-heading, #1e293b); margin: 0;">
            <i class="fa-solid fa-sitemap" style="color: #4f46e5; margin-right: 8px;"></i>
            Penugasan Unit Binaan Deputi Pendidikan
        </h1>
        <p style="margin: 4px 0 0 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
            Atur pembagian wewenang unit sekolah (KB, TK, SD, SMP, SMA) yang dibawahi oleh masing-masing Deputi Pendidikan.
        </p>
    </div>
</div>

<div class="row" style="display: flex; flex-direction: column; gap: 20px;">
    <?php if (empty($deputiList)): ?>
        <div class="card" style="padding: 30px; text-align: center; color: var(--text-muted);">
            <i class="fa-solid fa-user-slash" style="font-size: 2.5rem; margin-bottom: 12px; color: #94a3b8;"></i>
            <p>Belum ada pengguna dengan peran <strong>Deputi Pendidikan</strong> yang terdaftar di sistem.</p>
        </div>
    <?php else: ?>
        <?php foreach ($deputiList as $deputi): ?>
            <?php 
                $uId = (int)$deputi['id'];
                $assigned = $deputiUnitsMap[$uId] ?? [];
            ?>
            <div class="card" style="background: var(--bg-card, #ffffff); border-radius: 12px; border: 1px solid var(--border-color, #e2e8f0); box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 24px;">
                <form action="<?= $baseUrl ?>/deputi/units/save" method="POST">
                    <?= CSRF::inputField() ?>
                    <input type="hidden" name="user_id" value="<?= $uId ?>">

                    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <div style="width: 48px; height: 48px; border-radius: 50%; background: #e0e7ff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 700;">
                                <?= strtoupper(substr($deputi['full_name'], 0, 1)) ?>
                            </div>
                            <div>
                                <h3 style="margin: 0; font-size: 1.125rem; font-weight: 700; color: var(--text-heading, #1e293b);">
                                    <?= SecurityHelper::escape($deputi['full_name']) ?>
                                </h3>
                                <div style="font-size: 0.8125rem; color: var(--text-muted, #64748b); margin-top: 2px;">
                                    Username: <code><?= SecurityHelper::escape($deputi['username']) ?></code> 
                                    <?php if (!empty($deputi['contact_phone'])): ?>
                                        &bull; Telp: <?= SecurityHelper::escape($deputi['contact_phone']) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" style="padding: 8px 18px; font-weight: 600;">
                            <i class="fa-solid fa-save"></i> Simpan Penugasan Unit
                        </button>
                    </div>

                    <div style="background: #f8fafc; border-radius: 8px; padding: 16px; border: 1px solid #e2e8f0;">
                        <div style="font-weight: 600; font-size: 0.875rem; color: #334155; margin-bottom: 12px;">
                            Pilih Unit Sekolah yang Dibawahi:
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 12px;">
                            <?php foreach ($allUnits as $unit): ?>
                                <?php 
                                    $unitId = (int)$unit['id'];
                                    $isChecked = in_array($unitId, $assigned, true);
                                ?>
                                <label style="display: flex; align-items: center; gap: 10px; background: #ffffff; padding: 10px 14px; border-radius: 8px; border: 1px solid <?= $isChecked ? '#818cf8' : '#e2e8f0' ?>; cursor: pointer; transition: all 0.2s;">
                                    <input type="checkbox" name="units[]" value="<?= $unitId ?>" <?= $isChecked ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: #4f46e5;">
                                    <div>
                                        <div style="font-weight: 600; font-size: 0.875rem; color: #1e293b;">
                                            <?= SecurityHelper::escape($unit['name']) ?>
                                        </div>
                                        <div style="font-size: 0.75rem; color: #64748b;">
                                            Jenjang: <span class="badge" style="padding: 2px 6px; font-size: 10px;"><?= SecurityHelper::escape($unit['level']) ?></span>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

