<?php
require 'core/Database.php';
$db = Core\Database::connect();
echo "ACADEMIC YEARS:\n";
print_r($db->query("SELECT * FROM academic_years")->fetchAll(PDO::FETCH_ASSOC));

echo "ACADEMIC YEAR APPROVALS:\n";
print_r($db->query("SELECT * FROM academic_year_approvals")->fetchAll(PDO::FETCH_ASSOC));
