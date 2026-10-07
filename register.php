<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Registration</title>
</head>
<body>

    <h2>HTML Forms</h2>

    <form action="" method="post">
        <label for="username">Username:</label><br>
        <input type="text" id="username" name="name" required><br>

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br><br>

        <label for="pass">Password:</label><br>
        <input type="password" id="pass" name="pass" required><br><br>

        <input type="hidden" name="action" value="reg">
        <input type="submit" value="Submit">
    </form>

</body>
</html>

<?php
// Include your database configuration file
include 'config.php';

// Only process if the form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'reg') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['pass'] ?? '');

        // Validation: Prevent blank entries for NOT NULL fields
        if ($name === "" || $email === "" || $password === "") {
            echo "<p style='color:red;'>All fields are required!</p>";
            exit;
        }

        // Securely hash the password before inserting into the database
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        try {
            // Target the correct columns matching your updated phpMyAdmin structure
            $query = "INSERT INTO football_turf (username, email, password, status) 
                      VALUES (:username, :email, :password, :status)";
            
            // Prepare statement using the PDO connection variable ($pdo) from your config.php
            $stmt = $pdo->prepare($query);
            
            $isDone = $stmt->execute([
                ':username' => $name,
                ':email'    => $email,
                ':password' => $hashedPassword,
                ':status'   => 0
            ]);

            if ($isDone) {
                echo "<p style='color:green;'>Registration successful!</p>";
            }

        } catch (PDOException $e) {
            // Log errors internally; hide technical details from public users
            error_log($e->getMessage());
            echo "<p style='color:red;'>An error occurred. Registration failed.</p>";
        }
    }
}
?>
