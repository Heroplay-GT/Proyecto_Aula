let html5QrCode;

function iniciarEscaneo() {
  const reader = document.getElementById("reader");
  reader.style.display = "flex";

  if (!html5QrCode) {
    html5QrCode = new Html5Qrcode("reader-canvas");
  }

  html5QrCode.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: { width: 250, height: 250 } },
    qrCodeMessage => {
      html5QrCode.stop().then(() => {
        reader.style.display = "none";
      });

      if (qrCodeMessage.startsWith("http://") || qrCodeMessage.startsWith("https://")) {
        window.location.href = qrCodeMessage;
      } else {
        alert("Código QR detectado:\n" + qrCodeMessage);
      }
    },
    error => {
      // Errores de lectura ignorados por defecto
    }
  ).catch(err => {
    alert("No se pudo acceder a la cámara.");
    console.error(err);
  });
}

function cancelarEscaneo() {
  const reader = document.getElementById("reader");
  if (html5QrCode) {
    html5QrCode.stop().then(() => {
      reader.style.display = "none";
    }).catch(console.error);
  } else {
    reader.style.display = "none";
  }
}
