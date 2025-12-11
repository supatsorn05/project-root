erDiagram
    users {
        INT id PK
        VARCHAR email UK "อีเมลสำหรับเข้าสู่ระบบ"
        VARCHAR username UK "ชื่อผู้ใช้งาน"
        VARCHAR password "รหัสผ่าน"
        VARCHAR full_name "ชื่อ-นามสกุล"
        ENUM role "บทบาท"
        VARCHAR session_token UK "โทเค็นเซสชัน"
        VARCHAR profile_image_url "URL รูปโปรไฟล์"
    }

    advisors {
        INT advisor_id PK
        INT user_id FK "อ้างอิง users.id"
        VARCHAR department "ภาควิชา"
    }

    academic_terms {
        INT term_id PK
        INT academic_year "ปีการศึกษา"
        VARCHAR term_name "ชื่อภาคการศึกษา"
    }

    projects {
        INT project_id PK
        VARCHAR project_name "ชื่อโครงงาน (TH)"
        VARCHAR project_name_en "ชื่อโครงงาน (EN)"
        INT main_advisor_id FK "อาจารย์ที่ปรึกษาหลัก"
        INT secondary_advisor_id FK "อาจารย์ที่ปรึกษารอง"
        INT term_id FK "ภาคการศึกษา"
        TEXT abstract "บทคัดย่อ"
    }

    students {
        VARCHAR student_id PK "รหัสนิสิต"
        INT user_id FK  "อ้างอิง users.id"
        VARCHAR group_name "ชื่อกลุ่มโครงงาน"
    }

    project_members {
        INT project_id PK,FK "อ้างอิง projects.id"
        VARCHAR student_id PK,FK "อ้างอิง students(student_id)"
    }

    document_templates {
        INT template_id PK
        VARCHAR template_name "ชื่อเทมเพลต"
        VARCHAR pdf_url "URL PDF"
        VARCHAR docx_url "URL DOCX"
    }

    submission_document_types {
        INT doc_type_id PK
        VARCHAR doc_code UK "รหัสเอกสาร"
        VARCHAR doc_name "ชื่อเอกสาร"
    }

    submitted_documents {
        INT submission_id PK
        INT project_id FK "อ้างอิง projects(project_id)"
        INT doc_type_id FK "ประเภทเอกสาร"
        VARCHAR file_path "ตำแหน่งไฟล์"
        DATETIME uploaded_at "วันที่อัปโหลด"
        ENUM status "สถานะ"
        TEXT comment "ความคิดเห็น"
        INT uploader_user_id FK "ผู้ส่ง (อ้างอิง users(id))"
    }

    student_documents {
        INT student_document_id PK
        VARCHAR student_id FK "อ้างอิง students(student_id)"
        INT teacher_user_id FK "อาจารย์ผู้รับ (อ้างอิง users(id))"
        INT doc_type_id FK "ประเภทเอกสาร"
        VARCHAR file_path "ไฟล์ที่นิสิตอัปโหลด"
        DATETIME uploaded_at "วันที่อัปโหลด"
        ENUM status "สถานะ"
        TEXT comment "ความคิดเห็น"
        VARCHAR teacher_file_path "ไฟล์ที่อาจารย์อัปโหลดกลับ"
    }

    users ||--o{ advisors : "has"
    users ||--o{ students : "has"
    users ||--o{ submitted_documents : "uploaded_by"
    users ||--o{ student_documents : "reviewed_by"

    advisors ||--o{ projects : "main_advisor"
    advisors ||--o{ projects : "secondary_advisor"

    academic_terms ||--o{ projects : "has"

    projects ||--o{ project_members : "has"
    projects ||--o{ submitted_documents : "has"

    students ||--o{ project_members : "is_member_of"
    students ||--o{ student_documents : "submits"

    submission_document_types ||--o{ submitted_documents : "is_of_type"
    submission_document_types ||--o{ student_documents : "is_of_type"

