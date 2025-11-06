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
    <div class="d-flex justify-content-end align-items-center mb-3 catalog-controls">
      <div class="me-3 text-end d-none d-md-block">
        <!-- logo pequeño decorativo al lado del filtro -->
        <svg width="42" height="42" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="catalog-logo">
          <path d="M2 21s4-1 6-5 6-7 10-7" stroke="#7c3bd6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <circle cx="17" cy="6" r="3" stroke="#b48ff7" stroke-width="1.5"/>
        </svg>
      </div>
      <div style="min-width:260px; max-width:420px; width:40%; position:relative;">
        <input id="searchCatalog" class="form-control search-input" placeholder="Buscar servicios'..." aria-label="Buscar servicios">
        <ul id="searchSuggestions" class="list-group search-suggestions d-none"></ul>
      </div>
    </div>
    <div class="row" id="catalogoServicios">
      <!-- servicios serán cargados vía AJAX -->
    </div>
  </div>

  <?php include 'layout/footer.php'; ?>
    <script src="/salon_belleza/assets/js/catalogo.js"></script>
</body>
</html>
