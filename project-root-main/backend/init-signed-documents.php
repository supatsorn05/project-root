<?php
require_once __DIR__ . '/db_connect.php';

$pdo->exec("DROP TABLE IF EXISTS signed_documents;");

$pdo->exec("CREATE TABLE IF NOT EXISTS signed_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    form_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    user_id INT,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    feedback TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);");

echo "signed_documents table initialized successfully.";
