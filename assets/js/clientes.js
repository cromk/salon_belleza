document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('formCliente');
  const mensaje = document.getElementById('mensajeCliente');
  const tablaBody = document.querySelector('#tablaClientes tbody');
  const showInactive = document.getElementById('showInactive');
  const btnCancelar = document.getElementById('btnCancelarEdicion');
  const submitBtn = document.getElementById('btnGuardarCliente');
  let clientes = [];
  let editingId = null;

  function resetForm() {
    form.reset();
    editingId = null;
    submitBtn.textContent = 'Guardar cliente';
    submitBtn.classList.remove('btn-warning');
    submitBtn.classList.add('btn-primary');
    if (btnCancelar) btnCancelar.classList.add('d-none');
  }

  function showMessage(type, text) {
    if (!mensaje) return;
    mensaje.classList.remove('d-none', 'alert-danger', 'alert-success');
    mensaje.classList.add(type === 'error' ? 'alert-danger' : 'alert-success');
    mensaje.textContent = text;
  }

  function clearMessage() {
    if (!mensaje) return;
    mensaje.classList.add('d-none');
    mensaje.textContent = '';
  }

  function renderClientes() {
    if (!tablaBody) return;
    tablaBody.innerHTML = '';
    if (!clientes.length) {
      const tr = document.createElement('tr');
      tr.innerHTML = `<td colspan="6" class="text-center text-muted">No hay clientes registrados.</td>`;
      tablaBody.appendChild(tr);
      return;
    }
    clientes.forEach((c, idx) => {
      const estadoBadge = c.estado === 'Activo'
        ? '<span class="badge bg-success">Activo</span>'
        : '<span class="badge bg-secondary">Inactivo</span>';
      const historialText = c.total_citas > 0 ? `${c.total_citas} registro(s)` : 'Sin historial';
      const disableDelete = c.total_citas > 0;
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${idx + 1}</td>
        <td><strong>${c.nombre}</strong><br><small>${c.apellido ? c.apellido : ''}</small></td>
        <td>
          ${c.telefono ? `<div><i class="bi bi-telephone"></i> ${c.telefono}</div>` : ''}
          ${c.correo ? `<div><i class="bi bi-envelope"></i> ${c.correo}</div>` : ''}
        </td>
        <td>${estadoBadge}</td>
        <td>${historialText}</td>
        <td>
          <div class="btn-group" role="group">
            <button class="btn btn-sm btn-outline-primary btn-edit" data-id="${c.id_cliente}"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-outline-secondary btn-toggle" data-id="${c.id_cliente}" data-next="${c.estado === 'Activo' ? 'Inactivo' : 'Activo'}">
              <i class="bi ${c.estado === 'Activo' ? 'bi-person-dash' : 'bi-person-check'}"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${c.id_cliente}" ${disableDelete ? 'disabled title="No se puede eliminar con historial"' : ''}>
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </td>
      `;
      tablaBody.appendChild(tr);
    });
    attachRowEvents();
  }

  function loadClientes() {
    const includeInactive = showInactive && showInactive.checked ? '1' : '0';
    fetch(`/salon_belleza/controllers/ClienteController.php?action=read&all=${includeInactive}`)
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          clientes = res.data || [];
          renderClientes();
        } else {
          showMessage('error', res.message || 'No se pudieron cargar los clientes.');
        }
      })
      .catch(() => {
        showMessage('error', 'Error de conexión al cargar clientes.');
      });
  }

  function attachRowEvents() {
    document.querySelectorAll('.btn-edit').forEach(btn => {
      btn.addEventListener('click', function () {
        const id = this.getAttribute('data-id');
        const cliente = clientes.find(item => String(item.id_cliente) === String(id));
        if (!cliente) {
          showMessage('error', 'Cliente no encontrado.');
          return;
        }
        form.nombre.value = cliente.nombre || '';
        form.apellido.value = cliente.apellido || '';
        form.telefono.value = cliente.telefono || '';
        form.correo.value = cliente.correo || '';
        editingId = cliente.id_cliente;
        submitBtn.textContent = 'Actualizar cliente';
        submitBtn.classList.remove('btn-primary');
        submitBtn.classList.add('btn-warning');
        if (btnCancelar) btnCancelar.classList.remove('d-none');
        clearMessage();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });
    });

    document.querySelectorAll('.btn-toggle').forEach(btn => {
      btn.addEventListener('click', function () {
        const id = this.getAttribute('data-id');
        const next = this.getAttribute('data-next') === 'Activo' ? 'Activo' : 'Inactivo';
        const fd = new FormData();
        fd.append('action', 'setEstado');
        fd.append('id_cliente', id);
        fd.append('estado', next);
        fetch('/salon_belleza/controllers/ClienteController.php', { method: 'POST', body: fd })
          .then(r => r.json())
          .then(res => {
            if (res.success) {
              loadClientes();
            } else {
              showMessage('error', res.message || 'No se pudo actualizar el estado.');
            }
          })
          .catch(() => showMessage('error', 'Error de conexión.'));
      });
    });

    document.querySelectorAll('.btn-delete').forEach(btn => {
      btn.addEventListener('click', function () {
        const id = this.getAttribute('data-id');
        if (!id) return;
        if (!confirm('¿Eliminar este cliente? Esta acción es permanente.')) return;
        const fd = new FormData();
        fd.append('action', 'delete');
        fd.append('id_cliente', id);
        fetch('/salon_belleza/controllers/ClienteController.php', { method: 'POST', body: fd })
          .then(r => r.json())
          .then(res => {
            if (res.success) {
              loadClientes();
              showMessage('success', 'Cliente eliminado correctamente.');
            } else {
              if (res.hasHistory) {
                showMessage('error', res.message || 'No es posible eliminar clientes con historial.');
              } else {
                showMessage('error', res.message || 'No se pudo eliminar el cliente.');
              }
            }
          })
          .catch(() => showMessage('error', 'Error de conexión.'));
      });
    });
  }

  if (showInactive) {
    showInactive.addEventListener('change', loadClientes);
  }

  if (btnCancelar) {
    btnCancelar.addEventListener('click', function () {
      resetForm();
      clearMessage();
    });
  }

  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      clearMessage();
      submitBtn.disabled = true;
      const fd = new FormData(form);
      if (editingId) {
        fd.append('action', 'update');
        fd.append('id_cliente', editingId);
      } else {
        fd.append('action', 'create');
      }
      fetch('/salon_belleza/controllers/ClienteController.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            showMessage('success', editingId ? 'Cliente actualizado correctamente.' : 'Cliente registrado correctamente.');
            resetForm();
            loadClientes();
          } else {
            showMessage('error', res.message || 'No se pudo guardar el cliente.');
          }
        })
        .catch(() => {
          showMessage('error', 'Error de conexión.');
        })
        .finally(() => {
          submitBtn.disabled = false;
        });
    });
  }

  loadClientes();
});