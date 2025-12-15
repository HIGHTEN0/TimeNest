// esta clase es para que el cliente pueda ver y gestionar sus citas

// Cargar datos del cliente al iniciar la página
document.addEventListener('DOMContentLoaded', function() { // Esperar a que el DOM esté completamente cargado
    cargarDatosCliente(); // Cargar nombre y foto del cliente desde la base de datos
    cargarCitas(); // Cargar las citas del cliente
    
    // Recargar citas cada 30 segundos
    setInterval(cargarCitas, 30000); // Actualizar citas automáticamente
    
    const menuCheckbox = document.getElementById('menuCheckbox'); // Checkbox del menú 
    if (menuCheckbox) {
        menuCheckbox.checked = false; // Mantener el menú cerrado al inicio
    }
});

// Función para obtener y mostrar los datos del cliente
function cargarDatosCliente() {
    fetch('/ProyectoTimeNest/php/obtenerDatosCliente.php') // Solicitud al servidor para obtener datos
        .then(response => response.json()) // Convertir respuesta a JSON
        .then(data => {
            if (data.success) { // Si la solicitud fue exitosa
                // Actualizar el nombre en el navbar
                const nombreElemento = document.getElementById('nombreCliente'); // Elemento donde se muestra el nombre
                if (nombreElemento) {
                    const nombreCompleto = `${data.nombre} ${data.apellido || ''}`.trim();
                    nombreElemento.textContent = nombreCompleto; // Mostrar nombre completo del cliente
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
            } else {
                // Si no hay sesión válida, redirigir al login
                if (data.message === 'No hay sesión activa') {
                    window.location.href = '/ProyectoTimeNest/php/login.php'; // Redirigir a login
                }
            }
        })
        .catch(error => {
            console.error('Error al cargar datos del cliente:', error); // Mostrar error en consola
            // En caso de error, intentar cargar foto desde localStorage
            const profileImg = document.getElementById('profileImg'); // Elemento de la imagen de perfil
            const localFoto = localStorage.getItem('profileImage'); // Obtener imagen del localStorage
            if (profileImg && localFoto) {
                profileImg.src = localFoto; // Usar foto guardada localmente si hay error
            }
        });
}

// Función para obtener las citas del cliente desde el servidor
function cargarCitas() {
    fetch('/ProyectoTimeNest/php/obtenerCitasCliente.php') // Solicitud al servidor para obtener citas
        .then(response => response.json()) // Convertir respuesta a JSON
        .then(data => {
            if (data.success) { // Si la solicitud fue exitosa
                mostrarCitasProximas(data.citas_proximas); // Mostrar citas próximas
                mostrarCitasFinalizadas(data.citas_finalizadas); // Mostrar citas finalizadas
            }
        })
        .catch(error => console.error('Error al cargar citas:', error)); // Mostrar error en consola
}


// Función para mostrar las citas próximas en la interfaz
function mostrarCitasProximas(citas) {
    const container = document.getElementById('citasProximas'); // Contenedor donde se mostrarán las citas
    
    if (!citas || citas.length === 0) { // Si no hay citas
        container.innerHTML = '<p class="text-center text-muted">No hay citas próximas</p>';
        return;
    }
    
    container.innerHTML = ''; // Limpiar el contenedor
    
    citas.forEach(cita => { // Recorrer cada cita
        const nombreProfesional = `${cita.NombreProfesional} ${cita.ApellidoProfesional || ''}`.trim(); // Nombre completo del profesional
        
        const citaCard = `
            <div class="cita-card-cliente">
                <div class="cita-fecha-cliente">
                    ${formatearFecha(cita.DiaCita)}
                </div>
                
                <div class="cita-hora-cliente">
                    ${cita.HoraInicial} - ${cita.HoraFinal}
                </div>
                
                <div class="cita-info-cliente">
                    <span class="cita-info-cliente-label">Profesional:</span>
                    <span class="cita-info-cliente-valor">${cita.Profesion || 'Profesional'}</span>
                </div>
                
                <div class="cita-info-cliente">
                    <span class="cita-info-cliente-label">Nombre:</span>
                    <span class="cita-info-cliente-valor">${nombreProfesional}</span>
                </div>
                
                <div class="cita-info-cliente">
                    <span class="cita-info-cliente-label">Consultorio:</span>
                    <span class="cita-info-cliente-valor">${cita.NombreConsultorio || 'No especificado'}</span>
                </div>
                
                <div class="cita-ubicacion-cliente">
                    ${cita.UbicacionConsultorio || cita.Descripcion || 'Ubicación no especificada'}
                </div>
            </div>
        `;
        
        container.innerHTML += citaCard; // Agregar la tarjeta al contenedor
    });
}


// Función para mostrar las citas finalizadas en la interfaz
function mostrarCitasFinalizadas(citas) {
    const container = document.getElementById('citasFinalizadas'); // Contenedor donde se mostrarán las citas
    
    if (!citas || citas.length === 0) { // Si no hay citas
        container.innerHTML = '<p class="text-center text-muted">No hay citas finalizadas</p>';
        return;
    }
    
    container.innerHTML = ''; // Limpiar el contenedor
    
    citas.forEach(cita => { // Recorrer cada cita
        const nombreProfesional = `${cita.NombreProfesional} ${cita.ApellidoProfesional || ''}`.trim(); // Nombre completo del profesional
        
        const citaCard = `
            <div class="cita-card-cliente">
                <div class="cita-fecha-cliente">
                    ${formatearFecha(cita.DiaCita)}
                </div>
                
                <div class="cita-hora-cliente">
                    ${cita.HoraInicial}
                </div>
                
                <div class="cita-info-cliente">
                    <span class="cita-info-cliente-label">Profesional:</span>
                    <span class="cita-info-cliente-valor">${cita.Profesion || 'Profesional'}</span>
                </div>
                
                <div class="cita-info-cliente">
                    <span class="cita-info-cliente-label">Nombre:</span>
                    <span class="cita-info-cliente-valor">${nombreProfesional}</span>
                </div>
                
                <div class="cita-info-cliente">
                    <span class="cita-info-cliente-label">Consultorio:</span>
                    <span class="cita-info-cliente-valor">${cita.NombreConsultorio || 'No especificado'}</span>
                </div>
            </div>
        `;
        
        container.innerHTML += citaCard; // Agregar la tarjeta al contenedor
    });
}


// Función para formatear la fecha en formato dd/mm/aaaa
function formatearFecha(fecha) {
    const opciones = { year: 'numeric', month: '2-digit', day: '2-digit' }; // Opciones de formato
    return new Date(fecha + 'T00:00:00').toLocaleDateString('es-MX', opciones); // Formatear fecha
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
        
        // Si se hace clic en "Editar Perfil", redirigir
        if (this.textContent.trim() === 'Editar Perfil') { // Verificar el texto de la opción
            window.location.href = '/ProyectoTimeNest/php/editarPerfilCliente.php'; // Redirigir a editar perfil
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
const profilePicture = document.querySelector('.profile-picture-cliente'); // Seleccionar la foto de perfil
if (profilePicture) {
    profilePicture.addEventListener('click', function() { // Evento de clic en la foto de perfil
        window.location.href = '/ProyectoTimeNest/php/editarPerfilCliente.php'; // Redirigir a editar perfil
    });
}

// Prevenir que el menú se abra automáticamente al cargar la página
window.addEventListener('load', function() { // Evento cuando la página termina de cargar
    const menuCheckbox = document.getElementById('menuCheckbox'); // Checkbox del menú
    if (menuCheckbox) {
        menuCheckbox.checked = false; // Asegurar que el menú esté cerrado
    }
});