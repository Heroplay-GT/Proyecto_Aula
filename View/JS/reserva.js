document.addEventListener("DOMContentLoaded", () => {
    const tipoVehiculo = document.getElementById("tipoVehiculo");
    const precio = document.getElementById("precio");
    const verEspacios = document.getElementById("verEspacios");
    const contenedorTablas = document.getElementById("tablasEspacios");

    // Manejar cambio de tipo de vehículo
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

    // Manejar clic en ver espacios
    verEspacios.addEventListener("click", () => {
        fetch("../Controller/ConsultarEspacios.php")
            .then(response => {
                if (!response.ok) throw new Error("Error en la red");
                return response.json();
            })
            .then(data => {
                contenedorTablas.innerHTML = generarTablasHTML(data);
            })
            .catch(err => {
                console.error("Error:", err);
                mostrarMensaje('error', 'Error al cargar espacios');
            });
    });

    // Función para generar tablas HTML
    function generarTablasHTML(data) {
        const estados = ["Libres", "Reservados", "Ocupados"];
        let html = "<div class='tablas-container'>";
        
        estados.forEach(estado => {
            if(data[estado.toLowerCase()] && data[estado.toLowerCase()].length > 0) {
                html += `<h3>${estado}</h3><table border="1"><tr><th>ID</th><th>Espacio</th></tr>`;
                data[estado.toLowerCase()].forEach(espacio => {
                    html += `<tr><td>${espacio.id}</td><td>${espacio.nombre}</td></tr>`;
                });
                html += `</table>`;
            }
        });
        
        html += "</div>";
        return html;
    }

    // Función para mostrar mensajes
    function mostrarMensaje(tipo, mensaje) {
        const elemento = document.getElementById(`${tipo}-message`);
        if(elemento) {
            elemento.textContent = mensaje;
            elemento.style.display = 'block';
            
            setTimeout(() => {
                elemento.style.display = 'none';
            }, 3000);
        }
    }
});