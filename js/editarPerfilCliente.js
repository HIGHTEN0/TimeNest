//esta clase es para que el cliente pueda editar su perfil

let fotoTemporal = null; // Variable para almacenar la foto temporal
//let es una variable global para guardar la foto seleccionada antes de subirla
// Cargar foto desde base de datos o localStorage
document.addEventListener('DOMContentLoaded', function() { // Esperar a que el DOM esté completamente cargado
    const previewImg = document.getElementById('previewImg');
    const localFoto = localStorage.getItem('profileImage');
    
    // Si hay foto en localStorage y no hay en la base de datos, usar localStorage
    if (localFoto && previewImg.src.includes('usuario.png')) {
        previewImg.src = localFoto;
        fotoTemporal = localFoto;
    }
});

// Manejar selección de archivo
document.getElementById('inputFoto').addEventListener('change', function(e) { // Evento al cambiar el input de archivo
    const file = e.target.files[0]; // Obtener el archivo seleccionado
    if (file) {
        // Validar tamaño (máximo 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('La imagen es muy grande. Máximo 2MB.');
            this.value = '';
            return;
        }
        
        // Validar tipo
        if (!file.type.startsWith('image/')) { // Verificar que el archivo sea una imagen
            alert('Solo se permiten imágenes.');
            this.value = '';
            return;
        }
        
        const reader = new FileReader(); // Crear un lector de archivos
        reader.onload = function(e) { // Evento cuando se carga el archivo
            const imageUrl = e.target.result; // Obtener la URL de la imagen
            fotoTemporal = imageUrl; // Guardar en la variable temporal
            document.getElementById('previewImg').src = imageUrl; // Mostrar la imagen en el preview
            
            // También guardar en localStorage por si acaso
            localStorage.setItem('profileImage', imageUrl);
        };
        reader.readAsDataURL(file); // Leer el archivo como Data URL
    }
});

// Guardar foto en la base de datos
function guardarFoto() {
    if (!fotoTemporal) {
        alert('Primero selecciona una foto');
        return;
    }
     const btnGuardar = event.target;// Botón que se ha presionado
    const textoOriginal = btnGuardar.innerHTML;// Guardar el texto original del botón
    btnGuardar.disabled = true;// Deshabilitar el botón para evitar múltiples envíos
    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';// Cambiar el texto a un spinner
    //un spinner es un icono que indica que una acción está en progreso

    document.getElementById('foto_base64').value = fotoTemporal; // Asignar la foto al campo oculto

     const form = document.getElementById('formFoto');// Obtener el formulario
    const formData = new FormData(form);// Crear un FormData con los datos del formulario

    fetch(form.action || window.location.href, {// Enviar la solicitud al servidor
        method: 'POST',
        body: formData
    })
    .then(response => response.text())// Convertir la respuesta a texto
    .then(html => {// Manejar la respuesta del servidor
        window.location.reload();// Recargar la página para reflejar los cambios
    })
    .catch(error => {// Manejar errores
        console.error('Error:', error);
        alert('Error al guardar la foto');
        btnGuardar.disabled = false;// Rehabilitar el botón en caso de error
        btnGuardar.innerHTML = textoOriginal;// Restaurar el texto original del botón
    });
}

//editar campos de perfil
function editarCampo(campo, etiqueta, tipo) {
    // Obtener el valor actual
    const valorActual = document.getElementById('valor-' + campo).textContent.trim();
    
    // Configurar el modal
    //el modal es una ventana emergente para editar campos
    document.getElementById('modalTitulo').textContent = 'Editar ' + etiqueta;
    document.getElementById('modalLabel').textContent = etiqueta + ':';
    document.getElementById('modalCampo').value = campo;
    document.getElementById('modalValor').value = valorActual === 'No especificado' ? '' : valorActual; // Si no hay valor, dejar vacío
    document.getElementById('modalValor').type = tipo; // Establecer el tipo de input
    
    // Validaciones específicas
    if (tipo === 'number') { // Si es número, establecer rango
        document.getElementById('modalValor').min = 18; // Edad mínima
        document.getElementById('modalValor').max = 120;
    }
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalEditar'));
    modal.show();
}

function editarCampoSelect(campo, etiqueta) {
    // Obtener el valor actual
    const valorActual = document.getElementById('valor-' + campo).textContent.trim();
    
    // Configurar el select
    const select = document.getElementById('valorGenero');
    select.value = valorActual;
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalGenero'));
    modal.show();
}


// Confirmar antes de salir si hay cambios sin guardar
let hayFotoSinGuardar = false;

document.getElementById('inputFoto').addEventListener('change', function() {
    if (this.files.length > 0) {
        hayFotoSinGuardar = true;
    }
});

document.getElementById('formFoto').addEventListener('submit', function() {
    hayFotoSinGuardar = false;
});

// Advertir al usuario si intenta salir sin guardar la foto
window.addEventListener('beforeunload', function(e) { // Evento antes de descargar la página
    if (hayFotoSinGuardar) {
        e.preventDefault(); // Prevenir la acción por defecto
        e.returnValue = '¿Estás seguro de salir? Tienes una foto sin guardar.'; // Mensaje de advertencia
        return '¿Estás seguro de salir? Tienes una foto sin guardar.'; // Para navegadores antiguos
    }
});

//  Confirmar al hacer clic en "Volver" si hay foto sin guardar
document.addEventListener('DOMContentLoaded', function() { // Esperar a que el DOM esté completamente cargado
    const btnVolver = document.querySelector('a[href*="cliente.html"]');    // Seleccionar el botón "Volver"
    if (btnVolver) { // Si el botón existe
        btnVolver.addEventListener('click', function(e) {
            if (hayFotoSinGuardar) {
                if (!confirm('Tienes una foto sin guardar. ¿Estás seguro de salir?')) { // Confirmar salida
                    e.preventDefault();
                }
            }
        });
    }
});