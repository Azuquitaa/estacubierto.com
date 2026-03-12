// Variable para almacenar las traducciones actuales
let traduccionesActuales = {};

// Función para obtener el idioma del navegador
function getUserLanguage() {
    if (navigator.languages && navigator.languages.length) {
        return navigator.languages[0].split('-')[0];
    } else {
        return (navigator.language || navigator.userLanguage).split('-')[0];
    }
}

// Función para cargar el archivo de idioma
async function cargarIdioma(idioma) {
    try {
        // Intentamos cargar el archivo JSON correspondiente
        // const respuesta = await fetch(`/secure/idioma/${idioma}.json`);
         const respuesta = await fetch(`./idioma/${idioma}.json`);
         
        if (!respuesta.ok) {
            throw new Error(`No se pudo cargar el idioma ${idioma}`);
        }
        
        const traducciones = await respuesta.json();
        traduccionesActuales = traducciones;
        
        // Aplicar las traducciones a la página
        aplicarTraducciones();
        
        console.log(`Idioma cargado: ${idioma},`);
    } catch (error) {
        console.error('Error cargando traducciones:', error);
        // Si falla, cargar español por defecto
        if (idioma !== 'es') {
            cargarIdioma('es');
        }
    }
}

// Función para aplicar las traducciones a los elementos de la página
function aplicarTraducciones() {
    // Seleccionar todos los elementos que tengan data-key
    const elementos = document.querySelectorAll('[data-key]');
    
    elementos.forEach(elemento => {
        const clave = elemento.getAttribute('data-key');
        
        // Si existe la traducción para esa clave, actualizar el texto
        if (traduccionesActuales[clave]) {
            elemento.textContent = traduccionesActuales[clave];
        }
    });
}

// Función para cambiar idioma manualmente (para los botones)
function cambiarIdioma(idioma) {
    cargarIdioma(idioma);
    // Guardar preferencia en localStorage para futuras visitas
    localStorage.setItem('idiomaPreferido', idioma);
}

// Inicialización cuando carga la página
document.addEventListener('DOMContentLoaded', async () => {
    // Primero verificar si hay un idioma guardado en localStorage
    let idiomaInicial = localStorage.getItem('idiomaPreferido');
    
    // Si no hay idioma guardado, usar el del navegador
    if (!idiomaInicial) {
        idiomaInicial = getUserLanguage();
    }
    
    // Lista de idiomas soportados
    const idiomasSoportados = ['es', 'en'];
    
    // Si el idioma detectado no está soportado, usar español
    if (!idiomasSoportados.includes(idiomaInicial)) {
        idiomaInicial = 'es';
    }
    
    // Cargar el idioma inicial
    await cargarIdioma(idiomaInicial);
});