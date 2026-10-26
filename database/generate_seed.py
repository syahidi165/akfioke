# database/generate_seed.py
import json

def generate_seed():
    # 1. 114 Surahs data (Madinah Mushaf standard)
    surahs_data = [
        (1, 'الفاتحة', 'Al-Fatihah', 7, 1, 1),
        (2, 'البقرة', 'Al-Baqarah', 286, 2, 49),
        (3, 'آل عمران', 'Ali Imran', 200, 50, 76),
        (4, 'النساء', 'An-Nisa', 176, 77, 106),
        (5, 'المائدة', 'Al-Maidah', 120, 106, 127),
        (6, 'الأنعام', 'Al-Anam', 165, 128, 150),
        (7, 'الأعراف', 'Al-Araf', 206, 151, 176),
        (8, 'الأنفال', 'Al-Anfal', 75, 177, 186),
        (9, 'التوبة', 'At-Taubah', 129, 187, 207),
        (10, 'يونس', 'Yunus', 109, 208, 221),
        (11, 'هود', 'Hud', 123, 221, 235),
        (12, 'يوسف', 'Yusuf', 111, 235, 248),
        (13, 'الرعد', 'Ar-Rad', 43, 249, 255),
        (14, 'إبراهيم', 'Ibrahim', 52, 255, 261),
        (15, 'الحجر', 'Al-Hijr', 99, 262, 267),
        (16, 'النحل', 'An-Nahl', 128, 267, 281),
        (17, 'الإسراء', 'Al-Isra', 111, 282, 293),
        (18, 'الكهف', 'Al-Kahf', 110, 293, 304),
        (19, 'مريم', 'Maryam', 98, 305, 312),
        (20, 'طه', 'Taha', 135, 312, 321),
        (21, 'الأنبياء', 'Al-Anbiya', 112, 322, 331),
        (22, 'الحج', 'Al-Hajj', 78, 332, 341),
        (23, 'المؤمنون', 'Al-Muminun', 118, 342, 349),
        (24, 'النور', 'An-Nur', 64, 350, 359),
        (25, 'الفرقان', 'Al-Furqan', 77, 359, 366),
        (26, 'الشعراء', 'Asy-Syuara', 227, 367, 376),
        (27, 'النمل', 'An-Naml', 93, 377, 385),
        (28, 'القصص', 'Al-Qasas', 88, 385, 396),
        (29, 'العنكبوت', 'Al-Ankabut', 69, 396, 404),
        (30, 'الروم', 'Ar-Rum', 60, 404, 410),
        (31, 'لقمان', 'Luqman', 34, 411, 414),
        (32, 'السجدة', 'As-Sajdah', 30, 415, 417),
        (33, 'الأحزاب', 'Al-Ahzab', 73, 418, 427),
        (34, 'سبأ', 'Saba', 54, 428, 434),
        (35, 'فاطر', 'Fatir', 45, 434, 440),
        (36, 'يس', 'Yasin', 83, 440, 445),
        (37, 'الصافات', 'As-Saffat', 182, 446, 452),
        (38, 'ص', 'Sad', 88, 453, 458),
        (39, 'الزمر', 'Az-Zumar', 75, 458, 467),
        (40, 'غافر', 'Ghafir', 85, 467, 476),
        (41, 'فصلت', 'Fussilat', 54, 477, 482),
        (42, 'الشورى', 'Asy-Syura', 53, 483, 489),
        (43, 'الزخرف', 'Az-Zukhruf', 89, 489, 495),
        (44, 'الدخان', 'Ad-Dukhan', 59, 496, 498),
        (45, 'الجاثية', 'Al-Jatsiyah', 37, 499, 502),
        (46, 'الأحقاف', 'Al-Ahqaf', 35, 502, 506),
        (47, 'محمد', 'Muhammad', 38, 507, 510),
        (48, 'الفتح', 'Al-Fath', 29, 511, 515),
        (49, 'الحجرات', 'Al-Hujurat', 18, 515, 517),
        (50, 'ق', 'Qaf', 45, 518, 520),
        (51, 'الذاريات', 'Adz-Dzariyat', 60, 520, 523),
        (52, 'الطور', 'At-Tur', 49, 523, 525),
        (53, 'النجم', 'An-Najm', 62, 526, 528),
        (54, 'القمر', 'Al-Qamar', 55, 528, 531),
        (55, 'الرحمن', 'Ar-Rahman', 78, 531, 534),
        (56, 'الواقعة', 'Al-Waqiah', 96, 534, 537),
        (57, 'الحديد', 'Al-Hadid', 29, 537, 541),
        (58, 'المجادلة', 'Al-Mujadilah', 22, 542, 545),
        (59, 'الحشر', 'Al-Hasyr', 24, 545, 548),
        (60, 'الممتحنة', 'Al-Mumtahanah', 13, 549, 551),
        (61, 'الصف', 'As-Saff', 14, 551, 552),
        (62, 'الجمعة', 'Al-Jumuah', 11, 553, 554),
        (63, 'المنافقون', 'Al-Munafiqun', 11, 554, 555),
        (64, 'التغابن', 'At-Taghabun', 18, 556, 557),
        (65, 'الطلاق', 'At-Talaq', 12, 558, 559),
        (66, 'التحريم', 'At-Tahrim', 12, 560, 561),
        (67, 'الملك', 'Al-Mulk', 30, 562, 564),
        (68, 'القلم', 'Al-Qalam', 52, 564, 566),
        (69, 'الحاقة', 'Al-Haqqah', 52, 566, 568),
        (70, 'المعارج', 'Al-Maarij', 44, 568, 570),
        (71, 'نوح', 'Nuh', 28, 570, 571),
        (72, 'الجن', 'Al-Jinn', 28, 572, 573),
        (73, 'المزمل', 'Al-Muzzammil', 20, 574, 575),
        (74, 'المدثر', 'Al-Muddatstsir', 56, 575, 577),
        (75, 'القيامة', 'Al-Qiyamah', 40, 577, 578),
        (76, 'الإنسان', 'Al-Insan', 31, 578, 580),
        (77, 'المرسلات', 'Al-Mursalat', 50, 580, 581),
        (78, 'النبأ', 'An-Naba', 40, 582, 583),
        (79, 'النازعات', 'An-Naziat', 46, 583, 584),
        (80, 'عبس', 'Abasa', 42, 585, 586),
        (81, 'التكوير', 'At-Takwir', 29, 586, 586),
        (82, 'الانفطار', 'Al-Infitar', 19, 587, 587),
        (83, 'المطففين', 'Al-Muthaffifin', 36, 587, 589),
        (84, 'الانشقاق', 'Al-Insyiqaq', 25, 589, 590),
        (85, 'البروج', 'Al-Buruj', 22, 590, 590),
        (86, 'الطارق', 'At-Thariq', 17, 591, 591),
        (87, 'الأعلى', 'Al-Ala', 19, 591, 592),
        (88, 'الغاشية', 'Al-Ghasyiyah', 26, 592, 593),
        (89, 'الفجر', 'Al-Fajr', 30, 593, 594),
        (90, 'البلد', 'Al-Balad', 20, 594, 595),
        (91, 'الشمس', 'Asy-Syams', 15, 595, 595),
        (92, 'الليل', 'Al-Lail', 21, 595, 596),
        (93, 'الضحى', 'Ad-Dhuha', 11, 596, 596),
        (94, 'الشرح', 'Asy-Syarh', 8, 596, 596),
        (95, 'التين', 'At-Tin', 8, 597, 597),
        (96, 'العلق', 'Al-Alaq', 19, 597, 597),
        (97, 'القدر', 'Al-Qadr', 5, 598, 598),
        (98, 'البينة', 'Al-Bayyinah', 8, 598, 599),
        (99, 'الزلزلة', 'Az-Zalzalah', 8, 599, 599),
        (100, 'العاديات', 'Al-Adiyat', 11, 599, 600),
        (101, 'القارعة', 'Al-Qariah', 11, 600, 600),
        (102, 'التكاثر', 'At-Takatsur', 8, 600, 600),
        (103, 'العصر', 'Al-Asr', 3, 601, 601),
        (104, 'الهمزة', 'Al-Humazah', 9, 601, 601),
        (105, 'الفيل', 'Al-Fil', 5, 601, 601),
        (106, 'قريش', 'Quraisy', 4, 602, 602),
        (107, 'الماعون', 'Al-Maun', 7, 602, 602),
        (108, 'الكوثر', 'Al-Kautsar', 3, 602, 602),
        (109, 'الكافرون', 'Al-Kafirun', 6, 603, 603),
        (110, 'النصر', 'An-Nasr', 3, 603, 603),
        (111, 'المسد', 'Al-Lahab', 5, 603, 603),
        (112, 'الإخلاص', 'Al-Ikhlas', 4, 604, 604),
        (113, 'الفلق', 'Al-Falaq', 5, 604, 604),
        (114, 'الناس', 'An-Nas', 6, 604, 604)
    ]

    sql = []
    sql.append("-- SEED DATA FOR AKFIOKE (Yayasan Islam Al Kahfi Batam)")
    sql.append("USE akfioke_db;\nSET FOREIGN_KEY_CHECKS = 0;\n")

    # Roles
    roles = [
        ('operator_yayasan', 'Operator Yayasan', 'Akses penuh yayasan, kelola sekolah, pegawai, mutasi, master AKFI, backup'),
        ('direktur_pendidikan', 'Direktur Pendidikan', 'Dashboard yayasan, rekap AKFI, Atasan Berikut / HR'),
        ('deputi_pendidikan', 'Deputi Direktur Pendidikan', 'Approval administrasi KS, deadline, SPMB target, AKFI KS, sikap KS'),
        ('operator_sekolah', 'Operator Sekolah', 'Kelola siswa, kelas, mapel, akun ortu/guru, persetujuan pindah TA, SPMB'),
        ('tutor_siswa', 'Tutor Siswa', 'Uji tahfidz siswa, penentuan lulus juz, input nilai bulanan tahfidz guru'),
        ('tutor_sebaya', 'Tutor Sebaya', 'Assign halaqah guru, target hafalan guru, input nilai tahfidz guru bulanan'),
        ('tata_usaha', 'Tata Usaha', 'Tagihan manual siswa, pengajuan penagihan, upload berkas keuangan, checklist braja'),
        ('cleaning_service', 'Cleaning Service', 'Checklist harian, piket bulanan, gotong royong Sabtu, kepuasan kebersihan'),
        ('security', 'Security', 'Checklist laporan harian per hari ini, patroli, insiden kebobolan/gagalkan kriminalitas'),
        ('waka_kurikulum', 'Wakil Kurikulum', 'Pencatatan pelanggaran kehadiran guru, absen kajian guru, jadwal supervisi, deadline admin'),
        ('guru_supervisor', 'Guru Supervisor', 'Penilaian supervisi kelas (rubrik A/B/C), cetak instrumen'),
        ('waka_kesiswaan', 'Wakil Kesiswaan', 'Kehadiran, kajian, verifikasi prestasi lomba siswa'),
        ('koor_tahfidz', 'Koordinator Tahfidz', 'Assign halaqah siswa, rekap tuntas siswa, baseline 3x setahun, cetak rapor tahfidz'),
        ('walas', 'Guru Wali Kelas', 'Nilai bulanan, absensi siswa, leger, rapor bulanan, PTK, rekomendasi kenaikan/alumni'),
        ('guru_mapel', 'Guru Mapel Umum', 'Nilai bulanan, KKM, Best Practice, administrasi perangkat 8 item'),
        ('guru_diniyah', 'Guru Mapel Diniyah', 'Nilai diniyah bulanan, Best Practice, administrasi perangkat 8 item'),
        ('guru_tahfidz', 'Guru Tahfidz', 'Input hafalan siswa bulanan (kalkulator madinah), pengajuan ujian tutor siswa'),
        ('orang_tua', 'Orang Tua', 'Unduh rapor bulanan/tahfidz/diniyah/gabungan, survei ortu 53 butir, cek tagihan'),
        ('panitia_lomba', 'Ketua Panitia Lomba', 'Input siswa juara lomba OSN/O2SN/FLS2N tingkat kota/provinsi/nasional'),
        ('kepala_sekolah', 'Kepala Sekolah', 'Approval admin guru/TU, rekap hafalan/kegiatan/tunggakan, absensi TU/CS/Security, sikap guru'),
        ('siswa', 'Siswa (SMP/SMA)', 'Akses siswa untuk survei kepuasan pembelajaran')
    ]
    for r in roles:
        sql.append(f"INSERT INTO roles (name, display_name, description) VALUES ('{r[0]}', '{r[1]}', '{r[2]}') ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);")

    # Units
    units = [
        ('KB', 'KB Islam Al Kahfi Batam', 'KB', 'YAYASAN ISLAM AL KAHFI BATAM', 'PENDIDIKAN ANAK USIA DINI (KB) ISLAM AL KAHFI', 'Komp. Al Kahfi Islamic Center, Batam Centre, Kota Batam', '0778-123456', 'kb@alkahfi.sch.id', 'Bank Syariah Indonesia (BSI)', '7123450001', 'Yayasan Islam Al Kahfi Batam'),
        ('TK', 'TK Islam Al Kahfi Batam', 'TK', 'YAYASAN ISLAM AL KAHFI BATAM', 'TAMAN KANAK-KANAK ISLAM AL KAHFI', 'Komp. Al Kahfi Islamic Center, Batam Centre, Kota Batam', '0778-123457', 'tk@alkahfi.sch.id', 'Bank Syariah Indonesia (BSI)', '7123450002', 'Yayasan Islam Al Kahfi Batam'),
        ('SD', 'SD Islam Al Kahfi Batam', 'SD', 'YAYASAN ISLAM AL KAHFI BATAM', 'SEKOLAH DASAR ISLAM AL KAHFI', 'Komp. Al Kahfi Islamic Center, Batam Centre, Kota Batam', '0778-123458', 'sd@alkahfi.sch.id', 'Bank Syariah Indonesia (BSI)', '7123450003', 'Yayasan Islam Al Kahfi Batam'),
        ('SMP', 'SMP Islam Al Kahfi Batam', 'SMP', 'YAYASAN ISLAM AL KAHFI BATAM', 'SEKOLAH MENENGAH PERTAMA ISLAM AL KAHFI', 'Komp. Al Kahfi Islamic Center, Batam Centre, Kota Batam', '0778-123459', 'smp@alkahfi.sch.id', 'Bank Syariah Indonesia (BSI)', '7123450004', 'Yayasan Islam Al Kahfi Batam'),
        ('SMA', 'SMA Islam Al Kahfi Batam', 'SMA', 'YAYASAN ISLAM AL KAHFI BATAM', 'SEKOLAH MENENGAH ATAS ISLAM AL KAHFI', 'Komp. Al Kahfi Islamic Center, Batam Centre, Kota Batam', '0778-123460', 'sma@alkahfi.sch.id', 'Bank Syariah Indonesia (BSI)', '7123450005', 'Yayasan Islam Al Kahfi Batam')
    ]
    for u in units:
        sql.append(f"INSERT INTO units (code, name, level, kop_title, kop_subtitle, address, phone, email, bank_name, bank_account, bank_holder) VALUES ('{u[0]}', '{u[1]}', '{u[2]}', '{u[3]}', '{u[4]}', '{u[5]}', '{u[6]}', '{u[7]}', '{u[8]}', '{u[9]}', '{u[10]}') ON DUPLICATE KEY UPDATE name=VALUES(name);")

    # Academic Year
    sql.append("INSERT INTO academic_years (id, name, semester, is_active, is_locked) VALUES (1, '2024/2025', 'Genap', 1, 0) ON DUPLICATE KEY UPDATE is_active=1;")
    for u_id in range(1, 6):
        sql.append(f"INSERT INTO academic_year_approvals (academic_year_id, unit_id, status) VALUES (1, {u_id}, 'Setuju') ON DUPLICATE KEY UPDATE status='Setuju';")

    # Mushaf Surahs
    for s in surahs_data:
        sql.append(f"INSERT INTO mushaf_surahs (surah_number, name_ar, name_id, verses_count, start_page, end_page) VALUES ({s[0]}, '{s[1]}', '{s[2]}', {s[3]}, {s[4]}, {s[5]}) ON DUPLICATE KEY UPDATE name_ar=VALUES(name_ar), name_id=VALUES(name_id), verses_count=VALUES(verses_count), start_page=VALUES(start_page), end_page=VALUES(end_page);")

    # Mushaf Madinah 604 pages mapping
    for p in range(1, 605):
        juz = min(30, (p - 1) // 20 + 1)
        # Find which surah starts on this page
        s_match = 1
        for s in surahs_data:
            if s[4] <= p <= s[5]:
                s_match = s[0]
                break
        sql.append(f"INSERT INTO mushaf_madinah (page_number, juz_number, surah_start, ayah_start, surah_end, ayah_end, lines_count) VALUES ({p}, {juz}, {s_match}, 1, {s_match}, 10, 15) ON DUPLICATE KEY UPDATE lines_count=15;")

    # Default Accounts (Password: 'password123')
    pwd_hash = '$2y$10$ZBjEuAo13K8d7567rki5cujPiaxxHR8mI6/f1uQkxm2I6OiYpVnia'

    default_users = [
        (1, 'operator.yayasan', pwd_hash, 'admin@alkahfi.sch.id', 'Ahmad Operator Yayasan', '08110000001', 'operator_yayasan', None),
        (2, 'direktur.pendidikan', pwd_hash, 'direktur@alkahfi.sch.id', 'Dr. H. Muhammad Ridwan, M.Pd (Direktur/HR)', '08110000002', 'direktur_pendidikan', None),
        (3, 'deputi.pendidikan', pwd_hash, 'deputi@alkahfi.sch.id', 'Ust. Fathurrahman, S.Pd.I (Deputi Direktur)', '08110000003', 'deputi_pendidikan', None),
        (4, 'operator.sd', pwd_hash, 'ops.sd@alkahfi.sch.id', 'Rian Hidayat, S.Kom (Operator SD)', '08110000004', 'operator_sekolah', 3),
        (5, 'operator.tk', pwd_hash, 'ops.tk@alkahfi.sch.id', 'Siti Rahma (Operator TK)', '08110000005', 'operator_sekolah', 2),
        (6, 'operator.smp', pwd_hash, 'ops.smp@alkahfi.sch.id', 'Budi Santoso (Operator SMP)', '08110000006', 'operator_sekolah', 4),
        (7, 'operator.sma', pwd_hash, 'ops.sma@alkahfi.sch.id', 'Dewi Lestari (Operator SMA)', '08110000007', 'operator_sekolah', 5),
        (8, 'kepsek.sd', pwd_hash, 'kepsek.sd@alkahfi.sch.id', 'Ust. Abdullah Syafiq, M.Pd.I (Kepsek SD)', '08110000008', 'kepala_sekolah', 3),
        (9, 'kepsek.tk', pwd_hash, 'kepsek.tk@alkahfi.sch.id', 'Usth. Maryam Zahra, S.Pd (Kepsek TK)', '08110000009', 'kepala_sekolah', 2),
        (10, 'kepsek.smp', pwd_hash, 'kepsek.smp@alkahfi.sch.id', 'Ust. Salman Al Farisi, Lc (Kepsek SMP)', '08110000010', 'kepala_sekolah', 4),
        (11, 'kepsek.sma', pwd_hash, 'kepsek.sma@alkahfi.sch.id', 'Ust. H. Zainuddin, M.A (Kepsek SMA)', '08110000011', 'kepala_sekolah', 5),
        (12, 'waka.kurikulum', pwd_hash, 'waka.kurikulum@alkahfi.sch.id', 'Umar Hamzah, M.Pd (Waka Kurikulum SD)', '08110000012', 'waka_kurikulum', 3),
        (13, 'waka.kesiswaan', pwd_hash, 'waka.kesiswaan@alkahfi.sch.id', 'Khalid Basalamah, S.Pd (Waka Kesiswaan SD)', '08110000013', 'waka_kesiswaan', 3),
        (14, 'tutor.siswa', pwd_hash, 'tutor.siswa@alkahfi.sch.id', 'Ust. Hanif Al Hafidz (Tutor Siswa & Guru Tahfidz)', '08110000014', 'tutor_siswa', 3),
        (15, 'tutor.sebaya', pwd_hash, 'tutor.sebaya@alkahfi.sch.id', 'Ust. Muadz Al Hafidz (Tutor Sebaya Guru)', '08110000015', 'tutor_sebaya', 3),
        (16, 'koor.tahfidz', pwd_hash, 'koor.tahfidz@alkahfi.sch.id', 'Ust. Bilal Syahid, S.Q (Koordinator Tahfidz)', '08110000016', 'koor_tahfidz', 3),
        (17, 'walas.sd', pwd_hash, 'walas.sd@alkahfi.sch.id', 'Aisyah Wardani, S.Pd (Guru Walas 1A)', '08110000017', 'walas', 3),
        (18, 'guru.mapel', pwd_hash, 'guru.mapel@alkahfi.sch.id', 'Fajar Pratama, S.Pd (Guru Matematika)', '08110000018', 'guru_mapel', 3),
        (19, 'guru.diniyah', pwd_hash, 'guru.diniyah@alkahfi.sch.id', 'Ust. Hamzah Robbani, Lc (Guru PAI/Fiqih)', '08110000019', 'guru_diniyah', 3),
        (20, 'guru.tahfidz', pwd_hash, 'guru.tahfidz@alkahfi.sch.id', 'Usth. Khadijah Al Hafidzah (Guru Tahfidz SD)', '08110000020', 'guru_tahfidz', 3),
        (21, 'guru.supervisor', pwd_hash, 'guru.supervisor@alkahfi.sch.id', 'Drs. Supriyadi, M.Pd (Guru Supervisor)', '08110000021', 'guru_supervisor', 3),
        (22, 'panitia.lomba', pwd_hash, 'panitia.lomba@alkahfi.sch.id', 'Indra Gunawan, S.Pd (Ketua Panitia OSN)', '08110000022', 'panitia_lomba', 3),
        (23, 'tu.sd', pwd_hash, 'tu.sd@alkahfi.sch.id', 'Sri Wahyuni, A.Md (Staf TU Keuangan SD)', '08110000023', 'tata_usaha', 3),
        (24, 'cs.sd', pwd_hash, 'cs.sd@alkahfi.sch.id', 'Pak Joko Sutrisno (Cleaning Service SD)', '08110000024', 'cleaning_service', 3),
        (25, 'security.sd', pwd_hash, 'security.sd@alkahfi.sch.id', 'Pak Agus Salim (Komandan Security SD)', '08110000025', 'security', 3),
        (26, 'ortu.santri', pwd_hash, 'ortu.santri@alkahfi.sch.id', 'Bpk. Hendra Gunawan (Wali Santri No. KK 2171012345670001)', '08127000001', 'orang_tua', 3),
        (27, 'siswa.smp', pwd_hash, 'siswa.smp@alkahfi.sch.id', 'Muhammad Rayyan (Siswa SMP Kelas 8A)', '08127000002', 'siswa', 4),
    ]

    for u in default_users:
        sql.append(f"INSERT INTO users (id, username, password_hash, email, full_name, phone, is_active, force_change_password) VALUES ({u[0]}, '{u[1]}', '{u[2]}', '{u[3]}', '{u[4]}', '{u[5]}', 1, 0) ON DUPLICATE KEY UPDATE full_name=VALUES(full_name);")
        # Role mapping
        sql.append(f"INSERT INTO user_roles (user_id, role_id, is_primary) SELECT {u[0]}, id, 1 FROM roles WHERE name='{u[6]}' ON DUPLICATE KEY UPDATE is_primary=1;")

    # Additional roles for testing multi-role switcher:
    # walas.sd has walas (primary) and guru_mapel
    sql.append("INSERT INTO user_roles (user_id, role_id, is_primary) SELECT 17, id, 0 FROM roles WHERE name='guru_mapel' ON DUPLICATE KEY UPDATE is_primary=VALUES(is_primary);")
    # tutor.siswa has tutor_siswa and guru_tahfidz
    sql.append("INSERT INTO user_roles (user_id, role_id, is_primary) SELECT 14, id, 0 FROM roles WHERE name='guru_tahfidz' ON DUPLICATE KEY UPDATE is_primary=VALUES(is_primary);")
    # waka.kurikulum has waka_kurikulum, guru_mapel, and guru_supervisor
    sql.append("INSERT INTO user_roles (user_id, role_id, is_primary) SELECT 12, id, 0 FROM roles WHERE name='guru_mapel' ON DUPLICATE KEY UPDATE is_primary=VALUES(is_primary);")
    sql.append("INSERT INTO user_roles (user_id, role_id, is_primary) SELECT 12, id, 0 FROM roles WHERE name='guru_supervisor' ON DUPLICATE KEY UPDATE is_primary=VALUES(is_primary);")

    # Employees
    employees = [
        (1, 2, 'YAY-DIR-001', '2171010101700001', 'Dr. H. Muhammad Ridwan, M.Pd', 'L', 'Batam', '1970-01-01', '2015-07-01', 'Direktur Pendidikan', None, None, '08110000001', '08110000002', 'direktur@alkahfi.sch.id'),
        (2, 3, 'YAY-DEP-002', '2171010101750002', 'Ust. Fathurrahman, S.Pd.I', 'L', 'Padang', '1975-05-10', '2016-07-01', 'Deputi Direktur', None, 1, '08110000002', '08110000003', 'deputi@alkahfi.sch.id'),
        (3, 8, 'SD-KS-001', '2171010101800003', 'Ust. Abdullah Syafiq, M.Pd.I', 'L', 'Medan', '1980-08-15', '2017-07-01', 'Kepala Sekolah', 3, 2, '08110000003', '08110000008', 'kepsek.sd@alkahfi.sch.id'),
        (4, 9, 'TK-KS-001', '2171010101820004', 'Usth. Maryam Zahra, S.Pd', 'P', 'Batam', '1982-03-20', '2018-07-01', 'Kepala Sekolah', 2, 2, '08110000003', '08110000009', 'kepsek.tk@alkahfi.sch.id'),
        (5, 10, 'SMP-KS-001', '2171010101810005', 'Ust. Salman Al Farisi, Lc', 'L', 'Solo', '1981-11-12', '2017-07-01', 'Kepala Sekolah', 4, 2, '08110000003', '08110000010', 'kepsek.smp@alkahfi.sch.id'),
        (6, 11, 'SMA-KS-001', '2171010101780006', 'Ust. H. Zainuddin, M.A', 'L', 'Jakarta', '1978-04-25', '2016-07-01', 'Kepala Sekolah', 5, 2, '08110000003', '08110000011', 'kepsek.sma@alkahfi.sch.id'),
        (7, 12, 'SD-GUR-001', '2171010101850007', 'Umar Hamzah, M.Pd', 'L', 'Batam', '1985-09-09', '2019-07-01', 'Wakil Kurikulum / Guru Mapel', 3, 3, '08110000008', '08110000012', 'waka.kurikulum@alkahfi.sch.id'),
        (8, 13, 'SD-GUR-002', '2171010101860008', 'Khalid Basalamah, S.Pd', 'L', 'Batam', '1986-06-14', '2019-07-01', 'Wakil Kesiswaan', 3, 3, '08110000008', '08110000013', 'waka.kesiswaan@alkahfi.sch.id'),
        (9, 14, 'SD-GUR-003', '2171010101880009', 'Ust. Hanif Al Hafidz', 'L', 'Kudus', '1988-12-01', '2020-07-01', 'Tutor Siswa / Guru Tahfidz', 3, 3, '08110000008', '08110000014', 'tutor.siswa@alkahfi.sch.id'),
        (10, 15, 'SD-GUR-004', '2171010101870010', 'Ust. Muadz Al Hafidz', 'L', 'Jombang', '1987-07-07', '2020-07-01', 'Tutor Sebaya Guru', 3, 3, '08110000008', '08110000015', 'tutor.sebaya@alkahfi.sch.id'),
        (11, 16, 'SD-GUR-005', '2171010101840011', 'Ust. Bilal Syahid, S.Q', 'L', 'Batam', '1984-02-18', '2018-07-01', 'Koordinator Tahfidz', 3, 3, '08110000008', '08110000016', 'koor.tahfidz@alkahfi.sch.id'),
        (12, 17, 'SD-GUR-006', '2171010101900012', 'Aisyah Wardani, S.Pd', 'P', 'Tanjungpinang', '1990-05-15', '2021-07-01', 'Wali Kelas / Guru Mapel', 3, 3, '08110000008', '08110000017', 'walas.sd@alkahfi.sch.id'),
        (13, 18, 'SD-GUR-007', '2171010101920013', 'Fajar Pratama, S.Pd', 'L', 'Batam', '1992-10-10', '2022-07-01', 'Guru Mapel Umum', 3, 3, '08110000008', '08110000018', 'guru.mapel@alkahfi.sch.id'),
        (14, 19, 'SD-GUR-008', '2171010101890014', 'Ust. Hamzah Robbani, Lc', 'L', 'Batam', '1989-08-20', '2021-07-01', 'Guru Mapel Diniyah', 3, 3, '08110000008', '08110000019', 'guru.diniyah@alkahfi.sch.id'),
        (15, 20, 'SD-GUR-009', '2171010101930015', 'Usth. Khadijah Al Hafidzah', 'P', 'Batam', '1993-01-30', '2022-07-01', 'Guru Tahfidz', 3, 3, '08110000008', '08110000020', 'guru.tahfidz@alkahfi.sch.id'),
        (16, 21, 'SD-GUR-010', '2171010101760016', 'Drs. Supriyadi, M.Pd', 'L', 'Yogyakarta', '1976-03-12', '2016-07-01', 'Guru Supervisor', 3, 3, '08110000008', '08110000021', 'guru.supervisor@alkahfi.sch.id'),
        (17, 22, 'SD-GUR-011', '2171010101910017', 'Indra Gunawan, S.Pd', 'L', 'Batam', '1991-04-05', '2021-07-01', 'Guru / Ketua Panitia Lomba', 3, 3, '08110000008', '08110000022', 'panitia.lomba@alkahfi.sch.id'),
        (18, 23, 'SD-STF-001', '2171010101940018', 'Sri Wahyuni, A.Md', 'P', 'Batam', '1994-07-19', '2022-01-01', 'Tata Usaha Keuangan', 3, 3, '08110000008', '08110000023', 'tu.sd@alkahfi.sch.id'),
        (19, 24, 'SD-STF-002', '2171010101830019', 'Pak Joko Sutrisno', 'L', 'Batam', '1983-11-23', '2020-01-01', 'Cleaning Services', 3, 3, '08110000008', '08110000024', 'cs.sd@alkahfi.sch.id'),
        (20, 25, 'SD-STF-003', '2171010101850020', 'Pak Agus Salim', 'L', 'Batam', '1985-02-14', '2019-01-01', 'Security', 3, 3, '08110000008', '08110000025', 'security.sd@alkahfi.sch.id'),
    ]

    for e in employees:
        sup = str(e[11]) if e[11] is not None else 'NULL'
        sql.append(f"INSERT INTO employees (id, user_id, nipy, nik, nama, gender, birthplace, birthdate, tmt, level_posisi, unit_id, atasan_langsung_id, no_telp_atasan, contact_phone, email, status) VALUES ({e[0]}, {e[1]}, '{e[2]}', '{e[3]}', '{e[4]}', '{e[5]}', '{e[6]}', '{e[7]}', '{e[8]}', '{e[9]}', {e[10]}, {sup}, '{e[12]}', '{e[13]}', '{e[14]}', 'Aktif') ON DUPLICATE KEY UPDATE nama=VALUES(nama);")

    # Classes
    classes = [
        (1, 2, 1, 'TK A Al-Fatihah', 'TK A', 4),
        (2, 2, 1, 'TK B Al-Baqarah', 'TK B', 4),
        (3, 3, 1, 'Kelas 1A Abu Bakar', 'SD 1', 12),
        (4, 3, 1, 'Kelas 1B Umar bin Khattab', 'SD 1', 12),
        (5, 3, 1, 'Kelas 2A Utsman bin Affan', 'SD 2', 12),
        (6, 4, 1, 'Kelas 7A Thariq bin Ziyad', 'SMP 7', 8),
        (7, 4, 1, 'Kelas 8A Salahuddin Al-Ayyubi', 'SMP 8', 8),
        (8, 5, 1, 'Kelas 10 MIPA Al-Biruni', 'SMA 10', 8)
    ]
    for c in classes:
        sql.append(f"INSERT INTO classes (id, unit_id, academic_year_id, name, level, walas_id) VALUES ({c[0]}, {c[1]}, {c[2]}, '{c[3]}', '{c[4]}', {c[5]}) ON DUPLICATE KEY UPDATE name=VALUES(name);")

    # Subjects
    subjects = [
        # TK
        (1, 2, 'TK-01', 'Baca Latin', 'Baca Latin'),
        (2, 2, 'TK-02', 'Tahsin Al-Quran', 'Tahsin'),
        (3, 2, 'TK-03', 'Tahfidz Al-Quran', 'Tahfidz'),
        (4, 2, 'TK-04', 'National Kurikulum TK', 'National Kurikulum'),
        (5, 2, 'TK-05', 'Diniyah & Doa Harian', 'Diniyah'),
        # SD
        (6, 3, 'SD-MAT', 'Matematika', 'Umum'),
        (7, 3, 'SD-BIN', 'Bahasa Indonesia', 'Umum'),
        (8, 3, 'SD-IPA', 'Ilmu Pengetahuan Alam', 'Umum'),
        (9, 3, 'SD-PAI', 'Pendidikan Agama Islam', 'Diniyah'),
        (10, 3, 'SD-ARB', 'Bahasa Arab', 'Diniyah'),
        (11, 3, 'SD-TAH', 'Tahfidzul Quran', 'Tahfidz'),
        (12, 3, 'SD-THS', 'Tahsin & Tilawah', 'Tahsin'),
        # SMP
        (13, 4, 'SMP-MAT', 'Matematika', 'Umum'),
        (14, 4, 'SMP-ING', 'Bahasa Inggris', 'Umum'),
        (15, 4, 'SMP-TAH', 'Tahfidzul Quran', 'Tahfidz'),
        (16, 4, 'SMP-THS', 'Tahsin Al-Quran', 'Tahsin'),
        (17, 4, 'SMP-DIN', 'Fiqih & Aqidah Akhlak', 'Diniyah')
    ]
    for sub in subjects:
        sql.append(f"INSERT INTO subjects (id, unit_id, code, name, type) VALUES ({sub[0]}, {sub[1]}, '{sub[2]}', '{sub[3]}', '{sub[4]}') ON DUPLICATE KEY UPDATE name=VALUES(name);")

    # Teacher Subjects
    teacher_subs = [
        (12, 6, 3, 1), # Aisyah Wardani: Matematika Kelas 1A
        (12, 7, 3, 1), # Aisyah Wardani: B.Indo Kelas 1A
        (13, 6, 4, 1), # Fajar Pratama: Matematika Kelas 1B
        (14, 9, 3, 1), # Hamzah Robbani: PAI Kelas 1A
        (14, 10, 3, 1), # Hamzah Robbani: B.Arab Kelas 1A
        (15, 11, 3, 1), # Khadijah: Tahfidz Kelas 1A
        (15, 12, 3, 1), # Khadijah: Tahsin Kelas 1A
    ]
    for ts in teacher_subs:
        sql.append(f"INSERT INTO teacher_subjects (employee_id, subject_id, class_id, academic_year_id) VALUES ({ts[0]}, {ts[1]}, {ts[2]}, {ts[3]});")

    # Students & Parent Linking via No. KK
    # Same No. KK for 2 children across units: TK and SD!
    students = [
        (1, '20240101', '0012345671', '2171011505180001', '2171012345670001', 'Ahmad Faris Gunawan', 'L', 'Batam', '2018-05-15', 'Jl. Sukajadi No. 12, Batam', 'Hendra Gunawan', 'Siti Fatimah', '08127000001', 'ortu.santri@alkahfi.sch.id', 26, None, 3, 'Aktif'),
        (2, '20240201', '0012345672', '2171012008200002', '2171012345670001', 'Fatimah Az-Zahra Gunawan', 'P', 'Batam', '2020-08-20', 'Jl. Sukajadi No. 12, Batam', 'Hendra Gunawan', 'Siti Fatimah', '08127000001', 'ortu.santri@alkahfi.sch.id', 26, None, 2, 'Aktif'),
        (3, '20240102', '0012345673', '2171011003180003', '2171012345670002', 'Zaidan Al-Ghifari', 'L', 'Batam', '2018-03-10', 'Batam Centre Blok B No. 5', 'Bambang Irawan', 'Nurul Hidayah', '08127000003', 'bambang@gmail.com', None, None, 3, 'Aktif'),
        (4, '20240103', '0012345674', '2171012507180004', '2171012345670003', 'Aisyah Putri Maulida', 'P', 'Batam', '2018-07-25', 'Tiban Indah Permai C-8', 'Maulana Malik', 'Ratna Sari', '08127000004', 'maulana@gmail.com', None, None, 3, 'Aktif'),
        (5, '20240301', '0012345675', '2171011111100005', '2171012345670004', 'Muhammad Rayyan', 'L', 'Batam', '2010-11-11', 'Nongsa Asri Blok D No. 2', 'Ridwan Kamil', 'Laila Sari', '08127000005', 'ridwan@gmail.com', None, 27, 4, 'Aktif'),
    ]
    for s in students:
        pid = str(s[14]) if s[14] is not None else 'NULL'
        sid = str(s[15]) if s[15] is not None else 'NULL'
        sql.append(f"INSERT INTO students (id, nis, nisn, nik, no_kk, nama, gender, birthplace, birthdate, address, father_name, mother_name, parent_phone, parent_email, parent_user_id, student_user_id, unit_id, status) VALUES ({s[0]}, '{s[1]}', '{s[2]}', '{s[3]}', '{s[4]}', '{s[5]}', '{s[6]}', '{s[7]}', '{s[8]}', '{s[9]}', '{s[10]}', '{s[11]}', '{s[12]}', '{s[13]}', {pid}, {sid}, {s[16]}, '{s[17]}') ON DUPLICATE KEY UPDATE nama=VALUES(nama);")

    # Class Students
    class_stds = [
        (3, 1, 1), # Faris -> Kelas 1A SD
        (1, 2, 1), # Fatimah -> TK A
        (3, 3, 1), # Zaidan -> Kelas 1A SD
        (3, 4, 1), # Aisyah Putri -> Kelas 1A SD
        (7, 5, 1), # Rayyan -> Kelas 8A SMP
    ]
    for cs in class_stds:
        sql.append(f"INSERT INTO class_students (class_id, student_id, academic_year_id, status) VALUES ({cs[0]}, {cs[1]}, {cs[2]}, 'Aktif') ON DUPLICATE KEY UPDATE status='Aktif';")

    # Master Jenis Administrasi
    # Kepala Sekolah (18 items from DOKUMEN ADMINISTRASI KEPALA SEKOLAH KB TK SD SMP SMA.pdf)
    admin_ks = [
        ('KS-01', 'Struktur Kurikulum yang disahkan Divisi Pendidikan', '1x setahun', 'Awal Tahun Ajaran'),
        ('KS-02', 'Struktur Beban Mengajar Guru yang disahkan Divisi Pendidikan', '1x setahun', 'Awal Tahun Ajaran'),
        ('KS-03', 'Kalender Pendidikan Unit yang disahkan Divisi Pendidikan', '1x setahun', 'Awal Tahun Ajaran'),
        ('KS-04', 'Struktur Organisasi Sekolah', '1x setahun', 'Awal Tahun Ajaran'),
        ('KS-05', 'SK Komite Sekolah', '1x setahun', 'Awal Tahun Ajaran'),
        ('KS-06', 'PPT Parents Meeting', '1x setahun', 'Semester Ganjil'),
        ('KS-07', 'Laporan Pelaksanaan Parent Meeting', '1x setahun', 'Semester Ganjil'),
        ('KS-08', 'KKM (KPI) Mata Pelajaran', '1x setahun', 'Awal Tahun Ajaran'),
        ('KS-09', 'Dokumen 1 Sekolah', '1x setahun', 'Awal Tahun Ajaran'),
        ('KS-10', 'Administrasi Supervisi - SK Assessor, Jadwal, Blanko Penilaian, laporan nilai', '1x setahun', 'Akhir Semester Genap'),
        ('KS-11', 'Base line Tahfidz siswa', '3x setahun', 'Awal, Tengah, Akhir Tahun'),
        ('KS-12', 'Raport Pendidikan', '1x setahun', 'Akhir Tahun'),
        ('KS-13', 'Laporan Bulanan', 'setiap bulan', 'Tiap akhir bulan berjalan'),
        ('KS-14', 'Laporan Closing Laporan Keuangan Bulanan (CA)', 'setiap bulan', 'Tiap tanggal 5 bulan berikutnya'),
        ('KS-15', 'Laporan Penilaian Kinerja Guru (AKFI)', '1x setahun', 'Akhir Tahun Ajaran'),
        ('KS-16', 'Administrasi Pelaksanaan SPMB (Standard dan Pedoman Kerja SPMB, laporan pelaksanaan)', '1x setahun', 'Periode SPMB'),
        ('KS-17', 'Laporan Pelaksanaan Kegiatan UAS (Nilai Murni) dan Ijazah kelas akhir', '2x setahun', 'Tiap Semester'),
        ('KS-18', 'Dokumen pelacakan alumni', '1x setahun', 'Akhir Tahun Ajaran'),
    ]
    for i, a in enumerate(admin_ks):
        sql.append(f"INSERT INTO master_jenis_administrasi (target_role, code, title, frequency, due_info, order_num) VALUES ('KEPALA_SEKOLAH', '{a[0]}', '{a[1]}', '{a[2]}', '{a[3]}', {i+1});")

    # Guru TK (7 items)
    admin_tk = [
        ('TK-ADM-01', 'Program Tahunan (Prota)', '1x setahun', 'Awal Tahun Ajaran'),
        ('TK-ADM-02', 'Program Semester (Promes)', '2x setahun', 'Awal Tiap Semester'),
        ('TK-ADM-03', 'Silabus', '1x setahun', 'Awal Tahun Ajaran'),
        ('TK-ADM-04', 'Rencana Pelaksanaan Pembelajaran (RPP) / Modul Ajar', 'setiap bulan', 'Tiap awal bulan'),
        ('TK-ADM-05', 'Kalender Pendidikan', '1x setahun', 'Awal Tahun Ajaran'),
        ('TK-ADM-06', 'Analisis Ketercapaian Kurikulum', '2x setahun', 'Tiap Akhir Semester'),
        ('TK-ADM-07', 'Administrasi Kelas', '1x setahun', 'Berjalan'),
    ]
    for i, a in enumerate(admin_tk):
        sql.append(f"INSERT INTO master_jenis_administrasi (target_role, code, title, frequency, due_info, order_num) VALUES ('GURU_TK', '{a[0]}', '{a[1]}', '{a[2]}', '{a[3]}', {i+1});")

    # Guru Mapel SD/SMP/SMA (8 items)
    admin_mapel = [
        ('GUR-01', 'Perencanaan Pembelajaran: Alur Tujuan Pembelajaran (ATP) / Prota', '1x setahun', 'Awal Tahun Ajaran'),
        ('GUR-02', 'Perencanaan Pembelajaran: Silabus', '1x setahun', 'Awal Tahun Ajaran'),
        ('GUR-03', 'Perencanaan Pembelajaran: RPP / Modul Ajar (max 3 mapel diampu)', 'setiap bulan', 'Bulanan'),
        ('GUR-04', 'Pelaksanaan Pembelajaran: Daftar Hadir Siswa per Mata Pelajaran', 'setiap bulan', 'Bulanan'),
        ('GUR-05', 'Pelaksanaan Pembelajaran: Jurnal Mengajar', 'setiap bulan', 'Bulanan'),
        ('GUR-06', 'Penilaian: Kisi-kisi Soal (Ujian Semester)', '2x setahun', 'Tiap Ujian Semester'),
        ('GUR-07', 'Penilaian: Daftar Nilai Harian, Tugas, Ujian, Nilai Raport', 'setiap bulan', 'Bulanan'),
        ('GUR-08', 'Administrasi Tambahan: Dokumentasi Kegiatan Pembelajaran (min 2x)', '2x setahun', 'Tiap Semester'),
    ]
    for i, a in enumerate(admin_mapel):
        sql.append(f"INSERT INTO master_jenis_administrasi (target_role, code, title, frequency, due_info, order_num) VALUES ('GURU_MAPEL', '{a[0]}', '{a[1]}', '{a[2]}', '{a[3]}', {i+1});")

    # Wali Kelas (6 main categories)
    admin_walas = [
        ('WALAS-01', 'Data dan Identitas Siswa (Buku Induk, Biodata, Struktur Organisasi, Denah, Foto)', '1x setahun', 'Awal Tahun Ajaran'),
        ('WALAS-02', 'Kehadiran dan Ketertiban (Daftar Hadir Harian, Rekap, Catatan Pelanggaran, Reward & Punishment)', 'setiap bulan', 'Bulanan'),
        ('WALAS-03', 'Komunikasi dengan Orangtua (Buku Kontak, Notulen Pertemuan, Buku Penghubung, Perkembangan)', 'setiap bulan', 'Bulanan'),
        ('WALAS-04', 'Penilaian dan Rapor (Rekap Nilai Guru Mapel, Analisis Nilai, Rapor Siswa, Leger)', '2x setahun', 'Tiap Semester'),
        ('WALAS-05', 'Kegiatan Kesiswaan (Jadwal Piket, Dokumentasi Kelas, Agenda Harian/Mingguan)', 'setiap bulan', 'Bulanan'),
        ('WALAS-06', 'Administrasi Tambahan (Buku Kas Kelas, Inventaris Barang, Catatan Khusus Siswa)', '1x setahun', 'Berjalan'),
    ]
    for i, a in enumerate(admin_walas):
        sql.append(f"INSERT INTO master_jenis_administrasi (target_role, code, title, frequency, due_info, order_num) VALUES ('WALAS', '{a[0]}', '{a[1]}', '{a[2]}', '{a[3]}', {i+1});")

    # Tata Usaha (4 items)
    admin_tu = [
        ('TU-01', 'Terkumpulnya dokumen keuangan bulanan berikut lampiran pemakaian uang', 'setiap bulan', 'Bulanan'),
        ('TU-02', 'Penyampaian laporan keuangan bulanan tepat waktu', 'setiap bulan', 'Tiap tanggal 5'),
        ('TU-03', 'Terkumpulnya dokumen penggajian bulanan berikut lampiran pemakaian uang', 'setiap bulan', 'Bulanan'),
        ('TU-04', 'Penyampaian laporan penggajian bulanan tepat waktu', 'setiap bulan', 'Tiap tanggal 25'),
    ]
    for i, a in enumerate(admin_tu):
        sql.append(f"INSERT INTO master_jenis_administrasi (target_role, code, title, frequency, due_info, order_num) VALUES ('TU', '{a[0]}', '{a[1]}', '{a[2]}', '{a[3]}', {i+1});")

    # AKFI Master Templates & Items for 10 Positions
    # 1. GURU_MAPEL (SD/SMP/SMA)
    sql.append("INSERT INTO akfi_templates (id, position_code, position_name, academic_year_id, bobot_kinerja, bobot_sikap, bobot_absensi, grade_a_min, grade_b_min, grade_c_min, grade_d_min, grade_e_min) VALUES (1, 'GURU_MAPEL', 'Guru Mata Pelajaran (SD/SMP/SMA)', 1, 65.00, 20.00, 15.00, 91.00, 81.00, 61.00, 31.00, 11.00) ON DUPLICATE KEY UPDATE position_name=VALUES(position_name);")
    items_guru_mapel = [
        # A. Kinerja (65)
        ('A', 'Administrasi', 'K-ADM-01', 'Terkumpulnya dokumen perangkat perencanaan pembelajaran', '%', 100.00, 5.00, 'higher_better', 'administrasi_completion', 1),
        ('A', 'Administrasi', 'K-ADM-02', '% penyelesaian tugas sesuai deadline & kualitas', '%', 100.00, 5.00, 'higher_better', 'administrasi_ontime', 2),
        ('A', 'Pembelajaran', 'K-PEM-01', '% capaian kehadiran siswa di kelas yang diajarkan', '%', 90.00, 9.00, 'higher_better', 'student_attendance', 3),
        ('A', 'Pembelajaran', 'K-PEM-02', 'Terkumpulnya Best Practice', 'jumlah', 1.00, 10.00, 'higher_better', 'upload_best_practice', 4),
        ('A', 'Pembelajaran', 'K-PEM-03', 'Capaian nilai rata-rata mapel yang diajarkan', 'angka', 75.00, 10.00, 'higher_better', 'subject_average', 5),
        ('A', 'Performance', 'K-PRF-01', 'Capaian nilai supervisi mengajar', 'angka', 88.00, 10.00, 'higher_better', 'supervisi_avg', 6),
        ('A', 'Performance', 'K-PRF-02', '% target hafalan tahunan guru (1 Juz)', '%', 100.00, 6.00, 'higher_better', 'teacher_tahfidz_target', 7),
        ('A', 'Performance', 'K-PRF-03', '% kehadiran dalam kegiatan sekolah', '%', 95.00, 10.00, 'higher_better', 'kegiatan_kbm_attendance', 8),
        # B. Sikap (20)
        ('B', 'Sikap', 'S-01', 'Communication (Lisan & tulisan jelas dan mudah dipahami)', 'SKALA 1-5', 5.00, 4.00, 'higher_better', None, 9),
        ('B', 'Sikap', 'S-02', 'Profesionalisme (Refleksi diri & pemanfaatan TIK)', 'SKALA 1-5', 5.00, 4.00, 'higher_better', None, 10),
        ('B', 'Sikap', 'S-03', 'Teamwork (Kerjasama rekan kerja)', 'SKALA 1-5', 5.00, 3.00, 'higher_better', None, 11),
        ('B', 'Sikap', 'S-04', 'Sikap Empati & Sense of Belonging', 'SKALA 1-5', 5.00, 3.00, 'higher_better', None, 12),
        ('B', 'Sikap', 'S-05', 'Loyalitas & Militansi', 'SKALA 1-5', 5.00, 3.00, 'higher_better', None, 13),
        ('B', 'Sikap', 'S-06', 'Keteladanan & Disiplin Islami', 'SKALA 1-5', 5.00, 3.00, 'higher_better', None, 14),
        # C. Absensi (15) Target 6 hari/thn
        ('C', 'Absensi', 'AB-01', 'Datang Terlambat', 'Hari/Thn', 6.00, 2.00, 'lower_better', 'att_terlambat', 15),
        ('C', 'Absensi', 'AB-02', 'Pulang Cepat', 'Hari/Thn', 6.00, 2.00, 'lower_better', 'att_pulang_cepat', 16),
        ('C', 'Absensi', 'AB-03', 'Lupa Catat', 'Hari/Thn', 6.00, 1.00, 'lower_better', 'att_lupa_catat', 17),
        ('C', 'Absensi', 'AB-04', 'Tidak Hadir / Mangkir', 'Hari/Thn', 6.00, 10.00, 'lower_better', 'att_mangkir', 18)
    ]
    for it in items_guru_mapel:
        sql.append(f"INSERT INTO akfi_template_items (template_id, section, category, item_code, item_name, unit_type, target_val, weight, direction, auto_source, order_num) VALUES (1, '{it[0]}', '{it[1]}', '{it[2]}', '{it[3]}', '{it[4]}', {it[5]}, {it[6]}, '{it[7]}', {repr(it[8]) if it[8] else 'NULL'}, {it[9]});")

    # 2. GURU_TAHTA (SD/SMP/SMA)
    sql.append("INSERT INTO akfi_templates (id, position_code, position_name, academic_year_id, bobot_kinerja, bobot_sikap, bobot_absensi, grade_a_min, grade_b_min, grade_c_min, grade_d_min, grade_e_min) VALUES (2, 'GURU_TAHTA', 'Guru Tahfidz / Tahta (SD/SMP/SMA)', 1, 65.00, 20.00, 15.00, 91.00, 81.00, 61.00, 31.00, 11.00) ON DUPLICATE KEY UPDATE position_name=VALUES(position_name);")
    for it in items_guru_mapel:
        if it[2] == 'K-PEM-03':
            # Replace subject avg with % siswa lulus target tahfidz (target 80%, weight 10)
            sql.append(f"INSERT INTO akfi_template_items (template_id, section, category, item_code, item_name, unit_type, target_val, weight, direction, auto_source, order_num) VALUES (2, 'A', 'Pembelajaran', 'K-PEM-03', '% siswa lulus target tahfidz', '%', 80.00, 10.00, 'higher_better', 'tahfidz_pass_pct', 5);")
        else:
            sql.append(f"INSERT INTO akfi_template_items (template_id, section, category, item_code, item_name, unit_type, target_val, weight, direction, auto_source, order_num) VALUES (2, '{it[0]}', '{it[1]}', '{it[2]}', '{it[3]}', '{it[4]}', {it[5]}, {it[6]}, '{it[7]}', {repr(it[8]) if it[8] else 'NULL'}, {it[9]});")

    # 3. GURU_WALAS (SD/SMP/SMA)
    sql.append("INSERT INTO akfi_templates (id, position_code, position_name, academic_year_id, bobot_kinerja, bobot_sikap, bobot_absensi, grade_a_min, grade_b_min, grade_c_min, grade_d_min, grade_e_min) VALUES (3, 'GURU_WALAS', 'Guru Wali Kelas (SD/SMP/SMA)', 1, 65.00, 20.00, 15.00, 91.00, 81.00, 61.00, 31.00, 11.00) ON DUPLICATE KEY UPDATE position_name=VALUES(position_name);")
    items_walas = [
        ('A', 'Administrasi', 'K-ADM-01', 'Terkumpulnya dokumen perangkat perencanaan pembelajaran', '%', 100.00, 5.00, 'higher_better', 'administrasi_completion', 1),
        ('A', 'Administrasi', 'K-ADM-02', '% penyelesaian tugas sesuai deadline & kualitas', '%', 100.00, 5.00, 'higher_better', 'administrasi_ontime', 2),
        ('A', 'Pembelajaran', 'K-PEM-01', '% capaian kehadiran siswa (target 100%)', '%', 100.00, 9.00, 'higher_better', 'student_attendance', 3),
        ('A', 'Pembelajaran', 'K-PEM-02', 'Terkumpulnya PTK (Penelitian Tindakan Kelas)', 'jumlah', 1.00, 10.00, 'higher_better', 'upload_ptk', 4),
        ('A', 'Pembelajaran', 'K-PEM-03', 'Capaian nilai rata-rata mapel', 'angka', 95.00, 10.00, 'higher_better', 'subject_average', 5),
        ('A', 'Performance', 'K-PRF-01', 'Capaian nilai supervisi mengajar', 'angka', 90.00, 8.00, 'higher_better', 'supervisi_avg', 6),
        ('A', 'Performance', 'K-PRF-02', 'Jumlah siswa yang menjuarai lomba (juara 1)', 'Orang', 3.00, 4.00, 'higher_better', 'juara_lomba_walas', 7),
        ('A', 'Performance', 'K-PRF-03', '% siswa lulus target tahfidz', '%', 100.00, 1.00, 'higher_better', 'tahfidz_pass_pct', 8),
        ('A', 'Performance', 'K-PRF-04', '% target hafalan tahunan guru (1 Juz)', '%', 100.00, 5.00, 'higher_better', 'teacher_tahfidz_target', 9),
        ('A', 'Performance', 'K-PRF-05', '% kehadiran dalam kegiatan sekolah', '%', 95.00, 8.00, 'higher_better', 'kegiatan_kbm_attendance', 10),
        # Sikap (20)
        ('B', 'Sikap', 'S-01', 'Communication', 'SKALA 1-5', 5.00, 4.00, 'higher_better', None, 11),
        ('B', 'Sikap', 'S-02', 'Profesionalisme', 'SKALA 1-5', 5.00, 4.00, 'higher_better', None, 12),
        ('B', 'Sikap', 'S-03', 'Teamwork', 'SKALA 1-5', 5.00, 3.00, 'higher_better', None, 13),
        ('B', 'Sikap', 'S-04', 'Sikap Empati & Sense of Belonging', 'SKALA 1-5', 5.00, 3.00, 'higher_better', None, 14),
        ('B', 'Sikap', 'S-05', 'Loyalitas & Militansi', 'SKALA 1-5', 5.00, 3.00, 'higher_better', None, 15),
        ('B', 'Sikap', 'S-06', 'Keteladanan & Disiplin', 'SKALA 1-5', 5.00, 3.00, 'higher_better', None, 16),
        # Absensi (15)
        ('C', 'Absensi', 'AB-01', 'Datang Terlambat', 'Hari/Thn', 6.00, 2.00, 'lower_better', 'att_terlambat', 17),
        ('C', 'Absensi', 'AB-02', 'Pulang Cepat', 'Hari/Thn', 6.00, 2.00, 'lower_better', 'att_pulang_cepat', 18),
        ('C', 'Absensi', 'AB-03', 'Lupa Catat', 'Hari/Thn', 6.00, 1.00, 'lower_better', 'att_lupa_catat', 19),
        ('C', 'Absensi', 'AB-04', 'Tidak Hadir', 'Hari/Thn', 6.00, 10.00, 'lower_better', 'att_mangkir', 20),
    ]
    for it in items_walas:
        sql.append(f"INSERT INTO akfi_template_items (template_id, section, category, item_code, item_name, unit_type, target_val, weight, direction, auto_source, order_num) VALUES (3, '{it[0]}', '{it[1]}', '{it[2]}', '{it[3]}', '{it[4]}', {it[5]}, {it[6]}, '{it[7]}', {repr(it[8]) if it[8] else 'NULL'}, {it[9]});")

    # 4. GURU_TK (KB & TK)
    sql.append("INSERT INTO akfi_templates (id, position_code, position_name, academic_year_id, bobot_kinerja, bobot_sikap, bobot_absensi, grade_a_min, grade_b_min, grade_c_min, grade_d_min, grade_e_min) VALUES (4, 'GURU_TK', 'Guru TK / KB', 1, 65.00, 20.00, 15.00, 91.00, 81.00, 61.00, 31.00, 11.00) ON DUPLICATE KEY UPDATE position_name=VALUES(position_name);")
    items_tk = [
        ('A', 'Administrasi', 'K-ADM-01', 'Terkumpulnya dokumen perangkat perencanaan pembelajaran', '%', 100.00, 5.00, 'higher_better', 'administrasi_completion', 1),
        ('A', 'Administrasi', 'K-ADM-02', '% penyelesaian tugas sesuai deadline & kualitas', '%', 100.00, 5.00, 'higher_better', 'administrasi_ontime', 2),
        ('A', 'Pembelajaran', 'K-PEM-01', '% capaian kehadiran siswa (target 90%)', '%', 90.00, 9.00, 'higher_better', 'student_attendance', 3),
        ('A', 'Pembelajaran', 'K-PEM-02', 'Capaian nilai rata-rata pembelajaran umum', 'angka', 90.00, 10.00, 'higher_better', 'subject_average_umum', 4),
        ('A', 'Pembelajaran', 'K-PEM-03', 'Capaian nilai rata-rata pembelajaran diniyah', 'angka', 90.00, 10.00, 'higher_better', 'subject_average_diniyah', 5),
        ('A', 'Performance', 'K-PRF-01', 'Capaian nilai supervisi mengajar', 'angka', 85.00, 10.00, 'higher_better', 'supervisi_avg', 6),
        ('A', 'Performance', 'K-PRF-02', '% target hafalan tahunan guru (1/2 Juz)', '%', 100.00, 6.00, 'higher_better', 'teacher_tahfidz_target', 7),
        ('A', 'Performance', 'K-PRF-03', '% kehadiran dalam kegiatan sekolah', '%', 95.00, 10.00, 'higher_better', 'kegiatan_kbm_attendance', 8),
        # Sikap
        ('B', 'Sikap', 'S-01', 'Communication', 'SKALA 1-5', 5.00, 4.00, 'higher_better', None, 9),
        ('B', 'Sikap', 'S-02', 'Profesionalisme', 'SKALA 1-5', 5.00, 4.00, 'higher_better', None, 10),
        ('B', 'Sikap', 'S-03', 'Teamwork', 'SKALA 1-5', 5.00, 3.00, 'higher_better', None, 11),
        ('B', 'Sikap', 'S-04', 'Sikap Empati & Sense of Belonging', 'SKALA 1-5', 5.00, 3.00, 'higher_better', None, 12),
        ('B', 'Sikap', 'S-05', 'Loyalitas & Militansi', 'SKALA 1-5', 5.00, 3.00, 'higher_better', None, 13),
        ('B', 'Sikap', 'S-06', 'Keteladanan & Disiplin', 'SKALA 1-5', 5.00, 3.00, 'higher_better', None, 14),
        # Absensi (15)
        ('C', 'Absensi', 'AB-01', 'Datang Terlambat', 'Hari/Thn', 6.00, 2.00, 'lower_better', 'att_terlambat', 15),
        ('C', 'Absensi', 'AB-02', 'Pulang Cepat', 'Hari/Thn', 6.00, 2.00, 'lower_better', 'att_pulang_cepat', 16),
        ('C', 'Absensi', 'AB-03', 'Lupa Catat', 'Hari/Thn', 6.00, 1.00, 'lower_better', 'att_lupa_catat', 17),
        ('C', 'Absensi', 'AB-04', 'Tidak Hadir', 'Hari/Thn', 6.00, 10.00, 'lower_better', 'att_mangkir', 18),
    ]
    for it in items_tk:
        sql.append(f"INSERT INTO akfi_template_items (template_id, section, category, item_code, item_name, unit_type, target_val, weight, direction, auto_source, order_num) VALUES (4, '{it[0]}', '{it[1]}', '{it[2]}', '{it[3]}', '{it[4]}', {it[5]}, {it[6]}, '{it[7]}', {repr(it[8]) if it[8] else 'NULL'}, {it[9]});")

    # 5. TU (Tata Usaha) - Bobot disesuaikan 60 + 20 + 20 = 100
    sql.append("INSERT INTO akfi_templates (id, position_code, position_name, academic_year_id, bobot_kinerja, bobot_sikap, bobot_absensi, grade_a_min, grade_b_min, grade_c_min, grade_d_min, grade_e_min) VALUES (5, 'TU', 'Tata Usaha', 1, 60.00, 20.00, 20.00, 90.00, 70.00, 50.00, 30.00, 11.00) ON DUPLICATE KEY UPDATE position_name=VALUES(position_name);")
    items_tu = [
        ('A', 'Administrasi', 'K-TU-01', 'Terkumpulnya dokumen keuangan bulanan berikut lampiran pemakaian uang', '%', 100.00, 15.00, 'higher_better', 'tu_dokumen_keuangan', 1),
        ('A', 'Administrasi', 'K-TU-02', 'Penyampaian laporan keuangan bulanan tepat waktu', '%', 100.00, 15.00, 'higher_better', 'tu_laporan_keuangan_ontime', 2),
        ('A', 'Administrasi', 'K-TU-03', 'Terkumpulnya dokumen penggajian bulanan berikut lampiran pemakaian uang', '%', 100.00, 10.00, 'higher_better', 'tu_dokumen_gaji', 3),
        ('A', 'Administrasi', 'K-TU-04', 'Penyampaian laporan penggajian bulanan tepat waktu', '%', 100.00, 10.00, 'higher_better', 'tu_laporan_gaji_ontime', 4),
        ('A', 'Performance', 'K-TU-05', 'Jumlah kesalahan input Braja (Makin sedikit makin baik)', 'Freq', 0.00, 5.00, 'lower_better', 'tu_braja_errors', 5),
        ('A', 'Performance', 'K-TU-06', '% Dalam kegiatan sekolah', '%', 100.00, 5.00, 'higher_better', 'kegiatan_kbm_attendance', 6),
        # Sikap (20)
        ('B', 'Sikap', 'S-01', 'Communication', 'SKALA 1-5', 5.00, 4.00, 'higher_better', None, 7),
        ('B', 'Sikap', 'S-02', 'Profesionalisme', 'SKALA 1-5', 5.00, 4.00, 'higher_better', None, 8),
        ('B', 'Sikap', 'S-03', 'Teamwork', 'SKALA 1-5', 5.00, 3.00, 'higher_better', None, 9),
        ('B', 'Sikap', 'S-04', 'Sikap Empati & Sense of Belonging', 'SKALA 1-5', 5.00, 3.00, 'higher_better', None, 10),
        ('B', 'Sikap', 'S-05', 'Loyalitas & Militansi', 'SKALA 1-5', 5.00, 3.00, 'higher_better', None, 11),
        ('B', 'Sikap', 'S-06', 'Keteladanan & Disiplin', 'SKALA 1-5', 5.00, 3.00, 'higher_better', None, 12),
        # Absensi (20)
        ('C', 'Absensi', 'AB-01', 'Datang Terlambat', 'Hari/Thn', 6.00, 2.00, 'lower_better', 'att_terlambat', 13),
        ('C', 'Absensi', 'AB-02', 'Pulang Cepat', 'Hari/Thn', 6.00, 2.00, 'lower_better', 'att_pulang_cepat', 14),
        ('C', 'Absensi', 'AB-03', 'Lupa Catat', 'Hari/Thn', 6.00, 1.00, 'lower_better', 'att_lupa_catat', 15),
        ('C', 'Absensi', 'AB-04', 'Tidak Hadir', 'Hari/Thn', 6.00, 15.00, 'lower_better', 'att_mangkir', 16),
    ]
    for it in items_tu:
        sql.append(f"INSERT INTO akfi_template_items (template_id, section, category, item_code, item_name, unit_type, target_val, weight, direction, auto_source, order_num) VALUES (5, '{it[0]}', '{it[1]}', '{it[2]}', '{it[3]}', '{it[4]}', {it[5]}, {it[6]}, '{it[7]}', {repr(it[8]) if it[8] else 'NULL'}, {it[9]});")

    # 6. CS (Cleaning Service) - 60 + 20 + 20 = 100
    sql.append("INSERT INTO akfi_templates (id, position_code, position_name, academic_year_id, bobot_kinerja, bobot_sikap, bobot_absensi, grade_a_min, grade_b_min, grade_c_min, grade_d_min, grade_e_min) VALUES (6, 'CS', 'Cleaning Services', 1, 60.00, 20.00, 20.00, 90.00, 70.00, 50.00, 30.00, 11.00) ON DUPLICATE KEY UPDATE position_name=VALUES(position_name);")
    items_cs = [
        ('A', 'Administrasi', 'K-CS-01', 'Checklist pekerjaan rutin harian', 'Freq', 200.00, 15.00, 'higher_better', 'cs_checklist_rutin', 1),
        ('A', 'Administrasi', 'K-CS-02', 'Jumlah piket bulanan yang dilaksanakan', 'Freq', 200.00, 10.00, 'higher_better', 'cs_checklist_piket', 2),
        ('A', 'Administrasi', 'K-CS-03', 'Jumlah kegiatan gotong royong Sabtu yang terlaksana', 'Freq', 24.00, 10.00, 'higher_better', 'cs_gotong_royong', 3),
        ('A', 'Administrasi', 'K-CS-04', 'Tingkat kepuasan kebersihan wali murid (Survei E.5)', '%', 100.00, 10.00, 'higher_better', 'survey_parent_cleanliness', 4),
        ('A', 'Performance', 'K-CS-05', '% kehadiran dalam kegiatan sekolah', '%', 100.00, 15.00, 'higher_better', 'kegiatan_kbm_attendance', 5),
        # Sikap (20)
        ('B', 'Sikap', 'S-01', 'Communication', 'SKALA 1-5', 5.00, 4.00, 'higher_better', None, 6),
        ('B', 'Sikap', 'S-02', 'Profesionalisme', 'SKALA 1-5', 5.00, 4.00, 'higher_better', None, 7),
        ('B', 'Sikap', 'S-03', 'Teamwork', 'SKALA 1-5', 5.00, 3.00, 'higher_better', None, 8),
        ('B', 'Sikap', 'S-04', 'Sikap Empati & Sense of Belonging', 'SKALA 1-5', 5.00, 3.00, 'higher_better', None, 9),
        ('B', 'Sikap', 'S-05', 'Loyalitas & Militansi', 'SKALA 1-5', 5.00, 3.00, 'higher_better', None, 10),
        ('B', 'Sikap', 'S-06', 'Keteladanan & Disiplin', 'SKALA 1-5', 5.00, 3.00, 'higher_better', None, 11),
        # Absensi (20)
        ('C', 'Absensi', 'AB-01', 'Datang Terlambat', 'Hari/Thn', 6.00, 2.00, 'lower_better', 'att_terlambat', 12),
        ('C', 'Absensi', 'AB-02', 'Pulang Cepat', 'Hari/Thn', 6.00, 2.00, 'lower_better', 'att_pulang_cepat', 13),
        ('C', 'Absensi', 'AB-03', 'Lupa Catat', 'Hari/Thn', 6.00, 1.00, 'lower_better', 'att_lupa_catat', 14),
        ('C', 'Absensi', 'AB-04', 'Tidak Hadir', 'Hari/Thn', 6.00, 15.00, 'lower_better', 'att_mangkir', 15),
    ]
    for it in items_cs:
        sql.append(f"INSERT INTO akfi_template_items (template_id, section, category, item_code, item_name, unit_type, target_val, weight, direction, auto_source, order_num) VALUES (6, '{it[0]}', '{it[1]}', '{it[2]}', '{it[3]}', '{it[4]}', {it[5]}, {it[6]}, '{it[7]}', {repr(it[8]) if it[8] else 'NULL'}, {it[9]});")

    # 7. SECURITY - 50 + 30 + 20 = 100
    sql.append("INSERT INTO akfi_templates (id, position_code, position_name, academic_year_id, bobot_kinerja, bobot_sikap, bobot_absensi, grade_a_min, grade_b_min, grade_c_min, grade_d_min, grade_e_min) VALUES (7, 'SECURITY', 'Security', 1, 50.00, 30.00, 20.00, 91.00, 81.00, 61.00, 31.00, 11.00) ON DUPLICATE KEY UPDATE position_name=VALUES(position_name);")
    items_sec = [
        ('A', 'Administrasi', 'K-SEC-01', 'Laporan harian lengkap', 'Freq', 200.00, 5.00, 'higher_better', 'sec_daily_complete', 1),
        ('A', 'Administrasi', 'K-SEC-02', 'Laporan tepat waktu', '%', 100.00, 5.00, 'higher_better', 'sec_daily_ontime', 2),
        ('A', 'Performance', 'K-SEC-03', 'Patroli & Kesiapsiagaan sesuai jadwal', '%', 100.00, 10.00, 'higher_better', 'sec_patrol_pct', 3),
        ('A', 'Performance', 'K-SEC-04', 'Respon terhadap insiden (Bintang 1-5)', 'Bintang', 5.00, 10.00, 'higher_better', 'sec_incident_rating', 4),
        ('A', 'Performance', 'K-SEC-05', '% Keluhan dari pengguna jasa (makin rendah makin baik)', '%', 0.00, 10.00, 'lower_better', 'sec_complaint_pct', 5),
        ('A', 'Performance', 'K-SEC-06', 'Jumlah keberhasilan menggagalkan kejadian kriminalitas', '%', 100.00, 5.00, 'higher_better', 'sec_prevent_crime', 6),
        ('A', 'Performance', 'K-SEC-07', 'Jumlah Kebobolan (makin sedikit makin baik)', '%', 0.00, 5.00, 'lower_better', 'sec_breach_count', 7),
        # Sikap (30)
        ('B', 'Sikap', 'S-01', 'Sikap dan Penampilan (Fisik prima & seragam rapi)', 'SKALA 1-5', 5.00, 4.00, 'higher_better', None, 8),
        ('B', 'Sikap', 'S-02', 'Komunikasi (Senyum sapa dan salam)', 'SKALA 1-5', 5.00, 5.00, 'higher_better', None, 9),
        ('B', 'Sikap', 'S-03', 'Pelayanan (Sikap melayani tamu & civitas)', 'SKALA 1-5', 5.00, 9.00, 'higher_better', None, 10),
        ('B', 'Sikap', 'S-04', 'Profesionalisme (Pengaturan, penjagaan, patroli)', 'SKALA 1-5', 5.00, 8.00, 'higher_better', None, 11),
        ('B', 'Sikap', 'S-05', 'Loyalitas (Siap & sigap menerima tugas)', 'SKALA 1-5', 5.00, 4.00, 'higher_better', None, 12),
        # Absensi (20) Target 7 hari/thn
        ('C', 'Absensi', 'AB-01', 'Datang Terlambat', 'Hari/Thn', 7.00, 2.00, 'lower_better', 'att_terlambat', 13),
        ('C', 'Absensi', 'AB-02', 'Pulang Cepat', 'Hari/Thn', 7.00, 2.00, 'lower_better', 'att_pulang_cepat', 14),
        ('C', 'Absensi', 'AB-03', 'Lupa Catat', 'Hari/Thn', 7.00, 1.00, 'lower_better', 'att_lupa_catat', 15),
        ('C', 'Absensi', 'AB-04', 'Tidak Hadir', 'Hari/Thn', 7.00, 15.00, 'lower_better', 'att_mangkir', 16),
    ]
    for it in items_sec:
        sql.append(f"INSERT INTO akfi_template_items (template_id, section, category, item_code, item_name, unit_type, target_val, weight, direction, auto_source, order_num) VALUES (7, '{it[0]}', '{it[1]}', '{it[2]}', '{it[3]}', '{it[4]}', {it[5]}, {it[6]}, '{it[7]}', {repr(it[8]) if it[8] else 'NULL'}, {it[9]});")

    # 8. KANIT_SD (Kepala Sekolah SD) - 60 + 30 + 10 = 100
    sql.append("INSERT INTO akfi_templates (id, position_code, position_name, academic_year_id, bobot_kinerja, bobot_sikap, bobot_absensi, grade_a_min, grade_b_min, grade_c_min, grade_d_min, grade_e_min) VALUES (8, 'KANIT_SD', 'Kepala Sekolah (SD)', 1, 60.00, 30.00, 10.00, 91.00, 81.00, 61.00, 31.00, 11.00) ON DUPLICATE KEY UPDATE position_name=VALUES(position_name);")
    items_kanit_sd = [
        # A. Kinerja (60)
        ('A', 'Administrasi', 'K-KS-01', '% Dokumen administrasi sekolah terarsip', '%', 100.00, 3.00, 'higher_better', 'ks_admin_archived', 1),
        ('A', 'Administrasi', 'K-KS-02', '% Penyelesaian tugas sesuai deadline', '%', 100.00, 3.00, 'higher_better', 'ks_admin_ontime', 2),
        ('A', 'Hasil', 'K-KS-03', '% Kehadiran guru (Target 90%)', '%', 90.00, 3.00, 'higher_better', 'unit_teacher_attendance', 3),
        ('A', 'Hasil', 'K-KS-04', '% Capaian Tahfidz (Target 95%)', '%', 95.00, 4.00, 'higher_better', 'unit_tahfidz_pct', 4),
        ('A', 'Hasil', 'K-KS-05', '% Capaian Tahsin (Target 65%)', '%', 65.00, 5.00, 'higher_better', 'unit_tahsin_pct', 5),
        ('A', 'Hasil', 'K-KS-06', 'Capaian nilai rata-rata Diniyah (Target 100%)', '%', 100.00, 5.00, 'higher_better', 'unit_diniyah_avg', 6),
        ('A', 'Hasil', 'K-KS-07', '% Tunggakan sekolah (Makin rendah makin baik, target 0%)', '%', 0.00, 4.00, 'lower_better', 'unit_tunggakan_ratio', 7),
        ('A', 'Performance', 'K-KS-08', 'Jumlah siswa menjuarai OSN/O2SN/FLS2N tingkat kota', 'Orang', 3.00, 3.00, 'higher_better', 'unit_lomba_resmi_count', 8),
        ('A', 'Performance', 'K-KS-09', '% Guru dengan nilai KPI B atau A', '%', 100.00, 4.00, 'higher_better', 'unit_guru_kpi_b', 9),
        ('A', 'Performance', 'K-KS-10', '% Siswa baru dalam SPMB vs Target Deputi', '%', 100.00, 4.00, 'higher_better', 'unit_spmb_achievement', 10),
        ('A', 'Performance', 'K-KS-11', '% Siswa yang melanjutkan ke sekolah favorit', '%', 45.00, 4.00, 'higher_better', 'unit_alumni_favorite', 11),
        ('A', 'Hubungan', 'K-KS-12', 'Rata-rata Turn Over Talent (Target <= 5%, makin rendah makin baik)', '%', 5.00, 3.00, 'lower_better', 'unit_turnover_rate', 12),
        ('A', 'Hubungan', 'K-KS-13', '% Kepuasan walimurid rata-rata unit', '%', 85.00, 3.00, 'higher_better', 'unit_parent_survey_avg', 13),
        ('A', 'SDM', 'K-KS-14', '% Guru berprestasi', '%', 5.00, 3.00, 'higher_better', 'unit_guru_berprestasi_pct', 14),
        ('A', 'SDM', 'K-KS-15', '% Kepuasan guru & karyawan terhadap Kepala Sekolah', '%', 90.00, 3.00, 'higher_better', 'unit_staff_survey_avg', 15),
        ('A', 'SDM', 'K-KS-16', '% Kehadiran guru di kajian pekanan', '%', 100.00, 3.00, 'higher_better', 'unit_kajian_guru_pct', 16),
        ('A', 'SDM', 'K-KS-17', '% Guru mencapai target hafalan tahunan (1/2 - 1 Juz)', '%', 50.00, 3.00, 'higher_better', 'unit_teacher_tahfidz_pass', 17),
        # B. Sikap (30) - dinilai oleh Deputi
        ('B', 'Sikap', 'S-01', 'Communication (Penyampaian ide/maksud jelas & dipahami)', 'SKALA 1-5', 5.00, 4.00, 'higher_better', None, 18),
        ('B', 'Sikap', 'S-02', 'Coaching & Counseling (Penanganan guru)', 'SKALA 1-5', 5.00, 3.00, 'higher_better', None, 19),
        ('B', 'Sikap', 'S-03', 'Problem Solving & Decision Making', 'SKALA 1-5', 5.00, 3.00, 'higher_better', None, 20),
        ('B', 'Sikap', 'S-04', 'Teamwork', 'SKALA 1-5', 5.00, 4.00, 'higher_better', None, 21),
        ('B', 'Sikap', 'S-05', 'Sikap Empati & Sense of Belonging', 'SKALA 1-5', 5.00, 3.00, 'higher_better', None, 22),
        ('B', 'Sikap', 'S-06', 'Loyalitas & Militansi', 'SKALA 1-5', 5.00, 5.00, 'higher_better', None, 23),
        ('B', 'Sikap', 'S-07', 'Pengelolaan SDM', 'SKALA 1-5', 5.00, 4.00, 'higher_better', None, 24),
        ('B', 'Sikap', 'S-08', 'Keteladanan & Disiplin', 'SKALA 1-5', 5.00, 4.00, 'higher_better', None, 25),
        # C. Absensi (10)
        ('C', 'Absensi', 'AB-01', 'Datang Terlambat', 'Hari/Thn', 6.00, 2.00, 'lower_better', 'att_terlambat', 26),
        ('C', 'Absensi', 'AB-02', 'Pulang Cepat', 'Hari/Thn', 6.00, 2.00, 'lower_better', 'att_pulang_cepat', 27),
        ('C', 'Absensi', 'AB-03', 'Lupa Catat', 'Hari/Thn', 6.00, 1.00, 'lower_better', 'att_lupa_catat', 28),
        ('C', 'Absensi', 'AB-04', 'Mangkir', 'Hari/Thn', 6.00, 5.00, 'lower_better', 'att_mangkir', 29),
    ]
    for it in items_kanit_sd:
        sql.append(f"INSERT INTO akfi_template_items (template_id, section, category, item_code, item_name, unit_type, target_val, weight, direction, auto_source, order_num) VALUES (8, '{it[0]}', '{it[1]}', '{it[2]}', '{it[3]}', '{it[4]}', {it[5]}, {it[6]}, '{it[7]}', {repr(it[8]) if it[8] else 'NULL'}, {it[9]});")

    # 9. KANIT_TK (Kepala Sekolah TK)
    sql.append("INSERT INTO akfi_templates (id, position_code, position_name, academic_year_id, bobot_kinerja, bobot_sikap, bobot_absensi, grade_a_min, grade_b_min, grade_c_min, grade_d_min, grade_e_min) VALUES (9, 'KANIT_TK', 'Kepala Sekolah (TK)', 1, 60.00, 30.00, 10.00, 91.00, 81.00, 61.00, 31.00, 11.00) ON DUPLICATE KEY UPDATE position_name=VALUES(position_name);")
    for it in items_kanit_sd:
        if it[2] == 'K-KS-05':
            sql.append(f"INSERT INTO akfi_template_items (template_id, section, category, item_code, item_name, unit_type, target_val, weight, direction, auto_source, order_num) VALUES (9, 'A', 'Hasil', 'K-KS-05', '% Capaian Baca Latin (Target 90%)', '%', 90.00, 4.00, 'higher_better', 'unit_baca_latin_pct', 5);")
        elif it[2] == 'K-KS-06':
            sql.append(f"INSERT INTO akfi_template_items (template_id, section, category, item_code, item_name, unit_type, target_val, weight, direction, auto_source, order_num) VALUES (9, 'A', 'Hasil', 'K-KS-06', '% Capaian National Kurikulum (Target 90%)', '%', 90.00, 4.00, 'higher_better', 'unit_national_kurikulum_pct', 6);")
        else:
            sql.append(f"INSERT INTO akfi_template_items (template_id, section, category, item_code, item_name, unit_type, target_val, weight, direction, auto_source, order_num) VALUES (9, '{it[0]}', '{it[1]}', '{it[2]}', '{it[3]}', '{it[4]}', {it[5]}, {it[6]}, '{it[7]}', {repr(it[8]) if it[8] else 'NULL'}, {it[9]});")

    # 10. KANIT_SMP_SMA (Kepala Sekolah SMP & SMA)
    sql.append("INSERT INTO akfi_templates (id, position_code, position_name, academic_year_id, bobot_kinerja, bobot_sikap, bobot_absensi, grade_a_min, grade_b_min, grade_c_min, grade_d_min, grade_e_min) VALUES (10, 'KANIT_SMP_SMA', 'Kepala Sekolah (SMP & SMA)', 1, 60.00, 30.00, 10.00, 91.00, 81.00, 61.00, 31.00, 11.00) ON DUPLICATE KEY UPDATE position_name=VALUES(position_name);")
    for it in items_kanit_sd:
        if it[2] == 'K-KS-13':
            # Split Hubungan into parent survey and student survey
            sql.append(f"INSERT INTO akfi_template_items (template_id, section, category, item_code, item_name, unit_type, target_val, weight, direction, auto_source, order_num) VALUES (10, 'A', 'Hubungan', 'K-KS-13', '% Kepuasan walimurid rata-rata unit', '%', 85.00, 2.00, 'higher_better', 'unit_parent_survey_avg', 13);")
            sql.append(f"INSERT INTO akfi_template_items (template_id, section, category, item_code, item_name, unit_type, target_val, weight, direction, auto_source, order_num) VALUES (10, 'A', 'Hubungan', 'K-KS-13B', '% Kepuasan siswa terhadap layanan pembelajaran', '%', 85.00, 2.00, 'higher_better', 'unit_student_survey_avg', 14);")
        elif it[2] == 'K-KS-12':
            sql.append(f"INSERT INTO akfi_template_items (template_id, section, category, item_code, item_name, unit_type, target_val, weight, direction, auto_source, order_num) VALUES (10, 'A', 'Hubungan', 'K-KS-12', 'Rata-rata Turn Over Talent', '%', 5.00, 2.00, 'lower_better', 'unit_turnover_rate', 12);")
        else:
            sql.append(f"INSERT INTO akfi_template_items (template_id, section, category, item_code, item_name, unit_type, target_val, weight, direction, auto_source, order_num) VALUES (10, '{it[0]}', '{it[1]}', '{it[2]}', '{it[3]}', '{it[4]}', {it[5]}, {it[6]}, '{it[7]}', {repr(it[8]) if it[8] else 'NULL'}, {it[9]});")

    # Survey Templates
    # 1. Survey Orang Tua (53 questions from SURVEY ORANGTUA.pdf)
    survey_ortu_questions = {
        "A": {
            "title": "A. Layanan Pembayaran",
            "items": [
                "Respon komunikasi petugas dalam melayani pembayaran",
                "Kecepatan layanan pembayaran di tempat",
                "Frekuensi pengingat kewajiban pembayaran",
                "Kemudahan proses pembayaran via transfer",
                "Kejelasan informasi biaya dan tagihan sekolah",
                "Pilihan metode pembayaran yang tersedia",
                "Kelengkapan dan ketepatan bukti pembayaran"
            ]
        },
        "B": {
            "title": "B. Layanan Kesehatan (UKS)",
            "items": [
                "Ketepatan penanganan pertama saat siswa sakit",
                "Komunikasi kepada orangtua terkait tindak lanjut kesehatan",
                "Keramahan petugas kesehatan"
            ]
        },
        "C": {
            "title": "C. Fasilitas",
            "items": [
                "Ketersediaan & kondisi ruang kelas",
                "Ketersediaan & kondisi lapangan / tempat bermain",
                "Ketersediaan & kondisi tempat ibadah / masjid",
                "Ketersediaan & kondisi tempat makan siswa",
                "Ketersediaan & kondisi tempat jajan santri / mini market",
                "Ketersediaan & kondisi suplai air bersih"
            ]
        },
        "D1": {
            "title": "D.1 Komunikasi Wali Kelas dan Guru dengan Orangtua",
            "items": [
                "Responsif dalam komunikasi",
                "Bahasa yang digunakan jelas & santun",
                "Frekuensi & kejelasan informasi terkait siswa/kegiatan sekolah"
            ]
        },
        "D2": {
            "title": "D.2 Layanan Pembelajaran oleh Guru",
            "items": [
                "Metode pengajaran dalam kelas",
                "Metode evaluasi & penilaian",
                "Kehadiran & kedisiplinan guru",
                "Keteladanan guru dalam berakhlak karimah",
                "Perhatian guru terhadap siswa",
                "Media pembelajaran yang digunakan",
                "Pembiasaan penggunaan bahasa asing"
            ]
        },
        "D3": {
            "title": "D.3 Ketercapaian Pembelajaran Siswa",
            "items": [
                "Ketercapaian pembelajaran Tahfidzul Qur'an",
                "Ketercapaian pembelajaran Bahasa Arab",
                "Ketercapaian pembelajaran Ilmu Agama",
                "Ketercapaian pembelajaran Ilmu Umum & Teknologi",
                "Anak memahami materi Pelajaran sesuai jenjangnya",
                "Anak mampu memanfaatkan teknologi secara tepat dan bertanggung jawab",
                "Anak menunjukkan kreativitas dalam belajar",
                "Anak mampu bekerja sama dengan teman (kolaborasi)",
                "Anak mampu mengelola waktu dan menyelesaikan tugas dengan baik (produktivitas)",
                "Anak mampu berkomunikasi dengan sopan dan efektif"
            ]
        },
        "D4": {
            "title": "D.4 Ketercapaian Perubahan Sikap, Akhlak, dan Ibadah Siswa",
            "items": [
                "Ibadah siswa sesuai tuntunan (Anak menunjukkan perilaku religious dan akhlak yang baik)",
                "Sikap siswa terhadap orangtua & keluarga",
                "Sikap siswa terhadap orang lain",
                "Akhlak & adab siswa dalam kegiatan sehari-hari",
                "Anak bersikap disiplin dalam belajar dan aktivitas harian",
                "Anak bertanggung jawab terhadap tugas dan kewajibannya",
                "Anak bersikap jujur dalam perkataan dan perbuatan"
            ]
        },
        "D5": {
            "title": "D.5 Sarana & Prasarana Pembelajaran",
            "items": [
                "Media pembelajaran / alat peraga di kelas",
                "Laboratorium IPA (Biologi, Fisika, Kimia)",
                "Laboratorium Komputer",
                "Perpustakaan / pojok literasi"
            ]
        },
        "E": {
            "title": "E. Kepuasan Secara Umum",
            "items": [
                "Sikap staf & guru dalam membantu dan melayani",
                "Keramahan staf & guru",
                "Kejelasan alur penyampaian keluhan atau saran",
                "Respons sekolah terhadap keluhan",
                "Kebersihan lingkungan sekolah",
                "Keamanan lingkungan sekolah"
            ]
        }
    }
    json_ortu = json.dumps(survey_ortu_questions, ensure_ascii=False).replace("'", "''")
    sql.append(f"INSERT INTO survey_templates (id, code, title, target_respondent, questions_json, is_active) VALUES (1, 'SURVEY_ORTU', 'Kuesioner Kepuasan Layanan Sekolah (Orang Tua)', 'Orang Tua', '{json_ortu}', 1) ON DUPLICATE KEY UPDATE title=VALUES(title);")

    # 2. Survey Guru terhadap Kepala Sekolah (Configurable)
    survey_guru_questions = {
        "A": {
            "title": "Kepemimpinan & Keteladanan",
            "items": [
                "Kepala sekolah memberikan arahan program kerja dengan jelas dan terarah",
                "Kepala sekolah menjadi teladan dalam kedisiplinan dan ibadah",
                "Kepala sekolah terbuka terhadap masukan dan kritik konstruktif"
            ]
        },
        "B": {
            "title": "Manajemen & Dukungan Pembelajaran",
            "items": [
                "Kepala sekolah memberikan dukungan fasilitas untuk proses KBM dan tahfidz",
                "Kepala sekolah mengapresiasi pencapaian dan kinerja guru",
                "Kepala sekolah adil dan bijaksana dalam penyelesaian permasalahan guru/staf"
            ]
        }
    }
    json_guru = json.dumps(survey_guru_questions, ensure_ascii=False).replace("'", "''")
    sql.append(f"INSERT INTO survey_templates (id, code, title, target_respondent, questions_json, is_active) VALUES (2, 'SURVEY_KEPSEK', 'Kuesioner Kepuasan Guru & Staf terhadap Kepala Sekolah', 'Guru', '{json_guru}', 1) ON DUPLICATE KEY UPDATE title=VALUES(title);")

    # 3. Survey Siswa (SMP/SMA)
    survey_siswa_questions = {
        "A": {
            "title": "Kepuasan Layanan Pembelajaran di Sekolah",
            "items": [
                "Guru mengajar dengan jelas, menarik, dan mudah dipahami",
                "Fasilitas kelas dan laboratorium mendukung pembelajaran dengan baik",
                "Program tahfidz dan kegiatan keagamaan membimbing ibadah saya",
                "Sekolah memberikan kesempatan menyalurkan minat, bakat, dan lomba",
                "Lingkungan sekolah aman, nyaman, dan bersih untuk belajar"
            ]
        }
    }
    json_siswa = json.dumps(survey_siswa_questions, ensure_ascii=False).replace("'", "''")
    sql.append(f"INSERT INTO survey_templates (id, code, title, target_respondent, questions_json, is_active) VALUES (3, 'SURVEY_SISWA', 'Kuesioner Kepuasan Siswa terhadap Layanan Sekolah (SMP/SMA)', 'Siswa', '{json_siswa}', 1) ON DUPLICATE KEY UPDATE title=VALUES(title);")

    # Sample Bills
    sample_bills = [
        (1, 3, 1, 1, 1, 2025, 'SPP Bulan Januari 2025', 750000.00, '2025-01-10', 'Lunas', '2025-01-08', 750000.00, 'Transfer BSI', 'Lunas tepat waktu', 23),
        (2, 3, 1, 1, 2, 2025, 'SPP Bulan Februari 2025', 750000.00, '2025-02-10', 'Tertunggak', None, 0.00, None, 'Menunggu pembayaran orang tua', 23),
        (3, 3, 1, 3, 2, 2025, 'SPP Bulan Februari 2025', 750000.00, '2025-02-10', 'Tertunggak', None, 0.00, None, 'Tunggakan 1 bulan', 23),
        (4, 2, 1, 2, 2, 2025, 'SPP TK Bulan Februari 2025', 600000.00, '2025-02-10', 'Lunas', '2025-02-05', 600000.00, 'Transfer BSI', 'Lunas', 23),
        (5, 4, 1, 5, 2, 2025, 'SPP SMP Bulan Februari 2025', 850000.00, '2025-02-10', 'Tertunggak', None, 0.00, None, 'Tunggakan siswa SMP', 23),
    ]
    for b in sample_bills:
        pdate = f"'{b[10]}'" if b[10] else "NULL"
        pmeth = f"'{b[12]}'" if b[12] else "NULL"
        sql.append(f"INSERT INTO bills (id, unit_id, academic_year_id, student_id, bill_month, bill_year, title, amount, due_date, status, paid_date, paid_amount, payment_method, notes, created_by_user_id) VALUES ({b[0]}, {b[1]}, {b[2]}, {b[3]}, {b[4]}, {b[5]}, '{b[6]}', {b[7]}, '{b[8]}', '{b[9]}', {pdate}, {b[11]}, {pmeth}, '{b[13]}', {b[14]}) ON DUPLICATE KEY UPDATE status=VALUES(status);")

    # Sample SPMB Targets
    for u in range(1, 6):
        sql.append(f"INSERT INTO spmb_targets (unit_id, academic_year_id, target_count, set_by_deputi_id) VALUES ({u}, 1, 100, 3) ON DUPLICATE KEY UPDATE target_count=100;")

    # Sample SPMB Registrations
    sql.append("INSERT INTO spmb_registrations (unit_id, academic_year_id, reg_number, student_name, gender, parent_name, phone, previous_school, status) VALUES (3, 1, 'SPMB-SD-2025-001', 'Muhammad Salman', 'L', 'Iskandar Zulkarnain', '081277112233', 'TK Al-Furqan Batam', 'Terdaftar') ON DUPLICATE KEY UPDATE student_name=VALUES(student_name);")
    sql.append("INSERT INTO spmb_registrations (unit_id, academic_year_id, reg_number, student_name, gender, parent_name, phone, previous_school, status) VALUES (3, 1, 'SPMB-SD-2025-002', 'Nabila Zahira', 'P', 'Rahmat Hidayat', '081277112234', 'TK Pertiwi Batam', 'Diterima') ON DUPLICATE KEY UPDATE student_name=VALUES(student_name);")

    # Sample Kegiatan di Luar KBM
    sql.append("INSERT INTO kegiatan_luar_kbm (id, unit_id, academic_year_id, title, event_date, location, created_by_user_id) VALUES (1, 3, 1, 'Kajian Tarhib Ramadhan 1446 H', '2025-02-20', 'Masjid Al Kahfi Batam', 8) ON DUPLICATE KEY UPDATE title=VALUES(title);")
    for emp_id in range(7, 21):
        sql.append(f"INSERT INTO kegiatan_luar_kbm_participants (kegiatan_id, employee_id, status) VALUES (1, {emp_id}, 'Hadir') ON DUPLICATE KEY UPDATE status='Hadir';")

    # Sample Halaqah Tahfidz
    sql.append("INSERT INTO tahfidz_halaqah (id, unit_id, academic_year_id, tutor_employee_id, type, name) VALUES (1, 3, 1, 9, 'Siswa', 'Halaqah Imam Nafi (SD)') ON DUPLICATE KEY UPDATE name=VALUES(name);")
    sql.append("INSERT INTO tahfidz_halaqah_members (halaqah_id, student_id) VALUES (1, 1), (1, 3), (1, 4);")

    sql.append("INSERT INTO tahfidz_halaqah (id, unit_id, academic_year_id, tutor_employee_id, type, name) VALUES (2, 3, 1, 10, 'Guru', 'Halaqah Guru SD Al Kahfi') ON DUPLICATE KEY UPDATE name=VALUES(name);")
    for e_id in [7, 8, 12, 13, 14, 18, 19, 20]:
        sql.append(f"INSERT INTO tahfidz_halaqah_members (halaqah_id, employee_id) VALUES (2, {e_id});")

    # Sample Tahfidz Targets
    sql.append("INSERT INTO tahfidz_targets (academic_year_id, target_for, unit_id, grade_or_employee_id, target_lines, baseline_info, target_info) VALUES (1, 'Siswa', 3, 'SD 1', 300, 'Surah An-Nas', 'Juz 30 (300 Baris)') ON DUPLICATE KEY UPDATE target_lines=300;")
    sql.append("INSERT INTO tahfidz_targets (academic_year_id, target_for, unit_id, grade_or_employee_id, target_lines, baseline_info, target_info) VALUES (1, 'Guru', 3, 'GURU_SD', 300, 'Juz 29', '1 Juz (300 Baris)') ON DUPLICATE KEY UPDATE target_lines=300;")
    sql.append("INSERT INTO tahfidz_targets (academic_year_id, target_for, unit_id, grade_or_employee_id, target_lines, baseline_info, target_info) VALUES (1, 'Guru', 2, 'GURU_TK', 150, 'Juz 30', '1/2 Juz (150 Baris)') ON DUPLICATE KEY UPDATE target_lines=150;")

    # Sample School Favorit Master
    sql.append("INSERT INTO alumni_tracking (id, student_id, unit_id, graduation_year, destination_type, destination_name, is_favorite, major, notes) VALUES (1, 5, 4, '2024', 'Sekolah', 'SMA Negeri 1 Batam', 1, 'MIPA', 'Sekolah Favorit Kota Batam') ON DUPLICATE KEY UPDATE destination_name=VALUES(destination_name);")

    sql.append("\nSET FOREIGN_KEY_CHECKS = 1;\n")

    with open('database/seed.sql', 'w', encoding='utf-8') as f:
        f.write('\n'.join(sql))
    print('seed.sql successfully generated with', len(sql), 'statements.')

if __name__ == '__main__':
    generate_seed()
