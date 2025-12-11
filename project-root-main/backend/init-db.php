<?php
header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/db_connect.php';

try {
    // Drop all tables in correct order to avoid foreign key constraints
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("DROP TABLE IF EXISTS student_documents;");
    $pdo->exec("DROP TABLE IF EXISTS submitted_documents;");
    $pdo->exec("DROP TABLE IF EXISTS submission_document_types;");
    $pdo->exec("DROP TABLE IF EXISTS document_templates;");
    $pdo->exec("DROP TABLE IF EXISTS project_members;");
    $pdo->exec("DROP TABLE IF EXISTS students;");
    $pdo->exec("DROP TABLE IF EXISTS projects;");
    $pdo->exec("DROP TABLE IF EXISTS academic_terms;");
    $pdo->exec("DROP TABLE IF EXISTS advisors;");
    $pdo->exec("DROP TABLE IF EXISTS users;");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "- Dropped all existing tables.";

    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users ( id INT AUTO_INCREMENT PRIMARY KEY, email VARCHAR(255) NOT NULL UNIQUE, username VARCHAR(255) NULL UNIQUE, password VARCHAR(255) NOT NULL, full_name VARCHAR(255) NOT NULL, role ENUM('admin', 'teacher', 'student') NOT NULL, session_token VARCHAR(255) NULL UNIQUE, profile_image_url VARCHAR(255) NULL ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

    // Create Advisors table
    $pdo->exec("CREATE TABLE IF NOT EXISTS advisors ( advisor_id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL UNIQUE, department VARCHAR(255) NULL, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

    // Create AcademicTerms table
    $pdo->exec("CREATE TABLE IF NOT EXISTS academic_terms ( term_id INT AUTO_INCREMENT PRIMARY KEY, academic_year INT NOT NULL, term_name VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL, UNIQUE(academic_year, term_name) ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

    // Create Projects table
    $pdo->exec("CREATE TABLE IF NOT EXISTS projects ( project_id INT AUTO_INCREMENT PRIMARY KEY, project_name VARCHAR(255) NOT NULL, project_name_en VARCHAR(255) NULL, main_advisor_id INT NOT NULL, secondary_advisor_id INT NULL, term_id INT NOT NULL, abstract TEXT NULL, FOREIGN KEY (main_advisor_id) REFERENCES advisors(advisor_id) ON DELETE CASCADE, FOREIGN KEY (secondary_advisor_id) REFERENCES advisors(advisor_id) ON DELETE SET NULL, FOREIGN KEY (term_id) REFERENCES academic_terms(term_id) ON DELETE CASCADE ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

    // Create Students table
    $pdo->exec("CREATE TABLE IF NOT EXISTS students ( student_id VARCHAR(20) PRIMARY KEY, user_id INT NOT NULL UNIQUE, group_name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

    // Create ProjectMembers table
    $pdo->exec("CREATE TABLE IF NOT EXISTS project_members ( project_id INT NOT NULL, student_id VARCHAR(20) NOT NULL, PRIMARY KEY (project_id, student_id), FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE, FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

    // Create DocumentTemplates table
    $pdo->exec("CREATE TABLE IF NOT EXISTS document_templates ( template_id INT AUTO_INCREMENT PRIMARY KEY, template_name VARCHAR(255) NOT NULL, pdf_url VARCHAR(255) NULL, docx_url VARCHAR(255) NULL ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

    // Create SubmissionDocumentTypes table
    $pdo->exec("CREATE TABLE IF NOT EXISTS submission_document_types ( doc_type_id INT AUTO_INCREMENT PRIMARY KEY, doc_code VARCHAR(50) NOT NULL UNIQUE, doc_name VARCHAR(255) NOT NULL ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

    // Create SubmittedDocuments table
    $pdo->exec("CREATE TABLE IF NOT EXISTS submitted_documents ( submission_id INT AUTO_INCREMENT PRIMARY KEY, project_id INT NOT NULL, doc_type_id INT NOT NULL, file_path VARCHAR(255) NOT NULL, uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP, status ENUM('submitted', 'approved', 'rejected') NOT NULL DEFAULT 'submitted', comment TEXT NULL, uploader_user_id INT NULL, FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE, FOREIGN KEY (doc_type_id) REFERENCES submission_document_types(doc_type_id) ON DELETE CASCADE, FOREIGN KEY (uploader_user_id) REFERENCES users(id) ON DELETE CASCADE ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

    // Create StudentDocuments table (for submissions without a project)
    $pdo->exec("CREATE TABLE `student_documents` ( `student_document_id` INT AUTO_INCREMENT PRIMARY KEY, `student_id` VARCHAR(20) NOT NULL, `teacher_user_id` INT NOT NULL, `doc_type_id` INT NOT NULL, `file_path` VARCHAR(255) NOT NULL, `uploaded_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, `status` ENUM('submitted', 'approved', 'rejected') NOT NULL DEFAULT 'submitted', `comment` TEXT NULL, `teacher_file_path` VARCHAR(255) NULL, CONSTRAINT `fk_stdoc_student` FOREIGN KEY (`student_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE, CONSTRAINT `fk_stdoc_teacher` FOREIGN KEY (`teacher_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE, CONSTRAINT `fk_stdoc_doctype` FOREIGN KEY (`doc_type_id`) REFERENCES `submission_document_types`(`doc_type_id`) ON DELETE CASCADE ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "- Created all tables.";

    // --- Insert Mock Data ---
    echo "- Inserting mock data...";
    // Users
    $users_data = [
        ['admin@test.com', 'admin', password_hash('adminpass', PASSWORD_DEFAULT), 'Admin User', 'admin'],
        ['teacher@test.com', 'teacher', password_hash('teacherpass', PASSWORD_DEFAULT), 'Teacher User', 'teacher'],
        ['student@test.com', 'student', password_hash('studentpass', PASSWORD_DEFAULT), 'Student User', 'student'],
    ];
    $stmt_users = $pdo->prepare("INSERT INTO users (email, username, password, full_name, role) VALUES (?, ?, ?, ?, ?)");
    foreach ($users_data as $user) { $stmt_users->execute($user); }

    $admin_user_id = $pdo->query("SELECT id FROM users WHERE username = 'admin'")->fetchColumn();
    $teacher_user_id = $pdo->query("SELECT id FROM users WHERE username = 'teacher'")->fetchColumn();
    $student_user_id = $pdo->query("SELECT id FROM users WHERE username = 'student'")->fetchColumn();

    // Advisors
    $advisors_data = [ [$teacher_user_id, 'Computer Science'], ];
    $stmt_advisors = $pdo->prepare("INSERT INTO advisors (user_id, department) VALUES (?, ?)");
    foreach ($advisors_data as $advisor) { $stmt_advisors->execute($advisor); }
    $teacher_advisor_id = $pdo->query("SELECT advisor_id FROM advisors WHERE user_id = $teacher_user_id")->fetchColumn();

    // AcademicTerms
    $terms_data = [ [2568, 'เทอมต้น'], [2568, 'เทอมปลาย'], [2568, 'ภาคฤดูร้อน'], [2567, 'เทอมต้น'], [2567, 'เทอมปลาย'], [2567, 'ภาคฤดูร้อน'], ];
    $stmt_terms = $pdo->prepare("INSERT INTO academic_terms (academic_year, term_name) VALUES (?, ?)");
    foreach ($terms_data as $term) { $stmt_terms->execute($term); }

    // Projects
    $projects_data = [
        ['ระบบจัดการโครงงานสหกิจ', 'Cooperative Education Project Management System', $teacher_advisor_id, null, $pdo->query("SELECT term_id FROM academic_terms WHERE academic_year = 2568 AND term_name = 'เทอมต้น'")->fetchColumn(), 'บทคัดย่อระบบจัดการโครงงานสหกิจ'],
        ['แอปพลิเคชันติดตามผลการเรียน', 'Academic Progress Tracking Application', $teacher_advisor_id, null, $pdo->query("SELECT term_id FROM academic_terms WHERE academic_year = 2567 AND term_name = 'เทอมปลาย'")->fetchColumn(), 'บทคัดย่อแอปพลิเคชันติดตามผลการเรียน'],
    ];
    $stmt_projects = $pdo->prepare("INSERT INTO projects (project_name, project_name_en, main_advisor_id, secondary_advisor_id, term_id, abstract) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($projects_data as $project) { $stmt_projects->execute($project); }

    // Students
    $students_data = [ ['64000001', $student_user_id, 'Group A'], ];
    $stmt_students = $pdo->prepare("INSERT INTO students (student_id, user_id, group_name) VALUES (?, ?, ?)");
    foreach ($students_data as $student) { $stmt_students->execute($student); }

    // ProjectMembers
    $project_id_1 = $pdo->query("SELECT project_id FROM projects WHERE project_name = 'ระบบจัดการโครงงานสหกิจ'")->fetchColumn();
    $student_id_1 = '64000001';
    $stmt_members = $pdo->prepare("INSERT INTO project_members (project_id, student_id) VALUES (?, ?)");
    $stmt_members->execute([$project_id_1, $student_id_1]);

        // DocumentTemplates
    $templates_data = [
        // Forms
        ['CS-01', 'CS-01.pdf', null],
        ['CS-02', 'CS-02.pdf', null],
        ['CS-03', 'CS-03.pdf', null],
        ['CS-04', 'CS-04.pdf', null],
        ['CS-05', 'CS-05.pdf', null],
        // Report Templates
        ['หน้าปก', 'template-cover.pdf', 'template-cover.docx'],
        ['ใบรับรอง', 'template-certificate.pdf', 'template-certificate.docx'],
        ['บทคัดย่อ', 'template-abstract.pdf', 'template-abstract.docx'],
        ['บทที่ 1', 'template-ch1.pdf', 'template-ch1.docx'],
        ['บทที่ 2', 'template-ch2.pdf', 'template-ch2.docx'],
        ['บทที่ 3', 'template-ch3.pdf', 'template-ch3.docx'],
        ['บทที่ 4', 'template-ch4.pdf', 'template-ch4.docx'],
        ['บทที่ 5', 'template-ch5.pdf', 'template-ch5.docx'],
        ['บรรณานุกรม', 'template-bibliography.pdf', 'template-bibliography.docx'],
    ];
    $stmt_templates = $pdo->prepare("INSERT INTO document_templates (template_name, pdf_url, docx_url) VALUES (?, ?, ?)");
    foreach ($templates_data as $template) {
        $stmt_templates->execute($template);
    }

    // SubmissionDocumentTypes
    $doc_types_data = [ 
        ['CS01', 'แบบฟอร์มขอกำหนดหัวข้อและแต่งตั้งอาจารย์ที่ปรึกษา'], 
        ['CS02', 'แบบฟอร์มขอสอบกำหนดหัวข้อและร่ยงานความก้าวหน้า'], 
        ['CS03', 'แบบฟอร์มขอสอบประมวลความรูขั้นสุดท้าย'],
        ['CS04', 'แบบฟอร์ม'],
        ['CS05', 'แบบฟอร์ม'],
        ['รายงาน', 'เล่มรายงานโครงงาน'], 
    ];
    $stmt_doc_types = $pdo->prepare("INSERT INTO submission_document_types (doc_code, doc_name) VALUES (?, ?)");
    foreach ($doc_types_data as $doc_type) { $stmt_doc_types->execute($doc_type); }

    // SubmittedDocuments (example)
        $doc_type_id_report = $pdo->query("SELECT doc_type_id FROM submission_document_types WHERE doc_code = 'รายงาน'")->fetchColumn();
    $submitted_docs_data = [ [$project_id_1, $doc_type_id_report, 'uploads/project_documents/project1_report.pdf', $admin_user_id], ]; // Use admin_user_id
    $stmt_submitted_docs = $pdo->prepare("INSERT INTO submitted_documents (project_id, doc_type_id, file_path, uploader_user_id) VALUES (?, ?, ?, ?)"); // Change column name
    foreach ($submitted_docs_data as $doc) { $stmt_submitted_docs->execute($doc); }
    echo "- Mock data inserted successfully.";

    echo "\nDatabase initialized successfully!";

} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}

?>