<?php

declare(strict_types=1);

/*
 * Works with Railway / cloud variables OR falls back to XAMPP defaults.
 */
$mysqlHost = trim((string) (getenv("MYSQLHOST") ?: getenv("DB_HOST") ?: ""));

try {
    if ($mysqlHost !== "") {
        $host = $mysqlHost;
        $port = (int) (getenv("MYSQLPORT") ?: getenv("DB_PORT") ?: 3306);
        $database = (string) (getenv("MYSQLDATABASE") ?: getenv("DB_NAME") ?: "");
        $username = (string) (getenv("MYSQLUSER") ?: getenv("DB_USER") ?: "");
        $password = (string) (getenv("MYSQLPASSWORD") ?: getenv("DB_PASSWORD") ?: "");

        if ($database === "" || $username === "") {
            throw new RuntimeException("Database variables are incomplete.");
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
    } else {
        // ---------- XAMPP FALLBACK (edit these if needed) ----------
        $host     = "127.0.0.1";
        $port     = 3306;
        $database = "leadership_academy";
        $username = "root";
        $password = "";          // XAMPP default is empty
        // -----------------------------------------------------------

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
    }

    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    if (filter_var(getenv("DB_AUTO_MIGRATE") ?: "true", FILTER_VALIDATE_BOOLEAN)) {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS users (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                full_name VARCHAR(120) NOT NULL,
                email VARCHAR(190) NOT NULL,
                phone VARCHAR(40) NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY users_email_unique (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS contact_messages (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(120) NOT NULL,
                email VARCHAR(190) NOT NULL,
                subject VARCHAR(80) NOT NULL,
                message TEXT NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY contact_messages_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }
} catch (Throwable $error) {
    error_log("Leadership Academy database error: " . $error->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed. Check the database variables and schema."
    ]);
    exit;
}