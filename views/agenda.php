<?php
include 'layout/header.php';
?>
<body class="index">
  <?php include 'layout/navbar.php'; ?>
  <div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2>Gestión de Agenda - Estilistas</h2>
        <p class="text-muted">Bloquea horarios por ausencias o permisos.</p>
      </div>
      <div>
        <a href="/salon_belleza/views/personal.php" class="btn btn-secondary">Volver a Personal</a>
      </div>
    </div>

    <div class="card p-3 mb-4">
      <div class="row g-2 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Estilista</label>
          <select id="selEstilista" class="form-control"></select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Fecha</label>
          <input type="date" id="selFecha" class="form-control" value="<?php echo date('Y-m-d'); ?>">
        </div>
        <div class="col-md-2">
          <button id="btnCargar" class="btn btn-primary">Cargar</button>
        </div>
      </div>
    </div>

    <div id="agendaArea">
      <!-- agenda cargada aquí -->
    </div>
  </div>

  <?php include 'layout/footer.php'; ?>
  <script src="/salon_belleza/assets/js/agenda.js"></script>
</body>
</html>
