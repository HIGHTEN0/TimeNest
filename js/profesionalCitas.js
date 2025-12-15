//en esta clase se manejan las funcionalidades de las citas del profesional

let profesionalId = null;
let profesion = null;

document.addEventListener('DOMContentLoaded', function() {
    cargarDatosProfesional();
    cargarDatosCitas();
    configurarFechaMinima();
    cargarCitas();
    
    document.getElementById('formCrearCita').addEventListener('submit', crearCita);
});


function cargarDatosCitas() {
    fetch('/ProyectoTimeNest/php/obtenerDatosProfesional.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                profesionalId = data.id;
                profesion = data.profesion;
                
                document.getElementById('profesion').value = profesion || 'Profesional';
                
                // Cargar clientes y consultorios
                cargarClientes();
                cargarConsultorios();
            }
        })
        .catch(error => console.error('Error:', error));
}

function configurarFechaMinima() {
    const hoy = new Date().toISOString().split('T')[0];
    document.getElementById('fecha').min = hoy;
    document.getElementById('fecha').value = hoy;
    
    document.getElementById('fecha').addEventListener('change', function() {
        const fechaSeleccionada = this.value;
        const horaInicio = document.getElementById('horaInicio');
        
        if (fechaSeleccionada === hoy) {
            const ahora = new Date();
            const horaActual = ahora.getHours().toString().padStart(2, '0') + ':' + 
                              ahora.getMinutes().toString().padStart(2, '0');
            horaInicio.min = horaActual;
        } else {
            horaInicio.removeAttribute('min');
        }
    });
}


function cargarClientes() {
    fetch('/ProyectoTimeNest/php/obtenerClientesProfesional.php') 
        .then(response => response.json())
        .then(data => {
            //console.log('Clientes recibidos:', data); // DEBUG
            if (data.success) {
                const select = document.getElementById('clienteSelect');
                select.innerHTML = '<option value="">Seleccionar cliente...</option>';
                
                if (data.clientes && data.clientes.length > 0) {
                    data.clientes.forEach(cliente => {
                        const option = document.createElement('option');
                        option.value = cliente.IDClienteProfesional;
                        option.textContent = cliente.NombreCompleto;
                        select.appendChild(option);
                    });
                } else {
                    select.innerHTML = '<option value="">No hay clientes dados de alta</option>';
                }
            }
        })
        .catch(error => console.error('Error al cargar clientes:', error));
}



function cargarConsultorios() {
    fetch('/ProyectoTimeNest/php/obtenerConsultorios.php')
        .then(response => response.json())
        .then(data => {
            //console.log('Consultorios recibidos:', data); 
            if (data.success) {
                const select = document.getElementById('consultorio');
                select.innerHTML = '<option value="">Seleccionar consultorio...</option>';
                
                if (data.consultorios && data.consultorios.length > 0) {
                    data.consultorios.forEach(consultorio => {
                        const option = document.createElement('option');
                        option.value = consultorio.IDConsultorio;
                        option.textContent = consultorio.NombreConsultorio;
                        option.dataset.direccion = consultorio.Direccion || '';
                        select.appendChild(option);
                    });
                    
                    // Event listener para autocompletar dirección
                    select.addEventListener('change', function() {
                        const selectedOption = this.options[this.selectedIndex];
                        const direccion = selectedOption.dataset.direccion;
                        document.getElementById('ubicacion').value = direccion || 'Sin dirección especificada';
                    });
                } else {
                    select.innerHTML = '<option value="">No hay consultorios registrados</option>';
                }
            }
        })
        .catch(error => console.error('Error al cargar consultorios:', error));
}

