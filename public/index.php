<?php
// public/index.php - Front Controller
declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Core\SecurityHelper;
SecurityHelper::sendSecurityHeaders();

use Core\Router;
use Core\Auth;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\MasterController;
use App\Controllers\AdministrasiController;
use App\Controllers\TahfidzController;
use App\Controllers\AkfiController;
use App\Controllers\SupervisiController;
use App\Controllers\NilaiBulananController;
use App\Controllers\AbsensiController;
use App\Controllers\TagihanController;
use App\Controllers\SurveyController;
use App\Controllers\ChecklistController;
use App\Controllers\PanitiaLombaController;
use App\Controllers\SpmbController;
use App\Controllers\AlumniController;
use App\Controllers\AuditBackupController;
use App\Controllers\ParentController;
use App\Controllers\ProfileController;
use App\Controllers\SarprasController;
use App\Controllers\JadwalController;
use App\Controllers\PwaController;
use App\Controllers\NotificationController;

// PWA (Progressive Web App) Routes
Router::get('/manifest.json', [PwaController::class, 'manifest']);
Router::get('/sw.js', [PwaController::class, 'serviceWorker']);
Router::get('/offline', [PwaController::class, 'offline']);

// 1. Auth & Session Routes
Router::get('/', [AuthController::class, 'showLogin']);
Router::get('/login', [AuthController::class, 'showLogin']);
Router::post('/login', [AuthController::class, 'login']);
Router::get('/logout', [AuthController::class, 'logout']);
Router::get('/change-password', [AuthController::class, 'showChangePassword']);
Router::post('/change-password', [AuthController::class, 'changePassword']);
Router::post('/switch-role', [AuthController::class, 'switchRole']);
Router::post('/switch-ta', [AuthController::class, 'switchTa']);

// 2. Dashboard & Panduan
Router::get('/dashboard', [DashboardController::class, 'index']);
Router::get('/panduan', [DashboardController::class, 'downloadPanduan']);
Router::get('/buku-panduan', [DashboardController::class, 'downloadPanduan']);

