// dashboard.js

// Espera a que el DOM cargue
document.addEventListener("DOMContentLoaded", () => {
    cargarEspacios();
    cargarVistaParqueadero();

    const filtro = document.getElementById("filtroTipo");
    filtro.addEventListener("change", () => cargarVistaParqueadero());

    // Recargar cada 10 segundos para ver cambios en tiempo real
    setInterval(() => cargarVistaParqueadero(), 10000);
});

async function cargarEspacios(filtro = "") {
    try {
        const res = await fetch("../../Controller/ConfiguracionEspaciosController.php");
        const espacios = await res.json();

        const contenedor = document.getElementById("contenedorEspacios");
        if (!contenedor) return;
        contenedor.innerHTML = "";

        espacios.forEach(espacio => {
            if (filtro && espacio.tipo_vehiculo !== filtro) return;

            const card = document.createElement("div");
            card.className = `espacio-card ${espacio.estado.toLowerCase()}`;
            card.innerHTML = `
                <h3>${espacio.codigo}</h3>
                <p><strong>Tipo:</strong> ${espacio.tipo_vehiculo}</p>
                <p><strong>Estado:</strong> <span class="badge ${espacio.estado.toLowerCase()}">${espacio.estado}</span></p>
                <p><strong>Precio/hora:</strong> $${parseInt(espacio.precio_hora).toLocaleString("es-CO")} COP</p>
            `;
            card.addEventListener("click", () => mostrarModalEspacio(espacio));
            contenedor.appendChild(card);
        });
    } catch (err) {
        console.error("Error al cargar espacios:", err);
    }
}

function mostrarModalEspacio(espacio) {
    const modal = document.getElementById("modalEspacio");
    const contenido = document.getElementById("modalEspacioContenido");

    contenido.innerHTML = `
        <h3>Detalles del espacio</h3>
        <p><strong>Código:</strong> ${espacio.codigo}</p>
        <p><strong>Tipo de vehículo:</strong> ${espacio.tipo_vehiculo}</p>
        <p><strong>Estado:</strong> ${espacio.estado}</p>
        <p><strong>Precio/hora:</strong> $${parseInt(espacio.precio_hora).toLocaleString("es-CO")} COP</p>
    `;

    modal.style.display = "flex";
}

function cerrarModalEspacio() {
    const modal = document.getElementById("modalEspacio");
    modal.style.display = "none";
}

async function cargarVistaParqueadero() {
    try {
        const res = await fetch("../../Controller/ConfiguracionEspaciosController.php?action=listar_espacios");
        const espacios = await res.json();

        const filtro = document.getElementById("filtroTipo").value;
        const contenedor = document.getElementById("vistaParqueadero");
        const resumenDiv = document.getElementById("resumenTotales");
        contenedor.innerHTML = "";

        const resumen = {
            total: espacios.length,
            disponibles: 0,
            ocupados: 0,
            carros: 0,
            motos: 0,
            bicicletas: 0
        };

        espacios
            .filter(e => filtro === "Todos" || e.tipo_vehiculo === filtro)
            .forEach(espacio => {
                const card = document.createElement("div");
                card.classList.add("espacio-card", espacio.estado.toLowerCase());

                card.innerHTML = `
                    <h3>${espacio.codigo}</h3>
                    <p><strong>Tipo:</strong> ${espacio.tipo_vehiculo}</p>
                    <p><strong>Estado:</strong> ${espacio.estado}</p>
                    <p><strong>Precio/Hora:</strong> $${parseInt(espacio.precio_hora).toLocaleString("es-CO")} COP</p>
                `;

                card.addEventListener("click", () => mostrarModalEspacio(espacio));

                contenedor.appendChild(card);
            });

        espacios.forEach(e => {
            if (e.estado === "Disponible") resumen.disponibles++;
            if (e.estado === "Ocupado") resumen.ocupados++;

            if (e.tipo_vehiculo === "Carro") resumen.carros++;
            if (e.tipo_vehiculo === "Moto") resumen.motos++;
            if (e.tipo_vehiculo === "Bicicleta") resumen.bicicletas++;
        });

        mostrarResumenTotales(resumen);

    } catch (error) {
        console.error("Error cargando espacios:", error);
    }
}

function mostrarResumenTotales(resumen) {
    const resumenDiv = document.getElementById("resumenTotales");
    if (!resumenDiv) return;
    resumenDiv.innerHTML = `
        <div class="resumen-box">
            <h4>Total espacios</h4>
            <p>${resumen.total}</p>
        </div>
        <div class="resumen-box">
            <h4>Disponibles</h4>
            <p style="color: green;">${resumen.disponibles}</p>
        </div>
        <div class="resumen-box">
            <h4>Ocupados</h4>
            <p style="color: red;">${resumen.ocupados}</p>
        </div>
        <div class="resumen-box">
            <h4>Carros</h4>
            <p>${resumen.carros}</p>
        </div>
        <div class="resumen-box">
            <h4>Motos</h4>
            <p>${resumen.motos}</p>
        </div>
        <div class="resumen-box">
            <h4>Bicicletas</h4>
            <p>${resumen.bicicletas}</p>
        </div>
    `;
}

var lista = document.querySelectorAll('.nav li');

function activarLink() {
    lista.forEach((item) => {
        item.classList.remove('active');
    });

    this.classList.add('active');
}

lista.forEach((item) => {
    item.addEventListener('mouseover', activarLink);
});

var toggle = document.querySelector('.toggle');
var nav = document.querySelector('.nav');
var container = document.querySelector('.container');

toggle.onclick = function () {
    nav.classList.toggle('active');
    container.classList.toggle('active');
}

function cargarVista(ruta) {
    fetch(ruta)
        .then(res => res.text())
        .then(html => {
            document.querySelector(".detalles").innerHTML = html;
        })
        .catch(err => {
            document.querySelector(".detalles").innerHTML = `<p style="color:red;">Error cargando la vista: ${err}</p>`;
        });
}

// Cerrar modal al hacer clic fuera del contenido
document.addEventListener("click", function (event) {
    const modal = document.getElementById("modalEspacio");
    const contenido = document.querySelector("#modalEspacio .modal-content");

    if (modal.style.display === "flex" && !contenido.contains(event.target) && !event.target.closest(".espacio-card")) {
        cerrarModalEspacio();
    }
});
