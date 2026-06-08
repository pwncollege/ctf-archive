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
    // Delete archives first
    $stmt = $pdo->prepare("DELETE FROM archives WHERE client_id = ?");
    $stmt->execute([$data['client_id']]);
    
    // Delete client
    $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
    $stmt->execute([$data['client_id']]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
