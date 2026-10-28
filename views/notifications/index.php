<?php
// views/notifications/index.php
use Core\SecurityHelper;
use Core\Auth;

$baseUrl = Auth::baseUrl();
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
    <div>
        <h1 class="page-title" style="margin: 0 0 6px 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px;">
            <i class="fa-regular fa-bell" style="color: #0284c7;"></i> Notifikasi Sistem
        </h1>
        <p class="page-subtitle" style="margin: 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
            Daftar seluruh riwayat aktivitas, verifikasi, dan pemberitahuan penting pada akun Anda.
        </p>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <?php if (!empty($unreadCount) && $unreadCount > 0): ?>
            <a href="<?= $baseUrl ?>/notifications/mark-all-read" class="btn btn-primary" style="font-size: 0.875rem;">
                <i class="fa-solid fa-check-double" style="margin-right: 6px;"></i> Tandai Semua Sudah Dibaca (<?= $unreadCount ?>)
            </a>
        <?php endif; ?>
        <?php if (!empty($notifications)): ?>
            <a href="<?= $baseUrl ?>/notifications/delete-all" class="btn btn-outline" onclick="return confirm('Apakah Anda yakin ingin menghapus seluruh riwayat notifikasi Anda?');" style="font-size: 0.875rem; color: #dc2626; border-color: #fca5a5;">
                <i class="fa-solid fa-trash-can" style="margin-right: 6px;"></i> Hapus Semua
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden; border-radius: 12px;">
    <div class="card-header" style="padding: 16px 20px; background: #ffffff; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title" style="margin: 0; font-size: 1rem; font-weight: 700;">
            <i class="fa-solid fa-list-ul" style="color: var(--primary); margin-right: 8px;"></i> Riwayat Notifikasi
        </h3>
        <span class="badge badge-secondary" style="font-size: 12px;">
            Total: <?= count($notifications ?? []) ?> Notifikasi
        </span>
    </div>

    <div class="card-body" style="padding: 0;">
        <?php if (empty($notifications)): ?>
            <div style="padding: 48px 20px; text-align: center; color: var(--text-muted);">
                <i class="fa-regular fa-bell-slash" style="font-size: 40px; margin-bottom: 12px; display: block; opacity: 0.4;"></i>
                <h4 style="margin: 0 0 6px 0; font-size: 1rem; color: var(--text-main);">Belum Ada Notifikasi</h4>
                <p style="margin: 0; font-size: 0.875rem;">Seluruh riwayat notifikasi Anda telah bersih.</p>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column;">
                <?php foreach ($notifications as $n): ?>
                    <?php
                        $isUnread = empty($n['is_read']);
                        $bg = $isUnread ? '#f0f9ff' : '#ffffff';
                        $iconClass = match($n['type'] ?? 'info') {
                            'success' => 'fa-solid fa-circle-check',
                            'warning' => 'fa-solid fa-triangle-exclamation',
                            'danger'  => 'fa-solid fa-circle-exclamation',
                            default   => 'fa-solid fa-circle-info'
                        };
                        $iconColor = match($n['type'] ?? 'info') {
                            'success' => '#10b981',
                            'warning' => '#f59e0b',
                            'danger'  => '#ef4444',
                            default   => '#0284c7'
                        };
                        $readUrl = $baseUrl . '/notifications/' . (int)$n['id'] . '/read';
                        $markReadUrl = $baseUrl . '/notifications/' . (int)$n['id'] . '/mark-read';
                        $deleteUrl = $baseUrl . '/notifications/' . (int)$n['id'] . '/delete';
                    ?>
                    <div style="padding: 16px 20px; border-bottom: 1px solid #f1f5f9; background: <?= $bg ?>; display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; transition: background 0.15s;">
                        <div style="display: flex; align-items: flex-start; gap: 14px;">
                            <div style="font-size: 20px; color: <?= $iconColor ?>; margin-top: 2px;">
                                <i class="<?= $iconClass ?>"></i>
                            </div>
                            <div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <strong style="font-size: 0.9375rem; color: var(--text-main); font-weight: 700;">
                                        <?= SecurityHelper::escape($n['title']) ?>
                                    </strong>
                                    <?php if ($isUnread): ?>
                                        <span class="badge badge-info" style="font-size: 10px; padding: 2px 6px;">Baru</span>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size: 0.875rem; color: var(--text-muted); margin-top: 4px; line-height: 1.5;">
                                    <?= SecurityHelper::escape($n['message']) ?>
                                </div>
                                <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 6px;">
                                    <i class="fa-regular fa-clock" style="margin-right: 4px;"></i>
                                    <?= date('d M Y, H:i', strtotime($n['created_at'])) ?> WIB
                                </div>
                            </div>
                        </div>

                        <div style="flex-shrink: 0; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; justify-content: flex-end;">
                            <?php if (!empty($n['url'])): ?>
                                <a href="<?= $readUrl ?>" class="btn btn-primary btn-sm" style="font-size: 12px; white-space: nowrap; padding: 5px 12px;">
                                    Buka Halaman <i class="fa-solid fa-arrow-right" style="margin-left: 4px;"></i>
                                </a>
                            <?php endif; ?>
                            <?php if ($isUnread): ?>
                                <a href="<?= $markReadUrl ?>" class="btn btn-outline btn-sm" title="Tandai Sudah Dibaca" style="font-size: 12px; white-space: nowrap; padding: 5px 10px;">
                                    <i class="fa-solid fa-check" style="margin-right: 4px;"></i> Tandai Dibaca
                                </a>
                            <?php endif; ?>
                            <a href="<?= $deleteUrl ?>" 
                               class="btn btn-outline btn-sm" 
                               onclick="return confirm('Apakah Anda yakin ingin menghapus notifikasi ini dari riwayat?');" 
                               title="Hapus Notifikasi"
                               style="font-size: 12px; color: #dc2626; border-color: #fca5a5; background: #ffffff; padding: 5px 10px; white-space: nowrap; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="fa-regular fa-trash-can"></i> Hapus
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
