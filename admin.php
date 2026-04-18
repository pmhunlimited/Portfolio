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
        const originalError = console.error;
        console.error = function(...args) {
          if (args[0] && typeof args[0] === 'string' && (args[0].includes('MetaMask') || args[0].includes('ethereum'))) return;
          originalError.apply(console, args);
        };
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

        <!-- Global Settings -->
        <div class="glass p-8 rounded-2xl space-y-6">
            <h2 class="text-xs font-black uppercase tracking-[0.3em] text-zinc-500">Global_Pulse_Config</h2>
            <form method="POST" class="space-y-4">
                <?php
                $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
                $s = [];
                while($row = $stmt->fetch()) $s[$row['setting_key']] = $row['setting_value'];
                ?>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-[9px] uppercase font-bold text-zinc-500">App Title</label>
                        <input type="text" name="appTitle" value="<?php echo htmlspecialchars($s['appTitle'] ?? ''); ?>" placeholder="App Title" class="w-full bg-black/40 border border-white/10 p-3 rounded-lg outline-none focus:border-orange-500">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] uppercase font-bold text-zinc-500">Authorized Admin Email</label>
                        <input type="email" name="authorized_email" value="<?php echo htmlspecialchars($s['authorized_email'] ?? ''); ?>" placeholder="admin@example.com" class="w-full bg-black/40 border border-white/10 p-3 rounded-lg outline-none focus:border-orange-500">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[9px] uppercase font-bold text-zinc-500">Hero Subtext</label>
                    <textarea name="heroSubtext" placeholder="Hero Subtext" class="w-full bg-black/40 border border-white/10 p-3 rounded-lg outline-none focus:border-orange-500 h-20"><?php echo htmlspecialchars($s['heroSubtext'] ?? ''); ?></textarea>
                </div>

                <div class="pt-6 border-t border-white/5 space-y-4">
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-orange-500">Secure API Vault</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <label class="text-[9px] uppercase font-bold text-zinc-500">Gemini Key</label>
                            <input type="password" name="gemini_api_key" value="<?php echo htmlspecialchars($s['gemini_api_key'] ?? ''); ?>" placeholder="••••" class="w-full bg-black/40 border border-white/10 p-3 rounded-lg outline-none focus:border-orange-500 font-mono text-xs">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] uppercase font-bold text-zinc-500">DeepSeek Key</label>
                            <input type="password" name="deepseek_api_key" value="<?php echo htmlspecialchars($s['deepseek_api_key'] ?? ''); ?>" placeholder="••••" class="w-full bg-black/40 border border-white/10 p-3 rounded-lg outline-none focus:border-orange-500 font-mono text-xs">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] uppercase font-bold text-zinc-500">PageSpeed Key</label>
                            <input type="password" name="pagespeed_api_key" value="<?php echo htmlspecialchars($s['pagespeed_api_key'] ?? ''); ?>" placeholder="••••" class="w-full bg-black/40 border border-white/10 p-3 rounded-lg outline-none focus:border-orange-500 font-mono text-xs">
                        </div>
                    </div>
                </div>

                <div class="pt-4 space-y-2">
                    <label class="text-[9px] uppercase font-bold text-orange-500">Change Admin Portal Master Passkey</label>
                    <input type="password" name="admin_password" value="<?php echo htmlspecialchars($s['admin_password'] ?? ''); ?>" placeholder="New Passkey" class="w-full bg-black/40 border border-white/10 p-3 rounded-lg outline-none focus:border-orange-500 font-mono text-xs">
                </div>

                <button type="submit" name="update_settings" class="w-full bg-white/5 border border-white/10 hover:border-orange-500 text-white font-black py-3 rounded-xl uppercase tracking-widest transition-all text-xs">Update Pulse Core</button>
            </form>
        </div>

        <!-- New Node Entry -->
        <div class="glass p-8 rounded-2xl space-y-6">
            <h2 class="text-xs font-black uppercase tracking-[0.3em] text-zinc-500">Inject_New_Node</h2>
            <form method="POST" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" name="title" placeholder="Project Title" class="w-full bg-black/40 border border-white/10 p-3 rounded-lg outline-none focus:border-orange-500" required>
                    <input type="url" name="url" placeholder="Production URL" class="w-full bg-black/40 border border-white/10 p-3 rounded-lg outline-none focus:border-orange-500">
                </div>
                <textarea name="content" placeholder="System Architecture & Strategy (Power Pitch)" class="w-full bg-black/40 border border-white/10 p-3 h-32 rounded-lg outline-none focus:border-orange-500"></textarea>
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" name="thumbnail_url" placeholder="Thumbnail URL" class="w-full bg-black/40 border border-white/10 p-3 rounded-lg outline-none focus:border-orange-500">
                    <select name="type" class="bg-black/40 border border-white/10 p-3 rounded-lg outline-none">
                        <option value="web">SEO Web</option>
                        <option value="app">App Build</option>
                    </select>
                </div>
                <button type="submit" name="save_project" class="w-full bg-orange-600 hover:bg-orange-500 text-white font-black py-4 rounded-xl uppercase tracking-widest transition-all">Publish Node</button>
            </form>
        </div>

        <!-- Node List -->
        <div class="space-y-4">
            <h2 class="text-xs font-black uppercase tracking-[0.3em] text-zinc-500">Active_Nodes</h2>
            <?php foreach($projects as $p): ?>
            <div class="glass p-4 rounded-xl flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-black rounded-lg overflow-hidden border border-white/10">
                        <?php echo render_media($p['thumbnail_url']); ?>
                    </div>
                    <div>
                        <div class="text-sm font-bold uppercase"><?php echo $p['title']; ?></div>
                        <div class="text-[9px] font-mono text-zinc-500"><?php echo $p['slug']; ?></div>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <a href="?toggle_pin=<?php echo $p['id']; ?>" class="text-[9px] font-black uppercase tracking-widest px-3 py-1 rounded border <?php echo $p['is_pinned'] ? 'bg-orange-500 border-orange-500 text-black' : 'border-white/10 text-zinc-500'; ?>">
                        <?php echo $p['is_pinned'] ? 'Pinned' : 'Pin_to_Hero'; ?>
                    </a>
                    <a href="?delete=<?php echo $p['id']; ?>" class="p-2 text-zinc-500 hover:text-red-500" onclick="return confirm('Purge Node permanently?');">PURGE</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
