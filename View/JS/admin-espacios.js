document.addEventListener("DOMContentLoaded", () => {
    cargarConfiguracionActual();
    cargarEspaciosCreados();

    const form = document.getElementById("configForm");

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const data = {
            action: "crear_espacios",
            carros: form.carros.value,
            motos: form.motos.value,
            bicicletas: form.bicicletas.value,
            precioCarro: form.precioCarro.value,
            precioMoto: form.precioMoto.value,
            precioBici: form.precioBici.value,
        };

        try {
            const res = await fetch("../../Controller/ConfiguracionEspaciosController.php", {
                method: "POST",
                body: new FormData(form)
            });

            const resultado = await res.json();

            if (resultado.success) {
                alert("Configuración guardada con éxito.");
                form.reset();
                cargarEspaciosCreados();
            } else {
                alert("Error: " + resultado.error);
            }
        } catch (err) {
            alert("Error en la conexión con el servidor.");
            console.error(err);
        }
    });
});

async function cargarConfiguracionActual() {
    try {
        const res = await fetch("../../Controller/ConfiguracionEspaciosController.php?action=config_actual");
        const data = await res.json();

        const div = document.getElementById("configActual");
        div.innerHTML = `
            <div class="config-card">
                <h4>Carros</h4>
                <p><strong>Total:</strong> ${data.carros}</p>
                <p><strong>Precio/Hora:</strong> ${formatearCOP(data.precioCarro)}</p>
            </div>
            <div class="config-card">
                <h4>Motos</h4>
                <p><strong>Total:</strong> ${data.motos}</p>
                <p><strong>Precio/Hora:</strong> ${formatearCOP(data.precioMoto)}</p>
            </div>
            <div class="config-card">
                <h4>Bicicletas</h4>
                <p><strong>Total:</strong> ${data.bicicletas}</p>
                <p><strong>Precio/Hora:</strong> ${formatearCOP(data.precioBici)}</p>
            </div>
        `;
    } catch (err) {
        console.error("Error cargando configuración actual:", err);
    }
}


async function cargarEspaciosCreados() {
    try {
        const res = await fetch("../../Controller/ConfiguracionEspaciosController.php?action=listar_espacios");
        const espacios = await res.json();

        const tbody = document.querySelector("#tablaEspacios tbody");
        tbody.innerHTML = "";

        espacios.forEach(espacio => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${espacio.codigo}</td>
                <td>${espacio.tipo_vehiculo}</td>
                <td>${espacio.estado}</td>
                <td>${formatearCOP(espacio.precio_hora)}</td>
            `;
            tbody.appendChild(row);
        });
    } catch (err) {
        console.error("Error al cargar los espacios:", err);
    }
}

function formatearCOP(valor) {
    return new Intl.NumberFormat("es-CO", {
        style: "currency",
        currency: "COP",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(valor);
}

function mostrarModalEliminar() {
    document.getElementById("modalEliminar").style.display = "flex";
}

function cerrarModal(id) {
    document.getElementById(id).style.display = "none";
}

async function confirmarEliminacion() {
    const password = document.getElementById("adminPass").value.trim();

    if (password !== "admin") {
        alert("Contraseña incorrecta.");
        return;
    }

    try {
        const res = await fetch("../../Controller/ConfiguracionEspaciosController.php?action=eliminar_todo", {
            method: "POST"
        });

        const data = await res.json();

        if (data.success) {
            alert("Todos los espacios han sido eliminados.");
            cerrarModal("modalEliminar");
            cargarConfiguracionActual();
            cargarEspaciosCreados();
        } else {
            alert("Error: " + data.error);
        }
    } catch (err) {
        alert("Error de conexión con el servidor.");
    }
}
