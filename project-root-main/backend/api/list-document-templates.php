<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../db_connect.php';

header('Content-Type: application/json; charset=utf-8');

$upload_dir_base = __DIR__ . '/../uploads/documents'; // Base directory for actual files

try {
    $stmt = $pdo->query("SELECT template_id, template_name, pdf_url, docx_url FROM document_templates ORDER BY template_id ASC");
    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $forms = [];
    $reportTemplates = [];

    foreach ($templates as $template) {
        $is_form = strpos($template['template_name'], 'CS') === 0;

        // Construct full URLs and check availability
        $full_pdf_url = $template['pdf_url'] ? '/uploads/documents/' . $template['pdf_url'] : null;
        $full_docx_url = $template['docx_url'] ? '/uploads/documents/' . $template['docx_url'] : null;

        $pdf_available = $template['pdf_url'] ? file_exists($upload_dir_base . '/' . $template['pdf_url']) : false;
        $docx_available = $template['docx_url'] ? file_exists($upload_dir_base . '/' . $template['docx_url']) : false;

        if ($is_form) {
            $forms[] = [
                'id' => $template['template_id'],
                'name' => $template['template_name'],
                'path' => $full_pdf_url, // Frontend expects 'path' for forms
                'available' => $pdf_available,
            ];
        } else {
            $reportTemplates[] = [
                'id' => $template['template_id'],
                'name' => $template['template_name'],
                'pdfPath' => $full_pdf_url,
                'docxPath' => $full_docx_url,
                'pdf_available' => $pdf_available,
                'docx_available' => $docx_available,
            ];
        }
    }

    $response_data = [
        'forms' => $forms,
        'reportTemplates' => $reportTemplates,
    ];

    echo json_encode(array('status' => 'success', 'data' => $response_data));

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
}
