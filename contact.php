<?php
// ===== CONTACT : envoi du mail avec PHPMailer =====

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

$config = require __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

// Petite fonction pour renvoyer une réponse au JavaScript
function respond(bool $success, string $message, int $status = 200): void
{
    http_response_code($status);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// 1. On accepte seulement les envois du formulaire (méthode POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed.', 405);
}

// 2. Anti-spam : si le champ caché est rempli, c'est un robot
if (!empty($_POST['website'])) {
    respond(true, 'Thank you! Your message has been sent.');
}

// 3. Récupération et nettoyage des données
$name    = trim(str_replace(["\r", "\n"], ' ', $_POST['name'] ?? ''));
$email   = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

// 4. Vérifications
if ($name === '' || $email === '' || $message === '') {
    respond(false, 'Please fill in all the fields.', 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Please enter a valid email address.', 400);
}

if (mb_strlen($name) > 100 || mb_strlen($message) > 3000) {
    respond(false, 'Your name or message is too long.', 400);
}

// 5. Envoi du mail
$mail = new PHPMailer(true);

try {
    // Connexion au serveur SMTP de Gmail
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['smtp_user'];
    $mail->Password   = $config['smtp_pass'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    // Expéditeur, destinataire, et "répondre à" = le visiteur
    $mail->setFrom($config['smtp_user'], 'Web2Risk Agency - Website');
    $mail->addAddress($config['to_email']);
    $mail->addReplyTo($email, $name);

    // Contenu du mail
    $safeName    = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeEmail   = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $safeMessage = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));

    $mail->isHTML(true);
    $mail->Subject = 'New contact message from ' . $name;
    $mail->Body    = "
        <h2>New message from the Web2Risk website</h2>
        <p><strong>Name:</strong> {$safeName}</p>
        <p><strong>Email:</strong> {$safeEmail}</p>
        <p><strong>Message:</strong><br>{$safeMessage}</p>
    ";
    $mail->AltBody = "Name: {$name}\nEmail: {$email}\n\nMessage:\n{$message}";

    $mail->send();
    respond(true, 'Thank you! Your message has been sent. We will get back to you soon.');
} catch (Exception $e) {
    error_log('Mail error: ' . $mail->ErrorInfo); // l'erreur détaillée reste côté serveur
    respond(false, 'Sorry, your message could not be sent. Please try again later.', 500);
}