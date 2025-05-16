document.addEventListener("DOMContentLoaded", () => {
    // Elementos del DOM
    const tipoVehiculo = document.getElementById("tipoVehiculo");
    const precioInput = document.getElementById("precio");
    const verEspaciosBtn = document.getElementById("verEspacios");
    const selectEspacio = document.getElementById("selectEspacio");
    const modal = document.getElementById("espaciosModal");
    const closeModal = document.querySelector(".close-modal");
    const contenidoEspacios = document.getElementById("contenidoEspacios");
    const filtroTipo = document.getElementById("filtroTipo");
    const placaInput = document.querySelector('input[name="placa"]');
    const reservaForm = document.getElementById("reservaForm");

    // Event Listeners
    tipoVehiculo.addEventListener("change", function () {
        actualizarRequerimientoPlaca();
        cargarEspaciosDisponibles();
    });

    verEspaciosBtn.addEventListener("click", mostrarModalEspacios);
    selectEspacio.addEventListener("change", actualizarPrecio);
    closeModal.addEventListener("click", cerrarModal);
    filtroTipo.addEventListener("change", cargarEspaciosModal);

    // Cerrar modal haciendo clic fuera del contenido
    window.addEventListener("click", (e) => {
        if (e.target === modal) cerrarModal();
    });

    // Función para actualizar requerimiento de placa
    function actualizarRequerimientoPlaca() {
        if (tipoVehiculo.value === 'Bicicleta') {
            placaInput.removeAttribute('required');
            placaInput.placeholder = 'Opcional (ej: ABC123)';
        } else {
            placaInput.setAttribute('required', '');
            placaInput.placeholder = '';
        }
    }

    function cerrarModal() {
        modal.style.display = "none";
    }

    function mostrarModalEspacios() {
        modal.style.display = "block";
        cargarEspaciosModal();
    }

    function cargarEspaciosModal() {
        const tipo = filtroTipo.value;
        contenidoEspacios.innerHTML = '<p class="loading">Cargando espacios...</p>';

        fetch(`../../Controller/ReservaController.php?todos_los_espacios=1&tipo=${tipo === 'all' ? '' : tipo}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) throw new Error(data.error);
                contenidoEspacios.innerHTML = data.data.length > 0
                    ? generarCardsEspacios(data.data)
                    : '<p class="no-espacios">No hay espacios disponibles</p>';
                agregarEventosCards();
            })
            .catch(err => {
                console.error("Error:", err);
                contenidoEspacios.innerHTML = `<p class="error">Error al cargar espacios: ${err.message}</p>`;
            });
    }

    function generarCardsEspacios(espacios) {
        return espacios.map(espacio => {
            let claseEstado = espacio.estado.toLowerCase();
            let textoEstado = espacio.estado;

            return `
            <div class="espacio-card ${claseEstado}" 
                 data-id="${espacio.id}" 
                 data-codigo="${espacio.codigo}" 
                 data-precio="${espacio.precio_hora}"
                 data-tipo="${espacio.tipo_vehiculo}"
                 data-estado="${espacio.estado}">
                <h3>${espacio.codigo}</h3>
                <p><strong>Tipo:</strong> ${espacio.tipo_vehiculo}</p>
                <p><strong>Estado:</strong> ${textoEstado}</p>
                <p><strong>Precio:</strong> $${espacio.precio_hora.toLocaleString('es-CO')}/h</p>
            </div>
            `;
        }).join('');
    }

    function cargarEspaciosDisponibles() {
        const tipo = tipoVehiculo.value;
        if (!tipo) {
            selectEspacio.innerHTML = '<option value="" disabled selected hidden>Seleccione tipo primero</option>';
            selectEspacio.disabled = true;
            precioInput.value = "";
            return;
        }

        selectEspacio.innerHTML = '<option value="" disabled selected hidden>Cargando espacios...</option>';
        selectEspacio.disabled = true;
        precioInput.value = "";

        fetch(`../../Controller/ReservaController.php?tipo_vehiculo=${encodeURIComponent(tipo)}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) throw new Error(data.error);

                selectEspacio.innerHTML = '<option value="" disabled selected hidden>Seleccione espacio</option>';

                if (data.data && data.data.length > 0) {
                    // Mostrar el precio del primer espacio disponible como referencia
                    if (data.data[0].precio_hora) {
                        precioInput.value = `$${data.data[0].precio_hora.toLocaleString('es-CO')}/hora`;
                    }

                    data.data.forEach(espacio => {
                        const option = new Option(
                            `${espacio.codigo}`,
                            espacio.id
                        );
                        option.dataset.precio = espacio.precio_hora;
                        selectEspacio.add(option);
                    });
                    selectEspacio.disabled = false;
                } else {
                    selectEspacio.innerHTML = '<option value="" disabled selected hidden>No hay espacios disponibles</option>';
                }
            })
            .catch(err => console.error("Error:", err));
    }

    function agregarEventosCards() {
        document.querySelectorAll('.espacio-card').forEach(card => {
            card.addEventListener('click', function () {
                if (this.dataset.estado === 'Disponible') {
                    // Actualizar tipo de vehículo si es diferente
                    if (tipoVehiculo.value !== this.dataset.tipo) {
                        tipoVehiculo.value = this.dataset.tipo;
                        actualizarRequerimientoPlaca();
                    }

                    // Seleccionar el espacio
                    selectEspacio.innerHTML = '';
                    const option = new Option(
                        `${this.dataset.codigo}`,
                        this.dataset.id
                    );
                    option.dataset.precio = this.dataset.precio;
                    selectEspacio.add(option);
                    selectEspacio.value = this.dataset.id;
                    selectEspacio.disabled = false;

                    // Actualizar precio
                    precioInput.value = `$${parseFloat(this.dataset.precio).toLocaleString('es-CO')}/hora`;

                    // Cerrar modal
                    cerrarModal();
                } else {
                    mostrarMensaje('error', 'Solo puedes seleccionar espacios disponibles');
                }
            });
        });
    }

    function actualizarPrecio() {
        const selectedOption = selectEspacio.options[selectEspacio.selectedIndex];
        if (selectedOption && selectedOption.dataset.precio) {
            precioInput.value = `$${parseFloat(selectedOption.dataset.precio).toLocaleString('es-CO')}/hora`;
        }
    }

    function mostrarMensaje(tipo, mensaje) {
        const elemento = document.getElementById(`${tipo}-message`);
        if (elemento) {
            elemento.textContent = mensaje;
            elemento.style.display = 'block';
            setTimeout(() => elemento.style.display = 'none', 5000);
        }
    }

    // Inicializar requerimiento de placa al cargar
    actualizarRequerimientoPlaca();

    // Añadir al final del reserva.js
    reservaForm.addEventListener("submit", function (e) {
        // Validar que se haya seleccionado un espacio
        if (!selectEspacio.value || selectEspacio.disabled) {
            e.preventDefault();
            mostrarMensaje('error', 'Debe seleccionar un espacio válido');
            selectEspacio.focus();
            return;
        }

        // Validar placa solo si no es bicicleta
        if (tipoVehiculo.value !== 'Bicicleta') {
            const placa = placaInput.value.trim();
            if (!placa) {
                e.preventDefault();
                mostrarMensaje('error', 'La placa es requerida para este tipo de vehículo');
                placaInput.focus();
                return;
            }
        }

        // Si es bicicleta y no tiene placa, asignar valor por defecto
        if (tipoVehiculo.value === 'Bicicleta' && !placaInput.value.trim()) {
            placaInput.value = 'BIC-' + Math.random().toString(36).substr(2, 6).toUpperCase();
        }
    });
});