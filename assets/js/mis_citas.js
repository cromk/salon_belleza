document.addEventListener('DOMContentLoaded', function(){
  const selFecha = document.getElementById('selFecha');
  const btnCargar = document.getElementById('btnCargar');
  const agendaArea = document.getElementById('agendaArea');
  const filterDesde = document.getElementById('filterDesde');
  const filterHasta = document.getElementById('filterHasta');
  const selEstilista = document.getElementById('filterEstilista');
  const notifBtn = document.getElementById('notifBtn');
  const notifBadge = document.getElementById('notifBadge');

  // modals elements
  const notifModalEl = document.getElementById('notifModal');
  const assignModalEl = document.getElementById('assignModal');
  const rescheduleModalEl = document.getElementById('rescheduleModal');
  const cancelModalEl = document.getElementById('cancelModal');

  let notifModal = null, assignModal = null, rescheduleModal = null, cancelModal = null;
  if (window.bootstrap) {
    notifModal = new bootstrap.Modal(notifModalEl);
    assignModal = new bootstrap.Modal(assignModalEl);
    rescheduleModal = new bootstrap.Modal(rescheduleModalEl);
    cancelModal = new bootstrap.Modal(cancelModalEl);
  }

  const assign_estilista = document.getElementById('assign_estilista');
  const assign_id_cita = document.getElementById('assign_id_cita');
  const btnAssignConfirm = document.getElementById('btnAssignConfirm');

  const res_id_cita = document.getElementById('res_id_cita');
  const res_fecha = document.getElementById('res_fecha');
  const res_hora_inicio = document.getElementById('res_hora_inicio');
  const res_hora_fin = document.getElementById('res_hora_fin');
  const res_estilista = document.getElementById('res_estilista');
  const btnRescheduleConfirm = document.getElementById('btnRescheduleConfirm');

  const cancel_id_cita = document.getElementById('cancel_id_cita');
  const cancel_motivo = document.getElementById('cancel_motivo');
  const btnCancelConfirm = document.getElementById('btnCancelConfirm');

  let myEstilistaId = null;
  let refreshTimer = null;
  let myRole = null;
  let stylistOptions = [];

  function getMyEstilista(){
    return fetch('/salon_belleza/controllers/PersonalController.php?action=getMyEstilista')
      .then(r=>r.json()).then(res=>{
        if(!res.success || !res.data) throw new Error(res.message || 'Error obteniendo perfil');
        myRole = res.data.id_rol ? parseInt(res.data.id_rol) : null;
        if(res.data.id_estilista){ myEstilistaId = res.data.id_estilista; return myEstilistaId; }
        if(res.data.id_rol && (parseInt(res.data.id_rol) === 1 || parseInt(res.data.id_rol) === 2)){ myEstilistaId = 0; return myEstilistaId; }
        throw new Error(res.message || 'No tienes permisos para ver esta página');
      });
  }

  function loadAgenda(){
    if(myEstilistaId === null) { alert('No se encontró el perfil de estilista. Contacta al administrador.'); return; }
    const fecha = selFecha.value;
    const params = new URLSearchParams();
    let targetEst = myEstilistaId;
    if (selEstilista && selEstilista.value !== '') targetEst = selEstilista.value;
    params.append('id_estilista', targetEst);
    if (fecha) params.append('fecha', fecha);
    fetch(`/salon_belleza/controllers/PersonalController.php?action=getAgenda&${params.toString()}`)
      .then(r=>r.json()).then(res=>{ if(res.success) renderAgenda(res.data); else { alert(res.message || 'Error cargando agenda'); } }).catch(err=>{ alert(err.message || 'Error'); });
  }

  function renderAgenda(data){
    const h = data.horarios || [];
    const c = data.citas || [];
    function formatDate(d){ if(!d) return ''; const parts = d.split('-'); if(parts.length!==3) return d; return `${parts[2]}/${parts[1]}/${parts[0]}`; }
    function estadoBadge(estado){ if(!estado) return '<span class="badge bg-secondary">Sin estado</span>'; const e = estado.toLowerCase(); if(e.includes('confirm')) return `<span class="badge bg-success">${estado}</span>`; if(e.includes('pend')) return `<span class="badge bg-warning text-dark">${estado}</span>`; if(e.includes('cancel') || e.includes('anul')) return `<span class="badge bg-danger">${estado}</span>`; return `<span class="badge bg-secondary">${estado}</span>`; }

    let html = '';
    if (myRole === 3) {
      html += '<div class="card p-3 mb-3">';
      html += '<h5>Horario base</h5>';
      if(h.length===0){ html += '<p class="text-muted">No hay horario definido para este día.</p>'; } else {
        html += '<div class="row g-2 mb-3">';
        h.forEach(it=>{ html += `<div class="col-sm-6"><div class="border rounded p-2"><strong>${it.dia_semana}</strong><div class="text-muted">${it.hora_inicio} - ${it.hora_fin}</div></div></div>`; });
        html += '</div>';
      }
      html += '<h5>Citas asignadas</h5>';
    } else {
      html += '<div class="card p-3 mb-3">'; html += '<h5>Citas asignadas</h5>';
    }
  html += '<div class="table-responsive">';
  // construir encabezados condicionalmente: la columna 'Acciones' solo para admin(1) y recepcionista(2)
  let ths = '<th style="width:110px">Fecha</th><th style="width:90px">Hora inicio</th><th style="width:90px">Hora fin</th><th style="width:200px">Cliente</th><th style="width:200px">Servicio</th><th style="width:180px">Estilista</th><th style="width:110px">Estado</th><th>Observaciones</th>';
  if (myRole === 1 || myRole === 2) ths += '<th style="width:220px">Acciones</th>';
  html += `<table class="table table-striped table-hover align-middle small"><thead class="table-light"><tr>${ths}</tr></thead><tbody>`;

    let desde = (filterDesde && filterDesde.value) ? filterDesde.value : '00:00';
    let hasta = (filterHasta && filterHasta.value) ? filterHasta.value : '23:59';
    if(!filterDesde || !filterHasta){ desde = '00:00'; hasta = '23:59'; }
    c.forEach(row=>{
      if(row.hora_inicio && (row.hora_inicio < desde || row.hora_inicio > hasta)) return;
      const clienteNombre = (row.cliente_nombre || '') + (row.cliente_apellido ? (' ' + row.cliente_apellido) : '');
      const servicioNombre = row.servicio_nombre || '';
      const estilistaNombre = (row.estilista_nombre || '') + (row.estilista_apellido ? (' ' + row.estilista_apellido) : '');
      const fechaCita = row.fecha_cita ? formatDate(row.fecha_cita) : '';
      html += `<tr>`;
      html += `<td>${fechaCita}</td>`;
      html += `<td>${row.hora_inicio || ''}</td>`;
      html += `<td>${row.hora_fin || ''}</td>`;
      html += `<td>${clienteNombre}</td>`;
      html += `<td>${servicioNombre}</td>`;
      html += `<td>${estilistaNombre}</td>`;
      html += `<td>${estadoBadge(row.estado)}</td>`;
      html += `<td><div style="max-width:360px; white-space:normal; word-break:break-word;">${row.observaciones?row.observaciones:'-'}</div></td>`;
      // acciones (solo renderizar la celda si el rol lo permite)
      if (myRole === 1 || myRole === 2) {
        // si la cita está cancelada, mostramos la celda pero sin botones
        const estadoLower = (row.estado || '').toString().toLowerCase();
        if (estadoLower.includes('cancel') || estadoLower.includes('anul')) {
          // mostrar celda vacía (sin texto) para citas canceladas
          html += `<td></td>`;
        } else {
          let actions = '';
          actions += `<div class="btn-group" role="group">`;
          actions += `<button class="btn btn-sm btn-outline-primary btn-assign" data-id="${row.id_cita}" title="Reasignar">Reasignar</button>`;
          // auto-assign removed: admins/recepcionistas no deben auto-asignar citas
          actions += `<button class="btn btn-sm btn-outline-warning btn-reschedule" data-id="${row.id_cita}" title="Reagendar">Reagendar</button>`;
          actions += `<button class="btn btn-sm btn-outline-danger btn-cancel" data-id="${row.id_cita}" title="Cancelar">Cancelar</button>`;
          actions += `</div>`;
          html += `<td>${actions}</td>`;
        }
      }
      html += `</tr>`;
    });

    html += '</tbody></table></div>';
    html += '</div>';
    agendaArea.innerHTML = html;

    // attach event listeners for action buttons
    document.querySelectorAll('.btn-assign').forEach(b=>{
      b.addEventListener('click', function(){ const id = this.dataset.id; assign_id_cita.value = id; populateStylistSelects(); assignModal && assignModal.show(); });
    });
    // auto-assign listener removed (feature disabled)
    document.querySelectorAll('.btn-reschedule').forEach(b=>{
      b.addEventListener('click', function(){ const id = this.dataset.id; res_id_cita.value = id; res_fecha.value = ''; res_hora_inicio.value = ''; res_hora_fin.value = ''; populateStylistSelects(); rescheduleModal && rescheduleModal.show(); });
    });
    document.querySelectorAll('.btn-cancel').forEach(b=>{
      b.addEventListener('click', function(){ const id = this.dataset.id; cancel_id_cita.value = id; cancel_motivo.value = ''; cancelModal && cancelModal.show(); });
    });
  }

  function startAutoRefresh(){ if(refreshTimer) clearInterval(refreshTimer); refreshTimer = setInterval(()=>{ loadAgenda(); }, 30000); }

  btnCargar.addEventListener('click', function(){ loadAgenda(); });
  // limpiar filtros
  const btnLimpiar = document.getElementById('btnLimpiar');
  if(btnLimpiar){ btnLimpiar.addEventListener('click', function(){ if(selFecha) selFecha.value = ''; if(filterDesde) filterDesde.value = '00:00'; if(filterHasta) filterHasta.value = '23:59'; if(selEstilista) selEstilista.value = ''; loadAgenda(); }); }

  // iniciar
  getMyEstilista().then(id=>{
    if (selEstilista && id === 0) {
      fetch('/salon_belleza/controllers/ServicioController.php?action=catalogData').then(r=>r.json()).then(res=>{
          if(res.success && res.data && Array.isArray(res.data.stylists)){
            stylistOptions = res.data.stylists;
            res.data.stylists.forEach(s => { const opt = document.createElement('option'); opt.value = s.id_estilista; opt.textContent = s.nombre || (s.id_estilista); selEstilista.appendChild(opt); });
          }
        }).catch(()=>{/* no bloquear carga si falla */}).finally(()=>{ loadAgenda(); startAutoRefresh(); fetchNotifications(); });
    } else {
      // cargar estilistas en background para modales
      fetch('/salon_belleza/controllers/ServicioController.php?action=catalogData').then(r=>r.json()).then(res=>{ if(res.success && res.data && Array.isArray(res.data.stylists)) stylistOptions = res.data.stylists; }).finally(()=>{ loadAgenda(); startAutoRefresh(); fetchNotifications(); });
    }
  }).catch(err=>{ agendaArea.innerHTML = `<div class="alert alert-warning">${err.message}</div>`; });

  // ------- Notificaciones -------
  function fetchNotifications(){ fetch('/salon_belleza/controllers/PersonalController.php?action=getNotifications').then(r=>r.json()).then(res=>{ if(!res.success) return; const unread = res.data.unread || 0; if(unread>0){ notifBadge.style.display='inline-block'; notifBadge.textContent = unread; } else { notifBadge.style.display='none'; } window.__mis_citas_notifications = res.data.notifications || []; }).catch(()=>{}); }

  notifBtn && notifBtn.addEventListener('click', function(){ const list = window.__mis_citas_notifications || []; const container = document.getElementById('notifList'); if(list.length===0){ container.innerHTML = '<div class="text-muted">No hay notificaciones.</div>'; notifModal && notifModal.show(); return; } let html = '<div class="list-group">'; list.forEach(n=>{ const read = n.read ? 'opacity-75' : 'fw-bold'; html += `<a href="#" class="list-group-item list-group-item-action ${read}" data-id="${n.id}"><div><strong>${n.title}</strong> <small class="text-muted">${n.created_at}</small></div><div class="small">${n.message}</div></a>`; }); html += '</div>'; container.innerHTML = html; container.querySelectorAll('.list-group-item').forEach(a=>{ a.addEventListener('click', function(e){ e.preventDefault(); const id = this.dataset.id; fetch('/salon_belleza/controllers/PersonalController.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: new URLSearchParams({action:'markNotificationRead', id:id})}).then(r=>r.json()).then(res=>{ fetchNotifications(); loadAgenda(); }); }); }); notifModal && notifModal.show(); });

  function populateStylistSelects(){ if(!stylistOptions || stylistOptions.length===0) return; if(assign_estilista){ assign_estilista.innerHTML = '<option value="">-- Seleccione --</option>'; stylistOptions.forEach(s=>{ const opt = document.createElement('option'); opt.value = s.id_estilista; opt.textContent = s.nombre || (s.id_estilista); assign_estilista.appendChild(opt); }); } if(res_estilista){ res_estilista.innerHTML = '<option value="">(mantener)</option>'; stylistOptions.forEach(s=>{ const opt = document.createElement('option'); opt.value = s.id_estilista; opt.textContent = s.nombre || (s.id_estilista); res_estilista.appendChild(opt); }); } }

  // Assign confirm
  btnAssignConfirm && btnAssignConfirm.addEventListener('click', function(){ const id = assign_id_cita.value; const est = assign_estilista.value; if(!id || !est){ alert('Seleccione un estilista'); return; } fetch('/salon_belleza/controllers/PersonalController.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: new URLSearchParams({action:'assignCita', id_cita:id, id_estilista:est})}).then(r=>r.json()).then(res=>{ if(res.success){ assignModal && assignModal.hide(); fetchNotifications(); loadAgenda(); } else alert(res.message || 'Error'); }); });

  // Reschedule confirm
  btnRescheduleConfirm && btnRescheduleConfirm.addEventListener('click', function(){ const id = res_id_cita.value; const fecha = res_fecha.value; const hi = res_hora_inicio.value; const hf = res_hora_fin.value; const ie = res_estilista.value; if(!id || !fecha || !hi || !hf){ alert('Complete fecha y horas'); return; } const body = { action:'rescheduleCita', id_cita:id, fecha:fecha, hora_inicio:hi, hora_fin:hf }; if(ie) body.id_estilista = ie; fetch('/salon_belleza/controllers/PersonalController.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: new URLSearchParams(body)}).then(r=>r.json()).then(res=>{ if(res.success){ rescheduleModal && rescheduleModal.hide(); fetchNotifications(); loadAgenda(); } else alert(res.message || 'Error'); }); });

  // Cancel confirm
  btnCancelConfirm && btnCancelConfirm.addEventListener('click', function(){ const id = cancel_id_cita.value; const motivo = cancel_motivo.value || 'Cancelada desde recepción'; if(!id){ alert('ID inválido'); return; } if(!confirm('Confirmar cancelación?')) return; fetch('/salon_belleza/controllers/PersonalController.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: new URLSearchParams({action:'cancelCita', id_cita:id, motivo:motivo})}).then(r=>r.json()).then(res=>{ if(res.success){ cancelModal && cancelModal.hide(); fetchNotifications(); loadAgenda(); } else alert(res.message || 'Error'); }); });

});
