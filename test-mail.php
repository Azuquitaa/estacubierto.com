<?php
// TEST MAIL - Archivo para verificar configuración del servidor
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test de Configuración de Correo</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; }
        .info { background: #e7f3ff; color: #004085; padding: 15px; border-radius: 5px; margin-top: 20px; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; }
        button { background: #f86254; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>🔧 Test de Configuración de Correo</h1>
    
    <div class="info">
        <strong>📋 Información del servidor:</strong>
        <pre>
PHP Version: <?php echo phpversion(); ?>
Server Software: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido'; ?>
Document Root: <?php echo $_SERVER['DOCUMENT_ROOT']; ?>
sendmail_path: <?php echo ini_get('sendmail_path') ?: 'No configurado'; ?>
SMTP: <?php echo ini_get('SMTP') ?: 'No configurado'; ?>
smtp_port: <?php echo ini_get('smtp_port') ?: 'No configurado'; ?>
        </pre>
    </div>

    <?php
    // Probar envío si se solicitó
    if (isset($_POST['test_email'])) {
        $to = $_POST['test_email'];
        $subject = "Prueba desde " . $_SERVER['HTTP_HOST'];
        $message = "Este es un correo de prueba enviado desde " . $_SERVER['HTTP_HOST'] . " a las " . date('H:i:s');
        $headers = "From: test@" . $_SERVER['HTTP_HOST'] . "\r\n";
        
        echo "<h2>📤 Resultado del envío:</h2>";
        
        if (mail($to, $subject, $message, $headers)) {
            echo '<div class="success">✅ CORREO ENVIADO CON ÉXITO a ' . htmlspecialchars($to) . '</div>';
        } else {
            echo '<div class="error">❌ ERROR: No se pudo enviar el correo</div>';
            
            // Mostrar posibles causas
            echo '<div class="info">';
            echo '<strong>🔍 Posibles causas:</strong><br>';
            echo '• La función mail() está deshabilitada<br>';
            echo '• No hay servidor SMTP configurado<br>';
            echo '• El servidor no permite envío de correos<br>';
            echo '• Problemas de permisos en el servidor';
            echo '</div>';
        }
    }
    ?>

    <h2>📧 Probar envío de correo</h2>
    <form method="POST">
        <label>Email de prueba:</label><br>
        <input type="email" name="test_email" value="luxamgames2000@gmail.com" required style="width: 100%; padding: 10px; margin: 10px 0;">
        <button type="submit">Enviar correo de prueba</button>
    </form>

    <h2>🔍 Verificar tu archivo contacto.php</h2>
    <p>Haz clic en el botón para probar tu archivo de contacto:</p>
    <button onclick="testContacto()">Probar contacto.php</button>
    <div id="resultado" style="margin-top: 20px;"></div>

    <script>
    async function testContacto() {
        const resultado = document.getElementById('resultado');
        resultado.innerHTML = '⏳ Probando...';
        
        // Crear FormData de prueba
        const formData = new FormData();
        formData.append('email', 'test@ejemplo.com');
        formData.append('mensaje', 'Este es un mensaje de prueba');
        
        try {
            const response = await fetch('php/contacto.php', {
                method: 'POST',
                body: formData
            });
            
            const text = await response.text();
            
            resultado.innerHTML = `
                <div class="${response.ok ? 'success' : 'error'}">
                    <strong>Status:</strong> ${response.status} ${response.statusText}<br>
                    <strong>Respuesta:</strong> 
                    <pre>${text}</pre>
                </div>
            `;
        } catch (error) {
            resultado.innerHTML = `
                <div class="error">
                    <strong>Error:</strong> ${error.message}<br>
                    Verifica que la ruta "php/contacto.php" sea correcta
                </div>
            `;
        }
    }
    </script>

    <h2>⚙️ Configuración recomendada para contacto.php</h2>
    <p>Si el test muestra que mail() funciona, tu código debería funcionar. Si no, usa esta configuración:</p>
    <pre>
// Al inicio de contacto.php, después de &lt;?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar si es una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Método no permitido']));
}

// Tus datos
$to = "luxamgames2000@gmail.com";
$subject = "Contacto desde web";
$email = $_POST['email'] ?? '';
$mensaje = $_POST['mensaje'] ?? '';

// Validaciones básicas
if (empty($email) || empty($mensaje)) {
    die(json_encode(['success' => false, 'message' => 'Completa todos los campos']));
}

// Intentar enviar
if (mail($to, $subject, "Email: $email\nMensaje: $mensaje", "From: $email")) {
    echo json_encode(['success' => true, 'message' => 'Enviado']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error']);
}
    </pre>
</body>
</html>