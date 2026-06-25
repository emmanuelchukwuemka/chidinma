<?php
$db_url = getenv('DATABASE_URL');
if ($db_url) {
    $parts = parse_url($db_url);
    $host = $parts['host'];
    $port = isset($parts['port']) ? $parts['port'] : 5432;
    $dbname = ltrim($parts['path'], '/');
    $user = $parts['user'];
    $password = isset($parts['pass']) ? urldecode($parts['pass']) : '';
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
} else {
    $dsn = "pgsql:host=localhost;port=5432;dbname=outreach_monitor";
    $user = 'postgres';
    $password = '';
}

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
