<?php
/**
 * install.php - Cyber-Pulse System Installer
 */
session_start();
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

// PHP Version & Extensions Check
$php_version = phpversion();
$pdo_enabled = extension_loaded('pdo_mysql');
$json_enabled = extension_loaded('json');
$requirements_met = version_compare($php_version, '7.4', '>=') && $pdo_enabled && $json_enabled;

if ($step === 2 && isset($_POST['db_config'])) {
    $host = $_POST['host'];
    $db   = $_POST['db'];
    $user = $_POST['user'];
    $pass = $_POST['pass'];

    try {
        $pdo = new PDO("mysql:host=$host", $user, $pass);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        $pdo->exec("USE `$db`;");
        
        // Import Schema
        $sql = file_get_contents('database.sql');
        // Remove 'CREATE DATABASE' lines from sql to avoid conflict if already exists
        $sql = preg_replace('/CREATE DATABASE IF NOT EXISTS.*;/i', '', $sql);
        $sql = preg_replace('/USE .*/i', '', $sql);
        
        $pdo->exec($sql);

        // Update db.php
        $db_content = "<?php
// db.php - Database connection
\$host = '$host';
\$db   = '$db';
\$user = '$user';
\$pass = '$pass';
\$charset = 'utf8mb4';

\$dsn = \"mysql:host=\$host;dbname=\$db;charset=\$charset\";
\$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     \$pdo = new PDO(\$dsn, \$user, \$pass, \$options);
     \$check = \$pdo->query(\"SHOW TABLES LIKE 'settings'\");
     if (\$check->rowCount() == 0 && basename(\$_SERVER['PHP_SELF']) !== 'install.php') {
         header(\"Location: install.php\");
         exit;
     }
} catch (\\PDOException \$e) {
     if (basename(\$_SERVER['PHP_SELF']) !== 'install.php') {
         header(\"Location: install.php\");
         exit;
     }
}
?>";
        file_put_contents('db.php', $db_content);
        
        header("Location: install.php?step=3");
        exit;
    } catch (PDOException $e) {
        $error = "Connection Failed: " . $e->getMessage();
    }
}

if ($step === 3 && isset($_POST['admin_config'])) {
    require_once 'db.php';
    $email = $_POST['email'];
    $pass  = $_POST['pass'];
    
    // Update settings table
    $stmt = $pdo->prepare("REPLACE INTO settings (setting_key, setting_value) VALUES (?, ?), (?, ?), (?, ?)");
    $stmt->execute([
        'admin_username', $email,
        'admin_password', $pass,
        'authorized_email', $email
    ]);
    
    header("Location: install.php?step=4");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cyber-Pulse System | Installer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { background: #000; color: white; font-family: 'Inter', sans-serif; }
        .glass { background: rgba(255,255,255,0.03); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.08); }
        .text-glow { text-shadow: 0 0 10px rgba(234, 88, 12, 0.5); }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-6">
    <div class="max-w-xl w-full glass rounded-[32px] p-10 space-y-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-orange-600/10 blur-3xl rounded-full translate-x-1/2 -translate-y-1/2"></div>
        
        <header class="text-center space-y-2">
            <h1 class="text-2xl font-black italic tracking-tighter text-orange-500 uppercase">Cyber Pulse Installer</h1>
            <div class="flex justify-center gap-2">
                <?php for($i=1; $i<=4; $i++): ?>
                <div class="h-1 w-8 rounded-full <?php echo $step >= $i ? 'bg-orange-500' : 'bg-white/10'; ?>"></div>
                <?php endfor; ?>
            </div>
        </header>

        <?php if($step === 1): ?>
            <div class="space-y-6">
                <div class="space-y-4">
                    <h2 class="text-sm font-black uppercase tracking-[0.3em] text-zinc-400">Environment Health</h2>
                    <ul class="space-y-3 font-mono text-[11px] uppercase">
                        <li class="flex justify-between items-center p-3 glass rounded-xl">
                            <span>PHP Version (>= 7.4)</span>
                            <span class="<?php echo version_compare($php_version, '7.4', '>=') ? 'text-green-500' : 'text-red-500'; ?>"><?php echo $php_version; ?></span>
                        </li>
                        <li class="flex justify-between items-center p-3 glass rounded-xl">
                            <span>PDO MySQL Extension</span>
                            <span class="<?php echo $pdo_enabled ? 'text-green-500' : 'text-red-500'; ?>"><?php echo $pdo_enabled ? 'READY' : 'MISSING'; ?></span>
                        </li>
                        <li class="flex justify-between items-center p-3 glass rounded-xl">
                            <span>JSON Extension</span>
                            <span class="<?php echo $json_enabled ? 'text-green-500' : 'text-red-500'; ?>"><?php echo $json_enabled ? 'READY' : 'MISSING'; ?></span>
                        </li>
                    </ul>
                </div>
                <?php if($requirements_met): ?>
                    <a href="?step=2" class="block w-full py-4 bg-white text-black text-center font-black uppercase italic tracking-widest rounded-xl hover:bg-orange-500 transition-all">Start Calibration</a>
                <?php else: ?>
                    <p class="text-[10px] text-red-500 text-center font-bold uppercase tracking-widest">System requirements not met. Please configure server.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if($step === 2): ?>
            <form method="POST" class="space-y-6">
                <h2 class="text-sm font-black uppercase tracking-[0.3em] text-zinc-400">Database Link</h2>
                <?php if(isset($error)): ?>
                    <div class="p-4 bg-red-500/10 border border-red-500/20 text-red-500 text-[10px] font-bold rounded-xl"><?php echo $error; ?></div>
                <?php endif; ?>
                <div class="grid grid-cols-1 gap-4">
                    <input type="text" name="host" value="localhost" placeholder="dbHost" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500" required>
                    <input type="text" name="db" placeholder="databaseName" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500" required>
                    <input type="text" name="user" placeholder="username" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500" required>
                    <input type="password" name="pass" placeholder="password" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500">
                </div>
                <button type="submit" name="db_config" class="w-full py-4 bg-white text-black font-black uppercase italic tracking-widest rounded-xl hover:bg-orange-500 transition-all">Synchronize Data</button>
            </form>
        <?php endif; ?>

        <?php if($step === 3): ?>
            <form method="POST" class="space-y-6">
                <h2 class="text-sm font-black uppercase tracking-[0.3em] text-zinc-400">Admin Authorization</h2>
                <div class="grid grid-cols-1 gap-4">
                    <input type="email" name="email" placeholder="adminEmail" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500" required>
                    <input type="password" name="pass" placeholder="masterPassword" class="w-full bg-black/40 border border-white/10 p-4 rounded-xl outline-none focus:border-orange-500" required>
                </div>
                <button type="submit" name="admin_config" class="w-full py-4 bg-white text-black font-black uppercase italic tracking-widest rounded-xl hover:bg-orange-500 transition-all">Initialize Identity</button>
            </form>
        <?php endif; ?>

        <?php if($step === 4): ?>
            <div class="text-center space-y-6">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-orange-600/20 border border-orange-500/30 text-orange-500">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div class="space-y-2">
                    <h2 class="text-xl font-black italic uppercase italic">System Calibrated!</h2>
                    <p class="text-zinc-500 text-[11px] leading-loose max-w-sm mx-auto uppercase font-medium">
                        Deployment complete. You are now the master of the Cyber-Pulse grid. Proceed to the admin panel to populate your node cluster.
                    </p>
                </div>
                <div class="p-6 glass rounded-2xl text-left space-y-4">
                    <h3 class="text-[9px] font-black uppercase tracking-widest text-orange-500">Master Protocol:</h3>
                    <ul class="text-[10px] space-y-2 text-zinc-400">
                        <li>• Access Admin via <span class="text-white font-mono">/admin.php</span></li>
                        <li>• Configure AI Pulse in <span class="text-white">API Manager</span></li>
                        <li>• Inject at least 4 nodes for optimal Hero display</li>
                    </ul>
                </div>
                <a href="admin.php" class="block w-full py-4 bg-orange-600 text-white font-black uppercase italic tracking-widest rounded-xl hover:bg-orange-500 transition-all">Access Portal</a>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>
