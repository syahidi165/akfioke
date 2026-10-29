<?php
// database/fix_mushaf_surahs_utf8.php
// Skrip untuk memperbaiki teks nama surah bahasa Arab pada server VPS

declare(strict_types=1);

require_once dirname(__DIR__) . '/core/Database.php';

use Core\Database;

echo "=======================================================
";
echo "  MEMPERBAIKI NAMA SURAH BAHASA ARAB PADA DATABASE     
";
echo "=======================================================

";

try {
    $db = Database::connect();
    
    // 1. Pastikan koneksi menggunakan utf8mb4
    $db->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "[1/3] Menyesuaikan charset tabel mushaf_surahs ke utf8mb4...
";
    $db->exec("ALTER TABLE `mushaf_surahs` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $db->exec("ALTER TABLE `mushaf_surahs` MODIFY `name_ar` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
    echo "      -> Tabel dan kolom berhasil dikonversi ke utf8mb4_unicode_ci.

";

    echo "[2/3] Memperbarui seluruh 114 nama surah dengan teks Arab yang benar...
";
    $surahData = array (
  0 => 
  array (
    'surah_number' => 1,
    'name_ar' => 'الفاتحة',
    'name_id' => 'Al-Fatihah',
    'verses_count' => 7,
    'start_page' => 1,
    'end_page' => 1,
  ),
  1 => 
  array (
    'surah_number' => 2,
    'name_ar' => 'البقرة',
    'name_id' => 'Al-Baqarah',
    'verses_count' => 286,
    'start_page' => 2,
    'end_page' => 49,
  ),
  2 => 
  array (
    'surah_number' => 3,
    'name_ar' => 'آل عمران',
    'name_id' => 'Ali Imran',
    'verses_count' => 200,
    'start_page' => 50,
    'end_page' => 76,
  ),
  3 => 
  array (
    'surah_number' => 4,
    'name_ar' => 'النساء',
    'name_id' => 'An-Nisa',
    'verses_count' => 176,
    'start_page' => 77,
    'end_page' => 106,
  ),
  4 => 
  array (
    'surah_number' => 5,
    'name_ar' => 'المائدة',
    'name_id' => 'Al-Maidah',
    'verses_count' => 120,
    'start_page' => 106,
    'end_page' => 127,
  ),
  5 => 
  array (
    'surah_number' => 6,
    'name_ar' => 'الأنعام',
    'name_id' => 'Al-Anam',
    'verses_count' => 165,
    'start_page' => 128,
    'end_page' => 150,
  ),
  6 => 
  array (
    'surah_number' => 7,
    'name_ar' => 'الأعراف',
    'name_id' => 'Al-Araf',
    'verses_count' => 206,
    'start_page' => 151,
    'end_page' => 176,
  ),
  7 => 
  array (
    'surah_number' => 8,
    'name_ar' => 'الأنفال',
    'name_id' => 'Al-Anfal',
    'verses_count' => 75,
    'start_page' => 177,
    'end_page' => 186,
  ),
  8 => 
  array (
    'surah_number' => 9,
    'name_ar' => 'التوبة',
    'name_id' => 'At-Taubah',
    'verses_count' => 129,
    'start_page' => 187,
    'end_page' => 207,
  ),
  9 => 
  array (
    'surah_number' => 10,
    'name_ar' => 'يونس',
    'name_id' => 'Yunus',
    'verses_count' => 109,
    'start_page' => 208,
    'end_page' => 221,
  ),
  10 => 
  array (
    'surah_number' => 11,
    'name_ar' => 'هود',
    'name_id' => 'Hud',
    'verses_count' => 123,
    'start_page' => 221,
    'end_page' => 235,
  ),
  11 => 
  array (
    'surah_number' => 12,
    'name_ar' => 'يوسف',
    'name_id' => 'Yusuf',
    'verses_count' => 111,
    'start_page' => 235,
    'end_page' => 248,
  ),
  12 => 
  array (
    'surah_number' => 13,
    'name_ar' => 'الرعد',
    'name_id' => 'Ar-Rad',
    'verses_count' => 43,
    'start_page' => 249,
    'end_page' => 255,
  ),
  13 => 
  array (
    'surah_number' => 14,
    'name_ar' => 'إبراهيم',
    'name_id' => 'Ibrahim',
    'verses_count' => 52,
    'start_page' => 255,
    'end_page' => 261,
  ),
  14 => 
  array (
    'surah_number' => 15,
    'name_ar' => 'الحجر',
    'name_id' => 'Al-Hijr',
    'verses_count' => 99,
    'start_page' => 262,
    'end_page' => 267,
  ),
  15 => 
  array (
    'surah_number' => 16,
    'name_ar' => 'النحل',
    'name_id' => 'An-Nahl',
    'verses_count' => 128,
    'start_page' => 267,
    'end_page' => 281,
  ),
  16 => 
  array (
    'surah_number' => 17,
    'name_ar' => 'الإسراء',
    'name_id' => 'Al-Isra',
    'verses_count' => 111,
    'start_page' => 282,
    'end_page' => 293,
  ),
  17 => 
  array (
    'surah_number' => 18,
    'name_ar' => 'الكهف',
    'name_id' => 'Al-Kahf',
    'verses_count' => 110,
    'start_page' => 293,
    'end_page' => 304,
  ),
  18 => 
  array (
    'surah_number' => 19,
    'name_ar' => 'مريم',
    'name_id' => 'Maryam',
    'verses_count' => 98,
    'start_page' => 305,
    'end_page' => 312,
  ),
  19 => 
  array (
    'surah_number' => 20,
    'name_ar' => 'طه',
    'name_id' => 'Taha',
    'verses_count' => 135,
    'start_page' => 312,
    'end_page' => 321,
  ),
  20 => 
  array (
    'surah_number' => 21,
    'name_ar' => 'الأنبياء',
    'name_id' => 'Al-Anbiya',
    'verses_count' => 112,
    'start_page' => 322,
    'end_page' => 331,
  ),
  21 => 
  array (
    'surah_number' => 22,
    'name_ar' => 'الحج',
    'name_id' => 'Al-Hajj',
    'verses_count' => 78,
    'start_page' => 332,
    'end_page' => 341,
  ),
  22 => 
  array (
    'surah_number' => 23,
    'name_ar' => 'المؤمنون',
    'name_id' => 'Al-Muminun',
    'verses_count' => 118,
    'start_page' => 342,
    'end_page' => 349,
  ),
  23 => 
  array (
    'surah_number' => 24,
    'name_ar' => 'النور',
    'name_id' => 'An-Nur',
    'verses_count' => 64,
    'start_page' => 350,
    'end_page' => 359,
  ),
  24 => 
  array (
    'surah_number' => 25,
    'name_ar' => 'الفرقان',
    'name_id' => 'Al-Furqan',
    'verses_count' => 77,
    'start_page' => 359,
    'end_page' => 366,
  ),
  25 => 
  array (
    'surah_number' => 26,
    'name_ar' => 'الشعراء',
    'name_id' => 'Asy-Syuara',
    'verses_count' => 227,
    'start_page' => 367,
    'end_page' => 376,
  ),
  26 => 
  array (
    'surah_number' => 27,
    'name_ar' => 'النمل',
    'name_id' => 'An-Naml',
    'verses_count' => 93,
    'start_page' => 377,
    'end_page' => 385,
  ),
  27 => 
  array (
    'surah_number' => 28,
    'name_ar' => 'القصص',
    'name_id' => 'Al-Qasas',
    'verses_count' => 88,
    'start_page' => 385,
    'end_page' => 396,
  ),
  28 => 
  array (
    'surah_number' => 29,
    'name_ar' => 'العنكبوت',
    'name_id' => 'Al-Ankabut',
    'verses_count' => 69,
    'start_page' => 396,
    'end_page' => 404,
  ),
  29 => 
  array (
    'surah_number' => 30,
    'name_ar' => 'الروم',
    'name_id' => 'Ar-Rum',
    'verses_count' => 60,
    'start_page' => 404,
    'end_page' => 410,
  ),
  30 => 
  array (
    'surah_number' => 31,
    'name_ar' => 'لقمان',
    'name_id' => 'Luqman',
    'verses_count' => 34,
    'start_page' => 411,
    'end_page' => 414,
  ),
  31 => 
  array (
    'surah_number' => 32,
    'name_ar' => 'السجدة',
    'name_id' => 'As-Sajdah',
    'verses_count' => 30,
    'start_page' => 415,
    'end_page' => 417,
  ),
  32 => 
  array (
    'surah_number' => 33,
    'name_ar' => 'الأحزاب',
    'name_id' => 'Al-Ahzab',
    'verses_count' => 73,
    'start_page' => 418,
    'end_page' => 427,
  ),
  33 => 
  array (
    'surah_number' => 34,
    'name_ar' => 'سبأ',
    'name_id' => 'Saba',
    'verses_count' => 54,
    'start_page' => 428,
    'end_page' => 434,
  ),
  34 => 
  array (
    'surah_number' => 35,
    'name_ar' => 'فاطر',
    'name_id' => 'Fatir',
    'verses_count' => 45,
    'start_page' => 434,
    'end_page' => 440,
  ),
  35 => 
  array (
    'surah_number' => 36,
    'name_ar' => 'يس',
    'name_id' => 'Yasin',
    'verses_count' => 83,
    'start_page' => 440,
    'end_page' => 445,
  ),
  36 => 
  array (
    'surah_number' => 37,
    'name_ar' => 'الصافات',
    'name_id' => 'As-Saffat',
    'verses_count' => 182,
    'start_page' => 446,
    'end_page' => 452,
  ),
  37 => 
  array (
    'surah_number' => 38,
    'name_ar' => 'ص',
    'name_id' => 'Sad',
    'verses_count' => 88,
    'start_page' => 453,
    'end_page' => 458,
  ),
  38 => 
  array (
    'surah_number' => 39,
    'name_ar' => 'الزمر',
    'name_id' => 'Az-Zumar',
    'verses_count' => 75,
    'start_page' => 458,
    'end_page' => 467,
  ),
  39 => 
  array (
    'surah_number' => 40,
    'name_ar' => 'غافر',
    'name_id' => 'Ghafir',
    'verses_count' => 85,
    'start_page' => 467,
    'end_page' => 476,
  ),
  40 => 
  array (
    'surah_number' => 41,
    'name_ar' => 'فصلت',
    'name_id' => 'Fussilat',
    'verses_count' => 54,
    'start_page' => 477,
    'end_page' => 482,
  ),
  41 => 
  array (
    'surah_number' => 42,
    'name_ar' => 'الشورى',
    'name_id' => 'Asy-Syura',
    'verses_count' => 53,
    'start_page' => 483,
    'end_page' => 489,
  ),
  42 => 
  array (
    'surah_number' => 43,
    'name_ar' => 'الزخرف',
    'name_id' => 'Az-Zukhruf',
    'verses_count' => 89,
    'start_page' => 489,
    'end_page' => 495,
  ),
  43 => 
  array (
    'surah_number' => 44,
    'name_ar' => 'الدخان',
    'name_id' => 'Ad-Dukhan',
    'verses_count' => 59,
    'start_page' => 496,
    'end_page' => 498,
  ),
  44 => 
  array (
    'surah_number' => 45,
    'name_ar' => 'الجاثية',
    'name_id' => 'Al-Jatsiyah',
    'verses_count' => 37,
    'start_page' => 499,
    'end_page' => 502,
  ),
  45 => 
  array (
    'surah_number' => 46,
    'name_ar' => 'الأحقاف',
    'name_id' => 'Al-Ahqaf',
    'verses_count' => 35,
    'start_page' => 502,
    'end_page' => 506,
  ),
  46 => 
  array (
    'surah_number' => 47,
    'name_ar' => 'محمد',
    'name_id' => 'Muhammad',
    'verses_count' => 38,
    'start_page' => 507,
    'end_page' => 510,
  ),
  47 => 
  array (
    'surah_number' => 48,
    'name_ar' => 'الفتح',
    'name_id' => 'Al-Fath',
    'verses_count' => 29,
    'start_page' => 511,
    'end_page' => 515,
  ),
  48 => 
  array (
    'surah_number' => 49,
    'name_ar' => 'الحجرات',
    'name_id' => 'Al-Hujurat',
    'verses_count' => 18,
    'start_page' => 515,
    'end_page' => 517,
  ),
  49 => 
  array (
    'surah_number' => 50,
    'name_ar' => 'ق',
    'name_id' => 'Qaf',
    'verses_count' => 45,
    'start_page' => 518,
    'end_page' => 520,
  ),
  50 => 
  array (
    'surah_number' => 51,
    'name_ar' => 'الذاريات',
    'name_id' => 'Adz-Dzariyat',
    'verses_count' => 60,
    'start_page' => 520,
    'end_page' => 523,
  ),
  51 => 
  array (
    'surah_number' => 52,
    'name_ar' => 'الطور',
    'name_id' => 'At-Tur',
    'verses_count' => 49,
    'start_page' => 523,
    'end_page' => 525,
  ),
  52 => 
  array (
    'surah_number' => 53,
    'name_ar' => 'النجم',
    'name_id' => 'An-Najm',
    'verses_count' => 62,
    'start_page' => 526,
    'end_page' => 528,
  ),
  53 => 
  array (
    'surah_number' => 54,
    'name_ar' => 'القمر',
    'name_id' => 'Al-Qamar',
    'verses_count' => 55,
    'start_page' => 528,
    'end_page' => 531,
  ),
  54 => 
  array (
    'surah_number' => 55,
    'name_ar' => 'الرحمن',
    'name_id' => 'Ar-Rahman',
    'verses_count' => 78,
    'start_page' => 531,
    'end_page' => 534,
  ),
  55 => 
  array (
    'surah_number' => 56,
    'name_ar' => 'الواقعة',
    'name_id' => 'Al-Waqiah',
    'verses_count' => 96,
    'start_page' => 534,
    'end_page' => 537,
  ),
  56 => 
  array (
    'surah_number' => 57,
    'name_ar' => 'الحديد',
    'name_id' => 'Al-Hadid',
    'verses_count' => 29,
    'start_page' => 537,
    'end_page' => 541,
  ),
  57 => 
  array (
    'surah_number' => 58,
    'name_ar' => 'المجادلة',
    'name_id' => 'Al-Mujadilah',
    'verses_count' => 22,
    'start_page' => 542,
    'end_page' => 545,
  ),
  58 => 
  array (
    'surah_number' => 59,
    'name_ar' => 'الحشر',
    'name_id' => 'Al-Hasyr',
    'verses_count' => 24,
    'start_page' => 545,
    'end_page' => 548,
  ),
  59 => 
  array (
    'surah_number' => 60,
    'name_ar' => 'الممتحنة',
    'name_id' => 'Al-Mumtahanah',
    'verses_count' => 13,
    'start_page' => 549,
    'end_page' => 551,
  ),
  60 => 
  array (
    'surah_number' => 61,
    'name_ar' => 'الصف',
    'name_id' => 'As-Saff',
    'verses_count' => 14,
    'start_page' => 551,
    'end_page' => 552,
  ),
  61 => 
  array (
    'surah_number' => 62,
    'name_ar' => 'الجمعة',
    'name_id' => 'Al-Jumuah',
    'verses_count' => 11,
    'start_page' => 553,
    'end_page' => 554,
  ),
  62 => 
  array (
    'surah_number' => 63,
    'name_ar' => 'المنافقون',
    'name_id' => 'Al-Munafiqun',
    'verses_count' => 11,
    'start_page' => 554,
    'end_page' => 555,
  ),
  63 => 
  array (
    'surah_number' => 64,
    'name_ar' => 'التغابن',
    'name_id' => 'At-Taghabun',
    'verses_count' => 18,
    'start_page' => 556,
    'end_page' => 557,
  ),
  64 => 
  array (
    'surah_number' => 65,
    'name_ar' => 'الطلاق',
    'name_id' => 'At-Talaq',
    'verses_count' => 12,
    'start_page' => 558,
    'end_page' => 559,
  ),
  65 => 
  array (
    'surah_number' => 66,
    'name_ar' => 'التحريم',
    'name_id' => 'At-Tahrim',
    'verses_count' => 12,
    'start_page' => 560,
    'end_page' => 561,
  ),
  66 => 
  array (
    'surah_number' => 67,
    'name_ar' => 'الملك',
    'name_id' => 'Al-Mulk',
    'verses_count' => 30,
    'start_page' => 562,
    'end_page' => 564,
  ),
  67 => 
  array (
    'surah_number' => 68,
    'name_ar' => 'القلم',
    'name_id' => 'Al-Qalam',
    'verses_count' => 52,
    'start_page' => 564,
    'end_page' => 566,
  ),
  68 => 
  array (
    'surah_number' => 69,
    'name_ar' => 'الحاقة',
    'name_id' => 'Al-Haqqah',
    'verses_count' => 52,
    'start_page' => 566,
    'end_page' => 568,
  ),
  69 => 
  array (
    'surah_number' => 70,
    'name_ar' => 'المعارج',
    'name_id' => 'Al-Maarij',
    'verses_count' => 44,
    'start_page' => 568,
    'end_page' => 570,
  ),
  70 => 
  array (
    'surah_number' => 71,
    'name_ar' => 'نوح',
    'name_id' => 'Nuh',
    'verses_count' => 28,
    'start_page' => 570,
    'end_page' => 571,
  ),
  71 => 
  array (
    'surah_number' => 72,
    'name_ar' => 'الجن',
    'name_id' => 'Al-Jinn',
    'verses_count' => 28,
    'start_page' => 572,
    'end_page' => 573,
  ),
  72 => 
  array (
    'surah_number' => 73,
    'name_ar' => 'المزمل',
    'name_id' => 'Al-Muzzammil',
    'verses_count' => 20,
    'start_page' => 574,
    'end_page' => 575,
  ),
  73 => 
  array (
    'surah_number' => 74,
    'name_ar' => 'المدثر',
    'name_id' => 'Al-Muddatstsir',
    'verses_count' => 56,
    'start_page' => 575,
    'end_page' => 577,
  ),
  74 => 
  array (
    'surah_number' => 75,
    'name_ar' => 'القيامة',
    'name_id' => 'Al-Qiyamah',
    'verses_count' => 40,
    'start_page' => 577,
    'end_page' => 578,
  ),
  75 => 
  array (
    'surah_number' => 76,
    'name_ar' => 'الإنسان',
    'name_id' => 'Al-Insan',
    'verses_count' => 31,
    'start_page' => 578,
    'end_page' => 580,
  ),
  76 => 
  array (
    'surah_number' => 77,
    'name_ar' => 'المرسلات',
    'name_id' => 'Al-Mursalat',
    'verses_count' => 50,
    'start_page' => 580,
    'end_page' => 581,
  ),
  77 => 
  array (
    'surah_number' => 78,
    'name_ar' => 'النبأ',
    'name_id' => 'An-Naba',
    'verses_count' => 40,
    'start_page' => 582,
    'end_page' => 583,
  ),
  78 => 
  array (
    'surah_number' => 79,
    'name_ar' => 'النازعات',
    'name_id' => 'An-Naziat',
    'verses_count' => 46,
    'start_page' => 583,
    'end_page' => 584,
  ),
  79 => 
  array (
    'surah_number' => 80,
    'name_ar' => 'عبس',
    'name_id' => 'Abasa',
    'verses_count' => 42,
    'start_page' => 585,
    'end_page' => 586,
  ),
  80 => 
  array (
    'surah_number' => 81,
    'name_ar' => 'التكوير',
    'name_id' => 'At-Takwir',
    'verses_count' => 29,
    'start_page' => 586,
    'end_page' => 586,
  ),
  81 => 
  array (
    'surah_number' => 82,
    'name_ar' => 'الانفطار',
    'name_id' => 'Al-Infitar',
    'verses_count' => 19,
    'start_page' => 587,
    'end_page' => 587,
  ),
  82 => 
  array (
    'surah_number' => 83,
    'name_ar' => 'المطففين',
    'name_id' => 'Al-Muthaffifin',
    'verses_count' => 36,
    'start_page' => 587,
    'end_page' => 589,
  ),
  83 => 
  array (
    'surah_number' => 84,
    'name_ar' => 'الانشقاق',
    'name_id' => 'Al-Insyiqaq',
    'verses_count' => 25,
    'start_page' => 589,
    'end_page' => 590,
  ),
  84 => 
  array (
    'surah_number' => 85,
    'name_ar' => 'البروج',
    'name_id' => 'Al-Buruj',
    'verses_count' => 22,
    'start_page' => 590,
    'end_page' => 590,
  ),
  85 => 
  array (
    'surah_number' => 86,
    'name_ar' => 'الطارق',
    'name_id' => 'At-Thariq',
    'verses_count' => 17,
    'start_page' => 591,
    'end_page' => 591,
  ),
  86 => 
  array (
    'surah_number' => 87,
    'name_ar' => 'الأعلى',
    'name_id' => 'Al-Ala',
    'verses_count' => 19,
    'start_page' => 591,
    'end_page' => 592,
  ),
  87 => 
  array (
    'surah_number' => 88,
    'name_ar' => 'الغاشية',
    'name_id' => 'Al-Ghasyiyah',
    'verses_count' => 26,
    'start_page' => 592,
    'end_page' => 593,
  ),
  88 => 
  array (
    'surah_number' => 89,
    'name_ar' => 'الفجر',
    'name_id' => 'Al-Fajr',
    'verses_count' => 30,
    'start_page' => 593,
    'end_page' => 594,
  ),
  89 => 
  array (
    'surah_number' => 90,
    'name_ar' => 'البلد',
    'name_id' => 'Al-Balad',
    'verses_count' => 20,
    'start_page' => 594,
    'end_page' => 595,
  ),
  90 => 
  array (
    'surah_number' => 91,
    'name_ar' => 'الشمس',
    'name_id' => 'Asy-Syams',
    'verses_count' => 15,
    'start_page' => 595,
    'end_page' => 595,
  ),
  91 => 
  array (
    'surah_number' => 92,
    'name_ar' => 'الليل',
    'name_id' => 'Al-Lail',
    'verses_count' => 21,
    'start_page' => 595,
    'end_page' => 596,
  ),
  92 => 
  array (
    'surah_number' => 93,
    'name_ar' => 'الضحى',
    'name_id' => 'Ad-Dhuha',
    'verses_count' => 11,
    'start_page' => 596,
    'end_page' => 596,
  ),
  93 => 
  array (
    'surah_number' => 94,
    'name_ar' => 'الشرح',
    'name_id' => 'Asy-Syarh',
    'verses_count' => 8,
    'start_page' => 596,
    'end_page' => 596,
  ),
  94 => 
  array (
    'surah_number' => 95,
    'name_ar' => 'التين',
    'name_id' => 'At-Tin',
    'verses_count' => 8,
    'start_page' => 597,
    'end_page' => 597,
  ),
  95 => 
  array (
    'surah_number' => 96,
    'name_ar' => 'العلق',
    'name_id' => 'Al-Alaq',
    'verses_count' => 19,
    'start_page' => 597,
    'end_page' => 597,
  ),
  96 => 
  array (
    'surah_number' => 97,
    'name_ar' => 'القدر',
    'name_id' => 'Al-Qadr',
    'verses_count' => 5,
    'start_page' => 598,
    'end_page' => 598,
  ),
  97 => 
  array (
    'surah_number' => 98,
    'name_ar' => 'البينة',
    'name_id' => 'Al-Bayyinah',
    'verses_count' => 8,
    'start_page' => 598,
    'end_page' => 599,
  ),
  98 => 
  array (
    'surah_number' => 99,
    'name_ar' => 'الزلزلة',
    'name_id' => 'Az-Zalzalah',
    'verses_count' => 8,
    'start_page' => 599,
    'end_page' => 599,
  ),
  99 => 
  array (
    'surah_number' => 100,
    'name_ar' => 'العاديات',
    'name_id' => 'Al-Adiyat',
    'verses_count' => 11,
    'start_page' => 599,
    'end_page' => 600,
  ),
  100 => 
  array (
    'surah_number' => 101,
    'name_ar' => 'القارعة',
    'name_id' => 'Al-Qariah',
    'verses_count' => 11,
    'start_page' => 600,
    'end_page' => 600,
  ),
  101 => 
  array (
    'surah_number' => 102,
    'name_ar' => 'التكاثر',
    'name_id' => 'At-Takatsur',
    'verses_count' => 8,
    'start_page' => 600,
    'end_page' => 600,
  ),
  102 => 
  array (
    'surah_number' => 103,
    'name_ar' => 'العصر',
    'name_id' => 'Al-Asr',
    'verses_count' => 3,
    'start_page' => 601,
    'end_page' => 601,
  ),
  103 => 
  array (
    'surah_number' => 104,
    'name_ar' => 'الهمزة',
    'name_id' => 'Al-Humazah',
    'verses_count' => 9,
    'start_page' => 601,
    'end_page' => 601,
  ),
  104 => 
  array (
    'surah_number' => 105,
    'name_ar' => 'الفيل',
    'name_id' => 'Al-Fil',
    'verses_count' => 5,
    'start_page' => 601,
    'end_page' => 601,
  ),
  105 => 
  array (
    'surah_number' => 106,
    'name_ar' => 'قريش',
    'name_id' => 'Quraisy',
    'verses_count' => 4,
    'start_page' => 602,
    'end_page' => 602,
  ),
  106 => 
  array (
    'surah_number' => 107,
    'name_ar' => 'الماعون',
    'name_id' => 'Al-Maun',
    'verses_count' => 7,
    'start_page' => 602,
    'end_page' => 602,
  ),
  107 => 
  array (
    'surah_number' => 108,
    'name_ar' => 'الكوثر',
    'name_id' => 'Al-Kautsar',
    'verses_count' => 3,
    'start_page' => 602,
    'end_page' => 602,
  ),
  108 => 
  array (
    'surah_number' => 109,
    'name_ar' => 'الكافرون',
    'name_id' => 'Al-Kafirun',
    'verses_count' => 6,
    'start_page' => 603,
    'end_page' => 603,
  ),
  109 => 
  array (
    'surah_number' => 110,
    'name_ar' => 'النصر',
    'name_id' => 'An-Nasr',
    'verses_count' => 3,
    'start_page' => 603,
    'end_page' => 603,
  ),
  110 => 
  array (
    'surah_number' => 111,
    'name_ar' => 'المسد',
    'name_id' => 'Al-Lahab',
    'verses_count' => 5,
    'start_page' => 603,
    'end_page' => 603,
  ),
  111 => 
  array (
    'surah_number' => 112,
    'name_ar' => 'الإخلاص',
    'name_id' => 'Al-Ikhlas',
    'verses_count' => 4,
    'start_page' => 604,
    'end_page' => 604,
  ),
  112 => 
  array (
    'surah_number' => 113,
    'name_ar' => 'الفلق',
    'name_id' => 'Al-Falaq',
    'verses_count' => 5,
    'start_page' => 604,
    'end_page' => 604,
  ),
  113 => 
  array (
    'surah_number' => 114,
    'name_ar' => 'الناس',
    'name_id' => 'An-Nas',
    'verses_count' => 6,
    'start_page' => 604,
    'end_page' => 604,
  ),
);

    $stmt = $db->prepare("
        UPDATE `mushaf_surahs` 
        SET `name_ar` = ?, `name_id` = ?, `verses_count` = ?, `start_page` = ?, `end_page` = ?
        WHERE `surah_number` = ?
    ");

    $updated = 0;
    foreach ($surahData as $s) {
        $stmt->execute([
            $s['name_ar'],
            $s['name_id'],
            $s['verses_count'],
            $s['start_page'],
            $s['end_page'],
            $s['surah_number']
        ]);
        $updated++;
    }

    echo "      -> Berhasil memperbarui {$updated} nama surah dalam bahasa Arab.

";

    echo "[3/3] Verifikasi sampel surah:
";
    $samples = Database::fetchAll("SELECT surah_number, name_ar, name_id FROM mushaf_surahs WHERE surah_number IN (1, 2, 36, 112, 114) ORDER BY surah_number ASC");
    foreach ($samples as $samp) {
        echo "      Surah {$samp['surah_number']}. {$samp['name_id']} => {$samp['name_ar']}
";
    }

    echo "
=======================================================
";
    echo "  SELESAI! Teks Arab pada VPS sudah normal kembali.    
";
    echo "=======================================================
";

} catch (\Throwable $e) {
    echo "
[ERROR] Gagal memperbaiki nama surah: " . $e->getMessage() . "
";
    exit(1);
}