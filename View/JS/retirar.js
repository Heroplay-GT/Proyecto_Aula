let vehiculoIdSeleccionado = null;

document.addEventListener("DOMContentLoaded", () => {
    fetch("../../Controller/ReservaAdminController.php?vehiculos_activos=1")
        .then(res => res.json())
        .then(data => {
            const tbody = document.querySelector("#tablaVehiculos tbody");
            data.forEach(v => {
                const fila = document.createElement("tr");
                fila.innerHTML = `
          <td>${v.placa}</td>
          <td>${v.tipo}</td>
          <td>${v.modelo}</td>
          <td>${v.espacio_id}</td>
          <td>${v.fecha_ingreso}</td>
          <td><button onclick="abrirModal(${v.id}, '${v.placa}')">Retirar</button></td>
        `;
                tbody.appendChild(fila);
            });
        });
});

function abrirModal(id, placa) {
    vehiculoIdSeleccionado = id;
    document.getElementById("infoVehiculo").textContent = `Placa: ${placa}`;
    document.getElementById("modalRetiro").style.display = "block";
}

function cerrarModal() {
    document.getElementById("modalRetiro").style.display = "none";
    vehiculoIdSeleccionado = null;
}

document.getElementById("minutos").addEventListener("input", () => {
    const tarifaMinuto = 100; // Cambiar según tu lógica
    const minutos = parseInt(document.getElementById("minutos").value) || 0;
    document.getElementById("valor").value = minutos * tarifaMinuto;
});

document.getElementById("confirmarRetiro").addEventListener("click", () => {
    const minutos = parseInt(document.getElementById("minutos").value);
    const valor = parseInt(document.getElementById("valor").value);

    if (!vehiculoIdSeleccionado || !minutos || !valor) {
        alert("Completa los campos.");
        return;
    }

    fetch(`../../Controller/ReservaAdminController.php?action=retirar&id=${vehiculoIdSeleccionado}&minutos=${minutos}&valor=${valor}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                mostrarRecibo(data.recibo);
            } else {
                alert("Error al retirar: " + (data.error || ""));
            }
        });

});

function mostrarRecibo(info) {
    document.getElementById("reciboContenido").innerHTML = `
    <p><strong>Placa:</strong> ${info.placa}</p>
    <p><strong>Tipo:</strong> ${info.tipo}</p>
    <p><strong>Ingreso:</strong> ${info.fecha_ingreso}</p>
    <p><strong>Salida:</strong> ${info.fecha_salida}</p>
    <p><strong>Tiempo total:</strong> ${info.minutos} min (${info.horas} horas)</p>
    <p><strong>Precio/hora:</strong> $${info.precio_hora}</p>
    <p><strong>Valor pagado:</strong> $${info.valor}</p>
  `;
    document.getElementById("reciboModal").style.display = "block";
}

function cerrarRecibo() {
    document.getElementById("reciboModal").style.display = "none";
    location.reload();
}

