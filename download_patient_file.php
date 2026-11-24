<?php
include './config/connection.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { 
    http_response_code(404); 
    echo 'Invalid file ID';
    exit; 
}

try {
    // First try patient_files table
    $stmt = $con->prepare("SELECT patient_id, stored_name, original_name, mime_type FROM patient_files WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If not found in patient_files, try prescription_files
    if (!$row) {
        $stmt = $con->prepare("SELECT patient_id, stored_name, original_name, mime_type FROM prescription_files WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    if (!$row) { 
        http_response_code(404); 
        echo 'File not found in database';
        exit; 
    }

    $file = __DIR__ . '/uploads/patient_files/' . $row['stored_name'];
    
    // Debug mode - add ?debug=1 to see debug info
    if (isset($_GET['debug'])) {
        echo "<h3>Debug Information:</h3>";
        echo "<p><strong>File ID:</strong> $id</p>";
        echo "<p><strong>Stored Name:</strong> " . $row['stored_name'] . "</p>";
        echo "<p><strong>Original Name:</strong> " . $row['original_name'] . "</p>";
        echo "<p><strong>MIME Type:</strong> " . $row['mime_type'] . "</p>";
        echo "<p><strong>Full Path:</strong> $file</p>";
        echo "<p><strong>File Exists:</strong> " . (file_exists($file) ? 'YES' : 'NO') . "</p>";
        echo "<p><strong>File Readable:</strong> " . (is_readable($file) ? 'YES' : 'NO') . "</p>";
        
        if (file_exists($file)) {
            echo "<p><strong>File Size:</strong> " . number_format(filesize($file)) . " bytes</p>";
        }
        exit;
    }
    
    if (!file_exists($file)) { 
        http_response_code(404); 
        echo 'File not found on disk: ' . $file;
        exit; 
    }
    
    if (!is_readable($file)) {
        http_response_code(403);
        echo 'File not readable';
        exit;
    }

    $mime = $row['mime_type'] ?: 'application/octet-stream';
    
    // Detect actual MIME type if stored one is wrong
    $detectedMime = mime_content_type($file);
    if ($detectedMime && $detectedMime !== 'application/octet-stream') {
        $mime = $detectedMime;
    }
    
    // Set appropriate headers for different file types
    if (strpos($mime, 'image/') === 0) {
        // For images, use inline display
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . basename($row['original_name']) . '"');
        header('Cache-Control: public, max-age=3600'); // Cache for 1 hour
    } else {
        // For other files, use attachment
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . basename($row['original_name']) . '"');
    }
    
    header('Content-Length: ' . filesize($file));
    header('Accept-Ranges: bytes');
    header('X-Sendfile-Type: X-Accel-Redirect');
    
    // Clear any output buffer
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    readfile($file);
    exit;
    
} catch (Exception $ex) {
    http_response_code(500);
    echo 'Error: ' . $ex->getMessage();
}
?>


