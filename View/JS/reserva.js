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

    // Event Listeners
    tipoVehiculo.addEventListener("change", function() {
        // Limpiar campos al cambiar tipo
        selectEspacio.innerHTML = '<option value="" disabled selected hidden>Seleccione espacio</option>';
        selectEspacio.disabled = true;
        precioInput.value = "";
        
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

        fetch(`../../Controller/ConsultarEspacios.php?tipo=${tipo === 'all' ? '' : tipo}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) throw new Error(data.error);
                contenidoEspacios.innerHTML = data.length > 0 
                    ? generarCardsEspacios(data) 
                    : '<p class="no-espacios">No hay espacios disponibles</p>';
                agregarEventosCards();
            })
            .catch(err => {
                console.error("Error:", err);
                contenidoEspacios.innerHTML = `<p class="error">Error al cargar espacios: ${err.message}</p>`;
            });
    }

    function generarCardsEspacios(espacios) {
        return espacios.map(espacio => `
            <div class="espacio-card ${espacio.estado.toLowerCase()}" 
                 data-id="${espacio.id}" 
                 data-codigo="${espacio.codigo}" 
                 data-precio="${espacio.precio_hora}"
                 data-tipo="${espacio.tipo_vehiculo}">
                <h3>${espacio.codigo}</h3>
                <p>${espacio.tipo_vehiculo} - $${espacio.precio_hora.toLocaleString('es-CO')}/h</p>
            </div>
        `).join('');
    }

    function cargarEspaciosDisponibles() {
        const tipo = tipoVehiculo.value;
        if (!tipo) return;

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
            card.addEventListener('click', function() {
                if (this.classList.contains('disponible')) {
                    // Actualizar tipo de vehículo si es diferente
                    if (tipoVehiculo.value !== this.dataset.tipo) {
                        tipoVehiculo.value = this.dataset.tipo;
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
});