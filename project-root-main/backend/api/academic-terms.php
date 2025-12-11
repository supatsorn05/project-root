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
include_once '../load_env.php';

$requestMethod = $_SERVER["REQUEST_METHOD"];

switch ($requestMethod) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Get single academic term
            $termId = $_GET['id'];
            $sql = "SELECT * FROM academic_terms WHERE term_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$termId]);
            $term = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($term) {
                echo json_encode($term);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Academic term not found."]);
            }
        } elseif (isset($_GET['check_usage']) && isset($_GET['term_id'])) {
            // Check if academic term is used in projects
            $termId = $_GET['term_id'];
            $sql = "SELECT COUNT(*) as count FROM projects WHERE term_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$termId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode(["inUse" => $row['count'] > 0]);
        } else {
            // Get all academic terms
            $sql = "SELECT at.term_id, at.academic_year, at.term_name, COUNT(p.project_id) as project_count
                    FROM academic_terms at
                    LEFT JOIN projects p ON at.term_id = p.term_id
                    GROUP BY at.term_id, at.academic_year, at.term_name
                    ORDER BY 
                        at.academic_year DESC,
                        CASE at.term_name
                            WHEN 'เทอมต้น' THEN 1
                            WHEN 'เทอมปลาย' THEN 2
                            WHEN 'ภาคฤดูร้อน' THEN 3
                            ELSE 4
                        END";
            $stmt = $pdo->query($sql);
            $academicTerms = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($academicTerms);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->academic_year) || !isset($data->term_name)) {
            http_response_code(400);
            echo json_encode(["message" => "Missing academic_year or term_name."]);
            exit();
        }

        $academicYear = $data->academic_year;
        $termName = $data->term_name;

        // Validate academic_year format
        if (!preg_match("/^[0-9]{4}$/", $academicYear)) {
            http_response_code(400);
            echo json_encode(["message" => "Academic year must be a 4-digit number."]);
            exit();
        }

        // Validate term_name
        $allowedTermNames = ['เทอมต้น', 'เทอมปลาย', 'ภาคฤดูร้อน'];
        if (!in_array($termName, $allowedTermNames)) {
            http_response_code(400);
            echo json_encode(["message" => "Invalid term name."]);
            exit();
        }

        // Check for uniqueness
        $sql = "SELECT COUNT(*) as count FROM academic_terms WHERE academic_year = ? AND term_name = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$academicYear, $termName]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row['count'] > 0) {
            http_response_code(409); // Conflict
            echo json_encode(["message" => "Academic term already exists."]);
            exit();
        }

        $sql = "INSERT INTO academic_terms (academic_year, term_name) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$academicYear, $termName])) {
            $newId = $pdo->lastInsertId();
            http_response_code(201);
            echo json_encode(["message" => "Academic term created.", "term_id" => $newId, "academic_year" => $academicYear, "term_name" => $termName]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to create academic term."]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($_GET['id']) || !isset($data->academic_year) || !isset($data->term_name)) {
            http_response_code(400);
            echo json_encode(["message" => "Missing ID, academic_year or term_name."]);
            exit();
        }

        $termId = $_GET['id'];
        $academicYear = $data->academic_year;
        $termName = $data->term_name;

        // Validate academic_year format
        if (!preg_match("/^[0-9]{4}$/", $academicYear)) {
            http_response_code(400);
            echo json_encode(["message" => "Academic year must be a 4-digit number."]);
            exit();
        }

        // Validate term_name
        $allowedTermNames = ['เทอมต้น', 'เทอมปลาย', 'ภาคฤดูร้อน'];
        if (!in_array($termName, $allowedTermNames)) {
            http_response_code(400);
            echo json_encode(["message" => "Invalid term name."]);
            exit();
        }

        // Check for uniqueness (excluding current term)
        $sql = "SELECT COUNT(*) as count FROM academic_terms WHERE academic_year = ? AND term_name = ? AND term_id != ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$academicYear, $termName, $termId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row['count'] > 0) {
            http_response_code(409); // Conflict
            echo json_encode(["message" => "Academic term with this year and name already exists."]);
            exit();
        }

        $sql = "UPDATE academic_terms SET academic_year = ?, term_name = ? WHERE term_id = ?";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$academicYear, $termName, $termId])) {
            if ($stmt->rowCount() > 0) {
                echo json_encode(["message" => "Academic term updated."]);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Academic term not found or no changes made."]);
            }
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to update academic term."]);
        }
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["message" => "Missing ID."]);
            exit();
        }

        $termId = $_GET['id'];

        // Check for usage in projects table
        $sql = "SELECT COUNT(*) as count FROM projects WHERE term_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$termId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row['count'] > 0) {
            http_response_code(409); // Conflict
            echo json_encode(["message" => "Cannot delete academic term: it is used in existing projects."]);
            exit();
        }

        $sql = "DELETE FROM academic_terms WHERE term_id = ?";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$termId])) {
            if ($stmt->rowCount() > 0) {
                echo json_encode(["message" => "Academic term deleted."]);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Academic term not found."]);
            }
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to delete academic term."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed."]);
        break;
}

// PDO connection is automatically closed when the script finishes
?>