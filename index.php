<?php
require_once 'db.php';
require_once 'functions.php';

// Global Security Pulse: Protection Headers
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self' https: 'unsafe-inline' 'unsafe-eval' data: blob:;");

// Fetch Settings
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$appTitle = $settings['appTitle'] ?? 'CYBER-PULSE';
$heroSubtext = $settings['heroSubtext'] ?? '';

// Search & Filter Logic
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM projects WHERE 1=1";
$params = [];

if ($filter !== 'all') {
    $sql .= " AND project_type = ?";
    $params[] = $filter;
}

if ($search) {
    $sql .= " AND (title LIKE ? OR content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();

// Hero Nodes Prioritization: Pinned -> Latest (Max 4)
$hero_projects = $pdo->query("SELECT * FROM projects WHERE is_pinned = 1 ORDER BY created_at DESC LIMIT 4")->fetchAll();
if (count($hero_projects) < 4) {
    $needed = 4 - count($hero_projects);
    $pinned_ids = array_column($hero_projects, 'id');
    $placeholders = empty($pinned_ids) ? "" : "AND id NOT IN (".implode(',', $pinned_ids).")";
    $backfills = $pdo->query("SELECT * FROM projects WHERE 1=1 $placeholders ORDER BY created_at DESC LIMIT $needed")->fetchAll();
    $hero_projects = array_merge($hero_projects, $backfills);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $appTitle; ?> | Elite Portfolio Engine</title>
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <style>
        :root {
            --sharp-orange: #FF6600;
            --glossy-purple: #BF00FF;
            --pitch-black: #000000;
        }
        body { background: var(--pitch-black); color: white; font-family: 'Inter', sans-serif; }
        .glass { background: rgba(255,255,255,0.03); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.08); }
        .text-glow-orange { text-shadow: 0 0 10px rgba(255, 102, 0, 0.5); }
        .sharp-orange { color: var(--sharp-orange); }
    </style>
</head>
<body class="selection:bg-[#FF6600]">
    
    <nav class="fixed top-0 w-full z-50 glass border-b border-white/5 px-6 py-4 flex justify-between items-center">
        <div class="text-xl font-black italic tracking-tighter uppercase">
            <?php 
                $parts = explode('-', $appTitle);
                echo $parts[0];
                if(isset($parts[1])) echo " <span class='sharp-orange'>{$parts[1]}</span>";
            ?>
        </div>
        <div class="hidden md:flex gap-8">
            <a href="index.php" class="text-[10px] font-black uppercase tracking-widest hover:text-sharp-orange transition-colors">Grid</a>
            <a href="admin.php" class="text-[10px] font-black uppercase tracking-widest text-[#BF00FF] hover:brightness-110">Security Portal</a>
        </div>
    </nav>

    <main class="pt-32 px-6 max-w-7xl mx-auto space-y-32 pb-20">
        <!-- Hero Section -->
        <section class="flex flex-col lg:flex-row gap-16 items-center">
            <div class="flex-1 space-y-8 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-600/10 border border-orange-600/20 text-[10px] font-black uppercase tracking-[0.3em] text-orange-500">
                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></span>
                    System Online
                </div>
                <h1 class="text-6xl md:text-8xl font-black italic uppercase tracking-tighter leading-[0.8] text-glow-orange">
                    Future Proofed <br/> <span class="text-zinc-500">Engineering</span>
                </h1>
                <p class="text-zinc-400 text-lg md:text-xl font-medium max-w-2xl leading-relaxed">
                    <?php echo $heroSubtext; ?>
                </p>
            </div>
            
            <div class="w-full lg:w-[450px] grid grid-cols-2 gap-4">
                <?php foreach($hero_projects as $hp): ?>
                <a href="project.php?slug=<?php echo $hp['slug']; ?>" class="group block aspect-square glass rounded-[24px] overflow-hidden relative border border-white/10 hover:border-orange-500/40 transition-all shadow-2xl">
                    <?php echo render_media($hp['thumbnail_url'], "absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"); ?>
                    <div class="absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-black/80 to-transparent backdrop-blur-[2px]">
                        <div class="text-[9px] font-black uppercase tracking-widest text-white truncate"><?php echo $hp['title']; ?></div>
                        <?php if($hp['is_pinned']): ?>
                            <div class="text-[7px] font-mono text-orange-500 uppercase mt-1">Priority Node 0<?php echo $hp['id']; ?></div>
                        <?php endif; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Project Grid -->
        <section class="space-y-12">
            <div class="flex flex-col md:flex-row justify-between items-center gap-8">
                <div class="flex flex-wrap gap-4">
                    <?php foreach(['all', 'web', 'app'] as $t): ?>
                    <a href="?filter=<?php echo $t; ?>&search=<?php echo htmlspecialchars($search); ?>" 
                       class="px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] transition-all <?php echo $filter === $t ? 'bg-orange-600 text-black shadow-lg shadow-orange-600/20' : 'glass text-zinc-500 hover:text-white'; ?>">
                        <?php echo $t; ?>
                    </a>
                    <?php endforeach; ?>
                </div>

                <form method="GET" class="relative group">
                    <input type="hidden" name="filter" value="<?php echo $filter; ?>">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search Active Nodes..." 
                           class="bg-white/5 border border-white/10 rounded-xl py-3 pl-10 pr-4 outline-none focus:border-orange-500 transition-all font-mono text-[10px] w-64 uppercase">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500 group-focus-within:text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php if(empty($projects)): ?>
                    <div class="col-span-full py-20 text-center font-mono text-zinc-600 uppercase tracking-widest">Database Query Null Entities</div>
                <?php endif; ?>
                <?php foreach($projects as $proj): ?>
                <a href="project.php?slug=<?php echo $proj['slug']; ?>" class="group glass rounded-2xl overflow-hidden p-3 border border-white/5 hover:border-sharp-orange/30 transition-all hover:translate-y-[-4px]">
                    <div class="aspect-video rounded-xl overflow-hidden mb-4 relative bg-black/40">
                        <?php echo render_media($proj['thumbnail_url']); ?>
                    </div>
                    <div class="space-y-2">
                        <h3 class="font-black text-lg uppercase truncate italic leading-none"><?php echo $proj['title']; ?></h3>
                        <div class="flex justify-between items-center text-[10px] font-mono text-zinc-500 uppercase tracking-tighter">
                            <span><?php echo $proj['project_type']; ?> Node</span>
                            <span class="group-hover:text-orange-500 transition-colors">Access Portal →</span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <footer class="py-20 border-t border-white/5 px-6 text-center text-zinc-600 font-mono text-[9px] uppercase tracking-widest">
        &copy; <?php echo date('Y'); ?> <?php echo $appTitle; ?> Node Controller. System Architecture Secure.
    </footer>

</body>
</html>