function crearCita(e) {
    e.preventDefault();
    //aqui se obtienen los datos del formulario
    const formData = {
        fecha: document.getElementById('fecha').value,
        hora_inicio: document.getElementById('horaInicio').value,
        hora_fin: document.getElementById('horaFin').value,
        ubicacion: document.getElementById('ubicacion').value,
        cliente_id: document.getElementById('clienteSelect').value,
        consultorio_id: document.getElementById('consultorio').value
    };
    
    // Validaciones
    if (!formData.cliente_id) {
        alert('Selecciona un cliente');
        return;
    }
    
    if (!formData.consultorio_id) {
        alert('Por favor selecciona un consultorio');
        return;
    }
    
    if (formData.hora_inicio >= formData.hora_fin) {
        alert('La hora de fin debe ser posterior a la hora de inicio');
        return;
    }
    
    fetch('/ProyectoTimeNest/php/crearCita.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Cita creada exitosamente');
            document.getElementById('formCrearCita').reset();
            document.getElementById('ubicacion').value = '';
            configurarFechaMinima();
            cargarCitas();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al crear la cita');
    });
}


function cargarCitas() {
    fetch('/ProyectoTimeNest/php/obtenerCitas.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarCitasEnCurso(data.citas_curso);
                mostrarCitasFinalizadas(data.citas_finalizadas);
            }
        })
        .catch(error => console.error('Error:', error));
}


function mostrarCitasEnCurso(citas) {
    const container = document.getElementById('citasEnCurso');
    
    if (!citas || citas.length === 0) {
        container.innerHTML = '<p class="text-center text-muted">No hay citas en curso</p>';
        return;
    }
    
    container.innerHTML = '';
    
    citas.forEach(cita => {
        //aqui se crea la tarjeta de cada cita en curso
        const citaCard = `
            <div class="cita-card">
                <div class="cita-info">
                    <span class="cita-info-label">Cita para:</span>
                    <span class="cita-info-valor">${cita.NombreCompleto || cita.NombreCliente}</span>
                </div>
                
                <div class="cita-fecha">
                    <i class="fas fa-calendar"></i> ${formatearFecha(cita.DiaCita)}
                </div>
                
                <div class="cita-hora">
                    <span class="hora-badge">${cita.HoraInicial}</span>
                    <span>-</span>
                    <span class="hora-badge">${cita.HoraFinal}</span>
                </div>
                
                <div class="cita-info">
                    <span class="cita-info-label">con el:</span>
                    <span class="cita-info-valor">${profesion}</span>
                </div>
                
                <div class="cita-info">
                    <span class="cita-info-label">en el consultorio:</span>
                    <span class="cita-info-valor">${cita.NombreConsultorio || 'No especificado'}</span>
                </div>
                
                <div class="cita-ubicacion">
                    <i class="fas fa-map-marker-alt"></i> ${cita.Descripcion || 'Sin ubicación especificada'}
                </div>
                
                <div class="cita-acciones">
                    <button class="btn-editar" onclick="editarCita(${cita.IDCita})">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                    <button class="btn-finalizar" onclick="finalizarCita(${cita.IDCita})">
                        <i class="fas fa-check"></i> Finalizar
                    </button>
                    <button class="btn-cancelar" onclick="cancelarCita(${cita.IDCita})">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </div>
        `;
        
        container.innerHTML += citaCard;
    });
}


function mostrarCitasFinalizadas(citas) {
    const container = document.getElementById('citasFinalizadas');
    
    if (!citas || citas.length === 0) {
        container.innerHTML = '<p class="text-center text-muted">No hay citas finalizadas</p>';
        return;
    }
    
    container.innerHTML = '';
    
    citas.forEach(cita => {//forEach recorre cada cita en el array de citas
        //aqui se crea la tarjeta de cada cita finalizada
        const citaCard = `
            <div class="cita-card">
                <div class="cita-info">
                    <span class="cita-info-label">Cita para:</span>
                    <span class="cita-info-valor">${cita.NombreCompleto || cita.NombreCliente}</span>
                </div>
                
                <div class="cita-fecha">
                    <i class="fas fa-calendar"></i> ${formatearFecha(cita.DiaCita)}
                </div>
                
                <div class="cita-hora">
                    <span class="hora-badge">${cita.HoraInicial}</span>
                </div>
                
                <div class="cita-info">
                    <span class="cita-info-label">en el consultorio:</span>
                    <span class="cita-info-valor">${cita.NombreConsultorio || 'No especificado'}</span>
                </div>
                
                <div class="cita-acciones">
                    <button class="btn-borrar" onclick="eliminarCita(${cita.IDCita})">
                        <i class="fas fa-trash"></i> Borrar
                    </button>
                </div>
            </div>
        `;
        
        container.innerHTML += citaCard;
    });
}


