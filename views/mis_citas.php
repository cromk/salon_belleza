<?php
include 'layout/header.php';
?>
<body class="index">
  <?php include 'layout/navbar.php'; ?>
  <div class="container mt-5">
    <?php $role = $_SESSION['usuario']['id_rol'] ?? 0; ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2><?php echo ($role == 2 || $role == 1) ? 'Citas' : 'Mis Citas'; ?></h2>
        <p class="text-muted"><?php echo ($role == 2 || $role == 1) ? 'Listado de citas del salón. Usa el filtro de estilista para ver las citas por profesional.' : 'Vista personal de tus citas asignadas. Actualiza automáticamente y muestra todas tus citas por defecto si no aplicas filtros.'; ?></p>
      </div>
    </div>

    <div class="card p-3 mb-4">
      <div class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label">Fecha </label>
          <input type="date" id="selFecha" class="form-control" value="">
        </div>
        <div class="col-md-2">
          <label class="form-label">Desde (HH:MM)</label>
          <input type="time" id="filterDesde" class="form-control" value="00:00">
        </div>
        <div class="col-md-2">
          <label class="form-label">Hasta (HH:MM)</label>
          <input type="time" id="filterHasta" class="form-control" value="23:59">
        </div>
        <?php if ($role == 2 || $role == 1): /* recepcionistas y admin */ ?>
        <div class="col-md-3">
          <label class="form-label">Estilista</label>
          <select id="filterEstilista" class="form-select">
            <option value="">-- Todos los estilistas --</option>
          </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
        <?php else: ?>
        <div class="col-md-3 d-flex gap-2">
        <?php endif; ?>
          <button id="btnCargar" class="btn btn-primary icon-btn btn-sm" title="Aplicar filtros" aria-label="Aplicar filtros"><i class="bi bi-funnel"></i></button>
          <button id="btnLimpiar" class="btn btn-outline-secondary icon-btn btn-sm" title="Limpiar filtros" aria-label="Limpiar filtros"><i class="bi bi-eraser"></i></button>
        </div>
      </div>
    </div>

    <div id="agendaArea">
      <!-- agenda cargada aquí -->
    </div>
  </div>

  <?php include 'layout/footer.php'; ?>
  <script src="/salon_belleza/assets/js/mis_citas.js"></script>
</body>
</html>
