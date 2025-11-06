<?php
include 'layout/header.php';
?>
<body class="index">
  <?php include 'layout/navbar.php'; ?>
  <div class="container mt-5">
    <div class="text-center mb-4">
      <h2>Catálogo de Servicios</h2>
      <p class="text-muted">Elige el servicio que prefieras</p>
    </div>
    <div class="row" id="catalogoServicios">
      <!-- servicios serán cargados vía AJAX -->
    </div>
  </div>

  <?php include 'layout/footer.php'; ?>
  <script>
    // Cargar servicios públicos
    fetch('/salon_belleza/controllers/ServicioController.php?action=read')
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          const target = document.getElementById('catalogoServicios');
          target.innerHTML = '';
          res.data.forEach(s => {
            const col = document.createElement('div');
            col.className = 'col-md-4 mb-4';
            col.innerHTML = `
              <div class="card p-3 h-100">
                <h5>${s.nombre}</h5>
                <p class="text-muted">${s.descripcion || ''}</p>
                <p><strong>Precio:</strong> ${s.precio_base} Bs</p>
                <p><strong>Duración:</strong> ${s.duracion_base} min</p>
              </div>`;
            target.appendChild(col);
          });
        }
      }).catch(err => console.error(err));
  </script>
</body>
</html>
