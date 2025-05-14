document.addEventListener("DOMContentLoaded", () => {
    const tipoVehiculo = document.getElementById("tipoVehiculo");
    const precio = document.getElementById("precio");
    const verEspacios = document.getElementById("verEspacios");
    const contenedorTablas = document.getElementById("tablasEspacios");

    tipoVehiculo.addEventListener("change", () => {
        switch (tipoVehiculo.value) {
            case "Carro":
                precio.value = "5000";
                break;
            case "Moto":
                precio.value = "3000";
                break;
            case "Bicicleta":
                precio.value = "1000";
                break;
            default:
                precio.value = "";
        }
    });

    verEspacios.addEventListener("click", () => {
        fetch("../Controller/ConsultarEspacios.php")
            .then(response => response.json())
            .then(data => {
                contenedorTablas.innerHTML = generarTablasHTML(data);
            })
            .catch(err => {
                contenedorTablas.innerHTML = `<p style="color:red;">Error al cargar espacios.</p>`;
            });
    });

    function generarTablasHTML(data) {
        const estados = ["Libres", "Reservados", "Ocupados"];
        let html = "<div class='tablas-container'>";
        estados.forEach(estado => {
            html += `<h3>${estado}</h3><table border="1"><tr><th>ID</th><th>Espacio</th></tr>`;
            data[estado.toLowerCase()].forEach(espacio => {
                html += `<tr><td>${espacio.id}</td><td>${espacio.nombre}</td></tr>`;
            });
            html += `</table>`;
        });
        html += "</div>";
        return html;
    }
});
