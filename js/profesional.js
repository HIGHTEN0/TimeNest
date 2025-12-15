//en esta clase se manejan las funcionalidades comunes de las paginas de profesional


// Cargar datos del profesional al iniciar la página
document.addEventListener('DOMContentLoaded', function() { // Esperar a que el DOM esté completamente cargado
    cargarDatosProfesional(); // Cargar nombre, foto, logo y profesión del profesional desde la base de datos
    
    // Asegurarse de que el menú esté cerrado al cargar
    const menuCheckbox = document.getElementById('menuCheckbox'); // Checkbox del menú hamburguesa
    if (menuCheckbox) {
        menuCheckbox.checked = false; // Mantener el menú cerrado al inicio
    }
});

// Función para obtener y mostrar los datos del profesional
function cargarDatosProfesional() {
    fetch('/ProyectoTimeNest/php/obtenerDatosProfesional.php') // Solicitud al servidor para obtener datos
        .then(response => response.json()) // Convertir respuesta a JSON
        .then(data => {
            if (data.success) { // Si la solicitud fue exitosa
                
                // Actualizar nombre del profesional (debajo de la foto)
                const nombreProfesional = document.getElementById('nombreProfesional'); // Elemento donde se muestra el nombre
                if (nombreProfesional) {
                    nombreProfesional.textContent = data.nombre_completo; // Mostrar nombre completo del profesional
                }
                
                // Actualizar profesión (al lado del logo)
                const profesionText = document.getElementById('profesionText'); // Elemento donde se muestra la profesión
                if (profesionText && data.profesion) {
                    profesionText.textContent = data.profesion; // Mostrar profesión del profesional
                } else if (profesionText) {
                    profesionText.textContent = 'Profesional'; // Texto por defecto si no hay profesión
                }
                
                // Actualizar nombre del consultorio (centro)
                const nombreConsultorio = document.getElementById('nombreConsultorio'); // Elemento donde se muestra el nombre del consultorio
                if (nombreConsultorio && data.nombre_consultorio) {
                    nombreConsultorio.textContent = data.nombre_consultorio; // Mostrar nombre del consultorio
                } else if (nombreConsultorio) {
                    nombreConsultorio.textContent = 'Consultorio'; // Texto por defecto si no hay nombre
                }
                
                // Actualizar foto de perfil
                const profileImg = document.getElementById('profileImg'); // Elemento de la imagen de perfil
                if (profileImg) {
                    if (data.foto_perfil && data.foto_perfil !== '' && data.foto_perfil !== 'null') {
                        // Si hay foto en la base de datos, usarla
                        profileImg.src = data.foto_perfil; // Cargar foto desde la BD
                        localStorage.setItem('profileImage', data.foto_perfil); // Guardar también en localStorage
                    } else {
                        // Si no hay foto en BD, intentar cargar desde localStorage
                        const localFoto = localStorage.getItem('profileImage'); // Obtener imagen del localStorage
                        if (localFoto) {
                            profileImg.src = localFoto; // Usar foto guardada localmente
                        } else {
                            profileImg.src = '/imagenes/usuario.png'; // Imagen por defecto
                        }
                    }
                }
                
                // Actualizar logo del consultorio
                const logoImg = document.getElementById('logoImg'); // Elemento de la imagen del logo
                if (logoImg) {
                    if (data.logo && data.logo !== '' && data.logo !== 'null') {
                        // Si hay logo en la base de datos, usarlo
                        logoImg.src = data.logo; // Cargar logo desde la BD
                        localStorage.setItem('logoImage', data.logo); // Guardar también en localStorage
                    } else {
                        // Si no hay logo en BD, intentar cargar desde localStorage
                        const localLogo = localStorage.getItem('logoImage'); // Obtener logo del localStorage
                        if (localLogo) {
                            logoImg.src = localLogo; // Usar logo guardado localmente
                        } else {
                            logoImg.src = '/imagenes/usuario.png'; // Imagen por defecto
                        }
                    }
                }
                
            } else {
                // Si no hay sesión válida, redirigir al login
                if (data.message === 'No hay sesión activa') {
                    window.location.href = '/ProyectoTimeNest/php/login.php'; // Redirigir a login
                }
            }
        })
        .catch(error => {
            console.error('Error al cargar datos del profesional:', error); // Mostrar error en consola
            // En caso de error, intentar cargar imágenes desde localStorage
            
            const profileImg = document.getElementById('profileImg'); // Elemento de la imagen de perfil
            const localFoto = localStorage.getItem('profileImage'); // Obtener imagen del localStorage
            if (profileImg && localFoto) {
                profileImg.src = localFoto; // Usar foto guardada localmente si hay error
            }
            
            const logoImg = document.getElementById('logoImg'); // Elemento de la imagen del logo
            const localLogo = localStorage.getItem('logoImage'); // Obtener logo del localStorage
            if (logoImg && localLogo) {
                logoImg.src = localLogo; // Usar logo guardado localmente si hay error
            }
        });
}


