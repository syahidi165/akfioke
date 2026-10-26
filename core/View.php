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
        $currentUnitId = Auth::unitId();
        $currentTaId = Auth::academicYearId();
        $baseUrl = Auth::baseUrl();
        $csrfToken = CSRF::getToken();
        $csrfField = CSRF::inputField();

        // Notifikasi unread
        $unreadNotifCount = $currentUser ? Notification::countUnread($currentUser['id']) : 0;
        $unreadNotifs = $currentUser ? Notification::getUnread($currentUser['id'], 5) : [];

        // Daftar tahun ajaran & unit untuk switcher global di header
        $globalAcademicYears = Database::fetchAll("SELECT * FROM academic_years ORDER BY id DESC");
        $globalUnits = Database::fetchAll("SELECT * FROM units ORDER BY id ASC");
        $currentUnit = Database::fetch("SELECT * FROM units WHERE id = ?", [$currentUnitId]) ?: ($globalUnits[0] ?? []);

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
