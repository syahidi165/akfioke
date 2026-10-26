<?php
// app/Controllers/ParentController.php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use Core\ExportHelper;
use Core\SecurityHelper;

class ParentController extends Controller {

    /**
     * Ambil seluruh santri yang berelasi resmi dengan akun orang tua yang sedang login
     * Berdasarkan parent_user_id atau kesesuaian Nomor KK akun.
     */
    private function getMyChildren(): array {
        $userId = Auth::id();
        $user = Auth::user();
        $username = $user['username'] ?? '';

        return Database::fetchAll(
            "SELECT s.*, c.name as class_name, c.id as class_id, u.name as unit_name 
             FROM students s 
             JOIN units u ON s.unit_id = u.id 
             LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
             LEFT JOIN classes c ON cs.class_id = c.id 
             WHERE s.parent_user_id = ? 
                OR (s.parent_user_id IS NULL AND s.no_kk = ? AND ? != '')
             ORDER BY s.nama ASC",
            [$userId, $username, $username]
        );
    }

    /**
     * Validasi otorisasi anti-IDOR: pastikan santri adalah benar anak dari orang tua yang login
     */
    private function assertChildAccess(int $studentId): void {
        $userId = Auth::id();
        $user = Auth::user();
        $username = $user['username'] ?? '';

        $child = Database::fetch(
            "SELECT id FROM students WHERE id = ? AND (parent_user_id = ? OR (no_kk = ? AND ? != ''))",
            [$studentId, $userId, $username, $username]
        );

        if (!$child) {
            die('Akses ditolak: Anda tidak memiliki wewenang untuk melihat data santri ini.');
        }
    }

    // --- 1. PORTAL RAPOR ANAK ---
    public function raporIndex(): void {
        $this->requireRole('orang_tua');
        $children = $this->getMyChildren();

        $this->view('parent/rapor', [
            'pageTitle' => 'Portal Rapor & Capaian Belajar Ananda',
            'children'  => $children
        ]);
    }

    public function printRaporBulanan(string $id): void {
        $this->requireRole('orang_tua');
        $studentId = (int)$id;
        $this->assertChildAccess($studentId);

        $controller = new NilaiBulananController();
        $controller->printRaporBulanan($id);
    }

    public function printRaporTahfidz(string $id): void {
        $this->requireRole('orang_tua');
        $studentId = (int)$id;
        $this->assertChildAccess($studentId);

        $exam = Database::fetch("SELECT id FROM tahfidz_exams WHERE student_id = ? AND status = 'Lulus' ORDER BY id DESC LIMIT 1", [$studentId]);
        if ($exam) {
            $ctrl = new TahfidzController();
            $ctrl->printSertifikat((string)$exam['id']);
        } else {
            die('Belum ada sertifikasi tahfidz yang diterbitkan untuk ananda.');
        }
    }

    public function printRaporGabungan(string $id): void {
        $this->requireRole('orang_tua');
        $studentId = (int)$id;
        $this->assertChildAccess($studentId);

        $controller = new NilaiBulananController();
        $controller->printRaporBulanan($id);
    }

    // --- 2. TAGIHAN & PEMBAYARAN ANAK ---
    public function billsIndex(): void {
        $this->requireRole('orang_tua');
        $children = $this->getMyChildren();
        $studentIds = array_column($children, 'id');

        $bills = [];
        $totalTunggakan = 0;
        if (!empty($studentIds)) {
            $inClause = implode(',', array_fill(0, count($studentIds), '?'));
            $bills = Database::fetchAll(
                "SELECT b.*, s.nama as student_name, s.nis, u.name as unit_name 
                 FROM bills b 
                 JOIN students s ON b.student_id = s.id 
                 JOIN units u ON b.unit_id = u.id 
                 WHERE b.student_id IN ($inClause) 
                 ORDER BY b.due_date ASC",
                $studentIds
            );

            foreach ($bills as $b) {
                if ($b['status'] === 'Tertunggak') {
                    $totalTunggakan += (float)$b['amount'];
                }
            }
        }

        $unitIds = array_unique(array_filter(array_column($children, 'unit_id')));
        $unitAccounts = [];
        if (!empty($unitIds)) {
            $inUnits = implode(',', array_fill(0, count($unitIds), '?'));
            $unitsData = Database::fetchAll("SELECT * FROM units WHERE id IN ($inUnits)", array_values($unitIds));
            
            $childrenByUnit = [];
            foreach ($children as $ch) {
                $childrenByUnit[$ch['unit_id']][] = $ch['nama'];
            }

            foreach ($unitsData as $u) {
                $unitAccounts[] = [
                    'unit_id'      => $u['id'],
                    'unit_name'    => $u['name'],
                    'bank_name'    => $u['bank_name'] ?: 'Bank Syariah Indonesia (BSI)',
                    'bank_account' => $u['bank_account'] ?: '-',
                    'bank_holder'  => $u['bank_holder'] ?: 'Yayasan Islam Al Kahfi Batam',
                    'children'     => $childrenByUnit[$u['id']] ?? []
                ];
            }
        }

        $this->view('parent/bills', [
            'pageTitle'      => 'Informasi Kewajiban & Tagihan SPP Ananda',
            'children'       => $children,
            'bills'          => $bills,
            'totalTunggakan' => $totalTunggakan,
            'unitAccounts'   => $unitAccounts
        ]);
    }

    public function printSuratTagihan(string $id): void {
        $this->requireRole('orang_tua');
        $billId = (int)$id;

        $userId = Auth::id();
        $user = Auth::user();
        $username = $user['username'] ?? '';

        $bill = Database::fetch(
            "SELECT b.id FROM bills b 
             JOIN students s ON b.student_id = s.id 
             WHERE b.id = ? AND (s.parent_user_id = ? OR (s.no_kk = ? AND ? != ''))",
            [$billId, $userId, $username, $username]
        );
        if (!$bill) {
            die('Akses ditolak: Anda tidak memiliki wewenang untuk melihat surat tagihan ini.');
        }

        $ctrl = new TagihanController();
        $ctrl->printSuratTagihan($id);
    }
}
