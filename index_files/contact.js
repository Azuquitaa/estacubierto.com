// formulario de contacto - VERSIÓN CORREGIDA
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formContacto');
    if (!form) return; // Si no existe el formulario, salir
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validación antes de enviar
        if (!validateForm()) {
            return;
        }
        
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        
        // Estado de carga
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
        
        try {
            const formData = new FormData(form);
            
            // Agregar marca de tiempo
            formData.append('timestamp', new Date().toISOString());
            
            const response = await fetch('php/contact.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            // Verificar si la respuesta es JSON
            let data;
            const contentType = response.headers.get('content-type');
            
            if (contentType && contentType.includes('application/json')) {
                data = await response.json();
            } else {
                const text = await response.text();
                console.error('Respuesta no JSON:', text);
                throw new Error('Error en el servidor');
            }
            
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Error en el servidor');
            }
            
            // Mostrar notificación de éxito
            showNotification('success', data.message);
            form.reset();
            
        } catch (error) {
            console.error('Error en el formulario:', error);
            
            // Mostrar notificación de error
            showNotification('error', 'Error al enviar. Intente nuevamente.');
            
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    });
});

// Función de validación
function validateForm() {
    let isValid = true;
    
    // Validar email
    const email = document.getElementById('email');
    const emailError = document.getElementById('emailError');
    
    if (!email || !email.value.trim()) {
        if (emailError) emailError.classList.remove('hidden');
        isValid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
        if (emailError) emailError.classList.remove('hidden');
        isValid = false;
    } else {
        if (emailError) emailError.classList.add('hidden');
    }
    
    // Validar mensaje
    const mensaje = document.getElementById('mensaje');
    const mensajeError = document.getElementById('mensajeError');
    
    if (!mensaje || !mensaje.value.trim()) {
        if (mensajeError) mensajeError.classList.remove('hidden');
        isValid = false;
    } else {
        if (mensajeError) mensajeError.classList.add('hidden');
    }
    
    return isValid;
}

// Función para mostrar notificaciones
function showNotification(type, message) {
    // Eliminar notificaciones anteriores
    document.querySelectorAll('.custom-notification').forEach(el => el.remove());
    
    const notification = document.createElement('div');
    notification.className = `custom-notification ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} animate-fade-in`;
    notification.innerHTML = `
        <div style="display: flex; align-items: center;">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}" style="margin-right: 10px;"></i>
            ${message}
        </div>
    `;
    document.body.appendChild(notification);
    
    // Auto cerrar notificación
    setTimeout(() => {
        notification.classList.replace('animate-fade-in', 'animate-fade-out');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}