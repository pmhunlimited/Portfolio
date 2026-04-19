<?php
// db.php - Database connection
$host = 'localhost';
$db   = 'cyber_pulse_portfolio';
$user = 'root'; // Change as needed
$pass = '';     // Change as needed
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     
     // HTTP Security Headers
     header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
     header("Content-Security-Policy: default-src 'self' https: data: 'unsafe-inline' 'unsafe-eval'; img-src 'self' https: data: blob:; font-src 'self' https: data:;");
     header("X-Content-Type-Options: nosniff");
     header("X-Frame-Options: SAMEORIGIN");
     header("X-XSS-Protection: 1; mode=block");

     // Check if settings table exists to confirm installation
     $check = $pdo->query("SHOW TABLES LIKE 'settings'");
     if ($check->rowCount() == 0 && basename($_SERVER['PHP_SELF']) !== 'install.php') {
         header("Location: install.php");
         exit;
     }
} catch (\PDOException $e) {
     if (basename($_SERVER['PHP_SELF']) !== 'install.php') {
         header("Location: install.php");
         exit;
     }
}
?>
