<?php
// Get the section key from the query parameter
$section = isset($_GET['section']) ? $_GET['section'] : null;

// Define the file path for each section
$templateFiles = [
    'semi_structured' => '../templates/semi_structured_template.docx',
    'data_analysis' => '../templates/data_analysis_template.docx',
    'themes' => '../templates/themes_template.docx',
];

// Validate the section and check if the file exists
if ($section && isset($templateFiles[$section])) {
    $filePath = $templateFiles[$section];

    if (file_exists($filePath)) {
        // Set headers for file download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Pragma: public');

        // Read and output the file
        readfile($filePath);
        exit;
    } else {
        echo "Error: File not found.";
    }
} else {
    echo "Error: Invalid section.";
}
?>
