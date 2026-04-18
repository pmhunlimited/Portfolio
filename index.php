<?php
require_once 'db.php';
require_once 'functions.php';

// Fetch Settings
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$appTitle = $settings['appTitle'] ?? 'CYBER-PULSE';
$heroSubtext = $settings['heroSubtext'] ?? '';

// Fetch Projects
$filter = $_GET['filter'] ?? 'all';
$query = "SELECT * FROM projects";
if ($filter !== 'all') {
    $query .= " WHERE project_type = :type";
}
$query .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($query);
if ($filter !== 'all') {
    $stmt->execute(['type' => $filter]);
} else {
    $stmt->execute();
}
$projects = $stmt->fetchAll();

// Hero Nodes (Pinned or Latest)
$hero_projects = array_filter($projects, function($p) { return $p['is_pinned']; });
if (empty($hero_projects)) {
    $hero_projects = array_slice($projects, 0, 4);
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
        :root {
            --sharp-orange: #FF6600;
            --glossy-purple: #BF00FF;
            --pitch-black: #000000;
        }
        body { background: var(--pitch-black); color: white; font-family: 'Inter', sans-serif; }
        .glass { background: rgba(255,255,255,0.05); backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.1); }
        .text-glow-orange { text-shadow: 0 0 10px rgba(255, 102, 0, 0.5); }
    </style>
</head>
<body class="selection:bg-[#FF6600]">
    
    <nav class="fixed top-0 w-full z-50 glass border-b border-white/5 px-6 py-4 flex justify-between items-center">
        <div class="text-xl font-black italic tracking-tighter uppercase">
            <?php 
                $parts = explode('-', $appTitle);
                echo $parts[0];
                if(isset($parts[1])) echo " <span class='text-sharp-orange'>{$parts[1]}</span>";
            ?>
        </div>
        <div class="hidden md:flex gap-8">
            <a href="index.php" class="text-[10px] font-black uppercase tracking-widest hover:text-sharp-orange transition-colors">Grid</a>
            <a href="#about" class="text-[10px] font-black uppercase tracking-widest hover:text-sharp-orange transition-colors">Specs</a>
            <a href="admin.php" class="text-[10px] font-black uppercase tracking-widest text-glossy-purple">Security_Portal</a>
        </div>
    </nav>

    <main class="pt-32 px-6 max-w-7xl mx-auto space-y-32 pb-20">
        <!-- Hero Section -->
        <section class="flex flex-col lg:flex-row gap-16 items-center">
            <div class="flex-1 space-y-8 text-center lg:text-left">
                <h1 class="text-6xl md:text-8xl font-black italic uppercase tracking-tighter leading-[0.8] text-glow-orange">
                    <?php echo $appTitle; ?>
                </h1>
                <p class="text-zinc-400 text-lg md:text-xl font-medium max-w-2xl leading-relaxed">
                    <?php echo $heroSubtext; ?>
                </p>
            </div>
            
            <div class="w-full lg:w-[450px] grid grid-cols-2 gap-4">
                <?php foreach($hero_projects as $hp): ?>
                <a href="project.php?slug=<?php echo $hp['slug']; ?>" class="group block aspect-square glass rounded-xl overflow-hidden relative">
                    <?php echo render_media($hp['thumbnail_url'], "absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"); ?>
                    <div class="absolute inset-x-0 bottom-0 p-3 bg-black/60 backdrop-blur-sm">
                        <div class="text-[9px] font-black uppercase text-white truncate"><?php echo $hp['title']; ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Project Grid -->
        <section class="space-y-12">
            <div class="flex flex-wrap gap-4">
                <?php foreach(['all', 'web', 'app'] as $t): ?>
                <a href="?filter=<?php echo $t; ?>" class="text-[12px] font-black uppercase tracking-[0.2em] <?php echo $filter === $t ? 'text-sharp-orange' : 'text-zinc-500 hover:text-white'; ?>">
                    <?php echo $t; ?>
                </a>
                <?php endforeach; ?>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach($projects as $proj): ?>
                <a href="project.php?slug=<?php echo $proj['slug']; ?>" class="group glass rounded-2xl overflow-hidden p-3 border border-white/5 hover:border-sharp-orange/30 transition-all">
                    <div class="aspect-video rounded-xl overflow-hidden mb-4 relative">
                        <?php echo render_media($proj['thumbnail_url']); ?>
                    </div>
                    <h3 class="font-black text-lg uppercase truncate"><?php echo $proj['title']; ?></h3>
                    <div class="text-[10px] font-mono text-zinc-500 uppercase"><?php echo $proj['slug']; ?></div>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

</body>
</html>
