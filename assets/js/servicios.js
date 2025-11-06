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
            tr.innerHTML = `<td>${i+1}</td><td>${s.nombre}</td><td>${s.precio_base}</td><td>${s.duracion_base} min</td>`;
            tablaBody.appendChild(tr);
          });
        }
      }).catch(err => console.error(err));
  }

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnCrear');
    btn.disabled = true;
    mensaje.classList.add('d-none');

    const data = new FormData(form);
    data.append('action', 'create');

    fetch('/salon_belleza/controllers/ServicioController.php', {
      method: 'POST',
      body: data
    }).then(r => r.json())
      .then(res => {
        if (res.success) {
          mensaje.classList.remove('d-none');
          mensaje.classList.remove('alert-danger');
          mensaje.classList.add('alert-success');
          mensaje.textContent = 'Servicio creado correctamente.';
          form.reset();
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

  // Inicializar
  cargarServicios();
});
