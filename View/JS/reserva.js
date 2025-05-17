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

    // URL base corregida
    const baseUrl = window.location.origin + '/Proyecto_Aula';

    // Event Listeners
    tipoVehiculo.addEventListener("change", function() {
        actualizarRequerimientoPlaca();
        cargarEspaciosDisponibles();
    });

    verEspaciosBtn.addEventListener("click", mostrarModalEspacios);
    selectEspacio.addEventListener("change", actualizarPrecio);
    closeModal.addEventListener("click", cerrarModal);
    filtroTipo.addEventListener("change", cargarEspaciosModal);

    window.addEventListener("click", (e) => e.target === modal && cerrarModal());

    // Funciones principales
    function actualizarRequerimientoPlaca() {
        const isBicicleta = tipoVehiculo.value === 'Bicicleta';
        placaInput.toggleAttribute('required', !isBicicleta);
        placaInput.placeholder = isBicicleta ? 'Opcional (ej: ABC123)' : '';
    }

    function mostrarModalEspacios() {
        modal.style.display = "block";
        cargarEspaciosModal();
    }

    function cerrarModal() {
        modal.style.display = "none";
    }

    async function cargarEspaciosModal() {
        const tipo = filtroTipo.value;
        contenidoEspacios.innerHTML = '<p class="loading">Cargando espacios...</p>';

        try {
            const url = `${baseUrl}/Controller/ReservaController.php?todos_los_espacios=1${tipo !== 'all' ? `&tipo=${tipo}` : ''}`;
            const response = await fetch(url);
            
            if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);
            
            const data = await response.json();
            
            if (!data.success) throw new Error(data.error || "Error en los datos recibidos");
            
            contenidoEspacios.innerHTML = data.data.length > 0 
                ? generarCardsEspacios(data.data) 
                : '<p class="no-espacios">No hay espacios disponibles</p>';
            
            agregarEventosCards();
        } catch (error) {
            console.error("Error:", error);
            contenidoEspacios.innerHTML = `
                <div class="error">
                    <p>Error al cargar espacios</p>
                    <p><small>${error.message}</small></p>
                    <button onclick="cargarEspaciosModal()">Reintentar</button>
                </div>`;
        }
    }

    function generarCardsEspacios(espacios) {
        return espacios.map(espacio => `
            <div class="espacio-card ${espacio.estado.toLowerCase()}" 
                 data-id="${espacio.id}" 
                 data-codigo="${espacio.codigo}" 
                 data-precio="${espacio.precio_hora}"
                 data-tipo="${espacio.tipo_vehiculo}"
                 data-estado="${espacio.estado}">
                <h3>${espacio.codigo}</h3>
                <p><strong>Tipo:</strong> ${espacio.tipo_vehiculo}</p>
                <p><strong>Estado:</strong> ${espacio.estado}</p>
                <p><strong>Precio:</strong> $${espacio.precio_hora.toLocaleString('es-CO')}/h</p>
            </div>
        `).join('');
    }

    async function cargarEspaciosDisponibles() {
        const tipo = tipoVehiculo.value;
        if (!tipo) {
            resetSelectEspacio();
            return;
        }

        resetSelectEspacio('Cargando espacios...');
        
        try {
            const response = await fetch(`${baseUrl}/Controller/ReservaController.php?tipo_vehiculo=${encodeURIComponent(tipo)}`);
            const data = await response.json();
            
            if (data.error) throw new Error(data.error);
            
            selectEspacio.innerHTML = '<option value="" disabled selected hidden>Seleccione espacio</option>';
            
            if (data.data?.length > 0) {
                data.data.forEach(espacio => {
                    const option = new Option(espacio.codigo, espacio.id);
                    option.dataset.precio = espacio.precio_hora;
                    selectEspacio.add(option);
                });
                selectEspacio.disabled = false;
                if (data.data[0].precio_hora) {
                    precioInput.value = `$${data.data[0].precio_hora.toLocaleString('es-CO')}/hora`;
                }
            } else {
                resetSelectEspacio('No hay espacios disponibles');
            }
        } catch (error) {
            console.error("Error:", error);
            resetSelectEspacio('Error al cargar');
        }
    }

    function resetSelectEspacio(text = 'Seleccione tipo primero') {
        selectEspacio.innerHTML = `<option value="" disabled selected hidden>${text}</option>`;
        selectEspacio.disabled = true;
        precioInput.value = "";
    }

    function agregarEventosCards() {
        document.querySelectorAll('.espacio-card').forEach(card => {
            card.addEventListener('click', function() {
                if (this.dataset.estado !== 'Disponible') {
                    mostrarMensaje('error', 'Solo puedes seleccionar espacios disponibles');
                    return;
                }

                // Actualizar tipo de vehículo si es diferente
                if (tipoVehiculo.value !== this.dataset.tipo) {
                    tipoVehiculo.value = this.dataset.tipo;
                    actualizarRequerimientoPlaca();
                }

                // Actualizar select de espacios
                selectEspacio.innerHTML = '';
                const option = new Option(this.dataset.codigo, this.dataset.id);
                option.dataset.precio = this.dataset.precio;
                selectEspacio.add(option);
                selectEspacio.value = this.dataset.id;
                selectEspacio.disabled = false;

                // Actualizar precio
                precioInput.value = `$${parseFloat(this.dataset.precio).toLocaleString('es-CO')}/hora`;
                cerrarModal();
            });
        });
    }

    function actualizarPrecio() {
        const selectedOption = selectEspacio.options[selectEspacio.selectedIndex];
        if (selectedOption?.dataset.precio) {
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

    // Validación del formulario
    reservaForm.addEventListener("submit", function(e) {
        if (!selectEspacio.value || selectEspacio.disabled) {
            e.preventDefault();
            mostrarMensaje('error', 'Debe seleccionar un espacio válido');
            selectEspacio.focus();
            return;
        }

        if (tipoVehiculo.value !== 'Bicicleta' && !placaInput.value.trim()) {
            e.preventDefault();
            mostrarMensaje('error', 'La placa es requerida para este tipo de vehículo');
            placaInput.focus();
            return;
        }

        if (tipoVehiculo.value === 'Bicicleta' && !placaInput.value.trim()) {
            placaInput.value = 'BIC-' + Math.random().toString(36).substr(2, 6).toUpperCase();
        }
    });

    // Inicialización
    actualizarRequerimientoPlaca();
});