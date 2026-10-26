<?php
// app/Controllers/ChecklistController.php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use Core\AuditLog;
use Core\SecurityHelper;

class ChecklistController extends Controller {

    // --- 1. CHECKLIST SECURITY HARIAN (LOCKED TO SERVER DATE) ---
    public function securityIndex(): void {
        $this->requireRole(['security', 'kepala_sekolah', 'deputi_pendidikan']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $today = date('Y-m-d');
        $userId = Auth::id();

        $emp = Database::fetch("SELECT id FROM employees WHERE user_id = ?", [$userId]);
        $empId = $emp['id'] ?? 1;

        // Cek apakah hari ini sudah diisi
        $todayCheck = Database::fetch(
            "SELECT * FROM checklists_security WHERE employee_id = ? AND check_date = ?",
            [$empId, $today]
        );

        $savedItems = $todayCheck ? json_decode($todayCheck['check_items_json'], true) : [];

        $history = Database::fetchAll(
            "SELECT cs.*, e.nama as officer_name 
             FROM checklists_security cs 
             JOIN employees e ON cs.employee_id = e.id 
             WHERE cs.unit_id = ? AND cs.academic_year_id = ? 
             ORDER BY cs.check_date DESC LIMIT 30",
            [$unitId, $taId]
        );

        $items = [
            1 => 'Seragam PDL, atribut lengkap, sepatu laras, dan kartu identitas',
            2 => 'Pemeriksaan buku mutasi jaga dan serah terima inventaris pos',
            3 => 'Pemeriksaan fungsi CCTV, monitor, dan radio komunikasi HT',
            4 => 'Pengaturan kelancaran lalu lintas penjemputan/antar santri',
            5 => 'Pemeriksaan tamu masuk (identitas, buku tamu, dan kartu visitor)',
            6 => 'Pemeriksaan pintu gerbang, pagar perimeter, dan kunci kelas malam',
            7 => 'Pemeriksaan potensi bahaya api, listrik, kran air, dan tabung APAR'
        ];

        $this->view('checklist/security', [
            'pageTitle'  => 'Checklist Harian Petugas Keamanan (Security)',
            'today'      => $today,
            'todayCheck' => $todayCheck,
            'savedItems' => $savedItems,
            'items'      => $items,
            'history'    => $history
        ]);
    }

    public function securitySave(): void {
        $this->requireRole('security');
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $userId = Auth::id();
        $today = date('Y-m-d'); // Strict server date

        $emp = Database::fetch("SELECT id FROM employees WHERE user_id = ?", [$userId]);
        $empId = $emp['id'] ?? 1;

        $checkItems = $_POST['check'] ?? [];
        $notes = trim($_POST['notes'] ?? '');
        $isOnTime = (date('H:i') <= '07:15') ? 1 : 0;
        $isComplete = count($checkItems) >= 7 ? 1 : 0;

        Database::execute(
            "INSERT INTO checklists_security (unit_id, employee_id, academic_year_id, check_date, is_complete, is_on_time, check_items_json, notes) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
             ON DUPLICATE KEY UPDATE is_complete = VALUES(is_complete), is_on_time = VALUES(is_on_time), check_items_json = VALUES(check_items_json), notes = VALUES(notes)",
            [$unitId, $empId, $taId, $today, $isComplete, $isOnTime, json_encode($checkItems), $notes]
        );

        AuditLog::log('SAVE_SECURITY_CHECKLIST', 'checklists_security', $empId, null, ['date' => $today, 'is_complete' => $isComplete]);
        $this->setFlash('success', 'Checklist pos jaga hari ini (' . SecurityHelper::formatTanggalIndo($today) . ') berhasil disimpan.');
        $this->redirect(Auth::baseUrl() . '/checklist/security');
    }

    // --- 2. BUKU PATROLI SECURITY ---
    public function securityPatrol(): void {
        $this->requireRole(['security', 'kepala_sekolah']);
        $unitId = Auth::unitId();

        $patrols = Database::fetchAll(
            "SELECT sp.*, e.nama as officer_name 
             FROM security_patrols sp 
             JOIN employees e ON sp.employee_id = e.id 
             WHERE sp.unit_id = ? 
             ORDER BY sp.patrol_date DESC, sp.actual_time DESC LIMIT 50",
            [$unitId]
        );

        $this->view('checklist/patrol', [
            'pageTitle' => 'Buku Jurnal Patroli Keliling Security',
            'patrols'   => $patrols
        ]);
    }

    public function securityPatrolSave(): void {
        $this->requireRole('security');
        $unitId = Auth::unitId();
        $userId = Auth::id();
        $emp = Database::fetch("SELECT id FROM employees WHERE user_id = ?", [$userId]);
        $empId = $emp['id'] ?? 1;

        $patrolDate = date('Y-m-d');
        $scheduleTime = $_POST['schedule_time'] ?? date('H:00:00');
        $actualTime = date('H:i:s');
        $location = trim($_POST['location'] ?? 'Area Perimeter');
        $findings = trim($_POST['findings'] ?? 'Aman, lampu dan jendela terkunci.');

        Database::execute(
            "INSERT INTO security_patrols (unit_id, employee_id, patrol_date, schedule_time, actual_time, location, findings) 
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$unitId, $empId, $patrolDate, $scheduleTime, $actualTime, $location, $findings]
        );

        $this->setFlash('success', 'Catatan patroli keliling berhasil disimpan.');
        $this->redirect(Auth::baseUrl() . '/security/patrol');
    }

