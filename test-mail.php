<?php
header('Content-Type: text/html; charset=UTF-8');

// ========================
// INCLUIR PHPMailer
// ========================
require __DIR__ . '/index_files/php/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/index_files/php/PHPMailer/src/SMTP.php';
require __DIR__ . '/index_files/php/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test SMTP (PHPMailer)</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 50px auto; }
        .success { background: #d4edda; padding: 15px; border-radius: 5px; }
        .error { background: #f8d7da; padding: 15px; border-radius: 5px; }
        .info { background: #e7f3ff; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>

<h1>🔧 Test SMTP con PHPMailer</h1>

<div class="info">
<pre>
PHP Version: <?php echo phpversion(); ?>
Servidor: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?>
</pre>
</div>

<?php
if (isset($_POST['test_email'])) {

    $to = $_POST['test_email'];

    echo "<h2>📤 Resultado del envío:</h2>";

    $mail = new PHPMailer(true);

    try {
        // 🔥 CONFIG SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'luxamgames2000@gmail.com'; // ← CAMBIAR
        $mail->Password = 'hjcbqruwpsokarwh'; // ← CAMBIAR
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        // DEBUG (muy útil si falla)
        $mail->SMTPDebug = 2;

        $mail->setFrom('luxamgames2000@gmail.com', 'Test Web');
        $mail->addAddress($to);

        $mail->Subject = 'Test SMTP OK';
        $mail->Body = 'Si recibiste esto, SMTP funciona perfecto 🚀';

        $mail->send();

        echo "<div class='success'>✅ CORREO ENVIADO a $to</div>";

    } catch (Exception $e) {
        echo "<div class='error'>";
        echo "❌ ERROR: " . $mail->ErrorInfo;
        echo "</div>";
    }
}
?>

<h2>📧 Enviar prueba</h2>

<form method="POST">
    <input type="email" name="test_email" value="luxamgames2000@gmail.com" required style="width:100%; padding:10px;">
    <br><br>
    <button type="submit">Enviar test</button>
</form>

<hr>

<h2>🔍 Test contacto.php</h2>
<button onclick="testContacto()">Probar contact.php</button>
<div id="resultado"></div>

<script>
async function testContacto() {
    const r = document.getElementById('resultado');
    r.innerHTML = '⏳ Probando...';

    const formData = new FormData();
    formData.append('email', 'test@ejemplo.com');
    formData.append('mensaje', 'Mensaje de prueba');

    try {
        const response = await fetch('/index_files/php/contact.php', {
            method: 'POST',
            body: formData
        });

        const text = await response.text();

        r.innerHTML = `
            <pre>Status: ${response.status}</pre>
            <pre>${text}</pre>
        `;

    } catch (e) {
        r.innerHTML = `<div class="error">${e.message}</div>`;
    }
}
</script>

</body>
</html>