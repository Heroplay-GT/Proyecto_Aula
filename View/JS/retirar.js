document.addEventListener('DOMContentLoaded', function () {
    const buscarPlaca = document.getElementById('buscarPlaca');
    const tablaVehiculos = document.getElementById('tablaVehiculos').getElementsByTagName('tbody')[0];
    const modalRetiro = document.getElementById('modalRetiro');
    const reciboModal = document.getElementById('reciboModal');
    const infoVehiculo = document.getElementById('infoVehiculo');
    const reciboContenido = document.getElementById('reciboContenido');
    const confirmarRetiro = document.getElementById('confirmarRetiro');

    let vehiculoActual = null;

    // Cargar vehículos al inicio
    cargarVehiculos();

    // Buscar vehículos al escribir en el campo de búsqueda
    buscarPlaca.addEventListener('input', function () {
        cargarVehiculos(this.value);
    });

    // Función para cargar vehículos
    function cargarVehiculos(placa = '') {
        fetch(`../../Controller/buscar_vehiculo.php?placa=${encodeURIComponent(placa)}`)
            .then(response => response.json())
            .then(data => {
                tablaVehiculos.innerHTML = '';
                data.forEach(vehiculo => {
                    const row = tablaVehiculos.insertRow();
                    row.innerHTML = `
                        <td>${vehiculo.placa}</td>
                        <td>${vehiculo.tipo}</td>
                        <td>${vehiculo.modelo || 'N/A'}</td>
                        <td>${vehiculo.espacio}</td>
                        <td>${vehiculo.fecha_ingreso}</td>
                        <td><button class="btn-retirar" data-id="${vehiculo.id}">Retirar</button></td>
                    `;
                });

                // Agregar eventos a los botones de retirar
                document.querySelectorAll('.btn-retirar').forEach(btn => {
                    btn.addEventListener('click', function () {
                        const id = this.getAttribute('data-id');
                        mostrarModalRetiro(id);
                    });
                });
            })
            .catch(error => console.error('Error:', error));
    }

    // Mostrar modal de confirmación de retiro
    function mostrarModalRetiro(id) {
        const vehiculo = Array.from(tablaVehiculos.querySelectorAll('tr'))
            .map(row => ({
                id: row.querySelector('.btn-retirar').getAttribute('data-id'),
                placa: row.cells[0].textContent,
                tipo: row.cells[1].textContent,
                modelo: row.cells[2].textContent,
                espacio: row.cells[3].textContent,
                fecha_ingreso: row.cells[4].textContent
            }))
            .find(v => v.id === id);

        vehiculoActual = vehiculo;
        infoVehiculo.innerHTML = `
            <strong>Placa:</strong> ${vehiculo.placa}<br>
            <strong>Tipo:</strong> ${vehiculo.tipo}<br>
            <strong>Modelo:</strong> ${vehiculo.modelo}<br>
            <strong>Espacio:</strong> ${vehiculo.espacio}<br>
            <strong>Fecha Ingreso:</strong> ${vehiculo.fecha_ingreso}
        `;
        modalRetiro.style.display = 'block';
    }

    // Cerrar modal de retiro
    window.cerrarModal = function () {
        modalRetiro.style.display = 'none';
    };

    window.cerrarRecibo = function () {
        reciboModal.style.display = 'none';
        cargarVehiculos(); // esta sí funciona porque está dentro del mismo scope
    };

    // Confirmar retiro
    confirmarRetiro.addEventListener('click', function () {
        if (!vehiculoActual) return;

        fetch('../../Controller/retirar_vehiculo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${vehiculoActual.id}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cerrarModal();
                    mostrarRecibo(data);
                } else {
                    alert('Error al retirar vehículo: ' + data.error);
                }
            })
            .catch(error => console.error('Error:', error));
    });

    // Mostrar recibo
    function mostrarRecibo(data) {
        reciboContenido.innerHTML = `
            <p><strong>Placa:</strong> ${data.placa}</p>
            <p><strong>Tipo:</strong> ${data.tipo}</p>
            <p><strong>Modelo:</strong> ${data.modelo}</p>
            <p><strong>Espacio:</strong> ${data.espacio}</p>
            <p><strong>Fecha Ingreso:</strong> ${data.fecha_ingreso}</p>
            <p><strong>Fecha Salida:</strong> ${data.fecha_salida}</p>
            <p><strong>Tiempo Estancia:</strong> ${data.horas} horas</p>
            <p><strong>Precio por hora:</strong> $${data.precio_hora}</p>
            <p><strong>Valor a pagar:</strong> $${data.valor_pagado}</p>
        `;
        reciboModal.style.display = 'block';
    }

    // Cerrar modales al hacer clic fuera del contenido
    window.addEventListener('click', function (event) {
        if (event.target === modalRetiro) {
            cerrarModal();
        }
        if (event.target === reciboModal) {
            cerrarRecibo();
        }
    });

});

function imprimirRecibo() {
    const contenido = document.getElementById('reciboContenido').innerHTML;
    const ventana = window.open('', '_blank');
    ventana.document.write(`
        <html>
            <head>
                <title>Recibo de Retiro</title>
                <style>
                    body {
                        font-family: 'Poppins', sans-serif;
                        padding: 30px;
                        color: #333;
                    }
                    h3 {
                        text-align: center;
                        margin-bottom: 20px;
                    }
                    .recibo-item {
                        margin-bottom: 10px;
                    }
                </style>
            </head>
            <body>
                <h3>Recibo de Retiro</h3>
                ${contenido}
            </body>
        </html>
    `);
    ventana.document.close();
    ventana.focus();
    ventana.print();
    ventana.close();
}