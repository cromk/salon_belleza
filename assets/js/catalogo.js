document.addEventListener('DOMContentLoaded', function() {
  const target = document.getElementById('catalogoServicios');
  const searchInput = document.getElementById('searchCatalog');
  const suggestionsEl = document.getElementById('searchSuggestions');
  let servicesData = [];

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
              <button class="btn btn-sm btn-primary">Reservar</button>
            </div>
          </div>
        </div>`;

      target.appendChild(col);
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

  // fetch services (includes especificaciones when controller was updated)
  fetch('/salon_belleza/controllers/ServicioController.php?action=read')
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        servicesData = res.data || [];
        renderServicios(servicesData);
      }
    }).catch(err => console.error(err));

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
