<?php
// views/layouts/navbar.php
use Core\SecurityHelper;

$activeRoleName = $activeRole['name'] ?? '';
$isYayasanOfficer = in_array($activeRoleName, ['operator_yayasan', 'direktur_pendidikan', 'deputi_pendidikan', 'ketua_yayasan', 'pembina_yayasan'], true);

// Resolusi logo unit aktif
$unitLogoUrl = '';
if (!empty($currentUnit['logo_path'])) {
    $logoRel = ltrim($currentUnit['logo_path'], '/');
    $logoFullPath = dirname(__DIR__, 2) . '/public/' . $logoRel;
    if (file_exists($logoFullPath)) {
        $unitLogoUrl = $baseUrl . '/public/' . htmlspecialchars($logoRel);
    }
}
?>
<header class="app-header">
    <div class="header-left">
        <button type="button" class="sidebar-toggle-btn" id="sidebarToggle" title="Toggle Sidebar">
            <i class="fa-solid fa-bars"></i>
        </button>
        
        <!-- Unit / Yayasan Badge -->
        <?php if ($isYayasanOfficer): ?>
            <div class="header-badge-wrap">
                <span class="badge header-yayasan-badge" title="Yayasan Islam Al Kahfi">
                    <i class="fa-solid fa-building-columns"></i>
                    <span class="badge-text-full">Yayasan Islam Al Kahfi</span>
                    <span class="badge-text-short">Al Kahfi</span>
                </span>
            </div>
        <?php elseif (!empty($currentUnit)): ?>
            <div class="header-badge-wrap">
                <?php if (!empty($unitLogoUrl)): ?>
                    <img src="<?= $unitLogoUrl ?>" alt="Logo" class="header-unit-thumb">
                <?php endif; ?>
                <span class="badge badge-info header-unit-badge" title="<?= SecurityHelper::escape($currentUnit['name']) ?>">
                    <i class="fa-solid fa-school"></i>
                    <span><?= SecurityHelper::escape($currentUnit['name']) ?></span>
                </span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Desktop Switchers (Tahun Ajaran & Peran) - Khusus Layar Komputer / Tablet Lebar -->
    <div class="header-switchers-desktop">
        <!-- Filter Tahun Ajaran Global -->
        <form method="POST" action="<?= $baseUrl ?>/switch-ta" class="header-switch-form">
            <?= $csrfField ?>
            <label for="taSelectDesktop" class="header-switch-label">
                <i class="fa-regular fa-calendar-check"></i> TA:
            </label>
            <select name="academic_year_id" id="taSelectDesktop" class="header-select" onchange="this.form.submit()">
                <?php foreach ($globalAcademicYears as $ta): ?>
                    <option value="<?= $ta['id'] ?>" <?= ((int)$ta['id'] === $currentTaId) ? 'selected' : '' ?>>
                        <?= SecurityHelper::escape($ta['name']) ?> - <?= SecurityHelper::escape($ta['semester']) ?> <?= $ta['is_active'] ? '★' : '(Arsip)' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <!-- Role Switcher (Pengalih Role Aktif) -->
        <?php if (count($userRoles) > 1): ?>
            <form method="POST" action="<?= $baseUrl ?>/switch-role" class="header-switch-form">
                <?= $csrfField ?>
                <label for="roleSelectDesktop" class="header-switch-label">
                    <i class="fa-solid fa-repeat"></i> Peran:
                </label>
                <select name="role_id" id="roleSelectDesktop" class="header-select header-select-role" onchange="this.form.submit()">
                    <?php foreach ($userRoles as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= ((int)$r['id'] === (int)$activeRole['id']) ? 'selected' : '' ?>>
                            <?= SecurityHelper::escape($r['display_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        <?php else: ?>
            <span class="badge badge-secondary" style="font-size: 12px;">
                <i class="fa-solid fa-user-shield" style="margin-right: 4px;"></i>
                <?= SecurityHelper::escape($activeRole['display_name']) ?>
            </span>
        <?php endif; ?>
    </div>

    <div class="header-right">
        <!-- Tombol Pasang Aplikasi (PWA Install Button) -->
        <button type="button" class="btn-pwa-nav pwa-install-btn" id="pwaInstallBtn" onclick="window.promptPwaInstall()" title="Pasang Aplikasi di HP">
            <i class="fa-solid fa-mobile-screen-button"></i>
            <span class="pwa-nav-text">Pasang</span>
        </button>

        <!-- Notifikasi Lonceng -->
        <div style="position: relative;" id="notifDropdownContainer">
            <button type="button" class="sidebar-toggle-btn" id="notifBtn" style="position: relative;" title="Notifikasi">
                <i class="fa-regular fa-bell" style="font-size: 18px;"></i>
                <?php if ($unreadNotifCount > 0): ?>
                    <span class="notif-badge-pill">
                        <?= $unreadNotifCount > 9 ? '9+' : $unreadNotifCount ?>
                    </span>
                <?php endif; ?>
            </button>

            <!-- Dropdown Notifikasi -->
            <div id="notifMenu" style="display: none; position: absolute; right: 0; top: 48px; width: 320px; background: #ffffff; border: 1px solid var(--border-color); border-radius: 10px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); z-index: 1000; overflow: hidden;">
                <div style="padding: 12px 16px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 600; font-size: 13px;">Notifikasi</span>
                    <?php if ($unreadNotifCount > 0): ?>
                        <a href="<?= $baseUrl ?>/notifications/mark-all-read" style="font-size: 11px; color: var(--primary);">Tandai semua dibaca</a>
                    <?php endif; ?>
                </div>
                <div style="max-height: 280px; overflow-y: auto;">
                    <?php if (empty($unreadNotifs)): ?>
                        <div style="padding: 20px; text-align: center; color: var(--text-muted); font-size: 13px;">
                            <i class="fa-regular fa-bell-slash" style="font-size: 24px; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                            Tidak ada notifikasi baru
                        </div>
                    <?php else: ?>
                        <?php foreach ($unreadNotifs as $notif): ?>
                            <a href="<?= $baseUrl ?>/notifications/<?= (int)$notif['id'] ?>/read" style="display: block; padding: 11px 16px; border-bottom: 1px solid #f1f5f9; text-decoration: none; color: inherit; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 8px;">
                                    <div style="font-weight: 600; font-size: 12px; color: var(--primary);"><?= SecurityHelper::escape($notif['title']) ?></div>
                                    <span style="width: 7px; height: 7px; border-radius: 50%; background: #3b82f6; display: inline-block; margin-top: 4px; flex-shrink: 0;" title="Belum dibaca"></span>
                                </div>
                                <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px; line-height: 1.4;"><?= SecurityHelper::escape($notif['message']) ?></div>
                                <div style="font-size: 10px; color: #94a3b8; margin-top: 4px;">
                                    <i class="fa-regular fa-clock" style="font-size: 9px; margin-right: 3px;"></i><?= date('d M H:i', strtotime($notif['created_at'])) ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div style="padding: 8px 16px; background: #f8fafc; border-top: 1px solid var(--border-color); text-align: center;">
                    <a href="<?= $baseUrl ?>/notifications" style="font-size: 11.5px; color: var(--primary); font-weight: 600; text-decoration: none;">
                        Lihat Semua Notifikasi &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Profil & Logout -->
        <div class="header-user-profile">
            <div class="header-user-info">
                <div class="header-user-name">
                    <?= SecurityHelper::escape($currentUser['name'] ?? 'User') ?>
                </div>
                <div class="header-user-username">
                    @<?= SecurityHelper::escape($currentUser['username'] ?? '') ?>
                </div>
            </div>
            <a href="<?= $baseUrl ?>/logout" class="btn-logout" title="Keluar">
                <i class="fa-solid fa-power-off"></i>
            </a>
        </div>

        <!-- Logo Unit (Desktop Saja) -->
        <?php if (!empty($unitLogoUrl)): ?>
            <div class="header-unit-logo" title="<?= SecurityHelper::escape($currentUnit['name'] ?? 'Unit') ?>">
                <img src="<?= $unitLogoUrl ?>" 
                     alt="<?= SecurityHelper::escape($currentUnit['name'] ?? 'Logo Unit') ?>">
            </div>
        <?php elseif (!empty($currentUnit['code']) && !$isYayasanOfficer): ?>
            <div class="header-unit-logo" title="<?= SecurityHelper::escape($currentUnit['name'] ?? 'Unit') ?>">
                <div class="header-unit-code">
                    <?= SecurityHelper::escape($currentUnit['code'] ?? 'AK') ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</header>

<!-- Mobile Context Bar: Selector Tahun Ajaran & Peran Ramping Khusus HP (<= 768px) -->
<div class="mobile-context-bar">
    <form method="POST" action="<?= $baseUrl ?>/switch-ta" class="mobile-ctx-form">
        <?= $csrfField ?>
        <div class="mobile-ctx-pill">
            <i class="fa-regular fa-calendar-check mobile-ctx-icon"></i>
            <select name="academic_year_id" class="mobile-ctx-select" onchange="this.form.submit()">
                <?php foreach ($globalAcademicYears as $ta): ?>
                    <option value="<?= $ta['id'] ?>" <?= ((int)$ta['id'] === $currentTaId) ? 'selected' : '' ?>>
                        <?= SecurityHelper::escape($ta['name']) ?> <?= SecurityHelper::escape($ta['semester']) ?> <?= $ta['is_active'] ? '★' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <?php if (count($userRoles) > 1): ?>
        <form method="POST" action="<?= $baseUrl ?>/switch-role" class="mobile-ctx-form">
            <?= $csrfField ?>
            <div class="mobile-ctx-pill mobile-ctx-role">
                <i class="fa-solid fa-repeat mobile-ctx-icon"></i>
                <select name="role_id" class="mobile-ctx-select" onchange="this.form.submit()">
                    <?php foreach ($userRoles as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= ((int)$r['id'] === (int)$activeRole['id']) ? 'selected' : '' ?>>
                            <?= SecurityHelper::escape($r['display_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    <?php endif; ?>
</div>
