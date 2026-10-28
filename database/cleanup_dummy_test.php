<?php
// database/cleanup_dummy_test.php
require_once __DIR__ . '/../core/Database.php';

use Core\Database;

Database::connect();
echo "=== CLEANING UP ALL DUMMY DATA FOR 'SEKOLAH TEST' ===\n";

try {
    Database::execute("SET FOREIGN_KEY_CHECKS = 0;");

    // Get unit test id
    $unit = Database::fetch("SELECT id FROM units WHERE code = 'TEST' OR name = 'SEKOLAH TEST'");
    $unitId = $unit ? (int)$unit['id'] : 1;

    // 1. Akfi Assessments
    $r = Database::execute("DELETE FROM akfi_assessments WHERE unit_id = ?", [$unitId]);
    echo "[OK] akfi_assessments cleaned.\n";

    // 2. Bills
    Database::execute("DELETE FROM bill_reminders WHERE bill_id IN (SELECT id FROM bills WHERE unit_id = ?)", [$unitId]);
    Database::execute("DELETE FROM bill_installments WHERE bill_id IN (SELECT id FROM bills WHERE unit_id = ?)", [$unitId]);
    Database::execute("DELETE FROM bills WHERE unit_id = ?", [$unitId]);
    echo "[OK] bills and installments cleaned.\n";

    // 3. Guru Berprestasi & Panitia
    Database::execute("DELETE FROM guru_berprestasi WHERE unit_id = ?", [$unitId]);
    Database::execute("DELETE FROM panitia_lomba_assignments WHERE unit_id = ?", [$unitId]);
    echo "[OK] guru_berprestasi & panitia_lomba_assignments cleaned.\n";

    // 4. Deadlines
    Database::execute("DELETE FROM unit_administrasi_deadlines WHERE unit_id = ?", [$unitId]);
    echo "[OK] unit_administrasi_deadlines cleaned.\n";

    // 5. Students
    Database::execute("DELETE FROM alumni_tracking WHERE student_id IN (SELECT id FROM students WHERE unit_id = ?)", [$unitId]);
    Database::execute("DELETE FROM students WHERE unit_id = ?", [$unitId]);
    echo "[OK] students & alumni_tracking cleaned.\n";

    // 6. Employees & Attendance
    Database::execute("DELETE FROM employee_attendance WHERE unit_id = ?", [$unitId]);
    Database::execute("DELETE FROM employees WHERE unit_id = ?", [$unitId]);
    echo "[OK] employees & employee_attendance cleaned.\n";

    // 7. Notifications
    Database::execute("DELETE FROM notifications WHERE user_id != 1");
    echo "[OK] dummy notifications cleaned.\n";

    // 8. Audit Logs
    Database::execute("DELETE FROM audit_logs WHERE user_id != 1");
    echo "[OK] audit logs cleaned.\n";

    // 9. Users & Roles (Keep Operator Yayasan / admin only)
    Database::execute("DELETE FROM user_roles WHERE user_id != 1");
    Database::execute("DELETE FROM users WHERE id != 1 AND username != 'admin'");
    echo "[OK] dummy users & roles removed (admin operator yayasan preserved).\n";

    // 10. Delete Unit SEKOLAH TEST
    Database::execute("DELETE FROM units WHERE id = ? OR code = 'TEST'", [$unitId]);
    echo "[OK] unit 'SEKOLAH TEST' removed.\n";

    Database::execute("SET FOREIGN_KEY_CHECKS = 1;");
    echo "\n=== CLEANUP COMPLETED SUCCESSFULLY! ===\n";

} catch (Exception $e) {
    Database::execute("SET FOREIGN_KEY_CHECKS = 1;");
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
