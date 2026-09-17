<?php

declare(strict_types=1);

require_once __DIR__ . "/bootstrap.php";
require_once __DIR__ . "/db.php";


/*
|--------------------------------------------------------------------------
| Only allow POST requests
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    jsonResponse([
        "success" => false,
        "message" => "Method not allowed."
    ], 405);
}


/*
|--------------------------------------------------------------------------
| Get JSON data from JavaScript
|--------------------------------------------------------------------------
*/

$data = requestData();


/*
|--------------------------------------------------------------------------
| Check JSON
|--------------------------------------------------------------------------
*/

if (!is_array($data)) {

    jsonResponse([
        "success" => false,
        "message" => "Invalid request data."
    ], 400);
}


/*
|--------------------------------------------------------------------------
| Get form values
|--------------------------------------------------------------------------
*/

$name = trim((string) ($data["name"] ?? $data["full_name"] ?? ""));
$email = strtolower(trim((string) ($data["email"] ?? "")));
$phone = trim((string) ($data["phone"] ?? ""));
$password = (string) ($data["password"] ?? "");


/*
|--------------------------------------------------------------------------
| Required fields
|--------------------------------------------------------------------------
*/

if (
    $name === "" ||
    $email === "" ||
    $phone === "" ||
    $password === ""
) {

    jsonResponse([
        "success" => false,
        "message" => "Please complete all fields."
    ], 400);
}


/*
|--------------------------------------------------------------------------
| Validate name
|--------------------------------------------------------------------------
*/

if (strlen($name) < 2) {

    jsonResponse([
        "success" => false,
        "message" => "Please enter your full name."
    ], 400);
}


/*
|--------------------------------------------------------------------------
| Validate email
|--------------------------------------------------------------------------
*/

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    jsonResponse([
        "success" => false,
        "message" => "Please enter a valid email address."
    ], 400);
}


/*
|--------------------------------------------------------------------------
| Validate phone
|--------------------------------------------------------------------------
*/

if (strlen($phone) < 7) {

    jsonResponse([
        "success" => false,
        "message" => "Please enter a valid phone number."
    ], 400);
}


/*
|--------------------------------------------------------------------------
| Validate password
|--------------------------------------------------------------------------
*/

if (strlen($password) < 8) {

    jsonResponse([
        "success" => false,
        "message" => "Password must be at least 8 characters."
    ], 400);
}


/*
|--------------------------------------------------------------------------
| Check if email already exists
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare(
        "SELECT id
         FROM users
         WHERE email = ?
         LIMIT 1"
    );

    $stmt->execute([$email]);

    $existingUser = $stmt->fetch();


    if ($existingUser) {

        jsonResponse([
            "success" => false,
            "error" => "email_exists",
            "message" => "An account with this email already exists."
        ], 409);
    }


    /*
    |--------------------------------------------------------------------------
    | Hash password
    |--------------------------------------------------------------------------
    */

    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );


    /*
    |--------------------------------------------------------------------------
    | Insert new account
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare(
        "INSERT INTO users
        (full_name, email, phone, password_hash)
        VALUES (?, ?, ?, ?)"
    );

    $stmt->execute([
        $name,
        $email,
        $phone,
        $passwordHash
    ]);


    /*
    |--------------------------------------------------------------------------
    | Get newly created user ID
    |--------------------------------------------------------------------------
    */

    $userId = $pdo->lastInsertId();


    /*
    |--------------------------------------------------------------------------
    | Registration successful
    |--------------------------------------------------------------------------
    */

    jsonResponse([
        "success" => true,
        "message" => "Registered successfully.",
        "user" => [
            "id" => $userId,
            "name" => $name,
            "email" => $email,
            "phone" => $phone
        ]
    ], 201);


} catch (PDOException $e) {

    /*
    |--------------------------------------------------------------------------
    | Database error
    |--------------------------------------------------------------------------
    */

    if ($e->getCode() === "23000") {
        jsonResponse([
            "success" => false,
            "error" => "email_exists",
            "message" => "An account with this email already exists."
        ], 409);
    }

    error_log("Leadership Academy registration error: " . $e->getMessage());
    jsonResponse([
        "success" => false,
        "message" => "Unable to create your account. Please try again."
    ], 500);
}
