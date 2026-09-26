<?php

declare(strict_types=1);

header("Content-Type: application/json; charset=UTF-8");

if (session_status() !== PHP_SESSION_ACTIVE) {
    $isSecure = (
        (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off")
        || (int) ($_SERVER["SERVER_PORT"] ?? 0) === 443
    );

    session_name("leadership_academy_session");
    session_set_cookie_params([
        "lifetime" => 0,
        "path" => "/",
        "secure" => $isSecure,
        "httponly" => true,
        "samesite" => "Lax",
    ]);
    session_start();
}

function jsonResponse(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function requestData(): array
{
    $raw = file_get_contents("php://input");
    if ($raw === false || trim($raw) === "") {
        return $_POST ?: [];
    }

    $contentType = strtolower((string) ($_SERVER["CONTENT_TYPE"] ?? ""));
    if (str_contains($contentType, "application/json")) {
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    return $_POST ?: [];
}