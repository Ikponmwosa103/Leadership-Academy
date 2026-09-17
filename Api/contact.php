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
$name = trim((string) ($data["name"] ?? ""));
$email = strtolower(trim((string) ($data["email"] ?? "")));
$subject = trim((string) ($data["subject"] ?? "General Inquiry"));
$message = trim((string) ($data["message"] ?? ""));

if ($name === "" || $email === "" || $message === "") {
    jsonResponse([
        "success" => false,
        "message" => "Please fill in your name, email, and message."
    ], 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse([
        "success" => false,
        "message" => "Please enter a valid email address."
    ], 422);
}

if (strlen($name) > 120 || strlen($subject) > 80 || strlen($message) > 10000) {
    jsonResponse([
        "success" => false,
        "message" => "One of your fields is too long."
    ], 422);
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO contact_messages (name, email, subject, message)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$name, $email, $subject ?: "General Inquiry", $message]);

    /*
     * Email delivery is optional. The message is already safely stored in the
     * database when SMTP is not configured, so the public form does not break
     * just because PHPMailer or Mailtrap has not been installed yet.
     */
    $autoloadPath = dirname(__DIR__) . "/vendor/autoload.php";
    $mailConfigured = getenv("MAILTRAP_HOST")
        && getenv("MAILTRAP_USERNAME")
        && getenv("MAILTRAP_PASSWORD")
        && getenv("MAILTRAP_FROM_EMAIL")
        && getenv("MAILTRAP_TO_EMAIL");

    if (is_file($autoloadPath) && $mailConfigured) {
        require_once $autoloadPath;

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = trim((string) getenv("MAILTRAP_HOST"));
        $mail->SMTPAuth = true;
        $mail->Username = trim((string) getenv("MAILTRAP_USERNAME"));
        $mail->Password = (string) getenv("MAILTRAP_PASSWORD");
        $mail->Port = (int) (getenv("MAILTRAP_PORT") ?: 2525);
        $mail->Timeout = 5;

        $encryption = strtolower(trim((string) (getenv("MAILTRAP_ENCRYPTION") ?: "tls")));
        if ($encryption === "tls") {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($encryption === "ssl") {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($encryption !== "none") {
            throw new RuntimeException("MAILTRAP_ENCRYPTION must be tls, ssl, or none.");
        }

        $fromEmail = trim((string) getenv("MAILTRAP_FROM_EMAIL"));
        $toEmail = trim((string) getenv("MAILTRAP_TO_EMAIL"));
        $mail->setFrom($fromEmail, "Leadership Academy");
        $mail->addAddress($toEmail, "Leadership Academy");
        $mail->addReplyTo($email, $name);
        $mail->isHTML(true);
        $mail->Subject = "Inquiry from {$name} - Leadership Academy";
        $mail->Body = sprintf(
            '<h2>New Leadership Academy contact message</h2>
             <p><strong>Name:</strong> %s</p>
             <p><strong>Email:</strong> %s</p>
             <p><strong>Subject:</strong> %s</p>
             <p><strong>Message:</strong><br>%s</p>',
            htmlspecialchars($name, ENT_QUOTES, "UTF-8"),
            htmlspecialchars($email, ENT_QUOTES, "UTF-8"),
            htmlspecialchars($subject, ENT_QUOTES, "UTF-8"),
            nl2br(htmlspecialchars($message, ENT_QUOTES, "UTF-8"))
        );
        $mail->AltBody = "Name: {$name}\nEmail: {$email}\nSubject: {$subject}\n\n{$message}";
        $mail->send();
    }

    jsonResponse([
        "success" => true,
        "message" => "Your message has been received. We will be in touch soon."
    ]);
} catch (Throwable $error) {
    error_log("Leadership Academy contact error: " . $error->getMessage());
    jsonResponse([
        "success" => false,
        "message" => "We could not save your message. Please try again later."
    ], 500);
}