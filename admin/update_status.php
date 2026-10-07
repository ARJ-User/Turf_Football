<?php
$host = '127.0.0.1';
$db = 'thearj'; 
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

if ($id && $action) {
    $id = intval($id); // Clean parameter injection normalization casting

    if ($action === 'confirm') {
        // FIXED: Using array syntax with variable parameters securely
        $stmt = $pdo->prepare("UPDATE football_turf SET status = 1 WHERE id = ?");
        $stmt->execute([$id]);
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM football_turf WHERE id = ?");
        $stmt->execute([$id]);
    }
}

// Redirect back to main dashboard smoothly
header("Location: dashboard.php");
exit;
?>