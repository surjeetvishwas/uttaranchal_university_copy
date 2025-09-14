<?php
header('Content-Type: text/plain');
require_once __DIR__ . '/includes/cloud-storage.php';

echo "Smoke Test\n";
try {
    $mgr = new StudentManager();
    echo "- StudentManager init: OK\n";

    $roll = 'T' . date('YmdHis');
    $name = 'Test User ' . substr($roll, -6);
    $saved = $mgr->saveStudent($roll, $name);
    echo "- saveStudent($roll, $name): " . ($saved ? 'OK' : 'FAIL') . "\n";

    $fetched = $mgr->getStudent($roll);
    echo "- getStudent: " . ($fetched ? ($fetched['roll_number'] . ' / ' . ($fetched['student_name'] ?? $fetched['name'] ?? '')) : 'NOT FOUND') . "\n";

    echo "- List students count: " . count($mgr->getAllStudents()) . "\n";

    echo "DONE\n";
} catch (Throwable $e) {
    // Avoid header warnings; just print error line
    echo "ERROR: " . $e->getMessage() . "\n";
}
