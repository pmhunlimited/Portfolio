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
                <h1 class="text-3xl font-black italic tracking-tighter uppercase glow-orange">Node_Access</h1>
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
                        <input type="text" name="username" placeholder="IDENTITY_ID" required
                               class="w-full bg-black/40 border border-white/10 rounded-2xl py-5 pl-14 pr-4 outline-none focus:border-orange-600 transition-all font-mono text-xs tracking-widest">
                    </div>

                    <div class="relative group">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-zinc-600 group-focus-within:text-orange-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        </div>
                        <input type="password" name="password" placeholder="PASSPHRASE" required
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
    $keys = ['appTitle', 'heroSubtext', 'gemini_api_key', 'deepseek_api_key', 'pagespeed_api_key', 'admin_username', 'admin_password', 'authorized_email'];
    foreach ($keys as $key) {
        if (isset($_POST[$key])) {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $_POST[$key], $_POST[$key]]);
        }
    }
    header("Location: admin.php?settings_updated=1");
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

    $stmt = $pdo->prepare("INSERT INTO projects (title, slug, content, site_url, thumbnail_url, project_type) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $slug, $content, $url, $thumbnail, $type]);
    header("Location: admin.php?success=1");
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: admin.php?deleted=1");
    exit;
}

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
            <a href="index.php" class="text-xs uppercase font-bold text-zinc-500 hover:text-white">Exit_Portal</a>
        </header>

        <!-- Admin Tabs (System vs Projects) -->
        <div class="flex gap-4 mb-8">
            <button onclick="switchTab('projects')" class="tab-btn px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] transition-all bg-orange-600 text-black shadow-lg" data-tab="projects">Projects</button>
            <button onclick="switchTab('system')" class="tab-btn px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] transition-all text-zinc-500 hover:text-white" data-tab="system">System Settings</button>
        </div>

        <!-- Project Management Tab -->
        <div id="tab-projects" class="space-y-12">
            <!-- Project Entry Form -->
            <div class="glass p-10 rounded-2xl space-y-8">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-black uppercase tracking-[0.4em] text-orange-500">Node_Entry_Portal</h2>
                    <div id="ai-loading" class="hidden flex items-center gap-2 text-[10px] text-orange-500 font-mono italic animate-pulse">
                        <svg class="animate-spin h-3 w-3" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Agent_Decoding...
                    </div>
                </div>

                <form method="POST" class="space-y-6" id="project-form">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[9px] uppercase font-bold text-zinc-500">Live URL</label>
                            <div class="flex gap-2">
                                <input type="url" name="url" id="f-url" placeholder="https://..." class="flex-1 bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500">
                                <button type="button" onclick="generateAI()" class="px-6 bg-orange-600 text-black font-black uppercase italic text-[10px] rounded-xl hover:brightness-110 active:scale-95 transition-all">
                                    Magic_AI
                                </button>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] uppercase font-bold text-zinc-500">Internal Name</label>
                            <input type="text" name="title" id="f-title" placeholder="Project Name" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[9px] uppercase font-bold text-zinc-500">Node Cluster</label>
                            <select name="type" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500 uppercase font-black text-xs">
                                <option value="web">Web_Interface</option>
                                <option value="app">Mobile_App</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] uppercase font-bold text-zinc-500">Master_Visual_URL</label>
                            <input type="text" name="thumbnail_url" id="f-thumb" placeholder="https://images..." class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[9px] uppercase font-bold text-zinc-500">Power_Pitch (Markdown)</label>
                        <textarea name="content" id="f-content" placeholder="System capabilities..." class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500 h-40 font-mono text-sm"></textarea>
                    </div>

                    <button type="submit" name="save_project" class="w-full py-5 bg-white text-black font-black uppercase italic tracking-[0.2em] text-sm rounded-xl hover:bg-orange-500 transition-all shadow-xl text-center">
                        Commit Node to Grid
                    </button>
                </form>
            </div>

            <!-- Existing Nodes -->
            <div class="space-y-4">
                <h2 class="text-xs font-black uppercase tracking-[0.4em] text-orange-500">Active_Nodes</h2>
                <div class="grid grid-cols-1 gap-4">
                    <?php foreach($projects as $p): ?>
                    <div class="glass p-6 rounded-2xl flex items-center justify-between group">
                        <div class="flex items-center gap-6">
                            <div class="w-20 h-11 rounded-lg overflow-hidden flex-shrink-0 bg-zinc-900 border border-white/5">
                                <?php echo render_media($p['thumbnail_url']); ?>
                            </div>
                            <div>
                                <h4 class="font-black uppercase italic"><?php echo $p['title']; ?></h4>
                                <div class="text-[9px] font-mono text-zinc-500 uppercase tracking-widest"><?php echo $p['slug']; ?></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-all">
                            <a href="?toggle_pin=<?php echo $p['id']; ?>" class="p-3 rounded-lg hover:bg-orange-600/20 text-orange-500 font-bold uppercase text-[9px] tracking-widest">
                                <?php echo $p['is_pinned'] ? 'PINNED' : 'PIN'; ?>
                            </a>
                            <a href="?delete=<?php echo $p['id']; ?>" class="p-3 rounded-lg hover:bg-red-600/20 text-red-500 font-bold uppercase text-[9px] tracking-widest" onclick="return confirm('Erase node?')">DEL</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- System Settings Tab -->
        <div id="tab-system" class="hidden space-y-12">
            <div class="glass p-10 rounded-2xl space-y-8">
                <h2 class="text-xs font-black uppercase tracking-[0.4em] text-orange-500">Global_Configuration</h2>
                <form method="POST" class="space-y-6">
                    <?php
                    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
                    $s = [];
                    while($row = $stmt->fetch()) $s[$row['setting_key']] = $row['setting_value'];
                    ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[9px] uppercase font-bold text-zinc-500">Station Identity</label>
                            <input type="text" name="appTitle" value="<?php echo htmlspecialchars($s['appTitle'] ?? ''); ?>" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] uppercase font-bold text-zinc-500">Auth Matrix Email</label>
                            <input type="email" name="authorized_email" value="<?php echo htmlspecialchars($s['authorized_email'] ?? ''); ?>" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] uppercase font-bold text-zinc-500">Broadcast Subtext</label>
                        <textarea name="heroSubtext" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500 h-24"><?php echo htmlspecialchars($s['heroSubtext'] ?? ''); ?></textarea>
                    </div>

                    <div class="pt-8 border-t border-white/5 space-y-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-[10px] font-black uppercase tracking-widest text-orange-500">Secure API Vault</h3>
                            <span class="text-[8px] font-mono text-zinc-500 italic uppercase">AES-256 Equivalent Simulation</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[9px] uppercase font-bold text-zinc-500">Gemini Key</label>
                                <input type="password" name="gemini_api_key" value="<?php echo htmlspecialchars($s['gemini_api_key'] ?? ''); ?>" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] uppercase font-bold text-zinc-500">DeepSeek Key</label>
                                <input type="password" name="deepseek_api_key" value="<?php echo htmlspecialchars($s['deepseek_api_key'] ?? ''); ?>" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[9px] uppercase font-bold text-orange-500">Admin Identity</label>
                                <input type="text" name="admin_username" value="<?php echo htmlspecialchars($s['admin_username'] ?? ''); ?>" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500 font-mono text-xs">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] uppercase font-bold text-orange-500">Admin Passphrase</label>
                                <input type="password" name="admin_password" value="<?php echo htmlspecialchars($s['admin_password'] ?? ''); ?>" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500 font-mono text-xs">
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="update_settings" class="w-full py-5 bg-orange-600 text-black font-black uppercase italic tracking-[0.2em] text-sm rounded-xl hover:brightness-110 active:scale-95 transition-all shadow-xl text-center">
                        Synchronize Matrix
                    </button>
                </form>
            </div>
        </div>

    <script>
        function switchTab(tab) {
            document.querySelectorAll('[id^="tab-"]').forEach(el => el.classList.add('hidden'));
            document.getElementById('tab-' + tab).classList.remove('hidden');
            
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('bg-orange-600', 'text-black');
                btn.classList.add('text-zinc-500');
                if(btn.dataset.tab === tab) {
                    btn.classList.add('bg-orange-600', 'text-black');
                    btn.classList.remove('text-zinc-500');
                }
            });
        }

        async function generateAI() {
            const url = document.getElementById('f-url').value;
            const title = document.getElementById('f-title').value;
            if(!url) return alert('Target URL Required');
            
            const loader = document.getElementById('ai-loading');
            loader.classList.remove('hidden');
            
            try {
                const res = await fetch('api_ai.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ url, title })
                });
                const data = await res.json();
                
                if(data.error) throw new Error(data.error);
                
                if(data.content) document.getElementById('f-content').value = data.content;
                if(data.metaTitle && !title) document.getElementById('f-title').value = data.metaTitle;
                
                alert('Analysis Complete. Node data populated.');
            } catch(e) {
                alert('Analysis Error: ' + (e.message || 'Unknown Protocol Error'));
            } finally {
                loader.classList.add('hidden');
            }
        }
    </script>
    </div>
</body>
</html>
