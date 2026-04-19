<?php
// functions.php - Utility functions for Cyber-Pulse

/**
 * Renders media intelligently (Image or Video)
 */
function render_media($url, $class = "w-full h-full object-cover", $props = "") {
    if (!$url) return "<div class='w-full h-full bg-zinc-900 flex items-center justify-center text-[10px] text-zinc-700 font-mono uppercase'>No Media</div>";
    
    $video_extensions = ['mp4', 'webm', 'ogg', 'mov'];
    $path = parse_url($url, PHP_URL_PATH);
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    
    // Check for base64 video or common extensions
    $is_video = in_array(strtolower($ext), $video_extensions) || strpos($url, 'data:video/') === 0;

    if ($is_video) {
        return "<video src='$url' class='$class' autoplay loop muted playsinline $props></video>";
    }
    
    return "<img src='$url' class='$class' referrerpolicy='no-referrer' loading='lazy' $props>";
}

/**
 * AI Integration via cURL (Gemini or DeepSeek)
 */
function generate_project_pitch($api_key, $target_url, $agent = 'gemini', $title_context = "Determine from content") {
    // Gemini Free Tier Integration: Use system environment variable if key is missing
    if ($agent === 'gemini' && empty($api_key)) {
        $api_key = getenv('GEMINI_API_KEY');
    }

    if ($agent === 'deepseek') {
        $endpoint = "https://api.deepseek.com/chat/completions";
        $model = "deepseek-chat";
    } else {
        $model = "gemini-3-flash-preview";
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";
    }

    $prompt = "
    Role: Senior Creative Director & Software Architect
    Target URL: $target_url
    Title Context: $title_context
    
    Objective: Craft an elite project analysis and pitch. 
    Tone: 100% Human, Sophisticated, Opinionated.
    
    Writing Style Requirements:
    - Eliminate all AI linguistic fingerprints: NEVER use 'Delve into', 'In today's digital age', 'Moreover', 'Robust', or 'Unleash'.
    - Use 'Burstiness': Vary sentence structure and length creatively. 
    - Use 'Perplexity': Incorporate specific technical jargon and industry-standard nuances naturally.
    - Focus on 'The Soul': Describe the emotional impact and the clever engineering solutions (the 'elegant hacks').
    - Professional Wit: Sound like an expert who has seen it all and is genuinely impressed by specific details.
    
    Return ONLY a JSON object:
    {
      \"content\": \"Markdown allowed. 2-3 focused paragraphs. Avoid lists. Flow naturally from technical architecture to user experience. Sound like a lead engineer explaining a project to a stakeholder over coffee.\",
      \"metaTitle\": \"Click-worthy, non-generic title (35-55 chars)\",
      \"metaDescription\": \"Natural, engaging description with zero fluff (130-155 chars)\",
      \"keywords\": [\"unique-tech-tag\", \"industry-context\", \"design-style\", \"specific-feature\", \"high-level-category\"],
      \"techStack\": [{\"name\": \"SpecificLibrary\"}, {\"name\": \"CustomFramework\"}, {\"name\": \"CoreTech\"}],
      \"waMessage\": \"Short, punchy, human inquiry. No excessive emojis. Sound like a colleague.\"
    }
    ";

    if ($agent === 'deepseek') {
        $data = [
            "model" => $model,
            "messages" => [
                ["role" => "system", "content" => "You are a professional software architect. Your output must be indistinguishable from human writing."],
                ["role" => "user", "content" => $prompt]
            ],
            "response_format" => ["type" => "json_object"]
        ];
        $headers = [
            'Content-Type: application/json',
            "Authorization: Bearer $api_key"
        ];
    } else {
        $data = [
            "contents" => [
                ["parts" => [["text" => $prompt]]]
            ],
            "generationConfig" => [
                "responseMimeType" => "application/json"
            ]
        ];
        $headers = ['Content-Type: application/json'];
    }

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    if (curl_errno($ch)) return ['error' => 'Curl Error: ' . curl_error($ch)];
    curl_close($ch);

    $result = json_decode($response, true);
    
    if ($agent === 'deepseek') {
        if (isset($result['choices'][0]['message']['content'])) {
            return json_decode($result['choices'][0]['message']['content'], true);
        }
    } else {
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            $clean_json = trim($result['candidates'][0]['content']['parts'][0]['text']);
            $clean_json = preg_replace('/^```json|```$/m', '', $clean_json);
            return json_decode($clean_json, true);
        }
    }
    
    return ['error' => 'Model did not return valid JSON', 'raw' => $response, 'agent' => $agent];
}

