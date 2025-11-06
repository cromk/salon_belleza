document.addEventListener('DOMContentLoaded', function() {
  const selectServicio = document.getElementById('selectServicio');
  const tablaBody = document.querySelector('#tablaEspecificaciones tbody');
  const form = document.getElementById('formEspecificacion');
  const mensaje = document.getElementById('mensajeEspecificacion');
  const tipoSelect = document.getElementById('esp_tipo');
  const precioInput = document.getElementById('esp_precio');
  const tiempoInput = document.getElementById('esp_tiempo');

  function cargarServicios() {
    fetch('/salon_belleza/controllers/ServicioController.php?action=read&all=1')
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          selectServicio.innerHTML = '<option value="">-- Seleccione --</option>';
          res.data.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id_servicio;
            opt.textContent = s.nombre;
            selectServicio.appendChild(opt);
          });
        }
      }).catch(err => console.error(err));
  }

  function cargarEspecificaciones(servicioId) {
    if (!servicioId) { tablaBody.innerHTML = ''; return; }
    fetch(`/salon_belleza/controllers/ServicioController.php?action=getSpecifications&servicio_id=${servicioId}`)
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          tablaBody.innerHTML = '';
          res.data.forEach((v, i) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${i+1}</td><td>${v.nombre}</td><td>${v.valor_precio}</td><td>${v.valor_tiempo} min</td>`;
            tablaBody.appendChild(tr);
          });
        }
      }).catch(err => console.error(err));
  }

  function setVisibilityByTipo() {
    const tipo = tipoSelect ? tipoSelect.value : 'ambos';
    if (!precioInput || !tiempoInput) return;
    const precioWrapper = precioInput.closest('.mb-3');
    const tiempoWrapper = tiempoInput.closest('.mb-3');

    if (tipo === 'precio') {
      precioWrapper.classList.remove('d-none');
      tiempoWrapper.classList.add('d-none');
      precioInput.disabled = false; precioInput.required = true;
      tiempoInput.disabled = true; tiempoInput.required = false;
    } else if (tipo === 'tiempo') {
      precioWrapper.classList.add('d-none');
      tiempoWrapper.classList.remove('d-none');
      precioInput.disabled = true; precioInput.required = false;
      tiempoInput.disabled = false; tiempoInput.required = true;
    } else { // ambos
      precioWrapper.classList.remove('d-none');
      tiempoWrapper.classList.remove('d-none');
      precioInput.disabled = false; precioInput.required = false;
      tiempoInput.disabled = false; tiempoInput.required = false;
    }
  }

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnCrearEsp');
    btn.disabled = true; mensaje.classList.add('d-none');
    const data = new FormData(form);
    data.append('action', 'addSpecification');

    fetch('/salon_belleza/controllers/ServicioController.php', { method: 'POST', body: data })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          mensaje.classList.remove('d-none');
          mensaje.classList.remove('alert-danger');
          mensaje.classList.add('alert-success');
          mensaje.textContent = 'Especificación agregada correctamente.';
          form.reset();
          cargarServicios();
          cargarEspecificaciones(selectServicio.value);
        } else {
          mensaje.classList.remove('d-none');
          mensaje.classList.remove('alert-success');
          mensaje.classList.add('alert-danger');
          mensaje.textContent = res.message || 'Error al crear especificación.';
        }
      }).catch(err => {
        mensaje.classList.remove('d-none');
        mensaje.classList.remove('alert-success');
        mensaje.classList.add('alert-danger');
        mensaje.textContent = 'Error de conexión.';
      }).finally(() => { btn.disabled = false; });
  });

  selectServicio.addEventListener('change', function() {
    cargarEspecificaciones(this.value);
  });

  // actualizar visibilidad según el tipo seleccionado
  if (tipoSelect) {
    tipoSelect.addEventListener('change', setVisibilityByTipo);
    // inicializar estado
    setVisibilityByTipo();
  }

  // init
  cargarServicios();
});
