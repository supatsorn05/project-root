<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../db_connect.php';

// Auth Check (Admin only)
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (preg_match('/Bearer\s(.+)/', $authHeader, $matches)) {
    $token_hash = hash('sha256', $matches[1]);
    $stmt = $pdo->prepare("SELECT role FROM users WHERE session_token = ?");
    $stmt->execute([$token_hash]);
    $session_user = $stmt->fetch();
    if (!$session_user || $session_user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
} else {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Token required']);
    exit;
}

// Handle PUT request
if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $data = json_decode(file_get_contents("php://input"));

    if (!$id || !$data) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Fields to update in users table
        $full_name = trim($data->full_name ?? '');
        $email = trim($data->email ?? '');
        $username = trim($data->username ?? '');
        $role = $data->role ?? '';
        $password = $data->password ?? null;
        $student_id = trim($data->student_id ?? ''); // Get student_id from payload
        $group_name_raw = $data->group_name ?? '';
        $group_name = trim((string)$group_name_raw);


        // Validation
        if (empty($full_name) || empty($email) || empty($username) || empty($role)) {
            throw new Exception('Required fields cannot be empty.', 400);
        }
        if (!preg_match('/^[\p{L}\p{M}\s\'\-,.]+$/u', $full_name)) {
            throw new Exception('ชื่อ-นามสกุลไม่สามารถมีอักขระพิเศษที่ไม่ได้รับอนุญาตได้', 400);
        }
        if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
            throw new Exception('ชื่อผู้ใช้งานต้องเป็นภาษาอังกฤษและตัวเลขเท่านั้น', 400);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('รูปแบบอีเมลไม่ถูกต้อง', 400);
        }
        if (!in_array($role, ['admin', 'teacher', 'student'])) {
            throw new Exception('Invalid role.', 400);
        }

        // Check for duplicate email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) {
            throw new Exception('อีเมลนี้มีผู้ใช้งานแล้ว', 409);
        }

        // Check for duplicate username
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $id]);
        if ($stmt->fetch()) {
            throw new Exception('ชื่อผู้ใช้งานนี้มีผู้ใช้งานแล้ว', 409);
        }

        // Handle student_id logic
        if ($role === 'student') {
            if (empty($student_id)) {
                throw new Exception('รหัสนิสิตจำเป็นสำหรับบทบาทนิสิต', 400);
            }
            if (!preg_match('/^[0-9]{10}$/', $student_id)) {
                throw new Exception('รหัสนิสิตต้องเป็นตัวเลข 10 หลักเท่านั้น', 400);
            }
           if (!empty($group_name) && !preg_match('/^[\p{Thai}a-zA-Z0-9\s\-\_\.\(\)&]+$/u', $group_name)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ชื่อกลุ่มสามารถใช้ภาษาไทย, อังกฤษ, ตัวเลข และอักขระพิเศษบางตัว (- _ . ( ) &) ได้เท่านั้น']);
    exit;
}

            // Check for duplicate student_id (excluding current user's student_id if it exists)
            $stmt = $pdo->prepare("SELECT user_id FROM students WHERE student_id = ? AND user_id != ?");
            $stmt->execute([$student_id, $id]);
            if ($stmt->fetch()) {
                throw new Exception('รหัสนิสิตนี้มีผู้ใช้งานแล้ว', 409);
            }

            // Check if student record already exists for this user
            $stmt = $pdo->prepare("SELECT student_id FROM students WHERE user_id = ?");
            $stmt->execute([$id]);
            if ($stmt->fetch()) {
                // Update existing student record
                $stmt = $pdo->prepare("UPDATE students SET student_id = ?, group_name = ? WHERE user_id = ?");
                $stmt->execute([$student_id, $group_name, $id]);
            } else {
                // Insert new student record
                $stmt = $pdo->prepare("INSERT INTO students (student_id, user_id, group_name) VALUES (?, ?, ?)");
                $stmt->execute([$student_id, $id, $group_name]);
            }
        } else {
            // If role is not student, delete any existing student record for this user
            $stmt = $pdo->prepare("DELETE FROM students WHERE user_id = ?");
            $stmt->execute([$id]);
        }

        // Build the query for users table dynamically
        $fields_to_update = [
            'full_name' => $full_name,
            'email' => $email,
            'username' => $username,
            'role' => $role
        ];

        if (isset($data->password)) {
            if (strlen($data->password) > 0) { // Ensure password is not an empty string
                if (strlen($data->password) < 6 || strlen($data->password) > 10) {
                    throw new Exception('รหัสผ่านต้องมีความยาว 6-10 ตัวอักษร', 400);
                }
                $fields_to_update['password'] = password_hash($data->password, PASSWORD_DEFAULT);
            }
        }

        $set_clause = [];
        foreach ($fields_to_update as $key => $value) {
            $set_clause[] = "$key = :$key";
        }
        $sql = "UPDATE users SET " . implode(", ", $set_clause) . " WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $fields_to_update['id'] = $id;
        $stmt->execute($fields_to_update);

        $pdo->commit();

        echo json_encode(['status' => 'success', 'message' => 'User updated successfully.']);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $code = $e->getCode() >= 400 ? $e->getCode() : 500;
        http_response_code($code);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        error_log("Update user PDO error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
}
?>
