
let filtroActual = null;

// aqui se establece la fecha maxima del input fecha como la fecha actual
document.addEventListener('DOMContentLoaded', function() {
    const hoy = new Date().toISOString().split('T')[0]; //toISOString convierte a formato YYYY-MM-DD
    //split separa la fecha y la hora, se toma solo la fecha y lo que esta entre parentesis que es la T, significa el separador entre fecha y hora
    document.getElementById('fecha-especifica').setAttribute('max', hoy);
});

function seleccionarFiltro(filtro) {
    // aqui se remueve active de todos los filtros
    document.querySelectorAll('.filtro-option').forEach(opt => {
        opt.classList.remove('active');
    });
    
    // Ocultar todos los inputs
    document.getElementById('input-dia').style.display = 'none';
    document.getElementById('input-mes').style.display = 'none';
    document.getElementById('input-genero').style.display = 'none';
    document.getElementById('input-edad').style.display = 'none';
    
    // aqui se agrega active al seleccionado
    event.currentTarget.classList.add('active');
    filtroActual = filtro;
    
    //aqui se muestra el input correspondiente
    if (filtro === 'dia') {
        document.getElementById('input-dia').style.display = 'block'; //style display block para mostrar el filtro
    } else if (filtro === 'mes') {
        document.getElementById('input-mes').style.display = 'block';
    } else if (filtro === 'genero') {
        document.getElementById('input-genero').style.display = 'block';
    } else if (filtro === 'edad') {
        document.getElementById('input-edad').style.display = 'block';
    }
}

function generarPDF() {
    if (!filtroActual) {
        alert('Por favor selecciona un filtro');
        return;
    }
    
    let params = `filtro=${filtroActual}`;
    
    // Obtener valor según filtro
    if (filtroActual === 'dia') {
        const fecha = document.getElementById('fecha-especifica').value;
        if (!fecha) {
            alert('Por favor selecciona una fecha');
            return;
        }
        params += `&fecha=${fecha}`;
    } else if (filtroActual === 'mes') {
        const mes = document.getElementById('mes-seleccionado').value;
        const anio = document.getElementById('anio-seleccionado').value;
        if (!mes) {
            alert('Por favor selecciona un mes');
            return;
        }
        params += `&mes=${mes}&anio=${anio}`;
    } else if (filtroActual === 'genero') {
        const genero = document.getElementById('genero-seleccionado').value;
        if (!genero) {
            alert('Por favor selecciona un género');
            return;
        }
        params += `&genero=${genero}`;
    } else if (filtroActual === 'edad') {
        const edad = document.getElementById('edad-especifica').value;
        if (!edad) {
            alert('Por favor ingresa una edad');
            return;
        }
        params += `&edad=${edad}`;
    }
    
    // Abrir PDF en nueva ventana
    window.open(`/ProyectoTimeNest/php/generarPDFClientes.php?${params}`, '_blank');
}