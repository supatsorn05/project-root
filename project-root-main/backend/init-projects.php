<?php
require_once __DIR__ . '/db_connect.php';

$pdo->exec("DROP TABLE IF EXISTS projects;");

$pdo->exec("CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    advisor_id INT,
    FOREIGN KEY (advisor_id) REFERENCES users(id)
);");

$projects = [
    ['Project Alpha', 'This is the first project.', 2],
    ['Project Beta', 'This is the second project.', 2],
    ['Project Gamma', 'This is the third project.', 2],
];

$stmt = $pdo->prepare("INSERT INTO projects (name, description, advisor_id) VALUES (?, ?, ?)");

foreach ($projects as $project) {
    $stmt->execute($project);
}

echo "Projects table initialized successfully.";
