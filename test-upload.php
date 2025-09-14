<?php
require_once 'includes/cloud-storage.php';

echo "<h2>Cloud Storage Upload Test</h2>";

// Test access token retrieval
echo "<h3>1. Testing Access Token Retrieval:</h3>";
$cloudHelper = new CloudStorageHelper();
$token = $cloudHelper->getAccessToken();
if ($token) {
    echo "✅ Access token retrieved successfully (length: " . strlen($token) . ")<br>";
} else {
    echo "❌ Failed to retrieve access token<br>";
}

echo "<h3>2. Testing Database Creation and Upload:</h3>";

try {
    // Create a simple test database
    $testDbPath = '/tmp/test_upload.db';
    $pdo = new PDO("sqlite:$testDbPath");
    $pdo->exec("CREATE TABLE IF NOT EXISTS test (id INTEGER PRIMARY KEY, name TEXT)");
    $pdo->exec("INSERT INTO test (name) VALUES ('test_upload')");
    $pdo = null; // Close connection
    
    echo "✅ Test database created at: $testDbPath<br>";
    echo "📁 File size: " . filesize($testDbPath) . " bytes<br>";
    
    // Try to upload it
    $cloudHelper = new CloudStorageHelper();
    $success = $cloudHelper->uploadDatabase($testDbPath, 'test_upload.db');
    
    if ($success) {
        echo "✅ Database uploaded successfully to cloud storage<br>";
    } else {
        echo "❌ Database upload failed<br>";
    }
    
    // Clean up
    if (file_exists($testDbPath)) {
        unlink($testDbPath);
        echo "🧹 Test database cleaned up<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Exception during test: " . $e->getMessage() . "<br>";
}

echo "<h3>3. Checking Error Logs:</h3>";
$errorLog = error_get_last();
if ($errorLog) {
    echo "<pre>";
    print_r($errorLog);
    echo "</pre>";
} else {
    echo "No PHP errors logged<br>";
}

echo "<p><a href='diagnose.php'>← Back to Diagnose</a></p>";
?>