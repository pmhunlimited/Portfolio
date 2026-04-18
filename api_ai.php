<?php
// api_ai.php - Backend endpoint for AI generation
require_once 'db.php';
require_once 'functions.php';

session_start();
if (!isset($_SESSION['authorized'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'UNAUTHORIZED']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$url = $data['url'] ?? '';
$title = $data['title'] ?? 'Determine from content';

if (!$url) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'URL_REQUIRED']);
    exit;
}

// Fetch Gemini Key
$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'gemini_api_key'");
$stmt->execute();
$api_key = $stmt->fetchColumn();

if (!$api_key) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'API_KEY_MISSING']);
    exit;
}

$pitch = generate_project_pitch($api_key, $url, $title);

header('Content-Type: application/json');
echo json_encode($pitch ?: ['error' => 'GENERATION_FAILED']);
