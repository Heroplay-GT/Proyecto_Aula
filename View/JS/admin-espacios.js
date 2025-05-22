// admin-espacios.js

// Al cargar la página, obtiene los espacios existentes y los muestra
document.addEventListener("DOMContentLoaded", () => {
    cargarEspacios();

    const form = document.getElementById("configForm");
    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const formData = new FormData(form);
        const response = await fetch("../../Controller/AdminEspaciosController.php", {
            method: "POST",
            body: formData,
        });

        const result = await response.json();

        if (result.success) {
            alert("Espacios configurados exitosamente");
            form.reset();
            cargarEspacios();
        } else {
            alert("Error: " + result.error);
        }
    });
});

// Cargar los espacios desde el servidor y mostrarlos en la tabla
async function cargarEspacios() {
    const tabla = document.querySelector("#tablaEspacios tbody");
    tabla.innerHTML = "<tr><td colspan='4'>Cargando...</td></tr>";

    try {
        const response = await fetch("../../Controller/AdminEspaciosController.php?action=obtener");
        const data = await response.json();

        if (data.success) {
            tabla.innerHTML = "";
            if (data.espacios.length === 0) {
                tabla.innerHTML = "<tr><td colspan='4'>No hay espacios registrados.</td></tr>";
                return;
            }

            data.espacios.forEach(e => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${e.codigo}</td>
                    <td>${e.tipo_vehiculo}</td>
                    <td>${e.estado}</td>
                    <td>$${parseFloat(e.precio_hora).toLocaleString()}</td>
                `;
                tabla.appendChild(row);
            });
        } else {
            tabla.innerHTML = `<tr><td colspan='4'>${data.error}</td></tr>`;
        }
    } catch (error) {
        console.error("Error cargando espacios:", error);
        tabla.innerHTML = "<tr><td colspan='4'>Error al cargar espacios</td></tr>";
    }
}