// Función para cargar la foto de perfil guardada en localStorage
function loadProfilePicture() {
    const savedImage = localStorage.getItem('profileImage'); // Obtener la imagen guardada del localStorage
    const profileImg = document.getElementById('profileImg'); // Elemento de la imagen de perfil
    
    if (savedImage) { // Si hay una imagen guardada, cargarla
        profileImg.src = savedImage; 
    } else {
        profileImg.src = '/imagenes/usuario.png'; // Imagen por defecto
    }
}


// Cerrar el menú al hacer clic en una opción
document.querySelectorAll('.menu-list').forEach(item => { // Seleccionar todas las opciones del menú
    item.addEventListener('click', function() { // Añadir un evento de clic a cada opción
        const checkbox = document.getElementById('menuCheckbox'); // Checkbox del menú
        if (checkbox) {
            checkbox.checked = false; // Cerrar el menú
        }
           if (this.textContent.trim() === 'Generar Listado de Citas') {
            window.location.href = '/ProyectoTimeNest/html/generarListadosCitas.html';
        }
         if (this.textContent.trim() === 'Generar Listado de Clientes') {
            window.location.href = '/ProyectoTimeNest/html/generarListadosClientes.html';
        }
        // Si se hace clic en "Editar Perfil", redirigir
        if (this.textContent.trim() === 'Editar Perfil') { // Verificar el texto de la opción
            window.location.href = '/ProyectoTimeNest/php/editarPerfilProfesional.php'; // Redirigir a editar perfil
        }
        
        // Si se hace clic en "Cerrar Sesión"
        if (this.classList.contains('cerrar-sesion')) { // Verificar si tiene la clase cerrar-sesion
            if (confirm('¿Estás seguro de cerrar sesión?')) { // Confirmación antes de cerrar sesión
                window.location.href = '/ProyectoTimeNest/php/cerrarSesion.php'; // Redirigir a cerrar sesión
            }
        }
    });
});

// Cerrar el menú al hacer clic fuera de él
document.addEventListener('click', function(event) { // Evento de clic en el documento
    const menu = document.querySelector('.event-wrapper'); // Seleccionar el menú
    const checkbox = document.getElementById('menuCheckbox'); // Checkbox del menú
    
    if (menu && checkbox && !menu.contains(event.target)) { // Si el clic no es dentro del menú
        checkbox.checked = false; // Cerrar el menú
    }
});

// Click en la foto de perfil para ir a editar perfil
const profilePicture = document.querySelector('.profile-picture'); // Seleccionar la foto de perfil
if (profilePicture) {
    profilePicture.addEventListener('click', function() { // Evento de clic en la foto de perfil
        window.location.href = '/ProyectoTimeNest/php/editarPerfilProfesional.php'; // Redirigir a editar perfil
    });
}

// Click en el logo para ir a editar perfil
const logoPicture = document.querySelector('.logo-picture'); // Seleccionar el logo
if (logoPicture) {
    logoPicture.addEventListener('click', function() { // Evento de clic en el logo
        window.location.href = '/ProyectoTimeNest/php/editarPerfilProfesional.php'; // Redirigir a editar perfil
    });
}

// Prevenir que el menú se abra automáticamente al cargar la página
window.addEventListener('load', function() { // Evento cuando la página termina de cargar
    const menuCheckbox = document.getElementById('menuCheckbox'); // Checkbox del menú
    if (menuCheckbox) {
        menuCheckbox.checked = false; // Asegurar que el menú esté cerrado
    }
});