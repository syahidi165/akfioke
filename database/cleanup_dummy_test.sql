-- database/cleanup_dummy_test.sql
-- Hapus semua dummy data SEKOLAH TEST dan akun dummy, pertahankan akun operator yayasan (admin)

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Hapus penilaian AKFI dummy
DELETE FROM `akfi_assessments` WHERE `unit_id` = 1 OR `unit_id` IN (SELECT `id` FROM `units` WHERE `code` = 'TEST');

-- 2. Hapus tagihan & reminder siswa dummy
DELETE FROM `bill_reminders` WHERE `bill_id` IN (SELECT `id` FROM `bills` WHERE `unit_id` = 1);
DELETE FROM `bill_installments` WHERE `bill_id` IN (SELECT `id` FROM `bills` WHERE `unit_id` = 1);
DELETE FROM `bills` WHERE `unit_id` = 1 OR `unit_id` IN (SELECT `id` FROM `units` WHERE `code` = 'TEST');

-- 3. Hapus prestasi guru dummy & penugasan panitia lomba
DELETE FROM `guru_berprestasi` WHERE `unit_id` = 1 OR `unit_id` IN (SELECT `id` FROM `units` WHERE `code` = 'TEST');
DELETE FROM `panitia_lomba_assignments` WHERE `unit_id` = 1 OR `unit_id` IN (SELECT `id` FROM `units` WHERE `code` = 'TEST');

-- 4. Hapus deadline administrasi unit dummy
DELETE FROM `unit_administrasi_deadlines` WHERE `unit_id` = 1 OR `unit_id` IN (SELECT `id` FROM `units` WHERE `code` = 'TEST');

-- 5. Hapus data siswa dummy
DELETE FROM `alumni_tracking` WHERE `student_id` IN (SELECT `id` FROM `students` WHERE `unit_id` = 1);
DELETE FROM `students` WHERE `unit_id` = 1 OR `unit_id` IN (SELECT `id` FROM `units` WHERE `code` = 'TEST');

-- 6. Hapus pegawai dummy
DELETE FROM `employee_attendance` WHERE `unit_id` = 1;
DELETE FROM `employees` WHERE `unit_id` = 1 OR `unit_id` IN (SELECT `id` FROM `units` WHERE `code` = 'TEST');

-- 7. Hapus notifikasi akun dummy
DELETE FROM `notifications` WHERE `user_id` != 1;

-- 8. Hapus log audit akun dummy
DELETE FROM `audit_logs` WHERE `user_id` != 1;

-- 9. Hapus role pengguna selain admin yayasan
DELETE FROM `user_roles` WHERE `user_id` != 1;

-- 10. Hapus akun pengguna dummy selain admin yayasan
DELETE FROM `users` WHERE `id` != 1 AND `username` != 'admin';

-- 11. Hapus unit sekolah dummy 'TEST'
DELETE FROM `units` WHERE `code` = 'TEST' OR `name` = 'SEKOLAH TEST' OR `id` = 1;

SET FOREIGN_KEY_CHECKS = 1;
