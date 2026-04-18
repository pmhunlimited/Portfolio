<?php
// functions.php - Utility functions for Cyber-Pulse

/**
 * Renders media intelligently (Image or Video)
 */
function render_media($url, $class = "w-full h-full object-cover", $props = "") {
    if (!$url) return "<div class='w-full h-full bg-zinc-900 flex items-center justify-center text-[10px] text-zinc-700 font-mono uppercase'>No_Media</div>";
    
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
 * Gemini AI Integration via cURL
 * Replicates geminiService.ts advanced features
 */
function generate_project_pitch($api_key, $target_url, $title_context = "Determine from content") {
    $model = "gemini-1.5-flash"; // Responsive and capable
    $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";

    $prompt = "
    Perform a deep-scan and analysis of the provided URL to extract its core value proposition, technical architecture, and visual identity.
    Target URL: $target_url
    Title Context: $title_context
    
    Your goal is to generate a 'Power Pitch' that is 100% inline with exactly what the website offers. 
    Return ONLY a JSON object with:
    {
      \"content\": \"Markdown string with 2-3 clear paragraphs for maximum readability\",
      \"metaTitle\": \"SEO title (max 60 chars)\",
      \"metaDescription\": \"SEO desc (max 160 chars)\",
      \"keywords\": [\"tag1\", \"tag2\", \"tag3\", \"tag4\", \"tag5\"],
      \"techStack\": [{\"name\": \"React\"}, {\"name\": \"Tailwind\"}, {\"name\": \"Firebase\"}],
      \"waMessage\": \"A professional WhatsApp inquiry message tailored to this specific service.\"
    }
    ";

    $data = [
        "contents" => [
            ["parts" => [["text" => $prompt]]]
        ],
        "generationConfig" => [
            "responseMimeType" => "application/json"
        ]
    ];

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    if (curl_errno($ch)) return null;
    curl_close($ch);

    $result = json_decode($response, true);
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $clean_json = trim($result['candidates'][0]['content']['parts'][0]['text']);
        // Strip markdown backticks if AI included them
        $clean_json = preg_replace('/^```json|```$/m', '', $clean_json);
        return json_decode($clean_json, true);
    }
    
    return ['error' => 'Model did not return valid JSON', 'raw' => $response];
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
