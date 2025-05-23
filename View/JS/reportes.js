document.addEventListener("DOMContentLoaded", () => {
    const selectMes = document.getElementById("mes");
    const selectAnio = document.getElementById("anio");

    // Consultar al cargar
    const fecha = new Date();
    selectMes.value = String(fecha.getMonth() + 1).padStart(2, "0");
    selectAnio.value = fecha.getFullYear();

    consultarReporte();
});

async function consultarReporte() {
    try {
        const mes = document.getElementById("mes").value;
        const anio = document.getElementById("anio").value;

        const inicio = `${anio}-${mes}-01`;
        const fin = new Date(anio, parseInt(mes), 0).toISOString().split("T")[0];

        const formData = new FormData();
        formData.append("fecha_inicio", inicio);
        formData.append("fecha_fin", fin);

        const response = await fetch("/Proyecto_Aula/Controller/ReporteController.php", {
            method: "POST",
            body: formData,
        });

        const data = await response.json();

        if (!data.success) {
            alert("Error: " + data.error);
            return;
        }

        const tablaBody = document.querySelector("#tablaReporte tbody");
        tablaBody.innerHTML = "";

        let total = 0;

        if (data.data.length === 0) {
            tablaBody.innerHTML = `<tr><td colspan="6">No hay datos disponibles para el mes seleccionado.</td></tr>`;
        } else {
            data.data.forEach(reg => {
                const fila = document.createElement("tr");
                fila.innerHTML = `
          <td>${reg.espacio}</td>
          <td>${reg.tipo_vehiculo}</td>
          <td>${reg.fecha_ingreso}</td>
          <td>${reg.fecha_salida}</td>
          <td>${reg.duracion}</td>
          <td>$${parseInt(reg.valor_pagado).toLocaleString("es-CO")} COP</td>
        `;
                tablaBody.appendChild(fila);
                total += parseFloat(reg.valor_pagado);
            });
        }

        document.getElementById("totalRecaudado").innerText =
            `Total recaudado: $${total.toLocaleString("es-CO")} COP`;

    } catch (err) {
     
    }
}

// Función para exportar a PDF (opcional)
async function descargarPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    const reporte = document.querySelector(".reporte-container");

    await html2canvas(reporte, {
        scale: 2, // mejor calidad
    }).then(canvas => {
        const imgData = canvas.toDataURL("image/png");
        const imgProps = doc.getImageProperties(imgData);
        const pdfWidth = doc.internal.pageSize.getWidth();
        const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

        doc.addImage(imgData, "PNG", 0, 0, pdfWidth, pdfHeight);
        doc.save("reporte_mensual.pdf");
    });
}
