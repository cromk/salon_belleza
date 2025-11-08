document.addEventListener('DOMContentLoaded', function() {
  const selectUsuario = document.getElementById('selectUsuario');
  const listaServicios = document.getElementById('listaServicios');
  const tablaBody = document.querySelector('#tablaEstilistas tbody');
  const form = document.getElementById('formEstilista');
  const mensaje = document.getElementById('mensajeEstilista');
  const btnAddHorario = document.getElementById('btnAddHorario');
  const horariosList = document.getElementById('horariosList');
  const horarioDia = document.getElementById('horario_dia');
  const horarioHi = document.getElementById('horario_hi');
  const horarioHf = document.getElementById('horario_hf');

  let horarios = [];

  function renderHorarios() {
    if (!horariosList) return;
    horariosList.innerHTML = '';
    horarios.forEach((h, idx) => {
      const div = document.createElement('div');
      div.className = 'd-flex align-items-center gap-2 mb-1';
      div.innerHTML = `<small class="text-muted">${h.dia_semana} ${h.hora_inicio} - ${h.hora_fin}</small> <button type="button" class="btn btn-sm btn-link text-danger" data-idx="${idx}">Eliminar</button>`;
      horariosList.appendChild(div);
    });
    // attach delete
    horariosList.querySelectorAll('button[data-idx]').forEach(b => { b.addEventListener('click', function(){ const i = parseInt(this.dataset.idx); horarios.splice(i,1); renderHorarios(); }); });
  }

  if (btnAddHorario) {
    btnAddHorario.addEventListener('click', function(){
      const dia = horarioDia.value;
      const hi = horarioHi.value;
      const hf = horarioHf.value;
      if (!dia || !hi || !hf) { alert('Complete día, hora inicio y hora fin'); return; }
      if (hi >= hf) { alert('Hora inicio debe ser menor que hora fin'); return; }
      horarios.push({ dia_semana: dia, hora_inicio: hi, hora_fin: hf });
      renderHorarios();
      horarioHi.value = ''; horarioHf.value = '';
    });
  }

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

    // agregar horarios al form data
    if (horarios && horarios.length > 0) {
      fd.append('horarios', JSON.stringify(horarios));
    }

    fd.append('action','createEstilista');
    fd.append('services', JSON.stringify(services));

    fetch('/salon_belleza/controllers/PersonalController.php', { method: 'POST', body: fd })
      .then(r => r.json()).then(res => {
        if (res.success) {
          mensaje.classList.remove('d-none'); mensaje.classList.remove('alert-danger'); mensaje.classList.add('alert-success');
          mensaje.textContent = 'Estilista creado correctamente.';
          // limpiar formulario y estado local de horarios
          form.reset();
          horarios = [];
          try { renderHorarios(); } catch(e) { /* noop */ }
          loadServicios(); loadUsuarios(); loadEstilistas();
          // ocultar la notificación de éxito automáticamente tras 15 segundos
          setTimeout(() => {
            try { mensaje.classList.add('d-none'); } catch(e) {}
          }, 15000);
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
