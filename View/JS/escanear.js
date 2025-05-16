function iniciarEscaneo() {
  const reader = document.getElementById("reader");
  const resultado = document.getElementById("resultado");
  reader.style.display = "block";
  resultado.textContent = "";

  const html5QrCode = new Html5Qrcode("reader");

  html5QrCode.start(
    { facingMode: "environment" }, // Cámara trasera si está disponible
    {
      fps: 10, // Cuadros por segundo
      qrbox: { width: 300, height: 300 } // Tamaño del cuadro de escaneo
    },
    qrCodeMessage => {
      // Mostrar resultado
      resultado.textContent = "QR Detectado: " + qrCodeMessage;

      // Redirigir si es un enlace
      if (qrCodeMessage.startsWith("http://") || qrCodeMessage.startsWith("https://")) {
        window.location.href = qrCodeMessage;
      } else {
        alert("QR detectado, pero no es un enlace: " + qrCodeMessage);
      }

      // Detener escáner
      html5QrCode.stop().then(() => {
        reader.style.display = "none";
      });
    },
    errorMessage => {
      // Errores durante la lectura (puedes quitar este console si no lo necesitas)
      console.log("Intento fallido: ", errorMessage);
    }
  ).catch(err => {
    console.error("Error al iniciar escaneo: ", err);
    resultado.textContent = "No se pudo acceder a la cámara.";
  });
}
