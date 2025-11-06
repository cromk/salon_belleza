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
    <script src="/salon_belleza/assets/js/catalogo.js"></script>
</body>
</html>
