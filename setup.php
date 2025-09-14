<?php
// Setup script to ensure bucket structure exists
require_once 'includes/cloud-storage.php';

try {
    echo "Setting up Google Cloud Storage...\n";
    
    // Create bucket folder structure
    $commands = [
        'gsutil mb -p $(gcloud config get-value project) gs://resultexyx 2>/dev/null || echo "Bucket exists"',
        'echo "test" | gsutil cp - gs://resultexyx/UUD/.gitkeep',
    ];
    
    foreach ($commands as $command) {
        echo "Running: $command\n";
        $output = shell_exec($command);
        echo "Output: $output\n";
    }
    
    echo "Setup completed!\n";
    
} catch (Exception $e) {
    echo "Setup failed: " . $e->getMessage() . "\n";
}
?>