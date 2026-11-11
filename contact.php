<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode nav atļauta.']);
    exit;
}

$configPath = __DIR__ . '/../private/config.php';
if (!is_file($configPath)) {
    error_log('EB Technologies contact form: missing config file at ' . $configPath);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Forma šobrīd nav pieejama. Lūdzu, rakstiet uz info@ebtechnologies.lv.']);
    exit;
}

$config = require $configPath;

if (empty($config['enabled'])) {
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Kontaktforma vēl nav pieslēgta. Lūdzu, rakstiet uz info@ebtechnologies.lv vai zvaniet +371 29 179 536.']);
    exit;
}

$field = static function (string $name): string {
    $value = $_POST[$name] ?? '';
    if (!is_string($value)) {
        return '';
    }
    return trim(str_replace(["\r", "\n", "%0a", "%0d"], ' ', strip_tags($value)));
};

$name = $field('name');
$company = $field('company');
$email = $field('email');
$phone = $field('phone');
$service = $field('service');
$message = trim((string) ($_POST['message'] ?? ''));
$consent = $field('consent');
$honeypot = $field('website');

/* A filled hidden field means a bot. Pretend success without sending. */
if ($honeypot !== '') {
    echo json_encode(['success' => true, 'message' => 'Paldies! Jūsu ziņa ir nosūtīta.']);
    exit;
}

if ($name === '' || $email === '' || $message === '' || $consent !== 'yes') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Lūdzu, aizpildiet obligātos laukus.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Lūdzu, ievadiet derīgu e-pasta adresi.']);
    exit;
}

if (mb_strlen($name) > 120 || mb_strlen($company) > 160 || mb_strlen($phone) > 40 || mb_strlen($service) > 160 || mb_strlen($message) > 5000) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Ziņa ir pārāk gara. Lūdzu, saīsiniet tekstu.']);
    exit;
}

$autoload = __DIR__ . '/vendor/autoload.php';
if (!is_file($autoload)) {
    error_log('EB Technologies contact form: PHPMailer is not installed (composer install missing).');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Forma šobrīd nav pieejama. Lūdzu, rakstiet uz info@ebtechnologies.lv.']);
    exit;
}

require $autoload;

$to = $config['contactTo'] ?? 'info@ebtechnologies.lv';

$body = "Jauns pieteikums no ebtechnologies.lv\n\n";
$body .= "Vārds: {$name}\n";
$body .= "Uzņēmums: " . ($company !== '' ? $company : '-') . "\n";
$body .= "E-pasts: {$email}\n";
$body .= 'Tālrunis: ' . ($phone !== '' ? $phone : '-') . "\n";
$body .= 'Pakalpojums: ' . ($service !== '' ? $service : '-') . "\n\n";
$body .= "Ziņa:\n{$message}\n\n";
$body .= "---\n";
$body .= 'Nosūtīts no: ' . ($_SERVER['HTTP_REFERER'] ?? 'nav norādīts') . "\n";
$body .= 'IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'nav norādīts') . "\n";

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = $config['smtpHost'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['smtpUser'];
    $mail->Password = $config['smtpPass'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = (int) $config['smtpPort'];
    $mail->CharSet = 'UTF-8';

    $mail->setFrom($config['smtpUser'], 'EB Technologies');
    $mail->addAddress($to);
    $mail->addReplyTo($email, $name);

    $mail->Subject = 'Jauns pieteikums no ebtechnologies.lv - ' . $name;
    $mail->Body = $body;

    $mail->send();

    echo json_encode(['success' => true, 'message' => 'Paldies! Jūsu ziņa ir nosūtīta. Sazināsimies ar jums.']);
} catch (Exception $error) {
    error_log('EB Technologies contact form error: ' . $mail->ErrorInfo);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Neizdevās nosūtīt ziņu. Lūdzu, rakstiet uz info@ebtechnologies.lv.']);
}
