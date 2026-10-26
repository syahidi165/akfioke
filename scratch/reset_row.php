<?php
require 'core/Database.php';
$db = Core\Database::connect();
$db->query("UPDATE academic_year_approvals SET status = 'Menunggu', rejection_reason = NULL, decided_by_user_id = NULL WHERE id = 17");
echo "Row 17 reset to Menunggu successfully!\n";
