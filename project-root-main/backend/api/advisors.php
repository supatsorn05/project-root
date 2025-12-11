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

$requestMethod = $_SERVER["REQUEST_METHOD"];

switch ($requestMethod) {
    case 'GET':
        try {
            if (isset($_GET['id'])) {
                $advisorId = $_GET['id'];
                $sql = "SELECT a.*, u.full_name, u.email FROM advisors a JOIN users u ON a.user_id = u.id WHERE a.advisor_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$advisorId]);
                $advisor = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($advisor) {
                    echo json_encode($advisor);
                } else {
                    http_response_code(404);
                    echo json_encode(["message" => "Advisor not found."]);
                }
            } else {
                $sql = "SELECT a.*, u.full_name, u.email FROM advisors a JOIN users u ON a.user_id = u.id ORDER BY u.full_name ASC";
                $stmt = $pdo->query($sql);
                $advisors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($advisors);
            }
        } catch (Throwable $e) {
            http_response_code(500);
            error_log("Error in advisors.php (GET): " . $e->getMessage());
            echo json_encode(["message" => "Server error while fetching advisors."]);
        }
        break;

    case 'POST':
        try {
            $data = json_decode(file_get_contents("php://input"));

            if (!isset($data->user_id) || !isset($data->department)) {
                http_response_code(400);
                echo json_encode(["message" => "Missing user_id or department."]);
                exit();
            }

            $userId = $data->user_id;
            $department = $data->department;

            $sql_check = "SELECT COUNT(*) as count FROM advisors WHERE user_id = ?";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([$userId]);
            $row = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if ($row['count'] > 0) {
                http_response_code(409);
                echo json_encode(["message" => "This user is already an advisor."]);
                exit();
            }

            $sql_insert = "INSERT INTO advisors (user_id, department) VALUES (?, ?)";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->execute([$userId, $department]);
            
            $newId = $pdo->lastInsertId();
            http_response_code(201);
            echo json_encode(["message" => "Advisor created.", "advisor_id" => $newId, "user_id" => $userId, "department" => $department]);

        } catch (Throwable $e) {
            http_response_code(500);
            error_log("Error in advisors.php (POST): " . $e->getMessage() . " on line " . $e->getLine());
            echo json_encode(["message" => "Server error: " . $e->getMessage()]);
        }
        break;

    case 'PUT':
        try {
            $data = json_decode(file_get_contents("php://input"));

            if (!isset($_GET['id']) || !isset($data->department)) {
                http_response_code(400);
                echo json_encode(["message" => "Missing ID or department."]);
                exit();
            }

            $advisorId = $_GET['id'];
            $department = $data->department;

            $sql = "UPDATE advisors SET department = ? WHERE advisor_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$department, $advisorId]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(["message" => "Advisor updated."]);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Advisor not found or no changes made."]);
            }
        } catch (Throwable $e) {
            http_response_code(500);
            error_log("Error in advisors.php (PUT): " . $e->getMessage());
            echo json_encode(["message" => "Server error while updating advisor."]);
        }
        break;

    case 'DELETE':
        try {
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode(["message" => "Missing ID."]);
                exit();
            }

            $advisorId = $_GET['id'];

            // Corrected check for usage in projects table
            $sql_check = "SELECT COUNT(*) as count FROM projects WHERE main_advisor_id = ? OR secondary_advisor_id = ?";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([$advisorId, $advisorId]);
            $row = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if ($row['count'] > 0) {
                http_response_code(409);
                echo json_encode(["message" => "Cannot delete advisor: this advisor is assigned to existing projects."]);
                exit();
            }

            $sql_delete = "DELETE FROM advisors WHERE advisor_id = ?";
            $stmt_delete = $pdo->prepare($sql_delete);
            $stmt_delete->execute([$advisorId]);

            if ($stmt_delete->rowCount() > 0) {
                echo json_encode(["message" => "Advisor deleted."]);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Advisor not found."]);
            }
        } catch (Throwable $e) {
            http_response_code(500);
            error_log("Error in advisors.php (DELETE): " . $e->getMessage());
            echo json_encode(["message" => "Server error while deleting advisor."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed."]);
        break;
}
?>