/**
 * Google PageSpeed Insights Intelligence
 */
function fetch_pagespeed_vitals($api_key, $url) {
    if (!$api_key) return null;
    
    $endpoint = "https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=" . urlencode($url) . "&key=" . $api_key . "&category=performance";
    
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // PageSpeed takes time
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    if (!isset($data['lighthouseResult'])) return null;
    
    $score = ($data['lighthouseResult']['categories']['performance']['score'] ?? 0.9) * 100;
    $screenshot = $data['lighthouseResult']['audits']['final-screenshot']['details']['data'] ?? null;
    
    return [
        'speed' => round($score),
        'screenshot' => $screenshot
    ];
}

/**
 * Screenshot Intelligence Fallback (Multi-Source Resilience)
 */
function fetch_screenshot_fallback($url) {
    $encoded_url = urlencode($url);
    
    // Attempt 1: WordPress mshots (Reliable, high-speed)
    $mshots_url = "https://s.wordpress.com/mshots/v1/{$encoded_url}?w=1280&h=800";
    $res = try_fetch_base64_image($mshots_url, 15000); // Increased min size to ensure it's not a placeholder
    if ($res) return $res;

    // Attempt 2: Microlink Protocol (Sophisticated browser-based)
    $microlink_url = "https://api.microlink.io/?url={$encoded_url}&screenshot=true&embed=screenshot.url&waitFor=8000&viewport.width=1280&viewport.height=800";
    $res = try_fetch_base64_image($microlink_url, 10000);
    if ($res) return $res;

    // Attempt 3: FlashLearner (Alternative)
    $flash_url = "https://api.screenshotmachine.com/?key=FREE&url={$encoded_url}&dimension=1024x768"; // Requires key usually, but placeholder logic here
    $res = try_fetch_base64_image($flash_url, 5000);
    if ($res) return $res;
    
    // Attempt 4: Site-Shoot (Last resort public API)
    $site_shoot = "https://www.screenshotlayer.com/php_helper_fallback?url={$encoded_url}"; 
    $res = try_fetch_base64_image($site_shoot, 5000);
    if ($res) return $res;

    // Attempt 5: Google Thumbnail Service
    $google_thumb = "https://www.google.com/s2/favicons?domain={$url}&sz=128";
    $res = try_fetch_base64_image($google_thumb, 100); // Very small but better than nothing
    if ($res) return $res;
    
    return null;
}

/**
 * Helper to fetch and convert binary image to Base64
 */
function try_fetch_base64_image($url, $min_size = 0) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) CyberPulse/1.0');
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    $binary = curl_exec($ch);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200 && $binary && strpos($content_type, 'image') !== false && strlen($binary) >= $min_size) {
        return 'data:' . $content_type . ';base64,' . base64_encode($binary);
    }
    return null;
}

/**
 * AI Usage Intelligence
 */
function increment_ai_usage($pdo, $agent) {
    $key = ($agent === 'deepseek') ? 'deepseek_scans' : 'gemini_scans';
    $stmt = $pdo->prepare("UPDATE settings SET setting_value = CAST(setting_value AS UNSIGNED) + 1 WHERE setting_key = ?");
    $stmt->execute([$key]);
}

/**
 * DeepSeek Balance Protocol
 */
function check_deepseek_balance($api_key) {
    if (!$api_key) return null;
    $ch = curl_init("https://api.deepseek.com/user/balance");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $api_key"]);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

/**
 * Email Dispatch Protocol (Respects SMTP Identity)
 */
function send_email($to, $subject, $message, $s) {
    $from_name = $s['smtp_from_name'] ?? 'CyberPulse Admin';
    $from_email = $s['smtp_from_email'] ?? 'noreply@cyberpulse.local';
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: $from_name <$from_email>\r\n";
    $headers .= "Reply-To: $from_email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // Integration Note: In high-security production environments, 
    // replacing this with PHPMailer and the configured SMTP host/port/pass is recommended.
    return @mail($to, $subject, $message, $headers);
}

/**
 * Universal Input Sanitizer
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Slugifier for clean URLs
 */
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}
?>
