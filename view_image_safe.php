<?php
// Safe image viewer using base64 encoding with debug mode
error_reporting(E_ALL);
ini_set('display_errors', 1);

include './config/connection.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$debug = isset($_GET['debug']);

if ($id <= 0) { 
    if ($debug) {
        echo "<h3>Error: Invalid file ID</h3>";
    } else {
        echo "Invalid file ID";
    }
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
        if ($debug) {
            echo "<h3>Error: File not found in database</h3>";
            echo "<p>File ID: $id</p>";
        } else {
            echo "File not found in database";
        }
        exit; 
    }

    $file = __DIR__ . '/uploads/patient_files/' . $row['stored_name'];
    
    if ($debug) {
        echo "<h3>Debug Information:</h3>";
        echo "<p><strong>File ID:</strong> $id</p>";
        echo "<p><strong>Stored Name:</strong> " . $row['stored_name'] . "</p>";
        echo "<p><strong>Original Name:</strong> " . $row['original_name'] . "</p>";
        echo "<p><strong>Stored MIME:</strong> " . $row['mime_type'] . "</p>";
        echo "<p><strong>Full Path:</strong> $file</p>";
        echo "<p><strong>File Exists:</strong> " . (file_exists($file) ? 'YES' : 'NO') . "</p>";
        echo "<p><strong>File Readable:</strong> " . (is_readable($file) ? 'YES' : 'NO') . "</p>";
        
        if (file_exists($file)) {
            echo "<p><strong>File Size:</strong> " . number_format(filesize($file)) . " bytes</p>";
            $detectedMime = mime_content_type($file);
            echo "<p><strong>Detected MIME:</strong> $detectedMime</p>";
        }
    }
    
    if (!file_exists($file)) { 
        if ($debug) {
            echo "<p style='color: red;'><strong>Error:</strong> File not found on disk: $file</p>";
        } else {
            echo "File not found on disk";
        }
        exit; 
    }
    
    if (!is_readable($file)) {
        if ($debug) {
            echo "<p style='color: red;'><strong>Error:</strong> File not readable</p>";
        } else {
            echo "File not readable";
        }
        exit;
    }

    $mime = $row['mime_type'] ?: 'application/octet-stream';
    
    // Detect actual MIME type
    $detectedMime = mime_content_type($file);
    if ($detectedMime && $detectedMime !== 'application/octet-stream') {
        $mime = $detectedMime;
    }
    
    if ($debug) {
        echo "<p><strong>Final MIME Type:</strong> $mime</p>";
    }
    
    // Check if it's an image
    if (strpos($mime, 'image/') !== 0) {
        if ($debug) {
            echo "<p style='color: red;'><strong>Error:</strong> File is not an image (MIME: $mime)</p>";
        } else {
            echo "File is not an image";
        }
        exit;
    }
    
    // Read file content
    $fileContent = file_get_contents($file);
    if ($fileContent === false) {
        if ($debug) {
            echo "<p style='color: red;'><strong>Error:</strong> Could not read file content</p>";
        } else {
            echo "Could not read file content";
        }
        exit;
    }
    
    if ($debug) {
        echo "<p><strong>File Content Size:</strong> " . strlen($fileContent) . " bytes</p>";
        echo "<p><strong>Base64 Size:</strong> " . strlen(base64_encode($fileContent)) . " characters</p>";
        echo "<h3>Image Preview:</h3>";
        echo "<img src='data:" . $mime . ";base64," . base64_encode($fileContent) . "' style='max-width: 300px; border: 1px solid #ccc;'>";
        exit;
    }
    
    // Output the image directly with proper headers
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . strlen($fileContent));
    header('Cache-Control: public, max-age=3600');
    header('Content-Disposition: inline; filename="' . $row['original_name'] . '"');
    
    // Clear any previous output
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    echo $fileContent;
    
} catch (Exception $ex) {
    if ($debug) {
        echo "<p style='color: red;'><strong>Exception:</strong> " . $ex->getMessage() . "</p>";
        echo "<p><strong>Stack Trace:</strong></p><pre>" . $ex->getTraceAsString() . "</pre>";
    } else {
        echo "Error: " . $ex->getMessage();
    }
}
?>