function editarCita(id) {
    fetch(`/ProyectoTimeNest/php/obtenerCita.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('editCitaId').value = id;
                document.getElementById('editFecha').value = data.cita.DiaCita;
                document.getElementById('editHoraInicio').value = data.cita.HoraInicial;
                document.getElementById('editHoraFin').value = data.cita.HoraFinal;
                
                // Cargar consultorios en el select de edición
                cargarConsultoriosEdicion(data.cita.IDConsultorio);
                
                const modal = new bootstrap.Modal(document.getElementById('modalEditarCita'));
                modal.show();//modal es una clase de bootstrap que maneja los modales que son ventanas emergentes
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al obtener la cita');
        });
}

function cargarConsultoriosEdicion(consultorioSeleccionado) {
    fetch('/ProyectoTimeNest/php/obtenerConsultorios.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('editConsultorio');
                select.innerHTML = '<option value="">Seleccionar...</option>';
                
                data.consultorios.forEach(consultorio => {
                    const option = document.createElement('option');
                    option.value = consultorio.IDConsultorio;
                    option.textContent = consultorio.NombreConsultorio;
                    if (consultorio.IDConsultorio == consultorioSeleccionado) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Error:', error));
}

function guardarEdicionCita() {
    const datos = {
        id: document.getElementById('editCitaId').value,
        fecha: document.getElementById('editFecha').value,
        hora_inicio: document.getElementById('editHoraInicio').value,
        hora_fin: document.getElementById('editHoraFin').value,
        consultorio_id: document.getElementById('editConsultorio').value
    };
    
    // Validaciones
    if (!datos.fecha || !datos.hora_inicio || !datos.hora_fin) {
        alert('Completa todos los campos');
        return;
    }
    
    if (datos.hora_inicio >= datos.hora_fin) {
        alert('La hora de fin debe ser posterior a la hora de inicio');
        return;
    }
    
    fetch('/ProyectoTimeNest/php/editarCita.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json' // Indicar que se envía JSON
        },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {//then es para manejar la respuesta del servidor
        if (data.success) {
            alert('Cita actualizada exitosamente');
            bootstrap.Modal.getInstance(document.getElementById('modalEditarCita')).hide(); //getInstance obtiene la instancia del modal e hide lo cierra
            cargarCitas();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar la cita');
    });
}

function finalizarCita(idCita) {
    if (!confirm('Estás seguro de finalizar esta cita?')) {
        return;
    }
    
    fetch('/ProyectoTimeNest/php/finalizarCita.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: idCita })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Cita finalizada exitosamente');
            cargarCitas();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al finalizar la cita');
    });
}

// ============================================
// CANCELAR CITA
// ============================================

function cancelarCita(idCita) {
    if (!confirm('¿Estás seguro de cancelar esta cita?')) {
        return;
    }
    
    fetch('/ProyectoTimeNest/php/cancelarCita.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: idCita })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Cita cancelada exitosamente');
            cargarCitas();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cancelar la cita');
    });
}

// ============================================
// ELIMINAR CITA
// ============================================

function eliminarCita(idCita) {
    if (!confirm('¿Estás seguro de eliminar esta cita del historial? Esta acción no se puede deshacer.')) {
        return;
    }
    
    fetch('/ProyectoTimeNest/php/eliminarCita.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: idCita })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Cita eliminada exitosamente');
            cargarCitas();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar la cita');
    });
}

// ============================================
// UTILIDADES
// ============================================

function formatearFecha(fecha) {
    const opciones = { year: 'numeric', month: '2-digit', day: '2-digit' };
    return new Date(fecha + 'T00:00:00').toLocaleDateString('es-MX', opciones);
}