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
        const originalError = console.error;
        console.error = function(...args) {
          if (args[0] && typeof args[0] === 'string' && (args[0].includes('MetaMask') || args[0].includes('ethereum'))) return;
          originalError.apply(console, args);
        };
      })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <style>
        body { background: #000; color: white; font-family: 'Inter', sans-serif; }
        .glass { background: rgba(255,255,255,0.05); backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.1); }
        .text-glow-orange { text-shadow: 0 0 10px rgba(255, 102, 0, 0.5); }
        .prose p { margin-bottom: 1.5rem; color: #a1a1aa; line-height: 1.8; }
    </style>
</head>
<body class="p-6 md:p-12">
    <nav class="mb-12">
        <a href="index.php" class="text-[10px] font-black uppercase tracking-[0.3em] text-zinc-500 hover:text-white transition-all flex items-center gap-2">
            ← Return_to_Grid
        </a>
    </nav>

    <main class="max-w-7xl mx-auto grid grid-cols-1 xl:grid-cols-2 gap-16">
        <!-- Left Column: Media Display -->
        <div class="space-y-8">
            <div class="aspect-video glass rounded-3xl overflow-hidden relative border-orange-500/20 shadow-[0_0_50px_rgba(255,102,0,0.1)]">
                <?php echo render_media($project['thumbnail_url'], "w-full h-full object-cover"); ?>
            </div>

            <!-- Gallery Grid -->
            <div class="grid grid-cols-5 gap-4">
                <?php foreach($gallery as $img): ?>
                <div class="aspect-square glass rounded-xl overflow-hidden border border-white/5">
                    <?php echo render_media($img['media_url']); ?>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Performance HUD -->
            <div class="grid grid-cols-2 gap-4">
                <div class="glass p-6 rounded-2xl border-orange-500/10">
                    <div class="text-[9px] font-black uppercase tracking-[0.2em] text-orange-500 mb-2">Core Vitals</div>
                    <div class="text-3xl font-black italic">98<span class="text-xs text-zinc-500 ml-1">GTX</span></div>
                </div>
                <div class="glass p-6 rounded-2xl border-purple-500/10">
                    <div class="text-[9px] font-black uppercase tracking-[0.2em] text-purple-500 mb-2">Defense Layer</div>
                    <div class="text-3xl font-black italic">100<span class="text-xs text-zinc-500 ml-1">L7</span></div>
                </div>
            </div>
        </div>

        <!-- Right Column: Strategic Analysis -->
        <div class="space-y-12">
            <header class="space-y-4">
                <h1 class="text-5xl md:text-7xl font-black italic uppercase tracking-tighter leading-none text-glow-orange">
                    <?php echo $project['title']; ?>
                </h1>
                <div class="flex flex-wrap gap-2">
                    <?php foreach($tech_stack as $t): ?>
                    <span class="px-3 py-1 bg-white/5 border border-white/10 rounded-full text-[10px] font-bold uppercase tracking-widest text-zinc-300">
                        <?php echo $t['name']; ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </header>

            <div class="prose max-w-none font-medium text-lg leading-relaxed">
                <?php echo nl2br($project['content']); ?>
            </div>

            <div class="pt-8 border-t border-white/10 flex flex-col md:flex-row gap-6 items-center justify-between">
                <a href="<?php echo $project['site_url']; ?>" target="_blank" class="w-full md:w-auto px-8 py-4 bg-orange-600 hover:bg-orange-500 font-black uppercase tracking-[0.2em] text-sm rounded-xl transition-all text-center">
                    Launch_Interface
                </a>
                
                <div class="flex items-center gap-6">
                    <div class="text-right">
                        <div class="text-[9px] font-black uppercase text-zinc-500">Node ID</div>
                        <div class="text-[11px] font-mono text-white"><?php echo strtoupper($project['slug']); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
