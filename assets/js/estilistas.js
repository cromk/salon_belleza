document.addEventListener('DOMContentLoaded', function() {
  const selectUsuario = document.getElementById('selectUsuario');
  const listaServicios = document.getElementById('listaServicios');
  const tablaBody = document.querySelector('#tablaEstilistas tbody');
  const form = document.getElementById('formEstilista');
  const mensaje = document.getElementById('mensajeEstilista');

  function loadUsuarios() {
    fetch('/salon_belleza/controllers/PersonalController.php?action=getUsuariosByRole&role=3')
      .then(r => r.json()).then(res => {
        if (res.success) {
          selectUsuario.innerHTML = '<option value="">-- Seleccione usuario --</option>' + res.data.map(u => `<option value="${u.id_usuario}">${u.nombre} ${u.apellido}</option>`).join('');
        }
      }).catch(err => console.error(err));
  }

  function loadServicios() {
    fetch('/salon_belleza/controllers/PersonalController.php?action=getServices')
      .then(r => r.json()).then(res => {
        if (res.success) {
          listaServicios.innerHTML = res.data.map(s => `
            <label class="form-check form-check-inline mb-2">
              <input class="form-check-input" type="checkbox" name="services" value="${s.id_servicio}">
              <span class="form-check-label"> ${s.nombre}</span>
            </label>
          `).join('');
        }
      }).catch(err => console.error(err));
  }

  function loadEstilistas() {
    fetch('/salon_belleza/controllers/PersonalController.php?action=readEstilistas')
      .then(r => r.json()).then(res => {
        if (res.success) {
          tablaBody.innerHTML = '';
          res.data.forEach((e, i) => {
            const servicios = (e.servicios || []).map(s => s.nombre).join(', ');
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${i+1}</td><td>${e.nombre} ${e.apellido}</td><td>${e.especialidad || ''}</td><td>${e.experiencia_anios || 0}</td><td>${servicios}</td>`;
            tablaBody.appendChild(tr);
          });
        }
      }).catch(err => console.error(err));
  }

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    mensaje.classList.add('d-none');
    const btn = form.querySelector('button[type="submit"]');
    btn.disabled = true;

    const fd = new FormData(form);
    const services = [];
    form.querySelectorAll('input[name="services"]:checked').forEach(ch => services.push(ch.value));

    fd.append('action','createEstilista');
    fd.append('services', JSON.stringify(services));

    fetch('/salon_belleza/controllers/PersonalController.php', { method: 'POST', body: fd })
      .then(r => r.json()).then(res => {
        if (res.success) {
          mensaje.classList.remove('d-none'); mensaje.classList.remove('alert-danger'); mensaje.classList.add('alert-success');
          mensaje.textContent = 'Estilista creado correctamente.';
          form.reset();
          loadServicios(); loadUsuarios(); loadEstilistas();
        } else {
          mensaje.classList.remove('d-none'); mensaje.classList.remove('alert-success'); mensaje.classList.add('alert-danger');
          mensaje.textContent = res.message || 'Error al crear estilista.';
        }
      }).catch(err => {
        mensaje.classList.remove('d-none'); mensaje.classList.remove('alert-success'); mensaje.classList.add('alert-danger');
        mensaje.textContent = 'Error de conexión.';
      }).finally(() => { btn.disabled = false; });
  });

  // Inicializar
  loadUsuarios(); loadServicios(); loadEstilistas();
});
