document.addEventListener('DOMContentLoaded', function() {
  const target = document.getElementById('catalogoServicios');

  function formatPrice(p) {
    return Number(p).toFixed(2);
  }

  function renderServicios(data) {
    target.innerHTML = '';
    data.forEach(s => {
      const col = document.createElement('div');
      col.className = 'col-md-4 mb-4';

      // build specs inputs
      let specsHtml = '';
      if (s.especificaciones && s.especificaciones.length) {
        specsHtml += '<div class="mb-2"><strong>Opciones:</strong>';
        s.especificaciones.forEach(v => {
          // use checkbox; store data attributes for price/time and tipo
          const tipo = v.tipo || '';
          let labelExtra = '';
          if (tipo === 'precio') labelExtra = `(+${formatPrice(v.valor_precio)})`;
          else if (tipo === 'tiempo') labelExtra = `(+${v.valor_tiempo} min)`;
          else labelExtra = `(+${formatPrice(v.valor_precio)} | +${v.valor_tiempo} min)`;

          specsHtml += `\n<div class="form-check">`;
          specsHtml += `<input class="form-check-input espec-check" type="checkbox" data-precio="${v.valor_precio}" data-tiempo="${v.valor_tiempo}" data-tipo="${tipo}" data-nombre="${escapeHtml(v.nombre)}" id="esp-${v.id_especificacion}" />`;
          specsHtml += `<label class="form-check-label" for="esp-${v.id_especificacion}">${escapeHtml(v.nombre)} ${labelExtra}</label>`;
          specsHtml += `</div>`;
        });
        specsHtml += '</div>';
      }

      col.innerHTML = `
        <div class="card p-3 h-100">
          <h5>${escapeHtml(s.nombre)}</h5>
          <p class="text-muted">${escapeHtml(s.descripcion || '')}</p>
          <p><strong>Precio base:</strong> <span class="base-price">${formatPrice(s.precio_base)}</span> $</p>
          <p><strong>Duración base:</strong> <span class="base-time">${s.duracion_base}</span> min</p>
          ${specsHtml}
          <hr>
          <p><strong>Total:</strong> <span class="total-price">${formatPrice(s.precio_base)}</span> $</p>
          <p><strong>Tiempo total:</strong> <span class="total-time">${s.duracion_base}</span> min</p>
        </div>`;

      target.appendChild(col);
    });

    // attach listeners
    document.querySelectorAll('.espec-check').forEach(chk => {
      chk.addEventListener('change', function() {
        const card = this.closest('.card');
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
      if (res.success) renderServicios(res.data);
    }).catch(err => console.error(err));

});
