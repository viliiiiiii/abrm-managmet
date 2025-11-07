<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/../app/Bootstrap.php';
$boot = new \App\Bootstrap(); // will init config + DB

use App\Model\DB;

try {
    $pdo = DB::core(); // your DB factory that returns PDO for core_db
    $stmt = $pdo->prepare('SELECT id, email, password FROM users WHERE email = ? LIMIT 1');
    $stmt->execute(['elvir@abrm.com']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    header('Content-Type: text/plain');
    if (!$row) { echo "NO_USER"; exit; }
    echo "USER_FOUND\n";
    echo "email=".$row['email']."\n";
    echo "hash_prefix=".substr((string)$row['password'], 0, 15)."...\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo "DB_ERROR: ".$e->getMessage();
}
