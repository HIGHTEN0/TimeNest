//esta clase es para el registro de usuarios

//cambio de formulario según rol seleccionado
function cambiarFormulario() { // Llamar a esta función cuando se cambie la selección de rol
    const role = document.querySelector('input[name="role"]:checked').value; // Obtener el rol seleccionado
    
    const camposCliente = document.getElementById('campos-cliente');//div de campos cliente
    const camposProfesional = document.getElementById('campos-profesional');//div de campos profesional
    
    const inputProfesion = document.querySelector('input[name="profesion"]');//input profesion
    const inputEmailProf = document.querySelector('input[name="email_prof"]');//input email profesional
    const inputPasswordProf = document.querySelector('input[name="password_prof"]');//input password profesional
    const inputConfirmProf = document.querySelector('input[name="confirm_password_prof"]');//input confirmar password
    
    if (role === 'Profesional') {
        // Mostrar campos de profesional
        camposCliente.style.display = 'none'; // Ocultar campos de cliente
        camposProfesional.style.display = 'block'; // Mostrar campos de profesional
        
        // Hacer campos de profesional obligatorios
        if (inputProfesion) inputProfesion.required = true;
        if (inputEmailProf) inputEmailProf.required = true;
        if (inputPasswordProf) inputPasswordProf.required = true;
        if (inputConfirmProf) inputConfirmProf.required = true;
        
        // Hacer campos de cliente opcionales
        document.querySelector('input[name="email"]').required = false;
        document.querySelector('input[name="password"]').required = false;
        document.querySelector('input[name="confirm_password"]').required = false;
        document.querySelector('input[name="telefono"]').required = false;
        
    } else {
        // Mostrar campos de cliente
        camposCliente.style.display = 'block'; // Mostrar campos de cliente
        camposProfesional.style.display = 'none'; // Ocultar campos de profesional
        
        // Hacer campos de cliente obligatorios
        document.querySelector('input[name="email"]').required = true;
        document.querySelector('input[name="password"]').required = true;
        document.querySelector('input[name="confirm_password"]').required = true;
        document.querySelector('input[name="telefono"]').required = true;
        
        // Hacer campos de profesional opcionales
        if (inputProfesion) inputProfesion.required = false;
        if (inputEmailProf) inputEmailProf.required = false;
        if (inputPasswordProf) inputPasswordProf.required = false;
        if (inputConfirmProf) inputConfirmProf.required = false;
    }
}

//foto de perfil
document.getElementById("input-foto").addEventListener("change", function (e) { // Manejo de la foto de perfil
  const file = e.target.files[0]; // Obtener el archivo seleccionado. target.files es una lista de archivos
  if (file) {
    if (file.size > 2 * 1024 * 1024) { // Validar tamaño máximo de 2MB
      alert("La imagen es muy grande. Máximo 2MB.");
      this.value = "";
      return;
    }

    if (!file.type.startsWith("image/")) { // Validar que sea una imagen
      alert("Solo se permiten imágenes.");
      this.value = "";
      return;
    }

    const reader = new FileReader(); // Leer el archivo
    reader.onload = function (e) {
      document.getElementById("preview-foto").src = e.target.result; // Mostrar preview
      document.getElementById("foto_base64").value = e.target.result; // Guardar en base64
    };
    reader.readAsDataURL(file); // Convertir a base64
  }
});

//logo
document.getElementById("input-logo").addEventListener("change", function (e) {
  const file = e.target.files[0];//const file es el archivo seleccionado
  if (file) {
    if (file.size > 2 * 1024 * 1024) { // Validar tamaño máximo
      alert("La imagen es muy grande. Máximo 2MB.");
      this.value = "";
      return;
    }

    if (!file.type.startsWith("image/")) { // Validar tipo de archivo
      alert("Solo se permiten imágenes.");
      this.value = "";
      return;
    }

    const reader = new FileReader(); // Leer archivo
    reader.onload = function (e) {
      document.getElementById("preview-logo").src = e.target.result; // Mostrar preview del logo
      document.getElementById("logo_base64").value = e.target.result; // Guardar en base64
    };
    reader.readAsDataURL(file); // Convertir a base64
  }
});

//validacion de contraseñas
document
  .getElementById("registroForm") //selecciona el formulario de registro
  .addEventListener("submit", function (e) {
    const role = document.querySelector('input[name="role"]:checked').value; // Obtener rol seleccionado
    
    let password, confirm; // Variables para las contraseñas
    
    // Obtener contraseñas según el rol
    if (role === 'Cliente') {
        password = document.querySelector('input[name="password"]').value; //obtener valor de la contraseña
        confirm = document.querySelector('input[name="confirm_password"]').value;
    } else {
        password = document.querySelector('input[name="password_prof"]').value;
        confirm = document.querySelector('input[name="confirm_password_prof"]').value;
    }

    if (password !== confirm) { // Validar que las contraseñas coincidan
      e.preventDefault(); // Prevenir envío del formulario
      alert("Las contraseñas no coinciden");
      return false;
    }

    if (password.length < 6) { // Validar longitud mínima
      e.preventDefault();
      alert("La contraseña debe tener al menos 6 caracteres");
      return false;
    }
  });

// Validar email en tiempo real
document.querySelectorAll('input[type="email"]').forEach(input => { // Seleccionar todos los inputs de email
    input.addEventListener('blur', function() { // Evento cuando se pierde el foco
        const email = this.value.trim();//.trim es un método que elimina los espacios en blanco
        const dominiosPermitidos = [ // Lista de dominios permitidos
            'gmail.com',
            'outlook.com',
            'hotmail.com',
            'yahoo.com',
            'icloud.com'
        ];
        
        if (email) {
            const partes = email.split('@');// Dividir el email en partes usando '@' como separador
            //.split es un método que divide una cadena en un array de subcadenas
            if (partes.length === 2) {// Verificar que haya exactamente una '@'
                const dominio = partes[1].toLowerCase();// Obtener la parte del dominio y convertir a minúsculas
                if (!dominiosPermitidos.includes(dominio)) { // Verificar si el dominio está en la lista
                    this.setCustomValidity('Por favor usa un correo de: ' + dominiosPermitidos.join(', '));// Establecer mensaje de error personalizado
                    this.reportValidity(); // Mostrar el mensaje de error
                } else {
                    this.setCustomValidity(''); // Limpiar el mensaje de error
                }
            }
        }
    });
});