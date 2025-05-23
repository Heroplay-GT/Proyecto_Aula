document.addEventListener('DOMContentLoaded', function () {
    fetch('../../Controller/ClienteAdminController.php?action=listar')
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la red');
            }
            return response.json();
        })
        .then(data => {
            console.log('Respuesta del servidor:', data);

            if (!data.success) {
                alert('Error: ' + data.error);
                return;
            }

            const tbody = document.querySelector('#tabla-clientes tbody');
            tbody.innerHTML = '';

            if (data.data && data.data.length > 0) {
                data.data.forEach(cliente => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${cliente.username || 'N/A'}</td>
                        <td>${cliente.email || 'N/A'}</td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="2">No hay clientes registrados</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los clientes: ' + error.message);
        });
});