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

// Fetch AI Config
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('gemini_api_key', 'deepseek_api_key', 'default_ai_agent')");
$settings = [];
while($row = $stmt->fetch()) $settings[$row['setting_key']] = $row['setting_value'];

$agent = $settings['default_ai_agent'] ?? 'gemini';
$api_key = ($agent === 'deepseek') ? ($settings['deepseek_api_key'] ?? '') : ($settings['gemini_api_key'] ?? '');

if (!$api_key) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'API_KEY_MISSING_FOR_' . strtoupper($agent)]);
    exit;
}

$pitch = generate_project_pitch($api_key, $url, $agent, $title);

header('Content-Type: application/json');
echo json_encode($pitch ?: ['error' => 'GENERATION_FAILED']);
