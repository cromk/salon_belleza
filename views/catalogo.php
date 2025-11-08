<?php
include 'layout/header.php';
?>
<body class="index">
  <?php include 'layout/navbar.php'; ?>
  <div class="container mt-5">
    <div class="text-center mb-4">
      <h3 class="section-title mb-4 text-center">Catálogo de Servicios 💇‍♀️</h3>
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
        <input id="searchCatalog" class="form-control search-input" placeholder="Buscar servicios..." aria-label="Buscar servicios">
        <ul id="searchSuggestions" class="list-group search-suggestions d-none"></ul>
      </div>
      <div class="ms-2" style="min-width:260px; max-width:420px; width:40%; position:relative;">
        <input id="filterStylist" class="form-control search-input" placeholder="Filtrar por estilista..." aria-label="Filtrar por estilista">
        <ul id="filterStylistSuggestions" class="list-group search-suggestions d-none" style="position:absolute; z-index:1050; width:100%;"></ul>
      </div>
      <?php $role = $_SESSION['usuario']['id_rol'] ?? 0; if ($role == 2): ?>
      <div class="ms-2">
        <button id="btnVerDisponibilidad" class="btn btn-outline-primary btn-sm">Ver disponibilidad</button>
      </div>
        <div class="ms-2">
          <button id="btnReservarDesdeModal" class="btn btn-primary btn-sm d-none">Reservar</button>
        </div>
      <?php endif; ?>
    </div>
    <div class="row" id="catalogoServicios">
      <!-- servicios serán cargados vía AJAX -->
    </div>
    <!-- Modal disponibilidad -->
    <div class="modal fade" id="availabilityModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Disponibilidad</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-2 mb-3 align-items-end">
              <div class="col-md-6">
                <label class="form-label">Estilista</label>
                <input id="availEstilistaDisplay" class="form-control" disabled>
              </div>
              <div class="col-md-6">
                <label class="form-label">Fecha</label>
                <input id="availFecha" type="date" class="form-control" value="">
              </div>
            </div>
            <div id="availSlotsArea">Cargando...</div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include 'layout/footer.php'; ?>
    <script src="/salon_belleza/assets/js/catalogo.js"></script>
</body>
</html>
