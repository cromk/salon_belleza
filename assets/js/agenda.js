document.addEventListener('DOMContentLoaded', function(){
  const selEst = document.getElementById('selEstilista');
  const selFecha = document.getElementById('selFecha');
  const btnCargar = document.getElementById('btnCargar');
  const agendaArea = document.getElementById('agendaArea');

  function loadEstilistas(){
    // usar endpoint readEstilistas para obtener id_estilista (no id_usuario)
    fetch('/salon_belleza/controllers/PersonalController.php?action=readEstilistas')
      .then(r=>r.json()).then(res=>{
        if(res.success){
          selEst.innerHTML = '<option value="">-- Seleccione --</option>' + res.data.map(e=>`<option value="${e.id_estilista}">${e.nombre} ${e.apellido}</option>`).join('');
        }
      }).catch(err=>console.error(err));
  }

  function renderAgenda(data){
    // data: {horarios:[], citas:[]}
    const h = data.horarios || [];
    const c = data.citas || [];
    let html = '';
    html += '<div class="card p-3 mb-3">';
    html += '<h5>Horario base</h5>';
    if(h.length===0) html += '<p class="text-muted">No hay horario definido para este día.</p>';
    html += '<ul class="list-group mb-3">';
    h.forEach(it=>{ html += `<li class="list-group-item">${it.dia_semana}: ${it.hora_inicio} - ${it.hora_fin}</li>` });
    html += '</ul>';

    html += '<h5>Citas y Bloqueos</h5>';
    html += '<table class="table"><thead><tr><th>Hora inicio</th><th>Hora fin</th><th>Estado</th><th>Observaciones</th><th>Acción</th></tr></thead><tbody>';
    c.forEach(row=>{
      const isBlock = (row.observaciones||'').indexOf('BLOQUEO:')===0;
      html += `<tr data-id="${row.id_cita}">`;
      html += `<td>${row.hora_inicio}</td><td>${row.hora_fin}</td><td>${row.estado}${isBlock? ' <span class="badge bg-warning text-dark">BLOQUEO</span>':''}</td><td>${row.observaciones||''}</td>`;
      html += `<td>`;
      if(isBlock){ html += `<button class="btn btn-sm btn-danger btn-unblock">Quitar</button>`; }
      html += `</td></tr>`;
    });
    html += '</tbody></table>';

    // Form para bloquear un slot
    html += `<div class="card p-3"><h5>Bloquear horario</h5>
      <div class="row g-2 align-items-end">
        <div class="col-md-3"><label class="form-label">Hora inicio</label><input id="blkInicio" type="time" class="form-control" value="09:00"></div>
        <div class="col-md-3"><label class="form-label">Hora fin</label><input id="blkFin" type="time" class="form-control" value="10:00"></div>
        <div class="col-md-4"><label class="form-label">Motivo</label><input id="blkMotivo" type="text" class="form-control" placeholder="Ej. Permiso" value="Ausencia"></div>
        <div class="col-md-2"><button id="btnBlock" class="btn btn-warning">Bloquear</button></div>
      </div></div>`;

    html += '</div>';
    agendaArea.innerHTML = html;

    // attach unblock handlers
    agendaArea.querySelectorAll('.btn-unblock').forEach(btn=>{
      btn.addEventListener('click', function(){
        const tr = this.closest('tr');
        const id = tr.dataset.id;
        if(!confirm('Quitar bloqueo?')) return;
        const fd = new FormData(); fd.append('action','unblockSlot'); fd.append('id_cita', id);
        fetch('/salon_belleza/controllers/PersonalController.php', {method:'POST', body: fd}).then(r=>r.json()).then(res=>{
          if(res.success) loadAgenda(); else alert(res.message||'Error');
        }).catch(()=>alert('Error'));
      });
    });

    const btnBlock = document.getElementById('btnBlock');
    if(btnBlock){ btnBlock.addEventListener('click', function(){
      const inicio = document.getElementById('blkInicio').value;
      const fin = document.getElementById('blkFin').value;
      const motivo = document.getElementById('blkMotivo').value;
      const est = selEst.value; const fecha = selFecha.value;
      if(!est || !fecha || !inicio || !fin){ alert('Complete todos los campos'); return; }
      if(inicio >= fin){ alert('Hora inicio debe ser menor que fin'); return; }
      const fd = new FormData(); fd.append('action','blockSlot'); fd.append('id_estilista', est); fd.append('fecha', fecha); fd.append('hora_inicio', inicio); fd.append('hora_fin', fin); fd.append('motivo', motivo);
      fetch('/salon_belleza/controllers/PersonalController.php', {method:'POST', body: fd}).then(r=>r.json()).then(res=>{
        if(res.success){ alert('Bloqueo creado'); loadAgenda(); } else { alert(res.message || 'No se pudo bloquear'); }
      }).catch(()=>alert('Error'));
    }); }
  }

  function loadAgenda(){
    const est = selEst.value; const fecha = selFecha.value;
    if(!est || !fecha){ alert('Seleccione estilista y fecha'); return; }
    fetch(`/salon_belleza/controllers/PersonalController.php?action=getAgenda&id_estilista=${est}&fecha=${fecha}`)
      .then(r=>r.json()).then(res=>{ if(res.success) renderAgenda(res.data); else alert(res.message||'Error'); }).catch(()=>alert('Error'));
  }

  btnCargar.addEventListener('click', loadAgenda);
  loadEstilistas();
});
