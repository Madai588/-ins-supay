document.addEventListener('DOMContentLoaded', () => {
  // Algunos navegadores no repintan el texto de los formularios hasta perder el foco.
  // Reaplicamos el color y forzamos la capa visual en cada tecla para que se vea al instante.
  document.querySelectorAll('input:not([type="hidden"]), textarea').forEach((campo) => {
    // Evita que el navegador muestre valores antiguos con fondo claro en los formularios del sistema.
    if (!campo.closest('.login-form')) {
      campo.setAttribute('autocomplete', 'off');
    }
    campo.addEventListener('input', () => {
      campo.style.color = '#f7efe7';
      campo.style.webkitTextFillColor = '#f7efe7';
      campo.style.caretColor = '#f7efe7';
      campo.style.transform = 'translateZ(0)';
      window.requestAnimationFrame(() => {
        campo.style.transform = '';
      });
    });
  });

  document.querySelectorAll('form:not(.login-form)').forEach((formulario) => {
    formulario.setAttribute('autocomplete', 'off');
  });

  document.querySelectorAll('input[name="codigo"]').forEach((input) => {
    input.spellcheck = false;
    input.setAttribute('autocomplete', 'off');
    input.setAttribute('autocapitalize', 'off');
  });

  document.querySelectorAll('[data-auto-dismiss]').forEach((alerta) => {
    window.setTimeout(() => {
      if (window.bootstrap?.Alert) bootstrap.Alert.getOrCreateInstance(alerta).close();
    }, 4000);
  });

  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((elemento) => {
    if (window.bootstrap?.Tooltip) bootstrap.Tooltip.getOrCreateInstance(elemento);
  });

  document.querySelectorAll('[data-delete-id]').forEach((boton) => {
    boton.addEventListener('click', () => {
      const campo = document.querySelector(boton.dataset.deleteTarget || '#idClienteEliminar');
      if (campo) campo.value = boton.dataset.deleteId;
    });
  });

  document.querySelectorAll('[data-backup-name]').forEach((boton) => {
    boton.addEventListener('click', () => {
      const modal = boton.dataset.bsTarget;
      const campo = document.querySelector(modal === '#modalRestaurar' ? '#respaldoRestaurar' : '#respaldoEliminar');
      if (campo) campo.value = boton.dataset.backupName;
    });
  });

  document.querySelectorAll('[data-user-id]').forEach((boton) => {
    boton.addEventListener('click', () => {
      const id = document.querySelector('#estadoUsuarioId');
      const activo = document.querySelector('#estadoUsuarioActivo');
      const texto = document.querySelector('#textoEstadoUsuario');
      const seraActivo = boton.dataset.userActive === '1';
      if (id) id.value = boton.dataset.userId;
      if (activo) activo.value = boton.dataset.userActive;
      if (texto) texto.textContent = seraActivo
        ? `¿Deseas activar el acceso de ${boton.dataset.userName}?`
        : `¿Deseas desactivar el acceso de ${boton.dataset.userName}?`;
    });
  });

  document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
    toggle.addEventListener('click', () => {
      const input = document.querySelector(toggle.dataset.passwordToggle);
      const icono = toggle.querySelector('i');
      if (!input) return;
      input.type = input.type === 'password' ? 'text' : 'password';
      icono?.classList.toggle('bi-eye');
      icono?.classList.toggle('bi-eye-slash');
    });
  });

  const sidebar = document.querySelector('.sidebar');
  const backdrop = document.querySelector('.sidebar-backdrop');
  const cerrarSidebar = () => {
    sidebar?.classList.remove('show');
    backdrop?.classList.remove('show');
  };
  document.querySelectorAll('[data-sidebar-toggle]').forEach((boton) => {
    boton.addEventListener('click', () => {
      sidebar?.classList.toggle('show');
      backdrop?.classList.toggle('show');
    });
  });
  backdrop?.addEventListener('click', cerrarSidebar);
  document.querySelectorAll('.sidebar .nav-link').forEach((link) => link.addEventListener('click', cerrarSidebar));

  document.querySelectorAll('[data-back-button]').forEach((boton) => {
    boton.addEventListener('click', () => {
      if (window.history.length > 1) window.history.back();
      else window.location.href = '/supay/';
    });
  });
});
