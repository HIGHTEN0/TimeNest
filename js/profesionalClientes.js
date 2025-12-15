//esta clase es para los clientes que da de alta el profesional

document.addEventListener('DOMContentLoaded', function() {
    cargarDatosProfesional();
    cargarClientes();
    document.getElementById('formAltaCliente').addEventListener('submit', darAltaCliente);
    
    // aqui se maneja la logica de mostrar u ocultar campos segun el metodo de contacto
    document.getElementById('metodoContacto').addEventListener('change', function() {
        const metodo = this.value;
        const campoTelefono = document.getElementById('campoTelefono');
        const campoCorreo = document.getElementById('campoCorreo');
        const inputTelefono = document.getElementById('telefono');
        const inputCorreo = document.getElementById('correo');
        
        // Ocultar ambos campos
        campoTelefono.style.display = 'none';
        campoCorreo.style.display = 'none';
        
        // aqui se remueven los atributos required
        inputTelefono.removeAttribute('required');
        inputCorreo.removeAttribute('required');
        
        // Limpiar valores
        inputTelefono.value = '';
        inputCorreo.value = '';
        
        // Mostrar según selección
        if (metodo === 'telefono') {
            campoTelefono.style.display = 'block';
            inputTelefono.setAttribute('required', 'required');
        } else if (metodo === 'correo') {
            campoCorreo.style.display = 'block';
            inputCorreo.setAttribute('required', 'required');
        } else if (metodo === 'ambos') {
            campoTelefono.style.display = 'block';
            campoCorreo.style.display = 'block';
            inputTelefono.setAttribute('required', 'required');
            inputCorreo.setAttribute('required', 'required');
        }
    });
});

function darAltaCliente(e) {
    e.preventDefault();
    
    const metodoContacto = document.getElementById('metodoContacto').value;
    
    const datos = {
        nombre: document.getElementById('nombre').value.trim(),
        apellido: document.getElementById('apellido').value.trim(),
        genero: document.getElementById('genero').value,
        edad: document.getElementById('edad').value,
        telefono: document.getElementById('telefono').value.trim(),
        correo: document.getElementById('correo').value.trim(),
        domicilio: document.getElementById('domicilio').value.trim(),
        metodoContacto: metodoContacto
    };
    
    // Validación: campos obligatorios
    if (!datos.nombre || !datos.genero || !metodoContacto) {
        alert('Por favor completa todos los campos obligatorios');
        return;
    }
    
    // Validar según método de contacto
    if (metodoContacto === 'telefono' && !datos.telefono) {
        alert('Debes proporcionar un teléfono');
        return;
    }
    
    if (metodoContacto === 'correo' && !datos.correo) {
        alert('Debes proporcionar un correo');
        return;
    }
    
    if (metodoContacto === 'ambos' && (!datos.telefono || !datos.correo)) {
        alert('Debes proporcionar teléfono y correo');
        return;
    }
    
    // Validar formato de teléfono si se proporciona
    if (datos.telefono && !/^[0-9]{10}$/.test(datos.telefono)) {
        alert('El teléfono debe tener exactamente 10 dígitos');
        return;
    }
    
    // Validar formato de correo si se proporciona
    if (datos.correo && !validarCorreo(datos.correo)) {
        alert('Por favor ingresa un correo válido (gmail.com, hotmail.com, outlook.com, yahoo.com, etc.)');
        return;
    }
    
    fetch('/ProyectoTimeNest/php/crearClienteProfesional.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(datos) //.stringify convierte el objeto a formato JSON para enviarlo al servidor
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Cliente dado de alta exitosamente');
            document.getElementById('formAltaCliente').reset();
            document.getElementById('campoTelefono').style.display = 'none';
            document.getElementById('campoCorreo').style.display = 'none';
            cargarClientes();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al dar de alta el cliente');
    });
}


function validarCorreo(email) {
    const dominiosPermitidos = [
        'gmail.com', 
        'hotmail.com', 
        'outlook.com', 
        'yahoo.com'
    ];
    
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { //esta estructura es para validar que tenga formato de correo
        return false; 
    }
    
    const partes = email.split('@');//separa el correo en dos partes para obtener el dominio
    if (partes.length != 2) {//si no tiene dos partes, no es valido
        return false;
    }
    
    const dominio = partes[1].toLowerCase();//obtiene el dominio y lo convierte a minusculas
    return dominiosPermitidos.includes(dominio);//verifica si el dominio esta en la lista de permitidos
}

//aqui se cargan los clientes
function cargarClientes() {
    fetch('/ProyectoTimeNest/php/obtenerClientesDadosAlta.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarClientes(data.clientes);
            }
        })
        .catch(error => console.error('Error:', error));
}

