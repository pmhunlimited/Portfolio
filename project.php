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
        
        /* Iframe Frame Controls */
        .frame-mobile { width: 375px; height: 667px; margin: 0 auto; border-radius: 40px; border: 12px solid #1a1a1a; }
        .frame-tablet { width: 768px; height: 1024px; margin: 0 auto; border-radius: 30px; border: 12px solid #1a1a1a; }
        .frame-desktop { width: 100%; height: 100%; border-radius: 0; border: none; }
        .iframe-container { transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); background: #000; overflow: hidden; position: relative; }
        .zoom-container { transition: transform 0.3s ease-out; transform-origin: top center; }
    </style>
</head>
<body class="selection:bg-orange-600 selection:text-white p-6 md:p-12 min-h-screen">
    <nav class="fixed top-0 left-0 w-full z-50 glass border-b border-white/5 px-8 py-4 flex justify-between items-center">
        <a href="index.php" class="text-[10px] font-black uppercase tracking-[0.4em] text-zinc-500 hover:text-white transition-all flex items-center gap-3 group">
            <span class="group-hover:-translate-x-1 transition-transform">←</span> Return to Grid
        </a>
        <div class="flex gap-4">
            <button onclick="togglePreview()" class="text-[9px] font-black uppercase tracking-widest text-orange-500 border border-orange-500/20 px-4 py-1.5 rounded-lg glass hover:bg-orange-500 hover:text-black transition-all">Project Projection</button>
        </div>
    </nav>

    <!-- Projection Layer (Iframe) -->
    <div id="projection-layer" class="hidden fixed inset-0 z-[60] glass flex flex-col p-4 md:p-8">
        <div class="flex flex-col md:flex-row justify-between items-center gap-6 mb-6">
            <div class="flex items-center gap-4">
                <button onclick="togglePreview()" class="p-3 glass rounded-xl text-zinc-500 hover:text-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
                <div>
                    <h2 class="text-xs font-black uppercase tracking-widest text-orange-500">Live Interface Projection</h2>
                    <p class="text-[8px] font-mono text-zinc-600 uppercase">Interactive Node Streaming // <?php echo strtoupper($project['slug']); ?></p>
                </div>
            </div>
            
            <!-- Controls -->
            <div class="flex items-center gap-2 glass p-1.5 rounded-2xl">
                <button onclick="setFrame('mobile')" class="frame-btn px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest text-zinc-500 hover:text-white transition-all" data-frame="mobile">Mobile</button>
                <button onclick="setFrame('tablet')" class="frame-btn px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest text-zinc-500 hover:text-white transition-all" data-frame="tablet">Tablet</button>
                <button onclick="setFrame('desktop', true)" class="frame-btn px-4 py-2 rounded-xl bg-orange-600 text-black text-[9px] font-black uppercase tracking-widest shadow-lg transition-all" data-frame="desktop">Desktop</button>
                <div class="w-px h-6 bg-white/5 mx-2"></div>
                <div class="flex items-center gap-3 px-4">
                    <span class="text-[9px] font-black uppercase text-zinc-600">Zoom</span>
                    <input type="range" min="0.5" max="1.5" step="0.1" value="1" oninput="setZoom(this.value)" class="w-24 accent-orange-500">
                </div>
            </div>

            <a href="<?php echo $project['site_url']; ?>" target="_blank" class="px-6 py-3 bg-white text-black text-[9px] font-black uppercase tracking-[0.2em] rounded-xl hover:bg-orange-500 transition-all flex items-center gap-3">
                Full External Access
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
            </a>
        </div>

        <div class="flex-grow flex items-center justify-center overflow-auto p-4 md:p-12">
            <div id="iframe-wrap" class="iframe-container frame-desktop shadow-[0_50px_100px_rgba(0,0,0,0.8)]">
                <div id="zoom-wrap" class="zoom-container w-full h-full">
                    <iframe src="<?php echo $project['site_url']; ?>" class="w-full h-full border-0 bg-white"></iframe>
                </div>
            </div>
        </div>
    </div>

    <main class="max-w-7xl mx-auto pt-24 grid grid-cols-1 xl:grid-cols-2 gap-20">
        <!-- Left Column: Vision Cluster -->
        <div class="space-y-12">
            <div class="aspect-video glass rounded-[32px] overflow-hidden relative border border-white/10 shadow-[0_0_100px_rgba(255,102,0,0.05)] cursor-pointer group" onclick="togglePreview()">
                <?php echo render_media($project['thumbnail_url'], "w-full h-full object-cover grayscale-[0.3] group-hover:grayscale-0 transition-all duration-700"); ?>
                <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all scale-90 group-hover:scale-100">
                    <div class="px-6 py-3 glass rounded-2xl text-[10px] font-black uppercase tracking-[0.3em] border-orange-500/50 text-orange-500">Project Projection Init</div>
                </div>
            </div>

            <!-- Analysis HUD -->
            <div class="grid grid-cols-3 gap-4">
                <div class="glass p-6 rounded-2xl border-orange-500/10 hover:border-orange-500/30 transition-all">
                    <div class="text-[8px] font-black uppercase tracking-[0.3em] text-orange-500 mb-2">Performance</div>
                    <div class="text-3xl font-black italic tracking-tighter"><?php echo $project['speed'] ?? 98; ?><span class="text-[10px] text-zinc-600 ml-1">GTX</span></div>
                </div>
                <div class="glass p-6 rounded-2xl border-purple-500/10 hover:border-purple-500/30 transition-all">
                    <div class="text-[8px] font-black uppercase tracking-[0.3em] text-purple-500 mb-2">Security</div>
                    <div class="text-3xl font-black italic tracking-tighter">100<span class="text-[10px] text-zinc-600 ml-1">LYR</span></div>
                </div>
                <div class="glass p-6 rounded-2xl border-blue-500/10 hover:border-blue-500/30 transition-all">
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
                    Master Protocol Active
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

            <!-- Multi-Tier Interface Access Protocol -->
            <div class="space-y-6 pt-12 border-t border-white/5">
                <h3 class="text-xs font-black uppercase tracking-[0.4em] text-orange-500">Tiered Interface Access</h3>
                
                <div class="grid grid-cols-1 gap-4">
                    <?php 
                    $tiers = [
                        ['id' => '0', 'name' => 'Super Admin', 'prefix' => 'lvl0_', 'color' => 'orange'],
                        ['id' => '1', 'name' => 'Restricted Admin', 'prefix' => 'lvl1_', 'color' => 'zinc'],
                        ['id' => '2', 'name' => 'Standard User', 'prefix' => 'lvl2_', 'color' => 'blue']
                    ];
                    foreach($tiers as $tier): 
                        $prefix = $tier['prefix'];
                        if(empty($project[$prefix.'login_url']) && empty($project[$prefix.'direct_url'])) continue;
                    ?>
                    <div class="glass p-6 rounded-2xl space-y-4 border-<?php echo $tier['color']; ?>-500/10 hover:border-<?php echo $tier['color']; ?>-500/30 transition-all">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-3">
                                <div class="w-1.5 h-6 bg-<?php echo $tier['color']; ?>-600 rounded-full"></div>
                                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-<?php echo $tier['color']; ?>-500">Access Level <?php echo $tier['id']; ?>: <?php echo $tier['name']; ?></span>
                            </div>
                            <?php if(!empty($project[$prefix.'direct_url'])): ?>
                            <a href="<?php echo $project[$prefix.'direct_url']; ?>" target="_blank" class="px-4 py-1.5 bg-<?php echo $tier['color']; ?>-600 text-black text-[9px] font-black uppercase tracking-widest rounded-lg hover:brightness-110">One-Click Bypass</a>
                            <?php elseif(!empty($project[$prefix.'user']) && !empty($project[$prefix.'pass']) && !empty($project[$prefix.'login_url'])): ?>
                            <button onclick="attemptLogin('<?php echo $project[$prefix.'login_url']; ?>', '<?php echo $project[$prefix.'user']; ?>', '<?php echo $project[$prefix.'pass']; ?>')" class="px-4 py-1.5 bg-<?php echo $tier['color']; ?>-600 text-black text-[9px] font-black uppercase tracking-widest rounded-lg hover:brightness-110">Auto Deploy Access</button>
                            <?php endif; ?>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <?php if(!empty($project[$prefix.'user'])): ?>
                            <div class="glass p-3 rounded-xl">
                                <span class="block text-[8px] uppercase text-zinc-600 font-bold mb-1">Identity</span>
                                <div class="flex justify-between items-center font-mono text-[10px]">
                                    <span class="truncate"><?php echo $project[$prefix.'user']; ?></span>
                                    <button onclick="copyToClipboard('<?php echo $project[$prefix.'user']; ?>')" class="text-zinc-700 hover:text-white transition-colors uppercase text-[8px] font-bold">Copy</button>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if(!empty($project[$prefix.'pass'])): ?>
                            <div class="glass p-3 rounded-xl">
                                <span class="block text-[8px] uppercase text-zinc-600 font-bold mb-1">Passphrase</span>
                                <div class="flex justify-between items-center font-mono text-[10px]">
                                    <span class="truncate">••••••••</span>
                                    <button onclick="copyToClipboard('<?php echo $project[$prefix.'pass']; ?>')" class="text-zinc-700 hover:text-white transition-colors uppercase text-[8px] font-bold">Copy</button>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if(!empty($project[$prefix.'note'])): ?>
                        <div class="p-3 bg-white/[0.02] border border-white/5 rounded-xl">
                            <span class="block text-[8px] uppercase text-zinc-600 font-bold mb-1 italic">Legacy Payload Note</span>
                            <div class="text-[10px] font-mono text-zinc-400"><?php echo $project[$prefix.'note']; ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if(!empty($project[$prefix.'login_url']) && empty($project[$prefix.'direct_url'])): ?>
                        <a href="<?php echo $project[$prefix.'login_url']; ?>" target="_blank" class="block w-full text-center py-2 border border-white/5 hover:border-white/10 rounded-xl text-[9px] font-black uppercase tracking-[0.2em] text-zinc-500 hover:text-white transition-all">Manual Login Gateway</a>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="pt-12 border-t border-white/5 flex flex-col md:flex-row gap-10 items-center">
                <script>
                    function copyToClipboard(text) {
                        navigator.clipboard.writeText(text).then(() => {
                            alert('Protocol Data Copied: ' + text);
                        });
                    }

                    function attemptLogin(url, user, pass) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = url;
                        form.target = '_blank';

                        const userInp = document.createElement('input');
                        userInp.name = 'username'; // Common field names
                        userInp.value = user;
                        form.appendChild(userInp);

                        const passInp = document.createElement('input');
                        passInp.name = 'password';
                        passInp.value = pass;
                        form.appendChild(passInp);

                        // Hidden fields for standard CMS logins (trying to be generic)
                        const submitInp = document.createElement('input');
                        submitInp.type = 'submit';
                        form.appendChild(submitInp);

                        document.body.appendChild(form);
                        form.submit();
                        document.body.removeChild(form);
                        alert('Autonomous Login Triggered. Accessing Destination Node.');
                    }

                    function togglePreview() {
                        const layer = document.getElementById('projection-layer');
                        layer.classList.toggle('hidden');
                        if(!layer.classList.contains('hidden')) {
                            document.body.style.overflow = 'hidden';
                        } else {
                            document.body.style.overflow = 'auto';
                        }
                    }

                    function setFrame(type, reset = false) {
                        const wrap = document.getElementById('iframe-wrap');
                        wrap.classList.remove('frame-mobile', 'frame-tablet', 'frame-desktop');
                        wrap.classList.add('frame-' + type);
                        
                        document.querySelectorAll('.frame-btn').forEach(btn => {
                            btn.classList.remove('bg-orange-600', 'text-black', 'shadow-lg');
                            btn.classList.add('text-zinc-500');
                            if(btn.dataset.frame === type) {
                                btn.classList.add('bg-orange-600', 'text-black', 'shadow-lg');
                                btn.classList.remove('text-zinc-500');
                            }
                        });

                        if(reset) setZoom(1);
                    }

                    function setZoom(val) {
                        document.getElementById('zoom-wrap').style.transform = `scale(${val})`;
                    }
                </script>
                <a href="<?php echo $project['site_url']; ?>" target="_blank" 
                   class="group relative w-full md:w-auto px-12 py-5 bg-orange-600 text-black font-black uppercase tracking-[0.3em] text-xs rounded-2xl transition-all hover:scale-105 active:scale-95 shadow-[0_0_50px_rgba(234,88,12,0.2)]">
                    <span class="relative z-10">Launch Live Interface</span>
                    <div class="absolute inset-0 bg-white/20 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-500 skew-x-12"></div>
                </a>

                <div class="space-y-1">
                    <div class="text-[9px] font-black uppercase text-zinc-600 tracking-widest leading-none">Node ID Authorization</div>
                    <div class="text-xs font-mono text-zinc-300 font-bold"><?php echo strtoupper($project['slug']); ?>-V1.0</div>
                </div>
            </div>
        </div>
    </main>

    <!-- Visual Gallery Matrix -->
    <section class="max-w-7xl mx-auto py-32 space-y-12">
        <h2 class="text-xs font-black uppercase tracking-[0.5em] text-zinc-700 text-center">Visual Identity Nodes</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
            <?php foreach($gallery as $img): ?>
            <div class="group aspect-square glass rounded-3xl overflow-hidden relative border border-white/5 hover:border-orange-500/30 transition-all">
                <?php echo render_media($img['media_url'], "w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"); ?>
                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <span class="text-[10px] font-black uppercase tracking-widest text-white border-b-2 border-orange-600">View Media</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</body>
</html>
