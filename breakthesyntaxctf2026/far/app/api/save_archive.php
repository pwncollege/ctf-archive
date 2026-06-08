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
    if (!empty($data['archive_id'])) {
        // Update existing archive
        $stmt = $pdo->prepare("UPDATE archives SET size = ? WHERE id = ?");
        $stmt->execute([$data['size'], $data['archive_id']]);
    } else {
        // Insert new archive
        $stmt = $pdo->prepare("INSERT INTO archives (client_id, size, creation_date) VALUES (?, ?, date('now'))");
        $stmt->execute([$data['client_id'], $data['size']]);
    }
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