function mostrarClientes(clientes) {
    const container = document.getElementById('clientesContainer');
    
    if (!clientes || clientes.length === 0) {
        container.innerHTML = '<p class="text-center text-muted">No hay clientes dados de alta</p>';
        return;
    }
    
    container.innerHTML = '';
    
    clientes.forEach(cliente => {
        const clienteCard = `
            <div class="cliente-card">
                <div class="cliente-info">
                    <span class="cliente-info-label">Nombre:</span>
                    <span class="cliente-info-valor">${cliente.NombreCliente} ${cliente.ApellidoCliente || ''}</span>
                </div>
                
                <div class="cliente-info">
                    <span class="cliente-info-label">Género:</span>
                    <span class="cliente-info-valor">${cliente.GeneroCliente}</span>
                </div>
                
                ${cliente.EdadCliente ? `
                <div class="cliente-info">
                    <span class="cliente-info-label">Edad:</span>
                    <span class="cliente-info-valor">${cliente.EdadCliente} años</span>
                </div>
                ` : ''}
                
                ${cliente.TelefonoCliente ? `
                <div class="cliente-info">
                    <span class="cliente-info-label">Teléfono:</span>
                    <span class="cliente-info-valor">${cliente.TelefonoCliente}</span>
                </div>
                ` : ''}
                
                ${cliente.CorreoCliente ? `
                <div class="cliente-info">
                    <span class="cliente-info-label">Correo:</span>
                    <span class="cliente-info-valor">${cliente.CorreoCliente}</span>
                </div>
                ` : ''}
                
                ${cliente.DomicilioCliente ? `
                <div class="cliente-info">
                    <span class="cliente-info-label">Domicilio:</span>
                    <span class="cliente-info-valor">${cliente.DomicilioCliente}</span>
                </div>
                ` : ''}
                
                <div class="cliente-acciones">
                    <button class="btn-editar-cliente" onclick="editarCliente(${cliente.IDClienteProfesional})">
                        Editar
                    </button>
                    <button class="btn-borrar-cliente" onclick="borrarCliente(${cliente.IDClienteProfesional})">
                        Borrar
                    </button>
                </div>
            </div>
        `;
        
        container.innerHTML += clienteCard;
    });
}

//aqui se edita el cliente
function editarCliente(id) {
    fetch(`/ProyectoTimeNest/php/obtenerCliente.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('editClienteId').value = id;
                document.getElementById('editNombre').value = data.cliente.NombreCliente;
                document.getElementById('editApellido').value = data.cliente.ApellidoCliente || '';
                document.getElementById('editGenero').value = data.cliente.GeneroCliente;
                document.getElementById('editEdad').value = data.cliente.EdadCliente || '';
                document.getElementById('editTelefono').value = data.cliente.TelefonoCliente || '';
                document.getElementById('editCorreo').value = data.cliente.CorreoCliente || '';
                document.getElementById('editDomicilio').value = data.cliente.DomicilioCliente || '';
                
                const modal = new bootstrap.Modal(document.getElementById('modalEditarCliente'));
                modal.show();
            }
        })
        .catch(error => console.error('Error:', error));
}
//aqui se guarda la edicion del cliente
function guardarEdicionCliente() {
    const datos = {
        id: document.getElementById('editClienteId').value,
        nombre: document.getElementById('editNombre').value.trim(),
        apellido: document.getElementById('editApellido').value.trim(),
        genero: document.getElementById('editGenero').value,
        edad: document.getElementById('editEdad').value,
        telefono: document.getElementById('editTelefono').value.trim(),
        correo: document.getElementById('editCorreo').value.trim(),
        domicilio: document.getElementById('editDomicilio').value.trim()
    };
    
    // Validar que tenga al menos teléfono o correo
    if (!datos.telefono && !datos.correo) {
        alert('Debe tener al menos teléfono o correo');
        return;
    }
    
    fetch('/ProyectoTimeNest/php/editarClienteProfesional.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Cliente actualizado exitosamente');
            bootstrap.Modal.getInstance(document.getElementById('modalEditarCliente')).hide();
            cargarClientes();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar el cliente');
    });
}
//borrar cliente
function borrarCliente(id) {
    if (!confirm('Estás seguro de eliminar este cliente?')) {
        return;
    }
    
    fetch('/ProyectoTimeNest/php/eliminarClienteProfesional.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Cliente eliminado exitosamente');
            cargarClientes();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar el cliente');
    });
}


function cancelarFormulario() {
    if (confirm('Deseas cancelar?')) {
        document.getElementById('formAltaCliente').reset();// Reiniciar el formulario
        document.getElementById('campoTelefono').style.display = 'none';// Ocultar campos de contacto
        document.getElementById('campoCorreo').style.display = 'none';
    }
}