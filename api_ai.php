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
$vitals_only = $data['vitals_only'] ?? false;

if (!$url && !($data['get_stats'] ?? false)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'URL_REQUIRED']);
    exit;
}

// Fetch System Config
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while($row = $stmt->fetch()) $settings[$row['setting_key']] = $row['setting_value'];

// Statistics Protocol
if ($data['get_stats'] ?? false) {
    header('Content-Type: application/json');
    $stats = [
        'gemini_scans' => $settings['gemini_scans'] ?? 0,
        'deepseek_scans' => $settings['deepseek_scans'] ?? 0,
        'deepseek_balance' => check_deepseek_balance($settings['deepseek_api_key'] ?? '')
    ];
    echo json_encode($stats);
    exit;
}

$response = [];

// Protocol 1: PageSpeed Intelligence (Vitals + Screenshot)
if (!empty($settings['pagespeed_api_key'])) {
    $vitals = fetch_pagespeed_vitals($settings['pagespeed_api_key'], $url);
    if ($vitals) {
        $response['speed'] = $vitals['speed'];
        $response['screenshot'] = $vitals['screenshot'];
    }
}

// Fallback Protocol: If screenshot is still missing
if (empty($response['screenshot'])) {
    $response['screenshot'] = fetch_screenshot_fallback($url);
}

// Protocol 2: AI Synapse (If not vitals_only)
if (!$vitals_only) {
    $agent = $settings['default_ai_agent'] ?? 'gemini';
    $api_key = ($agent === 'deepseek') ? ($settings['deepseek_api_key'] ?? '') : ($settings['gemini_api_key'] ?? '');
    
    // AI Studio Environment Fallback for Gemini
    if ($agent === 'gemini' && empty($api_key)) {
        $api_key = getenv('GEMINI_API_KEY');
    }

    if ($api_key) {
        $ai_data = generate_project_pitch($api_key, $url, $agent, $title);
        if (isset($ai_data['error'])) {
            $response['ai_error'] = $ai_data['error'];
        } else {
            $response = array_merge($response, $ai_data);
            increment_ai_usage($pdo, $agent);
        }
    } else {
        $response['ai_error'] = "API_KEY_MISSING_FOR_" . strtoupper($agent);
    }
}

header('Content-Type: application/json');
echo json_encode($response ?: ['error' => 'OPERATION_FAILED']);
