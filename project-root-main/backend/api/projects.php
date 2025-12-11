<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

header("Content-Type: application/json; charset=UTF-8");

include_once '../db_connect.php';
include_once '../load_env.php'; // Although not directly used for DB creds, it might be for other env vars

$requestMethod = $_SERVER["REQUEST_METHOD"];

switch ($requestMethod) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Get single project
            $projectId = $_GET['id'];
            $sql = "SELECT * FROM projects WHERE project_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$projectId]);
            $project = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($project) {
                echo json_encode($project);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Project not found."]);
            }
        } else {
            // Get all projects
            $sql = "SELECT p.*, 
                           u1.full_name as main_advisor_name, 
                           u2.full_name as secondary_advisor_name,
                           at.academic_year, 
                           at.term_name
                    FROM projects p
                    LEFT JOIN advisors a1 ON p.main_advisor_id = a1.advisor_id
                    LEFT JOIN users u1 ON a1.user_id = u1.id
                    LEFT JOIN advisors a2 ON p.secondary_advisor_id = a2.advisor_id
                    LEFT JOIN users u2 ON a2.user_id = u2.id
                    LEFT JOIN academic_terms at ON p.term_id = at.term_id
                    ORDER BY p.project_id DESC";
            $stmt = $pdo->query($sql);
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($projects);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->project_name) || !isset($data->project_name_en) || !isset($data->main_advisor_id) || !isset($data->term_id) ) {
            http_response_code(400);
            echo json_encode(["message" => "Missing required fields."]);
            exit();
        }

        $projectName = $data->project_name;
        $projectNameEn = $data->project_name_en;
        $mainAdvisorId = $data->main_advisor_id;
        $secondaryAdvisorId = $data->secondary_advisor_id ?? null; // Optional
        $termId = $data->term_id;
        $abstract = "";

        if ($secondaryAdvisorId !== null && $mainAdvisorId == $secondaryAdvisorId) {
            http_response_code(400);
            echo json_encode(["message" => "อาจารย์ที่ปรึกษาหลักและรองต้องไม่เป็นคนเดียวกัน"]);
            exit();
        }

        // Check for duplicate project name
        $stmt = $pdo->prepare("SELECT project_id FROM projects WHERE project_name = ?");
        $stmt->execute([$projectName]);
        if ($stmt->fetch()) {
            http_response_code(409); // Conflict
            echo json_encode(["message" => "มีโครงงานชื่อนี้อยู่แล้ว"]);
            exit();
        }

        $sql = "INSERT INTO projects (project_name, project_name_en, main_advisor_id, secondary_advisor_id, term_id, abstract) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$projectName, $projectNameEn, $mainAdvisorId, $secondaryAdvisorId, $termId, $abstract])) {
            $newId = $pdo->lastInsertId();
            http_response_code(201);
            echo json_encode(["message" => "Project created.", "project_id" => $newId, "project_name" => $projectName, "project_name_en" => $projectNameEn, "main_advisor_id" => $mainAdvisorId, "secondary_advisor_id" => $secondaryAdvisorId, "term_id" => $termId, "abstract" => $abstract]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to create project."]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($_GET['id']) || !isset($data->project_name) || !isset($data->project_name_en) || !isset($data->main_advisor_id) || !isset($data->term_id)) {
            http_response_code(400);
            echo json_encode(["message" => "Missing ID or required fields."]);
            exit();
        }

        $projectId = $_GET['id'];
        $projectName = $data->project_name;
        $projectNameEn = $data->project_name_en;
        $mainAdvisorId = $data->main_advisor_id;
        $secondaryAdvisorId = $data->secondary_advisor_id ?? null;
        $termId = $data->term_id;
        $abstract = $data->abstract ?? "";

        if ($secondaryAdvisorId !== null && $mainAdvisorId == $secondaryAdvisorId) {
            http_response_code(400);
            echo json_encode(["message" => "อาจารย์ที่ปรึกษาหลักและรองต้องไม่เป็นคนเดียวกัน"]);
            exit();
        }

        $sql = "UPDATE projects SET project_name = ?, project_name_en = ?, main_advisor_id = ?, secondary_advisor_id = ?, term_id = ?, abstract = ? WHERE project_id = ?";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$projectName, $projectNameEn, $mainAdvisorId, $secondaryAdvisorId, $termId, $abstract, $projectId])) {
            if ($stmt->rowCount() > 0) {
                echo json_encode(["message" => "Project updated."]);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Project not found or no changes made."]);
            }
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to update project."]);
        }
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["message" => "Missing ID."]);
            exit();
        }

        $projectId = $_GET['id'];

        // TODO: Add check for related tables (e.g., project_members, submitted_documents) before deleting

        $sql = "DELETE FROM projects WHERE project_id = ?";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$projectId])) {
            if ($stmt->rowCount() > 0) {
                echo json_encode(["message" => "Project deleted."]);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Project not found."]);
            }
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to delete project."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed."]);
        break;
}

// PDO connection is automatically closed when the script finishes
?>