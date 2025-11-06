document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('formServicio');
  const tablaBody = document.querySelector('#tablaServicios tbody');
  const mensaje = document.getElementById('mensajeServicio');

  function cargarServicios() {
    fetch('/salon_belleza/controllers/ServicioController.php?action=read')
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          tablaBody.innerHTML = '';
          res.data.forEach((s, i) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
              <td>${i+1}</td>
              <td>${s.nombre}</td>
              <td>${parseFloat(s.precio_base).toFixed(2)}</td>
              <td>${s.duracion_base} min</td>
              <td>
                <div class="btn-group" role="group">
                  <button class="btn btn-sm btn-outline-primary btn-edit" data-id="${s.id_servicio}"><i class="bi bi-pencil"></i></button>
                  <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${s.id_servicio}"><i class="bi bi-trash"></i></button>
                </div>
              </td>
            `;
            tablaBody.appendChild(tr);
          });
          // Attach handlers
          document.querySelectorAll('.btn-edit').forEach(b => b.addEventListener('click', onEdit));
          document.querySelectorAll('.btn-delete').forEach(b => b.addEventListener('click', onDelete));
        }
      }).catch(err => console.error(err));
  }

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnCrear');
    btn.disabled = true;
    mensaje.classList.add('d-none');
    const data = new FormData(form);
    // Si viene un id_servicio, hacemos update
    const id = document.getElementById('id_servicio') ? document.getElementById('id_servicio').value : '';
    data.append('action', id ? 'update' : 'create');

    fetch('/salon_belleza/controllers/ServicioController.php', { method: 'POST', body: data }).then(r => r.json())
      .then(res => {
        if (res.success) {
          mensaje.classList.remove('d-none');
          mensaje.classList.remove('alert-danger');
          mensaje.classList.add('alert-success');
          mensaje.textContent = id ? 'Servicio actualizado correctamente.' : 'Servicio creado correctamente.';
          form.reset();
          // limpiar modo edición
          if (document.getElementById('id_servicio')) document.getElementById('id_servicio').value = '';
          cargarServicios();
        } else {
          mensaje.classList.remove('d-none');
          mensaje.classList.remove('alert-success');
          mensaje.classList.add('alert-danger');
          mensaje.textContent = res.message || 'Error al crear servicio.';
        }
      }).catch(err => {
        mensaje.classList.remove('d-none');
        mensaje.classList.remove('alert-success');
        mensaje.classList.add('alert-danger');
        mensaje.textContent = 'Error de conexión.';
      }).finally(() => { btn.disabled = false; });
  });

  function onEdit(e) {
    const id = e.currentTarget.getAttribute('data-id');
    // Obtener datos del servicio (llamamos al read y filtramos)
    fetch('/salon_belleza/controllers/ServicioController.php?action=read')
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          const s = res.data.find(x => String(x.id_servicio) === String(id));
          if (!s) return alert('Servicio no encontrado');
          document.getElementById('nombre').value = s.nombre;
          document.getElementById('precio').value = parseFloat(s.precio_base).toFixed(2);
          document.getElementById('duracion').value = s.duracion_base;
          document.getElementById('descripcion').value = s.descripcion || '';
          if (document.getElementById('id_servicio')) document.getElementById('id_servicio').value = s.id_servicio;
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }
      }).catch(err => console.error(err));
  }

  function onDelete(e) {
    const id = e.currentTarget.getAttribute('data-id');
    if (!confirm('¿Eliminar este servicio? Esta acción ocultará el servicio del catálogo.')) return;
    const data = new FormData();
    data.append('action', 'delete');
    data.append('id_servicio', id);
    fetch('/salon_belleza/controllers/ServicioController.php', { method: 'POST', body: data })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          cargarServicios();
        } else alert(res.message || 'Error al eliminar');
      }).catch(err => console.error(err));
  }

  // Inicializar
  cargarServicios();
});
