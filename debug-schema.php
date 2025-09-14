<?php
require_once 'includes/cloud-storage.php';

// Simple auth check
if (!isset($_GET['debug']) || $_GET['debug'] !== 'schema') {
    die('Access denied');
}

try {
    echo "<h2>Database Schema Debug</h2>";
    
    $studentManager = new StudentManager();
    $db = $studentManager->db;
    
    echo "<h3>Students Table Schema:</h3>";
    $stmt = $db->query("PRAGMA table_info(students)");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1'>";
    echo "<tr><th>Column</th><th>Type</th><th>Not Null</th><th>Default</th><th>Primary Key</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['name']) . "</td>";
        echo "<td>" . htmlspecialchars($column['type']) . "</td>";
        echo "<td>" . ($column['notnull'] ? 'YES' : 'NO') . "</td>";
        echo "<td>" . htmlspecialchars($column['dflt_value']) . "</td>";
        echo "<td>" . ($column['pk'] ? 'YES' : 'NO') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>Results Table Schema:</h3>";
    $stmt = $db->query("PRAGMA table_info(results)");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1'>";
    echo "<tr><th>Column</th><th>Type</th><th>Not Null</th><th>Default</th><th>Primary Key</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['name']) . "</td>";
        echo "<td>" . htmlspecialchars($column['type']) . "</td>";
        echo "<td>" . ($column['notnull'] ? 'YES' : 'NO') . "</td>";
        echo "<td>" . htmlspecialchars($column['dflt_value']) . "</td>";
        echo "<td>" . ($column['pk'] ? 'YES' : 'NO') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>Sample Data:</h3>";
    $students = $studentManager->getAllStudents();
    echo "<p>Total students: " . count($students) . "</p>";
    
    if (count($students) > 0) {
        echo "<h4>Students:</h4>";
        foreach ($students as $student) {
            echo "<pre>" . print_r($student, true) . "</pre>";
        }
    }
    
} catch (Exception $e) {
    echo "<h3>Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>