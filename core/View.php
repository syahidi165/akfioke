<?php
// core/View.php
namespace Core;

class View {
    public static function render(string $viewPath, array $data = [], ?string $layout = 'layouts/main'): void {
        // Extract variables to local scope
        extract($data);

        // Common global view variables
        $currentUser = Auth::user();
        $activeRole = Auth::activeRole();
        $userRoles = Auth::roles();
        $activeRoleName = $activeRole['name'] ?? '';
        $isYayasanOfficer = in_array($activeRoleName, ['operator_yayasan', 'direktur_pendidikan', 'deputi_pendidikan', 'ketua_yayasan', 'pembina_yayasan'], true);
        $currentUnitId = $isYayasanOfficer ? 0 : Auth::unitId();
        $currentTaId = Auth::academicYearId();
        $baseUrl = Auth::baseUrl();
        $csrfToken = CSRF::getToken();
        $csrfField = CSRF::inputField();

        // Notifikasi unread
        $unreadNotifCount = $currentUser ? Notification::countUnread($currentUser['id']) : 0;
        $unreadNotifs = $currentUser ? Notification::getUnread($currentUser['id'], 5) : [];

        // Fitur Switch User: Operator Yayasan (bebas switch) atau TU / Operator Sekolah berizin
        \App\Controllers\MasterController::ensureSwitchPermissionsTable();

        $isImpersonating = Auth::isImpersonating();
        $impersonator = Auth::impersonator();
        $isOperatorYayasan = Auth::hasRole('operator_yayasan');
        
        $currentUserId = (int)Auth::id();
        $origUserId = $isImpersonating ? (int)($impersonator['id'] ?? $currentUserId) : $currentUserId;
        
        // Cek apakah user asal adalah operator yayasan
        $origIsOperatorYayasan = $isOperatorYayasan;
        if ($isImpersonating && $origUserId > 0) {
            $origRoles = Database::fetchAll("SELECT r.name FROM roles r JOIN user_roles ur ON r.id = ur.role_id WHERE ur.user_id = ?", [$origUserId]);
            $origRoleNames = array_column($origRoles, 'name');
            $origIsOperatorYayasan = in_array('operator_yayasan', $origRoleNames, true);
        }

        // Cek apakah user (atau impersonator) memiliki izin switch
        $hasGrantedPermissions = false;
        if (!$origIsOperatorYayasan && $origUserId > 0) {
            $permCount = (int)(Database::fetch("SELECT COUNT(*) as c FROM user_switch_permissions WHERE user_id = ?", [$origUserId])['c'] ?? 0);
            $hasGrantedPermissions = ($permCount > 0);
        }

        $canSwitchUser = $origIsOperatorYayasan || $isImpersonating || $hasGrantedPermissions;
        $isOperatorYayasanOrImpersonating = $canSwitchUser; // untuk kompatibilitas jika ada view lain yang membaca variabel ini

        $switchableUsers = [];
        if ($canSwitchUser) {
            try {
                \App\Controllers\MasterController::ensureUserRolesUnitColumn();
                $selectCols = "
                    u.id, u.username, u.full_name, u.email,
                    COALESCE(u.unit_id, e.unit_id, 0) as home_unit_id,
                    COALESCE(un.name, 'Yayasan / Pusat') as unit_name,
                    (SELECT r1.display_name FROM roles r1 JOIN user_roles ur1 ON r1.id = ur1.role_id WHERE ur1.user_id = u.id ORDER BY ur1.is_primary DESC, r1.id ASC LIMIT 1) as role_display,
                    (SELECT r1.name FROM roles r1 JOIN user_roles ur1 ON r1.id = ur1.role_id WHERE ur1.user_id = u.id ORDER BY ur1.is_primary DESC, r1.id ASC LIMIT 1) as role_name,
                    GROUP_CONCAT(DISTINCT r.name ORDER BY COALESCE(ur.unit_id, 0) ASC, ur.is_primary DESC, r.id ASC SEPARATOR ',') as all_role_names,
                    GROUP_CONCAT(DISTINCT CONCAT(r.name, '|', r.display_name, '|', COALESCE(ur.unit_id, 0), '|', COALESCE(ur_un.code, '')) ORDER BY COALESCE(ur.unit_id, 0) ASC, ur.is_primary DESC, r.id ASC SEPARATOR ';;') as all_role_entries,
                    GROUP_CONCAT(DISTINCT CASE WHEN COALESCE(ur.unit_id, 0) > 0 THEN ur.unit_id END ORDER BY ur.unit_id ASC SEPARATOR ',') as cross_unit_ids,
                    GROUP_CONCAT(DISTINCT CASE WHEN COALESCE(ur.unit_id, 0) > 0 THEN ur_un.name END ORDER BY ur_un.id ASC SEPARATOR ', ') as cross_unit_names
                ";

                if ($origIsOperatorYayasan) {
                    $switchableUsers = Database::fetchAll("
                        SELECT {$selectCols}
                        FROM users u
                        LEFT JOIN employees e ON u.id = e.user_id AND e.is_deleted = 0
                        LEFT JOIN units un ON COALESCE(u.unit_id, e.unit_id) = un.id
                        LEFT JOIN user_roles ur ON u.id = ur.user_id
                        LEFT JOIN roles r ON ur.role_id = r.id
                        LEFT JOIN units ur_un ON ur.unit_id = ur_un.id
                        WHERE u.is_active = 1
                        GROUP BY u.id, u.username, u.full_name, u.email, un.id, un.name, u.unit_id, e.unit_id
                        ORDER BY un.name ASC, role_display ASC, u.full_name ASC
                    ");
                } else {
                    $switchableUsers = Database::fetchAll("
                        SELECT {$selectCols}
                        FROM users u
                        JOIN user_switch_permissions usp ON u.id = usp.target_user_id
                        LEFT JOIN employees e ON u.id = e.user_id AND e.is_deleted = 0
                        LEFT JOIN units un ON COALESCE(u.unit_id, e.unit_id) = un.id
                        LEFT JOIN user_roles ur ON u.id = ur.user_id
                        LEFT JOIN roles r ON ur.role_id = r.id
                        LEFT JOIN units ur_un ON ur.unit_id = ur_un.id
                        WHERE usp.user_id = ? AND u.is_active = 1
                        GROUP BY u.id, u.username, u.full_name, u.email, un.id, un.name, u.unit_id, e.unit_id
                        ORDER BY un.name ASC, role_display ASC, u.full_name ASC
                    ", [$origUserId]);
                }
            } catch (\Throwable $e) {
                $switchableUsers = [];
            }
        }

        // Daftar tahun ajaran & unit untuk switcher global di header
        $globalAcademicYears = Database::fetchAll("SELECT * FROM academic_years ORDER BY id DESC");
        $globalUnits = Database::fetchAll("SELECT * FROM units ORDER BY id ASC");
        $currentUnit = ($currentUnitId > 0 && !$isYayasanOfficer) 
            ? (Database::fetch("SELECT * FROM units WHERE id = ?", [$currentUnitId]) ?: []) 
            : [];

        // System & Brand Settings
        $appName = Setting::get('app_name', 'Manajemen Sekolah Berbasis Komputer (MASEKO)');
        $appShortName = Setting::get('app_short_name', 'MASEKO');
        $appSubName = Setting::get('app_sub_name', 'Manajemen Sekolah Berbasis Komputer');
        $appLogoRel = Setting::get('app_logo', '');
        $appLogoUrl = '';
        if (!empty($appLogoRel)) {
            $appLogoFile = dirname(__DIR__) . '/public/' . ltrim($appLogoRel, '/');
            if (file_exists($appLogoFile)) {
                $appLogoUrl = $baseUrl . '/public/' . htmlspecialchars(ltrim($appLogoRel, '/'));
            }
        }

        $appFaviconRel = Setting::get('app_favicon', '');
        $appFaviconUrl = '';
        if (!empty($appFaviconRel)) {
            $appFaviconFile = dirname(__DIR__) . '/public/' . ltrim($appFaviconRel, '/');
            if (file_exists($appFaviconFile)) {
                $appFaviconUrl = $baseUrl . '/public/' . htmlspecialchars(ltrim($appFaviconRel, '/'));
            }
        }

        // Start output buffering for the view file
        $baseDir = dirname(__DIR__) . '/views/';
        $viewFile = $baseDir . ltrim($viewPath, '/') . '.php';

        if (!file_exists($viewFile)) {
            die("View [{$viewPath}] tidak ditemukan pada: {$viewFile}");
        }

        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // If layout specified, render inside layout
        if ($layout) {
            $layoutFile = $baseDir . ltrim($layout, '/') . '.php';
            if (file_exists($layoutFile)) {
                include $layoutFile;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
    }
}
