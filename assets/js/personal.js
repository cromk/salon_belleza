document.addEventListener('DOMContentLoaded', function() {
  const selectRol = document.getElementById('id_rol');
  const formPersonal = document.getElementById('formPersonal');
  const mensaje = document.getElementById('mensaje');

  function loadRoles() {
    fetch('/salon_belleza/controllers/PersonalController.php?action=getRoles')
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          let opts = '<option value="">Seleccione un rol</option>';
          res.data.forEach(r => { opts += `<option value="${r.id_rol}">${r.nombre}</option>`; });
          selectRol.innerHTML = opts;
        }
      }).catch(err => console.error(err));
  }

  if (formPersonal) {
    formPersonal.addEventListener('submit', function(e) {
      e.preventDefault();
      mensaje.classList.add('d-none');
      const btn = formPersonal.querySelector('button[type="submit"]');
      btn.disabled = true;

      // Asegurar que el correo contiene el dominio requerido
      const correoEl = formPersonal.querySelector('#correo');
      if (correoEl) {
        let correoVal = correoEl.value.trim();
        if (correoVal !== '' && correoVal.indexOf('@') === -1) {
          correoVal = correoVal + '@salonbelleza.com';
          correoEl.value = correoVal;
        }
      }

      const fd = new FormData(formPersonal);
      fd.append('action','create');

      fetch('/salon_belleza/controllers/usuarioController.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            mensaje.classList.remove('d-none'); mensaje.classList.remove('alert-danger'); mensaje.classList.add('alert-success');
            mensaje.textContent = res.message || 'Personal registrado correctamente.';
            formPersonal.reset();
            // refrescar tabla de personal
            if (typeof cargarPersonal === 'function') cargarPersonal();
            else loadPersonal();
            // si el rol seleccionado fue Estilista (id 3), refrescar el select de usuarios en la sección de estilistas
            const rolVal = fd.get('id_rol');
            if (rolVal && String(rolVal) === '3') {
              // actualizar selectUsuario en la sección de estilistas
              fetch('/salon_belleza/controllers/PersonalController.php?action=getUsuariosByRole&role=3')
                .then(r => r.json()).then(rj => {
                  if (rj.success) {
                    const sel = document.getElementById('selectUsuario');
                    if (sel) sel.innerHTML = '<option value="">-- Seleccione usuario --</option>' + rj.data.map(u => `<option value="${u.id_usuario}">${u.nombre} ${u.apellido}</option>`).join('');
                  }
                }).catch(() => {});
            }
          } else {
            mensaje.classList.remove('d-none'); mensaje.classList.remove('alert-success'); mensaje.classList.add('alert-danger');
            mensaje.textContent = res.message || 'Error al registrar.';
          }
        }).catch(err => {
          mensaje.classList.remove('d-none'); mensaje.classList.remove('alert-success'); mensaje.classList.add('alert-danger');
          mensaje.textContent = 'Error de conexión.';
        }).finally(() => { btn.disabled = false; });
    });
  }

  // Inicializar
  loadRoles();
  // cargar tabla de personal
  function loadPersonal() {
    fetch('/salon_belleza/controllers/PersonalController.php?action=readPersonal')
      .then(r => r.json()).then(res => {
        if (res.success) {
          const tbody = document.querySelector('#tablaPersonal tbody');
          if (!tbody) return;
          tbody.innerHTML = '';
          res.data.forEach((p, i) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
              <td>${i+1}</td>
              <td>${p.nombre} ${p.apellido}</td>
              <td>${p.correo}</td>
              <td>${p.telefono || ''}</td>
              <td>${p.usuario || ''}</td>
              <td>${p.rol || ''}</td>
            `;
            tbody.appendChild(tr);
          });
        }
      }).catch(err => console.error(err));
  }
  loadPersonal();
});
