<?php
require_once 'db.php';
require_once 'functions.php';

session_start();

// Simple Login Check
if (isset($_POST['login'])) {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'admin_username'");
    $stmt->execute();
    $masterUser = $stmt->fetchColumn() ?: 'philmorehost@gmail.com';

    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'admin_password'");
    $stmt->execute();
    $masterPass = $stmt->fetchColumn() ?: 'password1234';
    
    if ($_POST['username'] === $masterUser && $_POST['password'] === $masterPass) {
        $_SESSION['authorized'] = true;
    } else {
        $error = "ACCESS_DENIED: Invalid Node Credentials";
    }
}

if (!isset($_SESSION['authorized'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Gatekeeper | Elite Security</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=JetBrains+Mono&display=swap" rel="stylesheet">
        <style>
            body { background: #050505; color: white; font-family: 'Inter', sans-serif; }
            .glow-orange { text-shadow: 0 0 20px rgba(255, 102, 0, 0.4); }
            .bg-glass { background: rgba(255,255,255,0.03); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.08); }
        </style>
    </head>
    <body class="min-h-screen flex items-center justify-center p-6 overflow-hidden relative">
        <!-- Background Accents -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10">
            <div class="absolute top-[20%] left-[20%] w-[400px] h-[400px] bg-orange-600/10 blur-[120px] rounded-full"></div>
            <div class="absolute bottom-[20%] right-[20%] w-[300px] h-[300px] bg-purple-600/10 blur-[100px] rounded-full"></div>
        </div>

        <div class="w-full max-w-md bg-glass p-12 rounded-[40px] space-y-10 shadow-2xl relative">
            <div class="text-center space-y-4">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-orange-600/10 border border-orange-600/20 mb-4 rotate-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-orange-600 -rotate-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                </div>
                <h1 class="text-3xl font-black italic tracking-tighter uppercase glow-orange">Node Access</h1>
                <p class="text-[10px] font-mono text-zinc-500 uppercase tracking-[0.3em]">Administrative Bypass Protocol 2.0</p>
            </div>

            <?php if(isset($error)): ?>
                <div class="bg-red-500/10 border border-red-500/20 text-red-500 text-[10px] font-mono p-3 rounded-lg text-center uppercase tracking-widest">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div class="space-y-4">
                    <div class="relative group">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-zinc-600 group-focus-within:text-orange-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        </div>
                        <input type="text" name="username" placeholder="identityId" required
                               class="w-full bg-black/40 border border-white/10 rounded-2xl py-5 pl-14 pr-4 outline-none focus:border-orange-600 transition-all font-mono text-xs tracking-widest">
                    </div>

                    <div class="relative group">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-zinc-600 group-focus-within:text-orange-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        </div>
                        <input type="password" name="password" placeholder="masterPassphrase" required
                               class="w-full bg-black/40 border border-white/10 rounded-2xl py-5 pl-14 pr-4 outline-none focus:border-orange-600 transition-all font-mono text-xs tracking-widest">
                    </div>
                </div>

                <button type="submit" name="login" 
                        class="w-full bg-orange-600 py-5 rounded-2xl text-black font-black uppercase italic tracking-[0.2em] text-sm hover:brightness-110 active:scale-95 transition-all shadow-[0_0_30px_rgba(234,88,12,0.3)]">
                    Initiate Sync
                </button>
            </form>

            <div class="pt-6 border-t border-white/5 text-center space-y-4">
                <p class="text-[9px] font-mono text-zinc-600 uppercase tracking-tighter italic">Sec_Protocol_v4.5.1 // Status: Encrypted</p>
                <div class="flex justify-center gap-1.5">
                    <div class="w-1.5 h-1.5 rounded-full bg-orange-600 animate-pulse"></div>
                    <div class="w-1.5 h-1.5 rounded-full bg-orange-600 animate-pulse delay-75"></div>
                    <div class="w-1.5 h-1.5 rounded-full bg-orange-600 animate-pulse delay-150"></div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Handle CRUD Operations
if (isset($_POST['update_settings'])) {
    $keys = [
        'appTitle', 'heroSubtext', 'gemini_api_key', 'deepseek_api_key', 'pagespeed_api_key', 
        'admin_username', 'admin_password', 'authorized_email', 'default_ai_agent',
        'gemini_scans', 'deepseek_scans'
    ];
    foreach ($keys as $key) {
        if (isset($_POST[$key])) {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $_POST[$key], $_POST[$key]]);
        }
    }
    header("Location: admin.php?settings_updated=1");
    exit;
}

if (isset($_POST['reset_usage'])) {
    $stmt = $pdo->prepare("UPDATE settings SET setting_value = '0' WHERE setting_key IN ('gemini_scans', 'deepseek_scans')");
    $stmt->execute();
    header("Location: admin.php?usage_reset=1");
    exit;
}

if (isset($_GET['toggle_pin'])) {
    $id = $_GET['toggle_pin'];
    $current = $pdo->prepare("SELECT is_pinned FROM projects WHERE id = ?");
    $current->execute([$id]);
    $is_pinned = $current->fetchColumn();
    
    $stmt = $pdo->prepare("UPDATE projects SET is_pinned = ? WHERE id = ?");
    $stmt->execute([!$is_pinned, $id]);
    header("Location: admin.php?pinned_updated=1");
    exit;
}

if (isset($_POST['save_project'])) {
    $title = $_POST['title'];
    $slug = slugify($title);
    $url = $_POST['url'];
    $content = $_POST['content'];
    $thumbnail = $_POST['thumbnail_url'];
    $type = $_POST['type'];

    // Multi-Tier Fields
    $lvl0_login_url = $_POST['lvl0_login_url'] ?? '';
    $lvl0_user = $_POST['lvl0_user'] ?? '';
    $lvl0_pass = $_POST['lvl0_pass'] ?? '';
    $lvl0_direct_url = $_POST['lvl0_direct_url'] ?? '';
    $lvl0_note = $_POST['lvl0_note'] ?? '';

    $lvl1_login_url = $_POST['lvl1_login_url'] ?? '';
    $lvl1_user = $_POST['lvl1_user'] ?? '';
    $lvl1_pass = $_POST['lvl1_pass'] ?? '';
    $lvl1_direct_url = $_POST['lvl1_direct_url'] ?? '';
    $lvl1_note = $_POST['lvl1_note'] ?? '';

    $lvl2_login_url = $_POST['lvl2_login_url'] ?? '';
    $lvl2_user = $_POST['lvl2_user'] ?? '';
    $lvl2_pass = $_POST['lvl2_pass'] ?? '';
    $lvl2_direct_url = $_POST['lvl2_direct_url'] ?? '';
    $lvl2_note = $_POST['lvl2_note'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO projects (title, slug, content, site_url, thumbnail_url, project_type, 
        lvl0_login_url, lvl0_user, lvl0_pass, lvl0_direct_url, lvl0_note,
        lvl1_login_url, lvl1_user, lvl1_pass, lvl1_direct_url, lvl1_note,
        lvl2_login_url, lvl2_user, lvl2_pass, lvl2_direct_url, lvl2_note,
        meta_title, wa_message, speed
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $title, $slug, $content, $url, $thumbnail, $type,
        $lvl0_login_url, $lvl0_user, $lvl0_pass, $lvl0_direct_url, $lvl0_note,
        $lvl1_login_url, $lvl1_user, $lvl1_pass, $lvl1_direct_url, $lvl1_note,
        $lvl2_login_url, $lvl2_user, $lvl2_pass, $lvl2_direct_url, $lvl2_note,
        $_POST['meta_title'] ?? '', $_POST['wa_message'] ?? '',
        $_POST['speed'] ?? 98
    ]);
    
    $project_id = $pdo->lastInsertId();

    // Handle Tech Stack
    if (!empty($_POST['tech_stack'])) {
        $stacks = explode(',', $_POST['tech_stack']);
        foreach ($stacks as $s) {
            $s = trim($s);
            if (!empty($s)) {
                $stmt = $pdo->prepare("INSERT INTO tech_stacks (project_id, name) VALUES (?, ?)");
                $stmt->execute([$project_id, $s]);
            }
        }
    }

    // Handle Keywords
    if (!empty($_POST['keywords'])) {
        $keys = explode(',', $_POST['keywords']);
        foreach ($keys as $k) {
            $k = trim($k);
            if (!empty($k)) {
                $stmt = $pdo->prepare("INSERT INTO keywords (project_id, keyword) VALUES (?, ?)");
                $stmt->execute([$project_id, $k]);
            }
        }
    }

    // Handle Gallery (Up to 5)
    if (isset($_POST['gallery_images']) && is_array($_POST['gallery_images'])) {
        foreach ($_POST['gallery_images'] as $order => $media_url) {
            if (!empty($media_url)) {
                $stmt = $pdo->prepare("INSERT INTO project_gallery (project_id, media_url, sort_order) VALUES (?, ?, ?)");
                $stmt->execute([$project_id, $media_url, $order]);
            }
        }
    }

    header("Location: admin.php?success=1");
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: admin.php?deleted=1");
    exit;
}

// Database Maintenance: Schema Synchronization Protocol
try {
    // Media handling consistency
    $pdo->exec("ALTER TABLE projects MODIFY thumbnail_url LONGTEXT");
    $pdo->exec("ALTER TABLE project_gallery MODIFY media_url LONGTEXT");
    
    // Missing Core Metadata & Pulse Metrics
    $columns_to_add = [
        "lvl0_login_url VARCHAR(500)", "lvl0_user VARCHAR(255)", "lvl0_pass VARCHAR(255)", "lvl0_direct_url VARCHAR(500)", "lvl0_note TEXT",
        "lvl1_login_url VARCHAR(500)", "lvl1_user VARCHAR(255)", "lvl1_pass VARCHAR(255)", "lvl1_direct_url VARCHAR(500)", "lvl1_note TEXT",
        "lvl2_login_url VARCHAR(500)", "lvl2_user VARCHAR(255)", "lvl2_pass VARCHAR(255)", "lvl2_direct_url VARCHAR(500)", "lvl2_note TEXT",
        "meta_title VARCHAR(255)", "wa_message TEXT", "speed INT DEFAULT 98", "is_pinned BOOLEAN DEFAULT FALSE"
    ];
    
    foreach ($columns_to_add as $col) {
        try {
            $pdo->exec("ALTER TABLE projects ADD COLUMN $col");
        } catch (Exception $e) { /* Column likely already exists, bypassing protocol */ }
    }
} catch(Exception $e) {}

$projects = $pdo->query("SELECT * FROM projects ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GRID NODE CONTROL | PHP</title>
    <script>
      (function() {
        // Advanced Extension Guard: Mocking Provider
        if (!window.ethereum) {
          window.ethereum = { 
            isMetaMask: true, 
            request: async () => ({}), 
            on: () => {}, 
            removeListener: () => {},
            isCommunity: true
          };
        }
        if (!window.web3) window.web3 = { currentProvider: window.ethereum };

        // Console Interceptor
        const originalError = console.error;
        const originalWarn = console.warn;
        const isSpam = (msg) => {
          if (!msg) return false;
          const s = String(msg).toLowerCase();
          return s.includes('metamask') || s.includes('ethereum') || s.includes('web3') || s.includes('provider') || s.includes('rpc');
        };

        console.error = function(...args) {
          if (args[0] && isSpam(args[0])) return;
          originalError.apply(console, args);
        };
        console.warn = function(...args) {
          if (args[0] && isSpam(args[0])) return;
          originalWarn.apply(console, args);
        };

        // Global Error Guard
        window.addEventListener('error', function(event) {
          if (event.message && isSpam(event.message)) {
            event.stopImmediatePropagation();
            event.preventDefault();
          }
        }, true);

        window.addEventListener('unhandledrejection', function(event) {
          if (event.reason && isSpam(event.reason)) {
            event.stopImmediatePropagation();
            event.preventDefault();
          }
        }, true);
      })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #000; color: white; font-family: 'Inter', sans-serif; }
        .glass { background: rgba(255,255,255,0.05); backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.1); }
    </style>
</head>
<body class="p-8">
    <div class="max-w-4xl mx-auto space-y-12">
        <header class="flex justify-between items-center">
            <h1 class="text-2xl font-black italic tracking-tighter text-orange-500 uppercase">System Administration</h1>
            <a href="index.php" class="text-xs uppercase font-bold text-zinc-500 hover:text-white">exitPortal</a>
        </header>

        <!-- Admin Tabs -->
        <div class="flex gap-4 mb-8 overflow-x-auto pb-2">
            <button onclick="switchTab('posting')" class="tab-btn px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] transition-all bg-orange-600 text-black shadow-lg" data-tab="posting">postingProjects</button>
            <button onclick="switchTab('system')" class="tab-btn px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] transition-all text-zinc-500 hover:text-white" data-tab="system">systemConfiguration</button>
            <button onclick="switchTab('api')" class="tab-btn px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] transition-all text-zinc-500 hover:text-white" data-tab="api">integrationVault</button>
        </div>

        <!-- Posting Tab -->
        <div id="tab-posting" class="space-y-12">
            <!-- Project Entry Form -->
            <div class="glass p-10 rounded-2xl space-y-8">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-black uppercase tracking-[0.4em] text-orange-500">Node Deployment</h2>
                    <div id="ai-loading" class="hidden flex items-center gap-2 text-[10px] text-orange-500 font-mono italic animate-pulse">
                        <svg class="animate-spin h-3 w-3" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span id="ai-status">Agent Decoding...</span>
                    </div>
                </div>

                <form method="POST" class="space-y-6" id="project-form">
                    <input type="hidden" name="speed" id="f-speed" value="98">
                    
                    <div class="space-y-4">
                        <label class="text-[9px] uppercase font-bold text-zinc-500">Deployment Protocol</label>
                        <div class="flex gap-4">
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="mode" value="auto" class="hidden peer" checked onchange="toggleDeploymentMode('auto')">
                                <div class="glass p-4 rounded-xl border border-white/5 peer-checked:border-orange-500 peer-checked:bg-orange-600/10 transition-all text-center">
                                    <div class="text-[10px] font-black uppercase text-zinc-400 peer-checked:text-orange-500">Automated Deploy</div>
                                    <div class="text-[8px] text-zinc-600 uppercase mt-1">AI-Powered Synthesis</div>
                                </div>
                            </label>
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="mode" value="manual" class="hidden peer" onchange="toggleDeploymentMode('manual')">
                                <div class="glass p-4 rounded-xl border border-white/5 peer-checked:border-orange-500 peer-checked:bg-orange-600/10 transition-all text-center">
                                    <div class="text-[10px] font-black uppercase text-zinc-400 peer-checked:text-orange-500">Manual Entry</div>
                                    <div class="text-[8px] text-zinc-600 uppercase mt-1">Surgical Grid Control</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[9px] uppercase font-bold text-zinc-500">liveDestinationUrl</label>
                            <div class="flex gap-2">
                                <input type="url" name="url" id="f-url" placeholder="https://..." class="flex-1 bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500">
                                <?php
                                $stmt_agent = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'default_ai_agent'");
                                $stmt_agent->execute();
                                $current_agent = $stmt_agent->fetchColumn() ?: 'gemini';
                                ?>
                                <button type="button" id="scan-btn" onclick="generateAI()" class="px-6 bg-orange-600 text-black font-black uppercase italic text-[10px] rounded-xl hover:brightness-110 active:scale-95 transition-all">
                                    <?php echo strtoupper($current_agent); ?> SCAN & DEPLOY
                                </button>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] uppercase font-bold text-zinc-500">projectIdentifier</label>
                            <input type="text" name="title" id="f-title" placeholder="nodeTitle" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[9px] uppercase font-bold text-zinc-500">nodeClusterType</label>
                            <select name="type" id="f-type" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500 uppercase font-black text-xs">
                                <option value="web">WEB_INTERFACE</option>
                                <option value="app">APP_RUNTIME</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] uppercase font-bold text-zinc-500">masterThumbnailUrl</label>
                            <input type="text" name="thumbnail_url" id="f-thumb" placeholder="thumbnailUrl" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[9px] uppercase font-bold text-zinc-500">nodeContentMarkdown</label>
                        <textarea name="content" id="f-content" placeholder="nodeContentMarkdown" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500 h-40 font-mono text-sm"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 border-b border-white/5">
                        <div class="space-y-2">
                            <label class="text-[9px] uppercase font-bold text-zinc-500">strategicTechStack (Comma separated)</label>
                            <input type="text" name="tech_stack" id="f-tech" placeholder="React, Node.js, PHP..." class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500 text-xs">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] uppercase font-bold text-zinc-500">intelligenceKeywords (Comma separated)</label>
                            <input type="text" name="keywords" id="f-keywords" placeholder="web3, blockchain, saas..." class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500 text-xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 border-b border-white/5">
                        <div class="space-y-2">
                            <label class="text-[9px] uppercase font-bold text-zinc-500">seoMetaTitle</label>
                            <input type="text" name="meta_title" id="f-meta-title" placeholder="seoMetaTitle" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500 text-xs">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] uppercase font-bold text-zinc-500">waInquiryPayload</label>
                            <input type="text" name="wa_message" id="f-wa" placeholder="waInquiryPayload" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500 text-xs">
                        </div>
                    </div>

                    <div class="space-y-4">
                        <label class="text-[9px] uppercase font-bold text-zinc-500">Gallery Integration (Max 5 Slots / Drag & Drop to Base64)</label>
                        <div class="grid grid-cols-5 gap-3">
                            <?php for($i=0; $i<5; $i++): ?>
                            <div class="space-y-2">
                                <div class="aspect-square glass rounded-xl flex flex-col items-center justify-center relative overflow-hidden group border-dashed border-white/10 hover:border-orange-500/50 transition-all cursor-pointer" 
                                     onclick="document.getElementById('gallery-file-<?php echo $i; ?>').click()"
                                     ondragover="event.preventDefault(); this.classList.add('border-orange-500')"
                                     ondragleave="this.classList.remove('border-orange-500')"
                                     ondrop="handleDrop(event, <?php echo $i; ?>)">
                                    <input type="file" id="gallery-file-<?php echo $i; ?>" class="hidden" onchange="handleFile(this, <?php echo $i; ?>)">
                                    <input type="hidden" name="gallery_images[]" id="gallery-input-<?php echo $i; ?>">
                                    <img id="gallery-preview-<?php echo $i; ?>" class="hidden absolute inset-0 w-full h-full object-cover">
                                    <div id="gallery-placeholder-<?php echo $i; ?>" class="text-zinc-700 text-[10px] font-mono group-hover:text-orange-500 transition-colors uppercase">Slot <?php echo $i; ?></div>
                                    <div id="gallery-progress-<?php echo $i; ?>" class="hidden absolute bottom-0 left-0 h-1 bg-orange-500 transition-all duration-300" style="width: 0%"></div>
                                    <button type="button" onclick="event.stopPropagation(); removeGallery(<?php echo $i; ?>)" id="gallery-remove-<?php echo $i; ?>" class="hidden absolute top-1 right-1 p-1 bg-red-600 rounded-md text-white hover:bg-red-700 z-10">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- Multi-Tier Access Matrix -->
                    <div class="space-y-6 pt-6 border-t border-white/5">
                        <h3 class="text-[10px] font-black uppercase tracking-[0.4em] text-orange-500">Automated Direct Login Matrix</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Level 0: Super Admin -->
                            <div class="glass p-6 rounded-2xl space-y-4 border-orange-500/10">
                                <label class="text-[9px] font-black uppercase tracking-widest text-orange-500">Tier 0: Super Admin</label>
                                <input type="url" name="lvl0_login_url" placeholder="loginUrl" class="w-full bg-black/20 border border-white/5 p-3 rounded-lg outline-none focus:border-orange-500 text-[10px]">
                                <input type="text" name="lvl0_user" placeholder="username" class="w-full bg-black/20 border border-white/5 p-3 rounded-lg outline-none focus:border-orange-500 text-[10px]">
                                <input type="text" name="lvl0_pass" placeholder="password" class="w-full bg-black/20 border border-white/5 p-3 rounded-lg outline-none focus:border-orange-500 text-[10px]">
                                <input type="url" name="lvl0_direct_url" placeholder="directLoginUrl" class="w-full bg-black/20 border border-white/5 p-3 rounded-lg outline-none focus:border-orange-500 text-[10px]">
                                <textarea name="lvl0_note" placeholder="accessNote" class="w-full bg-black/20 border border-white/5 p-3 rounded-lg outline-none focus:border-orange-500 text-[10px] h-16"></textarea>
                            </div>
 
                             <!-- Level 1 -->
                            <div class="glass p-6 rounded-2xl space-y-4 border-zinc-500/10">
                                <label class="text-[9px] font-black uppercase tracking-widest text-zinc-500">Tier 1: Restricted</label>
                                <input type="url" name="lvl1_login_url" placeholder="loginUrl" class="w-full bg-black/20 border border-white/5 p-3 rounded-lg outline-none focus:border-orange-500 text-[10px]">
                                <input type="text" name="lvl1_user" placeholder="username" class="w-full bg-black/20 border border-white/5 p-3 rounded-lg outline-none focus:border-orange-500 text-[10px]">
                                <input type="text" name="lvl1_pass" placeholder="password" class="w-full bg-black/20 border border-white/5 p-3 rounded-lg outline-none focus:border-orange-500 text-[10px]">
                                <input type="url" name="lvl1_direct_url" placeholder="directLoginUrl" class="w-full bg-black/20 border border-white/5 p-3 rounded-lg outline-none focus:border-orange-500 text-[10px]">
                                <textarea name="lvl1_note" placeholder="accessNote" class="w-full bg-black/20 border border-white/5 p-3 rounded-lg outline-none focus:border-orange-500 text-[10px] h-16"></textarea>
                            </div>
 
                            <!-- Level 2 -->
                            <div class="glass p-6 rounded-2xl space-y-4 border-blue-500/10">
                                <label class="text-[9px] font-black uppercase tracking-widest text-blue-500">Tier 2: Standard</label>
                                <input type="url" name="lvl2_login_url" placeholder="loginUrl" class="w-full bg-black/20 border border-white/5 p-3 rounded-lg outline-none focus:border-orange-500 text-[10px]">
                                <input type="text" name="lvl2_user" placeholder="username" class="w-full bg-black/20 border border-white/5 p-3 rounded-lg outline-none focus:border-orange-500 text-[10px]">
                                <input type="text" name="lvl2_pass" placeholder="password" class="w-full bg-black/20 border border-white/5 p-3 rounded-lg outline-none focus:border-orange-500 text-[10px]">
                                <input type="url" name="lvl2_direct_url" placeholder="directLoginUrl" class="w-full bg-black/20 border border-white/5 p-3 rounded-lg outline-none focus:border-orange-500 text-[10px]">
                                <textarea name="lvl2_note" placeholder="accessNote" class="w-full bg-black/20 border border-white/5 p-3 rounded-lg outline-none focus:border-orange-500 text-[10px] h-16"></textarea>
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="save_project" class="w-full py-5 bg-white text-black font-black uppercase italic tracking-[0.2em] text-sm rounded-xl hover:bg-orange-500 transition-all shadow-xl text-center">
                        synthesizeCommitToNode
                    </button>
                </form>
            </div>

            <!-- Existing Nodes -->
            <div class="space-y-4">
                <h2 class="text-xs font-black uppercase tracking-[0.4em] text-orange-500">Active Grid Nodes</h2>
                <div class="grid grid-cols-1 gap-4">
                    <?php if(empty($projects)): ?>
                        <div class="glass p-12 text-center text-[10px] font-mono text-zinc-700 uppercase italic">No active nodes detected in sector</div>
                    <?php endif; ?>
                    <?php foreach($projects as $p): ?>
                    <div class="glass p-6 rounded-2xl flex items-center justify-between group border-white/5 hover:border-orange-500/30 transition-all">
                        <div class="flex items-center gap-6">
                            <div class="w-20 h-11 rounded-lg overflow-hidden flex-shrink-0 bg-zinc-900 border border-white/10">
                                <?php echo render_media($p['thumbnail_url']); ?>
                            </div>
                            <div>
                                <h4 class="font-black uppercase italic text-sm"><?php echo $p['title']; ?></h4>
                                <div class="text-[8px] font-mono text-zinc-600 uppercase tracking-widest mt-1">
                                    UUID: <?php echo $p['id']; ?> | <?php echo strtoupper($p['project_type']); ?> | <?php echo $p['slug']; ?>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-all scale-95 group-hover:scale-100">
                            <a href="?toggle_pin=<?php echo $p['id']; ?>" class="px-4 py-2 rounded-lg <?php echo $p['is_pinned'] ? 'bg-orange-600/20 text-orange-500 border border-orange-500/20' : 'bg-white/5 text-zinc-500' ?> font-black uppercase text-[9px] tracking-widest hover:brightness-125 transition-all">
                                <?php echo $p['is_pinned'] ? 'PINNED' : 'PIN TO HERO'; ?>
                            </a>
                            <a href="?delete=<?php echo $p['id']; ?>" class="px-4 py-2 rounded-lg bg-red-600/10 text-red-500 border border-red-500/10 font-black uppercase text-[9px] tracking-widest hover:bg-red-600 hover:text-white transition-all" onclick="return confirm('Erase node?')">TERMINATE</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- System Settings Tab -->
        <div id="tab-system" class="hidden space-y-8">
            <div class="glass p-10 rounded-2xl space-y-8">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-black uppercase tracking-[0.4em] text-orange-500">Core System Settings</h2>
                    <span class="text-[8px] font-mono text-zinc-600">v4.8 // IDENTITY CLUSTER</span>
                </div>
                <form method="POST" class="space-y-6">
                    <?php
                    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
                    $s = [];
                    while($row = $stmt->fetch()) $s[$row['setting_key']] = $row['setting_value'];
                    ?>
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[9px] uppercase font-bold text-zinc-500">Broadcast Station Identifier (App Title)</label>
                                <input type="text" name="appTitle" value="<?php echo htmlspecialchars($s['appTitle'] ?? ''); ?>" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500 font-black">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] uppercase font-bold text-zinc-500">Admin Authentication Email</label>
                                <input type="email" name="authorized_email" value="<?php echo htmlspecialchars($s['authorized_email'] ?? ''); ?>" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500 font-mono">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] uppercase font-bold text-zinc-500">Primary Hero Broadcast Subtext</label>
                            <textarea name="heroSubtext" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500 h-24 text-sm leading-relaxed"><?php echo htmlspecialchars($s['heroSubtext'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-white/5">
                            <div class="space-y-2">
                                <label class="text-[9px] uppercase font-bold text-orange-500">Master Identity ID</label>
                                <input type="text" name="admin_username" value="<?php echo htmlspecialchars($s['admin_username'] ?? ''); ?>" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500 font-mono text-xs">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] uppercase font-bold text-orange-500">Master Passphrase</label>
                                <input type="password" name="admin_password" value="<?php echo htmlspecialchars($s['admin_password'] ?? ''); ?>" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500 font-mono text-xs">
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="update_settings" class="w-full py-5 bg-orange-600 text-black font-black uppercase italic tracking-[0.2em] text-sm rounded-xl hover:brightness-110 transition-all shadow-xl">
                        commitSystemChanges
                    </button>
                </form>
            </div>
        </div>

        <!-- API Keys Tab -->
        <div id="tab-api" class="hidden space-y-8">
            <div class="glass p-10 rounded-2xl space-y-8">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-black uppercase tracking-[0.4em] text-orange-500">Integrations Vault</h2>
                    <span class="text-[8px] font-mono text-zinc-600">ENCRYPTION: ACTIVE</span>
                </div>
                <form method="POST" class="space-y-8">
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[9px] uppercase font-bold text-zinc-400">Gemini AI Key (Node Alpha)</label>
                                <input type="password" name="gemini_api_key" value="<?php echo htmlspecialchars($s['gemini_api_key'] ?? ''); ?>" placeholder="Enter Gemini Key (Leave empty for System Free Tier)" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500 font-mono">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] uppercase font-bold text-zinc-400">DeepSeek AI Key (Node Bravo)</label>
                                <input type="password" name="deepseek_api_key" value="<?php echo htmlspecialchars($s['deepseek_api_key'] ?? ''); ?>" placeholder="Enter DeepSeek Key" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500 font-mono">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[9px] uppercase font-bold text-zinc-500">PageSpeed Insights Index Key</label>
                                <input type="password" name="pagespeed_api_key" value="<?php echo htmlspecialchars($s['pagespeed_api_key'] ?? ''); ?>" placeholder="Enter PageSpeed Key" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500 font-mono">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] uppercase font-bold text-orange-500">Default AI Dispatcher</label>
                                <select name="default_ai_agent" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500 uppercase font-black text-xs">
                                    <option value="gemini" <?php echo ($s['default_ai_agent'] ?? 'gemini') === 'gemini' ? 'selected' : ''; ?>>AI ALPHA: GEMINI 1.5 PRO</option>
                                    <option value="deepseek" <?php echo ($s['default_ai_agent'] ?? 'gemini') === 'deepseek' ? 'selected' : ''; ?>>AI BRAVO: DEEPSEEK V3</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="update_settings" class="w-full py-5 bg-white text-black font-black uppercase italic tracking-[0.2em] text-sm rounded-xl hover:bg-orange-500 transition-all shadow-xl">
                        vaultUpdateMatrix
                    </button>
                    <p class="text-center text-[8px] font-mono text-zinc-600 uppercase tracking-widest italic">Note: AI specialists handle all 'Scan & Deploy' operations based on active node choice.</p>
                </form>

                <!-- AI Usage Stats -->
                <div class="pt-10 border-t border-white/5 space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400">Node Usage Telemetry</h3>
                        <form method="POST" onsubmit="return confirm('Reset all usage metrics?')">
                            <button type="submit" name="reset_usage" class="text-[8px] uppercase font-bold text-red-500 hover:text-white transition-all bg-red-500/10 px-3 py-1.5 rounded-lg border border-red-500/20">resetCounters</button>
                        </form>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="glass p-6 rounded-xl space-y-3 relative overflow-hidden group">
                            <div class="flex justify-between items-center relative z-10">
                                <span class="text-[9px] uppercase font-bold text-zinc-500">Gemini (Node Alpha)</span>
                                <span class="text-xs font-black text-orange-500" id="stat-gemini-scans">...</span>
                            </div>
                            <div class="text-[8px] uppercase font-mono text-zinc-600 tracking-tighter">Usage: Scans performed in current cycle</div>
                            <div class="absolute bottom-0 left-0 h-0.5 bg-orange-600 transition-all duration-1000" style="width: 0%" id="stat-gemini-bar"></div>
                        </div>
                        
                        <div class="glass p-6 rounded-xl space-y-3 relative overflow-hidden group">
                            <div class="flex justify-between items-center relative z-10">
                                <span class="text-[9px] uppercase font-bold text-zinc-500">DeepSeek (Node Bravo)</span>
                                <span class="text-xs font-black text-blue-500" id="stat-deepseek-scans">...</span>
                            </div>
                            <div class="text-[8px] uppercase font-mono text-zinc-600 tracking-tighter" id="stat-deepseek-balance">Balance: Syncing telemetry...</div>
                            <div class="absolute bottom-0 left-0 h-0.5 bg-blue-600 transition-all duration-1000" style="width: 0%" id="stat-deepseek-bar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <script>
        let deploymentMode = 'auto';

        function toggleDeploymentMode(mode) {
            deploymentMode = mode;
            const btn = document.getElementById('scan-btn');
            if(mode === 'manual') {
                btn.innerText = 'VITAL GRID SCAN';
            } else {
                btn.innerText = '<?php echo strtoupper($current_agent); ?> SCAN & DEPLOY';
            }
        }

        function switchTab(tab) {
            document.querySelectorAll('[id^="tab-"]').forEach(el => el.classList.add('hidden'));
            document.getElementById('tab-' + tab).classList.remove('hidden');
            
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('bg-orange-600', 'text-black', 'shadow-lg');
                btn.classList.add('text-zinc-500');
                if(btn.dataset.tab === tab) {
                    btn.classList.add('bg-orange-600', 'text-black', 'shadow-lg');
                    btn.classList.remove('text-zinc-500');
                }
            });

            if(tab === 'api') refreshStats();
        }

        async function refreshStats() {
            try {
                const res = await fetch('api_ai.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ get_stats: true })
                });
                const data = await res.json();
                
                // Update Gemini UI
                document.getElementById('stat-gemini-scans').innerText = data.gemini_scans + ' SCANS';
                const geminiPercent = Math.min((data.gemini_scans / 50) * 100, 100); // 50 is a soft cap for visual
                document.getElementById('stat-gemini-bar').style.width = geminiPercent + '%';
                
                // Update DeepSeek UI
                document.getElementById('stat-deepseek-scans').innerText = data.deepseek_scans + ' SCANS';
                const dsPercent = Math.min((data.deepseek_scans / 50) * 100, 100);
                document.getElementById('stat-deepseek-bar').style.width = dsPercent + '%';
                
                if(data.deepseek_balance && data.deepseek_balance.is_available) {
                    const bal = data.deepseek_balance.balance_infos[0];
                    document.getElementById('stat-deepseek-balance').innerText = `Balance: ${bal.total_balance} ${bal.currency} (${bal.topped_up_balance} Paid)`;
                } else if(data.deepseek_balance) {
                    document.getElementById('stat-deepseek-balance').innerText = "Balance: Unavailable (Check Key)";
                }

            } catch(e) { console.error('Stats Sync Error', e); }
        }

        async function generateAI() {
            const url = document.getElementById('f-url').value;
            const title = document.getElementById('f-title').value;
            if(!url) return alert('Protocol Error: Destination URL Required');
            
            const loader = document.getElementById('ai-loading');
            const status = document.getElementById('ai-status');
            loader.classList.remove('hidden');
            status.innerText = deploymentMode === 'auto' ? "Agent Scanning Destination..." : "Extracting Vital Stats...";
            
            try {
                const res = await fetch('api_ai.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ 
                        url, 
                        title, 
                        vitals_only: (deploymentMode === 'manual') 
                    })
                });
                const data = await res.json();
                
                if(data.error) throw new Error(data.error);
                
                status.innerText = "Synchronizing Metadata...";
                
                // Common fields (Vitals)
                if(data.speed) {
                    document.getElementById('f-speed').value = data.speed;
                }
                if(data.screenshot) {
                    document.getElementById('f-thumb').value = data.screenshot;
                }

                    // AI Fields
                    if(deploymentMode === 'auto') {
                        if(data.content) document.getElementById('f-content').value = data.content;
                        if(data.metaTitle) document.getElementById('f-meta-title').value = data.metaTitle;
                        if(data.metaTitle && !title) document.getElementById('f-title').value = data.metaTitle;
                        if(data.waMessage) document.getElementById('f-wa').value = data.waMessage;
                        
                        // Tech Stack population
                        if (data.techStack && data.techStack.length > 0) {
                            const stack = data.techStack.map(s => s.name).join(', ');
                            document.getElementById('f-tech').value = stack;
                        }

                        // Keywords population
                        if (data.keywords && data.keywords.length > 0) {
                            document.getElementById('f-keywords').value = data.keywords.join(', ');
                        }

                        alert('Autonomous Analysis Complete. Node successfully reconfigured.');
                    } else {
                    alert('Vital Scan Complete. Performance stats and screenshot captured.');
                }
            } catch(e) {
                alert('Neutralization Failed: ' + (e.message || 'Unknown Protocol Error'));
            } finally {
                loader.classList.add('hidden');
            }
        }

        // Gallery Handlers
        function handleDrop(e, index) {
            e.preventDefault();
            this.classList.remove('border-orange-500');
            const file = e.dataTransfer.files[0];
            if (file) processFile(file, index);
        }

        function handleFile(input, index) {
            const file = input.files[0];
            if (file) processFile(file, index);
        }

        function processFile(file, index) {
            if (!file.type.startsWith('image/')) return alert('CRITICAL: Image nodes only');
            
            const progress = document.getElementById('gallery-progress-' + index);
            const placeholder = document.getElementById('gallery-placeholder-' + index);
            
            progress.classList.remove('hidden');
            progress.style.width = '0%';
            placeholder.innerText = 'Converting...';

            const reader = new FileReader();
            
            reader.onprogress = (e) => {
                if (e.lengthComputable) {
                    const pct = (e.loaded / e.total) * 100;
                    progress.style.width = pct + '%';
                }
            };

            reader.onload = (e) => {
                const base64 = e.target.result;
                document.getElementById('gallery-input-' + index).value = base64;
                const preview = document.getElementById('gallery-preview-' + index);
                preview.src = base64;
                preview.classList.remove('hidden');
                document.getElementById('gallery-remove-' + index).classList.remove('hidden');
                progress.style.width = '100%';
                setTimeout(() => progress.classList.add('hidden'), 500);
            };
            reader.readAsDataURL(file);
        }

        function removeGallery(index) {
            document.getElementById('gallery-input-' + index).value = '';
            const preview = document.getElementById('gallery-preview-' + index);
            preview.src = '';
            preview.classList.add('hidden');
            document.getElementById('gallery-remove-' + index).classList.add('hidden');
            document.getElementById('gallery-placeholder-' + index).innerText = 'Slot ' + index;
        }

        // Initial Tab setup
        window.onload = () => switchTab('posting');
    </script>
    </div>
</body>
</html>
