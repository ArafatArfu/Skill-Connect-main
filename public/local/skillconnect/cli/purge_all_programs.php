<?php
define('CLI_SCRIPT', true);
require __DIR__ . '/../../../config.php';
require_once(__DIR__ . '/../locallib.php');

global $DB;

$programs = ['clc', 'road_safety', 'volunteer'];
$total = 0;

foreach ($programs as $program) {
    $count = $DB->count_records('local_sc_program_participants', ['program' => $program]);
    if ($count > 0) {
        $DB->delete_records('local_sc_program_participants', ['program' => $program]);
        echo "Deleted {$count} records from program: {$program}\n";
        $total += $count;
    } else {
        echo "No records found for program: {$program}\n";
    }
}

echo "\nTotal deleted: {$total} records\n";
echo "All test/demo data has been removed.\n";
