document.addEventListener('DOMContentLoaded', () => {
  const elemento = document.getElementById('lector-qr');
  if (!elemento || typeof Html5Qrcode === 'undefined') return;
  const estado = document.getElementById('estado-escaner');
  const boton = document.getElementById('iniciar-camara');
  const lector = new Html5Qrcode('lector-qr');
  let iniciado = false;
  const informar = (mensaje, tipo = 'info') => {
    if (!estado) return;
    estado.className = `alert alert-${tipo} mt-3`;
    estado.innerHTML = `<i class="bi bi-${tipo === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i> ${mensaje}`;
  };
  const iniciar = () => {
    if (iniciado) return;
    informar('Solicitando permiso para usar la cámara…');
    lector.start({facingMode: 'environment'}, {fps: 10, qrbox: {width: 250, height: 250}}, (texto) => {
      informar(`Código detectado: ${texto}`, 'success');
      lector.stop().then(() => { window.location.href = `/supay/modules/tecnico/historial.php?codigo=${encodeURIComponent(texto)}`; });
    }, () => {}).then(() => {
      iniciado = true;
      if (boton) boton.disabled = true;
      informar('Cámara activa. Enfoca el código QR del instrumento.', 'success');
    }).catch(() => informar('No fue posible iniciar la cámara. Requiere HTTPS o permiso del navegador; puedes usar la entrada manual.', 'warning'));
  };
  boton?.addEventListener('click', iniciar);
});
