<?php
require_once 'db.php';
require_once 'functions.php';

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM projects WHERE slug = ?");
$stmt->execute([$slug]);
$project = $stmt->fetch();

if (!$project) {
    echo "<h1>404: NODE_NOT_FOUND</h1>";
    exit;
}

// Fetch Gallery
$gallery_stmt = $pdo->prepare("SELECT * FROM project_gallery WHERE project_id = ? ORDER BY sort_order ASC");
$gallery_stmt->execute([$project['id']]);
$gallery = $gallery_stmt->fetchAll();

// Fetch Tech Stacks
$tech_stmt = $pdo->prepare("SELECT * FROM tech_stacks WHERE project_id = ?");
$tech_stmt->execute([$project['id']]);
$tech_stack = $tech_stmt->fetchAll();

// Fetch Keywords
$key_stmt = $pdo->prepare("SELECT * FROM keywords WHERE project_id = ?");
$key_stmt->execute([$project['id']]);
$keywords = $key_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $project['title']; ?> | Cyber-Pulse</title>
    <meta name="description" content="<?php echo $project['meta_description']; ?>">
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
            --pitch-black: #050505;
        }
        body { background: var(--pitch-black); color: white; font-family: 'Inter', sans-serif; }
        .glass { background: rgba(255,255,255,0.03); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.08); }
        .text-glow-orange { text-shadow: 0 0 20px rgba(255, 102, 0, 0.4); }
        .prose p { margin-bottom: 2rem; color: #a1a1aa; line-height: 2; font-weight: 500; }
        .sharp-orange { color: var(--sharp-orange); }
    </style>
</head>
<body class="selection:bg-orange-600 selection:text-white p-6 md:p-12 min-h-screen">
    <nav class="fixed top-0 left-0 w-full z-50 glass border-b border-white/5 px-8 py-4">
        <a href="index.php" class="text-[10px] font-black uppercase tracking-[0.4em] text-zinc-500 hover:text-white transition-all flex items-center gap-3 group">
            <span class="group-hover:-translate-x-1 transition-transform">←</span> Return_to_Grid
        </a>
    </nav>

    <main class="max-w-7xl mx-auto pt-24 grid grid-cols-1 xl:grid-cols-2 gap-20">
        <!-- Left Column: Vision Cluster -->
        <div class="space-y-12">
            <div class="aspect-video glass rounded-[32px] overflow-hidden relative border border-white/10 shadow-[0_0_100px_rgba(255,102,0,0.05)]">
                <?php echo render_media($project['thumbnail_url'], "w-full h-full object-cover grayscale-[0.3] hover:grayscale-0 transition-all duration-700"); ?>
                <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
            </div>

            <!-- Analysis HUD -->
            <div class="grid grid-cols-3 gap-4">
                <div class="glass p-6 rounded-2xl border-orange-500/10">
                    <div class="text-[8px] font-black uppercase tracking-[0.3em] text-orange-500 mb-2">Performance</div>
                    <div class="text-3xl font-black italic tracking-tighter"><?php echo $project['speed'] ?? 98; ?><span class="text-[10px] text-zinc-600 ml-1">GTX</span></div>
                </div>
                <div class="glass p-6 rounded-2xl border-purple-500/10">
                    <div class="text-[8px] font-black uppercase tracking-[0.3em] text-purple-500 mb-2">Security</div>
                    <div class="text-3xl font-black italic tracking-tighter">100<span class="text-[10px] text-zinc-600 ml-1">LYR</span></div>
                </div>
                <div class="glass p-6 rounded-2xl border-blue-500/10">
                    <div class="text-[8px] font-black uppercase tracking-[0.3em] text-blue-500 mb-2">Inquiries</div>
                    <div class="text-3xl font-black italic tracking-tighter"><?php echo $project['inquiries_count'] ?? 0; ?><span class="text-[10px] text-zinc-600 ml-1">HIT</span></div>
                </div>
            </div>

            <!-- Intelligence Map (Keywords) -->
            <div class="flex flex-wrap gap-2">
                <?php foreach($keywords as $k): ?>
                <span class="px-4 py-1.5 glass rounded-full text-[9px] font-black uppercase tracking-[0.2em] text-zinc-500 border-white/5">
                    #<?php echo $k['keyword']; ?>
                </span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Right Column: Strategic Protocol -->
        <div class="space-y-16">
            <div class="space-y-6">
                <div class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.4em] text-orange-500 animate-pulse">
                    <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                    Master_Protocol_Active
                </div>
                <h1 class="text-6xl md:text-8xl font-black italic uppercase tracking-tighter leading-[0.8] text-glow-orange">
                    <?php echo $project['title']; ?>
                </h1>
                <div class="flex flex-wrap gap-3">
                    <?php foreach($tech_stack as $t): ?>
                    <div class="px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] text-zinc-300 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-zinc-700"></span>
                        <?php echo $t['name']; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="prose max-w-none text-xl">
                <?php echo nl2br($project['content']); ?>
            </div>

            <div class="pt-12 border-t border-white/5 flex flex-col md:flex-row gap-10 items-center">
                <a href="<?php echo $project['site_url']; ?>" target="_blank" 
                   class="group relative w-full md:w-auto px-12 py-5 bg-orange-600 text-black font-black uppercase tracking-[0.3em] text-xs rounded-2xl transition-all hover:scale-105 active:scale-95 shadow-[0_0_50px_rgba(234,88,12,0.2)]">
                    <span class="relative z-10">Launch_Live_Interface</span>
                    <div class="absolute inset-0 bg-white/20 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-500 skew-x-12"></div>
                </a>

                <div class="space-y-1">
                    <div class="text-[9px] font-black uppercase text-zinc-600 tracking-widest leading-none">Node_ID_Authorization</div>
                    <div class="text-xs font-mono text-zinc-300 font-bold"><?php echo strtoupper($project['slug']); ?>-V1.0</div>
                </div>
            </div>
        </div>
    </main>

    <!-- Visual Gallery Matrix -->
    <section class="max-w-7xl mx-auto py-32 space-y-12">
        <h2 class="text-xs font-black uppercase tracking-[0.5em] text-zinc-700 text-center">Visual_Identity_Nodes</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
            <?php foreach($gallery as $img): ?>
            <div class="group aspect-square glass rounded-3xl overflow-hidden relative border border-white/5 hover:border-orange-500/30 transition-all">
                <?php echo render_media($img['media_url'], "w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"); ?>
                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <span class="text-[10px] font-black uppercase tracking-widest text-white border-b-2 border-orange-600">View_Media</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</body>
</html>
