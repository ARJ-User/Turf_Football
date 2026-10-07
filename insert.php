<?php
// Secure PDO connection to the correct database: "thearj"
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

// Collect POST data
$full_name = $_POST['full_name'] ?? '';
$phone     = $_POST['phone'] ?? '';
$date      = $_POST['date'] ?? '';
$time      = $_POST['time'] ?? '';

// Combine details into username/email since columns are limited
$username_data = $full_name . " (Phone: " . $phone . ")";
$email_data    = "Booking: " . $date . " @ " . $time;

try {
    // Secure prepared statement to prevent SQL injection
    $sql = "INSERT INTO football_turf (username, email, status) VALUES (:username, :email, :status)";
    $stmt = $pdo->prepare($sql);
    
    // Executes with status default '0' as per your phpMyAdmin
    $stmt->execute([
        ':username' => $username_data,
        ':email'    => $email_data,
        ':status'   => 0 
    ]);

    echo "<h2>Booking Successful!</h2>";

} catch (PDOException $e) {
    error_log($e->getMessage());
    echo "<h2>An error occurred. Please try again later.</h2>";
}
?>