// Notifikasi Sistem
Router::get('/notifications', [NotificationController::class, 'index']);
Router::get('/notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
Router::get('/notifications/delete-all', [NotificationController::class, 'deleteAll']);
Router::post('/notifications/delete-all', [NotificationController::class, 'deleteAll']);
Router::get('/notifications/:id/read', [NotificationController::class, 'read']);
Router::get('/notifications/:id/mark-read', [NotificationController::class, 'markRead']);
Router::get('/notifications/:id/delete', [NotificationController::class, 'delete']);
Router::post('/notifications/:id/delete', [NotificationController::class, 'delete']);

// 3. Master Data (Operator Yayasan & Operator Sekolah)
Router::get('/master/units', [MasterController::class, 'units']);
Router::post('/master/units/save', [MasterController::class, 'saveUnit']);
Router::post('/master/units/upload-logo', [MasterController::class, 'uploadUnitLogo']);
Router::get('/master/academic-years', [MasterController::class, 'academicYears']);
Router::post('/master/academic-years/save', [MasterController::class, 'saveAcademicYear']);
Router::post('/master/academic-years/switch-active', [MasterController::class, 'switchActiveYear']);
Router::post('/master/academic-years/approve', [MasterController::class, 'approveAcademicYear']);
Router::post('/master/academic-years/archive-to-nas', [MasterController::class, 'archiveTaToNas']);
Router::post('/master/academic-years/rehydrate-from-nas', [MasterController::class, 'rehydrateTaFromNas']);
Router::post('/master/storage/transfer-init', [MasterController::class, 'storageTransferInit']);
Router::post('/master/storage/transfer-step', [MasterController::class, 'storageTransferStep']);
Router::post('/master/storage/transfer-finalize', [MasterController::class, 'storageTransferFinalize']);
Router::post('/master/storage/test-nas', [MasterController::class, 'testNasConnectionAjax']);
Router::post('/master/storage/save-nas-config', [MasterController::class, 'saveNasConfig']);
Router::get('/master/academic-years/download-db-archive', [MasterController::class, 'downloadTaDbSnapshot']);
Router::get('/master/employees', [MasterController::class, 'employees']);
Router::post('/master/employees/save', [MasterController::class, 'saveEmployee']);
Router::post('/master/employees/delete', [MasterController::class, 'deleteEmployee']);
Router::get('/master/employees/export', [MasterController::class, 'exportEmployees']);
Router::get('/master/employees/template', [MasterController::class, 'templateEmployees']);
Router::post('/master/employees/import', [MasterController::class, 'importEmployees']);
Router::get('/master/turnover', [MasterController::class, 'turnover']);
Router::post('/master/turnover/save', [MasterController::class, 'saveTurnover']);
Router::get('/master/akfi-templates', [MasterController::class, 'akfiTemplates']);
Router::get('/master/akfi-templates/edit/:id', [MasterController::class, 'editAkfiTemplate']);
Router::post('/master/akfi-templates/update', [MasterController::class, 'updateAkfiTemplate']);
Router::get('/master/mushaf', [MasterController::class, 'mushaf']);

// 4. Operator Sekolah: Siswa, Kelas, Mapel, Akun, SPMB
Router::get('/students', [MasterController::class, 'students']);
Router::get('/students/alumni-mutasi', [MasterController::class, 'inactiveStudents']);
Router::post('/students/reactivate', [MasterController::class, 'reactivateStudent']);
Router::get('/students/export-inactive', [MasterController::class, 'exportInactiveStudents']);
Router::post('/students/save', [MasterController::class, 'saveStudent']);
Router::post('/students/delete', [MasterController::class, 'deleteStudent']);
Router::get('/students/export', [MasterController::class, 'exportStudents']);
Router::get('/students/template', [MasterController::class, 'templateStudents']);
Router::post('/students/import', [MasterController::class, 'importStudents']);
Router::get('/students/print-parent-card/:id', [MasterController::class, 'printParentCard']);
Router::get('/classes', [MasterController::class, 'classes']);
Router::post('/classes/save', [MasterController::class, 'saveClass']);
Router::post('/classes/promote', [MasterController::class, 'promoteClasses']);
Router::get('/classes/promote-preview', [MasterController::class, 'promotePreview']);
Router::get('/classes/placement', [MasterController::class, 'classPlacement']);
Router::post('/classes/assign-student', [MasterController::class, 'assignStudentClass']);
Router::post('/classes/remove-student', [MasterController::class, 'removeStudentClass']);
Router::post('/classes/batch-placement', [MasterController::class, 'batchPlacement']);
Router::get('/subjects', [MasterController::class, 'subjects']);
Router::post('/subjects/save', [MasterController::class, 'saveSubject']);
Router::post('/subjects/assign-teacher', [MasterController::class, 'assignTeacherSubject']);
Router::post('/subjects/unassign-teacher', [MasterController::class, 'unassignTeacherSubject']);
Router::get('/users/school-accounts', [MasterController::class, 'schoolAccounts']);
Router::post('/users/school-accounts/save', [MasterController::class, 'saveSchoolAccount']);
Router::post('/users/school-accounts/create', [MasterController::class, 'createSchoolAccount']);
Router::post('/users/school-accounts/assign-role', [MasterController::class, 'assignRole']);
Router::post('/users/school-accounts/unassign-role', [MasterController::class, 'unassignRole']);
Router::post('/users/school-accounts/delete', [MasterController::class, 'deleteSchoolAccount']);
Router::get('/settings/smtp', [MasterController::class, 'smtpSettings']);
Router::post('/settings/smtp/save', [MasterController::class, 'saveSmtpSettings']);
Router::post('/settings/smtp/test', [MasterController::class, 'testSmtp']);
Router::get('/settings/app', [MasterController::class, 'appSettings']);
Router::post('/settings/app/save', [MasterController::class, 'saveAppSettings']);

// 5. Administrasi Dokumen (Upload & Multi-Frequency Approval)
Router::get('/administrasi/ks', [AdministrasiController::class, 'ksUploads']);
Router::get('/administrasi/mapel', [AdministrasiController::class, 'mapelUploads']);
Router::get('/administrasi/walas', [AdministrasiController::class, 'walasUploads']);
Router::get('/administrasi/tu', [AdministrasiController::class, 'tuUploads']);
Router::post('/administrasi/upload', [AdministrasiController::class, 'uploadDocument']);
Router::get('/administrasi/review-ks', [AdministrasiController::class, 'reviewKs']);
Router::get('/administrasi/review-guru', [AdministrasiController::class, 'reviewGuru']);
Router::post('/administrasi/approve', [AdministrasiController::class, 'approveDocument']);
Router::post('/administrasi/batch-approve', [AdministrasiController::class, 'batchApproveTeacher']);
Router::get('/administrasi/download/:id', [AdministrasiController::class, 'downloadDocument']);
Router::post('/karya-ilmiah/approve', [AdministrasiController::class, 'approveKaryaIlmiah']);
Router::get('/karya-ilmiah/download/:id', [AdministrasiController::class, 'downloadKaryaIlmiah']);
Router::get('/walas/ptk', [PanitiaLombaController::class, 'bestPracticeIndex']);
Router::get('/administrasi/deadlines', [AdministrasiController::class, 'deadlines']);
Router::post('/administrasi/deadlines/save', [AdministrasiController::class, 'saveDeadline']);

// 6. Tahfidz & Kalkulator Madinah
Router::get('/tahfidz/setoran-siswa', [TahfidzController::class, 'setoranSiswa']);
Router::post('/tahfidz/setoran-siswa/save', [TahfidzController::class, 'saveSetoranSiswa']);
Router::post('/tahfidz/setoran-siswa/update', [TahfidzController::class, 'updateSetoranSiswa']);
Router::post('/tahfidz/setoran-siswa/delete', [TahfidzController::class, 'deleteSetoranSiswa']);
Router::get('/tahfidz/ajukan-ujian', [TahfidzController::class, 'ajukanUjianForm']);
Router::post('/tahfidz/ajukan-ujian', [TahfidzController::class, 'submitUjian']);
Router::get('/tahfidz/ujian-antrean', [TahfidzController::class, 'antreanUjian']);
Router::post('/tahfidz/ujian/grade', [TahfidzController::class, 'gradeUjian']);
Router::get('/tahfidz/ujian/sertifikat/:id', [TahfidzController::class, 'printSertifikat']);
Router::get('/tahfidz/guru-nilai', [TahfidzController::class, 'guruNilai']);
Router::post('/tahfidz/guru-nilai/save', [TahfidzController::class, 'saveGuruNilai']);
Router::get('/tahfidz/halaqah-siswa', [TahfidzController::class, 'halaqahSiswa']);
Router::post('/tahfidz/halaqah-siswa/save', [TahfidzController::class, 'saveHalaqahSiswa']);
Router::post('/tahfidz/halaqah-siswa/update', [TahfidzController::class, 'updateHalaqahSiswa']);
Router::post('/tahfidz/halaqah-siswa/delete', [TahfidzController::class, 'deleteHalaqahSiswa']);
Router::get('/tahfidz/halaqah-guru', [TahfidzController::class, 'halaqahGuru']);
Router::post('/tahfidz/halaqah-guru/save', [TahfidzController::class, 'saveHalaqahGuru']);
Router::get('/tahfidz/guru-target', [TahfidzController::class, 'guruTarget']);
Router::post('/tahfidz/guru-target/save', [TahfidzController::class, 'saveGuruTarget']);
Router::get('/tahfidz/rekap-siswa', [TahfidzController::class, 'rekapSiswa']);
Router::get('/tahfidz/target-siswa', [TahfidzController::class, 'targetSiswa']);
Router::post('/tahfidz/target-siswa/save', [TahfidzController::class, 'saveTargetSiswa']);
Router::get('/tahfidz/rapor-pdf/:id', [TahfidzController::class, 'printRaporTahfidz']);
Router::get('/tahfidz/baseline', [TahfidzController::class, 'baseline']);
Router::post('/tahfidz/baseline/save', [TahfidzController::class, 'saveBaseline']);
Router::get('/api/mushaf-calc', [TahfidzController::class, 'apiCalculateLines']);

// 7. AKFI Assessment Engine (10 Positions, 3-Tier Approval, Self & Superior)
Router::get('/akfi/pribadi', [AkfiController::class, 'pribadi']);
Router::post('/akfi/self-submit', [AkfiController::class, 'selfSubmit']);
Router::get('/akfi/penilaian-bawahan', [AkfiController::class, 'penilaianBawahan']);
Router::get('/akfi/assess/:id', [AkfiController::class, 'assessForm']);
Router::post('/akfi/assess-save', [AkfiController::class, 'assessSave']);
Router::get('/akfi/kanit-preview', [AkfiController::class, 'kanitPreview']);
Router::get('/akfi/penilaian-sikap-ks', [AkfiController::class, 'penilaianSikapKs']);
Router::post('/akfi/save-sikap-ks', [AkfiController::class, 'saveSikapKs']);
Router::get('/akfi/approval-hr', [AkfiController::class, 'approvalHr']);
Router::post('/akfi/hr-sign', [AkfiController::class, 'hrSign']);
Router::get('/akfi/rekap-yayasan', [AkfiController::class, 'rekapYayasan']);
Router::get('/akfi/export-xlsx', [AkfiController::class, 'exportXlsx']);
Router::get('/akfi/print-rapor/:id', [AkfiController::class, 'printRaporAkfi']);
Router::get('/deputi/target-kerja', [AkfiController::class, 'targetKerjaIndex']);
Router::post('/deputi/target-kerja/save', [AkfiController::class, 'targetKerjaSave']);


// 8. Supervisi Mengajar (Rubrik A/B/C dengan Validasi Indikator 12)
Router::get('/supervisi/jadwal', [SupervisiController::class, 'jadwal']);
Router::post('/supervisi/jadwal/save', [SupervisiController::class, 'saveJadwal']);
Router::post('/supervisi/jadwal/delete', [SupervisiController::class, 'deleteJadwal']);
Router::get('/supervisi/penilaian', [SupervisiController::class, 'penilaianList']);
Router::get('/supervisi/form/:id', [SupervisiController::class, 'form']);
Router::post('/supervisi/save-score', [SupervisiController::class, 'saveScore']);
Router::get('/supervisi/print/:id', [SupervisiController::class, 'printInstrumen']);
// 8b. Jadwal Pelajaran Interaktif (Waka Kurikulum, Kepsek & Guru)
Router::get('/jadwal/kelas', [JadwalController::class, 'index']);
Router::post('/jadwal/save', [JadwalController::class, 'save']);
Router::post('/jadwal/delete', [JadwalController::class, 'delete']);
Router::post('/jadwal/substitute', [JadwalController::class, 'substitute']);
Router::post('/jadwal/remove-substitute', [JadwalController::class, 'removeSubstitute']);
Router::get('/api/jadwal/teacher-status', [JadwalController::class, 'apiTeacherStatus']);
Router::get('/jadwal/print', [JadwalController::class, 'printJadwal']);
Router::get('/jadwal/tahfidz', [JadwalController::class, 'tahfidz']);
Router::post('/jadwal/slots/save', [JadwalController::class, 'saveSlot']);
Router::post('/jadwal/slots/delete', [JadwalController::class, 'deleteSlot']);
Router::post('/jadwal/slots/copy', [JadwalController::class, 'copySlots']);
Router::post('/jadwal/slots/reset', [JadwalController::class, 'resetSlots']);

// 9. Nilai Bulanan & Ledger Kelas
Router::get('/nilai/bulanan', [NilaiBulananController::class, 'index']);
Router::post('/nilai/bulanan/save', [NilaiBulananController::class, 'saveGrades']);
Router::post('/nilai/bulanan/import', [NilaiBulananController::class, 'importGrades']);
Router::get('/nilai/ledger', [NilaiBulananController::class, 'ledger']);
Router::get('/nilai/ledger/export-xlsx', [NilaiBulananController::class, 'exportLedgerXlsx']);
Router::get('/nilai/ledger/export-pdf', [NilaiBulananController::class, 'exportLedgerPdf']);
Router::get('/nilai/rapor-bulanan/:id', [NilaiBulananController::class, 'printRaporBulanan']);
Router::get('/nilai/rapor-gabungan/:id', [NilaiBulananController::class, 'printRaporGabungan']);

// 10. Absensi Pegawai & Siswa
Router::get('/attendance/teachers', [AbsensiController::class, 'teachersAttendance']);
Router::get('/attendance/teachers/template', [AbsensiController::class, 'teachersAttendanceTemplate']);
Router::post('/attendance/teachers/import', [AbsensiController::class, 'teachersAttendanceImport']);
Router::post('/attendance/teachers/save', [AbsensiController::class, 'saveTeachersAttendance']);
Router::get('/attendance/staff', [AbsensiController::class, 'staffAttendance']);
Router::get('/attendance/staff/template', [AbsensiController::class, 'staffAttendanceTemplate']);
Router::post('/attendance/staff/import', [AbsensiController::class, 'staffAttendanceImport']);
Router::post('/attendance/staff/save', [AbsensiController::class, 'saveStaffAttendance']);
Router::get('/attendance/students', [AbsensiController::class, 'studentsAttendance']);
Router::post('/attendance/students/save', [AbsensiController::class, 'saveStudentsAttendance']);

// 11. Keuangan & Tagihan SPP Siswa (FR 8001A Multi-Kolom, FR 8001B Reminder TU, FR 8001C/D Kepsek & Deputi, Buku Cicilan TU)
Router::get('/tagihan/manage', [TagihanController::class, 'manage']);
Router::get('/tagihan/template', [TagihanController::class, 'downloadTemplate']);
Router::post('/tagihan/import', [TagihanController::class, 'importBills']);
Router::post('/tagihan/save', [TagihanController::class, 'saveBill']);
Router::post('/tagihan/delete', [TagihanController::class, 'deleteBill']);

// Alur Surat TU (FR 8001B) & Eskalasi
Router::get('/tagihan/cetak-fr8001b/:id', [TagihanController::class, 'printSuratFR8001B']);
Router::post('/tagihan/kirim-fr8001b', [TagihanController::class, 'kirimSuratFR8001B']);
Router::post('/tagihan/mark-selesai-tu', [TagihanController::class, 'markSelesaiTU']);
Router::post('/tagihan/eskalasi-kepsek', [TagihanController::class, 'eskalasiKepsek']);
Router::post('/tagihan/bulk-eskalasi-kepsek', [TagihanController::class, 'bulkEscalateToKepsek']);

// Alur Kepala Sekolah (FR 8001C Pemanggilan & FR 8001D Kesepakatan)
Router::get('/tagihan/approval', [TagihanController::class, 'approvalList']);
Router::post('/tagihan/kepsek/schedule', [TagihanController::class, 'saveKepsekSchedule']);
Router::get('/tagihan/cetak-fr8001c/:id', [TagihanController::class, 'printSuratFR8001C']);
Router::get('/tagihan/cetak-fr8001d/:id', [TagihanController::class, 'printSuratFR8001D']);
Router::post('/tagihan/approve-kesepakatan-fr8001d', [TagihanController::class, 'approveKesepakatanFR8001D']);
Router::post('/tagihan/mark-selesai-kepsek', [TagihanController::class, 'markSelesaiKepsek']);
Router::post('/tagihan/eskalasi-deputi', [TagihanController::class, 'eskalasiDeputi']);

// Alur Yayasan / Deputi Direktur Pendidikan (FR 8001C Kop Yayasan & FR 8001D)
Router::get('/tagihan/eskalasi-deputi', [TagihanController::class, 'deputiEskalasiList']);
Router::post('/tagihan/deputi/schedule', [TagihanController::class, 'saveDeputiSchedule']);
Router::get('/tagihan/cetak-fr8001c-deputi/:id', [TagihanController::class, 'printSuratFR8001CDeputi']);
Router::get('/tagihan/cetak-fr8001d-deputi/:id', [TagihanController::class, 'printSuratFR8001DDeputi']);
Router::post('/tagihan/mark-selesai-deputi', [TagihanController::class, 'markSelesaiDeputi']);

// Menu Baru Akun TU: Buku & Kartu Cicilan Siswa (FR 8001D)
Router::get('/tagihan/cicilan', [TagihanController::class, 'cicilanList']);
Router::post('/tagihan/cicilan/bayar', [TagihanController::class, 'saveCicilanPayment']);
Router::get('/tagihan/cicilan/kartu-pdf/:id', [TagihanController::class, 'printKartuCicilanPdf']);
Router::post('/tagihan/cicilan/kirim-pdf', [TagihanController::class, 'sendKartuCicilanEmail']);

// Backward Compatibility
Router::get('/tagihan/rekomendasi', [TagihanController::class, 'rekomendasiList']);
Router::get('/tagihan/surat-pdf/:id', [TagihanController::class, 'printSuratTagihan']);
Router::get('/tagihan/print-surat/:id', [TagihanController::class, 'printSuratTagihan']);

// 12. Survei Kepuasan (Ortu 53 butir, Guru terhadap Kepsek, Siswa)
Router::get('/survey/ortu', [SurveyController::class, 'ortuForm']);
Router::post('/survey/ortu/submit', [SurveyController::class, 'ortuSubmit']);
Router::get('/survey/guru-kepsek', [SurveyController::class, 'guruKepsekForm']);
Router::post('/survey/guru-kepsek/submit', [SurveyController::class, 'guruKepsekSubmit']);
Router::get('/survey/siswa', [SurveyController::class, 'siswaForm']);
Router::post('/survey/siswa/submit', [SurveyController::class, 'siswaSubmit']);
Router::get('/survey/rekap-kepsek', [SurveyController::class, 'rekapKepsek']);

// 13. Checklist Security & Cleaning Service
Router::get('/checklist/security', [ChecklistController::class, 'securityIndex']);
Router::post('/checklist/security/save', [ChecklistController::class, 'securitySave']);
Router::get('/security/patrol', [ChecklistController::class, 'securityPatrol']);
Router::post('/security/patrol/save', [ChecklistController::class, 'securityPatrolSave']);
Router::get('/security/incidents', [ChecklistController::class, 'securityIncidents']);
Router::post('/security/incidents/save', [ChecklistController::class, 'securityIncidentSave']);
Router::get('/checklist/cs', [ChecklistController::class, 'csIndex']);
Router::post('/checklist/cs/save', [ChecklistController::class, 'csSave']);
Router::get('/checklist/braja', [ChecklistController::class, 'brajaIndex']);
Router::post('/checklist/braja/save', [ChecklistController::class, 'brajaSave']);

// 14. Kegiatan di Luar KBM & Panitia Lomba & Alumni & SPMB
Router::get('/kegiatan/luar-kbm', [PanitiaLombaController::class, 'kegiatanIndex']);
Router::post('/kegiatan/luar-kbm/save', [PanitiaLombaController::class, 'kegiatanSave']);
Router::post('/kegiatan/luar-kbm/attendance', [PanitiaLombaController::class, 'kegiatanAttendanceSave']);
Router::get('/panitia/penugasan', [PanitiaLombaController::class, 'penugasanIndex']);
Router::post('/panitia/penugasan/save', [PanitiaLombaController::class, 'penugasanSave']);
Router::post('/panitia/penugasan/toggle-status', [PanitiaLombaController::class, 'penugasanToggleStatus']);
Router::post('/panitia/penugasan/update-students', [PanitiaLombaController::class, 'penugasanUpdateStudents']);
Router::get('/panitia/penugasan/cetak-sk/:id', [PanitiaLombaController::class, 'penugasanCetakSk']);
Router::get('/prestasi/panitia', [PanitiaLombaController::class, 'prestasiPanitiaIndex']);
Router::post('/prestasi/panitia/save', [PanitiaLombaController::class, 'prestasiPanitiaSave']);
Router::get('/prestasi/walas', [PanitiaLombaController::class, 'prestasiWalasIndex']);
Router::post('/prestasi/walas/save', [PanitiaLombaController::class, 'prestasiWalasSave']);
Router::get('/guru/prestasi-tetapkan', [PanitiaLombaController::class, 'guruPrestasiIndex']);
Router::post('/guru/prestasi-tetapkan/save', [PanitiaLombaController::class, 'guruPrestasiSave']);
Router::post('/guru/prestasi-tetapkan/update', [PanitiaLombaController::class, 'guruPrestasiUpdate']);
Router::post('/guru/prestasi-tetapkan/verify', [PanitiaLombaController::class, 'guruPrestasiVerify']);
Router::get('/guru/prestasi', [PanitiaLombaController::class, 'guruPrestasiMandiri']);
Router::post('/guru/prestasi/save', [PanitiaLombaController::class, 'guruPrestasiMandiriSave']);
Router::get('/guru/best-practice', [PanitiaLombaController::class, 'bestPracticeIndex']);
Router::post('/guru/best-practice/upload', [PanitiaLombaController::class, 'bestPracticeUpload']);
Router::get('/walas/rekomendasi', [AlumniController::class, 'walasRekomendasi']);
Router::post('/walas/rekomendasi/submit', [AlumniController::class, 'walasRekomendasiSubmit']);
Router::post('/walas/rekomendasi/delete', [AlumniController::class, 'walasRekomendasiDelete']);
Router::get('/alumni', [AlumniController::class, 'alumniIndex']);
Router::post('/alumni/save', [AlumniController::class, 'alumniSave']);
Router::get('/alumni/template', [AlumniController::class, 'alumniTemplate']);
Router::post('/alumni/import', [AlumniController::class, 'alumniImport']);
Router::get('/spmb/target', [SpmbController::class, 'targetIndex']);
Router::post('/spmb/target/save', [SpmbController::class, 'targetSave']);
Router::get('/spmb/operator', [SpmbController::class, 'operatorIndex']);
Router::post('/spmb/operator/save', [SpmbController::class, 'operatorSave']);
Router::post('/spmb/operator/convert', [SpmbController::class, 'operatorConvertToStudent']);

// 15. Portal Orang Tua
Router::get('/parent/rapor', [ParentController::class, 'raporIndex']);
Router::get('/parent/rapor-bulanan/:id', [ParentController::class, 'printRaporBulanan']);
Router::get('/parent/rapor-tahfidz/:id', [ParentController::class, 'printRaporTahfidz']);
Router::get('/parent/rapor-gabungan/:id', [ParentController::class, 'printRaporGabungan']);
Router::get('/parent/bills', [ParentController::class, 'billsIndex']);
Router::get('/parent/surat-tagihan/:id', [ParentController::class, 'printSuratTagihan']);

// 16. Audit Log & Backup/Restore
Router::get('/audit/logs', [AuditBackupController::class, 'logs']);
Router::get('/backup', [AuditBackupController::class, 'backupIndex']);
Router::post('/backup/create', [AuditBackupController::class, 'createBackup']);
Router::post('/backup/create-full', [AuditBackupController::class, 'createFullBackup']);
Router::post('/backup/restore', [AuditBackupController::class, 'restoreBackup']);
Router::post('/backup/restore-existing', [AuditBackupController::class, 'restoreExistingBackup']);
Router::post('/backup/copy-to-nas', [AuditBackupController::class, 'copyToNas']);
Router::post('/backup/delete', [AuditBackupController::class, 'deleteBackup']);
Router::get('/backup/download/:id', [AuditBackupController::class, 'downloadBackup']);
Router::get('/rekap/deputi', [AuditBackupController::class, 'rekapDeputi']);

// 17. Profil Pengguna Terpadu
Router::get('/profile', [ProfileController::class, 'index']);
Router::post('/profile/update', [ProfileController::class, 'update']);

// 18. Modul Sarpras, Inventaris, PO & Berita Acara Scrap (F30)
Router::get('/sarpras/categories', [SarprasController::class, 'categories']);
Router::post('/sarpras/categories/save', [SarprasController::class, 'saveCategory']);
Router::post('/sarpras/categories/delete', [SarprasController::class, 'deleteCategory']);

Router::get('/sarpras/assets', [SarprasController::class, 'assets']);
Router::post('/sarpras/assets/save', [SarprasController::class, 'saveAsset']);
Router::post('/sarpras/assets/delete', [SarprasController::class, 'deleteAsset']);
Router::get('/sarpras/assets/template', [SarprasController::class, 'templateAssets']);
Router::post('/sarpras/assets/import', [SarprasController::class, 'importAssets']);
Router::get('/sarpras/assets/export', [SarprasController::class, 'exportAssets']);

Router::get('/sarpras/po', [SarprasController::class, 'poIndex']);
Router::get('/sarpras/po/create', [SarprasController::class, 'poCreate']);
Router::post('/sarpras/po/save', [SarprasController::class, 'poSave']);
Router::get('/sarpras/po/detail/:id', [SarprasController::class, 'poDetail']);
Router::post('/sarpras/po/approve-kepsek', [SarprasController::class, 'poApproveKepsek']);
Router::post('/sarpras/po/approve-pengadaan', [SarprasController::class, 'poApprovePengadaan']);
Router::post('/sarpras/po/approve-sarpras-dir', [SarprasController::class, 'poApproveSarprasDir']);
Router::post('/sarpras/po/reject', [SarprasController::class, 'poReject']);
Router::post('/sarpras/po/realize', [SarprasController::class, 'poRealize']);
Router::get('/sarpras/po/print/:id', [SarprasController::class, 'poPrint']);

Router::get('/sarpras/scrap', [SarprasController::class, 'scrapIndex']);
Router::get('/sarpras/scrap/create', [SarprasController::class, 'scrapCreate']);
Router::post('/sarpras/scrap/save', [SarprasController::class, 'scrapSave']);
Router::get('/sarpras/scrap/detail/:id', [SarprasController::class, 'scrapDetail']);
Router::get('/sarpras/scrap/print/:id', [SarprasController::class, 'scrapPrint']);

Router::get('/sarpras/file/view', [SarprasController::class, 'viewFile']);

// Run Router
Router::run();
