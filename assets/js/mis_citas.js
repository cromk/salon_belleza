document.addEventListener('DOMContentLoaded', function(){
  const selFecha = document.getElementById('selFecha');
  const btnCargar = document.getElementById('btnCargar');
  const agendaArea = document.getElementById('agendaArea');
  const filterDesde = document.getElementById('filterDesde');
  const filterHasta = document.getElementById('filterHasta');
  const selEstilista = document.getElementById('filterEstilista');

  let myEstilistaId = null;
  let refreshTimer = null;
  let myRole = null;

  function getMyEstilista(){
    return fetch('/salon_belleza/controllers/PersonalController.php?action=getMyEstilista')
      .then(r=>r.json()).then(res=>{
        if(!res.success || !res.data) throw new Error(res.message || 'Error obteniendo perfil');
        // Guardar rol (se usa en la UI para condicionales)
        myRole = res.data.id_rol ? parseInt(res.data.id_rol) : null;
        // Si es estilista obtiene su id_estilista
        if(res.data.id_estilista){
          myEstilistaId = res.data.id_estilista;
          return myEstilistaId;
        }
        // Si es admin (1) o recepcionista (2) debe poder ver todas las citas -> usar id_estilista = 0
        if(res.data.id_rol && (parseInt(res.data.id_rol) === 1 || parseInt(res.data.id_rol) === 2)){
          myEstilistaId = 0;
          return myEstilistaId;
        }
        throw new Error(res.message || 'No tienes permisos para ver esta página');
      });
  }

  function loadAgenda(){
    if(myEstilistaId === null) { alert('No se encontró el perfil de estilista. Contacta al administrador.'); return; }
    const fecha = selFecha.value;
    const params = new URLSearchParams();
    // si existe el select de estilista y el usuario seleccionó uno, usamos ese id (recepcionista)
    let targetEst = myEstilistaId;
    if (selEstilista && selEstilista.value !== '') targetEst = selEstilista.value;
    params.append('id_estilista', targetEst);
    if (fecha) params.append('fecha', fecha);
    fetch(`/salon_belleza/controllers/PersonalController.php?action=getAgenda&${params.toString()}`)
      .then(r=>r.json()).then(res=>{
        if(res.success) renderAgenda(res.data); else { alert(res.message || 'Error cargando agenda'); }
      }).catch(err=>{ alert(err.message || 'Error'); });
  }

  function renderAgenda(data){
    const h = data.horarios || [];
    const c = data.citas || [];

    function formatDate(d){
      if(!d) return '';
      const parts = d.split('-');
      if(parts.length!==3) return d;
      return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }
    function estadoBadge(estado){
      if(!estado) return '<span class="badge bg-secondary">Sin estado</span>';
      const e = estado.toLowerCase();
      if(e.includes('confirm')) return `<span class="badge bg-success">${estado}</span>`;
      if(e.includes('pend')) return `<span class="badge bg-warning text-dark">${estado}</span>`;
      if(e.includes('cancel') || e.includes('anul')) return `<span class="badge bg-danger">${estado}</span>`;
      return `<span class="badge bg-secondary">${estado}</span>`;
    }

    let html = '';
    // Mostrar horario base solo si no es recepcionista
    if (myRole !== 2) {
      html += '<div class="card p-3 mb-3">';
      html += '<h5>Horario base</h5>';
      if(h.length===0){
        html += '<p class="text-muted">No hay horario definido para este día.</p>';
      } else {
        html += '<div class="row g-2 mb-3">';
        h.forEach(it=>{
          html += `<div class="col-sm-6"><div class="border rounded p-2"><strong>${it.dia_semana}</strong><div class="text-muted">${it.hora_inicio} - ${it.hora_fin}</div></div></div>`;
        });
        html += '</div>';
      }

      html += '<h5>Citas asignadas</h5>';
    } else {
      // para recepcionista solo mostrar título general antes de la tabla
      html += '<div class="card p-3 mb-3">';
      html += '<h5>Citas asignadas</h5>';
    }
    html += '<div class="table-responsive">';
  html += '<table class="table table-striped table-hover align-middle small"><thead class="table-light"><tr><th style="width:110px">Fecha</th><th style="width:90px">Hora inicio</th><th style="width:90px">Hora fin</th><th style="width:200px">Cliente</th><th style="width:200px">Servicio</th><th style="width:180px">Estilista</th><th style="width:110px">Estado</th><th>Observaciones</th></tr></thead><tbody>';

    let desde = (filterDesde && filterDesde.value) ? filterDesde.value : '00:00';
    let hasta = (filterHasta && filterHasta.value) ? filterHasta.value : '23:59';
    if(!filterDesde || !filterHasta){
      desde = '00:00'; hasta = '23:59';
    }
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
      html += `</tr>`;
    });

    html += '</tbody></table></div>';
    html += '</div>';
    agendaArea.innerHTML = html;
  }

  function startAutoRefresh(){
    if(refreshTimer) clearInterval(refreshTimer);
    refreshTimer = setInterval(()=>{ loadAgenda(); }, 30000); // cada 30s
  }

  btnCargar.addEventListener('click', function(){ loadAgenda(); });
  
  // limpiar filtros
  const btnLimpiar = document.getElementById('btnLimpiar');
  if(btnLimpiar){
    btnLimpiar.addEventListener('click', function(){
      if(selFecha) selFecha.value = '';
      if(filterDesde) filterDesde.value = '00:00';
      if(filterHasta) filterHasta.value = '23:59';
      if(selEstilista) selEstilista.value = '';
      loadAgenda();
    });
  }

  // iniciar
  getMyEstilista().then(id=>{
    // si existe el select de estilistas (recepcionista) y tenemos permisos para ver todo, cargar lista
    if (selEstilista && id === 0) {
      fetch('/salon_belleza/controllers/ServicioController.php?action=catalogData')
        .then(r=>r.json()).then(res=>{
          if(res.success && res.data && Array.isArray(res.data.stylists)){
            // poblar opciones
            res.data.stylists.forEach(s => {
              const opt = document.createElement('option');
              opt.value = s.id_estilista;
              opt.textContent = s.nombre || (s.id_estilista);
              selEstilista.appendChild(opt);
            });
          }
        }).catch(()=>{/* no bloquear carga si falla */})
        .finally(()=>{ loadAgenda(); startAutoRefresh(); });
    } else {
      loadAgenda(); startAutoRefresh();
    }
  }).catch(err=>{ agendaArea.innerHTML = `<div class="alert alert-warning">${err.message}</div>`; });

});
