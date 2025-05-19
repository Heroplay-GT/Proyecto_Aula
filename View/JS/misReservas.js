document.addEventListener('DOMContentLoaded', function () {
    // Elementos del DOM
    const modal = document.getElementById('reservaModal');
    const modalContent = document.getElementById('modalContent');

    window.mostrarDetalleReserva = function (reservaId, estado) {
        // Verificar si la reserva está cancelada
        if (estado === 'Cancelada') {
            return; // No hacer nada si está cancelada
        }

        fetch(`../../Controller/ReservasController.php?action=detalle&id=${reservaId}`)
            .then(response => {
                if (!response.ok) throw new Error('Error en la respuesta');
                return response.json();
            })
            .then(data => {
                let qrSection = '';
                let actionButton = '';

                if (data.qr_code) {
                    qrSection = `
                        <div class="qr-section">
                            <h3>Código QR</h3>
                            <img src="../../Media/QRCodes/${data.qr_code}" alt="QR Reserva">
                            <p class="qr-instructions">Muestra este código al ingresar al parqueadero</p>
                        </div>
                    `;
                }

                if (data.estado === 'Pendiente') {
                    actionButton = `
                        <div class="action-buttons">
                            <button class="btn-cancelar" onclick="cancelarReserva(${data.id})">
                                <ion-icon name="close-circle"></ion-icon> Cancelar Reserva
                            </button>
                        </div>
                    `;
                }

                modalContent.innerHTML = `
                    <div class="reserva-header">
                        <h2>Reserva #${data.id}</h2>
                        <span class="reserva-status ${data.estado.toLowerCase()}">${data.estado}</span>
                    </div>
                    
                    <div class="reserva-details">
                        <div class="detail-group">
                            <h3>Vehículo</h3>
                            <p><strong>Tipo:</strong> ${data.tipo}</p>
                            <p><strong>Modelo:</strong> ${data.modelo}</p>
                            <p><strong>Placa:</strong> ${data.placa}</p>
                        </div>
                        
                        <div class="detail-group">
                            <h3>Espacio</h3>
                            <p>${data.espacio_codigo || 'No asignado'}</p>
                        </div>
                        
                        <div class="detail-group">
                            <h3>Fechas</h3>
                            <p><strong>Reserva:</strong> ${new Date(data.fecha_reserva).toLocaleString()}</p>
                            ${data.fecha_ingreso ?
                        `<p><strong>Ingreso:</strong> ${new Date(data.fecha_ingreso).toLocaleString()}</p>` : ''
                    }
                        </div>
                    </div>
                    
                    ${qrSection}
                    ${actionButton}
                `;
                modal.style.display = 'block';
            })
            .catch(error => {
                console.error('Error:', error);
                modalContent.innerHTML = `
                    <div class="error-message">
                        <ion-icon name="warning"></ion-icon>
                        <p>Error al cargar los detalles de la reserva</p>
                    </div>
                `;
                modal.style.display = 'block';
            });
    };

    // Cerrar modal
    window.cerrarModal = function () {
        modal.style.display = 'none';
    };

    // Cerrar al hacer clic fuera del contenido
    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            cerrarModal();
        }
    });
});

// Función para cancelar reserva
function cancelarReserva(reservaId) {
    if (confirm("¿Estás seguro de cancelar esta reserva?\nEsta acción no se puede deshacer.")) {
        fetch(`../../Controller/ReservasController.php?action=cancelar&id=${reservaId}`)
            .then(response => {
                if (response.ok) {
                    location.reload();
                } else {
                    alert('No se pudo cancelar la reserva. Intente nuevamente.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ocurrió un error al comunicarse con el servidor');
            });
    }
}