//esta clase es para que el profesional pueda generar listados de citas en PDF segun filtros seleccionados

let filtroActual = null;

document.addEventListener("DOMContentLoaded", function () {
  const hoy = new Date().toISOString().split("T")[0];
  document.getElementById("fecha-finalizadas").setAttribute("max", hoy);
  document.getElementById("fecha-realizar").setAttribute("min", hoy);

  cargarClientes();
  cargarConsultorios();
});

function seleccionarFiltro(filtro) {
  // aqui se desactivan todos los filtros
  document.querySelectorAll(".filtro-option").forEach((opt) => {
    opt.classList.remove("active");
  });

  //aqui se ocultan todos los inputs de filtro
  //los input son los campos donde el profesional ingresa los datos para filtrar
  document.querySelectorAll(".input-filtro").forEach((input) => {
    input.style.display = "none";
  });

  // Activar el filtro seleccionado
  event.currentTarget.classList.add("active");
  filtroActual = filtro;

  // aqui se muestra el input correspondiente al filtro seleccionado
  const inputId = "input-" + filtro;
  const inputElement = document.getElementById(inputId);
  if (inputElement) {
    inputElement.style.display = "block";
  }
}

//en esta parte se cargan los clientes y consultorios para los filtros que los requieren
function cargarClientes() {
  fetch("/ProyectoTimeNest/php/obtenerClientesProfesional.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const selectFinalizadas = document.getElementById(
          "select-cliente-finalizadas"
        );
        const selectRealizar = document.getElementById(
          "select-cliente-realizar"
        );

        let options = '<option value="">Seleccionar cliente...</option>';
        data.clientes.forEach((cliente) => {
          options += `<option value="${cliente.IDClienteProfesional}">${
            cliente.NombreCliente
          } ${cliente.ApellidoCliente || ""}</option>`;
        });

        selectFinalizadas.innerHTML = options;//innerHTML es para insertar opciones en el select
        selectRealizar.innerHTML = options;
      }
    })
    .catch((error) => console.error("Error:", error));
}

//aqui se cargan los consultorios
function cargarConsultorios() {
  fetch("/ProyectoTimeNest/php/obtenerConsultorios.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const selectFinalizadas = document.getElementById(
          "select-consultorio-finalizadas"
        );
        const selectRealizar = document.getElementById(
          "select-consultorio-realizar"
        );

        let options = '<option value="">Seleccionar consultorio...</option>'; //opcion por defecto
        data.consultorios.forEach((consultorio) => {
          options += `<option value="${consultorio.IDConsultorio}">${consultorio.NombreConsultorio}</option>`;
        });

        selectFinalizadas.innerHTML = options;
        selectRealizar.innerHTML = options;//innerHTML es para insertar opciones en el select
      }
    })
    .catch((error) => console.error("Error:", error));
}
//aqui se genera el PDF segun los filtros seleccionados
function generarPDF() {
  if (!filtroActual) {
    alert("selecciona un filtro");
    return;
  }

  let params = `filtro=${filtroActual}`;

  // aqui se obtienen los valores de los inputs segun el filtro seleccionado
  if (filtroActual === "dia-finalizadas") {
    const fecha = document.getElementById("fecha-finalizadas").value;
    if (!fecha) {
      alert("selecciona una fecha");
      return;
    }
    params += `&fecha=${fecha}`;//& es para concatenar parametros en la URL

  } else if (filtroActual === "dia-realizar") {
    const fecha = document.getElementById("fecha-realizar").value;
    if (!fecha) {
      alert("selecciona una fecha");
      return;
    }
    params += `&fecha=${fecha}`;//aqui se agrega el parametro fecha a la URL

  } else if (filtroActual === "mes-finalizadas") {
    const mes = document.getElementById("mes-finalizadas").value;
    const año = document.getElementById("año-finalizadas").value;
    if (!mes) {
      alert("Por favor selecciona un mes");
      return;
    }
    params += `&mes=${mes}&año=${año}`;
  } else if (filtroActual === "mes-realizar") {
    const mes = document.getElementById("mes-realizar").value;
    const año = document.getElementById("año-realizar").value;
    if (!mes) {
      alert("selecciona un mes");
      return;
    }
    params += `&mes=${mes}&año=${año}`;
  } else if (filtroActual === "cliente-finalizadas") {
    const cliente = document.getElementById("select-cliente-finalizadas").value;
    if (!cliente) {
      alert("Por favor selecciona un cliente");
      return;
    }
    params += `&cliente=${cliente}`;
  } else if (filtroActual === "cliente-realizar") {
    const cliente = document.getElementById("select-cliente-realizar").value;
    if (!cliente) {
      alert("Por favor selecciona un cliente");
      return;
    }
    params += `&cliente=${cliente}`;
  } else if (filtroActual === "consultorio-finalizadas") {
    const consultorio = document.getElementById(
      "select-consultorio-finalizadas"
    ).value;
    if (!consultorio) {
      alert("selecciona un consultorio");
      return;
    }
    params += `&consultorio=${consultorio}`;
  } else if (filtroActual === "consultorio-realizar") {
    const consultorio = document.getElementById(
      "select-consultorio-realizar"
    ).value;
    if (!consultorio) {
      alert("Por favor selecciona un consultorio");
      return;
    }
    params += `&consultorio=${consultorio}`;
  }

  window.open(`/ProyectoTimeNest/php/generarPDFCitas.php?${params}`, "_blank"); //winsow.open abre una nueva pestaña con la URL generada
}
