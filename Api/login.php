<?php

declare(strict_types=1);

require_once __DIR__ . "/bootstrap.php";
require_once __DIR__ . "/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    jsonResponse([
        "success" => false,
        "message" => "Method not allowed."
    ], 405);
}

$data = requestData();

$email = trim((string) ($data["email"] ?? ""));
$password = (string) ($data["password"] ?? "");

if ($email === "" || $password === "") {
    jsonResponse([
        "success" => false,
        "message" => "Please enter your email and password."
    ], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse([
        "success" => false,
        "message" => "Please enter a valid email address."
    ], 400);
}

try {
    $stmt = $pdo->prepare(
        "SELECT id, full_name, email, phone, password_hash
         FROM users
         WHERE email = ?
         LIMIT 1"
    );
    $stmt->execute([strtolower($email)]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse([
            "success" => false,
            "error" => "account_not_found",
            "message" => "No registered account was found with this email."
        ], 404);
    }

    if (!password_verify($password, $user["password_hash"])) {
        jsonResponse([
            "success" => false,
            "error" => "incorrect_password",
            "message" => "Incorrect password. Please try again."
        ], 401);
    }

    session_regenerate_id(true);
    $_SESSION["user_id"] = (int) $user["id"];
    $_SESSION["user_name"] = $user["full_name"];
    $_SESSION["user_email"] = $user["email"];

    jsonResponse([
        "success" => true,
        "message" => "Login successful.",
        "user" => [
            "id" => (int) $user["id"],
            "name" => $user["full_name"],
            "email" => $user["email"],
            "phone" => $user["phone"]
        ]
    ]);
} catch (Throwable $error) {
    error_log("Leadership Academy login error: " . $error->getMessage());
    jsonResponse([
        "success" => false,
        "message" => "Unable to sign in right now. Please try again."
    ], 500);
}
