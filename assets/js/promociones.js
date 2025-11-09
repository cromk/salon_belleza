// Ejecutar el script una vez que todo el DOM esté disponible
document.addEventListener('DOMContentLoaded', () => {
  // Referencias a elementos utilizados con frecuencia
  const form = document.getElementById('formPromocion');
  const tablaBody = document.querySelector('#tablaPromociones tbody');
  const mensaje = document.getElementById('mensajePromocion');
  const btnGuardar = document.getElementById('btnGuardarPromocion');

  /**
   * Consultar las promociones desde el servidor y pintar la tabla.
   */
  function cargarPromociones() {
    fetch('/salon_belleza/controllers/PromocionController.php?action=read&all=1')
      .then(r => r.json())
      .then(res => {
        if (!res.success) {
          console.error(res.message || 'No se pudieron cargar las promociones');
          return;
        }
        // Limpiar filas existentes antes de repintar
        tablaBody.innerHTML = '';
        res.data.forEach((promo, index) => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${index + 1}</td>
            <td>${promo.nombre}</td>
            <td>${promo.tipo}</td>
            <td>$${parseFloat(promo.precio).toFixed(2)}</td>
            <td><span class="badge ${promo.estado === 'Activo' ? 'bg-success' : 'bg-secondary'}">${promo.estado}</span></td>
            <td>
              <div class="btn-group" role="group">
                <button class="btn btn-sm btn-outline-primary btn-edit" data-id="${promo.id_promocion}"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-warning btn-toggle" data-id="${promo.id_promocion}" data-estado="${promo.estado === 'Activo' ? 'Inactivo' : 'Activo'}">
                  <i class="bi ${promo.estado === 'Activo' ? 'bi-eye-slash' : 'bi-eye'}"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${promo.id_promocion}"><i class="bi bi-trash"></i></button>
              </div>
            </td>
          `;
          tablaBody.appendChild(tr);
        });
        // Asociar eventos después de generar la tabla
        document.querySelectorAll('.btn-edit').forEach(btn => btn.addEventListener('click', onEdit));
        document.querySelectorAll('.btn-delete').forEach(btn => btn.addEventListener('click', onDelete));
        document.querySelectorAll('.btn-toggle').forEach(btn => btn.addEventListener('click', onToggle));
      })
      .catch(err => console.error('Error consultando promociones', err));
  }

  /**
   * Mostrar el mensaje de feedback en pantalla.
   */
  function mostrarMensaje(texto, tipo = 'success') {
    mensaje.classList.remove('d-none', 'alert-success', 'alert-danger');
    mensaje.classList.add(tipo === 'success' ? 'alert-success' : 'alert-danger');
    mensaje.textContent = texto;
  }

  // Registrar el envío del formulario para crear/actualizar promociones
  form.addEventListener('submit', event => {
    event.preventDefault();
    btnGuardar.disabled = true;
    mensaje.classList.add('d-none');

    // Preparar datos para envío al servidor
    const data = new FormData(form);
    const id = document.getElementById('id_promocion').value;
    data.append('action', id ? 'update' : 'create');

    fetch('/salon_belleza/controllers/PromocionController.php', { method: 'POST', body: data })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          mostrarMensaje(id ? 'Promoción actualizada correctamente.' : 'Promoción creada correctamente.', 'success');
          form.reset();
          document.getElementById('id_promocion').value = '';
          cargarPromociones();
        } else {
          mostrarMensaje(res.message || 'Ocurrió un error al guardar.', 'danger');
        }
      })
      .catch(() => mostrarMensaje('Error de comunicación con el servidor.', 'danger'))
      .finally(() => { btnGuardar.disabled = false; });
  });

  /**
   * Colocar los datos de la promoción seleccionada dentro del formulario.
   */
  function onEdit(event) {
    const id = event.currentTarget.getAttribute('data-id');
    fetch('/salon_belleza/controllers/PromocionController.php?action=read&all=1')
      .then(r => r.json())
      .then(res => {
        if (!res.success) return;
        const promo = res.data.find(p => String(p.id_promocion) === String(id));
        if (!promo) return;
        document.getElementById('id_promocion').value = promo.id_promocion;
        document.getElementById('nombre').value = promo.nombre;
        document.getElementById('tipo').value = promo.tipo;
        document.getElementById('precio').value = parseFloat(promo.precio).toFixed(2);
        document.getElementById('descripcion').value = promo.descripcion || '';
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });
  }

  /**
   * Solicitar eliminación lógica de la promoción.
   */
  function onDelete(event) {
    const id = event.currentTarget.getAttribute('data-id');
    if (!confirm('¿Desea eliminar esta promoción? Se marcará como inactiva.')) return;
    const data = new FormData();
    data.append('action', 'delete');
    data.append('id_promocion', id);
    fetch('/salon_belleza/controllers/PromocionController.php', { method: 'POST', body: data })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          cargarPromociones();
        } else {
          mostrarMensaje(res.message || 'No se pudo eliminar la promoción.', 'danger');
        }
      })
      .catch(() => mostrarMensaje('Error de comunicación al eliminar.', 'danger'));
  }

  /**
   * Activar o desactivar rápidamente una promoción.
   */
  function onToggle(event) {
    const id = event.currentTarget.getAttribute('data-id');
    const estado = event.currentTarget.getAttribute('data-estado');
    const data = new FormData();
    data.append('action', 'changeStatus');
    data.append('id_promocion', id);
    data.append('estado', estado);
    fetch('/salon_belleza/controllers/PromocionController.php', { method: 'POST', body: data })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          cargarPromociones();
        } else {
          mostrarMensaje(res.message || 'No se pudo actualizar el estado.', 'danger');
        }
      })
      .catch(() => mostrarMensaje('Error de comunicación al cambiar estado.', 'danger'));
  }

  // Cargar los datos iniciales al abrir la página
  cargarPromociones();
});