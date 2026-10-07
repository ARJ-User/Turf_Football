<?php
header('Content-Type: application/json');

$host = '127.0.0.1';
$db = 'thearj';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (\PDOException $e) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$date = $_GET['date'] ?? '';

if (empty($date)) {
    echo json_encode([]);
    exit;
}

// Fetch any reserved row for this specific date string
$stmt = $pdo->prepare("SELECT slot_start, slot_end FROM football_turf WHERE booking_date = ?");
$stmt->execute([$date]);
$bookedRows = $stmt->fetchAll();

$bookedSlots = [];
foreach ($bookedRows as $row) {
    $start = substr($row['slot_start'], 0, 5);
    $end = substr($row['slot_end'], 0, 5);
    $bookedSlots[] = $start . '-' . $end;
}

echo json_encode($bookedSlots);
exit;
