<?php
$debug_mode = true;

if ($debug_mode) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    
    // Si viene con parámetro test, mostrar info del servidor
    if (isset($_GET['test'])) {
        echo "<h2>Prueba de servidor</h2>";
        echo "<pre>";
        echo "PHP Version: " . phpversion() . "\n";
        echo "sendmail_path: " . ini_get('sendmail_path') . "\n";
        echo "SMTP: " . ini_get('SMTP') . "\n";
        echo "smtp_port: " . ini_get('smtp_port') . "\n";
        echo "</pre>";
        exit;
    }
}

$domain = 'luxamgames2000@gmail.com'; // Correo principal
$additionalRecipients = [
    'luxamgames2000@gmail.com',
    'maxi@estacubierto.com'    
    // 'customer@gamabranch.com',
    // 'comercial@gamabranch.com',
    // 'administracion@gamabranch.com'
];

// Configuración inicial
header('Content-Type: application/json; charset=UTF-8');

// Habilitar registro de errores
ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/debug.log');

// Registrar solicitud entrante
error_log("\n\n[NUEVA SOLICITUD] ".date('Y-m-d H:i:s')." ".$_SERVER['REMOTE_ADDR']);

// 1. Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $error = 'Método no permitido';
    error_log("[ERROR] $error");
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => $error]));
}

// 2. Obtener y registrar datos
$data = [
    'email' => filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL),
    'mensaje' => trim(htmlspecialchars($_POST['mensaje'] ?? ''))
];

error_log("[DATOS PROCESADOS] ".print_r($data, true));

// 3. Validar campos
$errors = [];

if (empty($data['email'])) {
    $errors[] = "El campo email es requerido";
} elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Email no válido";
}

if (empty($data['mensaje'])) {
    $errors[] = "El campo mensaje es requerido";
}

if (!empty($errors)) {
    $errorMsg = implode(', ', $errors);
    error_log("[ERROR VALIDACIÓN] $errorMsg");
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => $errorMsg]));
}

// 4. Configurar correo
$subject = "Contacto desde Estacubierto - " . date('d/m/Y'); // ¡FALTABA ESTA LÍNEA!

$message = "Detalles del contacto:\n\n";
$message .= "Email: {$data['email']}\n";
$message .= "Mensaje:\n{$data['mensaje']}\n";
$message .= "\n---\n";
$message .= "Enviado: " . date('d/m/Y H:i:s');

$headers = [
    'From' => "no-reply@estacubierto.com",
    'Reply-To' => $data['email'],
    'X-Mailer' => 'PHP/'.phpversion(),
    'Content-Type' => 'text/plain; charset=UTF-8'
];

$headersString = '';
foreach ($headers as $key => $value) {
    $headersString .= "$key: $value\r\n";
}

error_log("[CONFIG CORREO] To: $domain\nSubject: $subject");

// 5. Intento de envío
try {
    error_log("[INTENTO ENVÍO] Iniciando envío...");
    
    // Lista completa de destinatarios (principal + adicionales)
    $allRecipients = array_merge([$domain], $additionalRecipients);
    $successCount = 0;
    
    foreach ($allRecipients as $recipient) {
        error_log("[ENVIANDO A] $recipient");
        $mailSent = mail($recipient, $subject, $message, $headersString);
        
        if ($mailSent) {
            $successCount++;
            error_log("[ENVÍO EXITOSO] Correo enviado a $recipient");
        } else {
            error_log("[FALLO ENVÍO] Falló el envío a $recipient");
        }
    }
    
    if ($successCount > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Mensaje enviado con éxito. ¡Gracias por contactarnos!'
        ]);
    } else {
        throw new Exception('Todos los intentos de envío fallaron');
    }
    
} catch (Exception $e) {
    $errorMsg = 'Error al enviar el correo: '.$e->getMessage();
    error_log("[ERROR ENVÍO] $errorMsg");
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al enviar el mensaje. Por favor intente nuevamente más tarde.'
    ]);
}
?>