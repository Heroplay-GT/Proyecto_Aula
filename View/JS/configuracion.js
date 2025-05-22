document.addEventListener("DOMContentLoaded", () => {
    // Cerrar modal si se hace click fuera del contenido
    const modales = document.querySelectorAll(".modal");
    modales.forEach(modal => {
        modal.addEventListener("click", e => {
            if (e.target === modal) {
                cerrarModal(modal.id);
            }
        });
    });

    // Formulario actualizar datos
    const formDatos = document.getElementById("formActualizarDatos");

    formDatos.addEventListener("submit", async (e) => {
        e.preventDefault();

        const passInput = formDatos.querySelector('input[name="password"]');
        const nombreInput = formDatos.querySelector('input[name="nombre"]');

        if (!passInput || passInput.value.trim() === "" || !nombreInput || nombreInput.value.trim() === "") {
            alert("Debes ingresar tu nombre y contraseña actual.");
            return;
        }

        const formData = new FormData(formDatos);
        formData.append("action", "actualizar_perfil");

        try {
            const response = await fetch("../../Controller/ClienteController.php", {
                method: "POST",
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                alert("Datos actualizados correctamente.");
                cerrarModal("modalDatos");
                location.reload();
            } else {
                alert("Error: " + result.error);
            }
        } catch (error) {
            console.error(error);
            alert("Error de conexión con el servidor.");
        }
    });

    // Formulario actualizar correo
    const formCorreo = document.getElementById("formActualizarCorreo");
    formCorreo.addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(formCorreo);
        formData.append("action", "actualizar_correo");

        const response = await enviarFormulario(formData);
        if (response.success) {
            alert("Correo actualizado correctamente.");
            cerrarModal("modalCorreo");
            location.reload();
        } else {
            alert("Error: " + response.error);
        }
    });

    // Formulario cambiar contraseña
    const formContrasena = document.getElementById("formCambiarContrasena");
    formContrasena.addEventListener("submit", async (e) => {
        e.preventDefault();

        const passNueva = formContrasena.nueva.value.trim();
        const passConfirmar = formContrasena.confirmar.value.trim();
        if (passNueva !== passConfirmar) {
            alert("La nueva contraseña y su confirmación no coinciden.");
            return;
        }

        const formData = new FormData(formContrasena);
        formData.append("action", "actualizar_contrasena");

        const response = await enviarFormulario(formData);
        if (response.success) {
            alert("Contraseña cambiada correctamente.");
            cerrarModal("modalContrasena");
            formContrasena.reset();
        } else {
            alert("Error: " + response.error);
        }
    });

});

// Función para mostrar modal
function mostrarModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.display = "flex";
    }
}

// Función para cerrar modal
function cerrarModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.display = "none";
    }
}

// Función para confirmar eliminación
async function confirmarEliminacion() {
    const pass = prompt("Para eliminar tu cuenta, por favor ingresa tu contraseña:");
    if (!pass) {
        alert("Contraseña requerida para eliminar cuenta.");
        return;
    }
    // Preparo datos para enviar
    const formData = new FormData();
    formData.append("action", "eliminar_cuenta");
    formData.append("pass_confirmar", pass);

    const response = await enviarFormulario(formData);
    if (response.success) {
        alert("Cuenta eliminada correctamente. Se cerrará la sesión.");
        // Redirigir a Home o login después de eliminar cuenta
        window.location.href = "../../Home.php";
    } else {
        alert("Error: " + response.error);
    }
}

// Función genérica para enviar formularios por fetch
async function enviarFormulario(formData) {
    try {
        const response = await fetch("../../Controller/ClienteController.php", {
            method: "POST",
            body: formData
        });
        const data = await response.json();
        return data;
    } catch (error) {
        return { success: false, error: "Error de conexión con el servidor." };
    }
}