    // --- 3. BUKU KEJADIAN SECURITY (INCIDENTS) ---
    public function securityIncidents(): void {
        $this->requireRole(['security', 'kepala_sekolah', 'deputi_pendidikan']);
        $unitId = Auth::unitId();

        $incidents = Database::fetchAll(
            "SELECT * FROM security_incidents WHERE unit_id = ? ORDER BY incident_date DESC, incident_time DESC",
            [$unitId]
        );

        $this->view('checklist/incidents', [
            'pageTitle' => 'Buku Kejadian & Laporan Insiden Keamanan',
            'incidents' => $incidents
        ]);
    }

    public function securityIncidentSave(): void {
        $this->requireRole('security');
        $unitId = Auth::unitId();

        $category = $_POST['category'] ?? 'Kehilangan';
        $desc = trim($_POST['description'] ?? '');
        $action = trim($_POST['action_taken'] ?? '');
        $isPrevented = !empty($_POST['is_prevented']) ? 1 : 0;
        $isBreach = !empty($_POST['is_breach']) ? 1 : 0;
        $rating = (int)($_POST['rating_response'] ?? 5);

        Database::execute(
            "INSERT INTO security_incidents (unit_id, incident_date, incident_time, category, description, action_taken, is_prevented, is_breach, rating_response) 
             VALUES (?, CURDATE(), CURTIME(), ?, ?, ?, ?, ?, ?)",
            [$unitId, $category, $desc, $action, $isPrevented, $isBreach, $rating]
        );

        $this->setFlash('success', 'Laporan kejadian berhasil dicatat dalam buku insiden.');
        $this->redirect(Auth::baseUrl() . '/security/incidents');
    }

    // --- 4. CHECKLIST CLEANING SERVICE ---
    public function csIndex(): void {
        $this->requireRole(['cleaning_service', 'kepala_sekolah', 'deputi_pendidikan']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        $checklists = Database::fetchAll(
            "SELECT c.*, e.nama as officer_name, u.full_name as verifier_name 
             FROM checklists_cs c 
             JOIN employees e ON c.employee_id = e.id 
             LEFT JOIN users u ON c.verified_by_user_id = u.id 
             WHERE c.unit_id = ? AND c.academic_year_id = ? 
             ORDER BY c.check_date DESC LIMIT 50",
            [$unitId, $taId]
        );

        $this->view('checklist/cs', [
            'pageTitle'  => 'Checklist Kebersihan Lingkungan (Cleaning Service)',
            'checklists' => $checklists
        ]);
    }

    public function csSave(): void {
        $this->requireRole(['cleaning_service', 'kepala_sekolah']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $userId = Auth::id();
        $emp = Database::fetch("SELECT id FROM employees WHERE user_id = ?", [$userId]);
        $empId = $emp['id'] ?? 1;

        $checkType = $_POST['check_type'] ?? 'Rutin Harian';
        $checkDate = $_POST['check_date'] ?? date('Y-m-d');
        $monthNum = (int)date('n', strtotime($checkDate));
        $notes = trim($_POST['notes'] ?? 'Kebersihan toilet, lorong, dan kelas tuntas disapu dan dipel.');

        Database::execute(
            "INSERT INTO checklists_cs (unit_id, employee_id, academic_year_id, check_type, check_date, month_num, is_verified, verified_by_user_id, notes) 
             VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?)",
            [$unitId, $empId, $taId, $checkType, $checkDate, $monthNum, $userId, $notes]
        );

        AuditLog::log('SAVE_CS_CHECKLIST', 'checklists_cs', Database::lastInsertId(), null, ['type' => $checkType, 'date' => $checkDate]);
        $this->setFlash('success', 'Checklist kebersihan berhasil disimpan.');
        $this->redirect(Auth::baseUrl() . '/checklist/cs');
    }

    // --- 5. BUKU RAWAT JAGA (BRAJA TU) ---
    public function brajaIndex(): void {
        $this->requireRole(['tata_usaha', 'kepala_sekolah']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        $brajaList = Database::fetchAll(
            "SELECT * FROM checklists_braja 
             WHERE unit_id = ? AND academic_year_id = ? 
             ORDER BY trx_date DESC, id DESC",
            [$unitId, $taId]
        );

        $totalTrx = count($brajaList);
        $totalNominal = 0;
        $totalError = 0;
        foreach ($brajaList as $b) {
            $totalNominal += (float)$b['amount'];
            if ((int)$b['is_error'] === 1) {
                $totalError++;
            }
        }

        $this->view('checklist/braja', [
            'pageTitle'    => 'Buku Rawat Jaga & Rekonsiliasi Belanja TU (Braja)',
            'brajaList'    => $brajaList,
            'totalTrx'     => $totalTrx,
            'totalNominal' => $totalNominal,
            'totalError'   => $totalError
        ]);
    }

    public function brajaSave(): void {
        $this->requireRole('tata_usaha');
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $userId = Auth::id();

        $trxDate = $_POST['trx_date'] ?? date('Y-m-d');
        $itemDesc = trim($_POST['item_desc'] ?? '');
        $amount = (float)($_POST['amount'] ?? 0);
        $isError = (int)($_POST['is_error'] ?? 0);

        if (!empty($itemDesc) && $amount > 0) {
            Database::execute(
                "INSERT INTO checklists_braja (unit_id, academic_year_id, user_id, trx_date, item_desc, amount, is_error) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$unitId, $taId, $userId, $trxDate, $itemDesc, $amount, $isError]
            );
            $this->setFlash('success', 'Catatan transaksi belanja braja berhasil disimpan.');
        } else {
            $this->setFlash('danger', 'Harap lengkapi uraian dan nominal transaksi belanja.');
        }

        $this->redirect(Auth::baseUrl() . '/checklist/braja');
    }
}
