<?php
// app/Controllers/SpmbController.php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use Core\AuditLog;
use Core\SecurityHelper;

class SpmbController extends Controller {

    // --- 1. TARGET SPMB (DEPUTI PENDIDIKAN) ---
    public function targetIndex(): void {
        $this->requireRole(['deputi_pendidikan', 'direktur_pendidikan', 'operator_yayasan']);
        $taId = Auth::academicYearId();

        $units = Database::fetchAll("SELECT * FROM units ORDER BY id ASC");
        $targets = Database::fetchAll(
            "SELECT st.*, u.name as unit_name, 
                    COALESCE(st.actual_count, 0) as actual_registrations 
             FROM spmb_targets st 
             JOIN units u ON st.unit_id = u.id 
             WHERE st.academic_year_id = ?",
            [$taId]
        );

        $this->view('spmb/target', [
            'pageTitle' => 'Penetapan Target SPMB Unit Sekolah (Deputi Pendidikan)',
            'units'     => $units,
            'targets'   => $targets
        ]);
    }

    public function targetSave(): void {
        $this->requireRole(['deputi_pendidikan', 'direktur_pendidikan']);
        $unitId = (int)($_POST['unit_id'] ?? 0);
        $targetCount = (int)($_POST['target_count'] ?? 100);
        $taId = Auth::academicYearId();
        $userId = Auth::id();

        if ($unitId && $targetCount > 0) {
            Database::execute(
                "INSERT INTO spmb_targets (unit_id, academic_year_id, target_count, set_by_deputi_id) 
                 VALUES (?, ?, ?, ?) 
                 ON DUPLICATE KEY UPDATE target_count = VALUES(target_count)",
                [$unitId, $taId, $targetCount, $userId]
            );
            $this->setFlash('success', 'Target pendaftaran SPMB berhasil disimpan.');
        }

        $this->redirect(Auth::baseUrl() . '/spmb/target');
    }

    // --- 2. OPERATOR SPMB: INPUT ANGKA REALISASI PENDAFTAR SISWA BARU ---
    public function operatorIndex(): void {
        $this->requireRole(['operator_spmb', 'operator_sekolah', 'operator_yayasan', 'kepala_sekolah', 'deputi_pendidikan']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $isYayasan = Auth::hasRole('operator_yayasan') || Auth::hasRole('deputi_pendidikan') || Auth::hasRole('direktur_pendidikan');

        if ($isYayasan && isset($_GET['unit_id']) && (int)$_GET['unit_id'] > 0) {
            $unitId = (int)$_GET['unit_id'];
        }

        $allUnits = Database::fetchAll("SELECT * FROM units ORDER BY id ASC");
        $unitInfo = Database::fetch("SELECT * FROM units WHERE id = ?", [$unitId]);

        $targetRow = Database::fetch("SELECT * FROM spmb_targets WHERE unit_id = ? AND academic_year_id = ?", [$unitId, $taId]);
        $targetVal = (int)($targetRow['target_count'] ?? 100);
        $actualCount = (int)($targetRow['actual_count'] ?? 0);

        $percentTarget = $targetVal > 0 ? round(($actualCount / $targetVal) * 100, 1) : 0;

        $this->view('spmb/operator', [
            'pageTitle'     => 'Pencapaian Realisasi SPMB Siswa Baru',
            'unitInfo'      => $unitInfo,
            'allUnits'      => $allUnits,
            'unitId'        => $unitId,
            'isYayasan'     => $isYayasan,
            'targetVal'     => $targetVal,
            'actualCount'   => $actualCount,
            'percentTarget' => $percentTarget,
            'targetRow'     => $targetRow
        ]);
    }

    public function operatorSave(): void {
        $this->requireRole(['operator_spmb', 'operator_sekolah', 'operator_yayasan', 'kepala_sekolah']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $isYayasan = Auth::hasRole('operator_yayasan');

        if ($isYayasan && isset($_POST['unit_id']) && (int)$_POST['unit_id'] > 0) {
            $unitId = (int)$_POST['unit_id'];
        }

        $actualCount = max(0, (int)($_POST['actual_count'] ?? 0));
        $notes = trim($_POST['notes'] ?? '');

        // Simpan / update angka realisasi pendaftar langsung ke spmb_targets
        Database::execute(
            "INSERT INTO spmb_targets (unit_id, academic_year_id, target_count, actual_count, set_by_deputi_id) 
             VALUES (?, ?, 100, ?, 1) 
             ON DUPLICATE KEY UPDATE actual_count = VALUES(actual_count)",
            [$unitId, $taId, $actualCount]
        );

        AuditLog::log('UPDATE_SPMB_ACTUAL', 'spmb_targets', $unitId, null, [
            'actual_count' => $actualCount,
            'unit_id'      => $unitId,
            'notes'        => $notes
        ]);

        $this->setFlash('success', "Angka realisasi pendaftar SPMB ({$actualCount} santri) berhasil disimpan dan terintegrasi otomatis ke AKFI.");
        $this->redirect(Auth::baseUrl() . '/spmb/operator' . ($isYayasan ? "?unit_id={$unitId}" : ''));
    }

    public function operatorConvertToStudent(): void {
        // Fungsi konversi dinonaktifkan sesuai kebutuhan alur kerja praktis (cukup input angka saja)
        $this->redirect(Auth::baseUrl() . '/spmb/operator');
    }
}
