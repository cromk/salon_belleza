document.getElementById('btnGenerar').addEventListener('click', async () => {
  const tipo = document.getElementById('tipoReporte').value;
  const inicio = document.getElementById('fechaInicio').value;
  const fin = document.getElementById('fechaFin').value;

  const res = await fetch(`/salon_belleza/controllers/ReportesController.php?tipo=${tipo}&inicio=${inicio}&fin=${fin}`);
  const data = await res.json();
  mostrarTabla(data);
  mostrarGrafico(data, tipo);
});

document.getElementById('btnExportarCSV').addEventListener('click', () => {
  const tabla = document.getElementById('tablaDatos');
  if (!tabla.querySelector('tr')) {
    alert('No hay datos para exportar.');
    return;
  }

  let csv = '';
  const filas = tabla.querySelectorAll('tr');

  filas.forEach(row => {
    const cols = [...row.querySelectorAll('th,td')].map(td => `"${td.innerText.replace(/"/g, '""')}"`);
    csv += cols.join(';') + '\r\n';
  });

  const blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  const fechaActual = new Date();
  const fechaFormateada = fechaActual.toISOString().slice(0, 10); // 2025-11-09
  const tipo = document.getElementById('tipoReporte').value;
  const nombreArchivo = `BeautyFlow_${tipo}_${fechaFormateada}.csv`;

  link.href = URL.createObjectURL(blob);
  link.download = nombreArchivo;
  link.click();
});

document.getElementById('btnExportarPDF').addEventListener('click', () => {
  const tabla = document.getElementById('tablaDatos'); 
  if (!tabla.querySelector('tr')) {
    alert('No hay datos para exportar.');
    return;
  }

  const tipo = document.getElementById('tipoReporte').value;
  const inicio = document.getElementById('fechaInicio').value;
  const fin = document.getElementById('fechaFin').value;

  window.open(`/salon_belleza/controllers/ExportarPDFController.php?tipo=${tipo}&inicio=${inicio}&fin=${fin}`, '_blank');
});

function mostrarTabla(datos) {
  const tabla = document.getElementById('tablaDatos');
  if (!datos.length) { tabla.innerHTML = '<tr><td>No hay datos</td></tr>'; return; }

  let html = '<thead><tr>';
  Object.keys(datos[0]).forEach(k => html += `<th>${k}</th>`);
  html += '</tr></thead><tbody>';
  datos.forEach(fila => {
    html += '<tr>';
    Object.values(fila).forEach(v => html += `<td>${v}</td>`);
    html += '</tr>';
  });
  html += '</tbody>';
  tabla.innerHTML = html;
}

let chart;
function mostrarGrafico(datos, tipo) {
  const ctx = document.getElementById('grafico').getContext('2d');
  if (chart) chart.destroy();

  let labels = [];
  let values = [];
  let labelTexto = '';

  switch (tipo) {
    case 'ventas':
      labels = datos.map(d => d.fecha_cita);
      values = datos.map(d => d.total_ventas);
      labelTexto = 'Ventas ($)';
      break;
    case 'servicios':
      labels = datos.map(d => d.nombre);
      values = datos.map(d => d.total);
      labelTexto = 'Servicios más solicitados';
      break;
    case 'ocupacion':
      labels = datos.map(d => d.estilista);
      values = datos.map(d => d.citas);
      labelTexto = 'Citas por estilista';
      break;
    case 'clientes':
      labels = datos.map(d => `${d.nombre} ${d.apellido ?? ''}`);
      values = datos.map(d => d.visitas);
      labelTexto = 'Clientes frecuentes';
      break;
  }

  chart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: labelTexto,
        data: values,
        backgroundColor: '#8a2be2', 
        borderColor: '#5e2a84',
        borderWidth: 1,
        borderRadius: 5
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            color: '#4b286d',
            font: { weight: '600' }
          },
          grid: { color: '#eee' }
        },
        x: {
          ticks: {
            color: '#4b286d',
            font: { weight: '600' }
          },
          grid: { display: false }
        }
      },
      plugins: {
        legend: {
          labels: {
            color: '#4b286d',
            font: { weight: '600' }
          }
        }
      }
    }
  });
}
