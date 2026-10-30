<?php
// database/migrate_role_guru_tk.php
require_once __DIR__ . '/../core/Database.php';

use Core\Database;

Database::connect();
echo "=== MIGRASI ROLE GURU TK/KB ===\n";

$exists = Database::fetch("SELECT id FROM roles WHERE name = 'guru_tk'");
if (!$exists) {
    Database::execute(
        "INSERT INTO roles (name, display_name, description) VALUES (?, ?, ?)",
        ['guru_tk', 'Guru TK/KB', 'Guru Satuan Pendidikan TK dan KB Al-Kahfi']
    );
    echo "[OK] Role guru_tk (Guru TK/KB) berhasil ditambahkan ke tabel roles.\n";
} else {
    Database::execute("UPDATE roles SET display_name = 'Guru TK/KB' WHERE name = 'guru_tk'");
    echo "[SKIP] Role guru_tk sudah ada, display_name dipastikan 'Guru TK/KB'.\n";
}

$all = Database::fetchAll("SELECT id, name, display_name FROM roles WHERE name IN ('guru_tk', 'guru_mapel_kbtk')");
print_r($all);

