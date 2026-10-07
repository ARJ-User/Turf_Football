<?php
// 1. Connection Configurations
define("DB_HOST", "localhost");
define("DB_NAME", "thearj");
define("DB_USER", "root");
define("DB_PASS", "");

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    error_log($e->getMessage());
    exit("Database connection failed.");
}

// 2. Function to Insert a New Turf Entry (Status defaults to 0)
function createTurfBooking($pdo, $username, $email) {
    try {
        $sql = "INSERT INTO football_turf (username, email) VALUES (:username, :email)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':username' => $username,
            ':email'    => $email
        ]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}

// 3. Function to Update Status (e.g., set status to 1 for "Approved")
function updateTurfStatus($pdo, $id, $newStatus) {
    try {
        $sql = "UPDATE football_turf SET status = :status WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':status' => $newStatus,
            ':id'     => $id
        ]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}
