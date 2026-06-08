<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../db.php';

$data = json_decode(file_get_contents('php://input'), true);

try {
    if (!empty($data['client_id'])) {
        // Update existing client
        $stmt = $pdo->prepare("UPDATE clients SET company_name = ?, cost_per_archive_gb = ? WHERE id = ?");
        $stmt->execute([$data['company_name'], $data['cost_per_archive_gb'], $data['client_id']]);
    } else {
        // Insert new client
        $stmt = $pdo->prepare("INSERT INTO clients (company_name, cost_per_archive_gb, registration_date) VALUES (?, ?, date('now'))");
        $stmt->execute([$data['company_name'], $data['cost_per_archive_gb']]);
    }
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
