<?php

// ========================
// CONFIGURACIÓN INICIAL
// ========================

$debug_mode = true;

if ($debug_mode) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    if (isset($_GET['test'])) {
        echo "<h2>Prueba de servidor</h2>";
        echo "<pre>";
        echo "PHP Version: " . phpversion() . "\n";
        echo "</pre>";
        exit;
    }
}

// ========================
// INCLUIR PHPMailer
// ========================

require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';
require __DIR__ . '/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ========================
// CONFIGURACIÓN GENERAL
// ========================

$domain = 'luxamgames2000@gmail.com';

$additionalRecipients = [
    'luxamgames2000@gmail.com',
    // 'maxi@estacubierto.com'
];

// Respuesta JSON
header('Content-Type: application/json; charset=UTF-8');

// Log de errores
ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/debug.log');

error_log("\n\n[NUEVA SOLICITUD] ".date('Y-m-d H:i:s')." ".$_SERVER['REMOTE_ADDR']);

// ========================
// VALIDAR MÉTODO
// ========================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Método no permitido']));
}

// ========================
// OBTENER DATOS
// ========================

$data = [
    'email' => filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL),
    'mensaje' => trim(htmlspecialchars($_POST['mensaje'] ?? ''))
];

// Validaciones
$errors = [];

if (empty($data['email'])) {
    $errors[] = "El email es requerido";
} elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Email no válido";
}

if (empty($data['mensaje'])) {
    $errors[] = "El mensaje es requerido";
}

if (!empty($errors)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => implode(', ', $errors)]));
}

// ========================
// ARMAR MENSAJE
// ========================

$subject = "Contacto desde Estacubierto - " . date('d/m/Y');

$message = "Detalles del contacto:\n\n";
$message .= "Email: {$data['email']}\n";
$message .= "Mensaje:\n{$data['mensaje']}\n\n";
$message .= "Enviado: " . date('d/m/Y H:i:s');

// ========================
// ENVÍO CON PHPMailer
// ========================

$allRecipients = array_merge([$domain], $additionalRecipients);
$successCount = 0;

foreach ($allRecipients as $recipient) {

    $mail = new PHPMailer(true);

    try {
        // CONFIG SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'luxamgames2000@gmail.com'; // ← CAMBIAR
        $mail->Password = 'hjcbqruwpsokarwh'; // ← CAMBIAR
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        // REMITENTE Y DESTINO
        $mail->setFrom('luxamgames2000@gmail.com', 'Web Estacubierto');
        $mail->addAddress($recipient);

        // CONTENIDO
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
        $successCount++;

        error_log("[OK] Enviado a $recipient");

    } catch (Exception $e) {
        error_log("[ERROR] $recipient -> " . $mail->ErrorInfo);
    }
}

// ========================
// RESPUESTA FINAL
// ========================

if ($successCount > 0) {
    echo json_encode([
        'success' => true,
        'message' => 'Mensaje enviado correctamente'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'No se pudo enviar el mensaje'
    ]);
}