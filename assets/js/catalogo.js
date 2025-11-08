document.addEventListener('DOMContentLoaded', function() {
  const target = document.getElementById('catalogoServicios');
  const searchInput = document.getElementById('searchCatalog');
  const suggestionsEl = document.getElementById('searchSuggestions');
  const filterStylist = document.getElementById('filterStylist');
  const filterStylistSuggestions = document.getElementById('filterStylistSuggestions');
  const filterServiceType = document.getElementById('filterServiceType');
  let servicesData = [];
  let serviceMap = {};
  let stylistsList = [];
  let selectedServiceId = null;

  function formatPrice(p) {
    return Number(p).toFixed(2);
  }

  function renderServicios(data) {
    target.innerHTML = '';
    data.forEach(s => {
      const col = document.createElement('div');
      col.className = 'col-md-4 mb-4';

      // build specs inputs inside a collapse
      let specsHtml = '';
      const collapseId = `specs-collapse-${s.id_servicio}`;
      if (s.especificaciones && s.especificaciones.length) {
        specsHtml += `<div class="mt-2">
            <a class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" href="#${collapseId}" role="button" aria-expanded="false" aria-controls="${collapseId}">Opciones</a>
            <div class="collapse mt-2" id="${collapseId}">`;
        s.especificaciones.forEach(v => {
          const tipo = v.tipo || '';
          let labelExtra = '';
          if (tipo === 'precio') labelExtra = `(+${formatPrice(v.valor_precio)})`;
          else if (tipo === 'tiempo') labelExtra = `(+${v.valor_tiempo} min)`;
          else labelExtra = `(+${formatPrice(v.valor_precio)} | +${v.valor_tiempo} min)`;

          specsHtml += `<div class="form-check py-1">
              <input class="form-check-input espec-check" type="checkbox" data-precio="${v.valor_precio}" data-tiempo="${v.valor_tiempo}" data-tipo="${tipo}" data-nombre="${escapeHtml(v.nombre)}" id="esp-${v.id_especificacion}" />
              <label class="form-check-label ms-2" for="esp-${v.id_especificacion}">${escapeHtml(v.nombre)} <small class="text-muted">${labelExtra}</small></label>
            </div>`;
        });
        specsHtml += `</div></div>`;
      }

      col.innerHTML = `
        <div class="service-card h-100 shadow-sm rounded overflow-hidden">
          <div class="service-header p-3 text-white">
            <h5 class="mb-0">${escapeHtml(s.nombre)}</h5>
            <small class="d-block opacity-75">${escapeHtml(s.descripcion || '')}</small>
          </div>
          <div class="service-body p-3 d-flex flex-column">
            <!-- image placeholder removed: catalog displays info-only cards without image -->
            <div class="mb-2 d-flex gap-2">
              <span class="badge bg-primary">Precio: <strong class="base-price ms-1">${formatPrice(s.precio_base)}</strong>$</span>
              <span class="badge bg-secondary">Duración: <strong class="base-time ms-1">${s.duracion_base}</strong> min</span>
            </div>
            ${specsHtml}
            <div class="mt-auto d-flex justify-content-between align-items-center pt-3 border-top">
              <div>
                <div>Total: <strong class="total-price">${formatPrice(s.precio_base)}</strong>$</div>
                <div class="text-muted">Tiempo: <strong class="total-time">${s.duracion_base}</strong> min</div>
              </div>
            </div>
          </div>
        </div>`;

      target.appendChild(col);
      // make card selectable: click anywhere on the card selects the service
      const card = col.querySelector('.service-card');
      if (card) {
        card.style.cursor = 'pointer';
        card.addEventListener('click', function(e){
          // ignore clicks on inputs/buttons inside the card (like opciones or checkboxes)
          if (e.target.closest('button') || e.target.closest('input') || e.target.closest('a')) return;
          // clear previous selection
          document.querySelectorAll('.service-card.selected-service').forEach(c => {
            c.classList.remove('selected-service');
            c.classList.remove('border','border-3','border-primary');
          });
          // mark this one
          card.classList.add('selected-service');
          card.classList.add('border','border-3','border-primary');
          selectedServiceId = s.id_servicio;
        });
      }
    });

    // attach listeners
    document.querySelectorAll('.espec-check').forEach(chk => {
      chk.addEventListener('change', function() {
        const card = this.closest('.service-card');
        recalcCard(card);
      });
    });
  }

  function recalcCard(card) {
    const basePrice = parseFloat(card.querySelector('.base-price').textContent) || 0;
    const baseTime = parseInt(card.querySelector('.base-time').textContent) || 0;
    let totalPrice = basePrice;
    let totalTime = baseTime;

    card.querySelectorAll('.espec-check:checked').forEach(ch => {
      const tipo = ch.dataset.tipo || '';
      if (tipo === 'precio') {
        totalPrice += parseFloat(ch.dataset.precio) || 0;
      } else if (tipo === 'tiempo') {
        totalTime += parseInt(ch.dataset.tiempo) || 0;
      } else {
        // ambos o sin tipo definido
        totalPrice += parseFloat(ch.dataset.precio) || 0;
        totalTime += parseInt(ch.dataset.tiempo) || 0;
      }
    });

    card.querySelector('.total-price').textContent = totalPrice.toFixed(2);
    card.querySelector('.total-time').textContent = totalTime;
  }

  function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/[&<>\"']/g, function(c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[c]; });
  }

  // fetch catalog data (services + especificaciones + stylists)
  fetch('/salon_belleza/controllers/ServicioController.php?action=catalogData')
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        servicesData = res.data.services || [];
        serviceMap = res.data.serviceMap || {};
        stylistsList = res.data.stylists || [];
        populateFilters();
        renderServicios(servicesData);
      } else {
        console.error('No autorizado o error:', res.message);
        // fallback: try public read
        return fetch('/salon_belleza/controllers/ServicioController.php?action=read').then(r=>r.json()).then(r2=>{ if(r2.success){ servicesData=r2.data||[]; renderServicios(servicesData); } });
      }
    }).catch(err => console.error(err));

  function populateFilters(){
    // populate stylist suggestions map
    const stylistsByName = {};
    if(filterStylist && stylistsList && stylistsList.length){
      stylistsList.forEach(s => {
        // map by display name
        stylistsByName[s.nombre] = s.id_estilista;
      });
      // attach input listener to show suggestions
      filterStylist.addEventListener('input', debounce(function(){
        const q = (this.value || '').trim().toLowerCase();
        if(!q){ filterStylistSuggestions.classList.add('d-none'); filterStylistSuggestions.innerHTML = ''; applyFilters(); return; }
        const matches = stylistsList.filter(st => (st.nombre || '').toLowerCase().includes(q)).slice(0,8);
        if(!matches.length){ filterStylistSuggestions.classList.add('d-none'); filterStylistSuggestions.innerHTML = ''; return; }
        filterStylistSuggestions.innerHTML = matches.map(m => `<li class="list-group-item list-group-item-action stylist-suggestion" data-id="${m.id_estilista}">${escapeHtml(m.nombre)}</li>`).join('');
        filterStylistSuggestions.classList.remove('d-none');
        // attach click handlers
        filterStylistSuggestions.querySelectorAll('.stylist-suggestion').forEach(li => li.addEventListener('click', function(){
          filterStylist.value = this.textContent;
          filterStylist.dataset.selectedEst = this.dataset.id;
          filterStylistSuggestions.classList.add('d-none');
          applyFilters();
        }));
      }, 160));
      // hide suggestions when clicking outside
      document.addEventListener('click', function(e){ if(!e.target.closest('#filterStylistSuggestions') && !e.target.closest('#filterStylist')) { filterStylistSuggestions.classList.add('d-none'); } });
    }
    if(filterServiceType && servicesData && servicesData.length){
      // fill service select with service names
      servicesData.forEach(s => {
        const opt = document.createElement('option'); opt.value = s.id_servicio; opt.textContent = s.nombre; filterServiceType.appendChild(opt);
      });
    }
    if(filterServiceType) filterServiceType.addEventListener('change', applyFilters);
    // disponibilidad: botón y modal (si existe)
    const btnDisp = document.getElementById('btnVerDisponibilidad');
    if(btnDisp && filterStylist){
      btnDisp.addEventListener('click', function(){
        // Validación: debe haber un servicio seleccionado
        if(!selectedServiceId){
          // mostrar mensaje claro
          alert('Selecciona un servicio del catálogo antes de ver la disponibilidad. Haz clic en la tarjeta del servicio para seleccionarlo.');
          return;
        }
        // obtener id de estilista usando dataset o buscando por nombre
        let estId = filterStylist.dataset && filterStylist.dataset.selectedEst ? filterStylist.dataset.selectedEst : '';
        if(!estId){
          const name = (filterStylist.value||'').trim();
          const found = stylistsList.find(st => st.nombre === name);
          if(found) estId = found.id_estilista;
        }
        if(!estId){
          alert('Selecciona o escribe y elige un estilista válido para ver la disponibilidad.');
          return;
        }
        // preparar modal
        const modalEl = document.getElementById('availabilityModal');
        const availEstDisplay = document.getElementById('availEstilistaDisplay');
        const availFecha = document.getElementById('availFecha');
        const availSlotsArea = document.getElementById('availSlotsArea');
        const stylistObj = stylistsList.find(s=> String(s.id_estilista) === String(estId));
        availEstDisplay.value = stylistObj ? stylistObj.nombre : estId;
        // si no hay fecha, usar hoy
        const today = new Date().toISOString().substr(0,10);
        availFecha.value = today;
        availSlotsArea.innerHTML = 'Cargando...';
        // show modal (Bootstrap)
        const m = new bootstrap.Modal(modalEl); m.show();
        // fetch slots
        fetchSlots(estId, availFecha.value, availSlotsArea);
        // cambiar fecha -> recargar
        availFecha.addEventListener('change', function(){ fetchSlots(estId, this.value, availSlotsArea); });
      });
    }
  }

  // --- Helpers para manipular tiempos y fragmentar intervalos ---
  function timeStrToMinutes(t) {
    if (!t) return 0;
    // aceptar formatos HH:MM o HH:MM:SS
    const parts = t.split(':').map(x=>parseInt(x,10)||0);
    return (parts[0]||0)*60 + (parts[1]||0);
  }

  function minutesToTime(m) {
    const hh = Math.floor(m/60);
    const mm = m%60;
    return String(hh).padStart(2,'0') + ':' + String(mm).padStart(2,'0');
  }

  function splitIntervalIntoSlots(startStr, endStr, durationMin) {
    const start = timeStrToMinutes(startStr);
    const end = timeStrToMinutes(endStr);
    const slots = [];
    if (durationMin <= 0 || start >= end) return slots;
    let cursor = start;
    while (cursor + durationMin <= end) {
      slots.push({ hora_inicio: minutesToTime(cursor), hora_fin: minutesToTime(cursor + durationMin) });
      cursor += durationMin; // contiguous slots; could be stepped differently if needed
    }
    return slots;
  }

  function fetchSlots(estId, fecha, targetEl){
    targetEl.innerHTML = 'Cargando...';
    fetch(`/salon_belleza/controllers/PersonalController.php?action=getAvailableSlots&id_estilista=${encodeURIComponent(estId)}&fecha=${encodeURIComponent(fecha)}`)
      .then(r=>r.json()).then(res=>{
        if(!res.success){ targetEl.innerHTML = `<div class="alert alert-warning">${res.message||'No disponible'}</div>`; return; }
        const slots = res.data || [];
        if(!slots.length){ targetEl.innerHTML = '<div class="text-muted">No hay slots libres para la fecha seleccionada.</div>'; return; }
        // intent: fragmentar los intervalos libres en ranuras del tamaño del servicio seleccionado
        // obtener duración y precio total desde la tarjeta seleccionada
        let serviceDuration = 0; let serviceTotalPrice = 0; let serviceName = '';
        const selectedCard = document.querySelector('.service-card.selected-service');
        if (selectedCard) {
          serviceDuration = parseInt(selectedCard.querySelector('.total-time').textContent) || 0;
          serviceTotalPrice = parseFloat(selectedCard.querySelector('.total-price').textContent) || 0;
          serviceName = selectedCard.querySelector('.service-header h5') ? selectedCard.querySelector('.service-header h5').textContent : '';
        } else if (selectedServiceId) {
          // fallback a datos del servicio
          const s = servicesData.find(x => String(x.id_servicio) === String(selectedServiceId));
          if (s) { serviceDuration = parseInt(s.duracion_base) || 0; serviceTotalPrice = parseFloat(s.precio_base)||0; serviceName = s.nombre||''; }
        }

        let html = '';
        // slots container
        html += '<div class="mb-3"><strong>Ranuras disponibles</strong></div>';
        html += '<div class="list-group mb-3" id="availSlotsList">';
        // por cada intervalo libre fragmentamos
        slots.forEach(interval => {
          const frag = splitIntervalIntoSlots(interval.hora_inicio, interval.hora_fin, serviceDuration || 30);
          if (frag.length === 0) {
            // si no pudimos fragmentar (duración 0), mostrar intervalo entero
            html += `<button class="list-group-item list-group-item-action avail-slot" data-start="${interval.hora_inicio}" data-end="${interval.hora_fin}" data-price="${serviceTotalPrice}">${interval.hora_inicio} - ${interval.hora_fin}</button>`;
          } else {
            frag.forEach(s => {
              html += `<button class="list-group-item list-group-item-action avail-slot" data-start="${s.hora_inicio}" data-end="${s.hora_fin}" data-price="${serviceTotalPrice}">${s.hora_inicio} - ${s.hora_fin}</button>`;
            });
          }
        });
        html += '</div>';

        // booking form container (hidden until a slot is chosen)
        html += `<div id="availBookingForm" class="card p-3 d-none">
          <h6>Reservar cita</h6>
          <div class="row g-2">
            <div class="col-md-6"><input id="bk_nombre" class="form-control" placeholder="Nombre cliente"></div>
            <div class="col-md-6"><input id="bk_apellido" class="form-control" placeholder="Apellido cliente"></div>
            <div class="col-md-6"><input id="bk_telefono" class="form-control" placeholder="Teléfono"></div>
            <div class="col-md-6"><input id="bk_correo" class="form-control" placeholder="Correo (opcional)"></div>
            <div class="col-12"><input id="bk_observaciones" class="form-control" placeholder="Observaciones (opcional)"></div>
            <div class="col-12">
              <label class="form-label">Variaciones</label>
              <div id="bk_specs_list" class="mb-2 small text-muted">Cargando opciones...</div>
            </div>
            <div class="col-12">
              <div id="bk_breakdown" class="small text-muted">
                <!-- breakdown: base price, especificaciones, total -->
              </div>
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" value="" id="bk_confirm_total_checkbox">
                <label class="form-check-label" for="bk_confirm_total_checkbox">He confirmado el total a pagar: <strong id="bk_confirm_total_amount">$0.00</strong></label>
              </div>
            </div>
            <div class="col-12 text-end">
              <button id="bk_confirm" class="btn btn-primary btn-sm mt-2" disabled>Confirmar reserva</button>
              <button id="bk_cancel" class="btn btn-secondary btn-sm mt-2">Cancelar</button>
            </div>
            <div class="col-12 mt-2" id="bk_result"></div>
          </div>
        </div>`;

        targetEl.innerHTML = html;
        const slotsList = targetEl.querySelectorAll('.avail-slot');
        slotsList.forEach(btn => btn.addEventListener('click', function(){
          // marcar selección
          slotsList.forEach(b=>b.classList.remove('active'));
          this.classList.add('active');
          // preparar formulario
          const start = this.dataset.start; const end = this.dataset.end; const price = parseFloat(this.dataset.price)||0;
          const form = document.getElementById('availBookingForm');
          form.classList.remove('d-none');
          document.getElementById('bk_result').innerHTML = '';
          // prefill some hidden context on the form element for submission
          form.dataset.start = start; form.dataset.end = end; form.dataset.price = price; form.dataset.date = fecha; form.dataset.estid = estId; form.dataset.svcid = selectedServiceId;
          // focus nombre
          document.getElementById('bk_nombre').focus();

          // populate specs list inside modal and breakdown: base price + selected especificaciones
          try {
            const bkBreak = document.getElementById('bk_breakdown');
            const bkAmount = document.getElementById('bk_confirm_total_amount');
            const bkSpecsList = document.getElementById('bk_specs_list');
            let basePrice = 0;
            let lines = [];
            // get service object
            const sObj = servicesData.find(x => String(x.id_servicio) === String(selectedServiceId));
            const selCard = document.querySelector('.service-card.selected-service');
            if (sObj) {
              basePrice = parseFloat(sObj.precio_base) || 0;
              // render specs checkboxes from sObj.especificaciones
              const specs = sObj.especificaciones || [];
              if (specs.length) {
                const specsHtml = specs.map(sp => {
                  const preco = parseFloat(sp.valor_precio) || 0;
                  const tmin = parseInt(sp.valor_tiempo) || 0;
                  const tipo = sp.tipo || '';
                  // check if card had it checked
                  let initiallyChecked = false;
                  if (selCard) {
                    const cardChk = selCard.querySelector(`#esp-${sp.id_especificacion}`);
                    if (cardChk && cardChk.checked) initiallyChecked = true;
                  }
                  const label = escapeHtml(sp.nombre || 'Opción');
                  const extra = [];
                  if (preco) extra.push(`+$${preco.toFixed(2)}`);
                  if (tmin) extra.push(`+${tmin} min`);
                  return `<div class="form-check">
                      <input class="form-check-input bk-espec-check" type="checkbox" id="bk_esp-${sp.id_especificacion}" data-precio="${preco}" data-tiempo="${tmin}" data-nombre="${escapeHtml(sp.nombre)}" data-id="${sp.id_especificacion}" ${initiallyChecked? 'checked':''}>
                      <label class="form-check-label ms-2" for="bk_esp-${sp.id_especificacion}">${label} <small class="text-muted">${extra.join(' | ')}</small></label>
                    </div>`;
                }).join('');
                bkSpecsList.innerHTML = specsHtml;
              } else {
                bkSpecsList.innerHTML = '<div class="text-muted">Sin variaciones disponibles.</div>';
              }
            } else {
              bkSpecsList.innerHTML = '<div class="text-muted">Sin variaciones disponibles.</div>';
            }

            function recalcModalTotal() {
              let totalCalc = basePrice;
              const checked = Array.from(document.querySelectorAll('.bk-espec-check:checked'));
              const linesLocal = [];
              linesLocal.push(`<div>Base: <strong>$${basePrice.toFixed(2)}</strong></div>`);
              if (checked.length) {
                linesLocal.push('<div class="mt-1">Variaciones:</div>');
                checked.forEach(ch => {
                  const nombre = ch.dataset.nombre || 'Opción';
                  const precio = parseFloat(ch.dataset.precio) || 0;
                  const tiempo = parseInt(ch.dataset.tiempo) || 0;
                  const extra = [];
                  if (precio) extra.push(`+$${precio.toFixed(2)}`);
                  if (tiempo) extra.push(`+${tiempo} min`);
                  linesLocal.push(`<div class="ms-3">${escapeHtml(nombre)} <small class="text-muted">${extra.join(' | ')}</small></div>`);
                  totalCalc += precio;
                });
              } else {
                linesLocal.push('<div class="mt-1 text-muted">Sin variaciones seleccionadas.</div>');
              }
              linesLocal.push(`<div class="mt-2">Total a pagar: <strong>$${totalCalc.toFixed(2)}</strong></div>`);
              bkBreak.innerHTML = linesLocal.join('');
              bkAmount.textContent = `$${totalCalc.toFixed(2)}`;
              form.dataset.price = totalCalc.toFixed(2);
              // uncheck the confirmation checkbox and disable confirm button until re-checked
              const chk = document.getElementById('bk_confirm_total_checkbox'); if (chk) { chk.checked = false; }
              const newConfirmBtn = document.getElementById('bk_confirm'); if (newConfirmBtn) newConfirmBtn.disabled = true;
            }

            // attach change listeners to modal spec checkboxes
            document.querySelectorAll('.bk-espec-check').forEach(el => el.addEventListener('change', recalcModalTotal));
            // initial calc
            recalcModalTotal();
          } catch(e) { /* noop */ }

          // attach handlers
          const confirmBtn = document.getElementById('bk_confirm');
          const cancelBtn = document.getElementById('bk_cancel');
          // remove previous listeners by cloning node (simple way)
          const newConfirm = confirmBtn.cloneNode(true); confirmBtn.parentNode.replaceChild(newConfirm, confirmBtn);
          const newCancel = cancelBtn.cloneNode(true); cancelBtn.parentNode.replaceChild(newCancel, cancelBtn);
          // ensure confirm starts disabled until total is confirmed
          newConfirm.disabled = true;

          newCancel.addEventListener('click', function(){
            form.classList.add('d-none');
            slotsList.forEach(b=>b.classList.remove('active'));
          });

          // checkbox to confirm total enables the confirm button
          const totalChk = document.getElementById('bk_confirm_total_checkbox');
          if (totalChk) {
            totalChk.addEventListener('change', function(){
              if (this.checked) newConfirm.disabled = false; else newConfirm.disabled = true;
            });
          }

          newConfirm.addEventListener('click', function(){
            const nombre = document.getElementById('bk_nombre').value.trim();
            const apellido = document.getElementById('bk_apellido').value.trim();
            const telefono = document.getElementById('bk_telefono').value.trim();
            const correo = document.getElementById('bk_correo').value.trim();
            const observ = document.getElementById('bk_observaciones').value.trim();
            if(!nombre){ document.getElementById('bk_result').innerHTML = '<div class="text-danger small">Ingrese al menos el nombre del cliente.</div>'; return; }
            // preparar payload
            const payload = new URLSearchParams();
            payload.append('action','createCita');
            payload.append('id_servicio', form.dataset.svcid || '');
            payload.append('id_estilista', form.dataset.estid || '');
            payload.append('fecha', form.dataset.date || '');
            payload.append('hora_inicio', form.dataset.start || '');
            payload.append('hora_fin', form.dataset.end || '');
            payload.append('total', form.dataset.price || '0');
            payload.append('observaciones', observ || '');
            payload.append('cliente_nombre', nombre);
            payload.append('cliente_apellido', apellido);
            payload.append('cliente_telefono', telefono);
            payload.append('cliente_correo', correo);
            // agregar especificaciones seleccionadas: preferir las del modal (edición), fallback a las de la tarjeta
            try {
              const modalChecked = Array.from(document.querySelectorAll('.bk-espec-check:checked')).map(ch => parseInt(ch.dataset.id,10)).filter(x=>x);
              if (modalChecked.length) {
                payload.append('especificaciones', JSON.stringify(modalChecked));
              } else {
                const selCard = document.querySelector('.service-card.selected-service');
                if (selCard) {
                  const checked = Array.from(selCard.querySelectorAll('.espec-check:checked')).map(ch => {
                    const idAttr = ch.id || '';
                    const m = idAttr.match(/esp-(\d+)/);
                    return m ? parseInt(m[1],10) : null;
                  }).filter(x=>x);
                  if (checked.length) payload.append('especificaciones', JSON.stringify(checked));
                }
              }
            } catch(e) { /* noop */ }

            newConfirm.disabled = true; newConfirm.textContent = 'Reservando...';
            fetch('/salon_belleza/controllers/PersonalController.php', { method: 'POST', body: payload })
              .then(r=>r.json()).then(resp=>{
                newConfirm.disabled = false; newConfirm.textContent = 'Confirmar reserva';
                if(resp.success){
                  document.getElementById('bk_result').innerHTML = '<div class="text-success">Reserva creada correctamente. Actualizando...</div>';
                  // cerrar modal y recargar para ver cambios
                  setTimeout(()=>{ const modalEl = document.getElementById('availabilityModal'); const m = bootstrap.Modal.getInstance(modalEl); if(m) m.hide(); location.reload(); }, 900);
                } else {
                  document.getElementById('bk_result').innerHTML = `<div class="text-danger small">${resp.message || 'Error creando reserva'}</div>`;
                }
              }).catch(err=>{
                newConfirm.disabled = false; newConfirm.textContent = 'Confirmar reserva';
                document.getElementById('bk_result').innerHTML = `<div class="text-danger small">Error de red al crear reserva</div>`;
              });
          });
        }));
      }).catch(err=>{ targetEl.innerHTML = `<div class="alert alert-danger">Error cargando disponibilidad</div>`; });
  }

  function applyFilters(){
    let filtered = servicesData.slice();
    const sId = filterServiceType ? filterServiceType.value : '';
    // obtener id de estilista: si el input tiene data-selectedEst lo usamos, sino intentamos buscar por nombre
    let estId = '';
    if(filterStylist){
      if(filterStylist.dataset && filterStylist.dataset.selectedEst) estId = filterStylist.dataset.selectedEst;
      else {
        const name = (filterStylist.value || '').trim();
        if(name){
          const found = stylistsList.find(st => st.nombre === name);
          if(found) estId = found.id_estilista;
        }
      }
    }
    if(sId){ filtered = filtered.filter(s => String(s.id_servicio) === String(sId)); }
    if(estId){
      // filter services that are offered by selected stylist
      filtered = filtered.filter(s => {
        const list = serviceMap[s.id_servicio] || [];
        return list.includes(parseInt(estId));
      });
    }
    renderServicios(filtered);
  }

  // --- Search / suggestions logic ---
  function debounce(fn, wait) {
    let t;
    return function(...args) {
      clearTimeout(t);
      t = setTimeout(() => fn.apply(this, args), wait);
    };
  }

  function buildSuggestions(query) {
    if (!query) { hideSuggestions(); return; }
    const q = query.trim().toLowerCase();
    const matches = [];
    for (const s of servicesData) {
      if (matches.length >= 8) break;
      const name = (s.nombre || '').toLowerCase();
      const desc = (s.descripcion || '').toLowerCase();
      if (name.includes(q) || desc.includes(q)) {
        matches.push({id: s.id_servicio, text: s.nombre});
        continue;
      }
      // search inside especificaciones
      if (s.especificaciones && s.especificaciones.length) {
        for (const e of s.especificaciones) {
          if ((e.nombre || '').toLowerCase().includes(q)) { matches.push({id: s.id_servicio, text: s.nombre}); break; }
        }
      }
    }
    renderSuggestions(matches);
  }

  function renderSuggestions(items) {
    if (!items || !items.length) { hideSuggestions(); return; }
    suggestionsEl.innerHTML = items.map(it => `<li class="list-group-item list-group-item-action suggestion-item" data-id="${it.id}">${escapeHtml(it.text)}</li>`).join('');
    suggestionsEl.classList.remove('d-none');
    // attach click handlers
    suggestionsEl.querySelectorAll('.suggestion-item').forEach(li => li.addEventListener('click', function(){
      const id = this.dataset.id;
      const text = this.textContent;
      searchInput.value = text;
      hideSuggestions();
      // filter result to the clicked service
      const filtered = servicesData.filter(s => String(s.id_servicio) === String(id));
      renderServicios(filtered);
    }));
  }

  function hideSuggestions() { suggestionsEl.classList.add('d-none'); suggestionsEl.innerHTML = ''; }

  const doFilter = debounce(function() {
    const q = (searchInput.value || '').trim().toLowerCase();
    buildSuggestions(q);
    if (!q) { renderServicios(servicesData); return; }
    const filtered = servicesData.filter(s => {
      const name = (s.nombre || '').toLowerCase();
      const desc = (s.descripcion || '').toLowerCase();
      if (name.includes(q) || desc.includes(q)) return true;
      if (s.especificaciones && s.especificaciones.length) {
        for (const e of s.especificaciones) {
          if ((e.nombre || '').toLowerCase().includes(q)) return true;
        }
      }
      return false;
    });
    renderServicios(filtered);
  }, 220);

  if (searchInput) {
    searchInput.addEventListener('input', doFilter);
    searchInput.addEventListener('focus', function(){ if (this.value) buildSuggestions(this.value); });
    document.addEventListener('click', function(e){ if (!e.target.closest('#searchSuggestions') && !e.target.closest('#searchCatalog')) hideSuggestions(); });
  }

});
